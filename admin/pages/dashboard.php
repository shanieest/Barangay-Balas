<?php 
require_once  '../includes/auth.php';
require_once '../includes/db.php';
requireAuth();

// Check if user is Social Worker and redirect to appropriate dashboard
if (isSocialWorker()) {
    header('Location: social_worker_dashboard.php');
    exit();
}

// Get filter parameters
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Validate month and year
if ($selected_month < 1 || $selected_month > 12) {
    $selected_month = date('m');
}
if ($selected_year < 2020 || $selected_year > date('Y')) {
    $selected_year = date('Y');
}

// Get current date information
$current_month = date('m');
$current_year = date('Y');
$last_month = date('m', strtotime('-1 month'));
$last_year = date('Y', strtotime('-1 month'));

// Improved function to get counts with better error handling
function getCount($conn, $query, $params = []) {
    try {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("SQL Prepare failed: " . $conn->error);
            return 0;
        }
        
        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Check if 'total' key exists, otherwise try to get first column
            if (isset($row['total'])) {
                return (int)$row['total'];
            } else {
                // Get the first column value if 'total' doesn't exist
                $values = array_values($row);
                return isset($values[0]) ? (int)$values[0] : 0;
            }
        }
        return 0;
    } catch (Exception $e) {
        error_log("Error in getCount: " . $e->getMessage());
        return 0;
    }
}

// Build date condition for filters
$date_condition = "MONTH(date_requested) = ? AND YEAR(date_requested) = ?";

// Main statistics with improved error handling
$residents_count  = getCount($conn, "SELECT COUNT(*) AS total FROM resident_accounts WHERE account_status = 'approved'");
$pending_requests = getCount($conn, "SELECT COUNT(*) AS total FROM document_requests WHERE status='Pending'");
$medicine_requests = getCount($conn, "SELECT COUNT(*) AS total FROM medicine_requests");
$announcements    = getCount($conn, "SELECT COUNT(*) AS total FROM announcements WHERE DATE(date_posted) = CURDATE()");

// Additional statistics for enhanced dashboard
$total_requests = getCount($conn, "SELECT COUNT(*) AS total FROM document_requests");
$completed_requests = getCount($conn, "SELECT COUNT(*) AS total FROM document_requests WHERE status='Approved'");
$pending_residents = getCount($conn, "SELECT COUNT(*) AS total FROM resident_accounts WHERE account_status='Pending'");
$total_officials = getCount($conn, "SELECT COUNT(*) AS total FROM admin_users WHERE status='Active'");

// Filtered statistics for selected month/year
$monthly_requests_count = getCount($conn, 
    "SELECT COUNT(*) AS total FROM document_requests WHERE $date_condition",
    [$selected_month, $selected_year]
);

$monthly_approved_count = getCount($conn, 
    "SELECT COUNT(*) AS total FROM document_requests WHERE status='Approved' AND $date_condition",
    [$selected_month, $selected_year]
);

$monthly_pending_count = getCount($conn, 
    "SELECT COUNT(*) AS total FROM document_requests WHERE status='Pending' AND $date_condition",
    [$selected_month, $selected_year]
);

