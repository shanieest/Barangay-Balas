<?php

session_start();
require_once '../../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a medicine request.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_SESSION['user_id'];
    $medicine_id = isset($_POST['medicine_id']) ? intval($_POST['medicine_id']) : 0;
    $medical_condition = trim($_POST['medical_condition']);
    $urgency_level = $_POST['urgency_level'];
    $additional_notes = trim($_POST['additional_notes'] ?? '');
    
    // Validate inputs
    if (empty($medicine_id) || empty($medical_condition) || empty($urgency_level)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }

    $medicine_check = $conn->prepare("SELECT medicine_name, stock_quantity FROM medicine_inventory WHERE id = ? AND is_active = 1");
    $medicine_check->bind_param("i", $medicine_id);
    $medicine_check->execute();
    $medicine_result = $medicine_check->get_result();
    
    if ($medicine_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Selected medicine is not available.']);
        $medicine_check->close();
        exit;
    }
    
    $medicine = $medicine_result->fetch_assoc();
    $medicine_name = $medicine['medicine_name'];
    $medicine_check->close();

    $request_number = 'MED-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $prescription_path = null;
    if (isset($_FILES['prescription']) && $_FILES['prescription']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/prescriptions/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['prescription']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($file_extension, $allowed_extensions)) {
            if ($_FILES['prescription']['size'] <= $max_file_size) {
                $filename = 'prescription_' . $resident_id . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['prescription']['tmp_name'], $file_path)) {
                    $prescription_path = 'uploads/prescriptions/' . $filename;
                } else {
                    error_log("Failed to move uploaded file: " . $_FILES['prescription']['tmp_name'] . " to " . $file_path);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'File size too large. Maximum size is 5MB.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid file format. Allowed formats: JPG, PNG, PDF, DOC, DOCX.']);
            exit;
        }
    }
    
    $stmt = $conn->prepare("
        INSERT INTO medicine_requests 
        (resident_id, request_number, medicine_name, medical_condition, urgency_level, prescription_path, additional_notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("issssss", 
        $resident_id,
        $request_number,
        $medicine_name,
        $medical_condition,
        $urgency_level,
        $prescription_path,
        $additional_notes
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Medicine request submitted successfully! Your request number: ' . $request_number,
            'request_number' => $request_number
        ]);
    } else {
        error_log("Medicine request error: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to submit request. Please try again.']);
    }
    
    $stmt->close();
    $conn->close();
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}
?>