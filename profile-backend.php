<?php
session_start();
require_once __DIR__ . '/includes/db.php'; 

$resident_id = $_SESSION['resident_id'] ?? null;

if (!$resident_id) {
    die("Unauthorized access.");
}


function getProfile($conn, $resident_id) {
    $sql = "SELECT * FROM residents WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $resident_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}


function updateProfile($conn, $resident_id, $data) {
    $sql = "UPDATE residents 
            SET first_name=?, middle_name=?, last_name=?, birthdate=?, sex=?, civil_status=?, 
                contact_number=?, email=?, address=?, purok=?, photo_path=?, updated_at=NOW()
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssi", 
        $data['first_name'],
        $data['middle_name'],
        $data['last_name'],
        $data['birthdate'],
        $data['sex'],
        $data['civil_status'],
        $data['contact_number'],
        $data['email'],
        $data['address'],
        $data['purok'],
        $data['photo_path'],
        $resident_id
    );
    return $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === "update_profile") {
    $data = [
        "first_name"     => $_POST['first_name'],
        "middle_name"    => $_POST['middle_name'],
        "last_name"      => $_POST['last_name'],
        "birthdate"      => $_POST['birthdate'],
        "sex"            => $_POST['sex'],
        "civil_status"   => $_POST['civil_status'],
        "contact_number" => $_POST['contact_number'],
        "email"          => $_POST['email'],
        "address"        => $_POST['address'],
        "purok"          => $_POST['purok'],
        "photo_path"     => null 
    ];

    if (updateProfile($conn, $resident_id, $data)) {
        $_SESSION['message'] = "Profile updated successfully!";
    } else {
        $_SESSION['message'] = "Error updating profile.";
    }
    header("Location: profile.php");
    exit;
}
?>
