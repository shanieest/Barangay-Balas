<?php
require_once '../../config/db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'] ?? null;
    $type = $_POST['type'] ?? null;

    if (!$requestId || !$type) {
        echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
        exit;
    }

    if ($type === 'document') {
        $stmt = $conn->prepare("UPDATE document_requests SET status = 'Cancelled' WHERE id = ? AND resident_id = ?");
        $stmt->bind_param("ii", $requestId, $_SESSION['user_id']);
    } elseif ($type === 'service') {
        $stmt = $conn->prepare("UPDATE service_reservations SET status = 'Cancelled' WHERE id = ? AND resident_id = ?");
        $stmt->bind_param("ii", $requestId, $_SESSION['user_id']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid type.']);
        exit;
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
$conn->close();
?>