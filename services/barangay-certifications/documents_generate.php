<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require '../../includes/db.php';

$request_id = $_GET['request_id'];

$stmt = $conn->prepare("
    SELECT dr.*, r.first_name, r.middle_name, r.last_name, r.address, r.purok, r.civil_status, r.sex, TIMESTAMPDIFF(YEAR, r.dob, CURDATE()) as age
    FROM document_requests dr
    JOIN residents r ON dr.resident_id = r.id
    WHERE dr.id = ?
");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Load template
$templatePath = __DIR__ . '/../templates/' . $data['document_type'] . '.docx';

if (!file_exists($templatePath)) {
    die("Template not found: " . $templatePath);
}

$template = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

// Replace placeholders
$full_name = trim($data['first_name'] . " " . $data['middle_name'] . " " . $data['last_name']);
$template->setValue('full_name', $full_name);
$template->setValue('address', $data['address']);
$template->setValue('purok', $data['purok']);
$template->setValue('age', $data['age']);
$template->setValue('sex', $data['sex']);
$template->setValue('purpose', $data['purpose']);
$template->setValue('civil_status', $data['civil_status']);
$template->setValue('request_date', date('F j, Y', strtotime($data['date_requested'])));
$template->setValue('request_number', $data['request_number']); // ✅ new

// Save and download
$filename = $data['document_type'] . '-' . $data['request_number'] . '.docx';
$template->saveAs($filename);

header("Content-Disposition: attachment; filename=" . $filename);
readfile($filename);
unlink($filename); // delete after download
exit;
?>
