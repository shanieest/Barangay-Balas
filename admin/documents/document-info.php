<?php
require 'includes/db.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing request ID"]);
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT dr.id, dr.purpose, dr.status, dr.date_requested, 
               dr.date_processed, dr.notes, dr.document_path,
               dt.document_type, 
               r.full_name, r.address, r.purok, r.contact_number
        FROM document_requests dr
        JOIN document_types dt ON dr.document_type = dt.id
        JOIN residents r ON dr.resident_id = r.id
        WHERE dr.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Request not found"]);
}


if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing request ID"]);
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT dr.id, dt.document_type, r.full_name 
        FROM document_requests dr
        JOIN document_types dt ON dr.document_type = dt.id
        JOIN residents r ON dr.resident_id = r.id
        WHERE dr.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Request not found"]);
}

?>