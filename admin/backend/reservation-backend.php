<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAuth();

require_once  '../../config/emailer.php';
require_once '../../email_templates/reservation_status.php';

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
        case 'get_calendar_data':
            handleGetCalendarData();
            break;
        case 'generate_service_report':
            handleGenerateServiceReport();
            break;
        case 'export_service_report':
            handleExportServiceReport();
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
               CONCAT(
                   DATE_FORMAT(sr.reservation_date_start, '%M %e, %Y'),
                   CASE WHEN sr.reservation_date_end != sr.reservation_date_start 
                        THEN CONCAT(' to ', DATE_FORMAT(sr.reservation_date_end, '%M %e, %Y'))
                        ELSE '' 
                   END
               ) as reservation_date,
               CONCAT(sr.duration_days, ' day', CASE WHEN sr.duration_days > 1 THEN 's' ELSE '' END) as duration,
               au.first_name as processed_by_name,
               DATE_FORMAT(sr.date_requested, '%M %e, %Y, %h:%i%p') as formatted_date_requested
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
            'Disapproved' => '<span class="badge bg-danger">' . $row['status'] . '</span>',
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
            'date_requested' => $row['formatted_date_requested'],
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
                       date_processed = NOW(),
                       processed_by = ?
                   WHERE id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $notes, $admin_id, $reservation_id);
    
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
        throw new Exception('Disapproval reason is required');
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
        throw new Exception('Only pending reservations can be disapproved');
    }
    
    // Update reservation status
    $update_sql = "UPDATE service_reservations 
                   SET status = 'Disapproved', 
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
    logActivity($admin_id, "Disapproved service reservation (ID: $reservation_id)", $conn);
    
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
        $emailData = reservationStatusEmail($resData['resident_name'], 'Disapproved', $resData['service_list'], $rejection_reason);
        sendEmail($resData['email'], $emailData['subject'], $emailData['message']);
    }

    
    echo json_encode([
        'success' => true, 
        'message' => 'Service reservation disapproved successfully'
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
    
    if ($current_status === 'Pending' || $current_status === 'Disapproved') {
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

function calculateReservationDuration($start_date, $end_date) {
    // Calculate duration without restrictions
    $end_date_obj = !empty($end_date) ? new DateTime($end_date) : $start_date;
    $duration_days = $start_date->diff($end_date_obj)->days + 1;
    
    return $duration_days;
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
    
    // Validate reservation date is not in the past
    $start_date = new DateTime($reservation_date_start);
    $today = new DateTime();
    
    if ($start_date < $today->setTime(0, 0, 0)) {
        throw new Exception('Reservation date cannot be in the past');
    }
    
    // Calculate duration without restrictions
    $duration_days = calculateReservationDuration($start_date, $reservation_date_end);
    
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
    
    // Validate reservation date is not in the past
    $start_date = new DateTime($reservation_date_start);
    $today = new DateTime();
    
    if ($start_date < $today->setTime(0, 0, 0)) {
        throw new Exception('Reservation date cannot be in the past');
    }
    
    // Calculate duration without restrictions
    $duration_days = calculateReservationDuration($start_date, $reservation_date_end);
    
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
    
    // Only allow deletion of pending or disapproved reservations
    if (!in_array($reservation['status'], ['Pending', 'Disapproved'])) {
        throw new Exception('Cannot delete reservations that are not pending or disapproved');
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
               CONCAT(
                   DATE_FORMAT(sr.reservation_date_start, '%M %e, %Y'),
                   CASE WHEN sr.reservation_date_end != sr.reservation_date_start 
                        THEN CONCAT(' to ', DATE_FORMAT(sr.reservation_date_end, '%M %e, %Y'))
                        ELSE '' 
                   END
               ) as reservation_date,
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

function handleGenerateServiceReport() {
    global $conn;
    
    $report_type = $_GET['report_type'] ?? 'monthly'; // monthly or yearly
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    
    if ($report_type === 'yearly') {
        $start_date = "$year-01-01";
        $end_date = "$year-12-31";
        $date_format = "%Y-%m";
        $group_by = "MONTH(sr.date_requested)";
        $period_format = "CONCAT(YEAR(sr.date_requested), '-', LPAD(MONTH(sr.date_requested), 2, '0'))";
    } else {
        $start_date = "$year-$month-01";
        $end_date = "$year-$month-" . date('t', strtotime($start_date));
        $date_format = "%Y-%m-%d";
        $group_by = "DATE(sr.date_requested)";
        $period_format = "DATE(sr.date_requested)";
    }
    
    // Summary statistics
    $summary_sql = "SELECT 
                    COUNT(*) as total_reservations,
                    SUM(CASE WHEN sr.status = 'Approved' THEN 1 ELSE 0 END) as approved_reservations,
                    SUM(CASE WHEN sr.status = 'Pending' THEN 1 ELSE 0 END) as pending_reservations,
                    SUM(CASE WHEN sr.status IN ('Cancelled', 'Disapproved') THEN 1 ELSE 0 END) as cancelled_reservations,
                    SUM(CASE WHEN sr.status = 'Completed' THEN 1 ELSE 0 END) as completed_reservations,
                    SUM(CASE WHEN sr.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_reservations
                FROM service_reservations sr
                WHERE sr.date_requested BETWEEN ? AND ?";
    
    $summary_stmt = $conn->prepare($summary_sql);
    $summary_stmt->bind_param("ss", $start_date, $end_date);
    $summary_stmt->execute();
    $summary_result = $summary_stmt->get_result();
    $summary = $summary_result->fetch_assoc();
    
    // Monthly/Yearly breakdown
    $breakdown_sql = "SELECT 
                        $period_format as period,
                        COUNT(*) as total,
                        SUM(CASE WHEN sr.status = 'Approved' THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN sr.status = 'Pending' THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN sr.status IN ('Cancelled', 'Disapproved') THEN 1 ELSE 0 END) as cancelled,
                        SUM(CASE WHEN sr.status = 'Completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN sr.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress
                    FROM service_reservations sr
                    WHERE sr.date_requested BETWEEN ? AND ?
                    GROUP BY $group_by
                    ORDER BY sr.date_requested";
    
    $breakdown_stmt = $conn->prepare($breakdown_sql);
    $breakdown_stmt->bind_param("ss", $start_date, $end_date);
    $breakdown_stmt->execute();
    $breakdown_result = $breakdown_stmt->get_result();
    
    $breakdown = [];
    while ($row = $breakdown_result->fetch_assoc()) {
        $breakdown[] = $row;
    }
    
    // Service type breakdown
    $service_sql = "SELECT 
                        st.service_name,
                        COUNT(*) as total_requests,
                        SUM(sri.quantity) as total_quantity
                    FROM service_reservation_items sri
                    JOIN service_types st ON sri.service_type_id = st.id
                    JOIN service_reservations sr ON sri.reservation_id = sr.id
                    WHERE sr.date_requested BETWEEN ? AND ?
                    GROUP BY st.service_name
                    ORDER BY total_requests DESC";
    
    $service_stmt = $conn->prepare($service_sql);
    $service_stmt->bind_param("ss", $start_date, $end_date);
    $service_stmt->execute();
    $service_result = $service_stmt->get_result();
    
    $service_breakdown = [];
    while ($row = $service_result->fetch_assoc()) {
        $service_breakdown[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'report_type' => $report_type,
        'period' => $report_type === 'yearly' ? $year : date('F Y', strtotime($start_date)),
        'summary' => $summary,
        'breakdown' => $breakdown,
        'service_breakdown' => $service_breakdown,
        'date_range' => [
            'start' => $start_date,
            'end' => $end_date
        ]
    ]);
    
    $summary_stmt->close();
    $breakdown_stmt->close();
    $service_stmt->close();
}

function handleExportServiceReport() {
    global $conn;
    
    $report_type = $_GET['report_type'] ?? 'monthly';
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    $format = $_GET['format'] ?? 'excel';
    
    if ($report_type === 'yearly') {
        $start_date = "$year-01-01";
        $end_date = "$year-12-31";
        $filename = "service_reservations_yearly_$year";
    } else {
        $start_date = "$year-$month-01";
        $end_date = "$year-$month-" . date('t', strtotime($start_date));
        $month_name = date('F', strtotime($start_date));
        $filename = "service_reservations_{$month_name}_{$year}";
    }
    
    // Get detailed data for export
    $sql = "SELECT 
                sr.id,
                CONCAT('SR-', LPAD(sr.id, 3, '0')) as reservation_id,
                sr.resident_name,
                sr.contact_number,
                sr.email,
                sr.purpose,
                sr.status,
                sr.reservation_date_start,
                sr.reservation_date_end,
                sr.duration_days,
                sr.date_requested,
                sr.date_processed,
                GROUP_CONCAT(DISTINCT st.service_name SEPARATOR ', ') as services,
                au.first_name as processed_by,
                sr.notes,
                sr.rejection_reason
            FROM service_reservations sr
            LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
            LEFT JOIN service_types st ON sri.service_type_id = st.id
            LEFT JOIN admin_users au ON sr.processed_by = au.id
            WHERE sr.date_requested BETWEEN ? AND ?
            GROUP BY sr.id
            ORDER BY sr.date_requested DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    if ($format === 'excel') {
        exportToExcel($data, $filename, $report_type, $month, $year);
    } else {
        exportToCSV($data, $filename);
    }
    
    $stmt->close();
}

function exportToExcel($data, $filename, $report_type, $month, $year) {
    // Create PHPExcel object (you might need to include PHPExcel library)
    // For simplicity, we'll create an HTML table that Excel can open
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<table border="1">';
    
    // Report header
    echo '<tr><td colspan="14" style="text-align:center;font-weight:bold;background-color:#f0f0f0;">';
    echo 'SERVICE RESERVATIONS REPORT - ' . strtoupper($report_type) . ' - ';
    echo $report_type === 'yearly' ? $year : date('F Y', strtotime("$year-$month-01"));
    echo '</td></tr>';
    echo '<tr><td colspan="14"></td></tr>';
    
    // Column headers
    echo '<tr style="background-color:#e0e0e0;font-weight:bold;">';
    echo '<th>Reservation ID</th>';
    echo '<th>Resident Name</th>';
    echo '<th>Contact Number</th>';
    echo '<th>Email</th>';
    echo '<th>Purpose</th>';
    echo '<th>Services</th>';
    echo '<th>Status</th>';
    echo '<th>Reservation Start</th>';
    echo '<th>Reservation End</th>';
    echo '<th>Duration (Days)</th>';
    echo '<th>Date Requested</th>';
    echo '<th>Date Processed</th>';
    echo '<th>Processed By</th>';
    echo '<th>Notes</th>';
    echo '</tr>';
    
    // Data rows
    foreach ($data as $row) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['reservation_id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['resident_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['contact_number']) . '</td>';
        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
        echo '<td>' . htmlspecialchars($row['purpose']) . '</td>';
        echo '<td>' . htmlspecialchars($row['services']) . '</td>';
        echo '<td>' . htmlspecialchars($row['status']) . '</td>';
        echo '<td>' . htmlspecialchars($row['reservation_date_start']) . '</td>';
        echo '<td>' . htmlspecialchars($row['reservation_date_end']) . '</td>';
        echo '<td>' . htmlspecialchars($row['duration_days']) . '</td>';
        echo '<td>' . htmlspecialchars($row['date_requested']) . '</td>';
        echo '<td>' . htmlspecialchars($row['date_processed'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['processed_by'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($row['notes'] ?? '') . '</td>';
        echo '</tr>';
    }
    
    // Summary row
    echo '<tr><td colspan="14"></td></tr>';
    echo '<tr style="background-color:#f0f0f0;font-weight:bold;">';
    echo '<td colspan="14">Total Reservations: ' . count($data) . '</td>';
    echo '</tr>';
    
    echo '</table>';
    echo '</body></html>';
    exit;
}

function exportToCSV($data, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fputs($output, "\xEF\xBB\xBF");
    
    // Headers
    fputcsv($output, [
        'Reservation ID',
        'Resident Name',
        'Contact Number',
        'Email',
        'Purpose',
        'Services',
        'Status',
        'Reservation Start Date',
        'Reservation End Date',
        'Duration (Days)',
        'Date Requested',
        'Date Processed',
        'Processed By',
        'Notes'
    ]);
    
    // Data
    foreach ($data as $row) {
        fputcsv($output, [
            $row['reservation_id'],
            $row['resident_name'],
            $row['contact_number'],
            $row['email'],
            $row['purpose'],
            $row['services'],
            $row['status'],
            $row['reservation_date_start'],
            $row['reservation_date_end'],
            $row['duration_days'],
            $row['date_requested'],
            $row['date_processed'] ?? 'N/A',
            $row['processed_by'] ?? 'N/A',
            $row['notes'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
}
function handleGetCalendarData() {
    global $conn;
    
    $year = $_GET['year'] ?? date('Y');
    $month = $_GET['month'] ?? date('m');
    
    $start_date = "$year-$month-01";
    $end_date = "$year-$month-" . date('t', strtotime($start_date));
    
    // Get reservations with service details - FIXED QUERY
    $sql = "SELECT 
                sr.reservation_date_start,
                COALESCE(sr.reservation_date_end, sr.reservation_date_start) as reservation_date_end,
                st.service_name,
                st.id as service_id,
                sri.quantity,
                sr.status
            FROM service_reservations sr
            JOIN service_reservation_items sri ON sr.id = sri.reservation_id
            JOIN service_types st ON sri.service_type_id = st.id
            WHERE (
                (sr.reservation_date_start BETWEEN ? AND ?) 
                OR (COALESCE(sr.reservation_date_end, sr.reservation_date_start) BETWEEN ? AND ?)
                OR (? BETWEEN sr.reservation_date_start AND COALESCE(sr.reservation_date_end, sr.reservation_date_start))
            )
            AND sr.status IN ('Approved', 'In Progress')"; 
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $start_date, $end_date, $start_date, $end_date, $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    
    // Get all available services
    $services_sql = "SELECT id, service_name, description FROM service_types WHERE is_active = 1";
    $services_result = $conn->query($services_sql);
    $services = [];
    while ($row = $services_result->fetch_assoc()) {
        $services[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'reservations' => $reservations,
            'services' => $services
        ]
    ]);
    
    $stmt->close();
}

$conn->close();
?>