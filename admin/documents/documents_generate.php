<?php
require_once __DIR__ . '/../vendor/autoload.php';
require '../../includes/db.php';

// Get request ID
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

if (!$data) {
    die("Request not found.");
}

// If disapproved → send email with notes, no document download
if ($data['status'] === 'Disapproved') {
    $to = $data['email'];
    $subject = "Document Request Disapproved";
    $message = "Dear " . $data['first_name'] . " " . $data['last_name'] . ",\n\n" .
               "Unfortunately, your request for '" . $data['document_type'] . "' has been disapproved.\n" .
               "Notes from the admin: " . $data['notes'] . "\n\n" .
               "Thank you for your understanding.";
    $headers = "From: no-reply@yourdomain.com";

    if (mail($to, $subject, $message, $headers)) {
        echo "The request was disapproved. A notification email has been sent to the resident.";
    } else {
        echo "The request was disapproved, but the email could not be sent.";
    }
    exit;
}

// If approved → generate document and download
if ($data['status'] === 'Approved') {
    // Template path
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

    $filename = $data['document_type'] . '-' . $data['id'] . '.docx';
    $template->saveAs($filename);

    // Force download
    header("Content-Description: File Transfer");
    header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
    header("Content-Disposition: attachment; filename=" . basename($filename));
    header("Content-Length: " . filesize($filename));
    flush();
    readfile($filename);
    unlink($filename);
    exit;
}

// If still pending
echo "The request is still pending approval.";
exit;
?>
