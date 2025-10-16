<?php
require '../../config/db.php';
session_start();

$token = $_GET['token'] ?? '';
$is_qr_verified = isset($_GET['qr_verified']) && $_GET['qr_verified'] === '1';

if (!$token || empty($_SESSION['viewer_tokens'][$token])) {
    http_response_code(403); 
    echo "Forbidden"; 
    exit;
}

//$data = $_SESSION['viewer_tokens'][$token];
//if ($data['expires'] < time()) { 
  //  unset($_SESSION['viewer_tokens'][$token]); 
    //http_response_code(403); 
    //echo "Expired"; 
    //exit; 
//}

$request_id = intval($data['request_id']);

// Get document info for watermark
$stmt = $mysqli->prepare("SELECT dr.document_file_path, dt.document_type, 
                         CONCAT(r.first_name, ' ', r.last_name) as resident_name,
                         dr.date_processed
                         FROM document_requests dr
                         JOIN document_types dt ON dt.id = dr.document_type_id
                         JOIN residents r ON r.id = dr.resident_id
                         WHERE dr.id = ?");
$stmt->bind_param('i', $request_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row || !file_exists($row['document_file_path'])) { 
    http_response_code(404); 
    echo "Not found"; 
    exit; 
}

$path = $row['document_file_path'];

// Apply QR verification watermark if scanned via QR
if ($is_qr_verified) {
    require_once 'qr_watermark_fixed.php';
    $watermarked_path = applyQrWatermarkFixed($path, $request_id, $row);
    if ($watermarked_path && file_exists($watermarked_path)) {
        $path = $watermarked_path;
        $is_temp_file = true;
    }
}

// Serve the PDF
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