<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAuth();

// Get basic statistics
$stats_query = "
    SELECT 
        COUNT(DISTINCT house_number, purok) as total_households,
        COUNT(*) as total_residents
    FROM residents 
    LEFT JOIN resident_accounts ON residents.id = resident_accounts.resident_id
    WHERE account_status = 'Approved'
";

$purok_filter = isset($_GET['purok']) ? $_GET['purok'] : 'all';

if ($purok_filter !== 'all') {
    $query .= " WHERE a.status = ?";
}

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Census Data Management - Barangay Balas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/census.css">
</head>
<body class="sb-nav-fixed">
    <?php include '../includes/navbar.php'; ?>
    <div id="layoutSidenav">
        <?php include '../includes/sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <!-- Loading Overlay -->
                <div class="loading-overlay" id="loadingOverlay">
                    <div class="loading-content">
                        <div class="loading-spinner"></div>
                        <h5>Generating Census Report...</h5>
                        <p class="text-muted">Please wait while we prepare your Excel file</p>
                    </div>
                </div>

                <div>
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="header-icon">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <h1 class="mb-2">Census Data Management</h1>
                                <p class="mb-0 opacity-75">Comprehensive household records and resident information</p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                                    <button class="btn btn-outline-light">
                                        <i class="fas fa-home me-1"></i> <?= number_format($stats['total_households']) ?> Households
                                    </button>
                                    <button class="btn btn-outline-light">
                                        <i class="fas fa-users me-1"></i> <?= number_format($stats['total_residents']) ?> Residents
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                            <li class="breadcrumb-item active"><i class="fas fa-users me-1"></i>Census Data</li>
                        </ol>
                    </nav>

                    <!-- export section -->
                    <div class="export-card">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-2">
                                    <i class="fas fa-print me-2 text-primary"></i>Print Reports
                                </h3>
                                <p class="mb-0 text-muted">Print and Export Census Data of Residents.</p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="btn-group" role="group">
                                    <button class="btn export-btn-excel me-2" onclick="exportToExcel('admin')">
                                        <i class="fas fa-file-excel me-2"></i>Excel
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn export-btn-pdf dropdown-toggle" type="button" id="pdfDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-print me-2"></i>Print PDF
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="pdfDropdown">
                                            <li><a class="dropdown-item" href="#" onclick="printPurokPDF('Purok 1')">
                                                <i class="fas fa-print me-2"></i>Print Purok 1
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="printPurokPDF('Purok 2')">
                                                <i class="fas fa-print me-2"></i>Print Purok 2
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="printPurokPDF('Purok 3')">
                                                <i class="fas fa-print me-2"></i>Print Purok 3
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="printPurokPDF('Purok 4')">
                                                <i class="fas fa-print me-2"></i>Print Purok 4
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="printPurokPDF('Purok 5')">
                                                <i class="fas fa-print me-2"></i>Print Purok 5
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="printPurokPDF('Purok 6')">
                                                <i class="fas fa-print me-2"></i>Print Purok 6
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="printPurokPDF('Purok 7')">
                                                <i class="fas fa-print me-2"></i>Print Purok 7
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" onclick="printPurokPDF('all')">
                                                <i class="fas fa-print me-2"></i>Print All Puroks
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="filter-card">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Purok Filter
                                </label>
                                <select class="form-select" id="purokFilter" onchange="filterHouseholds()">
                                    <option value="all" <?= $purok_filter === 'all' ? 'selected' : ''?>></option>All Puroks</option>
                                    <option value="Purok 1" <?= $purok_filter === 'Purok 1' ? 'selected' : ''?>></option>>Purok 1</option>
                                    <option value="Purok 2" <?= $purok_filter === 'Purok 2' ? 'selected' : ''?>></option>>Purok 2</option>
                                    <option value="Purok 3" <?= $purok_filter === 'Purok 3' ? 'selected' : ''?>></option>>Purok 3</option>
                                    <option value="Purok 4" <?= $purok_filter === 'Purok 4' ? 'selected' : ''?>></option>>Purok 4</option>
                                    <option value="Purok 5" <?= $purok_filter === 'Purok 5' ? 'selected' : ''?>></option>>Purok 5</option>
                                    <option value="Purok 6" <?= $purok_filter === 'Purok 6' ? 'selected' : ''?>></option>>Purok 6</option>
                                    <option value="Purok 7" <?= $purok_filter === 'Purok 7' ? 'selected' : ''?>></option>>Purok 7</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-search me-1"></i>Search Households
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" 
                                           placeholder="Search by name, house number, or address..." 
                                           onkeyup="filterHouseholds()">
                                    <button class="btn btn-primary" onclick="filterHouseholds()" style="background: var(--primary-gradient); border: none;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fas fa-list me-1"></i>Show Entries
                                </label>
                                <select class="form-select" id="entriesPerPage" onchange="changeEntriesPerPage()">
                                    <option value="10">10 per page</option>
                                    <option value="25" selected>25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Household Cards Container -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-home me-2"></i>Household Records
                        </div>
                        <div class="card-body">
                            <div id="householdContainer">
                                <!-- Dynamic content will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container" id="paginationContainer">
                        <!-- Dynamic pagination will be loaded here -->
                    </div>
                </div>
            </main>
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/census.js"></script>

    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadHouseholds();
        });

        // Export to Excel functionality
        function exportToExcel(type) {
            const loadingOverlay = document.getElementById('loadingOverlay');
            loadingOverlay.style.display = 'flex';
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../backend/census-backend.php';
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.name = 'action';
            actionInput.value = 'export_excel';
            
            const typeInput = document.createElement('input');
            typeInput.name = 'type';
            typeInput.value = type;
            
            form.appendChild(actionInput);
            form.appendChild(typeInput);
            document.body.appendChild(form);
            
            form.submit();
            
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
                document.body.removeChild(form);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Export Successful!',
                    text: 'Your Excel file has been downloaded successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 2000);
        }
    </script>
</body>
</html>