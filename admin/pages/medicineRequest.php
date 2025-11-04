<?php
// admin/pages/medicineRequest.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAuth();

// Fetch data
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Pagination for requests
$requests_page = isset($_GET['req_page']) ? max(1, intval($_GET['req_page'])) : 1;
$requests_per_page = isset($_GET['req_per_page']) ? max(10, min(100, intval($_GET['req_per_page']))) : 25;
$requests_offset = ($requests_page - 1) * $requests_per_page;

// Pagination for inventory
$inventory_page = isset($_GET['inv_page']) ? max(1, intval($_GET['inv_page'])) : 1;
$inventory_per_page = isset($_GET['inv_per_page']) ? max(10, min(100, intval($_GET['inv_per_page']))) : 25;
$inventory_offset = ($inventory_page - 1) * $inventory_per_page;

$where_clause = '';
if ($status_filter !== 'all') {
    $status = $conn->real_escape_string($status_filter);
    $where_clause = "WHERE mr.status = '$status'";
}

// Count total requests
$count_query = "SELECT COUNT(*) as total FROM medicine_requests mr $where_clause";
$count_result = $conn->query($count_query);
$total_requests = $count_result->fetch_assoc()['total'];
$total_requests_pages = ceil($total_requests / $requests_per_page);

// Fetch requests with pagination
$requests_query = "
    SELECT mr.*, r.first_name, r.last_name, r.email
    FROM medicine_requests mr
    JOIN residents r ON mr.resident_id = r.id
    $where_clause
    ORDER BY mr.created_at DESC
    LIMIT $requests_per_page OFFSET $requests_offset
";
$requests_result = $conn->query($requests_query);

// Initialize counters
$status_counts = [
    'Pending' => 0,
    'Approved' => 0,
    'Rejected' => 0,
    'Disapproved' => 0
];

// Count per status
$status_count_query = "
    SELECT status, COUNT(*) as count 
    FROM medicine_requests 
    GROUP BY status
";
$status_count_result = $conn->query($status_count_query);
while ($row = $status_count_result->fetch_assoc()) {
    $status = ucfirst(strtolower($row['status']));
    if (isset($status_counts[$status])) {
        $status_counts[$status] = $row['count'];
    }
}

// Count total inventory
$count_inventory_query = "SELECT COUNT(*) as total FROM medicine_inventory";
$count_inventory_result = $conn->query($count_inventory_query);
$total_inventory = $count_inventory_result->fetch_assoc()['total'];
$total_inventory_pages = ceil($total_inventory / $inventory_per_page);

// Fetch medicine inventory with pagination
$inventory_query = "
    SELECT * FROM medicine_inventory 
    ORDER BY medicine_name 
    LIMIT $inventory_per_page OFFSET $inventory_offset
";
$inventory_result = $conn->query($inventory_query);

