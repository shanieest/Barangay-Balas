<?php
// verify_qr.php
require 'db.php';
header('Content-Type: application/json');

$token = trim($_POST['token'] ?? '');

if (!$token) { http_response_code(400); echo json_encode(['error'=>'No token']); exit; }

// find QR record and request
$stmt = $mysqli->prepare("SELECT q.id as qr_id, q.request_id, q.verification_attempts, q.last_verified_at, r.* 
    FROM document_qr_codes q
    JOIN document_requests r ON r.id = q.request_id
    WHERE q.qr_code = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['error'=>'Invalid QR token']);
    exit;
}
$row = $res->fetch_assoc();
$stmt->close();

// increment attempt count
$mysqli->query("UPDATE document_qr_codes SET verification_attempts = verification_attempts + 1, last_verified_at = NOW() WHERE id = " . intval($row['qr_id']));

// Now decide what to return based on request status
$status = $row['status']; // Pending, Approved, Disapproved, Processing, Released
if ($status === 'Pending' || $status === 'Processing') {
    echo json_encode(['status'=>$status, 'message'=>'Your request is '.$status.'. Please wait.']);
    exit;
}

if ($status === 'Disapproved') {
    echo json_encode(['status'=>'Disapproved', 'notes'=>$row['notes'] ?? 'No notes provided.']);
    exit;
}

if ($status === 'Approved') {
    // create a secure one-time token to view the file (prevent direct file URL revealing)
    $viewToken = bin2hex(random_bytes(24));
    $expires = time() + 300; // 5 minutes

    // store in a small table or file — we'll use PHP session for simplicity (better: create a viewer_tokens table).
    session_start();
    $_SESSION['viewer_tokens'][$viewToken] = [
        'request_id' => $row['request_id'],
        'expires' => $expires
    ];

    $viewerUrl = 'viewer.php?token=' . $viewToken;
    // Also include download URL if you want auto-download when admin flagged auto_download
    echo json_encode(['status'=>'Approved', 'viewer_url'=>$viewerUrl, 'auto_download'=>0, 'notes'=>$row['notes'] ?? '']);
    exit;
}
