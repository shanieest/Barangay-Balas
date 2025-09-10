<?php
require '../config/db.php';

if (!isset($_GET['id'])) die('Invalid request');

$request_id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT document_file_path FROM document_requests WHERE id = ?");
$stmt->bind_param('i', $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

if (!$request || empty($request['document_file_path'])) die('File not found');

$file = __DIR__ . '/../public/' . $request['document_file_path'];
if (!file_exists($file)) die('File not found');

header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.basename($file).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
