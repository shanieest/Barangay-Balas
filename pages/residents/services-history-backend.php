<?php
require_once '../../config/db.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'] ?? null;
    $type = $_POST['type'] ?? null;
    $userId = $_SESSION['user_id'] ?? null;

    if (!$requestId || !$type || !$userId) {
        echo json_encode(['success' => false, 'message' => 'Invalid request data or user not logged in.']);
        exit;
    }

    $conn->begin_transaction();

    try {
        if ($type === 'document') {
            $checkStmt = $conn->prepare("SELECT id, document_type_id FROM document_requests WHERE id = ? AND resident_id = ? AND status = 'Pending'");
            $checkStmt->bind_param("ii", $requestId, $userId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Document request not found or cannot be cancelled.');
            }
            
            $requestData = $result->fetch_assoc();
            $checkStmt->close();

            $docTypeStmt = $conn->prepare("SELECT document_type FROM document_types WHERE id = ?");
            $docTypeStmt->bind_param("i", $requestData['document_type_id']);
            $docTypeStmt->execute();
            $docTypeResult = $docTypeStmt->get_result();
            $docTypeName = $docTypeResult->fetch_assoc()['document_type'] ?? 'Unknown';
            $docTypeStmt->close();

            $stmt = $conn->prepare("UPDATE document_requests SET status = 'Cancelled' WHERE id = ? AND resident_id = ?");
            $stmt->bind_param("ii", $requestId, $userId);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to cancel document request.');
            }
            $stmt->close();

            $activity = "Cancelled $docTypeName document request #" . $requestId;
            $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity, ip_address, user_agent, timestamp) VALUES (?, ?, ?, ?, NOW())");
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $logStmt->bind_param("isss", $userId, $activity, $ipAddress, $userAgent);
            $logStmt->execute();
            $logStmt->close();

        } elseif ($type === 'service') {
            $userStmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as full_name FROM residents WHERE id = ?");
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            
            if ($userResult->num_rows === 0) {
                throw new Exception('User not found.');
            }
            
            $userData = $userResult->fetch_assoc();
            $fullName = $userData['full_name'];
            $userStmt->close();

            $checkStmt = $conn->prepare("
                SELECT sr.id, sr.purpose, 
                       GROUP_CONCAT(DISTINCT st.service_name ORDER BY st.service_name SEPARATOR ', ') AS services
                FROM service_reservations sr
                LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
                LEFT JOIN service_types st ON sri.service_type_id = st.id
                WHERE sr.id = ? 
                  AND (sr.resident_id = ? OR (sr.resident_id IS NULL AND sr.resident_name = ?)) 
                  AND sr.status = 'Pending'
                GROUP BY sr.id, sr.purpose
            ");
            $checkStmt->bind_param("iis", $requestId, $userId, $fullName);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Service reservation not found or cannot be cancelled.');
            }
            
            $reservationData = $result->fetch_assoc();
            $checkStmt->close();

            $stmt = $conn->prepare("UPDATE service_reservations SET status = 'Cancelled' WHERE id = ? AND (resident_id = ? OR (resident_id IS NULL AND resident_name = ?))");
            $stmt->bind_param("iis", $requestId, $userId, $fullName);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to cancel service reservation.');
            }
            $stmt->close();

            $services = $reservationData['services'] ?? 'No services';
            $purpose = $reservationData['purpose'] ?? 'No purpose specified';
            $activity = "Cancelled service reservation #$requestId (Services: $services, Purpose: $purpose)";
            
            $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity, ip_address, user_agent, timestamp) VALUES (?, ?, ?, ?, NOW())");
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $logStmt->bind_param("isss", $userId, $activity, $ipAddress, $userAgent);
            $logStmt->execute();
            $logStmt->close();

        } else {
            throw new Exception('Invalid request type.');
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Request cancelled successfully.']);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? null;
    $reservationId = $_GET['reservation_id'] ?? null;
    $userId = $_SESSION['user_id'] ?? null;

    if ($action === 'view_reservation' && $reservationId && $userId) {
        try {
            $userStmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as full_name FROM residents WHERE id = ?");
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $userData = $userResult->fetch_assoc();
            $fullName = $userData['full_name'] ?? '';
            $userStmt->close();

            $resQuery = "
                SELECT sr.*, 
                       GROUP_CONCAT(
                           DISTINCT CONCAT(st.service_name, ' (Qty: ', sri.quantity, ')')
                           ORDER BY st.service_name SEPARATOR '<br>'
                       ) AS service_details,
                       GROUP_CONCAT(DISTINCT st.service_name ORDER BY st.service_name SEPARATOR ', ') AS services
                FROM service_reservations sr
                LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
                LEFT JOIN service_types st ON sri.service_type_id = st.id
                WHERE sr.id = ? AND (sr.resident_id = ? OR (sr.resident_id IS NULL AND sr.resident_name = ?))
                GROUP BY sr.id
            ";
            
            $stmt = $conn->prepare($resQuery);
            $stmt->bind_param("iis", $reservationId, $userId, $fullName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $reservation = $result->fetch_assoc();
                echo json_encode(['success' => true, 'data' => $reservation]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Reservation not found.']);
            }
            $stmt->close();

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>