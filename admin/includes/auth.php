<?php
// admin/includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    session_start();
}

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

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function isAdmin() {
    return strcasecmp(getUserRole() ?? '', 'Admin') === 0;
}

function isOfficial() {
    return strcasecmp(getUserRole() ?? '', 'Official') === 0;
}

function isSocialWorker() {
    return strcasecmp(getUserRole() ?? '', 'Social Worker') === 0;
}

function canModify() {
    return isAdmin();
}

function requireCanModify() {
    if (!canModify()) {
        header('Location: unauthorized.php');
        exit();
    }
}

function canAccessDaycare() {
    return isAdmin() || isSocialWorker();
}

function requireDaycareAccess() {
    if (!canAccessDaycare()) {
        header('Location: unauthorized.php');
        exit();
    }
}

function getUserData() {
    if (!isLoggedIn()) return null;

    global $conn;
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function login($username, $password) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT id, username, password, role, first_name, last_name 
        FROM admin_users 
        WHERE username = ? AND status = 'Active'
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Normalize role
            $roleMap = [
                'admin' => 'Admin',
                'official' => 'Official',
                'social worker' => 'Social Worker'
            ];
            
            $roleLower = strtolower(trim($user['role']));
            $role = $roleMap[$roleLower] ?? 'Official';

            // Set session
            $_SESSION['admin_id']  = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role']      = $role;
            $_SESSION['full_name'] = trim($user['first_name']) . ' ' . trim($user['last_name']);

            // Update last login
            $updateStmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();

            // Log activity
            logActivity($user['id'], "Logged in as " . $role);

            return true;
        }
    }

    return false;
}

function logout() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
    header("Location: login.php");
    exit();
}

function logActivity($userId, $activity) {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    $stmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, activity, ip_address, user_agent) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("isss", $userId, $activity, $ip, $ua);
    $stmt->execute();
}
?>