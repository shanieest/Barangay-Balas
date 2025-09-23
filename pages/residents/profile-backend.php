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
        $suffix = trim($_POST['suffix']);
        $birthdate = $_POST['birthdate'];
        $sex = $_POST['sex'];
        $civil_status = $_POST['civil_status'];
        $email = trim($_POST['email']);
        $contact_number = trim($_POST['contact_number']);
        $house_number = trim($_POST['house_number']);
        $purok = trim($_POST['purok']);
        $resident_status = $_POST['resident_status'] ?? 'Active';
        $address = trim($_POST['address']);
        $educational_attainment = $_POST['educational_attainment'] ?? '';
        $occupation = trim($_POST['occupation']);
        $religion = trim($_POST['religion']);
        $philhealth_number = trim($_POST['philhealth_number']);
        $is_indigent = isset($_POST['is_indigent']) ? 1 : 0;
        $is_4ps_member = isset($_POST['is_4ps_member']) ? 1 : 0;
        $medical_history = trim($_POST['medical_history']);

        $sql = "UPDATE residents 
                SET first_name=?, middle_name=?, last_name=?, suffix=?, 
                    birthdate=?, sex=?, civil_status=?, email=?, contact_number=?, 
                    house_number=?, purok=?, resident_status=?, address=?, 
                    educational_attainment=?, occupation=?, religion=?, philhealth_number=?, 
                    is_indigent=?, is_4ps_member=?, medical_history=?, updated_at=NOW()
                WHERE id=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssssssssssiisi",
            $first_name, $middle_name, $last_name, $suffix,
            $birthdate, $sex, $civil_status, $email, $contact_number,
            $house_number, $purok, $resident_status, $address,
            $educational_attainment, $occupation, $religion, $philhealth_number,
            $is_indigent, $is_4ps_member, $medical_history, $userId
        );

        if ($stmt->execute()) {
            logActivity($conn, $userId, "Updated profile information.");
            $message = "Profile updated successfully!";
        } else {
            $message = "Error updating profile: " . $stmt->error;
        }

        $stmt->close();
        header("Location: profile.php?message=" . urlencode($message));
        exit;
    }


    if ($_POST['action'] === "upload_photo") {
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
            $fileName = $_FILES['profile_photo']['name'];
            $fileSize = $_FILES['profile_photo']['size'];
            $fileType = $_FILES['profile_photo']['type'];

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                $message = "Invalid file type. Allowed: JPG, PNG, GIF.";
                header("Location: profile.php?message=" . urlencode($message));
                exit;
            }

            if ($fileSize > 5 * 1024 * 1024) { // 5MB
                $message = "File too large. Max size is 5MB.";
                header("Location: profile.php?message=" . urlencode($message));
                exit;
            }

            $newFileName = "profile_" . $userId . "_" . time() . "." . $fileExtension;
            $uploadDir = "../../uploads/profile_photos/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $photoPath = "uploads/profile_photos/" . $newFileName;

                $sql = "UPDATE residents SET photo_path=?, updated_at=NOW() WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $photoPath, $userId);

                if ($stmt->execute()) {
                    logActivity($conn, $userId, "Updated profile photo.");
                    $message = "Profile photo updated successfully!";
                } else {
                    $message = "Error updating photo: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $message = "Error uploading file. Please try again.";
            }
        } else {
            $message = "No file selected or upload error.";
        }

        header("Location: profile.php?message=" . urlencode($message));
        exit;
    }
}
?>
