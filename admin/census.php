<?php
include 'includes/db.php';

// Initialize variables with default values
$total_households = 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$purok_filter = isset($_GET['purok']) ? $_GET['purok'] : '';

// Calculate offset for pagination
$offset = ($page - 1) * $limit;

// Build the base query
$query = "SELECT h.*, r.first_name, r.last_name, r.middle_name, 
          COUNT(hm.resident_id) as member_count
          FROM households h
          LEFT JOIN household_members hm ON h.id = hm.household_id
          LEFT JOIN residents r ON hm.resident_id = r.id AND hm.relationship_to_head = 'Head'
          WHERE 1=1";

$count_query = "SELECT COUNT(DISTINCT h.id) as total 
                FROM households h
                LEFT JOIN household_members hm ON h.id = hm.household_id
                LEFT JOIN residents r ON hm.resident_id = r.id
                WHERE 1=1";

// ---------------- FILTERS ----------------
$where_conditions = [];
$filter_params = [];
$filter_types = '';

if (!empty($purok_filter)) {
    $where_conditions[] = "h.purok = ?";
    $filter_params[] = $purok_filter;
    $filter_types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(r.first_name LIKE ? OR r.last_name LIKE ? OR h.house_number LIKE ? OR h.purok LIKE ?)";
    $search_term = "%$search%";
    $filter_params[] = $search_term;
    $filter_params[] = $search_term;
    $filter_params[] = $search_term;
    $filter_params[] = $search_term;
    $filter_types .= 'ssss';
}

// Apply filters
if (!empty($where_conditions)) {
    $where_clause = " AND " . implode(" AND ", $where_conditions);
    $query .= $where_clause;
    $count_query .= $where_clause;
}

// ---------------- COUNT QUERY ----------------
$stmt_count = $conn->prepare($count_query);
if (!empty($filter_params)) {
    $stmt_count->bind_param($filter_types, ...$filter_params);
}
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_row = $result_count->fetch_assoc();
$total_households = $total_row['total'] ?? 0;

// ---------------- MAIN QUERY ----------------
$query .= " GROUP BY h.id ORDER BY h.purok, h.house_number LIMIT ? OFFSET ?";

$main_params = $filter_params;
$main_types  = $filter_types . 'ii'; // add types for limit + offset
$main_params[] = $limit;
$main_params[] = $offset;

$stmt = $conn->prepare($query);
$stmt->bind_param($main_types, ...$main_params);
$stmt->execute();
$result = $stmt->get_result();

$households = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $households[] = $row;
    }
}

// Calculate pagination values
$total_pages = ceil($total_households / $limit);
$showing_from = ($page - 1) * $limit + 1;
$showing_to = min($page * $limit, $total_households);

// Function to build pagination links
function buildPaginationLink($page, $limit, $purok, $search) {
    $params = [
        'page' => $page,
        'limit' => $limit
    ];
    
    if (!empty($purok)) $params['purok'] = $purok;
    if (!empty($search)) $params['search'] = $search;
    
    return 'census.php?' . http_build_query($params);
}
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
    .stats-card {
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      text-align: center;
      margin-bottom: 20px;
    }
    .stats-card i {
      font-size: 2rem;
      margin-bottom: 10px;
    }
    .stats-card .number {
      font-size: 1.8rem;
      font-weight: bold;
    }
    .stats-card .label {
      color: #6c757d;
      font-size: 0.9rem;
    }
    .resident-image-placeholder {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      margin-right: 10px;
    }
    .action-buttons .btn {
      padding: 0.25rem 0.5rem;
      font-size: 0.75rem;
    }
    .household-badge {
      font-size: 0.9rem;
      padding: 0.5rem 1rem;
    }
    .bg-light-custom {
      background-color: #f8f9fa;
      border: 1px solid #dee2e6;
    }
    .table-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }
    @media (max-width: 768px) {
      .table-controls {
        flex-direction: column;
        align-items: flex-start;
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
                        <div class="number"><?php echo number_format($total_households); ?></div>
                        <div class="label">Total Households</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card bg-white">
                        <i class="fas fa-users text-info"></i>
                        <div class="number">0</div>
                        <div class="label">Total Approved Residents</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card bg-white">
                        <i class="fas fa-male text-success"></i>
                        <div class="number">0</div>
                        <div class="label">Male Population</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card bg-white">
                        <i class="fas fa-female text-warning"></i>
                        <div class="number">0</div>
                        <div class="label">Female Population</div>
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
                                <i class="fas fa-filter me-1"></i> Filters
                            </button>
                        </div>
                        <div class="collapse show" id="filterCollapse">
                            <div class="card-body py-3">
                                <form id="filterForm" method="GET" action="">
                                    <input type="hidden" name="page" value="1">
                                    <input type="hidden" name="limit" value="<?php echo $limit; ?>">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Purok</label>
                                            <select class="form-select" name="purok" onchange="this.form.submit()">
                                                <option value="" <?php echo empty($purok_filter) ? 'selected' : ''; ?>>All Puroks</option>
                                                <option value="1" <?php echo $purok_filter == '1' ? 'selected' : ''; ?>>Purok 1</option>
                                                <option value="2" <?php echo $purok_filter == '2' ? 'selected' : ''; ?>>Purok 2</option>
                                                <option value="3" <?php echo $purok_filter == '3' ? 'selected' : ''; ?>>Purok 3</option>
                                                <option value="4" <?php echo $purok_filter == '4' ? 'selected' : ''; ?>>Purok 4</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Search</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="search" placeholder="Search by name or house number" value="<?php echo htmlspecialchars($search); ?>">
                                                <button class="btn btn-outline-secondary" type="submit">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12 d-flex justify-content-end">
                                            <a href="census.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-undo me-1"></i> Reset
                                            </a>
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
                    </div>

                    <!-- Main Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="householdsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Head of Family</th>
                                    <th>Purok</th>
                                    <th>House No.</th>
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
                                                <div class="d-flex align-items-center">
                                                    <div class="resident-image-placeholder <?php echo $bg_colors[$color_index]; ?> text-white">
                                                        <?php echo $initials; ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($head_name); ?></div>
                                                        <div class="text-muted small">HH-<?php echo $household['purok']; ?>-<?php echo str_pad($household['id'], 4, '0', STR_PAD_LEFT); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Purok <?php echo htmlspecialchars($household['purok']); ?></td>
                                            <td><?php echo htmlspecialchars($household['house_number']); ?></td>
                                            <td>
                                                <span class="badge bg-info rounded-pill"><?php echo $household['member_count'] ?? 1; ?> members</span>
                                            </td>
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
                                        <td colspan="6" class="text-center py-4">
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
                                    <a class="page-link" href="<?php echo buildPaginationLink($page - 1, $limit, $purok_filter, $search); ?>">Previous</a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <li class="page-item active">
                                            <span class="page-link"><?php echo $i; ?></span>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo buildPaginationLink($i, $limit, $purok_filter, $search); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildPaginationLink($page + 1, $limit, $purok_filter, $search); ?>">Next</a>
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
<script src="assets/js/census.js"></script>

<script>
function updateLimit(newLimit) {
    const url = new URL(window.location.href);
    url.searchParams.set('limit', newLimit);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}
</script>

</body>
</html>

<?php
// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>