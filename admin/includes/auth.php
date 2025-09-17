<?php
//auth.php
session_start();

require_once __DIR__ . '/db.php';

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function getUserId() {
    return $_SESSION['admin_id'] ?? null;
}

function getUserData() {
    if (!isLoggedIn()) return null;
    
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function login($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, password FROM admin_users WHERE username = ? AND status = 'Active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            logActivity($user['id'], "Logged in");
            return true;
        }
    }
    
    return false;
}

function logout() {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

function logActivity($userId, $activity) {
    global $conn;
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $activity, $ip, $userAgent);
    $stmt->execute();
}
?>
