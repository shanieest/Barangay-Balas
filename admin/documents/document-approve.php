<?php
require 'includes/db.php';

if (!isset($_GET['id'], $_GET['action'])) {
    die("Invalid request");
}

$id = intval($_GET['id']);
$action = $_GET['action'];
$notes = isset($_GET['notes']) ? trim($_GET['notes']) : null;

$status = '';
if ($action === 'approve') {
    $status = 'Approved';
    $dateProcessed = date("Y-m-d H:i:s");
} elseif ($action === 'disapprove') {
    $status = 'Disapproved';
    $dateProcessed = date("Y-m-d H:i:s");
} else {
    die("Invalid action");
}

$sql = "UPDATE document_requests 
        SET status = ?, notes = ?, date_processed = ? 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $status, $notes, $dateProcessed, $id);

if ($stmt->execute()) {
    header("Location: documentAdmin.php?msg=success");
} else {
    header("Location: documentAdmin.php?msg=error");
}
