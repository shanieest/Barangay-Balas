<?php
// stream_pdf.php
require 'db.php';
session_start();

$token = $_GET['token'] ?? '';
if (!$token || empty($_SESSION['viewer_tokens'][$token])) {
    http_response_code(403); echo "Forbidden"; exit;
}
$data = $_SESSION['viewer_tokens'][$token];
if ($data['expires'] < time()) { unset($_SESSION['viewer_tokens'][$token]); http_response_code(403); echo "Expired"; exit; }
$request_id = intval($data['request_id']);

// fetch file path
$stmt = $mysqli->prepare("SELECT document_file_path FROM document_requests WHERE id = ?");
$stmt->bind_param('i', $request_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();
if (!$row || !file_exists($row['document_file_path'])) { http_response_code(404); echo "Not found"; exit; }

$path = $row['document_file_path'];
$size = filesize($path);
header('Content-Type: application/pdf');
header('Content-Length: ' . $size);
header('Content-Disposition: inline; filename="document.pdf"');
readfile($path);

// optionally remove token for single-use:
unset($_SESSION['viewer_tokens'][$token]);
exit;