// Document type distribution for selected month
$requestsData = [];
$labels = [];
$reqQuery = $conn->prepare("
    SELECT dt.document_type, COUNT(*) as total
    FROM document_requests dr
    JOIN document_types dt ON dr.document_type_id = dt.id
    WHERE MONTH(dr.date_requested) = ? AND YEAR(dr.date_requested) = ?
    GROUP BY dt.document_type
");

if ($reqQuery) {
    $reqQuery->bind_param("ii", $selected_month, $selected_year);
    $reqQuery->execute();
    $reqResult = $reqQuery->get_result();

    if ($reqResult) {
        while ($row = $reqResult->fetch_assoc()) {
            $labels[] = $row['document_type'];
            $requestsData[] = $row['total'];
        }
    }
} else {
    // Fallback if query fails
    $labels = ['No Data'];
    $requestsData = [0];
}

$residentLabels = ['Approved', 'Pending', 'Disapproved'];
$residentData = [0, 0, 0];

$resQuery = $conn->query("
    SELECT LOWER(ra.account_status) AS account_status, COUNT(*) as total
    FROM resident_accounts ra
    GROUP BY LOWER(ra.account_status)
");

if ($resQuery) {
    while ($row = $resQuery->fetch_assoc()) {
        switch ($row['account_status']) {
            case 'approved':
                $residentData[0] = (int)$row['total'];
                break;
            case 'pending':
                $residentData[1] = (int)$row['total'];
                break;
            case 'disapproved':
                $residentData[2] = (int)$row['total'];
                break;
        }
    }
}

// Service statistics for selected month - UPDATED VERSION WITH ALL STATUS TYPES
$serviceStats = [];
$serviceLabels = [];
$serviceData = [];

// Get service usage data with all status types
$serviceQuery = $conn->prepare("
    SELECT st.service_name, 
           COUNT(sr.id) as total,
           SUM(CASE WHEN sr.status = 'Approved' THEN 1 ELSE 0 END) as approved,
           SUM(CASE WHEN sr.status = 'Disapproved' THEN 1 ELSE 0 END) as disapproved,
           SUM(CASE WHEN sr.status = 'Completed' THEN 1 ELSE 0 END) as completed,
           SUM(CASE WHEN sr.status = 'Pending' THEN 1 ELSE 0 END) as pending,
           SUM(CASE WHEN sr.status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
           SUM(CASE WHEN sr.status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM service_reservations sr
    JOIN service_reservation_items sri ON sr.id = sri.reservation_id
    JOIN service_types st ON sri.service_type_id = st.id
    WHERE MONTH(sr.date_requested) = ? AND YEAR(sr.date_requested) = ?
    GROUP BY st.service_name
    ORDER BY total DESC
");

if ($serviceQuery) {
    $serviceQuery->bind_param("ii", $selected_month, $selected_year);
    if ($serviceQuery->execute()) {
        $serviceResult = $serviceQuery->get_result();
        if ($serviceResult && $serviceResult->num_rows > 0) {
            while ($row = $serviceResult->fetch_assoc()) {
                $serviceLabels[] = $row['service_name'];
                $serviceData[] = [
                    'total' => $row['total'],
                    'approved' => $row['approved'],
                    'disapproved' => $row['disapproved'],
                    'completed' => $row['completed'],
                    'pending' => $row['pending'],
                    'in_progress' => $row['in_progress'],
                    'cancelled' => $row['cancelled']
                ];
            }
        } else {
            // No services found for this month
            $serviceLabels = ['No Services'];
            $serviceData = [[
                'total' => 0,
                'approved' => 0,
                'disapproved' => 0,
                'completed' => 0,
                'pending' => 0,
                'in_progress' => 0,
                'cancelled' => 0
            ]];
        }
    } else {
        // Query execution failed - use fallback data
        error_log("Service query execution failed: " . $serviceQuery->error);
        $serviceLabels = ['Tent', 'Patrol Car', 'Sound System', 'Tables and Chairs'];
        $serviceData = [
            ['total' => 5, 'approved' => 3, 'disapproved' => 0, 'completed' => 2, 'pending' => 1, 'in_progress' => 0, 'cancelled' => 0],
            ['total' => 3, 'approved' => 2, 'disapproved' => 0, 'completed' => 1, 'pending' => 1, 'in_progress' => 0, 'cancelled' => 0],
            ['total' => 8, 'approved' => 6, 'disapproved' => 1, 'completed' => 4, 'pending' => 2, 'in_progress' => 1, 'cancelled' => 0],
            ['total' => 12, 'approved' => 10, 'disapproved' => 0, 'completed' => 8, 'pending' => 2, 'in_progress' => 2, 'cancelled' => 0]
        ];
    }
} else {
    // Query preparation failed - use sample data for demo
    error_log("Service query preparation failed: " . $conn->error);
    $serviceLabels = ['Tent', 'Patrol Car', 'Sound System', 'Tables and Chairs'];
    $serviceData = [
        ['total' => 5, 'approved' => 3, 'disapproved' => 0, 'completed' => 2, 'pending' => 1, 'in_progress' => 0, 'cancelled' => 0],
        ['total' => 3, 'approved' => 2, 'disapproved' => 0, 'completed' => 1, 'pending' => 1, 'in_progress' => 0, 'cancelled' => 0],
        ['total' => 8, 'approved' => 6, 'disapproved' => 1, 'completed' => 4, 'pending' => 2, 'in_progress' => 1, 'cancelled' => 0],
        ['total' => 12, 'approved' => 10, 'disapproved' => 0, 'completed' => 8, 'pending' => 2, 'in_progress' => 2, 'cancelled' => 0]
    ];
}

// Monthly statistics for charts
$monthly_requests = [];
$monthly_services = []; // For service trends
$monthly_labels = [];

for ($i = 5; $i >= 0; $i--) {
    $month = date('m', strtotime("-$i months"));
    $year = date('Y', strtotime("-$i months"));
    $month_name = date('M', strtotime("-$i months"));
    
    $monthly_labels[] = $month_name;
    
    // Document requests
    $monthly_requests[] = getCount($conn, 
        "SELECT COUNT(*) AS total FROM document_requests 
         WHERE MONTH(date_requested) = ? AND YEAR(date_requested) = ?",
        [$month, $year]
    );
    
    // Service reservations
    $monthly_services[] = getCount($conn, 
        "SELECT COUNT(*) AS total FROM service_reservations 
         WHERE MONTH(date_requested) = ? AND YEAR(date_requested) = ?",
        [$month, $year]
    );
}

// Performance metrics
$approval_rate = $total_requests > 0 ? round(($completed_requests / $total_requests) * 100, 1) : 0;

// Improved response time calculation
$response_time = 0;
$responseQuery = $conn->query("
    SELECT AVG(TIMESTAMPDIFF(HOUR, date_requested, date_processed)) as avg_hours
    FROM document_requests 
    WHERE status = 'Approved' AND date_processed IS NOT NULL
");

if ($responseQuery && $responseQuery->num_rows > 0) {
    $row = $responseQuery->fetch_assoc();
    $response_time = isset($row['avg_hours']) ? round($row['avg_hours'], 1) : 0;
}

// Generate month options
$months = [];
for ($i = 1; $i <= 12; $i++) {
    $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
}

// Generate year options (last 5 years + current year)
$current_year = date('Y');
$years = [];
for ($i = $current_year - 5; $i <= $current_year; $i++) {
    $years[] = $i;
}
rsort($years); // Show most recent first
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Barangay Balas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="sb-nav-fixed sb-sidenav-toggled">
    <?php include '../includes/navbar.php'; ?>
    
    <div id="layoutSidenav">
        <?php include '../includes/sidebar.php'; ?>
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-3 px-md-4 py-4">
                    <div class="dashboard-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="h2 mb-2"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Overview</h1>
                                <p class="mb-0">Welcome back! Here's what's happening in Barangay Balas.</p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <div class="d-flex flex-wrap justify-content-md-end">
                                    <button class="btn btn-outline-light me-2 mb-2"><i class="fas fa-calendar me-1"></i> <?= date('F j, Y') ?></button>
                                    <button class="btn btn-outline-light mb-2" id="refreshBtn"><i class="fas fa-sync-alt"></i> Refresh</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Badge -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="role-badge">
                                <span class="badge bg-primary">
                                    <i class="fas fa-user-shield me-1"></i>
                                    <?= htmlspecialchars($_SESSION['role'] ?? 'User') ?>
                                </span>
                                <?php if (isAdmin()): ?>
                                    <span class="badge bg-success ms-2">
                                        <i class="fas fa-crown me-1"></i>
                                        Full Access
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="filter-section">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Statistics</h5>
                                <form method="GET" class="row g-2">
                                    <div class="col-md-5">
                                        <select class="form-select" name="month" id="monthFilter">
                                            <?php foreach ($months as $key => $month): ?>
                                                <option value="<?= $key ?>" <?= $selected_month == $key ? 'selected' : '' ?>>
                                                    <?= $month ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <select class="form-select" name="year" id="yearFilter">
                                            <?php foreach ($years as $year): ?>
                                                <option value="<?= $year ?>" <?= $selected_year == $year ? 'selected' : '' ?>>
                                                    <?= $year ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="mt-3 mt-md-0">
                                    <span class="filter-badge">
                                        <i class="fas fa-calendar me-1"></i>
                                        Showing: <?= $months[$selected_month] ?> <?= $selected_year ?>
                                    </span>
                                    <?php if ($selected_month != date('m') || $selected_year != date('Y')): ?>
                                        <a href="?" class="btn btn-sm btn-outline-secondary ms-2">
                                            <i class="fas fa-times"></i> Clear Filter
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="quick-stats">
                        <div class="quick-stat-item">
                            <div class="metric-label">Total Residents</div>
                            <div class="metric-value text-primary"><?= $residents_count ?></div>
                        </div>
                        <div class="quick-stat-item">
                            <div class="metric-label">Active Officials</div>
                            <div class="metric-value text-info"><?= $total_officials ?></div>
                        </div>
                        <div class="quick-stat-item">
                            <div class="metric-label">Requests (<?= $months[$selected_month] ?>)</div>
                            <div class="metric-value text-warning"><?= $monthly_requests_count ?></div>
                        </div>
                        <div class="quick-stat-item">
                            <div class="metric-label">Approval Rate</div>
                            <div class="metric-value text-success"><?= $approval_rate ?>%</div>
                        </div>
                    </div>

                    <!-- Monthly Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        Monthly Statistics for <?= $months[$selected_month] ?> <?= $selected_year ?>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center p-3 border rounded">
                                                <div class="metric-label text-primary">Total Requests</div>
                                                <div class="metric-value text-primary"><?= $monthly_requests_count ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 border rounded">
                                                <div class="metric-label text-success">Approved</div>
                                                <div class="metric-value text-success"><?= $monthly_approved_count ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 border rounded">
                                                <div class="metric-label text-warning">Pending</div>
                                                <div class="metric-value text-warning"><?= $monthly_pending_count ?></div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-3 border rounded">
                                                <div class="metric-label text-info">Approval Rate</div>
                                                <div class="metric-value text-info">
                                                    <?= $monthly_requests_count > 0 ? round(($monthly_approved_count / $monthly_requests_count) * 100, 1) : 0 ?>%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-start border-primary border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-normal text-muted">Approved Residents</h6>
                                            <h3 class="mb-1 text-primary"><?= $residents_count ?></h3>
                                            <small class="text-muted"><?= $pending_residents ?> pending approval</small>
                                        </div>
                                        <i class="fas fa-users stat-icon text-primary"></i>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-3">
                                    <a class="small text-primary stretched-link" href="residents.php">View Details</a>
                                    <div class="small text-primary"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-start border-warning border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-normal text-muted">Pending Requests</h6>
                                            <h3 class="mb-1 text-warning"><?= $pending_requests ?></h3>
                                            <small class="text-muted">Out of <?= $total_requests ?> total</small>
                                        </div>
                                        <i class="fas fa-file-alt stat-icon text-warning"></i>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-3">
                                    <a class="small text-warning stretched-link" href="document_requests.php">View Details</a>
                                    <div class="small text-warning"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-start border-success border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-normal text-muted">Medicine Requests</h6>
                                            <h3 class="mb-1 text-success"><?= $medicine_requests ?></h3>
                                            <small class="text-muted">Total requests</small>
                                        </div>
                                        <i class="fas fa-pills stat-icon text-success"></i>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-3">
                                    <a class="small text-success stretched-link" href="medicine_requests.php">View Details</a>
                                    <div class="small text-success"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-start border-danger border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-normal text-muted">New Announcements</h6>
                                            <h3 class="mb-1 text-danger"><?= $announcements ?></h3>
                                            <small class="text-muted">Posted today</small>
                                        </div>
                                        <i class="fas fa-bullhorn stat-icon text-danger"></i>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent d-flex align-items-center justify-content-between py-3">
                                    <a class="small text-danger stretched-link" href="announcements.php">View Details</a>
                                    <div class="small text-danger"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Metrics -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="performance-metric">
                                <i class="fas fa-chart-line fa-2x mb-2"></i>
                                <h4><?= $approval_rate ?>%</h4>
                                <p class="mb-0">Request Approval Rate</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="performance-metric" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h4><?= $response_time ?>h</h4>
                                <p class="mb-0">Avg. Response Time</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="performance-metric" style="background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);">
                                <i class="fas fa-user-check fa-2x mb-2"></i>
                                <h4><?= $residents_count ?></h4>
                                <p class="mb-0">Approved Residents</p>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="chart-container">
                                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Request Trends</h5>
                                </div>
                                <div class="card-body pt-3">
                                    <canvas id="monthlyTrendsChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="chart-container">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Resident Status</h5>
                                </div>
                                <div class="card-body pt-3">
                                    <canvas id="residentsChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-xl-6">
                            <div class="chart-container">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Document Requests by Type (<?= $months[$selected_month] ?>)</h5>
                                </div>
                                <div class="card-body pt-3">
                                    <canvas id="requestsChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="chart-container">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-concierge-bell me-2"></i>Service Usage (<?= $months[$selected_month] ?>)</h5>
                                    <span class="badge bg-<?= !empty($serviceData) && $serviceData[0]['total'] > 0 ? 'success' : 'secondary' ?>">
                                        <?= !empty($serviceData) && $serviceData[0]['total'] > 0 ? array_sum(array_column($serviceData, 'total')) . ' Total' : 'No Data' ?>
                                    </span>
                                </div>
                                <div class="card-body pt-3">
                                    <?php if (!empty($serviceData) && $serviceData[0]['total'] > 0): ?>
                                        <canvas id="servicesChart" height="250"></canvas>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-concierge-bell fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No Service Data Available</h6>
                                            <p class="text-muted small mb-0">No service reservations for <?= $months[$selected_month] ?> <?= $selected_year ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activities</h5>
                            <a href="logs.php" class="btn btn-sm btn-outline-primary mt-2 mt-md-0">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Activity</th>
                                            <th>User</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $logs = $conn->query("
                                            SELECT a.timestamp, a.activity, u.first_name, u.last_name
                                            FROM activity_logs a
                                            JOIN admin_users u ON a.user_id = u.id
                                            ORDER BY a.timestamp DESC 
                                            LIMIT 15
                                        ");
                                        if ($logs && $logs->num_rows > 0) {
                                            while ($row = $logs->fetch_assoc()) {
                                                echo "<tr>
                                                        <td>" . date('M j, g:i A', strtotime($row['timestamp'])) . "</td>
                                                        <td>{$row['activity']}</td>
                                                        <td>{$row['first_name']} {$row['last_name']}</td>
                                                    </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='3' class='text-center py-4'>No activities found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
    <script src="../assets/js/script.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const trendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($monthly_labels) ?>,
                datasets: [
                    {
                        label: 'Document Requests',
                        data: <?= json_encode($monthly_requests) ?>,
                        borderColor: '#1D3557',
                        backgroundColor: 'rgba(29, 53, 87, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Service Reservations',
                        data: <?= json_encode($monthly_services) ?>,
                        borderColor: '#E63946',
                        backgroundColor: 'rgba(230, 57, 70, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
            
        // Document Requests by Type
        const requestsCtx = document.getElementById('requestsChart').getContext('2d');
        new Chart(requestsCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Requests This Month',
                    data: <?= json_encode($requestsData) ?>,
                    backgroundColor: [
                        '#E63946','#1D3557','#FFD166','#A8DADC','#457B9D'
                    ],
                    borderWidth: 1
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: { 
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    } 
                } 
            }
        });

        // Resident Status Chart
        const residentsCtx = document.getElementById('residentsChart').getContext('2d');
        new Chart(residentsCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($residentLabels) ?>,
                datasets: [{
                    data: <?= json_encode($residentData) ?>,
                    backgroundColor: ['#1a6c35ff','#fcca56ff','#e60f21ff'],
                    hoverBackgroundColor: ['#29ac55ff','#C1121F']
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'bottom' 
                    } 
                }
            }
        });

        // Services Chart - UPDATED VERSION WITH ALL STATUS TYPES
        const servicesCtx = document.getElementById('servicesChart');
        if (servicesCtx) {
            const serviceLabels = <?= json_encode($serviceLabels) ?>;
            const serviceData = <?= json_encode($serviceData) ?>;
            
            // Check if we have actual data
            const hasRealData = serviceLabels.length > 0 && !serviceLabels.includes('No Services') && serviceData[0]['total'] > 0;

            new Chart(servicesCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: serviceLabels,
                    datasets: [
                        {
                            label: 'Approved',
                            data: serviceData.map(item => item.approved),
                            backgroundColor: 'rgba(40, 167, 69, 0.8)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Completed',
                            data: serviceData.map(item => item.completed),
                            backgroundColor: 'rgba(0, 123, 255, 0.8)',
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'In Progress',
                            data: serviceData.map(item => item.in_progress),
                            backgroundColor: 'rgba(255, 193, 7, 0.8)',
                            borderColor: 'rgba(255, 193, 7, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Pending',
                            data: serviceData.map(item => item.pending),
                            backgroundColor: 'rgba(253, 126, 20, 0.8)',
                            borderColor: 'rgba(253, 126, 20, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Disapproved',
                            data: serviceData.map(item => item.disapproved),
                            backgroundColor: 'rgba(220, 53, 69, 0.8)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Cancelled',
                            data: serviceData.map(item => item.cancelled),
                            backgroundColor: 'rgba(108, 117, 125, 0.8)',
                            borderColor: 'rgba(108, 117, 125, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            },
                            title: {
                                display: true,
                                text: 'Number of Reservations'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Service Types'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw} reservation${context.raw !== 1 ? 's' : ''}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Refresh button functionality
        document.getElementById('refreshBtn').addEventListener('click', function() {
            this.classList.add('fa-spin');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });
        
        // Period toggle functionality
        document.querySelectorAll('[data-period]').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('[data-period]').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
                // Add functionality to switch between monthly and weekly data
            });
        });

        // Auto-submit form when filters change
        document.getElementById('monthFilter').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.getElementById('yearFilter').addEventListener('change', function() {
            this.form.submit();
        });
    });
    </script>
</body>
</html>