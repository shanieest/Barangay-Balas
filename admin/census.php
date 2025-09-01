<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();
require_once __DIR__ . '/includes/db.php';

// Initialize variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
$offset = ($page - 1) * $limit;

// Filter parameters
$purok_filter = isset($_GET['purok']) ? $_GET['purok'] : '';
$water_filter = isset($_GET['water_source']) ? $_GET['water_source'] : '';
$toilet_filter = isset($_GET['toilet_facility']) ? $_GET['toilet_facility'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build WHERE clause for filters
$where_conditions = [];
$params = [];
$types = '';

// Check if relationship_to_head column exists before adding the condition
$check_column_sql = "SHOW COLUMNS FROM residents LIKE 'relationship_to_head'";
$column_result = $conn->query($check_column_sql);
if ($column_result->num_rows > 0) {
    $where_conditions[] = "r.relationship_to_head = 'Head'";
}

if (!empty($purok_filter)) {
    $where_conditions[] = "r.purok = ?";
    $params[] = $purok_filter;
    $types .= 's';
}

if (!empty($water_filter)) {
    $where_conditions[] = "r.type_of_water_source = ?";
    $params[] = $water_filter;
    $types .= 's';
}

if (!empty($toilet_filter)) {
    $where_conditions[] = "r.type_of_toilet_facility = ?";
    $params[] = $toilet_filter;
    $types .= 's';
}

if (!empty($status_filter)) {
    $where_conditions[] = "r.resident_status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(r.first_name LIKE ? OR r.last_name LIKE ? OR r.house_number LIKE ? OR r.address LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ssss';
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total count of households (heads of family)
$count_sql = "SELECT COUNT(DISTINCT r.house_number, r.purok) as total 
              FROM residents r 
              $where_sql";

$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_households = $count_result->fetch_assoc()['total'];
$count_stmt->close();

// Reset params for main query
$main_params = array_slice($params, 0, count($params));
$main_types = $types;

// Get households data with pagination (heads of family)
$sql = "SELECT r.*, 
               (SELECT COUNT(*) FROM residents r2 
                WHERE r2.house_number = r.house_number 
                AND r2.purok = r.purok) as member_count
        FROM residents r 
        $where_sql 
        GROUP BY r.house_number, r.purok
        ORDER BY r.purok, r.house_number 
        LIMIT ? OFFSET ?";

// Add limit and offset to params
$main_params[] = $limit;
$main_params[] = $offset;
$main_types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($main_params)) {
    $stmt->bind_param($main_types, ...$main_params);
}
$stmt->execute();
$result = $stmt->get_result();
$households = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate pagination
$total_pages = ceil($total_households / $limit);
$showing_from = ($page - 1) * $limit + 1;
$showing_to = min($page * $limit, $total_households);

// Get statistics
$stats_sql = "SELECT 
    (SELECT COUNT(DISTINCT house_number, purok) FROM residents" . (($column_result->num_rows > 0) ? " WHERE relationship_to_head = 'Head'" : "") . ") as total_households,
    (SELECT COUNT(*) FROM resident_accounts WHERE account_status = 'approved') as total_residents,
    (SELECT COUNT(DISTINCT house_number, purok) FROM residents WHERE type_of_water_source IS NOT NULL AND type_of_water_source != ''" . (($column_result->num_rows > 0) ? " AND relationship_to_head = 'Head'" : "") . ") as water_coverage,
    (SELECT COUNT(DISTINCT house_number, purok) FROM residents WHERE type_of_toilet_facility IS NOT NULL AND type_of_toilet_facility != ''" . (($column_result->num_rows > 0) ? " AND relationship_to_head = 'Head'" : "") . ") as toilet_coverage";

$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
$stats_result->close();

// Calculate percentages
$water_percentage = $stats['total_households'] > 0 ? round(($stats['water_coverage'] / $stats['total_households']) * 100) : 0;
$toilet_percentage = $stats['total_households'] > 0 ? round(($stats['toilet_coverage'] / $stats['total_households']) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Census Data | Barangay Balas Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
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
        background-color: var(--light_bg);
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
                        <div class="number"><?php echo number_format($stats['total_households']); ?></div>
                        <div class="label">Total Households</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card bg-white">
                        <i class="fas fa-users text-info"></i>
                        <div class="number"><?php echo number_format($stats['total_residents']); ?></div>
                        <div class="label">Total Approved Residents</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card bg-white">
                        <i class="fas fa-tint text-success"></i>
                        <div class="number"><?php echo $water_percentage; ?>%</div>
                        <div class="label">Water Source Coverage</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card bg-white">
                        <i class="fas fa-toilet text-warning"></i>
                        <div class="number"><?php echo $toilet_percentage; ?>%</div>
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
                                <form id="filterForm" method="GET" action="">
                                    <input type="hidden" name="page" value="1">
                                    <input type="hidden" name="limit" value="<?php echo $limit; ?>">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Purok</label>
                                            <select class="form-select" name="purok">
                                                <option value="" <?php echo empty($purok_filter) ? 'selected' : ''; ?>>All Puroks</option>
                                                <option value="1" <?php echo $purok_filter == '1' ? 'selected' : ''; ?>>Purok 1</option>
                                                <option value="2" <?php echo $purok_filter == '2' ? 'selected' : ''; ?>>Purok 2</option>
                                                <option value="3" <?php echo $purok_filter == '3' ? 'selected' : ''; ?>>Purok 3</option>
                                                <option value="4" <?php echo $purok_filter == '4' ? 'selected' : ''; ?>>Purok 4</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Water Source</label>
                                            <select class="form-select" name="water_source">
                                                <option value="" <?php echo empty($water_filter) ? 'selected' : ''; ?>>All Sources</option>
                                                <option value="Level I" <?php echo $water_filter == 'Level I' ? 'selected' : ''; ?>>Level I (Well)</option>
                                                <option value="Level II" <?php echo $water_filter == 'Level II' ? 'selected' : ''; ?>>Level II (Deep Well)</option>
                                                <option value="Level III" <?php echo $water_filter == 'Level III' ? 'selected' : ''; ?>>Level III (Piped)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Toilet Facility</label>
                                            <select class="form-select" name="toilet_facility">
                                                <option value="" <?php echo empty($toilet_filter) ? 'selected' : ''; ?>>All Types</option>
                                                <option value="Water-sealed" <?php echo $toilet_filter == 'Water-sealed' ? 'selected' : ''; ?>>Water-sealed</option>
                                                <option value="Pit" <?php echo $toilet_filter == 'Pit' ? 'selected' : ''; ?>>Pit</option>
                                                <option value="None" <?php echo $toilet_filter == 'None' ? 'selected' : ''; ?>>None</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="" <?php echo empty($status_filter) ? 'selected' : ''; ?>>All Statuses</option>
                                                <option value="Active" <?php echo $status_filter == 'Active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="Inactive" <?php echo $status_filter == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                <option value="Deceased" <?php echo $status_filter == 'Deceased' ? 'selected' : ''; ?>>Deceased</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12 d-flex justify-content-end">
                                            <a href="census.php" class="btn btn-outline-secondary me-2">
                                                <i class="fas fa-undo me-1"></i> Reset
                                            </a>
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
                                <span id="totalHouseholds"><?php echo number_format($total_households); ?></span> Total Households
                            </span>
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <span class="input-group-text">Show</span>
                                <select class="form-select form-select-sm" id="rowsPerPage" onchange="updateLimit(this.value)">
                                    <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                                    <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                                </select>
                            </div>
                        </div>
                        <div class="search-box">
                            <form method="GET" action="" class="d-flex">
                                <input type="hidden" name="page" value="1">
                                <input type="hidden" name="limit" value="<?php echo $limit; ?>">
                                <input type="hidden" name="purok" value="<?php echo $purok_filter; ?>">
                                <input type="hidden" name="water_source" value="<?php echo $water_filter; ?>">
                                <input type="hidden" name="toilet_facility" value="<?php echo $toilet_filter; ?>">
                                <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                                <div class="input-group input-group-sm" style="width: 250px;">
                                    <input type="text" class="form-control form-control-sm" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
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
                                <?php if (count($households) > 0): ?>
                                    <?php $counter = $showing_from; ?>
                                    <?php foreach ($households as $household): ?>
                                        <?php
                                        $head_name = $household['first_name'] . ' ' . 
                                                   (!empty($household['middle_name']) ? substr($household['middle_name'], 0, 1) . '. ' : '') . 
                                                   $household['last_name'];
                                        
                                        $initials = substr($household['first_name'], 0, 1) . substr($household['last_name'], 0, 1);
                                        
                                        $bg_colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'];
                                        $color_index = rand(0, count($bg_colors) - 1);
                                        ?>
                                        <tr>
                                            <td><?php echo $counter; ?></td>
                                            <td>
                                                <span class="fw-bold">HH-<?php echo $household['purok']; ?>-<?php echo str_pad($household['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                                <div class="text-muted small"><?php echo htmlspecialchars($household['address']); ?></div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="resident-image-placeholder <?php echo $bg_colors[$color_index]; ?> text-white">
                                                        <?php echo $initials; ?>
                                                    </div>
                                                    <div><?php echo htmlspecialchars($head_name); ?></div>
                                                </div>
                                            </td>
                                            <td>Purok <?php echo htmlspecialchars($household['purok']); ?></td>
                                            <td><?php echo htmlspecialchars($household['house_number']); ?></td>
                                            <td><?php echo htmlspecialchars($household['type_of_water_source'] ?? 'Not specified'); ?></td>
                                            <td><?php echo htmlspecialchars($household['type_of_toilet_facility'] ?? 'Not specified'); ?></td>
                                            <td><?php echo $household['member_count'] ?? 1; ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary view-btn" data-bs-toggle="tooltip" title="View Details" data-id="<?php echo $household['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning edit-btn" data-bs-toggle="tooltip" title="Edit" data-id="<?php echo $household['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-btn" data-bs-toggle="tooltip" title="Delete" data-id="<?php echo $household['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php $counter++; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="py-3">
                                                <i class="fas fa-house-circle-exclamation fa-3x text-muted mb-3"></i>
                                                <h5>No households found</h5>
                                                <p class="text-muted">Try adjusting your filters or search terms</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                        <div class="text-muted small">
                            Showing <span id="showingFrom"><?php echo $showing_from; ?></span> to <span id="showingTo"><?php echo $showing_to; ?></span> of <span id="totalRecords"><?php echo number_format($total_households); ?></span> entries
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildPaginationLink($page - 1, $limit, $purok_filter, $water_filter, $toilet_filter, $status_filter, $search); ?>">Previous</a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <li class="page-item active">
                                            <span class="page-link"><?php echo $i; ?></span>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo buildPaginationLink($i, $limit, $purok_filter, $water_filter, $toilet_filter, $status_filter, $search); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildPaginationLink($page + 1, $limit, $purok_filter, $water_filter, $toilet_filter, $status_filter, $search); ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
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
    <script src="assets/js/script.js"></script>

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
                const householdData = JSON.parse(this.getAttribute('data-household'));
                
                // Populate modal with household data
                document.getElementById('modal-household-number').textContent = 'HH-' + householdData.purok + '-' + String(householdData.id).padStart(4, '0');
                document.getElementById('modal-purok').textContent = 'Purok ' + householdData.purok;
                document.getElementById('modal-address').textContent = householdData.address;
                document.getElementById('modal-water-source').textContent = householdData.type_of_water_source || 'Not specified';
                document.getElementById('modal-toilet-facility').textContent = householdData.type_of_toilet_facility || 'Not specified';
                
                // Fetch and display household members
                fetch('census-backend.php?house_number=' + householdData.house_number + '&purok=' + householdData.purok)
                    .then(response => response.json())
                    .then(data => {
                        const membersList = document.getElementById('modal-members-list');
                        membersList.innerHTML = '';
                        
                        if (data.success && data.members.length > 0) {
                            data.members.forEach(member => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${member.first_name} ${member.middle_name || ''} ${member.last_name}</td>
                                    <td>${member.relationship_to_head || 'Not specified'}</td>
                                    <td>${member.age}</td>
                                    <td>${member.sex}</td>
                                    <td>${member.civil_status || 'Not specified'}</td>
                                    <td>${member.occupation || 'Not specified'}</td>
                                    <td>${member.educational_attainment || 'Not specified'}</td>
                                    <td>${member.philhealth_number ? 'Yes' : 'No'}</td>
                                `;
                                membersList.appendChild(row);
                            });
                        } else {
                            membersList.innerHTML = '<tr><td colspan="8" class="text-center">No members found</td></tr>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching household members:', error);
                        document.getElementById('modal-members-list').innerHTML = '<tr><td colspan="8" class="text-center">Error loading members</td></tr>';
                    });
                
                var modal = new bootstrap.Modal(document.getElementById('householdModal'));
                modal.show();
            });
        });
        
        // Edit household
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                var householdId = this.getAttribute('data-id');
                // Redirect to edit page or show edit modal
                window.location.href = 'edit_household.php?id=' + householdId;
            });
        });
        
        // Delete household functionality
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                var householdId = this.getAttribute('data-id');
                
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
                        // AJAX request to delete household using the backend
                        fetch('census-backend.php?id=' + householdId, {
                            method: 'DELETE'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'Household record has been deleted.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'Failed to delete household record: ' + data.message,
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                'An error occurred while deleting the record.',
                                'error'
                            );
                        });
                    }
                });
            });
        });
        
        // Export to Excel functionality
        document.getElementById('exportExcelBtn').addEventListener('click', function() {
            // Build export URL with current filters
            const params = new URLSearchParams({
                export: 'excel',
                purok: '<?php echo $purok_filter; ?>',
                water_source: '<?php echo $water_filter; ?>',
                toilet_facility: '<?php echo $toilet_filter; ?>',
                status: '<?php echo $status_filter; ?>',
                search: '<?php echo $search; ?>'
            });
            
            Swal.fire({
                title: 'Export to Excel',
                text: 'Preparing data for export...',
                icon: 'info',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'export_census.php?' + params.toString();
            });
        });
    });
    
    function updateLimit(limit) {
        const url = new URL(window.location.href);
        url.searchParams.set('limit', limit);
        url.searchParams.set('page', 1); // Reset to first page
        window.location.href = url.toString();
    }
</script>
</body>
</html>

<?php
// Helper function to build pagination links
function buildPaginationLink($page, $limit, $purok, $water, $toilet, $status, $search) {
    $params = [
        'page' => $page,
        'limit' => $limit
    ];
    
    if (!empty($purok)) $params['purok'] = $purok;
    if (!empty($water)) $params['water_source'] = $water;
    if (!empty($toilet)) $params['toilet_facility'] = $toilet;
    if (!empty($status)) $params['status'] = $status;
    if (!empty($search)) $params['search'] = $search;
    
    return 'census.php?' . http_build_query($params);
}

// Close connection at the very end
$conn->close();
?>