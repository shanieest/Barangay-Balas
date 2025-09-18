<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

try {
    handleCreate();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function handleCreate() {
    global $conn, $admin_id;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $resident_name = trim($_POST['resident_name'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $reservation_date_start = $_POST['reservation_date_start'] ?? '';
    $reservation_date_end   = $_POST['reservation_date_end'] ?? '';
    $purpose = trim($_POST['purpose'] ?? '');
    $service_types = $_POST['service_types'] ?? [];
    $resident_id = !empty($_POST['resident_id']) ? intval($_POST['resident_id']) : null;

    $start_time = $_POST['start_time'] ?? null;
    $end_time = $_POST['end_time'] ?? null;
    $setup_time = $_POST['setup_time'] ?? null;
    $duration_type = $_POST['duration_type'] ?? null;
    $event_location = $_POST['event_location'] ?? null;

    if (empty($resident_name)) throw new Exception('Full name is required');
    if (empty($contact_number)) throw new Exception('Contact number is required');
    if (!preg_match('/^[0-9]{11}$/', $contact_number)) throw new Exception('Contact number must be 11 digits');
    if (empty($reservation_date_start)) throw new Exception('Reservation start date is required');
    if (empty($purpose)) throw new Exception('Purpose is required');
    if (empty($service_types) || !is_array($service_types)) throw new Exception('At least one service type is required');

    $start_date = new DateTime($reservation_date_start);
    $today = new DateTime();
    $today->setTime(0, 0, 0);

    if ($start_date <= $today) {
        throw new Exception('Reservation date must be at least one day in advance');
    }

    if (empty($reservation_date_end)) {
        $reservation_date_end = $reservation_date_start; 
    }

    $end_date = new DateTime($reservation_date_end);
    if ($end_date < $start_date) {
        throw new Exception('End date cannot be before start date');
    }

    $duration_days = $start_date->diff($end_date)->days + 1;

    foreach ($service_types as $service_type_id) {
        $service_type_id = intval($service_type_id);
        if ($service_type_id <= 0) {
            throw new Exception('Invalid service type selected');
        }
        $check_sql = "SELECT id FROM service_types WHERE id = ? AND is_active = 1";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $service_type_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows === 0) {
            throw new Exception('Selected service type is not available');
        }
        $check_stmt->close();
    }

    $conn->begin_transaction();

    try {
        $notes = '';
        if ($start_time) $notes .= "Start Time: $start_time\n";
        if ($end_time) $notes .= "End Time: $end_time\n";
        if ($setup_time) $notes .= "Setup Time: $setup_time\n";
        if ($duration_type) $notes .= "Duration Type: $duration_type\n";
        if ($event_location) $notes .= "Event Location: $event_location\n";

        $insert_sql = "INSERT INTO service_reservations 
                       (resident_id, resident_name, contact_number, email,
                        reservation_date_start, reservation_date_end, duration_days,
                        purpose, notes, status, date_requested) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";

        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isssssiss",
            $resident_id, $resident_name, $contact_number, $email,
            $reservation_date_start, $reservation_date_end, $duration_days,
            $purpose, $notes
        );

        if (!$insert_stmt->execute()) {
            throw new Exception('Failed to create reservation');
        }

        $reservation_id = $conn->insert_id;

        $item_sql = "INSERT INTO service_reservation_items (reservation_id, service_type_id) VALUES (?, ?)";
        $item_stmt = $conn->prepare($item_sql);

        foreach ($service_types as $service_type_id) {
            $service_type_id = intval($service_type_id);
            $item_stmt->bind_param("ii", $reservation_id, $service_type_id);
            if (!$item_stmt->execute()) {
                throw new Exception('Failed to add service items to reservation');
            }
        }

        if (isset($admin_id)) {
            logActivity($admin_id, "Created service reservation (ID: $reservation_id)", $conn);
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Reservation submitted successfully! You will receive a confirmation once it has been reviewed.',
            'reservation_id' => $reservation_id
        ]);

        $insert_stmt->close();
        $item_stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}
?>