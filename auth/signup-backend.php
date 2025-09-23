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
        'idType' => 'ID type',
        'idNumber' => 'ID number'
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

    // Process file upload
    $validIdPath = '';
    if (isset($_FILES['validId']) && $_FILES['validId']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($_FILES['validId']['type'], $allowedTypes)) {
            $response['errors']['validId'] = "Only JPG, PNG, and PDF files are allowed";
        } elseif ($_FILES['validId']['size'] > $maxSize) {
            $response['errors']['validId'] = "File size exceeds 5MB limit";
        } else {
            $uploadDir = 'uploads/valid_ids/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExt = pathinfo($_FILES['validId']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('id_') . '.' . $fileExt;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['validId']['tmp_name'], $filePath)) {
                $validIdPath = $filePath;
            } else {
                $response['errors']['validId'] = "Failed to upload file";
            }
        }
    } else {
        $response['errors']['validId'] = "Valid ID is required";
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

        // Insert into residents
        $residentQuery = "INSERT INTO residents (
            first_name, last_name, middle_name, suffix, sex, birthdate, age,
            contact_number, email, house_number, purok, address,
            verification_status, resident_status, valid_id_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Active', ?)";

        $residentStmt = $conn->prepare($residentQuery);
        $residentStmt->bind_param(
            "ssssssissssss",
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
            $validIdPath
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
