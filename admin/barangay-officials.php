<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Officials | Barangay Balas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
        .badge {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }
        .alert {
            transition: opacity 0.15s linear;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <?php include 'includes/navbar.php'; ?>
    
    <div id="layoutSidenav">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Barangay Officials Management</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Barangay Officials</li>
                    </ol>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-users me-1"></i>
                                    Officials List
                                </div>
                                <?php if (canModify()) { ?>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addOfficialModal">
                                    <i class="fas fa-plus me-1"></i> Add New Official
                                </button>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="officialsTable" class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Name</th>
                                            <th>Position & Role</th>
                                            <th>Contact Information</th>
                                            <th style="width: 100px;">Status</th>
                                            <?php if (canModify()) { ?>
                                            <th style="width: 120px;">Actions</th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="<?php echo canModify() ? '6' : '5'; ?>" class="text-center">
                                                <i class="fas fa-spinner fa-spin"></i> Loading officials...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    
    <?php include 'modals/officialsModal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js"></script>
    <script>
        window.USER_CAN_MODIFY = <?php echo canModify() ? 'true' : 'false'; ?>;
    </script>
    <script src="assets/js/officials.js"></script>
</body>
</html>