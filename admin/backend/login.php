<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../../config/emailer.php';

if (!function_exists('generateOTP')) {
    function generateOTP($length = 6) {
        $digits = '0123456789';
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= $digits[random_int(0, 9)];
        }
        return $otp;
    }
}

if (!function_exists('sendOTP')) {
    function sendOTP($email, $otp, $firstName = '') {
        if (!empty($email)) {
            $subject = 'Your OTP Code - Barangay Balas';
            $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .otp-code { font-size: 24px; font-weight: bold; color: #2c3e50; padding: 10px; background: #f8f9fa; text-align: center; margin: 20px 0; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>OTP Verification Code</h2>
                        <p>Hello {$firstName},</p>
                        <p>Your OTP verification code is:</p>
                        <div class='otp-code'>{$otp}</div>
                        <p>This code will expire in 5 minutes.</p>
                        <p>If you did not request this code, please ignore this email.</p>
                        <br>
                        <p>Best regards,<br>Barangay Balas</p>
                    </div>
                </body>
                </html>
            ";
            
            // Use your emailer function
            $result = sendEmail($email, $subject, $body, $firstName);
            
            if ($result['success']) {
                return true;
            } else {
                error_log("OTP Email failed: " . $result['message']);
                return false;
            }
        }
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT id, username, password, first_name, last_name, role, email
        FROM admin_users
        WHERE username = ? AND status = 'Active'
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Generate OTP
            $otp = generateOTP();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            // Save OTP
            $updateStmt = $conn->prepare("
                UPDATE admin_users
                SET otp_code = ?, otp_expires_at = ?, otp_verified = 0
                WHERE id = ?
            ");
            $updateStmt->bind_param("ssi", $otp, $expiresAt, $user['id']);
            
            if ($updateStmt->execute()) {
                // Send OTP via email
                $sent = sendOTP($user['email'], $otp, $user['first_name']);

                if ($sent) {
                    $_SESSION['temp_user_id'] = $user['id'];
                    $_SESSION['temp_username'] = $user['username'];
                    logActivity($user['id'], "OTP generated for login");

                    // Redirect to OTP verification page
                    header("Location: ../pages/verify-otp.php");
                    exit();
                } else {
                    header("Location: ../index.php?error=Failed to send OTP. Please try again.");
                    exit();
                }
            } else {
                header("Location: ../index.php?error=Database error. Please try again.");
                exit();
            }
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