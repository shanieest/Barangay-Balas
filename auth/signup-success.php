<?php
session_start();
if (!isset($_SESSION['registration_success'])) {
    header('Location: signup.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registration Successful</title>

     <style>
        :root {
            --primary-red: #990000;
            --primary-blue: #0033cc;
            --primary-blue-light: #3d6bff;
            --primary-blue-lighter: #e6ecff;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .auth-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-top: 5px solid var(--primary-red);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-header h2 {
            color: var(--primary-blue);
            font-weight: 600;
        }
        .auth-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
        .form-control {
            height: 45px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: var(--primary-blue-light);
            box-shadow: 0 0 0 0.2rem var(--primary-blue-lighter);
        }
        .btn-auth {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s;
        }
        .btn-auth:hover {
            background-color: var(--primary-blue-light);
            transform: translateY(-2px);
        }
        .btn-auth:active {
            transform: translateY(0);
        }
        .auth-footer {
            text-align: center;
            margin-top: 20px;
        }
        .auth-footer a {
            color: var(--primary-blue);
            text-decoration: none;
        }
        .auth-footer a:hover {
            text-decoration: underline;
            color: var(--primary-red);
        }
        .nav-tabs {
            border-bottom: none;
            margin-bottom: 25px;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #7f8c8d;
            font-weight: 600;
            padding: 10px 20px;
        }
        .nav-tabs .nav-link.active {
            color: var(--primary-blue);
            background: none;
            border-bottom: 3px solid var(--primary-red);
        }
        .custom-file-label::after {
            content: "Browse";
            background-color: var(--primary-blue-lighter);
            color: var(--primary-blue);
            border-left: 1px solid #ced4da;
        }
        .id-preview {
            max-width: 100%;
            margin-top: 10px;
            display: none;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .form-section-title {
            color: var(--primary-red);
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .datepicker table tr td.active.active, 
        .datepicker table tr td.active.highlighted.active, 
        .datepicker table tr td.active.highlighted:active, 
        .datepicker table tr td.active:active {
            background-color: var(--primary-blue);
            background-image: none;
        }
        .datepicker table tr td.today {
            background-color: var(--primary-blue-lighter);
        }
        .input-group-text {
            background-color: var(--primary-blue-lighter);
            color: var(--primary-blue);
            border-color: #ced4da;
        }
        .form-check-input:checked {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        .alert-primary {
            background-color: var(--primary-blue-lighter);
            border-color: var(--primary-blue-lighter);
            color: var(--primary-blue);
        }
    </style>

</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <img src="assets/img/balas-logo.png" alt="Barangay Balas Logo" class="auth-logo">
                <h2>Registration Submitted</h2>
            </div>
            
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['registration_success']; ?>
            </div>
            
            <p>Your registration has been submitted for review. You will receive an email notification once your account has been approved by our administrator.</p>
            
            <div class="text-center mt-4">
                <a href="signup.php" class="btn btn-primary">Go to Login Page</a>
            </div>
        </div>
    </div>
</body>
</html>
<?php unset($_SESSION['registration_success']); ?>