<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAuth();

require_once __DIR__ . '/../config/emailer.php';
require_once __DIR__ . '/../email_templates/reservation_status.php';


header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';
$admin_id = $_SESSION['admin_id'];

try {
    switch ($action) {
        case 'get':
            handleGet();
            break;
        case 'approve':
            handleApprove();
            break;
        case 'reject':
            handleReject();
            break;
        case 'update_status':
            handleUpdateStatus();
            break;
        case 'create':
            handleCreate();
            break;
        case 'update':
            handleUpdate();
            break;
        case 'delete':
            handleDelete();
            break;
        case 'list':
            handleList();
            break;
        default:
            throw new Exception('Invalid action specified');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function handleGet() {
    global $conn;
    
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Reservation ID is required');
    }
    
    $reservation_id = intval($_GET['id']);
    
    $sql = "SELECT sr.*, 
               GROUP_CONCAT(st.service_name SEPARATOR ', ') as service_names,
               CONCAT(sr.reservation_date_start, 
                      CASE WHEN sr.reservation_date_end != sr.reservation_date_start 
                           THEN CONCAT(' to ', sr.reservation_date_end) 
                           ELSE '' 
                      END) as reservation_date,
               CONCAT(sr.duration_days, ' day', CASE WHEN sr.duration_days > 1 THEN 's' ELSE '' END) as duration,
               au.first_name as processed_by_name
        FROM service_reservations sr
        LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
        LEFT JOIN service_types st ON sri.service_type_id = st.id
        LEFT JOIN admin_users au ON sr.processed_by = au.id
        WHERE sr.id = ?
        GROUP BY sr.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $service_types = '';
        if ($row['service_names']) {
            $services = explode(', ', $row['service_names']);
            foreach ($services as $service) {
                $badge_class = match($service) {
                    'Tent' => 'bg-primary',
                    'Vehicle' => 'bg-info', 
                    'Sound System' => 'bg-warning',
                    'Tables and Chairs' => 'bg-success',
                    default => 'bg-secondary'
                };
                $service_types .= '<span class="badge ' . $badge_class . ' me-1">' . htmlspecialchars($service) . '</span>';
            }
        }
        
        $status_badge = match($row['status']) {
            'Pending' => '<span class="badge bg-warning">' . $row['status'] . '</span>',
            'Approved' => '<span class="badge bg-success">' . $row['status'] . '</span>',
            'In Progress' => '<span class="badge bg-info">' . $row['status'] . '</span>',
            'Completed' => '<span class="badge bg-secondary">' . $row['status'] . '</span>',
            'Cancelled' => '<span class="badge bg-danger">' . $row['status'] . '</span>',
            'Rejected' => '<span class="badge bg-danger">' . $row['status'] . '</span>',
            default => '<span class="badge bg-light text-dark">' . $row['status'] . '</span>'
        };
        
        $reservation = [
            'id' => $row['id'],
            'resident_name' => $row['resident_name'],
            'service_types' => $service_types,
            'reservation_date' => $row['reservation_date'],
            'duration' => $row['duration'],
            'status_badge' => $status_badge,
            'purpose' => $row['purpose'],
            'contact_number' => $row['contact_number'],
            'email' => $row['email'],
            'date_requested' => date('M d, Y g:i A', strtotime($row['date_requested'])),
            'notes' => $row['notes'],
            'processed_by' => $row['processed_by_name'],
            'rejection_reason' => $row['rejection_reason'],
            'status' => $row['status']
        ];
        
        echo json_encode(['success' => true, 'reservation' => $reservation]);
    } else {
        throw new Exception('Reservation not found');
    }
    
    $stmt->close();
}

