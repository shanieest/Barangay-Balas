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
    
    $stmt = $conn->prepare("SELECT * FROM barangay_officials WHERE id = ?");
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
    
    // Validate required fields
    $required = ['first_name', 'last_name', 'position', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }
    
    // Check email duplicates
    $stmt = $conn->prepare("SELECT id FROM barangay_officials WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Email already exists");
    }
    
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Email already exists in admin users");
    }
    
    if (strlen($data['password']) < 8) {
        throw new Exception("Password must be at least 8 characters long");
    }
    
    if (!empty($data['contact_number']) && !preg_match('/^[0-9]{11}$/', $data['contact_number'])) {
        throw new Exception("Contact number must be 11 digits");
    }
    
    if ($data['position'] === 'Barangay Captain') {
        $stmt = $conn->prepare("SELECT id FROM barangay_officials WHERE position = 'Barangay Captain' AND status = 'Active'");
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("There can only be one active Barangay Captain");
        }
    }
    
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    $conn->begin_transaction();
    
    try {
        // Barangay officials
        $stmt = $conn->prepare("INSERT INTO barangay_officials 
            (first_name, last_name, middle_name, position, email, contact_number, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $first_name     = $data['first_name'];
        $last_name      = $data['last_name'];
        $middle_name    = $data['middle_name'] ?? '';
        $position       = $data['position'];
        $email          = $data['email'];
        $contact_number = $data['contact_number'] ?? '';
        $status         = $data['status'] ?? 'Active';
        
        $stmt->bind_param("sssssss", 
            $first_name, 
            $last_name, 
            $middle_name, 
            $position, 
            $email, 
            $contact_number, 
            $status
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to add official: " . $stmt->error);
        }
        
        $official_id = $stmt->insert_id;
        
        // Generate username
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
        
        $stmt = $conn->prepare("INSERT INTO admin_users 
            (username, password, first_name, last_name, middle_name, email, contact_number, position, role)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Official')");
        
        $au_first_name  = $first_name;
        $au_last_name   = $last_name;
        $au_middle_name = $middle_name;
        $au_email       = $email;
        $au_contact     = $contact_number;
        $au_position    = $position;
        
        $stmt->bind_param("ssssssss", 
            $username, 
            $hashed_password,
            $au_first_name, 
            $au_last_name, 
            $au_middle_name,
            $au_email, 
            $au_contact, 
            $au_position
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create admin account: " . $stmt->error);
        }
        
        $admin_user_id = $stmt->insert_id;
        $stmt = $conn->prepare("UPDATE barangay_officials SET admin_user_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $admin_user_id, $official_id);
        $stmt->execute();
        
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Official added successfully';
        $response['username'] = $username;
        echo json_encode($response);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
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

    $required = ['first_name', 'last_name', 'position', 'email'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    if (!empty($data['contact_number']) && !preg_match('/^[0-9]{11}$/', $data['contact_number'])) {
        throw new Exception("Contact number must be 11 digits");
    }

    // Check duplicate email in barangay_officials
    $stmt = $conn->prepare("SELECT id FROM barangay_officials WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $data['email'], $data['id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Email already exists");
    }

    // Get current official
    $stmt = $conn->prepare("SELECT * FROM barangay_officials WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    $current = $stmt->get_result()->fetch_assoc();
    if (!$current) {
        throw new Exception("Official not found");
    }

    // Barangay Captain constraint
    if ($data['position'] === 'Barangay Captain' && $current['position'] !== 'Barangay Captain') {
        $stmt = $conn->prepare("SELECT id FROM barangay_officials WHERE position = 'Barangay Captain' AND status = 'Active' AND id != ?");
        $stmt->bind_param("i", $data['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("There can only be one active Barangay Captain");
        }
    }

    $conn->begin_transaction();

    try {
        // ----------------------
        // Update barangay_officials
        // ----------------------
        $stmt = $conn->prepare("UPDATE barangay_officials SET 
            first_name = ?, last_name = ?, middle_name = ?, 
            position = ?, email = ?, contact_number = ?, status = ? 
            WHERE id = ?");

        $first_name     = $data['first_name'];
        $last_name      = $data['last_name'];
        $middle_name    = $data['middle_name'] ?? '';
        $position       = $data['position'];
        $email          = $data['email'];
        $contact_number = $data['contact_number'] ?? '';
        $status         = $data['status'] ?? 'Active';
        $id             = (int)$data['id'];

        $stmt->bind_param(
            "sssssssi",
            $first_name, $last_name, $middle_name,
            $position, $email, $contact_number,
            $status, $id
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to update official: " . $stmt->error);
        }

        // ----------------------
        // Update admin_users
        // ----------------------
        $sql = "UPDATE admin_users SET 
            first_name = ?, last_name = ?, middle_name = ?, 
            email = ?, contact_number = ?, position = ?, status = ?";
        $params = [
            $first_name, $last_name, $middle_name,
            $email, $contact_number, $position, $status
        ];
        $types = "sssssss";

        // Optional password
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 8) {
                throw new Exception("Password must be at least 8 characters long");
            }
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $sql .= ", password = ?";
            $types .= "s";
            $params[] = $hashed_password;
        }

        $sql .= " WHERE email = ?";
        $types .= "s";
        $params[] = $current['email'];

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param($types, ...$params);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update admin account: " . $stmt->error);
        }

        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Official updated successfully';
        echo json_encode($response);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}


function handleDeleteOfficial() {
    global $conn, $response;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id || !is_numeric($id)) {
        throw new Exception("Valid official ID is required");
    }
    
    // Get official data first
    $stmt = $conn->prepare("SELECT * FROM barangay_officials WHERE id = ?");
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
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete from admin_users first
        $stmt = $conn->prepare("DELETE FROM admin_users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $official['email']);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete official admin account: " . $stmt->error);
        }
        
        // Then delete from barangay_officials
        $stmt = $conn->prepare("DELETE FROM barangay_officials WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to delete official: " . $stmt->error);
        }
        
        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = 'Official deleted successfully';
        echo json_encode($response);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}
?>