<?php
require '../../includes/db.php';

// Collect inputs safely (avoid undefined index warnings)
$first_name     = $_POST['first_name'] ?? '';
$middle_name    = $_POST['middle_name'] ?? '';
$last_name      = $_POST['last_name'] ?? '';
$email          = $_POST['email'] ?? '';
$houseno        = $_POST['houseno'] ?? '';   // ✅ Now guaranteed from your modal
$purok          = $_POST['purok'] ?? '';
$civil_status   = $_POST['civil_status'] ?? '';
$sex            = $_POST['sex'] ?? '';
$birthdate      = $_POST['birthdate'] ?? '';
$age            = $_POST['age'] ?? 0;
$purpose        = $_POST['purpose'] ?? '';
$shipping_method= $_POST['shipping_method'] ?? '';

// Handle document type (dropdown or "Other")
$document_type  = ($_POST['document_type'] ?? '') === 'Other' 
    ? ($_POST['other_document'] ?? '') 
    : ($_POST['document_type'] ?? '');

$date_requested = date("Y-m-d H:i:s");

// Insert request
$insert = $conn->prepare("
    INSERT INTO document_requests (
        first_name, middle_name, last_name, email,
        houseno, purok, civil_status, sex, birthdate, age,
        document_type, purpose, shipping_method,
        status, date_requested
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)
");

$insert->bind_param(
    "sssssssssissss",
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
    $document_type,
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
