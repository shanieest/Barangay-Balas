<?php
//login.php backend logic
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT id, username, password, first_name, last_name FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            $conn->query("UPDATE admin_users SET last_login = NOW() WHERE id = {$user['id']}");
            
            logActivity($conn, $user['id'], "Logged in to the system");
            
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: index.php?error=Invalid username or password");
            exit();
        }
    } else {
        header("Location: index.php?error=Invalid username or password");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>