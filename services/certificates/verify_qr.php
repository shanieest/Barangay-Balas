<?php
require '../../config/db.php';
header('Content-Type: application/json');

$token = trim($_POST['token'] ?? '');

if (!$token) { 
    http_response_code(400); 
    echo json_encode(['error'=>'No token provided']); 
    exit; 
}

$stmt = $mysqli->prepare("SELECT q.id as qr_id, q.request_id, q.verification_attempts, q.last_verified_at, 
                         dr.*, dt.document_type, CONCAT(r.first_name, ' ', r.last_name) as resident_name
                         FROM document_qr_codes q
                         JOIN document_requests dr ON dr.id = q.request_id
                         JOIN document_types dt ON dt.id = dr.document_type_id
                         JOIN residents r ON r.id = dr.resident_id
                         WHERE q.qr_code = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['error'=>'Invalid QR code']);
    exit;
}

$row = $res->fetch_assoc();
$stmt->close();

// Update verification stats
$mysqli->query("UPDATE document_qr_codes SET 
               verification_attempts = verification_attempts + 1, 
               last_verified_at = NOW() 
               WHERE id = " . intval($row['qr_id']));

$status = $row['status'];

switch($status) {
    case 'Pending':
    case 'Processing':
        echo json_encode([
            'status' => $status, 
            'message' => 'Your document request is still '.strtolower($status).'. Please check back later.'
        ]);
        break;
        
    case 'Disapproved':
        echo json_encode([
            'status' => 'Disapproved', 
            'message' => 'This document request has been disapproved.',
            'notes' => $row['notes'] ?? 'No reason provided.',
            'resident_name' => $row['resident_name'],
            'document_type' => $row['document_type'],
            'date_requested' => $row['date_requested']
        ]);
        break;
        
    case 'Approved':
        // Generate secure viewer token
        $viewToken = bin2hex(random_bytes(32));
        $expires = time() + 300; // 5 minutes

        session_start();
        $_SESSION['viewer_tokens'][$viewToken] = [
            'request_id' => $row['request_id'],
            'expires' => $expires,
            'qr_verified' => true // Mark as QR verified for watermarking
        ];

        $viewerUrl = '/barangay-balas/services/certificates/viewer.php?token=' . $viewToken;
        
        echo json_encode([
            'status' => 'Approved',
            'message' => 'Document verified successfully!',
            'resident_name' => $row['resident_name'],
            'document_type' => $row['document_type'],
            'date_processed' => $row['date_processed'],
            'viewer_url' => $viewerUrl,
            'notes' => $row['notes'] ?? '',
            'verification_info' => [
                'verified_at' => date('Y-m-d H:i:s'),
                'verification_id' => $row['qr_id']
            ]
        ]);
        break;
        
    default:
        echo json_encode(['error'=>'Unknown document status']);
}
?>