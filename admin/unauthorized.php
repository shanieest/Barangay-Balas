<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Unauthorized Access</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <i class="fas fa-ban fa-5x text-danger mb-4"></i>
                <h1>Unauthorized Access</h1>
                <p class="text-muted">You don't have permission to access this page.</p>
                <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>