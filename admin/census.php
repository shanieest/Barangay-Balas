<?php
// admin/census.php - Admin Household Census Management Page
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAuth();

// Get statistics
$stats_query = "
    SELECT 
        COUNT(DISTINCT house_number, purok) as total_households,
        COUNT(*) as total_residents,
        SUM(CASE WHEN sex = 'male' THEN 1 ELSE 0 END) as male_population,
        SUM(CASE WHEN sex = 'female' THEN 1 ELSE 0 END) as female_population
    FROM residents 
    WHERE resident_status = 'Active'
";
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
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-blue: #0033cc;
            --secondary-blue: #3a7cb9;
            --accent-red: #e63946;
            --accent-yellow: #ffbe0b;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
        }

        .stats-card {
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 25px;
            transition: transform 0.2s, box-shadow 0.2s;
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .stats-card i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .stats-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stats-card .label {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
        }

        .export-section {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
        }

        .export-btn {
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .export-btn:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
            color: white;
            transform: translateY(-2px);
        }

        .household-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.3s;
            overflow: hidden;
        }

        .household-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }

        .household-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
            padding: 20px;
            position: relative;
        }

        .household-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-yellow);
        }

        .member-row {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }

        .member-row:hover {
            background-color: #f8f9fa;
        }

        .member-row:last-child {
            border-bottom: none;
        }

        .head-badge {
            background: var(--accent-yellow);
            color: var(--dark-gray);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .member-badge {
            background: #e9ecef;
            color: #6c757d;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .filter-card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .search-box {
            border-radius: 25px;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .search-box:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(0,51,204,0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,51,204,0.3);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .pagination .page-link {
            border-radius: 10px;
            margin: 0 5px;
            border: none;
            color: var(--primary-blue);
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div id="layoutSidenav">
        <?php include 'includes/sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <div class="container-fluid px-4 py-4">

                <!-- Loading Overlay -->
                <div class="loading-overlay" id="loadingOverlay">
                    <div class="text-center">
                        <div class="loading-spinner mb-3"></div>
                        <h5>Generating Census Report...</h5>
                        <p class="text-muted">Please wait while we prepare your Excel file</p>
                    </div>
                </div>

                <main class="container-fluid px-4">
                    <h1 class="mt-4">Census Data Management</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Census Data</li>
                    </ol>

                    <!-- Export Section -->
                    <div class="export-section">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-2">
                                    <i class="fas fa-file-excel me-2"></i>Export Census Data
                                </h3>
                                <p class="mb-0 opacity-75">Download complete household records grouped by house numbers for comprehensive analysis</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn export-btn" onclick="exportToExcel('admin')">
                                    <i class="fas fa-download me-2"></i>Export All Data
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <i class="fas fa-house-user text-primary"></i>
                                <div class="number text-primary" id="totalHouseholds"><?php echo $stats['total_households']; ?></div>
                                <div class="label">Total Households</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <i class="fas fa-users text-info"></i>
                                <div class="number text-info" id="totalResidents"><?php echo $stats['total_residents']; ?></div>
                                <div class="label">Total Residents</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <i class="fas fa-male text-success"></i>
                                <div class="number text-success" id="malePopulation"><?php echo $stats['male_population']; ?></div>
                                <div class="label">Male Population</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <i class="fas fa-female text-warning"></i>
                                <div class="number text-warning" id="femalePopulation"><?php echo $stats['female_population']; ?></div>
                                <div class="label">Female Population</div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="filter-card">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Purok Filter</label>
                                <select class="form-select" id="purokFilter" onchange="filterHouseholds()">
                                    <option value="">All Puroks</option>
                                    <option value="Purok 1">Purok 1</option>
                                    <option value="Purok 2">Purok 2</option>
                                    <option value="Purok 3">Purok 3</option>
                                    <option value="Purok 4">Purok 4</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Search Households</label>
                                <div class="input-group">
                                    <input type="text" class="form-control search-box" id="searchInput" 
                                           placeholder="Search by name, house number, or address..." 
                                           onkeyup="filterHouseholds()">
                                    <button class="btn btn-primary" onclick="filterHouseholds()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Show Entries</label>
                                <select class="form-select" id="entriesPerPage" onchange="changeEntriesPerPage()">
                                    <option value="10">10 per page</option>
                                    <option value="25" selected>25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Household Cards -->
                    <div id="householdContainer">
                        <!-- Dynamic content will be loaded here -->
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4" id="paginationContainer">
                        <!-- Dynamic pagination will be loaded here -->
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/census.js"></script>

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
            form.action = 'census-backend.php';
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