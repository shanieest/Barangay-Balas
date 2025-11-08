<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Balas - Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .logo-container {
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f0f0f0;
        }

        .logo-container h1 {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.5;
        }

        .login-form {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid #e1e5eb;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #0033cc;
            box-shadow: 0 0 0 3px rgba(0, 51, 204, 0.1);
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
        }

        .login-btn {
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
            margin-top: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 51, 204, 0.2);
        }

        .login-btn:active {
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

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #999;
        }

        @media (max-width: 480px) {
            .card {
                padding: 30px 20px;
            }
            
            .logo-container h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="logo-container">
                <img src="assets/img/balas-logo.png" alt="Barangay Balas Logo" class="logo">
                <h1>Barangay Balas</h1>
                <p class="subtitle">Administrator Panel</p>
            </div>
            
            <div class="login-form">
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>
                
                <form action="backend/login.php" method="POST">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-container">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="login-btn">Login</button>
                </form>
                
                <div class="footer">
                    &copy; Barangay Balas <?php echo date('Y'); ?>. All rights reserved.
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle eye icon
                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        });
    </script>
</body>
</html>