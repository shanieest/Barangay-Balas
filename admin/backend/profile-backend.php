<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
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
    
    $required = ['first_name', 'last_name', 'email'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

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
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        throw new Exception($errorMessages[$file['error']] ?? 'Unknown upload error');
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception("Only JPG, PNG, and GIF files are allowed");
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("File size must be less than 5MB");
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/../uploads/profile_photos/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "profile_{$user_id}_" . time() . "." . strtolower($ext);
    $destination = $upload_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to save uploaded file");
    }
    
    // Web-accessible path (relative to the root)
    $web_path = "uploads/profile_photos/$filename";
    
    // Get old photo path for cleanup
    $stmt = $conn->prepare("SELECT photo_path FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old_photo = $result->fetch_assoc()['photo_path'];
    
    // Delete old photo if it exists and is not the default
    if ($old_photo && $old_photo !== 'assets/admin-avatar.jpg' && file_exists(__DIR__ . '/../' . $old_photo)) {
        unlink(__DIR__ . '/../' . $old_photo);
    }
    
    // Update database with new photo path
    $stmt = $conn->prepare("UPDATE admin_users SET photo_path = ? WHERE id = ?");
    $stmt->bind_param("si", $web_path, $user_id);
    
    if (!$stmt->execute()) {
        // Clean up the uploaded file if database update fails
        unlink($destination);
        throw new Exception("Failed to update profile photo: " . $stmt->error);
    }
    
    $response['success'] = true;
    $response['message'] = 'Profile photo updated successfully';
    $response['photo_path'] = '../' . $web_path; // Return path relative to profile.php
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
    $response['redirect'] = '../login.php';
    echo json_encode($response);
}
?>