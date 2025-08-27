<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Document Requests | Barangay Balas Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7f9;
        }
        
        .bg-light-custom {
            background-color: var(--light-bg);
            border: 1px solid #e3e6e9;
        }
        
        .household-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        
        .table th {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-export {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-export:hover {
            background-color: #219653;
            color: white;
        }
        
        .action-buttons .btn {
            margin-right: 0.3rem;
        }
        
        .census-period-selector {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,0.1);
            font-weight: 600;
        }
        
        .stats-card {
            text-align: center;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            height: 100%;
        }
        
        .stats-card i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stats-card .number {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .stats-card .label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        #householdsTable tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        #householdsTable tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .resident-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .modal-header {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .resident-image-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .pagination {
            margin-bottom: 0;
        }
        
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        @media (max-width: 768px) {
            .table-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                width: 100%;
            }
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
        <h1 class="mt-4">Census Data</h1>
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Census data</li>
        </ol>

        <div class="card mb-4">
          <div class="card-header">
            <i class="fas fa-table me-1"></i> Census Data Records of Barangay Balas
          </div>
          <div class="card-body">
            <!-- Census Period Selector -->
            <div class="census-period-selector mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-0">Census Period: <span id="currentCensusPeriod">2025 Barangay Census</span></h4>
                        <p class="text-muted mb-0">March 1, 2025 - March 31, 2025</p>
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <div class="btn-group">
                            <button class="btn btn-outline-primary">
                                <i class="fas fa-plus me-1"></i> New Census
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-sync-alt me-1"></i> Change Period
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card bg-white">
                        <i class="fas fa-house-user text-primary"></i>
                        <div class="number">1,245</div>
                        <div class="label">Total Households</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card bg-white">
                        <i class="fas fa-users text-info"></i>
                        <div class="number">4,832</div>
                        <div class="label">Total Residents</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card bg-white">
                        <i class="fas fa-tint text-success"></i>
                        <div class="number">92%</div>
                        <div class="label">Water Source Coverage</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card bg-white">
                        <i class="fas fa-toilet text-warning"></i>
                        <div class="number">87%</div>
                        <div class="label">Toilet Facility Coverage</div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <i class="fas fa-users me-1"></i>
                            Household Records
                        </div>
                        <button class="btn btn-export btn-sm" id="exportExcelBtn">
                            <i class="fas fa-file-excel me-1"></i> Export to Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters Section -->
                    <div class="card mb-4 bg-light-custom">
                        <div class="card-header py-2 bg-light-custom">
                            <button class="btn btn-sm btn-link text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                                <i class="fas fa-filter me-1"></i> Advanced Filters
                            </button>
                        </div>
                        <div class="collapse show" id="filterCollapse">
                            <div class="card-body py-3">
                                <form id="filterForm">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Purok</label>
                                            <select class="form-select" name="purok">
                                                <option value="" selected>All Puroks</option>
                                                <option value="1">Purok 1</option>
                                                <option value="2">Purok 2</option>
                                                <option value="3">Purok 3</option>
                                                <option value="4">Purok 4</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Water Source</label>
                                            <select class="form-select" name="water_source">
                                                <option value="" selected>All Sources</option>
                                                <option value="Level I">Level I (Well)</option>
                                                <option value="Level II">Level II (Deep Well)</option>
                                                <option value="Level III">Level III (Piped)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Toilet Facility</label>
                                            <select class="form-select" name="toilet_facility">
                                                <option value="" selected>All Types</option>
                                                <option value="Water-sealed">Water-sealed</option>
                                                <option value="Pit">Pit</option>
                                                <option value="None">None</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="" selected>All Statuses</option>
                                                <option value="Complete">Complete</option>
                                                <option value="Incomplete">Incomplete</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12 d-flex justify-content-end">
                                            <button type="reset" class="btn btn-outline-secondary me-2">
                                                <i class="fas fa-undo me-1"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-filter me-1"></i> Apply Filters
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Table Controls -->
                    <div class="table-controls mb-3">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="badge bg-primary rounded-pill household-badge">
                                <span id="totalHouseholds">1,245</span> Total Households
                            </span>
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <span class="input-group-text">Show</span>
                                <select class="form-select form-select-sm" id="rowsPerPage">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <div class="search-box">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search...">
                                <button class="btn btn-sm btn-outline-secondary" type="button" id="searchButton">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Main Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="householdsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Household No.</th>
                                    <th>Head of Family</th>
                                    <th>Purok</th>
                                    <th>House No.</th>
                                    <th>Water Source</th>
                                    <th>Toilet Facility</th>
                                    <th>Members</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="householdsTableBody">
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <span class="fw-bold">HH-2025-001</span>
                                        <div class="text-muted small">123 Main Street</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="resident-image-placeholder bg-primary text-white">
                                                JR
                                            </div>
                                            <div>Juan Reyes</div>
                                        </div>
                                    </td>
                                    <td>Purok 1</td>
                                    <td>123</td>
                                    <td>Level III (Piped)</td>
                                    <td>Water-sealed</td>
                                    <td>4</td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-outline-primary view-btn" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning edit-btn" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>
                                        <span class="fw-bold">HH-2025-002</span>
                                        <div class="text-muted small">456 Oak Avenue</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="resident-image-placeholder bg-success text-white">
                                                MS
                                            </div>
                                            <div>Maria Santos</div>
                                        </div>
                                    </td>
                                    <td>Purok 2</td>
                                    <td>456</td>
                                    <td>Level II (Deep Well)</td>
                                    <td>Water-sealed</td>
                                    <td>6</td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-outline-primary view-btn" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning edit-btn" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>
                                        <span class="fw-bold">HH-2025-003</span>
                                        <div class="text-muted small">789 Pine Road</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="resident-image-placeholder bg-info text-white">
                                                AC
                                            </div>
                                            <div>Antonio Cruz</div>
                                        </div>
                                    </td>
                                    <td>Purok 3</td>
                                    <td>789</td>
                                    <td>Level I (Well)</td>
                                    <td>Pit</td>
                                    <td>3</td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-outline-primary view-btn" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning edit-btn" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>
                                        <span class="fw-bold">HH-2025-004</span>
                                        <div class="text-muted small">101 Maple Lane</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="resident-image-placeholder bg-warning text-dark">
                                                ED
                                            </div>
                                            <div>Elena Diaz</div>
                                        </div>
                                    </td>
                                    <td>Purok 4</td>
                                    <td>101</td>
                                    <td>Level III (Piped)</td>
                                    <td>Water-sealed</td>
                                    <td>5</td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-outline-primary view-btn" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning edit-btn" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                        <div class="text-muted small">
                            Showing <span id="showingFrom">1</span> to <span id="showingTo">4</span> of <span id="totalRecords">1,245</span> entries
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#">Previous</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
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
    </main>

    <?php include 'includes/footer.php'; ?>
  </div>
</div>

<?php include 'modals/censusModal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // View household details
        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function() {
                var modal = new bootstrap.Modal(document.getElementById('householdModal'));
                modal.show();
            });
        });
        
        // Delete household functionality
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire(
                            'Deleted!',
                            'Household record has been deleted.',
                            'success'
                        );
                    }
                });
            });
        });
        
        // Export to Excel functionality
        document.getElementById('exportExcelBtn').addEventListener('click', function() {
            Swal.fire({
                title: 'Export to Excel',
                text: 'Preparing data for export...',
                icon: 'info',
                showConfirmButton: false,
                timer: 1500
            });
        });
        
        // Filter form submission
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Filters Applied',
                text: 'Household data has been filtered based on your criteria.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        });
        
        // Search functionality
        document.getElementById('searchButton').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchInput').value;
            if (searchTerm) {
                Swal.fire({
                    title: 'Searching',
                    text: `Searching for "${searchTerm}"...`,
                    icon: 'info',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
        
        // Update pagination info based on actual table rows
        function updatePaginationInfo() {
            const rowsPerPage = parseInt(document.getElementById('rowsPerPage').value);
            const totalRecords = 1245;
            const currentPage = 1;
            
            const showingFrom = (currentPage - 1) * rowsPerPage + 1;
            const showingTo = Math.min(currentPage * rowsPerPage, totalRecords);
            
            document.getElementById('showingFrom').textContent = showingFrom;
            document.getElementById('showingTo').textContent = showingTo;
            document.getElementById('totalRecords').textContent = totalRecords;
        }
        
        // Update rows per page
        document.getElementById('rowsPerPage').addEventListener('change', function() {
            updatePaginationInfo();
        });
        
        // Initialize pagination info
        updatePaginationInfo();
    });
</script>
</body>
</html>
