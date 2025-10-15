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

    // Get resident data from hidden inputs (automatically filled if logged in)
    $resident_id = !empty($_POST['resident_id']) ? intval($_POST['resident_id']) : null;
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    
    // Validate that user is logged in
    if (!$resident_id || empty($first_name) || empty($last_name)) {
        throw new Exception('You must be logged in to make a reservation.');
    }
    
    // Verify that the resident exists and is active
    $resident_check_sql = "SELECT id, first_name, last_name FROM residents WHERE id = ? AND resident_status = 'Active'";
    $resident_check_stmt = $conn->prepare($resident_check_sql);
    $resident_check_stmt->bind_param("i", $resident_id);
    $resident_check_stmt->execute();
    $resident_check_result = $resident_check_stmt->get_result();
    
    if ($resident_check_result->num_rows === 0) {
        throw new Exception('Invalid resident account or account is not active.');
    }
    
    $resident_info = $resident_check_result->fetch_assoc();
    $resident_check_stmt->close();
    
    // Verify the names match (security check)
    if (trim($resident_info['first_name']) !== $first_name || trim($resident_info['last_name']) !== $last_name) {
        throw new Exception('Resident information mismatch. Please log out and log back in.');
    }

    // Get other form data
    $contact_number = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $reservation_date_start = $_POST['reservation_date_start'] ?? '';
    $reservation_date_end = $_POST['reservation_date_end'] ?? '';
    $purpose = trim($_POST['purpose'] ?? '');
    $service_types = $_POST['service_types'] ?? [];

    // Additional fields
    $start_time = $_POST['start_time'] ?? null;
    $end_time = $_POST['end_time'] ?? null;
    $setup_time = $_POST['setup_time'] ?? null;
    $duration_type = $_POST['duration_type'] ?? null;
    $event_location = $_POST['event_location'] ?? null;
    $notes = $_POST['notes'] ?? '';

    // Get quantity values
    $tent_qty = intval($_POST['tent_qty'] ?? 1);
    $tables_chairs_qty = intval($_POST['tables_chairs_qty'] ?? 1);
    $sound_system_qty = intval($_POST['sound_system_qty'] ?? 1);
    $vehicle_qty = intval($_POST['vehicle_qty'] ?? 1);

    // Validation
    if (empty($contact_number)) throw new Exception('Contact number is required');
    if (!preg_match('/^[0-9]{11}$/', $contact_number)) throw new Exception('Contact number must be 11 digits');
    if (empty($reservation_date_start)) throw new Exception('Reservation start date is required');
    if (empty($purpose)) throw new Exception('Purpose is required');
    if (empty($service_types) || !is_array($service_types)) throw new Exception('At least one service type is required');

    // Date validation
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

    // NEW VALIDATION: Check if duration exceeds 10 days
    if ($duration_days > 10) {
        throw new Exception('Reservation duration cannot exceed 10 days. Please adjust your dates.');
    }

    // Validate service types and quantities
    foreach ($service_types as $service_type_id) {
        $service_type_id = intval($service_type_id);
        if ($service_type_id <= 0) {
            throw new Exception('Invalid service type selected');
        }
        
        // Check if service type exists and is active
        $check_sql = "SELECT id FROM service_types WHERE id = ? AND is_active = 1";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $service_type_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows === 0) {
            throw new Exception('Selected service type is not available');
        }
        $check_stmt->close();
        
        // Validate quantities for specific service types
        switch ($service_type_id) {
            case 1: // Tent
                if ($tent_qty < 1 || $tent_qty > 10) {
                    throw new Exception('Tent quantity must be between 1 and 10');
                }
                break;
            case 2: // Vehicle
                if ($vehicle_qty < 1 || $vehicle_qty > 3) {
                    throw new Exception('Vehicle quantity must be between 1 and 3');
                }
                break;
            case 3: // Sound System
                if ($sound_system_qty < 1 || $sound_system_qty > 5) {
                    throw new Exception('Sound System quantity must be between 1 and 5');
                }
                break;
            case 4: // Tables and Chairs
                if ($tables_chairs_qty < 1 || $tables_chairs_qty > 20) {
                    throw new Exception('Tables and Chairs quantity must be between 1 and 20 sets');
                }
                break;
        }
    }

    $conn->begin_transaction();

    try {
        // Create resident name from verified data
        $resident_name = $first_name . ' ' . $last_name;
        
        // Build notes with additional information
        $additional_notes = '';
        if ($start_time) $additional_notes .= "Start Time: $start_time\n";
        if ($end_time) $additional_notes .= "End Time: $end_time\n";
        if ($setup_time) $additional_notes .= "Setup Time: $setup_time\n";
        if ($duration_type) $additional_notes .= "Duration Type: $duration_type\n";
        if ($event_location) $additional_notes .= "Event Location: $event_location\n";
        if ($notes) $additional_notes .= $notes . "\n";

        // Insert reservation
        $insert_sql = "INSERT INTO service_reservations 
                       (resident_id, resident_name, contact_number, email,
                        reservation_date_start, reservation_date_end, duration_days,
                        purpose, notes, status, date_requested) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";

        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isssssiss",
            $resident_id, $resident_name, $contact_number, $email,
            $reservation_date_start, $reservation_date_end, $duration_days,
            $purpose, $additional_notes
        );

        if (!$insert_stmt->execute()) {
            throw new Exception('Failed to create reservation');
        }

        $reservation_id = $conn->insert_id;

        // Insert service items with quantities
        $item_sql = "INSERT INTO service_reservation_items (reservation_id, service_type_id, quantity) VALUES (?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);

        foreach ($service_types as $service_type_id) {
            $service_type_id = intval($service_type_id);
            
            // Determine quantity based on service type
            $quantity = 1; // default
            switch ($service_type_id) {
                case 1: // Tent
                    $quantity = $tent_qty;
                    break;
                case 2: // Vehicle
                    $quantity = $vehicle_qty;
                    break;
                case 3: // Sound System
                    $quantity = $sound_system_qty;
                    break;
                case 4: // Tables and Chairs
                    $quantity = $tables_chairs_qty;
                    break;
            }
            
            $item_stmt->bind_param("iii", $reservation_id, $service_type_id, $quantity);
            if (!$item_stmt->execute()) {
                throw new Exception('Failed to add service items to reservation');
            }
        }

        // Log activity if admin is logged in
        if (isset($admin_id)) {
            logActivity($admin_id, "Created service reservation for resident: $resident_name (ID: $reservation_id)", $conn);
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Reservation submitted successfully! You will receive a confirmation once it has been reviewed by the barangay office.',
            'reservation_id' => $reservation_id
        ]);

        $insert_stmt->close();
        $item_stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

function logActivity($admin_id, $activity, $conn) {
    $log_sql = "INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("is", $admin_id, $activity);
    $log_stmt->execute();
    $log_stmt->close();
}
?>