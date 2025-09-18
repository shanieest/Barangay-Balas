<?php
require 'includes/db.php'; // Make sure this path is correct

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    die("Error: No document ID specified. Usage: download-document.php?id=1");
}

$request_id = (int) $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT document_file_path FROM document_requests WHERE id = ? AND status = 'Approved'");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if (!$request || empty($request['document_file_path'])) {
        http_response_code(404);
        die("Error: Document not found or not approved yet for ID: " . $request_id);
    }

    $file = basename($request['document_file_path']);
    
    $allowed_extensions = ['pdf', 'docx'];
    $file_extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        http_response_code(400);
        die("Error: Invalid file type in database: " . $file_extension);
    }

    $full_path = __DIR__ . "/../public/" . $request['document_file_path'];
    
    // Debug info
    // echo "Request ID: " . $request_id . "<br>";
    // echo "File path from DB: " . $request['document_file_path'] . "<br>";
    // echo "Full path: " . $full_path . "<br>";
    // echo "File exists: " . (file_exists($full_path) ? 'YES' : 'NO') . "<br>";
    // die(); // Remove this line after debugging

    if (!file_exists($full_path)) {
        http_response_code(404);
        die("Error: File not found at path: " . htmlspecialchars($request['document_file_path']));
    }

    if (!is_readable($full_path)) {
        http_response_code(403);
        die("Error: File is not readable.");
    }

    $file_size = filesize($full_path);
    if ($file_size === false) {
        http_response_code(500);
        die("Error: Could not determine file size.");
    }

    header("Content-Type: application/" . ($file_extension === 'pdf' ? 'pdf' : 'vnd.openxmlformats-officedocument.wordprocessingml.document'));
    header("Content-Disposition: attachment; filename=\"" . $file . "\"");
    header("Content-Length: " . $file_size);
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");

    if (ob_get_level()) {
        ob_end_clean();
    }

    if (!readfile($full_path)) {
        http_response_code(500);
        die("Error: Could not read file.");
    }

} catch (Exception $e) {
    error_log("Download error: " . $e->getMessage());
    http_response_code(500);
    die("Database error: " . $e->getMessage());
}

exit;
?>