function handleApprove() {
    global $conn, $admin_id;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $reservation_id = intval($_POST['reservation_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
   
    
    $conn->begin_transaction();
    
    $check_sql = "SELECT id, status, resident_name FROM service_reservations WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $reservation_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Reservation not found');
    }
    
    $reservation = $check_result->fetch_assoc();
    if ($reservation['status'] !== 'Pending') {
        throw new Exception('Only pending reservations can be approved');
    }
    
    $update_sql = "UPDATE service_reservations 
                   SET status = 'Approved', 
                       notes = ?, 
                       scheduled_datetime = ?,
                       date_processed = NOW(),
                       processed_by = ?
                   WHERE id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssii", $notes, $scheduled_datetime, $admin_id, $reservation_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update reservation status');
    }
    
    logActivity($admin_id, "Approved service reservation (ID: $reservation_id)", $conn);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Service reservation approved successfully'
    ]);

    $emailQuery = $conn->prepare("
        SELECT sr.resident_name, sr.email, GROUP_CONCAT(st.service_name SEPARATOR ', ') as service_list
        FROM service_reservations sr
        LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
        LEFT JOIN service_types st ON sri.service_type_id = st.id
        WHERE sr.id = ?
        GROUP BY sr.id
    ");
    $emailQuery->bind_param("i", $reservation_id);
    $emailQuery->execute();
    $resData = $emailQuery->get_result()->fetch_assoc();

    if ($resData && !empty($resData['email'])) {
        $emailData = reservationStatusEmail($resData['resident_name'], 'Approved', $resData['service_list'], $notes);
        sendEmail($resData['email'], $emailData['subject'], $emailData['message']);
    }

        
        $check_stmt->close();
        $update_stmt->close();
}

function handleReject() {
    global $conn, $admin_id;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $reservation_id = intval($_POST['reservation_id'] ?? 0);
    $rejection_reason = trim($_POST['rejection_reason'] ?? '');
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
    if (empty($rejection_reason)) {
        throw new Exception('Rejection reason is required');
    }
    
    $conn->begin_transaction();
    
    $check_sql = "SELECT id, status FROM service_reservations WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $reservation_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Reservation not found');
    }
    
    $reservation = $check_result->fetch_assoc();
    if ($reservation['status'] !== 'Pending') {
        throw new Exception('Only pending reservations can be rejected');
    }
    
    // Update reservation status
    $update_sql = "UPDATE service_reservations 
                   SET status = 'Rejected', 
                       rejection_reason = ?,
                       date_processed = NOW(),
                       processed_by = ?
                   WHERE id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $rejection_reason, $admin_id, $reservation_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update reservation status');
    }
    
    // Log activity
    logActivity($admin_id, "Rejected service reservation (ID: $reservation_id)", $conn);
    
    $conn->commit();

    $emailQuery = $conn->prepare("
        SELECT sr.resident_name, sr.email, GROUP_CONCAT(st.service_name SEPARATOR ', ') as service_list
        FROM service_reservations sr
        LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
        LEFT JOIN service_types st ON sri.service_type_id = st.id
        WHERE sr.id = ?
        GROUP BY sr.id
    ");
    $emailQuery->bind_param("i", $reservation_id);
    $emailQuery->execute();
    $resData = $emailQuery->get_result()->fetch_assoc();

    if ($resData && !empty($resData['email'])) {
        $emailData = reservationStatusEmail($resData['resident_name'], 'Rejected', $resData['service_list'], $rejection_reason);
        sendEmail($resData['email'], $emailData['subject'], $emailData['message']);
    }

    
    echo json_encode([
        'success' => true, 
        'message' => 'Service reservation rejected successfully'
    ]);
    
    $check_stmt->close();
    $update_stmt->close();
}

