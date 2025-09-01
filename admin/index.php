<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Balas - Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-red: #990000;
            --primary-blue:  #0033cc;
            --primary-yellow: #FFD166;
            --light-blue: #A8DADC;
            --light-gray: #F1FAEE;
        }
        
        body {
            background-color: var(--light-gray);
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-red));
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-form {
            padding: 30px;
        }
        
        .btn-login {
            background-color: var(--primary-red);
            border: none;
            padding: 10px 30px;
            font-weight: 600;
        }
        
        .btn-login:hover {
            background-color: #C1121F;
        }
        
        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.25rem rgba(29, 53, 87, 0.25);
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-container">
                    <div class="login-header">
                        <img src="assets\img\balas-logo.png" alt="Barangay Balas Logo" class="logo">
                        <h3>Barangay Balas</h3>
                        <p class="mb-0">Administrator Panel</p>
                    </div>
                    <div class="login-form">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-login text-white">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>