<?php
// admin/census.php - Updated Census Management Page
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAuth();

// Get enhanced statistics including relationship data
$stats_query = "
    SELECT 
        COUNT(DISTINCT house_number, purok) as total_households,
        COUNT(*) as total_residents,
        SUM(CASE WHEN sex = 'male' THEN 1 ELSE 0 END) as male_population,
        SUM(CASE WHEN sex = 'female' THEN 1 ELSE 0 END) as female_population,
        SUM(CASE WHEN age < 18 THEN 1 ELSE 0 END) as children,
        SUM(CASE WHEN age >= 18 AND age < 60 THEN 1 ELSE 0 END) as adults,
        SUM(CASE WHEN age >= 60 THEN 1 ELSE 0 END) as seniors,
        SUM(CASE WHEN is_indigent = 1 THEN 1 ELSE 0 END) as indigent_families,
        SUM(CASE WHEN is_4ps_member = 1 THEN 1 ELSE 0 END) as fourps_members,
        SUM(CASE WHEN philhealth_number IS NOT NULL AND philhealth_number != '' THEN 1 ELSE 0 END) as philhealth_members,
        SUM(CASE WHEN relationship_to_head IS NULL OR relationship_to_head = '' THEN 1 ELSE 0 END) as undetermined_relationships
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
        /* Spacious & Readable Household Card Styles */

.household-card {
    border-radius: 12px;
    overflow: hidden;
    border: none !important;
}

