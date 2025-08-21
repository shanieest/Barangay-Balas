<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $action = $_GET['action'] ?? '';
    $user_id = getUserId();

    switch ($action) {
        case 'update_profile':
            handleUpdateProfile($user_id);
            break;
        case 'update_password':
            handleUpdatePassword($user_id);
            break;
        case 'upload_photo':
            handleUploadPhoto($user_id);
            break;
        case 'delete_account':
            handleDeleteAccount($user_id);
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

function handleUpdateProfile($user_id) {
    global $conn, $response;
    
    $data = $_POST;
    
    // Validate required fields
    $required = ['first_name', 'last_name', 'email'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Ensure contact_number always exists
    $contact_number = isset($data['contact_number']) ? trim($data['contact_number']) : '';

    $stmt = $conn->prepare("UPDATE admin_users SET 
        first_name = ?, last_name = ?, email = ?, contact_number = ?
        WHERE id = ?");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssi", 
        $data['first_name'], 
        $data['last_name'], 
        $data['email'], 
        $contact_number, 
        $user_id
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update profile: " . $stmt->error);
    }
    
    $response['success'] = true;
    $response['message'] = 'Profile updated successfully';
    echo json_encode($response);
}

function handleUpdatePassword($user_id) {
    global $conn, $response;
    
    $data = $_POST;
    
    $required = ['current_password', 'new_password', 'confirm_password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    if ($data['new_password'] !== $data['confirm_password']) {
        throw new Exception("New passwords do not match");
    }
    
    if (strlen($data['new_password']) < 8) {
        throw new Exception("Password must be at least 8 characters long");
    }
    
    $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        throw new Exception("User not found");
    }
    
    $user = $result->fetch_assoc();
    
    if (!password_verify($data['current_password'], $user['password'])) {
        throw new Exception("Current password is incorrect");
    }
    
    $hashed_password = password_hash($data['new_password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update password: " . $stmt->error);
    }
    
    $response['success'] = true;
    $response['message'] = 'Password updated successfully';
    echo json_encode($response);
}

function handleUploadPhoto($user_id) {
    global $conn, $response;
    
    if (empty($_FILES['photo'])) {
        throw new Exception("No file uploaded");
    }
    
    $file = $_FILES['photo'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error: " . $file['error']);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Only JPG, PNG, and GIF files are allowed");
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("File size must be less than 5MB");
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "profile_{$user_id}_" . time() . ".$ext";
    $upload_dir = __DIR__ . '/uploads/profile_photos/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $destination = $upload_dir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to save uploaded file");
    }
    
    $web_path = "uploads/profile_photos/$filename";
    
    $stmt = $conn->prepare("SELECT photo_path FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old_photo = $result->fetch_assoc()['photo_path'];
    
    if ($old_photo && file_exists(__DIR__ . '/' . $old_photo)) {
        unlink(__DIR__ . '/' . $old_photo);
    }
    
    $stmt = $conn->prepare("UPDATE admin_users SET photo_path = ? WHERE id = ?");
    $stmt->bind_param("si", $web_path, $user_id);
    
    if (!$stmt->execute()) {
        unlink($destination);
        throw new Exception("Failed to update profile photo: " . $stmt->error);
    }
    
    $response['success'] = true;
    $response['message'] = 'Profile photo updated successfully';
    $response['photo_path'] = $web_path;
    echo json_encode($response);
}

function handleDeleteAccount($user_id) {
    global $conn, $response;
    
    $data = $_POST;
    
    if (empty($data['confirmation']) || $data['confirmation'] !== 'DELETE MY ACCOUNT') {
        throw new Exception("Please type 'DELETE MY ACCOUNT' to confirm");
    }
    
    $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        throw new Exception("User not found");
    }
    
    $user = $result->fetch_assoc();
    
    if (!password_verify($data['current_password'], $user['password'])) {
        throw new Exception("Current password is incorrect");
    }
    
    $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete account: " . $stmt->error);
    }
    
    $response['success'] = true;
    $response['message'] = 'Account deleted successfully';
    $response['redirect'] = 'login.php';
    echo json_encode($response);
}
?>
