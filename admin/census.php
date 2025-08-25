<?php 
require_once __DIR__ . '/includes/auth.php';
requireAuth();

// make sure backend functions are included
require_once __DIR__ . '/census-backend.php';

$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filters = [
        'purok' => $_POST['purok'] ?? '',
        'house_type' => $_POST['house_type'] ?? '',
        'water_source' => $_POST['water_source'] ?? '',
        'status' => $_POST['status'] ?? '',
        'search' => $_POST['search'] ?? ''
    ];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filters = [
        'purok' => $_GET['purok'] ?? '',
        'house_type' => $_GET['house_type'] ?? '',
        'water_source' => $_GET['water_source'] ?? '',
        'status' => $_GET['status'] ?? '',
        'search' => $_GET['search'] ?? ''
    ];
}

// Pagination setup
$rowsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $rowsPerPage;

// Fetch households with pagination
$households = getHouseholds($filters, $rowsPerPage, $offset);
$totalHouseholds = getTotalHouseholdsCount($filters);

// For AJAX requests, return JSON
if (isset($_GET['ajax']) && $_GET['ajax'] == 'true') {
    header('Content-Type: application/json');
    echo json_encode([
        'households' => $households,
        'total' => $totalHouseholds
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Census Data | Barangay Balas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Your existing styles */
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
                        <li class="breadcrumb-item active">Census Data</li>
                    </ol>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
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
                                                    <label class="form-label">House Type</label>
                                                    <select class="form-select" name="house_type">
                                                        <option value="" selected>All Types</option>
                                                        <option value="Single-detached">Single-detached</option>
                                                        <option value="Duplex">Duplex</option>
                                                        <option value="Apartment">Apartment</option>
                                                        <option value="Shanty">Shanty</option>
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
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" name="status">
                                                        <option value="" selected>All Statuses</option>
                                                        <option value="Active">Active</option>
                                                        <option value="Inactive">Inactive</option>
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
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary rounded-pill household-badge me-3">
                                        <span id="totalHouseholds"><?php echo $totalHouseholds; ?></span> Total Households
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
                                <div class="input-group input-group-sm" style="width: 250px;">
                                    <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" id="searchButton">
                                        <i class="fas fa-search"></i>
                                    </button>
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
                                            <th>Members</th>
                                            <th>House Type</th>
                                            <th>Status</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="householdsTableBody">
                                        <?php if (empty($households)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="fas fa-info-circle text-muted me-2"></i>
                                                    No households found
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($households as $index => $household): ?>
                                                <tr>
                                                    <td><?php echo $index + 1; ?></td>
                                                    <td>
                                                        <span class="fw-bold"><?php echo htmlspecialchars($household['household_number']); ?></span>
                                                        <div class="text-muted small"><?php echo htmlspecialchars($household['address']); ?></div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($household['head_of_family']); ?></td>
                                                    <td>Purok <?php echo htmlspecialchars($household['purok']); ?></td>
                                                    <td><?php echo $household['members']; ?></td>
                                                    <td><?php echo htmlspecialchars($household['house_type']); ?></td>
                                                    <td><span class="badge <?php echo getStatusBadgeClass($household['status']); ?>"><?php echo htmlspecialchars($household['status']); ?></span></td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-sm btn-outline-primary view-btn" data-id="<?php echo $household['id']; ?>" data-bs-toggle="tooltip" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-warning edit-btn" data-id="<?php echo $household['id']; ?>" data-bs-toggle="tooltip" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?php echo $household['id']; ?>" data-bs-toggle="tooltip" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    Showing <span id="showingFrom">1</span> to <span id="showingTo"><?php echo min(count($households), 25); ?></span> of <span id="totalRecords"><?php echo $totalHouseholds; ?></span> entries
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-sm mb-0" id="pagination">
                                        <!-- Pagination will be handled by JavaScript -->
                                    </ul>
                                </nav>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Your JavaScript code with AJAX integration
            let currentPage = 1;
            let rowsPerPage = 25;
            let filteredHouseholds = <?php echo json_encode($households); ?>;
            let totalHouseholds = <?php echo $totalHouseholds; ?>;
            
            // Function to load data via AJAX
            function loadHouseholds(filters = {}) {
                const params = new URLSearchParams();
                params.append('ajax', 'true');
                
                // Add filters to params
                Object.keys(filters).forEach(key => {
                    if (filters[key]) {
                        params.append(key, filters[key]);
                    }
                });
                
                fetch('census.php?' + params.toString())
                    .then(response => response.json())
                    .then(data => {
                        filteredHouseholds = data.households;
                        totalHouseholds = data.total;
                        updateTable();
                        updatePagination();
                        document.getElementById('totalHouseholds').textContent = totalHouseholds;
                        document.getElementById('totalRecords').textContent = totalHouseholds;
                    })
                    .catch(error => {
                        console.error('Error loading households:', error);
                        showAlert('Error loading data. Please try again.', 'error');
                    });
            }
            
            // Update the rest of your JavaScript to use the loadHouseholds function
            // ... [rest of your JavaScript code]
            
            // Filter form submission
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const filters = Object.fromEntries(formData.entries());
                
                loadHouseholds(filters);
            });
            
            // Search functionality
            document.getElementById('searchButton').addEventListener('click', function() {
                const searchTerm = document.getElementById('searchInput').value;
                
                loadHouseholds({
                    search: searchTerm,
                    purok: document.querySelector('select[name="purok"]').value,
                    house_type: document.querySelector('select[name="house_type"]').value,
                    water_source: document.querySelector('select[name="water_source"]').value,
                    status: document.querySelector('select[name="status"]').value
                });
            });
            
            // Helper function for status badge class
            function getStatusBadgeClass(status) {
                switch(status) {
                    case 'Active': return 'bg-success';
                    case 'Inactive': return 'bg-secondary';
                    case 'Incomplete': return 'bg-warning text-dark';
                    default: return 'bg-primary';
                }
            }
            
            // Initialize the page
            updatePagination();
            initTooltips();
        });
    </script>
</body>
</html>

<?php
// Helper function for status badge class
function getStatusBadgeClass($status) {
    switch($status) {
        case 'Active': return 'bg-success';
        case 'Inactive': return 'bg-secondary';
        case 'Incomplete': return 'bg-warning text-dark';
        default: return 'bg-primary';
    }
}
?>