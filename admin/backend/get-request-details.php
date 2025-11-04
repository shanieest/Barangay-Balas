<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Request ID is required']);
    exit;
}

$request_id = intval($_GET['id']);

$debug_stmt = $conn->prepare("SELECT id, status, processed_by, date_processed FROM document_requests WHERE id = ?");
$debug_stmt->bind_param('i', $request_id);
$debug_stmt->execute();
$debug_result = $debug_stmt->get_result();
$debug_data = $debug_result->fetch_assoc();

error_log("DEBUG - Request $request_id: status=" . $debug_data['status'] . ", processed_by=" . $debug_data['processed_by'] . ", date_processed=" . $debug_data['date_processed']);

$stmt = $conn->prepare("
    SELECT dr.*, dt.document_type, 
           a.id as admin_id,
           a.first_name as admin_first_name,
           a.last_name as admin_last_name,
           a.username as admin_username,
           CONCAT(a.first_name, ' ', a.last_name) AS processed_by_name, 
           a.username AS processed_by_username,
           ra.email AS resident_email, 
           ra.account_status,
           r.contact_number
    FROM document_requests dr
    LEFT JOIN document_types dt ON dr.document_type_id = dt.id
    LEFT JOIN admin_users a ON dr.processed_by = a.id
    LEFT JOIN residents r ON dr.resident_id = r.id
    LEFT JOIN resident_accounts ra ON r.id = ra.resident_id
    WHERE dr.id = ?
");

$stmt->bind_param('i', $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Request not found']);
    exit;
}

$request = $result->fetch_assoc();

error_log("DEBUG - Admin join result: admin_id=" . $request['admin_id'] . ", admin_first_name=" . $request['admin_first_name'] . ", processed_by_name=" . $request['processed_by_name']);

$stmt->close();

$processed_by_text = 'Not processed yet';
if ($request['processed_by']) {
    if ($request['processed_by_name'] && $request['processed_by_username']) {
        $processed_by_text = $request['processed_by_name'] . ' (' . $request['processed_by_username'] . ')';
    } else {
        $admin_fallback = $conn->prepare("SELECT first_name, last_name, username FROM admin_users WHERE id = ?");
        $admin_fallback->bind_param('i', $request['processed_by']);
        $admin_fallback->execute();
        $admin_result = $admin_fallback->get_result();
        if ($admin_data = $admin_result->fetch_assoc()) {
            $processed_by_text = $admin_data['first_name'] . ' ' . $admin_data['last_name'] . ' (' . $admin_data['username'] . ')';
            error_log("DEBUG - Used fallback query for admin: " . $processed_by_text);
        } else {
            $processed_by_text = 'Admin ID: ' . $request['processed_by'] . ' (Admin not found)';
            error_log("DEBUG - Admin not found for ID: " . $request['processed_by']);
        }
        $admin_fallback->close();
    }
}

$response = [
    'id' => $request['id'],
    'document_type' => $request['document_type'] ?: 'Unknown',
    'date_requested' => date('F j, Y, g:i a', strtotime($request['date_requested'])),
    'status' => $request['status'],
    'full_name' => trim($request['first_name'] . ' ' . ($request['middle_name'] ? $request['middle_name'] . ' ' : '') . $request['last_name']),
    'houseno' => $request['houseno'],
    'purok' => $request['purok'],
    'contact_number' => $request['contact_number'] ?: 'N/A',
    'purpose' => $request['purpose'],
    'notes' => $request['notes'],
    'resident_email' => $request['resident_email'] ?: 'N/A',
    'account_status' => $request['account_status'] ?: 'N/A',
    'processed_by' => $processed_by_text,
    'document_path' => $request['document_file_path'] ?? null,
    'debug_processed_by_id' => $request['processed_by'],
    'debug_admin_found' => $request['processed_by_name'] ? true : false
];

error_log("DEBUG - Final processed_by: " . $processed_by_text);

header('Content-Type: application/json');
echo json_encode($response);
?>