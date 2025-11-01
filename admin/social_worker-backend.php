<?php
// admin/social_worker-backend.php
session_start();
require_once "includes/db.php";
require_once "includes/auth.php";

requireAuth();
requireCanModify();

// Handle form submissions
$message = '';
$messageType = '';

// Function to generate username
function generateUsername($firstName, $lastName, $conn) {
    $baseUsername = strtolower(substr($firstName, 0, 1) . preg_replace('/[^a-z0-9]/', '', strtolower($lastName)));
    $username = $baseUsername;
    $counter = 1;
    
    // Check if username exists and find available one
    while (true) {
        $checkStmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows === 0) {
            break;
        }
        
        $username = $baseUsername . $counter;
        $counter++;
        
        // Safety limit to prevent infinite loop
        if ($counter > 100) {
            $username = $baseUsername . uniqid();
            break;
        }
    }
    
    return $username;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Add new social worker
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $middleName = trim($_POST['middle_name'] ?? '');
        $email = trim($_POST['email']);
        $contactNumber = trim($_POST['contact_number']);
        $position = trim($_POST['position']);
        $department = trim($_POST['department'] ?? 'Daycare Center');
        $specialization = trim($_POST['specialization'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        // Auto-generate username
        $username = generateUsername($firstName, $lastName, $conn);
        
        // Check if email exists
        $checkStmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            $_SESSION['message'] = "Email already exists!";
            $_SESSION['messageType'] = "danger";
        } else {
            // Validate password
            if (empty($password)) {
                $_SESSION['message'] = "Password is required!";
                $_SESSION['messageType'] = "danger";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert admin user
                $stmt = $conn->prepare("
                    INSERT INTO admin_users (username, password, first_name, last_name, middle_name, 
                                            email, contact_number, position, role, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Social Worker', 'Active')
                ");
                $stmt->bind_param("ssssssss", $username, $hashedPassword, $firstName, $lastName, 
                                $middleName, $email, $contactNumber, $position);
                
                if ($stmt->execute()) {
                    $adminUserId = $conn->insert_id;
                    
                    // Insert social worker profile
                    $swStmt = $conn->prepare("
                        INSERT INTO social_workers (admin_user_id, department, specialization) 
                        VALUES (?, ?, ?)
                    ");
                    $swStmt->bind_param("iss", $adminUserId, $department, $specialization);
                    
                    if ($swStmt->execute()) {
                        logActivity(getUserId(), "Added new social worker: $firstName $lastName (Username: $username)");
                        $_SESSION['message'] = "Social worker added successfully! Username: $username";
                        $_SESSION['messageType'] = "success";
                    } else {
                        $_SESSION['message'] = "Error creating social worker profile: " . $swStmt->error;
                        $_SESSION['messageType'] = "danger";
                    }
                } else {
                    $_SESSION['message'] = "Error creating user account: " . $stmt->error;
                    $_SESSION['messageType'] = "danger";
                }
            }
        }
    } elseif ($action === 'update') {
        // Update social worker
        $id = intval($_POST['id']);
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $middleName = trim($_POST['middle_name'] ?? '');
        $email = trim($_POST['email']);
        $contactNumber = trim($_POST['contact_number']);
        $position = trim($_POST['position']);
        $department = trim($_POST['department'] ?? 'Daycare Center');
        $specialization = trim($_POST['specialization'] ?? '');
        $status = $_POST['status'];
        
        // Update admin user
        $stmt = $conn->prepare("
            UPDATE admin_users 
            SET first_name = ?, last_name = ?, middle_name = ?, email = ?, 
                contact_number = ?, position = ?, status = ?
            WHERE id = ? AND role = 'Social Worker'
        ");
        $stmt->bind_param("sssssssi", $firstName, $lastName, $middleName, $email, 
                         $contactNumber, $position, $status, $id);
        
        if ($stmt->execute()) {
            // Update social worker profile
            $swStmt = $conn->prepare("
                UPDATE social_workers 
                SET department = ?, specialization = ?
                WHERE admin_user_id = ?
            ");
            $swStmt->bind_param("ssi", $department, $specialization, $id);
            $swStmt->execute();
            
            logActivity(getUserId(), "Updated social worker: $firstName $lastName");
            $_SESSION['message'] = "Social worker updated successfully!";
            $_SESSION['messageType'] = "success";
        } else {
            $_SESSION['message'] = "Error updating social worker: " . $stmt->error;
            $_SESSION['messageType'] = "danger";
        }
    } elseif ($action === 'delete') {
        // Delete social worker
        $id = intval($_POST['id']);
        
        $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ? AND role = 'Social Worker'");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity(getUserId(), "Deleted social worker ID: $id");
            $_SESSION['message'] = "Social worker deleted successfully!";
            $_SESSION['messageType'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting social worker: " . $stmt->error;
            $_SESSION['messageType'] = "danger";
        }
    }
}

header("Location: social_worker.php");
exit();
?>