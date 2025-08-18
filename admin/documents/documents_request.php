<?php
require '../../includes/db.php';

// Collect inputs safely
$first_name     = $_POST['first_name'] ?? '';
$middle_name    = $_POST['middle_name'] ?? '';
$last_name      = $_POST['last_name'] ?? '';
$email          = $_POST['email'] ?? '';
$houseno        = $_POST['houseno'] ?? '';   
$purok          = $_POST['purok'] ?? '';
$civil_status   = $_POST['civil_status'] ?? '';
$sex            = $_POST['sex'] ?? '';
$birthdate      = $_POST['birthdate'] ?? '';
$age            = $_POST['age'] ?? 0;
$purpose        = $_POST['purpose'] ?? '';
$shipping_method= $_POST['shipping_method'] ?? '';
$resident_id    = $_POST['resident_id'] ?? null; // assuming you pass the logged-in resident’s id

// Handle document type (dropdown or "Other")
// If "Other" is chosen, insert a new record into document_types table
if (($_POST['document_type'] ?? '') === 'Other') {
    $other_document = trim($_POST['other_document'] ?? '');
    if ($other_document !== '') {
        $stmt = $conn->prepare("INSERT INTO document_types (document_type) VALUES (?)");
        $stmt->bind_param("s", $other_document);
        $stmt->execute();
        $document_type_id = $stmt->insert_id; // new ID from document_types
        $stmt->close();
    } else {
        $document_type_id = null;
    }
} else {
    $document_type_id = $_POST['document_type'] ?? null; // should be the id from dropdown
}

$date_requested = date("Y-m-d H:i:s");

// Insert request
$insert = $conn->prepare("
    INSERT INTO document_requests (
        resident_id, first_name, middle_name, last_name, email,
        houseno, purok, civil_status, sex, birthdate, age,
        document_type_id, purpose, shipping_method,
        status, date_requested
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)
");

$insert->bind_param(
    "isssssssssissss",
    $resident_id,
    $first_name,
    $middle_name,
    $last_name,
    $email,
    $houseno,
    $purok,
    $civil_status,
    $sex,
    $birthdate,
    $age,
    $document_type_id,
    $purpose,
    $shipping_method,
    $date_requested
);

if ($insert->execute()) {
    echo "<script>
        alert('Document requested successfully! Please wait for admin approval.');
        window.location.href = '/balas-2.0/dashboard.php';
    </script>";
} else {
    echo "<script>alert('Request failed. Please try again.');</script>";
}
?>
