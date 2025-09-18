<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';

function logActivity($conn, $userId, $activity) {
    $sql = "INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $activity);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Security error. Please try again.";
        header("Location: profile.php?message=" . urlencode($message));
        exit;
    }
    
    if ($_POST['action'] === "update_profile") {
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name']);
        $last_name = trim($_POST['last_name']);
        $birthdate = $_POST['birthdate'];
        $sex = $_POST['sex'];
        $civil_status = $_POST['civil_status'];
        $contact_number = trim($_POST['contact_number']);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $house_number = trim($_POST['house_number']);
        $purok = $_POST['purok'];
        $address = trim($_POST['address']);
        $occupation = trim($_POST['occupation']);
        $educational_attainment = trim($_POST['educational_attainment']);
        $religion = trim($_POST['religion']);
        
        $birthDate = new DateTime($birthdate);
        $today = new DateTime();
        $age = $birthDate->diff($today)->y;
        
        $sql = "UPDATE residents 
                SET first_name=?, middle_name=?, last_name=?, birthdate=?, age=?, sex=?, civil_status=?, 
                    contact_number=?, email=?, house_number=?, purok=?, address=?, occupation=?, 
                    educational_attainment=?, religion=?, updated_at=NOW()
                WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssissssssssssi", 
            $first_name,
            $middle_name,
            $last_name,
            $birthdate,
            $age,
            $sex,
            $civil_status,
            $contact_number,
            $email,
            $house_number,
            $purok,
            $address,
            $occupation,
            $educational_attainment,
            $religion,
            $userId
        );
        
        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
            logActivity($conn, $userId, "Updated profile information");
        } else {
            $message = "Error updating profile: " . $conn->error;
        }
        $stmt->close();
        
        header("Location: profile.php?message=" . urlencode($message));
        exit;
    } 
    elseif ($_POST['action'] === "upload_photo") {
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_photo'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
            $max_size = 5 * 1024 * 1024; 
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($file_type, $allowed_types)) {
                $message = "Invalid file type. Only JPG, PNG and GIF are allowed.";
            } 
            else if ($file['size'] > $max_size) {
                $message = "File too large. Maximum size is 5MB.";
            } 
            else {
                $upload_dir = '../../uploads/profiles/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $get_photo_sql = "SELECT photo_path FROM residents WHERE id = ?";
                $stmt = $conn->prepare($get_photo_sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->bind_result($old_photo_path);
                $stmt->fetch();
                $stmt->close();
                
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'profile_' . $userId . '_' . time() . '.' . $file_extension;
                $destination = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $relative_path = 'uploads/profiles/' . $filename;
                    $update_sql = "UPDATE residents SET photo_path = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("si", $relative_path, $userId);
                    
                    if ($stmt->execute()) {
                        $message = "Profile photo updated successfully!";
                        logActivity($conn, $userId, "Updated profile photo");
                        
                        if (!empty($old_photo_path) && 
                            file_exists('../../' . $old_photo_path) && 
                            !str_contains($old_photo_path, 'via.placeholder.com')) {
                            unlink('../../' . $old_photo_path);
                        }
                    } else {
                        $message = "Error updating database: " . $conn->error;
                        unlink($destination);
                    }
                    $stmt->close();
                } else {
                    $message = "Error uploading file.";
                }
            }
        } else {
            $message = "Please select a valid image file.";
        }
        
        header("Location: profile.php?message=" . urlencode($message));
        exit;
    }
}

header("Location: profile.php");
exit;
?>
