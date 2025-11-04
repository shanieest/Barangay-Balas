<?php 
require_once '../includes/auth.php';
requireAuth();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents | Barangay Balas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/residents.css">
</head>
<body class="sb-nav-fixed">
    <?php include '../includes/navbar.php'; ?>
    
    <div id="layoutSidenav">
        <?php include '../includes/sidebar.php'; ?>
        
        <div id="layoutSidenav_content">
            <main>
                <div>
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="header-icon">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <h1 class="mb-2">Residents Management</h1>
                                <p class="mb-0 opacity-75">Manage verified residents and account requests</p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                                    <button class="btn btn-outline-light" onclick="location.reload()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                            <li class="breadcrumb-item active"><i class="fas fa-users me-1"></i>Residents</li>
                        </ol>
                    </nav>
                    
                    <!-- Tabs for Residents and Account Requests -->
                    <ul class="nav nav-tabs" id="residentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="verified-tab" data-bs-toggle="tab" data-bs-target="#verified-residents" type="button" role="tab">
                                <i class="fas fa-user-check me-2"></i>Verified Residents
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="requests-tab" data-bs-toggle="tab" data-bs-target="#account-requests" type="button" role="tab">
                                <i class="fas fa-user-clock me-2"></i>Account Requests 
                                <span class="badge bg-danger" id="pending-count">0</span>
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="residentTabsContent">
                        
                        <!-- Verified Residents Tab -->
                        <div class="tab-pane fade show active" id="verified-residents" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <i class="fas fa-list-alt me-2"></i>Verified Residents
                                        </div>
                                        <div>
                                            <?php if (canModify()) { ?>
                                            <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addResidentModal">
                                                <i class="fas fa-plus me-1"></i> Add Resident
                                            </button>
                                            <button class="btn btn-success btn-sm" onclick="exportResidents()">
                                                <i class="fas fa-file-excel me-1"></i> Export
                                            </button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="input-group search-box">
                                                <input type="text" class="form-control" id="residentSearch" placeholder="Search residents...">
                                                <button class="btn btn-primary" type="button" id="searchResidentBtn">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                                        <table id="residentsTable" class="table table-striped table-bordered" style="width:100%; min-width: 800px;">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Contact</th>
                                                    <th>Birthdate</th>
                                                    <th>Account Status</th>
                                                    <th>Request History</th>
                                                    <?php if (canModify()) { ?>
                                                    <th>Actions</th>
                                                    <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="pagination-info"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <nav aria-label="Residents pagination">
                                                <ul class="pagination justify-content-end mb-0">
                                                    <li class="page-item disabled" id="prevResidentPage">
                                                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                                                    </li>
                                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                                    <li class="page-item" id="nextResidentPage">
                                                        <a class="page-link" href="#">Next</a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Requests Tab -->
                        <div class="tab-pane fade" id="account-requests" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <i class="fas fa-clock me-2"></i>Resident Account Requests
                                        </div>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-filter me-1"></i>Filter: <span id="currentFilter">All</span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item filter-requests" href="#" data-status="all">All Requests</a></li>
                                                <li><a class="dropdown-item filter-requests" href="#" data-status="Pending">Pending</a></li>
                                                <li><a class="dropdown-item filter-requests" href="#" data-status="Approved">Approved</a></li>
                                                <li><a class="dropdown-item filter-requests" href="#" data-status="Disapproved">Disapproved</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                                        <table id="requestsTable" class="table table-striped table-bordered" style="width:100%; min-width: 800px;">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Contact</th>
                                                    <th>Date Requested</th>
                                                    <th>Status</th>
                                                    <th>Processed By</th>
                                                    <?php if (canModify()) { ?>
                                                    <th>Actions</th>
                                                    <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="pagination-info"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <nav aria-label="Requests pagination">
                                                <ul class="pagination justify-content-end mb-0">
                                                    <li class="page-item disabled" id="prevRequestPage">
                                                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                                                    </li>
                                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                                    <li class="page-item" id="nextRequestPage">
                                                        <a class="page-link" href="#">Next</a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>

    <?php include '../modals/residentsModal.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/residents.js"></script>
    <script>
        window.USER_CAN_MODIFY = <?php echo canModify() ? 'true' : 'false'; ?>;
    </script>
</body>
</html>