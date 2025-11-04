<?php

require '../../config/db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to apply.']);
        exit;
    }

    $resident_id = $_SESSION['user_id'];
    $notes = $_POST['additionalNotes'] ?? '';
    
    $signature_path = null;
    if (isset($_POST['signatureData']) && !empty($_POST['signatureData'])) {
        $signature_data = $_POST['signatureData'];
        $signature_data = str_replace('data:image/png;base64,', '', $signature_data);
        $signature_data = str_replace(' ', '+', $signature_data);
        $signature_decoded = base64_decode($signature_data);
        
        $signature_dir = '../uploads/signatures/';
        if (!is_dir($signature_dir)) {
            mkdir($signature_dir, 0777, true);
        }
        
        $signature_filename = 'sig_' . $resident_id . '_' . time() . '.png';
        $signature_path = $signature_dir . $signature_filename;
        
        if (file_put_contents($signature_path, $signature_decoded)) {
            $signature_path = 'uploads/signatures/' . $signature_filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save signature.']);
            exit;
        }
    } elseif (isset($_FILES['signatureFile']) && $_FILES['signatureFile']['error'] === UPLOAD_ERR_OK) {
        // Handle uploaded signature file - only PNG allowed
        $allowed_types = ['image/png'];
        $file_type = $_FILES['signatureFile']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Signature file must be PNG format with transparent background.']);
            exit;
        }
        
        if ($_FILES['signatureFile']['size'] > 2 * 1024 * 1024) { // 2MB
            echo json_encode(['success' => false, 'message' => 'Signature file too large (max 2MB).']);
            exit;
        }
        
        $signature_dir = '../uploads/signatures/';
        if (!is_dir($signature_dir)) {
            mkdir($signature_dir, 0777, true);
        }
        
        $extension = 'png'; // Force PNG extension
        $signature_filename = 'sig_' . $resident_id . '_' . time() . '.' . $extension;
        $signature_path = $signature_dir . $signature_filename;
        
        if (move_uploaded_file($_FILES['signatureFile']['tmp_name'], $signature_path)) {
            $signature_path = 'uploads/signatures/' . $signature_filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload signature.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Signature is required.']);
        exit;
    }
    
    // Handle formal photo upload
    $photo_path = null;
    if (isset($_FILES['formalPhoto']) && $_FILES['formalPhoto']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/png', 'image/jpeg', 'image/jpg'];
        $file_type = $_FILES['formalPhoto']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Invalid photo file type. Only JPG, JPEG, and PNG are allowed.']);
            exit;
        }
        
        if ($_FILES['formalPhoto']['size'] > 5 * 1024 * 1024) { // 5MB
            echo json_encode(['success' => false, 'message' => 'Photo file too large (max 5MB).']);
            exit;
        }
        
        $photo_dir = '../uploads/id_photos/';
        if (!is_dir($photo_dir)) {
            mkdir($photo_dir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['formalPhoto']['name'], PATHINFO_EXTENSION);
        $photo_filename = 'photo_' . $resident_id . '_' . time() . '.' . $extension;
        $photo_path = $photo_dir . $photo_filename;
        
        if (move_uploaded_file($_FILES['formalPhoto']['tmp_name'], $photo_path)) {
            $photo_path = 'uploads/id_photos/' . $photo_filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload photo.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Formal photo is required.']);
        exit;
    }

    // Insert application
    $stmt = $conn->prepare("INSERT INTO barangay_id_applications (resident_id, signature_path, photo_path, notes, status, application_date) VALUES (?, ?, ?, ?, 'Pending', NOW())");
    $stmt->bind_param("isss", $resident_id, $signature_path, $photo_path, $notes);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Barangay ID application submitted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error submitting application: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
exit;
?>