<?php
// get_application_details.php
require 'includes/db.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

$id = intval($_GET['id']);

// Get application and resident details - specify columns to avoid conflicts
$query = "
SELECT 
    a.id, a.resident_id, a.photo_path, a.signature_path, a.id_number,
    a.status, a.application_date, a.valid_until, a.notes,
    a.rejection_reason, a.digital_id_path,
    r.first_name, r.middle_name, r.last_name, r.suffix, r.address,
    r.birthdate, r.contact_number, r.civil_status, r.sex, r.place_of_birth
FROM barangay_id_applications a 
JOIN residents r ON a.resident_id = r.id 
WHERE a.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    // Format dates
    $application_date = date('M d, Y', strtotime($data['application_date']));
    $birthdate = date('M d, Y', strtotime($data['birthdate']));
    
    echo json_encode([
        'success' => true,
        'resident' => [
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? '',
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'] ?? '',
            'birthdate' => $birthdate,
            'address' => $data['address'],
            'contact_number' => $data['contact_number'] ?? '',
            'place_of_birth' => $data['place_of_birth'] ?? '',
        ],
        'application' => [
            'id' => $data['id'],
            'application_date' => $application_date,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? '',
            'photo_path' => $data['photo_path'] ?? null,
            'signature_path' => $data['signature_path'] ?? null,
            'id_number' => $data['id_number'] ?? null,
            'rejection_reason' => $data['reject_reason'] ?? null,
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Application not found']);
}

$stmt->close();
$conn->close();
?>