.household-card .card-header {
    background: linear-gradient(135deg, #0033cc 0%, #3a7cb9 100%);
    border: none;
}

.household-card .card-header h4 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

/* Member Row Styles */
.member-row {
    border-bottom: 2px solid #dee2e6;
    transition: all 0.2s ease;
}

.member-row:hover {
    background-color: #f0f8ff !important;
}

.member-row:last-child {
    border-bottom: none;
}

.member-head {
    background: linear-gradient(to right, #fff9e6 0%, #ffffff 50%) !important;
    border-left: 5px solid #ffbe0b;
}

/* Member Avatar */
.member-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0033cc, #3a7cb9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(0, 51, 204, 0.2);
    flex-shrink: 0;
}

.member-head .member-avatar {
    background: linear-gradient(135deg, #ffbe0b, #ff9800);
    box-shadow: 0 3px 10px rgba(255, 190, 11, 0.3);
}

/* Relationship Badges */
.badge-relationship-head {
    background: #0033cc;
    color: white;
    padding: 6px 14px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-relationship-spouse {
    background: #4caf50;
    color: white;
    padding: 6px 14px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-relationship-son,
.badge-relationship-daughter {
    background: #2196f3;
    color: white;
    padding: 6px 14px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-relationship-father,
.badge-relationship-mother {
    background: #ff9800;
    color: white;
    padding: 6px 14px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-relationship-brother,
.badge-relationship-sister {
    background: #9c27b0;
    color: white;
    padding: 6px 14px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-relationship-grandfather,
.badge-relationship-grandmother {
    background: #607d8b;
    color: white;
    padding: 6px 14px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-relationship-undetermined {
    background: #9e9e9e;
    color: white;
    padding: 6px 14px;
    font-size: 0.85rem;
    font-weight: 600;
}

/* Info Sections */
.member-row h5 {
    font-size: 1.2rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.member-row h6 {
    color: #6c757d;
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 1px;
    margin-bottom: 1rem;
}

.member-row p {
    font-size: 0.95rem;
    line-height: 1.8;
    color: #495057;
}

.member-row p strong {
    color: #2c3e50;
    font-weight: 600;
    display: inline-block;
    min-width: 120px;
}

/* Status Badges */
.bg-purple {
    background-color: #9c27b0 !important;
}

.bg-pink {
    background-color: #e91e63 !important;
}

/* Card Footer */
.card-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

/* Responsive */
@media (max-width: 991px) {
    .member-row .border-end {
        border-right: none !important;
        border-bottom: 1px solid #dee2e6;
    }
    
    .member-row .col-lg-4 {
        padding: 20px !important;
    }
}

@media (max-width: 576px) {
    .member-avatar {
        width: 50px;
        height: 50px;
        font-size: 1.1rem;
    }
    
    .member-row h5 {
        font-size: 1.1rem;
    }
    
    .member-row p strong {
        min-width: 100px;
        font-size: 0.9rem;
    }
}

        .stats-card {
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 25px;
            transition: transform 0.2s, box-shadow 0.2s;
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            min-height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: center;
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

        .household-card-enhanced {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.3s;
            overflow: hidden;
            background: white;
        }

        .household-card-enhanced:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-3px);
        }

        .household-header-enhanced {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
            padding: 20px;
            position: relative;
        }

        .household-header-enhanced::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-yellow);
        }

        .member-row-enhanced {
            transition: background-color 0.2s;
        }

        .member-row-enhanced:hover {
            background-color: #f8f9fa !important;
        }

        .member-row-enhanced:last-child {
            border-bottom: none !important;
        }

        .head-member-row {
            border-left: 4px solid var(--accent-yellow);
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

        .bg-purple { background-color: #6f42c1 !important; }
        .bg-cyan { background-color: #0dcaf0 !important; }
        
        .household-footer {
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
        }
        
        .alert-warning {
            border-left: 4px solid #ffc107;
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
                    <h1 class="mt-4">
                        <i class="fas fa-users me-2"></i>Census Data Management
                    </h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Census Data</li>
                    </ol>

                    <!-- Information Notice -->
                    <div id="relationshipWarning">
                        <?php if ($stats['undetermined_relationships'] > 0): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Information:</strong> System has automatically determined relationships for <?php echo $stats['undetermined_relationships']; ?> residents based on age and household composition. 
                            <small class="d-block mt-1">Census data is managed by residents themselves through their accounts.</small>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Export Section -->
                    <div class="export-section">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h3 class="mb-2">
                                    <i class="fas fa-file-excel me-2"></i>Export Census Data
                                </h3>
                                <p class="mb-0 opacity-75">Download comprehensive household records for viewing and analysis. Census data is managed by residents through their accounts.</p>
                            </div>
                            <div class="col-md-5 text-end">
                                <button class="btn export-btn" onclick="exportToExcel('admin')">
                                    <i class="fas fa-download me-2"></i>Export All Data
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Statistics -->
                    <div class="row">
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-house-user text-primary"></i>
                                <div class="number text-primary" id="totalHouseholds"><?php echo number_format($stats['total_households']); ?></div>
                                <div class="label">Total Households</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-users text-info"></i>
                                <div class="number text-info" id="totalResidents"><?php echo number_format($stats['total_residents']); ?></div>
                                <div class="label">Total Residents</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-male text-success"></i>
                                <div class="number text-success" id="malePopulation"><?php echo number_format($stats['male_population']); ?></div>
                                <div class="label">Male Population</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-female text-warning"></i>
                                <div class="number text-warning" id="femalePopulation"><?php echo number_format($stats['female_population']); ?></div>
                                <div class="label">Female Population</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-child text-danger"></i>
                                <div class="number text-danger" id="childrenCount"><?php echo number_format($stats['children']); ?></div>
                                <div class="label">Children (0-17)</div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-user-tie text-secondary"></i>
                                <div class="number text-secondary" id="adultsCount"><?php echo number_format($stats['adults']); ?></div>
                                <div class="label">Adults (18-59)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Statistics Row -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-walking text-purple"></i>
                                <div class="number text-purple" id="seniorsCount"><?php echo number_format($stats['seniors']); ?></div>
                                <div class="label">Senior Citizens (60+)</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-hand-holding-heart text-warning"></i>
                                <div class="number text-warning" id="indigentCount"><?php echo number_format($stats['indigent_families']); ?></div>
                                <div class="label">Indigent Families</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-home text-success"></i>
                                <div class="number text-success" id="fourpsCount"><?php echo number_format($stats['fourps_members']); ?></div>
                                <div class="label">4Ps Members</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="stats-card">
                                <i class="fas fa-notes-medical text-info"></i>
                                <div class="number text-info" id="philhealthCount"><?php echo number_format($stats['philhealth_members']); ?></div>
                                <div class="label">PhilHealth Members</div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="filter-card">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-map-marker-alt me-1"></i>Purok Filter
                                </label>
                                <select class="form-select" id="purokFilter" onchange="filterHouseholds()">
                                    <option value="">All Puroks</option>
                                    <option value="Purok 1">Purok 1</option>
                                    <option value="Purok 2">Purok 2</option>
                                    <option value="Purok 3">Purok 3</option>
                                    <option value="Purok 4">Purok 4</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-search me-1"></i>Search Households
                                </label>
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
                                <label class="form-label fw-semibold">
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