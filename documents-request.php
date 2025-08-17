<?php
require 'includes/db.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data safely
    $first_name     = $_POST['first_name'] ?? '';
    $middle_name    = $_POST['middle_name'] ?? '';
    $last_name      = $_POST['last_name'] ?? '';
    $email          = $_POST['email'] ?? '';
    $document_type  = $_POST['document_type'] ?? '';
    $purpose        = $_POST['purpose'] ?? '';
    $shipping_method = $_POST['shipping_method'] ?? '';

    // ✅ Get document type ID
    $docTypeQuery = $conn->prepare("SELECT id FROM document_types WHERE document_type = ?");
    $docTypeQuery->bind_param("s", $document_type);
    $docTypeQuery->execute();
    $docTypeResult = $docTypeQuery->get_result();
    $docTypeRow = $docTypeResult->fetch_assoc();
    $docTypeQuery->close();

    if (!$docTypeRow) {
        die("Invalid document type selected!");
    }

    $doc_type_id = $docTypeRow['id'];

    // ✅ Insert request
    $stmt = $conn->prepare("INSERT INTO document_requests 
        (first_name, middle_name, last_name, email, document_type, purpose, shipping_method, status, date_requested) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");

    // fixed: all strings except document_type_id (int)
    $stmt->bind_param("ssssiss", 
        $first_name, 
        $middle_name, 
        $last_name, 
        $email, 
        $doc_type_id, 
        $purpose, 
        $shipping_method
    );

    if ($stmt->execute()) {
        $success_message = "Document request submitted successfully!";
        header("Location: documents.php?success=1");
        exit();
    } else {
        die("Error submitting request: " . $stmt->error);
    }
}
?>
