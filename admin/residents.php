<?php 

require_once __DIR__ . '/includes/auth.php';
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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
        }
        .img-preview {
            max-height: 200px;
            max-width: 100%;
        }
        .note-textarea {
            min-height: 100px;
        }
        .note-textarea.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .account-notes {
            margin-top: 1rem;
        }
        .account-notes .notes-content {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            white-space: pre-wrap;
        }
        .account-status-badge {
            font-size: 0.75rem;
        }
        .account-approved { background-color: #28a745; }
        .account-pending { background-color: #ffc107; color: #212529; }
        .account-disapproved { background-color: #dc3545; }
        .account-details {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            margin-top: 1rem;
        }
        .pagination-info {
            margin-top: 0.5rem;
        }
        .search-box {
            max-width: 300px;
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
                    <h1 class="mt-4">Residents Management</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Residents</li>
                    </ol>
                    
                    <!-- Tabs for Residents and Account Requests -->
                    <ul class="nav nav-tabs mb-4" id="residentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="verified-tab" data-bs-toggle="tab" data-bs-target="#verified-residents" type="button" role="tab">Verified Residents</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="requests-tab" data-bs-toggle="tab" data-bs-target="#account-requests" type="button" role="tab">Account Requests <span class="badge bg-danger" id="pending-count">0</span></button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="residentTabsContent">
                        <!-- Verified Residents Tab -->
                        <div class="tab-pane fade show active" id="verified-residents" role="tabpanel">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-users me-1"></i>
                                            Verified Residents
                                        </div>
                                        <div>
                                            <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addResidentModal">
                                                <i class="fas fa-plus me-1"></i> Add Resident
                                            </button>
                                            <button class="btn btn-success btn-sm" onclick="exportResidents()">
                                                <i class="fas fa-file-excel me-1"></i> Export
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="input-group search-box">
                                                <input type="text" class="form-control" id="residentSearch" placeholder="Search residents...">
                                                <button class="btn btn-outline-secondary" type="button" id="searchResidentBtn">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="residentsTable" class="table table-striped table-bordered" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Contact</th>
                                                    <th>Birthdate</th>
                                                    <th>Account Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div class="pagination-info"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <nav aria-label="Residents pagination">
                                                <ul class="pagination justify-content-end">
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
                            <div class="card mb-4">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-user-clock me-1"></i>
                                            Resident Account Requests
                                        </div>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                Filter: <span id="currentFilter">All</span>
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
                                    <div class="table-responsive">
                                        <table id="requestsTable" class="table table-striped table-bordered" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Contact</th>
                                                    <th>Date Requested</th>
                                                    <th>Status</th>
                                                    <th>Processed By</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div class="pagination-info"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <nav aria-label="Requests pagination">
                                                <ul class="pagination justify-content-end">
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
            
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

 <?php include 'modals/residentsModal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/residents.js"></script>
</body>
</html> 