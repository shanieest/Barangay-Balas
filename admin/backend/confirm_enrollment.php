<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_once '../../config/emailer.php';
require_once '../../email_templates/enrollment_status.php';

requireAuth();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$enrollmentId = $data['id'] ?? null;

if (!$enrollmentId) {
    echo json_encode(['success' => false, 'message' => 'Enrollment ID is required']);
    exit;
}

// Get enrollment details
$stmt = $conn->prepare("SELECT * FROM daycare_enrollments WHERE id = ? AND confirmed = 0");
$stmt->bind_param("i", $enrollmentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Enrollment not found or already confirmed']);
    exit;
}

$enrollment = $result->fetch_assoc();

// Get social worker details
$socialWorkerStmt = $conn->prepare("
    SELECT au.*, sw.email_signature, sw.department 
    FROM admin_users au
    LEFT JOIN social_workers sw ON au.id = sw.admin_user_id
    WHERE au.id = ?
");
$socialWorkerStmt->bind_param("i", $_SESSION['admin_id']);
$socialWorkerStmt->execute();
$socialWorker = $socialWorkerStmt->get_result()->fetch_assoc();

// Update enrollment status
$updateStmt = $conn->prepare("
    UPDATE daycare_enrollments 
    SET confirmed = 1, confirmed_by = ?, confirmed_at = NOW()
    WHERE id = ?
");
$updateStmt->bind_param("ii", $_SESSION['admin_id'], $enrollmentId);

if (!$updateStmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to update enrollment status']);
    exit;
}

// Prepare email content
$childName = trim($enrollment['child_first_name'] . ' ' . $enrollment['child_middle_name'] . ' ' . $enrollment['child_last_name']);
$guardianName = $enrollment['guardian_name'];
$guardianEmail = $enrollment['email'];
$schoolYear = $enrollment['school_year'];

$swFullName = trim($socialWorker['first_name'] . ' ' . $socialWorker['last_name']);
$swEmail = $socialWorker['email'];
$swPosition = $socialWorker['position'] ?? 'Social Worker';
$swDepartment = $socialWorker['department'] ?? 'Daycare Center';

// Generate email template
$emailData = generateEnrollmentConfirmationEmail(
    $guardianName,
    $childName,
    $schoolYear,
    $swFullName,
    $swPosition,
    $swDepartment,
    $swEmail
);

// Send email
$response = sendEmail(
    $guardianEmail,                 
    $emailData['subject'],         
    $emailData['body'],             
    $guardianName,                
    $swEmail,                      
    $swFullName                     
);

if ($response['success']) {
    echo json_encode(['success' => true, 'message' => 'Enrollment confirmed and email sent successfully.']);
} else {
    error_log("Email failed: " . $response['message']);
    echo json_encode(['success' => true, 'message' => 'Enrollment confirmed but email failed to send.']);
}

$conn->close();
?>
