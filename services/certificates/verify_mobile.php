<?php
require '../../config/db.php';
session_start();

$token = trim($_GET['token'] ?? '');

if (!$token) { 
    header('Location: qr_error.php?message=No QR code token provided');
    exit; 
}

$stmt = $mysqli->prepare("SELECT q.id as qr_id, q.request_id, q.verification_attempts, q.last_verified_at, 
                         dr.*, dt.document_type, CONCAT(r.first_name, ' ', r.last_name) as resident_name,
                         dr.document_file_path
                         FROM document_qr_codes q
                         JOIN document_requests dr ON dr.id = q.request_id
                         JOIN document_types dt ON dt.id = dr.document_type_id
                         JOIN residents r ON r.id = dr.resident_id
                         WHERE q.qr_code = ? AND dr.status = 'Approved' LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header('Location: qr_error.php?message=Invalid QR code or document not approved');
    exit;
}

$document = $res->fetch_assoc();
$stmt->close();

// Update verification stats
$mysqli->query("UPDATE document_qr_codes SET 
               verification_attempts = verification_attempts + 1, 
               last_verified_at = NOW() 
               WHERE id = " . intval($document['qr_id']));

// Generate secure viewer token (NO EXPIRATION)
$viewToken = bin2hex(random_bytes(32));

$_SESSION['viewer_tokens'][$viewToken] = [
    'request_id' => $document['request_id'],
    'qr_verified' => true,
    'created_at' => time()
];

// Redirect directly to PDF stream with QR verification
header('Location: stream_pdf.php?token=' . urlencode($viewToken) . '&qr_verified=1');
exit;
?>