function handleUpdateStatus() {
    global $conn, $admin_id;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $reservation_id = intval($_POST['reservation_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
    if (empty($status)) {
        throw new Exception('Status is required');
    }
    
    // Validate status
    $valid_statuses = ['In Progress', 'Completed', 'Cancelled'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception('Invalid status');
    }
    
    $conn->begin_transaction();
    
    // Check if reservation exists
    $check_sql = "SELECT id, status, resident_name FROM service_reservations WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $reservation_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Reservation not found');
    }
    
    $reservation = $check_result->fetch_assoc();
    $current_status = $reservation['status'];
    
    if ($current_status === 'Pending' || $current_status === 'Rejected') {
        throw new Exception('Cannot update status from ' . $current_status . '. Please approve the reservation first.');
    }
    
    // Update reservation status
    $update_sql = "UPDATE service_reservations 
                   SET status = ?, 
                       notes = CASE WHEN ? != '' THEN CONCAT(COALESCE(notes, ''), '\n\n', 'Status Update: ', ?) ELSE notes END,
                       date_processed = NOW(),
                       processed_by = ?
                   WHERE id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssii", $status, $notes, $notes, $admin_id, $reservation_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update reservation status');
    }
    
    // Log activity
    logActivity($admin_id, "Updated service reservation status to '$status' (ID: $reservation_id)", $conn);
    
    $conn->commit();

    $emailQuery = $conn->prepare("
        SELECT sr.resident_name, sr.email, GROUP_CONCAT(st.service_name SEPARATOR ', ') as service_list
        FROM service_reservations sr
        LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
        LEFT JOIN service_types st ON sri.service_type_id = st.id
        WHERE sr.id = ?
        GROUP BY sr.id
    ");
    $emailQuery->bind_param("i", $reservation_id);
    $emailQuery->execute();
    $resData = $emailQuery->get_result()->fetch_assoc();

    if ($resData && !empty($resData['email'])) {
        $emailData = reservationStatusEmail($resData['resident_name'], $status, $resData['service_list'], $notes);
        sendEmail($resData['email'], $emailData['subject'], $emailData['message']);
    }

    
    echo json_encode([
        'success' => true, 
        'message' => 'Service reservation status updated successfully'
    ]);
    
    $check_stmt->close();
    $update_stmt->close();
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
    $reservation_date_end = $_POST['reservation_date_end'] ?? '';
    $purpose = trim($_POST['purpose'] ?? '');
    $service_types = $_POST['service_types'] ?? [];
    $resident_id = !empty($_POST['resident_id']) ? intval($_POST['resident_id']) : null;
    
    // Validation
    if (empty($resident_name)) throw new Exception('Resident name is required');
    if (empty($contact_number)) throw new Exception('Contact number is required');
    if (empty($reservation_date_start)) throw new Exception('Reservation start date is required');
    if (empty($purpose)) throw new Exception('Purpose is required');
    if (empty($service_types)) throw new Exception('At least one service type is required');
    
    // Calculate duration
    $start_date = new DateTime($reservation_date_start);
    $end_date = !empty($reservation_date_end) ? new DateTime($reservation_date_end) : $start_date;
    $duration_days = $start_date->diff($end_date)->days + 1;
    
    $conn->begin_transaction();
    
    // Insert reservation
    $insert_sql = "INSERT INTO service_reservations 
                   (resident_id, resident_name, contact_number, email, reservation_date_start, 
                    reservation_date_end, duration_days, purpose, status, date_requested) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())";
    
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("isssssii", $resident_id, $resident_name, $contact_number, $email, 
                            $reservation_date_start, $reservation_date_end, $duration_days, $purpose);
    
    if (!$insert_stmt->execute()) {
        throw new Exception('Failed to create reservation');
    }
    
    $reservation_id = $conn->insert_id;
    
    // Insert service items
    $item_sql = "INSERT INTO service_reservation_items (reservation_id, service_type_id) VALUES (?, ?)";
    $item_stmt = $conn->prepare($item_sql);
    
    foreach ($service_types as $service_type_id) {
        $item_stmt->bind_param("ii", $reservation_id, $service_type_id);
        $item_stmt->execute();
    }
    
    // Log activity
    logActivity($admin_id, "Created service reservation (ID: $reservation_id)", $conn);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Service reservation created successfully',
        'reservation_id' => $reservation_id
    ]);
    
    $insert_stmt->close();
    $item_stmt->close();
}

