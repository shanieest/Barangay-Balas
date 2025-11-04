<?php
// admin/social_worker.php
require_once  '../includes/auth.php';
require_once '../includes/db.php';

requireAuth();
requireCanModify();

// Check if user has access to social worker management
if (!isAdmin() && !isOfficial()) {
    header('Location: dashboard.php');
    exit();
}

// Handle session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

// Fetch all social workers
$sql = "SELECT au.*, sw.department, sw.specialization
        FROM admin_users au
        LEFT JOIN social_workers sw ON au.id = sw.admin_user_id
        WHERE au.role = 'Social Worker'
        ORDER BY au.last_name, au.first_name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Social Workers | Barangay Balas Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/socialWorker.css">
</head>
<body class="sb-nav-fixed sb-sidenav-toggled">
    <?php include '../includes/navbar.php'; ?>
    
    <div id="layoutSidenav">
        <?php include '../includes/sidebar.php'; ?>
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-3 px-md-4 py-4">
                    <div class="dashboard-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="h2 mb-2"><i class="fas fa-hands-helping me-2"></i>Manage Social Workers</h1>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="d-flex flex-wrap justify-content-md-end">
                                    <button class="btn btn-outline-light me-2 mb-2"><i class="fas fa-calendar me-1"></i> <?= date('F j, Y') ?></button>
                                    <button class="btn btn-outline-light mb-2" id="refreshBtn"><i class="fas fa-sync-alt"></i> Refresh</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Badge -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="role-badge">
                                <span class="badge bg-info">
                                    <i class="fas fa-user-shield me-1"></i>
                                    Admin/Official - Social Worker Management
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                        <?= nl2br(htmlspecialchars($message)) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-list-alt me-2"></i>Social Workers List</span>
                            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="fas fa-user-plus me-1"></i> Add New Social Worker
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- FIX: Added responsive wrapper with horizontal scrolling -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Contact</th>
                                            <th>Position</th>
                                            <th>Department</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $count = 1;
                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()): 
                                                $fullName = trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']);
                                                $statusBadge = $row['status'] === 'Active' 
                                                    ? '<span class="badge bg-success">Active</span>' 
                                                    : '<span class="badge bg-secondary">Inactive</span>';
                                        ?>
                                        <tr>
                                            <td><?= $count++ ?></td>
                                            <td><?= htmlspecialchars($fullName) ?></td>
                                            <td><?= htmlspecialchars($row['username']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= htmlspecialchars($row['contact_number']) ?></td>
                                            <td><?= htmlspecialchars($row['position']) ?></td>
                                            <td><?= htmlspecialchars($row['department'] ?? 'N/A') ?></td>
                                            <td><?= $statusBadge ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary btn-action" 
                                                        onclick="editSocialWorker(<?= htmlspecialchars(json_encode($row)) ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-action" 
                                                        onclick="deleteSocialWorker(<?= $row['id'] ?>, '<?= htmlspecialchars($fullName) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; 
                                        } else { ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <i class="fas fa-users fa-2x text-muted mb-3"></i><br>
                                                No social workers found. Click "Add New Social Worker" to get started.
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>
    
    <?php include "../modals/socialWorkerModal.php"; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/socialWorker.js"></script>
    

</body>
</html>