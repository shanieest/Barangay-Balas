<?php
require '../../config/db.php';
session_start();

$token = trim($_GET['token'] ?? '');

if (!$token) { 
    http_response_code(400);
    die("No QR token provided");
}

$stmt = $mysqli->prepare("SELECT q.request_id, dr.document_file_path, dr.status,
                         CONCAT(r.first_name, ' ', r.last_name) as resident_name,
                         dt.document_type, dr.date_processed
                         FROM document_qr_codes q
                         JOIN document_requests dr ON dr.id = q.request_id
                         JOIN document_types dt ON dt.id = dr.document_type_id
                         JOIN residents r ON r.id = dr.resident_id
                         WHERE q.qr_code = ? AND dr.status = 'Approved' LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404);
    die("Document not found or not approved");
}

$document = $res->fetch_assoc();
$stmt->close();

// Update verification stats
$mysqli->query("UPDATE document_qr_codes SET 
               verification_attempts = verification_attempts + 1, 
               last_verified_at = NOW() 
               WHERE qr_code = '" . $mysqli->real_escape_string($token) . "'");

// Apply QR watermark and serve directly
$path = $document['document_file_path'];

if (!file_exists($path)) {
    http_response_code(404);
    die("PDF file not found");
}

// Apply QR verification watermark
require_once 'qr_watermark_fixed.php';
$watermarked_path = applyQrWatermarkFixed($path, $document['request_id'], $document);

if ($watermarked_path && file_exists($watermarked_path)) {
    $path = $watermarked_path;
    $is_temp_file = true;
}

// Serve the PDF directly
$size = filesize($path);
header('Content-Type: application/pdf');
header('Content-Length: ' . $size);
header('Content-Disposition: inline; filename="barangay_balas_verified_document.pdf"');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($path);

// Clean up temporary watermarked file
if (isset($is_temp_file) && $is_temp_file && file_exists($watermarked_path)) {
    register_shutdown_function(function() use ($watermarked_path) {
        @unlink($watermarked_path);
    });
}

exit;
?>