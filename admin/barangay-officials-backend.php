<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $action = $_GET['action'] ?? '';
    $user_id = getUserId();

    switch ($action) {
        case 'get_officials':
            handleGetOfficials();
            break;
        case 'get_official':
            handleGetOfficial();
            break;
        case 'add_official':
            handleAddOfficial();
            break;
        case 'update_official':
            handleUpdateOfficial();
            break;
        case 'delete_official':
            handleDeleteOfficial();
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            exit();
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit();
}

function handleGetOfficials() {
    global $conn, $response;
    
    $query = "SELECT * FROM barangay_officials ORDER BY 
              CASE position
                WHEN 'Barangay Captain' THEN 1
                WHEN 'Barangay Secretary' THEN 2
                WHEN 'Barangay Treasurer' THEN 3
                WHEN 'Barangay Kagawad' THEN 4
                WHEN 'SK Chairman' THEN 5
                ELSE 6
              END, last_name, first_name";
    
    $result = $conn->query($query);
    
    $officials = [];
    while ($row = $result->fetch_assoc()) {
        $officials[] = $row;
    }
    
    $response['success'] = true;
    $response['data'] = $officials;
    echo json_encode($response);
}

function handleGetOfficial() {
    global $conn, $response;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception("Official ID is required");
    }
    
    $stmt = $conn->prepare("SELECT * FROM barangay_officials WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Official not found");
    }
    
    $response['success'] = true;
    $response['data'] = $result->fetch_assoc();
    echo json_encode($response);
}

function handleAddOfficial() {
    global $conn, $response;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['first_name', 'last_name', 'position', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    if (strlen($data['password']) < 8) {
        throw new Exception("Password must be at least 8 characters long");
    }
    
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO barangay_officials 
        (first_name, last_name, middle_name, position, email, contact_number, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssssss", 
        $data['first_name'], $data['last_name'], $data['middle_name'] ?? '',
        $data['position'], $data['email'], $data['contact_number'] ?? '',
        $data['status'] ?? 'Active');
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to add official: " . $stmt->error);
    }
    
    // Also add to admin_users table for login access
    $official_id = $stmt->insert_id;
    $username = strtolower($data['first_name'][0] . $data['last_name']);
    $username = preg_replace('/[^a-z0-9]/', '', $username);
    
    // Make sure username is unique
    $temp_username = $username;
    $counter = 1;
    while (true) {
        $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $temp_username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $username = $temp_username;
            break;
        }
        $temp_username = $username . $counter++;
    }
    
    $stmt = $conn->prepare("INSERT INTO admin_users 
        (username, password, first_name, last_name, middle_name, email, contact_number, position)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssss", 
        $username, $hashed_password,
        $data['first_name'], $data['last_name'], $data['middle_name'] ?? '',
        $data['email'], $data['contact_number'] ?? '', $data['position']);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create admin account for official: " . $stmt->error);
    }
    
    $response['success'] = true;
    $response['message'] = 'Official added successfully';
    echo json_encode($response);
}

function handleUpdateOfficial() {
    global $conn, $response;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        throw new Exception("Official ID is required");
    }
    
    $required = ['first_name', 'last_name', 'position', 'email'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $stmt = $conn->prepare("UPDATE barangay_officials SET 
        first_name = ?, last_name = ?, middle_name = ?, position = ?, 
        email = ?, contact_number = ?, status = ?
        WHERE id = ?");
    
    $stmt->bind_param("sssssssi", 
        $data['first_name'], $data['last_name'], $data['middle_name'] ?? '',
        $data['position'], $data['email'], $data['contact_number'] ?? '',
        $data['status'] ?? 'Active', $data['id']);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update official: " . $stmt->error);
    }
    
    // Update password if provided
    if (!empty($data['password'])) {
        if (strlen($data['password']) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
        
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $data['email']);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update official password: " . $stmt->error);
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Official updated successfully';
    echo json_encode($response);
}

function handleDeleteOfficial() {
    global $conn, $response;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception("Official ID is required");
    }
    
    $stmt = $conn->prepare("SELECT email FROM barangay_officials WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Official not found");
    }
    
    $official = $result->fetch_assoc();
    
    // Delete from admin_users first
    $stmt = $conn->prepare("DELETE FROM admin_users WHERE email = ?");
    $stmt->bind_param("s", $official['email']);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete official admin account: " . $stmt->error);
    }
    
    // Then delete from barangay_officials
    $stmt = $conn->prepare("DELETE FROM barangay_officials WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete official: " . $stmt->error);
    }
    
    $response['success'] = true;
    $response['message'] = 'Official deleted successfully';
    echo json_encode($response);
}
?>