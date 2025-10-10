<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = [
        'firstName' => 'First name',
        'lastName' => 'Last name',
        'middleName' => 'Middle name',
        'sex' => 'Sex',
        'birthdate' => 'Birthdate',
        'contactNumber' => 'Contact number',
        'houseNumber' => 'House number',
        'purok' => 'Purok',
        'email' => 'Email',
        'password' => 'Password',
        'confirmPassword' => 'Confirm password',
        'idType' => 'ID type'
    ];

    foreach ($required as $field => $name) {
        if (empty($_POST[$field])) {
            $response['errors'][$field] = "$name is required";
        }
    }

    if (isset($_POST['password'], $_POST['confirmPassword']) && $_POST['password'] !== $_POST['confirmPassword']) {
        $response['errors']['confirmPassword'] = "Passwords do not match";
    }

    if (isset($_POST['password']) && strlen($_POST['password']) < 8) {
        $response['errors']['password'] = "Password must be at least 8 characters";
    }

    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = "Invalid email format";
    }

    $birthdate = date('Y-m-d', strtotime($_POST['birthdate']));
    if (!$birthdate) {
        $response['errors']['birthdate'] = "Invalid birthdate format";
    }

    // Calculate age
    $birthdateObj = new DateTime($birthdate);
    $today = new DateTime();
    $age = $today->diff($birthdateObj)->y;

    // Generate address
    $houseNumber = $_POST['houseNumber'] ?? '';
    $purok = $_POST['purok'] ?? '';
    $address = "House $houseNumber, $purok, Balas, Mexico, Pampanga, Philippines";

    // Process file uploads for both ID photos
    $validIdPath1 = '';
    $validIdPath2 = '';
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    $maxSize = 5 * 1024 * 1024;

    // Upload Directory
    $uploadDir = 'uploads/valid_ids/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Process First ID Photo
    if (isset($_FILES['validId1']) && $_FILES['validId1']['error'] === UPLOAD_ERR_OK) {
        if (!in_array($_FILES['validId1']['type'], $allowedTypes)) {
            $response['errors']['validId1'] = "Only JPG, PNG, and PDF files are allowed for ID Photo 1";
        } elseif ($_FILES['validId1']['size'] > $maxSize) {
            $response['errors']['validId1'] = "File size exceeds 5MB limit for ID Photo 1";
        } else {
            $fileExt1 = pathinfo($_FILES['validId1']['name'], PATHINFO_EXTENSION);
            $fileName1 = uniqid('id1_') . '.' . $fileExt1;
            $filePath1 = $uploadDir . $fileName1;

            if (move_uploaded_file($_FILES['validId1']['tmp_name'], $filePath1)) {
                $validIdPath1 = $filePath1;
            } else {
                $response['errors']['validId1'] = "Failed to upload ID Photo 1";
            }
        }
    } else {
        $response['errors']['validId1'] = "ID Photo 1 is required";
    }

    // Process Second ID Photo
    if (isset($_FILES['validId2']) && $_FILES['validId2']['error'] === UPLOAD_ERR_OK) {
        if (!in_array($_FILES['validId2']['type'], $allowedTypes)) {
            $response['errors']['validId2'] = "Only JPG, PNG, and PDF files are allowed for ID Photo 2";
        } elseif ($_FILES['validId2']['size'] > $maxSize) {
            $response['errors']['validId2'] = "File size exceeds 5MB limit for ID Photo 2";
        } else {
            $fileExt2 = pathinfo($_FILES['validId2']['name'], PATHINFO_EXTENSION);
            $fileName2 = uniqid('id2_') . '.' . $fileExt2;
            $filePath2 = $uploadDir . $fileName2;

            if (move_uploaded_file($_FILES['validId2']['tmp_name'], $filePath2)) {
                $validIdPath2 = $filePath2;
            } else {
                $response['errors']['validId2'] = "Failed to upload ID Photo 2";
            }
        }
    } else {
        $response['errors']['validId2'] = "ID Photo 2 is required";
    }

    if (!empty($response['errors'])) {
        $response['message'] = "Please correct the errors below";
        echo json_encode($response);
        exit();
    }

    // Assign to variables for binding
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $middleName = $_POST['middleName'];
    $suffix = $_POST['suffix'] ?? '';
    $sex = $_POST['sex'];
    $contactNumber = $_POST['contactNumber'];
    $email = $_POST['email'];
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT id FROM residents WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            throw new Exception("Email address already registered");
        }

        // Insert into residents with both ID paths
        $residentQuery = "INSERT INTO residents (
            first_name, last_name, middle_name, suffix, sex, birthdate, age,
            contact_number, email, house_number, purok, address,
            verification_status, resident_status, valid_id_path, valid_id_path_2
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Active', ?, ?)";

        $residentStmt = $conn->prepare($residentQuery);
        $residentStmt->bind_param(
            "ssssssississss",
            $firstName,
            $lastName,
            $middleName,
            $suffix,
            $sex,
            $birthdate,
            $age,
            $contactNumber,
            $email,
            $houseNumber,
            $purok,
            $address,
            $validIdPath1,
            $validIdPath2
        );

        if (!$residentStmt->execute()) {
            throw new Exception("Error registering resident: " . $residentStmt->error);
        }

        $residentId = $conn->insert_id;

        // Insert into resident_accounts
        $accountQuery = "INSERT INTO resident_accounts (
            resident_id, email, password, account_status, date_requested
        ) VALUES (?, ?, ?, 'Pending', NOW())";

        $accountStmt = $conn->prepare($accountQuery);
        $accountStmt->bind_param("iss", $residentId, $email, $hashedPassword);

        if (!$accountStmt->execute()) {
            throw new Exception("Error creating account: " . $accountStmt->error);
        }

        // Commit transaction
        $conn->commit();

        $response['success'] = true;
        $response['message'] = "Registration successful! Your account is pending approval.";
        $_SESSION['registration_success'] = $response['message'];
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = "Invalid request method";
}

echo json_encode($response);
?>