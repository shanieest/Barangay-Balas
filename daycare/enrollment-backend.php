<?php
// daycare/enrollment-backend.php
require_once '../config/db.php';
require_once __DIR__ . '/../config/emailer.php';
require_once __DIR__ . '/../email_templates/enrollment_status.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Required fields check
    $required = ['childFirstName', 'childLastName', 'guardianName', 'email'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(["success" => false, "message" => "Missing required field: $field"]);
            exit;
        }
    }

    // Sanitize input
    $childFirstName = trim($_POST['childFirstName']);
    $childMiddleName = trim($_POST['childMiddleName'] ?? '');
    $childLastName = trim($_POST['childLastName']);
    $sex = trim($_POST['sex'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $guardian = trim($_POST['guardian'] ?? '');
    $relationship = trim($_POST['relationship'] ?? '');
    $firstLanguage = trim($_POST['firstLanguage'] ?? '');
    $secondaryLanguage = trim($_POST['secondaryLanguage'] ?? '');
    $guardianName = trim($_POST['guardianName']);
    $email = trim($_POST['email']);
    $motherName = trim($_POST['motherName'] ?? '');
    $motherAddress = trim($_POST['motherAddress'] ?? '');
    $motherOccupation = trim($_POST['motherOccupation'] ?? '');
    $motherContact = trim($_POST['motherContact'] ?? '');
    $fatherName = trim($_POST['fatherName'] ?? '');
    $fatherAddress = trim($_POST['fatherAddress'] ?? '');
    $fatherOccupation = trim($_POST['fatherOccupation'] ?? '');
    $fatherContact = trim($_POST['fatherContact'] ?? '');
    $emergencyName = trim($_POST['emergencyName'] ?? '');
    $emergencyRelationship = trim($_POST['emergencyRelationship'] ?? '');
    $emergencyContact = trim($_POST['emergencyContact'] ?? '');
    $emergencyOccupation = trim($_POST['emergencyOccupation'] ?? '');

    // Calculate current school year
    $currentYear = date('Y');
    $currentMonth = date('n');
    $schoolYear = ($currentMonth >= 6)
        ? "$currentYear-" . ($currentYear + 1)
        : ($currentYear - 1) . "-$currentYear";

    $stmt = $conn->prepare("INSERT INTO daycare_enrollments (
        child_first_name, child_middle_name, child_last_name, sex, address, birthday, guardian, relationship_to_child,
        first_language, secondary_language, guardian_name, email, 
        mother_name, mother_address, mother_occupation, mother_contact,
        father_name, father_address, father_occupation, father_contact,
        emergency_name, emergency_relationship, emergency_contact, emergency_occupation, school_year
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param(
        "sssssssssssssssssssssssss",
        $childFirstName, $childMiddleName, $childLastName, $sex, $address, $birthday, $guardian, $relationship,
        $firstLanguage, $secondaryLanguage, $guardianName, $email,
        $motherName, $motherAddress, $motherOccupation, $motherContact,
        $fatherName, $fatherAddress, $fatherOccupation, $fatherContact,
        $emergencyName, $emergencyRelationship, $emergencyContact, $emergencyOccupation, $schoolYear
    );

    if ($stmt->execute()) {
        // Prepare and send email
        $childName = "$childFirstName $childLastName";
        $emailData = sendInitialConfirmationEmail($guardianName, $childName);

        // Use the sendEmail() helper instead of $mail object
        $response = sendEmail(
            $email, // recipient
            $emailData['subject'],
            $emailData['message'],
            $guardianName,
            'daycare@barangaybalas.com', // sender email
            'Barangay Balas Daycare Center' // sender name
        );

        $msg = $response['success']
            ? "Enrollment submitted successfully for S.Y. $schoolYear! Confirmation email sent to $email."
            : "Enrollment saved but email could not be sent.";

        echo json_encode(["success" => true, "message" => $msg]);
    } else {
        echo json_encode(["success" => false, "message" => "Error saving enrollment: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
