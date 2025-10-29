<?php
// reject_barangay_id.php
require 'includes/db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'] ?? null;
    $reject_reason = $_POST['reject_reason'] ?? '';

    if (!$application_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid application ID.']);
        exit;
    }

    if (empty($reject_reason)) {
        echo json_encode(['success' => false, 'message' => 'Please provide a rejection reason.']);
        exit;
    }

    // Update status to rejected and store rejection reason
    $stmt = $conn->prepare("UPDATE barangay_id_applications SET status = 'Rejected', rejection_reason = ? WHERE id = ?");
    $stmt->bind_param("si", $reject_reason, $application_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Application rejected successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error rejecting application: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
exit;
?>