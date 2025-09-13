<?php
// get-request-details.php
require_once 'includes/db.php'; // Fixed path - should match your actual config path

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Request ID is required']);
    exit;
}

$request_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT dr.*, dt.document_type, 
           r.first_name, r.middle_name, r.last_name, r.contact_number,
           r.house_number, r.purok, r.address, r.civil_status, r.sex, r.birthdate, r.age,
           ra.email AS resident_email, ra.account_status,
           CONCAT(a.first_name, ' ', a.last_name) AS processed_by_name, a.username AS processed_by_username
    FROM document_requests dr
    LEFT JOIN document_types dt ON dr.document_type_id = dt.id
    LEFT JOIN residents r ON dr.resident_id = r.id
    LEFT JOIN resident_accounts ra ON r.id = ra.resident_id
    LEFT JOIN admin_users a ON dr.processed_by = a.id
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
$stmt->close();

// Format the response
$response = [
    'id' => $request['id'],
    'document_type' => $request['document_type'],
    'date_requested' => date('F j, Y, g:i a', strtotime($request['date_requested'])),
    'status' => $request['status'],
    'full_name' => trim($request['first_name'] . ' ' . ($request['middle_name'] ? $request['middle_name'] . ' ' : '') . $request['last_name']),
    'address' => $request['address'],
    'purok' => $request['purok'],
    'contact_number' => $request['contact_number'],
    'purpose' => $request['purpose'],
    'notes' => $request['notes'],
    'resident_email' => $request['resident_email'],
    'account_status' => $request['account_status'],
    'processed_by' => $request['processed_by_name'] ? $request['processed_by_name'] . ' (' . $request['processed_by_username'] . ')' : 'Not processed yet',
    'document_path' => $request['document_file_path'] ?? null // Add this for download functionality
];

header('Content-Type: application/json');
echo json_encode($response);
?>