function handleUpdate() {
    global $conn, $admin_id;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $reservation_id = intval($_POST['reservation_id'] ?? 0);
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
    $resident_name = trim($_POST['resident_name'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $reservation_date_start = $_POST['reservation_date_start'] ?? '';
    $reservation_date_end = $_POST['reservation_date_end'] ?? '';
    $purpose = trim($_POST['purpose'] ?? '');
    $service_types = $_POST['service_types'] ?? [];
    
    // Validation
    if (empty($resident_name)) throw new Exception('Resident name is required');
    if (empty($contact_number)) throw new Exception('Contact number is required');
    if (empty($reservation_date_start)) throw new Exception('Reservation start date is required');
    if (empty($purpose)) throw new Exception('Purpose is required');
    if (empty($service_types)) throw new Exception('At least one service type is required');
    
    // Calculate duration
    $start_date = new DateTime($reservation_date_start);
    $end_date = !empty($reservation_date_end) ? new DateTime($reservation_date_end) : $start_date;
    $duration_days = $start_date->diff($end_date)->days + 1;
    
    $conn->begin_transaction();
    
    // Check if reservation exists
    $check_sql = "SELECT status FROM service_reservations WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $reservation_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Reservation not found');
    }
    
    // Update reservation
    $update_sql = "UPDATE service_reservations 
                   SET resident_name = ?, contact_number = ?, email = ?, 
                       reservation_date_start = ?, reservation_date_end = ?, 
                       duration_days = ?, purpose = ?, updated_at = NOW()
                   WHERE id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssiis", $resident_name, $contact_number, $email, 
                            $reservation_date_start, $reservation_date_end, $duration_days, $purpose, $reservation_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update reservation');
    }
    
    // Delete existing service items
    $delete_items_sql = "DELETE FROM service_reservation_items WHERE reservation_id = ?";
    $delete_stmt = $conn->prepare($delete_items_sql);
    $delete_stmt->bind_param("i", $reservation_id);
    $delete_stmt->execute();
    
    // Insert new service items
    $item_sql = "INSERT INTO service_reservation_items (reservation_id, service_type_id) VALUES (?, ?)";
    $item_stmt = $conn->prepare($item_sql);
    
    foreach ($service_types as $service_type_id) {
        $item_stmt->bind_param("ii", $reservation_id, $service_type_id);
        $item_stmt->execute();
    }
    
    // Log activity
    logActivity($admin_id, "Updated service reservation (ID: $reservation_id)", $conn);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Service reservation updated successfully'
    ]);
    
    $check_stmt->close();
    $update_stmt->close();
    $delete_stmt->close();
    $item_stmt->close();
}

function handleDelete() {
    global $conn, $admin_id;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $reservation_id = intval($_POST['reservation_id'] ?? 0);
    
    if (!$reservation_id) {
        throw new Exception('Reservation ID is required');
    }
    
    $conn->begin_transaction();
    
    // Check if reservation exists
    $check_sql = "SELECT id, status FROM service_reservations WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $reservation_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Reservation not found');
    }
    
    $reservation = $check_result->fetch_assoc();
    
    // Only allow deletion of pending or rejected reservations
    if (!in_array($reservation['status'], ['Pending', 'Rejected'])) {
        throw new Exception('Cannot delete reservations that are not pending or rejected');
    }
    
    // Delete service items first (foreign key constraint)
    $delete_items_sql = "DELETE FROM service_reservation_items WHERE reservation_id = ?";
    $delete_items_stmt = $conn->prepare($delete_items_sql);
    $delete_items_stmt->bind_param("i", $reservation_id);
    $delete_items_stmt->execute();
    
    // Delete reservation
    $delete_sql = "DELETE FROM service_reservations WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $reservation_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception('Failed to delete reservation');
    }
    
    // Log activity
    logActivity($admin_id, "Deleted service reservation (ID: $reservation_id)", $conn);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Service reservation deleted successfully'
    ]);
    
    $check_stmt->close();
    $delete_items_stmt->close();
    $delete_stmt->close();
}

function handleList() {
    global $conn;
    
    $status = $_GET['status'] ?? 'all';
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    $where_clause = '';
    $params = [];
    $types = '';
    
    if ($status !== 'all') {
        $where_clause = 'WHERE sr.status = ?';
        $params[] = $status;
        $types = 's';
    }
    
    $sql = "SELECT sr.*, 
               GROUP_CONCAT(st.service_name SEPARATOR ', ') as service_names,
               CONCAT(sr.reservation_date_start, 
                      CASE WHEN sr.reservation_date_end != sr.reservation_date_start 
                           THEN CONCAT(' to ', sr.reservation_date_end) 
                           ELSE '' 
                      END) as reservation_date,
               CONCAT(sr.duration_days, ' day', CASE WHEN sr.duration_days > 1 THEN 's' ELSE '' END) as duration
        FROM service_reservations sr
        LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
        LEFT JOIN service_types st ON sri.service_type_id = st.id
        $where_clause
        GROUP BY sr.id
        ORDER BY sr.date_requested DESC
        LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    
    echo json_encode([
        'success' => true, 
        'reservations' => $reservations,
        'count' => count($reservations)
    ]);
    
    $stmt->close();
}

$conn->close();
?>