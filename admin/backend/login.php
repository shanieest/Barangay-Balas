<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("
        SELECT id, username, password, first_name, last_name, role 
        FROM admin_users 
        WHERE username = ? AND status = 'Active'
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id']  = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role']      = $user['role'];   // very important

            error_log("User logged in with role: " . $_SESSION['role']);

            $update = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $update->bind_param("i", $user['id']);
            $update->execute();

            logActivity($user['id'], "Logged in as " . $user['role']);
            
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            header("Location: ../index.php?error=Invalid username or password");
            exit();
        }
    } else {
        header("Location: ../index.php?error=Invalid username or password");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
