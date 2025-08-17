<?php
require_once __DIR__ . '../vendor/autoload.php';
require '../../includes/db.php';

$request_id = $_GET['request_id'];

// Fetch request info
$stmt = $conn->prepare("
    SELECT dr.*, dt.document_type 
    FROM document_requests dr
    JOIN document_types dt ON dr.document_type = dt.id
    WHERE dr.id = ?
");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Template path (e.g., indigency.docx, clearance.docx, etc.)
$templatePath = __DIR__ . '/../templates/' . $data['document_type'] . '.docx';

if (!file_exists($templatePath)) {
    die("Template not found: " . $templatePath);
}

$template = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

// Replace placeholders
$template->setValue('first_name', $data['first_name']);
$template->setValue('middle_name', $data['middle_name']);
$template->setValue('last_name', $data['last_name']);
$template->setValue('full_name', trim($data['first_name'] . ' ' . $data['middle_name'] . ' ' . $data['last_name']));
$template->setValue('email', $data['email']);
$template->setValue('purpose', $data['purpose']);
$template->setValue('shipping_method', $data['shipping_method']);
$template->setValue('date_requested', date('F j, Y', strtotime($data['date_requested'])));
$template->setValue('status', $data['status']);

// Save and download
$filename = $data['document_type'] . '-' . $data['id'] . '.docx';
$template->saveAs($filename);

header("Content-Disposition: attachment; filename=" . $filename);
readfile($filename);
unlink($filename);
exit;
?>
