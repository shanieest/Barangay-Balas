<?php
require '../../config/db.php';
require_once '../../auth/auth.php'; 

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_SESSION['user_id'])) {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. Please login first."
            ]);
        } else {
            echo "<h3>Unauthorized request. Please login first.</h3>";
        }
        exit;
    }

    $resident_id     = intval($_SESSION['user_id']); // From session
    $document_type_id= intval($_POST['document_type_id']);
    $first_name      = trim($_POST['first_name']);
    $middle_name     = trim($_POST['middle_name']);
    $last_name       = trim($_POST['last_name']);
    $houseno         = trim($_POST['houseno']);
    $purok           = trim($_POST['purok']);
    $civil_status    = trim($_POST['civil_status']);
    $sex             = trim($_POST['sex']);
    $birthdate       = trim($_POST['birthdate']);
    $age             = intval($_POST['age']);
    $email           = trim($_POST['email']);
    $purpose         = trim($_POST['purpose']);
    $shipping_method = trim($_POST['shipping_method']);

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    $sql = "INSERT INTO document_requests (
                resident_id, document_type_id, first_name, middle_name, last_name,
                houseno, purok, civil_status, sex, birthdate,
                age, email, purpose, shipping_method
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param(
            'iissssssssisss',
            $resident_id,
            $document_type_id,
            $first_name,
            $middle_name,
            $last_name,
            $houseno,
            $purok,
            $civil_status,
            $sex,
            $birthdate,
            $age,
            $email,
            $purpose,
            $shipping_method
        );

        if ($stmt->execute()) {
            $request_id = $stmt->insert_id;

            // Log activity
            $user_id  = $resident_id;
            $activity = "Requested document (ID: $document_type_id)";
            $ip       = $_SERVER['REMOTE_ADDR'] ?? null;
            $agent    = $_SERVER['HTTP_USER_AGENT'] ?? null;

            $log = $conn->prepare("INSERT INTO activity_logs (user_id, activity, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $log->bind_param('isss', $user_id, $activity, $ip, $agent);
            $log->execute();
            $log->close();

            if ($isAjax) {
                echo json_encode([
                    "status"  => "success",
                    "message" => "Request submitted successfully",
                    "request_id" => $request_id
                ]);
            } else {
                echo "<h3> Request submitted successfully!</h3>";
                echo "<p>Request ID: {$request_id}</p>";
                echo "<p>Name: {$first_name} {$middle_name} {$last_name}</p>";
                echo "<p>Document Type ID: {$document_type_id}</p>";
                echo "<p>Purpose: {$purpose}</p>";
            }
        } else {
            if ($isAjax) {
                echo json_encode(["status" => "error", "message" => "Failed to save request."]);
            } else {
                echo "<h3> Failed to save request.</h3>";
            }
        }
        $stmt->close();
    } else {
        if ($isAjax) {
            echo json_encode(["status" => "error", "message" => "System error (prepare failed)."]);
        } else {
            echo "<h3>System error (prepare failed).</h3>";
        }
    }

    $conn->close();
}
?>
