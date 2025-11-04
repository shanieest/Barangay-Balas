<?php
require '../includes/db.php';
session_start();

$application_id = $_GET['id'] ?? null;
if (!$application_id) {
    die("Invalid request.");
}

// Fetch the digital ID path
$query = "
SELECT a.digital_id_path, r.first_name, r.last_name, a.id_number
FROM barangay_id_applications a
JOIN residents r ON a.resident_id = r.id
WHERE a.id = ? AND a.status = 'Approved'
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Digital ID not found or not approved.");
}

$data = $result->fetch_assoc();
$stmt->close();

// file path construction
$filePath = "../../" . $data['digital_id_path']; 

error_log("Download attempt - DB path: " . $data['digital_id_path']);
error_log("Full file path: " . $filePath);
error_log("File exists: " . (file_exists($filePath) ? 'YES' : 'NO'));

if (!file_exists($filePath)) {
    // Try alternative path
    $altPath = "../" . $data['digital_id_path'];
    error_log("Trying alternative path: " . $altPath);
    
    if (file_exists($altPath)) {
        $filePath = $altPath;
    } else {
        error_log("File not found at either location");
        die("File not found on server. Please contact administrator.");
    }
}

// Generate filename
$filename = 'Barangay_ID_' . ($data['id_number'] ?? $data['first_name'] . '_' . $data['last_name']) . '.pdf';
$filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);

// Set headers for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Output file
readfile($filePath);
exit;
?>