function getStatusBadgeClass($status) {
    switch(strtolower($status)) {
        case 'pending': return 'bg-warning';
        case 'approved': return 'bg-success';
        case 'disapproved': return 'bg-danger';
        case 'completed': return 'bg-info';
        default: return 'bg-secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Management - Barangay Balas Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/medicineReq.css">
</head>
<body class="sb-nav-fixed">
    <?php include '../includes/navbar.php'; ?>
    <div id="layoutSidenav">
        <?php include '../includes/sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <div class="dashboard-header mt-4">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h1 class="mb-1"><i class="fas fa-capsules me-2"></i>Medicine</h1>
                                <p class="mb-0">Manage medicine and requests</p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                                    <button class="btn btn-outline-light">
                                        <i class="fas fa-calendar me-1"></i> <?= date('F j, Y') ?>
                                    </button>
                                    <button class="btn btn-outline-light" onclick="location.reload()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="quick-stats">
                        <?php
                        // Calculate stats
                        $pending_count = $status_counts['Pending'];
                        $approved_count = $status_counts['Approved'];
                        
                        // Get low stock and total medicines from full inventory
                        $stats_query = "
                            SELECT 
                                COUNT(*) as total_medicines,
                                SUM(CASE WHEN stock_quantity <= minimum_stock THEN 1 ELSE 0 END) as low_stock_count
                            FROM medicine_inventory
                        ";
                        $stats_result = $conn->query($stats_query);
                        $stats = $stats_result->fetch_assoc();
                        $low_stock_count = $stats['low_stock_count'];
                        $total_medicines = $stats['total_medicines'];
                        ?>
                        <div class="quick-stat-item">
                            <div class="metric-value"><?= $pending_count ?></div>
                            <div class="metric-label">Pending Requests</div>
                        </div>
                        <div class="quick-stat-item">
                            <div class="metric-value"><?= $approved_count ?></div>
                            <div class="metric-label">Approved This Month</div>
                        </div>
                        <div class="quick-stat-item">
                            <div class="metric-value"><?= $low_stock_count ?></div>
                            <div class="metric-label">Low Stock Items</div>
                        </div>
                        <div class="quick-stat-item">
                            <div class="metric-value"><?= $total_medicines ?></div>
                            <div class="metric-label">Total Medicines</div>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <!-- Main Content Tabs -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="medicineTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                                        <i class="fas fa-capsules me-1"></i> Medicine Inventory
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests" type="button" role="tab">
                                        <i class="fas fa-list me-1"></i> Medicine Requests
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="medicineTabsContent">
                                <!-- Inventory Tab -->
                                <div class="tab-pane fade show active" id="inventory" role="tabpanel">
                                    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div class="input-group" style="max-width: 300px;">
                                            <input type="text" class="form-control" placeholder="Search medicines..." id="searchMedicine">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <select class="form-select" style="width: auto;" id="inventoryPerPage" onchange="changeInventoryPerPage(this.value)">
                                                <option value="10" <?= $inventory_per_page == 10 ? 'selected' : '' ?>>10 per page</option>
                                                <option value="25" <?= $inventory_per_page == 25 ? 'selected' : '' ?>>25 per page</option>
                                                <option value="50" <?= $inventory_per_page == 50 ? 'selected' : '' ?>>50 per page</option>
                                                <option value="100" <?= $inventory_per_page == 100 ? 'selected' : '' ?>>100 per page</option>
                                            </select>
                                            <button class="btn btn-outline-success" id="exportInventoryBtn">
                                                <i class="fas fa-file-excel me-1"></i> Export Inventory
                                            </button>
                                            <?php if (canModify()) : ?>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMedicineModal">
                                                <i class="fas fa-plus me-1"></i> Add New Medicine
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                                        <table class="table table-hover table-bordered" style="width:100%; min-width: 800px;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Medicine Name</th>
                                                    <th>Category</th>
                                                    <th>Stock Quantity</th>
                                                    <th>Minimum Stock</th>
                                                    <th>Unit</th>
                                                    <th>Status</th>
                                                    <?php if (canModify()) : ?>
                                                    <th>Actions</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                while ($medicine = $inventory_result->fetch_assoc()): 
                                                    $isLowStock = $medicine['stock_quantity'] <= $medicine['minimum_stock'];
                                                ?>
                                                <tr class="<?= $isLowStock ? 'low-stock' : '' ?>">
                                                    <td><?= htmlspecialchars($medicine['medicine_name']) ?></td>
                                                    <td><?= htmlspecialchars($medicine['category']) ?></td>
                                                    <td>
                                                        <span class="<?= $isLowStock ? 'text-danger fw-bold' : '' ?>">
                                                            <?= $medicine['stock_quantity'] ?>
                                                        </span>
                                                    </td>
                                                    <td><?= $medicine['minimum_stock'] ?></td>
                                                    <td><?= htmlspecialchars($medicine['unit']) ?></td>
                                                    <td>
                                                        <span class="badge <?= $medicine['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                            <?= $medicine['is_active'] ? 'Active' : 'Inactive' ?>
                                                        </span>
                                                    </td>
                                                    <?php if (canModify()) : ?>
                                                    <td class="table-actions">
                                                        <button class="btn btn-sm btn-warning edit-medicine-btn" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editMedicineModal"
                                                                data-id="<?= $medicine['id'] ?>"
                                                                data-name="<?= htmlspecialchars($medicine['medicine_name']) ?>"
                                                                data-category="<?= htmlspecialchars($medicine['category']) ?>"
                                                                data-description="<?= htmlspecialchars($medicine['description']) ?>"
                                                                data-stock="<?= $medicine['stock_quantity'] ?>"
                                                                data-min-stock="<?= $medicine['minimum_stock'] ?>"
                                                                data-unit="<?= htmlspecialchars($medicine['unit']) ?>"
                                                                data-active="<?= $medicine['is_active'] ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger delete-medicine-btn" 
                                                                data-id="<?= $medicine['id'] ?>"
                                                                data-name="<?= htmlspecialchars($medicine['medicine_name']) ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Inventory Pagination -->
                                    <?php if ($total_inventory_pages > 1): ?>
                                    <nav aria-label="Inventory pagination">
                                        <ul class="pagination justify-content-center">
                                            <li class="page-item <?= $inventory_page <= 1 ? 'disabled' : '' ?>">
                                                <a class="page-link" href="?inv_page=<?= $inventory_page - 1 ?>&inv_per_page=<?= $inventory_per_page ?>&status=<?= $status_filter ?>">Previous</a>
                                            </li>
                                            <?php for ($i = 1; $i <= $total_inventory_pages; $i++): ?>
                                                <?php if ($i == 1 || $i == $total_inventory_pages || abs($i - $inventory_page) <= 2): ?>
                                                    <li class="page-item <?= $i == $inventory_page ? 'active' : '' ?>">
                                                        <a class="page-link" href="?inv_page=<?= $i ?>&inv_per_page=<?= $inventory_per_page ?>&status=<?= $status_filter ?>"><?= $i ?></a>
                                                    </li>
                                                <?php elseif (abs($i - $inventory_page) == 3): ?>
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <li class="page-item <?= $inventory_page >= $total_inventory_pages ? 'disabled' : '' ?>">
                                                <a class="page-link" href="?inv_page=<?= $inventory_page + 1 ?>&inv_per_page=<?= $inventory_per_page ?>&status=<?= $status_filter ?>">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                    <div class="text-center text-muted small">
                                        Showing <?= min($inventory_offset + 1, $total_inventory) ?> to <?= min($inventory_offset + $inventory_per_page, $total_inventory) ?> of <?= $total_inventory ?> entries
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Requests Tab -->
                                <div class="tab-pane fade" id="requests" role="tabpanel">
                                    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div class="input-group" style="max-width: 300px;">
                                            <input type="text" class="form-control" placeholder="Search requests..." id="searchRequest">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <select class="form-select" style="width: auto;" id="requestsPerPage" onchange="changeRequestsPerPage(this.value)">
                                                <option value="10" <?= $requests_per_page == 10 ? 'selected' : '' ?>>10 per page</option>
                                                <option value="25" <?= $requests_per_page == 25 ? 'selected' : '' ?>>25 per page</option>
                                                <option value="50" <?= $requests_per_page == 50 ? 'selected' : '' ?>>50 per page</option>
                                                <option value="100" <?= $requests_per_page == 100 ? 'selected' : '' ?>>100 per page</option>
                                            </select>
                                            <form method="GET" class="d-flex gap-2">
                                                <input type="hidden" name="req_page" value="<?= $requests_page ?>">
                                                <input type="hidden" name="req_per_page" value="<?= $requests_per_page ?>">
                                                <select class="form-select" name="status" onchange="this.form.submit()" style="width: auto;">
                                                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>
                                                        All (<?= $total_requests ?>)
                                                    </option>
                                                    <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>
                                                        Pending (<?= $status_counts['Pending'] ?>)
                                                    </option>
                                                    <option value="Approved" <?= $status_filter === 'Approved' ? 'selected' : '' ?>>
                                                        Approved (<?= $status_counts['Approved'] ?>)
                                                    </option>
                                                    <option value="Disapproved" <?= $status_filter === 'Disapproved' ? 'selected' : '' ?>>
                                                        Disapproved (<?= $status_counts['Disapproved'] ?>)
                                                    </option>
                                                </select>
                                                <?php if ($status_filter !== 'all'): ?>
                                                <a href="?req_page=1&req_per_page=<?= $requests_per_page ?>" class="btn btn-outline-secondary">
                                                    <i class="fas fa-times me-1"></i>Clear
                                                </a>
                                                <?php endif; ?>
                                            </form>
                                            <button class="btn btn-outline-success" id="exportRequestsBtn">
                                                <i class="fas fa-file-excel me-1"></i> Export Requests
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive" style="overflow-x: auto; max-width: 100%">
                                        <table class="table table-hover table-bordered" style="width:100%; min-width: 800px;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Request ID</th>
                                                    <th>Resident</th>
                                                    <th>Medicine</th>
                                                    <th>Medical Condition</th>
                                                    <th>Urgency</th>
                                                    <th>Date Requested</th>
                                                    <th>Status</th>
                                                    <?php if (canModify()) : ?>
                                                    <th>Actions</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                while ($request = $requests_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($request['request_number']) ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($request['email']) ?></small>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-pills medicine-icon me-2"></i>
                                                        <?= htmlspecialchars($request['medicine_name']) ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($request['medical_condition']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= 
                                                            $request['urgency_level'] === 'emergency' ? 'danger' : 
                                                            ($request['urgency_level'] === 'high' ? 'warning' : 
                                                            ($request['urgency_level'] === 'medium' ? 'info' : 'secondary'))
                                                        ?>">
                                                            <?= ucfirst($request['urgency_level']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('M j, Y g:i A', strtotime($request['date_requested'])) ?></td>
                                                    <td>
                                                        <span class="badge <?= getStatusBadgeClass($request['status']) ?>">
                                                            <?= $request['status'] ?>
                                                        </span>
                                                    </td>
                                                    <?php if (canModify()) : ?>
                                                    <td class="table-actions">
                                                        <button class="btn btn-sm btn-info view-request-btn" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#viewRequestModal"
                                                                data-request='<?= htmlspecialchars(json_encode($request), ENT_QUOTES, 'UTF-8') ?>'>
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($request['status'] === 'Pending'): ?>
                                                        <button class="btn btn-sm btn-success update-status-btn" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#updateStatusModal"
                                                                data-id="<?= $request['id'] ?>"
                                                                data-status="Approved">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger update-status-btn" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#updateStatusModal"
                                                                data-id="<?= $request['id'] ?>"
                                                                data-status="Disapproved">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php endif; ?>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Requests Pagination -->
                                    <?php if ($total_requests_pages > 1): ?>
                                    <nav aria-label="Requests pagination">
                                        <ul class="pagination justify-content-center">
                                            <li class="page-item <?= $requests_page <= 1 ? 'disabled' : '' ?>">
                                                <a class="page-link" href="?req_page=<?= $requests_page - 1 ?>&req_per_page=<?= $requests_per_page ?>&status=<?= $status_filter ?>">Previous</a>
                                            </li>
                                            <?php for ($i = 1; $i <= $total_requests_pages; $i++): ?>
                                                <?php if ($i == 1 || $i == $total_requests_pages || abs($i - $requests_page) <= 2): ?>
                                                    <li class="page-item <?= $i == $requests_page ? 'active' : '' ?>">
                                                        <a class="page-link" href="?req_page=<?= $i ?>&req_per_page=<?= $requests_per_page ?>&status=<?= $status_filter ?>"><?= $i ?></a>
                                                    </li>
                                                <?php elseif (abs($i - $requests_page) == 3): ?>
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <li class="page-item <?= $requests_page >= $total_requests_pages ? 'disabled' : '' ?>">
                                                <a class="page-link" href="?req_page=<?= $requests_page + 1 ?>&req_per_page=<?= $requests_per_page ?>&status=<?= $status_filter ?>">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                    <div class="text-center text-muted small">
                                        Showing <?= min($requests_offset + 1, $total_requests) ?> to <?= min($requests_offset + $requests_per_page, $total_requests) ?> of <?= $total_requests ?> entries
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Include Modals -->
    <?php include '../modals/medicineReqModal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/medicineReq.js"></script>
    <script>
        function changeInventoryPerPage(perPage) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('inv_per_page', perPage);
            urlParams.set('inv_page', '1'); // Reset to first page
            window.location.search = urlParams.toString();
        }

        function changeRequestsPerPage(perPage) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('req_per_page', perPage);
            urlParams.set('req_page', '1'); // Reset to first page
            window.location.search = urlParams.toString();
        }
    </script>
</body>
</html>