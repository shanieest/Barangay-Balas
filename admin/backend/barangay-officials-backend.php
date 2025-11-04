<?php
require_once  '../includes/auth.php';
require_once  '../includes/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_officials':
            handleGetOfficials();
            break;
        case 'get_official':
            handleGetOfficial();
            break;
        case 'add_official':
           requireCanModify(); 
            handleAddOfficial();
            break;
        case 'update_official':
              requireCanModify();
            handleUpdateOfficial();
            break;
        case 'delete_official':
                requireCanModify();
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
    
    $query = "SELECT id, username, first_name, last_name, middle_name, email, 
              contact_number, position, committee_position, status, role, created_at, last_login
              FROM admin_users 
              WHERE role IN ('Admin', 'Official')
              ORDER BY 
              CASE role
                WHEN 'Admin' THEN 1
                WHEN 'Official' THEN 2
                ELSE 3
              END,
              CASE position
                WHEN 'Barangay Captain' THEN 1
                WHEN 'Barangay Secretary' THEN 2
                WHEN 'Barangay Treasurer' THEN 3
                WHEN 'Barangay Kagawad' THEN 4
                WHEN 'SK Chairman' THEN 5
                ELSE 6
              END, last_name, first_name";
    
    $result = $conn->query($query);
    if ($result === false) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    
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
    if (!$id || !is_numeric($id)) {
        throw new Exception("Valid official ID is required");
    }
    
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ? AND role IN ('Admin', 'Official')");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
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
    if ($data === null) {
        throw new Exception("Invalid JSON data");
    }
    
    // Validate required fields - committee_position removed from required
    $required = ['first_name', 'last_name', 'position', 'email', 'password', 'role'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: " . ucfirst(str_replace('_', ' ', $field)));
        }
    }
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }
    
    // Check email duplicates
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Email already exists");
    }
    
    // Validate password length
    if (strlen($data['password']) < 8) {
        throw new Exception("Password must be at least 8 characters long");
    }
    
    // Validate contact number format
    if (!empty($data['contact_number']) && !preg_match('/^[0-9]{11}$/', $data['contact_number'])) {
        throw new Exception("Contact number must be exactly 11 digits");
    }
    
    // Validate role - Only allow Admin and Official
    if (!in_array($data['role'], ['Admin', 'Official'])) {
        throw new Exception("Invalid role selected. Must be Admin or Official");
    }
    
    // Check for existing Barangay Captain
    if ($data['position'] === 'Barangay Captain') {
        $stmt = $conn->prepare("SELECT id FROM admin_users WHERE position = 'Barangay Captain' AND status = 'Active'");
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("There can only be one active Barangay Captain");
        }
    }
    
    // Hash password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Generate unique username
    $first_name = trim($data['first_name']);
    $last_name = trim($data['last_name']);
    $username = strtolower($first_name[0] . $last_name);
    $username = preg_replace('/[^a-z0-9]/', '', $username);
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
        if ($counter > 100) {
            throw new Exception("Unable to generate unique username");
        }
    }
    
    // Insert new official - committee_position is now optional
    $stmt = $conn->prepare("INSERT INTO admin_users 
        (username, password, first_name, last_name, middle_name, email, contact_number, position, committee_position, role, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $middle_name = trim($data['middle_name'] ?? '');
    $contact_number = trim($data['contact_number'] ?? '');
    $status = $data['status'] ?? 'Active';
    $position = trim($data['position']);
    $committee_position = trim($data['committee_position'] ?? ''); // Now optional
    $email = trim($data['email']);
    $role = $data['role'];
    
    $stmt->bind_param("sssssssssss", 
        $username, 
        $hashed_password,
        $first_name, 
        $last_name, 
        $middle_name,
        $email, 
        $contact_number, 
        $position,
        $committee_position,
        $role,
        $status
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to add official: " . $stmt->error);
    }
    
    $response['success'] = true;
    $response['message'] = 'Official added successfully';
    $response['username'] = $username;
    echo json_encode($response);
}

function handleUpdateOfficial() {
    global $conn, $response;

    $data = json_decode(file_get_contents('php://input'), true);
    if ($data === null) {
        throw new Exception("Invalid JSON data");
    }

    if (empty($data['id']) || !is_numeric($data['id'])) {
        throw new Exception("Valid official ID is required");
    }

    // Validate required fields - committee_position removed from required
    $required = ['first_name', 'last_name', 'position', 'role'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: " . ucfirst(str_replace('_', ' ', $field)));
        }
    }

    // Validate contact number
    if (!empty($data['contact_number']) && !preg_match('/^[0-9]{11}$/', $data['contact_number'])) {
        throw new Exception("Contact number must be exactly 11 digits");
    }

    // Validate role - Only allow Admin and Official
    if (!in_array($data['role'], ['Admin', 'Official'])) {
        throw new Exception("Invalid role selected. Must be Admin or Official");
    }

    // Check duplicate email
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ? AND role IN ('Admin', 'Official')");
    $stmt->bind_param("si", $data['email'], $data['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Email already exists");
    }

    // Get current official data
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ? AND role IN ('Admin', 'Official')");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    if (!$current) {
        throw new Exception("Official not found");
    }

    // Check Barangay Captain constraint
    if ($data['position'] === 'Barangay Captain' && $current['position'] !== 'Barangay Captain') {
        $stmt = $conn->prepare("SELECT id FROM admin_users WHERE position = 'Barangay Captain' AND status = 'Active' AND id != ? AND role IN ('Admin', 'Official')");
        $stmt->bind_param("i", $data['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("There can only be one active Barangay Captain");
        }
    }

    // Build update query
    $sql = "UPDATE admin_users SET 
        first_name = ?, last_name = ?, middle_name = ?, 
      contact_number = ?, position = ?, committee_position = ?, role = ?, status = ?";
    
    $params = [
        trim($data['first_name']),
        trim($data['last_name']),
        trim($data['middle_name'] ?? ''),
        trim($data['contact_number'] ?? ''),
        trim($data['position']),
        trim($data['committee_position'] ?? ''), // Now optional
        $data['role'],
        $data['status'] ?? 'Active'
    ];
    $types = "ssssssss";

    // Add password if provided
    if (!empty($data['password'])) {
        if (strlen($data['password']) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql .= ", password = ?";
        $types .= "s";
        $params[] = $hashed_password;
    }

    $sql .= " WHERE id = ? AND role IN ('Admin', 'Official')";
    $types .= "i";
    $params[] = (int)$data['id'];

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        throw new Exception("Failed to update official: " . $stmt->error);
    }

    $response['success'] = true;
    $response['message'] = 'Official updated successfully';
    echo json_encode($response);
}

function handleDeleteOfficial() {
    global $conn, $response;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id || !is_numeric($id)) {
        throw new Exception("Valid official ID is required");
    }
    
    // Get official data
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ? AND role IN ('Admin', 'Official')");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Official not found");
    }
    
    $official = $result->fetch_assoc();
    
    // Prevent deletion of Barangay Captain
    if ($official['position'] === 'Barangay Captain') {
        throw new Exception("Cannot delete Barangay Captain");
    }
    
    // Delete the official
    $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ? AND role IN ('Admin', 'Official')");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete official: " . $stmt->error);
    }
    
    $response['success'] = true;
    $response['message'] = 'Official deleted successfully';
    echo json_encode($response);
}
?>