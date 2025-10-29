<?php
// download_barangayId.php
require 'includes/db.php';
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

$filePath = "../" . $data['digital_id_path'];

if (!file_exists($filePath)) {
    die("File not found on server.");
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