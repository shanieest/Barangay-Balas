<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../../config/emailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredOtp = trim(str_replace(' ', '', $_POST['otp']));
    $userId = $_SESSION['temp_user_id'] ?? null;

    if (!$userId) {
        header("Location: verify-otp.php?error=Session expired. Please log in again.");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if ($user['otp_code'] === $enteredOtp && strtotime($user['otp_expires_at']) > time()) {

            // Mark OTP as verified
            $updateStmt = $conn->prepare("UPDATE admin_users SET otp_verified = 1 WHERE id = ?");
            $updateStmt->bind_param("i", $userId);
            $updateStmt->execute();

            // Promote to full session (like normal login)
            $_SESSION['admin_id']  = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = trim($user['first_name']) . ' ' . trim($user['last_name']);

            // Normalize role
            $roleMap = [
                'admin' => 'Admin',
                'official' => 'Official',
                'social worker' => 'Social Worker'
            ];
            $roleLower = strtolower(trim($user['role']));
            $_SESSION['role'] = $roleMap[$roleLower] ?? 'Official';

            // Optional: clear temporary session
            unset($_SESSION['temp_user_id'], $_SESSION['temp_username']);

            // Log activity
            logActivity($userId, "User logged in via OTP verification");

            // Redirect to dashboard
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            header("Location: verify-otp.php?error=Invalid or expired OTP");
            exit();
        }
    } else {
        header("Location: verify-otp.php?error=Invalid session.");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0033cc 0%, #E63946 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --danger-gradient: linear-gradient(135deg, #851317ff 0%, #901b1bff 100%);
            --card-shadow: 0 8px 30px rgba(0,0,0,0.08);
            --hover-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 420px;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 40px 30px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-5px);
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo h1 {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        h2 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 22px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.5;
        }

        .otp-form {
            margin-top: 20px;
        }

        .otp-input {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid #e1e5eb;
            border-radius: 10px;
            font-size: 16px;
            text-align: center;
            letter-spacing: 8px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
        }

        .otp-input:focus {
            outline: none;
            border-color: #0033cc;
            box-shadow: 0 0 0 3px rgba(0, 51, 204, 0.1);
        }

        .verify-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .verify-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 51, 204, 0.2);
        }

        .verify-btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: var(--danger-gradient);
            color: white;
            padding: 12px 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            text-align: center;
        }

        .resend-option {
            margin-top: 25px;
            font-size: 14px;
            color: #666;
        }

        .resend-link {
            color: #0033cc;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .resend-link:hover {
            color: #E63946;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #999;
        }

        @media (max-width: 480px) {
            .card {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo">
                <h1>OTP verification</h1>
            </div>
            
            <h2>Enter Verification Code</h2>
            <p class="subtitle">We've sent a 6-digit code to your registered email address. Please enter it below to verify your identity.</p>
            
            <form method="POST" class="otp-form">
                <input type="text" name="otp" class="otp-input" placeholder="• • • • • •" maxlength="7" required autofocus>
                <button type="submit" class="verify-btn">Verify Code</button>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>
            </form>
            
            <div class="footer">
            <div class="text-muted">Copyright &copy; Barangay Balas <?php echo date('Y'); ?></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.querySelector('.otp-input');
            
            // Format OTP input with spaces for better readability
            otpInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '');
                
                // Remove any non-digit characters
                value = value.replace(/\D/g, '');
                
                if (value.length > 6) {
                    value = value.substring(0, 6);
                }
                
                // Add space after every 3 characters
                if (value.length > 3) {
                    value = value.substring(0, 3) + ' ' + value.substring(3);
                }
                
                e.target.value = value;
            });
            
            // Allow only numbers but enable paste functionality
            otpInput.addEventListener('keydown', function(e) {
                // Allow: backspace, delete, tab, escape, enter, arrows
                if ([46, 8, 9, 27, 13, 110].includes(e.keyCode) || 
                    // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                    (e.keyCode === 65 && e.ctrlKey === true) || 
                    (e.keyCode === 67 && e.ctrlKey === true) ||
                    (e.keyCode === 86 && e.ctrlKey === true) ||
                    (e.keyCode === 88 && e.ctrlKey === true) ||
                    // Allow: home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    return;
                }
                
                // Ensure that it is a number and stop the keypress if not
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
            
            // Handle paste event to clean up the pasted content
            otpInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                const numbersOnly = pastedData.replace(/\D/g, '').substring(0, 6);
                
                // Format the pasted numbers
                let formattedValue = numbersOnly;
                if (numbersOnly.length > 3) {
                    formattedValue = numbersOnly.substring(0, 3) + ' ' + numbersOnly.substring(3);
                }
                
                this.value = formattedValue;
            });
        });
    </script>
</body>
</html>