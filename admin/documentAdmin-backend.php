<?php
require '../includes/db.php';
require '../vendor/autoload.php'; // PHPWord autoload
use PhpOffice\PhpWord\TemplateProcessor;

session_start();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $admin = $_SESSION['admin_name'] ?? 'System';

    // Fetch request data
    $query = $conn->prepare("SELECT dr.*, ra.full_name, ra.email 
                             FROM document_requests dr
                             LEFT JOIN resident_accounts ra ON dr.resident_id = ra.id
                             WHERE dr.id=?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        // Update request to approved
        $update = $conn->prepare("UPDATE document_requests 
                                  SET status='Approved', date_processed=NOW(), processed_by=? 
                                  WHERE id=?");
        $update->bind_param("si", $admin, $id);
        $update->execute();

        // Load PHPWord template
        $templateFile = __DIR__ . "/templates/{$row['document_type']}.docx";
        if (!file_exists($templateFile)) {
            die("Template not found for this document type.");
        }

        $template = new TemplateProcessor($templateFile);

        // Replace placeholders with request data
        $template->setValue('full_name', $row['full_name']);
        $template->setValue('address', $row['address'] ?? '');
        $template->setValue('purok', $row['purok'] ?? '');
        $template->setValue('purpose', $row['purpose']);
        $template->setValue('date_today', date("F j, Y"));
        $template->setValue('civil_status', $row['civil_status'] ?? '');
        $template->setValue('sex', $row['sex'] ?? '');
        $template->setValue('birthdate', $row['birthdate'] ?? '');
        $template->setValue('age', $row['age'] ?? '');

        // Output as download
        $fileName = "{$row['document_type']}_{$row['request_number']}.docx";
        header("Content-Description: File Transfer");
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $template->saveAs("php://output");
        exit;
    }
}
