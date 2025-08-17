<?php
require '../../includes/db.php';
session_start();


$account_id = $_SESSION['resident_account_id'];

// ✅ Check if resident account exists and is approved
$checkResident = $conn->prepare("
    SELECT ra.id, ra.status, ra.email, r.id AS resident_id, 
           r.first_name, r.middle_name, r.last_name, r.birthdate,
           r.address, r.purok, r.sex, TIMESTAMPDIFF(YEAR, r.birthdate, CURDATE()) as age
    FROM resident_accounts ra
    JOIN residents r ON ra.resident_id = r.id
    WHERE ra.id = ?
    LIMIT 1
");
$checkResident->bind_param("i", $account_id);
$checkResident->execute();
$residentResult = $checkResident->get_result();

if ($residentResult->num_rows === 0) {
    die("<script>
        alert('No portal account found. Please register first.');
        window.location.href = '/balas-2.0/index.php';
    </script>");
}

$resident = $residentResult->fetch_assoc();

// ✅ Must be approved
if ($resident['status'] !== 'approved') {
    die("<script>
        alert('Your Barangay Portal Account must be approved to request documents.');
        window.location.href = '/balas-2.0/index.php';
    </script>");
}

$resident_id = $resident['resident_id'];
$email       = $resident['email']; 
$full_name   = trim($resident['first_name'] . ' ' . $resident['middle_name'] . ' ' . $resident['last_name']);

// ✅ Safely get POST values (only from form)
$document_type = $_POST['document_type'] ?? '';
$purpose       = $_POST['purpose'] ?? '';
$requirements  = $_POST['requirements'] ?? ''; // optional

// ✅ Validate required inputs
if (empty($document_type) || empty($purpose)) {
    die("<script>
        alert('Please fill in all required fields.');
        window.history.back();
    </script>");
}

// ✅ Auto-generate request_number (YEAR + random 5-digit ID)
$request_number = date("Y") . '-' . str_pad(mt_rand(1, 99999), 5, "0", STR_PAD_LEFT);

// ✅ Insert request
$insert = $conn->prepare("
    INSERT INTO document_requests (
        request_number, resident_id, document_type, purpose, requirements, status, date_requested
    ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");
$insert->bind_param(
    "sisss",
    $request_number,
    $resident_id,
    $document_type,
    $purpose,
    $requirements
);

if ($insert->execute()) {
    echo "<script>
        alert('Document requested successfully! Request No: {$request_number}');
        window.location.href = '/balas-2.0/index.php';
    </script>";
} else {
    echo "<script>
        alert('Request failed. Please try again.');
        window.history.back();
    </script>";
}
?>
