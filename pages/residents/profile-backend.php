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

function validatePhoneNumber($phone) {
    // Remove all non-digit characters
    $cleaned = preg_replace('/\D/', '', $phone);
    
    // Check if it's a valid Philippine mobile number (09XXXXXXXXX) or landline
    if (preg_match('/^09\d{9}$/', $cleaned)) {
        return true; // Mobile number
    } elseif (preg_match('/^\d{7,10}$/', $cleaned)) {
        return true; // Landline
    }
    
    return false;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // CSRF Token Validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Security error. Please try again.";
        header("Location: profile.php?message=" . urlencode($message));
        exit;
    }

    try {
        if ($_POST['action'] === "update_profile") {
            // Sanitize and validate input data
            $first_name = sanitizeInput($_POST['first_name']);
            $middle_name = sanitizeInput($_POST['middle_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $suffix = sanitizeInput($_POST['suffix']);
            $birthdate = $_POST['birthdate'];
            $place_of_birth = sanitizeInput($_POST['place_of_birth']);
            $sex = $_POST['sex'];
            $civil_status = $_POST['civil_status'];
            $email = sanitizeInput($_POST['email']);
            $contact_number = sanitizeInput($_POST['contact_number']);
            $house_number = sanitizeInput($_POST['house_number']);
            $purok = $_POST['purok'];
            $address = sanitizeInput($_POST['address']);
            
            // Additional optional fields
            $educational_attainment = isset($_POST['educational_attainment']) ? sanitizeInput($_POST['educational_attainment']) : '';
            $occupation = isset($_POST['occupation']) ? sanitizeInput($_POST['occupation']) : '';
            $religion = isset($_POST['religion']) ? sanitizeInput($_POST['religion']) : '';
            $philhealth_number = isset($_POST['philhealth_number']) ? sanitizeInput($_POST['philhealth_number']) : '';
            $is_indigent = isset($_POST['is_indigent']) ? 1 : 0;
            $is_4ps_member = isset($_POST['is_4ps_member']) ? 1 : 0;
            $medical_history = isset($_POST['medical_history']) ? sanitizeInput($_POST['medical_history']) : '';
            $resident_status = $_POST['resident_status'] ?? 'Active';

            // Validation
            $errors = [];

            // Required field validation
            if (empty($first_name)) $errors[] = "First name is required.";
            if (empty($last_name)) $errors[] = "Last name is required.";
            if (empty($birthdate)) $errors[] = "Birthdate is required.";
            if (empty($place_of_birth)) $errors[] = "Place of birth is required.";
            if (empty($sex)) $errors[] = "Gender is required.";
            if (empty($civil_status)) $errors[] = "Civil status is required.";
            if (empty($contact_number)) $errors[] = "Contact number is required.";
            if (empty($house_number)) $errors[] = "House number is required.";
            if (empty($purok)) $errors[] = "Purok is required.";
            if (empty($address)) $errors[] = "Address is required.";

            // Email validation
            if (!empty($email) && !validateEmail($email)) {
                $errors[] = "Invalid email format.";
            }

            // Phone number validation
            if (!validatePhoneNumber($contact_number)) {
                $errors[] = "Invalid contact number format. Please use a valid Philippine mobile (09XXXXXXXXX) or landline number.";
            }

            // Age validation (must be at least 1 year old)
            $birthdate_obj = DateTime::createFromFormat('Y-m-d', $birthdate);
            $today = new DateTime();
            $age = $birthdate_obj->diff($today)->y;
            
            if ($age < 1) {
                $errors[] = "You must be at least 1 year old.";
            }

            if ($age > 120) {
                $errors[] = "Please enter a valid birthdate.";
            }

            // If there are validation errors
            if (!empty($errors)) {
                $message = implode(" ", $errors);
                header("Location: profile.php?message=" . urlencode($message));
                exit;
            }

            // Check if this is the first profile update (for new residents)
            $checkSql = "SELECT first_name FROM residents WHERE id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("i", $userId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existingProfile = $checkResult->fetch_assoc();
            $checkStmt->close();

            $isFirstUpdate = empty($existingProfile['first_name']);

            // Prepare SQL query
            $sql = "UPDATE residents 
                    SET first_name=?, middle_name=?, last_name=?, suffix=?, 
                        birthdate=?, place_of_birth=?, sex=?, civil_status=?,
                        email=?, contact_number=?, house_number=?, purok=?, resident_status=?, address=?, 
                        educational_attainment=?, occupation=?, religion=?, philhealth_number=?, 
                        is_indigent=?, is_4ps_member=?, medical_history=?, updated_at=NOW()
                    WHERE id=?";

            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }

            $stmt->bind_param(
                "sssssssssssssssssiiisi",
                $first_name, $middle_name, $last_name, $suffix,
                $birthdate, $place_of_birth, $sex, $civil_status,
                $email, $contact_number, $house_number, $purok, $resident_status, $address,
                $educational_attainment, $occupation, $religion, $philhealth_number,
                $is_indigent, $is_4ps_member, $medical_history, $userId
            );

            if ($stmt->execute()) {
                // Log the activity
                logActivity($conn, $userId, "Updated profile information.");
                
                // If this is the first update, show a welcome message
                if ($isFirstUpdate) {
                    $message = "Profile created successfully! Welcome to Barangay Balas Portal.";
                } else {
                    $message = "Profile updated successfully!";
                }
                
                // Regenerate CSRF token for security
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } else {
                throw new Exception("Error updating profile: " . $stmt->error);
            }

            $stmt->close();

        } elseif ($_POST['action'] === "upload_photo") {
            
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
                $fileName = $_FILES['profile_photo']['name'];
                $fileSize = $_FILES['profile_photo']['size'];
                $fileType = $_FILES['profile_photo']['type'];

                // Validate file type
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new Exception("Invalid file type. Allowed types: JPG, JPEG, PNG, GIF.");
                }

                // Validate file size (5MB max)
                if ($fileSize > 5 * 1024 * 1024) {
                    throw new Exception("File too large. Maximum size is 5MB.");
                }

                // Validate image dimensions
                $imageInfo = getimagesize($fileTmpPath);
                if (!$imageInfo) {
                    throw new Exception("Invalid image file.");
                }

                $maxWidth = 2000;
                $maxHeight = 2000;
                if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
                    throw new Exception("Image dimensions too large. Maximum: {$maxWidth}x{$maxHeight} pixels.");
                }

                // Create upload directory if it doesn't exist
                $uploadDir = "../../uploads/profile_photos/";
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        throw new Exception("Failed to create upload directory.");
                    }
                }

                // Generate unique filename
                $newFileName = "profile_" . $userId . "_" . time() . "." . $fileExtension;
                $destPath = $uploadDir . $newFileName;

                // Move uploaded file
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $photoPath = "uploads/profile_photos/" . $newFileName;

                    // Delete old photo if exists
                    $getOldPhotoSql = "SELECT photo_path FROM residents WHERE id = ?";
                    $getOldPhotoStmt = $conn->prepare($getOldPhotoSql);
                    $getOldPhotoStmt->bind_param("i", $userId);
                    $getOldPhotoStmt->execute();
                    $oldPhotoResult = $getOldPhotoStmt->get_result();
                    
                    if ($oldPhotoRow = $oldPhotoResult->fetch_assoc()) {
                        if (!empty($oldPhotoRow['photo_path']) && file_exists("../../" . $oldPhotoRow['photo_path'])) {
                            unlink("../../" . $oldPhotoRow['photo_path']);
                        }
                    }
                    $getOldPhotoStmt->close();

                    // Update database
                    $sql = "UPDATE residents SET photo_path=?, updated_at=NOW() WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $photoPath, $userId);

                    if ($stmt->execute()) {
                        logActivity($conn, $userId, "Updated profile photo.");
                        $message = "Profile photo updated successfully!";
                    } else {
                        throw new Exception("Error updating photo in database: " . $stmt->error);
                    }

                    $stmt->close();
                } else {
                    throw new Exception("Error uploading file. Please try again.");
                }

            } else {
                $uploadError = $_FILES['profile_photo']['error'] ?? 'Unknown error';
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                ];
                
                throw new Exception($errorMessages[$uploadError] ?? 'File upload error: ' . $uploadError);
            }

        } else {
            throw new Exception("Invalid action specified.");
        }

    } catch (Exception $e) {
        $message = $e->getMessage();
    }

    // Redirect with message
    header("Location: profile.php?message=" . urlencode($message));
    exit;
}

// If someone tries to access this file directly without POST request
header("Location: profile.php");
exit;
?>