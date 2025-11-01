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
    <link rel="stylesheet" href="assets/css/officials.css">
</head>
<body class="sb-nav-fixed">
    <?php include 'includes/navbar.php'; ?>
    
    <div id="layoutSidenav">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="layoutSidenav_content">
            <main>
                <div>
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="header-icon">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <h1 class="mb-2">Barangay Officials Management</h1>
                                <p class="mb-0 opacity-75">Manage and oversee all barangay officials in the system</p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                                    <button class="btn btn-outline-light">
                                        <i class="fas fa-calendar me-1"></i> <?= date('F j, Y') ?>
                                    </button>
                                    <button class="btn btn-outline-light" id="refreshBtn">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                            <li class="breadcrumb-item active"><i class="fas fa-users me-1"></i>Barangay Officials</li>
                        </ol>
                    </nav>

                    <!-- Stats Cards -->
                    <div class="row" id="statsContainer" style="display: none;">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stats-card">
                                <div class="stats-number" id="totalOfficials">0</div>
                                <div class="stats-label">Total Officials</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stats-card">
                                <div class="stats-number" id="activeOfficials">0</div>
                                <div class="stats-label">Active Officials</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stats-card">
                                <div class="stats-number" id="adminCount">0</div>
                                <div class="stats-label">Administrators</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stats-card">
                                <div class="stats-number" id="officialCount">0</div>
                                <div class="stats-label">Officials</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search and Filter Bar -->
                    <div class="row mb-4" id="searchFilterContainer" style="display: none;">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control search-box border-start-0" placeholder="Search officials by name, position, or role..." id="searchInput">
                            </div>
                        </div>
                        <div class="col-md-4 mt-2 mt-md-0">
                            <div class="d-flex gap-2">
                                <select class="form-select filter-btn" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                                <select class="form-select filter-btn" id="roleFilter">
                                    <option value="">All Roles</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Official">Official</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-list-alt me-2"></i>Officials List</span>
                            <?php if (canModify()) { ?>
                            <button class="btn btn-light btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addOfficialModal">
                                <i class="fas fa-user-plus me-1"></i> Add New Official
                            </button>
                            <?php } ?>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="officialsTable" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <th>Official</th>
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
                                                <div class="loading-spinner">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                    <div class="mt-2">Loading officials...</div>
                                                </div>
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
        
        document.getElementById('refreshBtn').addEventListener('click', function() {
            this.classList.add('fa-spin');
            setTimeout(() => {
                this.classList.remove('fa-spin');
                window.location.reload();
            }, 500);
        });
    </script>
    <script src="assets/js/officials.js"></script>
</body>
</html>