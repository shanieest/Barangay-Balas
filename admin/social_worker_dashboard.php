<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireAuth();

// Redirect if not Social Worker
if (!isSocialWorker()) {
    header('Location: dashboard.php');
    exit();
}

// Add the missing getCount function
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

// Get filter parameters
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$school_year = isset($_GET['school_year']) ? $_GET['school_year'] : '2025-2026';

// Validate month and year
if ($selected_month < 1 || $selected_month > 12) {
    $selected_month = date('m');
}
if ($selected_year < 2020 || $selected_year > date('Y')) {
    $selected_year = date('Y');
}

// Function to get daycare statistics
function getDaycareStats($conn, $selected_month, $selected_year, $school_year) {
    $stats = [];
    
    // Total enrolled students
    $stats['total_students'] = getCount($conn, 
        "SELECT COUNT(*) as total FROM daycare_enrollments WHERE school_year = ? AND confirmed = 1",
        [$school_year]
    );
    
    // Total pending applications
    $stats['pending_applications'] = getCount($conn, 
        "SELECT COUNT(*) as total FROM daycare_enrollments WHERE school_year = ? AND confirmed = 0",
        [$school_year]
    );
    
    // New enrollments this month
    $stats['new_enrollments'] = getCount($conn, 
        "SELECT COUNT(*) as total FROM daycare_enrollments 
         WHERE MONTH(created_at) = ? AND YEAR(created_at) = ? AND school_year = ? AND confirmed = 1",
        [$selected_month, $selected_year, $school_year]
    );
    
    // Age group distribution
    $ageStats = [];
    $ageQuery = $conn->prepare("
        SELECT 
            CASE 
                WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 3 AND 4 THEN '3-4 years'
                WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 5 AND 6 THEN '5-6 years'
                ELSE 'Other'
            END as age_group,
            COUNT(*) as count
        FROM daycare_enrollments 
        WHERE school_year = ? AND confirmed = 1
        GROUP BY age_group
    ");
    
    if ($ageQuery) {
        $ageQuery->bind_param("s", $school_year);
        $ageQuery->execute();
        $ageResult = $ageQuery->get_result();
        
        while ($row = $ageResult->fetch_assoc()) {
            $ageStats[$row['age_group']] = $row['count'];
        }
    }
    $stats['age_groups'] = $ageStats;
    
    // Monthly enrollment trend
    $monthly_enrollments = [];
    $monthly_labels = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('m', strtotime("-$i months"));
        $year = date('Y', strtotime("-$i months"));
        $month_name = date('M', strtotime("-$i months"));
        
        $monthly_labels[] = $month_name;
        $monthly_enrollments[] = getCount($conn, 
            "SELECT COUNT(*) as total FROM daycare_enrollments 
             WHERE MONTH(created_at) = ? AND YEAR(created_at) = ? AND school_year = ? AND confirmed = 1",
            [$month, $year, $school_year]
        );
    }
    $stats['monthly_trend'] = $monthly_enrollments;
    $stats['monthly_labels'] = $monthly_labels;
    
    // Gender distribution
    $genderStats = [];
    $genderQuery = $conn->prepare("
        SELECT sex, COUNT(*) as count 
        FROM daycare_enrollments 
        WHERE school_year = ? AND confirmed = 1 
        GROUP BY sex
    ");
    
    if ($genderQuery) {
        $genderQuery->bind_param("s", $school_year);
        $genderQuery->execute();
        $genderResult = $genderQuery->get_result();
        
        while ($row = $genderResult->fetch_assoc()) {
            $genderStats[$row['sex']] = $row['count'];
        }
    }
    $stats['gender_distribution'] = $genderStats;
    
    // Language distribution
    $languageStats = [];
    $languageQuery = $conn->prepare("
        SELECT first_language, COUNT(*) as count 
        FROM daycare_enrollments 
        WHERE school_year = ? AND confirmed = 1 
        GROUP BY first_language
    ");
    
    if ($languageQuery) {
        $languageQuery->bind_param("s", $school_year);
        $languageQuery->execute();
        $languageResult = $languageQuery->get_result();
        
        while ($row = $languageResult->fetch_assoc()) {
            if (!empty($row['first_language'])) {
                $languageStats[$row['first_language']] = $row['count'];
            }
        }
    }
    $stats['language_distribution'] = $languageStats;
    
    // Recent enrollments
    $recentEnrollments = [];
    $recentQuery = $conn->prepare("
        SELECT de.*, au.first_name, au.last_name 
        FROM daycare_enrollments de 
        LEFT JOIN admin_users au ON de.confirmed_by = au.id 
        WHERE de.school_year = ? 
        ORDER BY de.created_at DESC 
        LIMIT 5
    ");
    
    if ($recentQuery) {
        $recentQuery->bind_param("s", $school_year);
        $recentQuery->execute();
        $recentResult = $recentQuery->get_result();
        
        while ($row = $recentResult->fetch_assoc()) {
            $recentEnrollments[] = $row;
        }
    }
    $stats['recent_enrollments'] = $recentEnrollments;
    
    return $stats;
}

// Get daycare statistics
$daycareStats = getDaycareStats($conn, $selected_month, $selected_year, $school_year);

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
rsort($years);

// Generate school year options
$current_school_year = date('Y') . '-' . (date('Y') + 1);
$school_years = [
    '2024-2025',
    '2025-2026',
    '2026-2027',
    '2027-2028'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daycare Analytics | Barangay Balas Social Worker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="sb-nav-fixed sb-sidenav-toggled">
    <?php include 'includes/navbar.php'; ?>
    
    <div id="layoutSidenav">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-3 px-md-4 py-4">
                    <div class="dashboard-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="h2 mb-2"><i class="fas fa-school me-2"></i>Daycare Enrollment Analytics</h1>
                                <p class="mb-0">Welcome back! Here's the daycare enrollment statistics and analytics.</p>
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
                                <span class="badge bg-info">
                                    <i class="fas fa-hands-helping me-1"></i>
                                    Social Worker - Daycare Access
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="filter-section">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Statistics</h5>
                                <form method="GET" class="row g-2">
                                    <div class="col-md-3">
                                        <select class="form-select" name="school_year" id="schoolYearFilter">
                                            <?php foreach ($school_years as $year): ?>
                                                <option value="<?= $year ?>" <?= $school_year == $year ? 'selected' : '' ?>>
                                                    <?= $year ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="month" id="monthFilter">
                                            <?php foreach ($months as $key => $month): ?>
                                                <option value="<?= $key ?>" <?= $selected_month == $key ? 'selected' : '' ?>>
                                                    <?= $month ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="year" id="yearFilter">
                                            <?php foreach ($years as $year): ?>
                                                <option value="<?= $year ?>" <?= $selected_year == $year ? 'selected' : '' ?>>
                                                    <?= $year ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search"></i> Apply
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="mt-3 mt-md-0">
                                    <span class="filter-badge">
                                        <i class="fas fa-calendar me-1"></i>
                                        School Year: <?= $school_year ?>
                                    </span>
                                    <span class="filter-badge ms-2">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Month: <?= $months[$selected_month] ?> <?= $selected_year ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="quick-stats">
                        <div class="quick-stat-item">
                            <div class="metric-label">Total Students</div>
                            <div class="metric-value text-primary"><?= $daycareStats['total_students'] ?></div>
                            <small class="text-muted"><?= $school_year ?> School Year</small>
                        </div>
                        <div class="quick-stat-item">
                            <div class="metric-label">Pending Applications</div>
                            <div class="metric-value text-warning"><?= $daycareStats['pending_applications'] ?></div>
                            <small class="text-muted">Awaiting confirmation</small>
                        </div>
                        <div class="quick-stat-item">
                            <div class="metric-label">New Enrollments</div>
                            <div class="metric-value text-success"><?= $daycareStats['new_enrollments'] ?></div>
                            <small class="text-muted"><?= $months[$selected_month] ?> <?= $selected_year ?></small>
                        </div>
                        <div class="quick-stat-item">
                            <div class="metric-label">Enrollment Rate</div>
                            <div class="metric-value text-info">
                                <?= ($daycareStats['total_students'] + $daycareStats['pending_applications']) > 0 ? 
                                    round(($daycareStats['total_students'] / ($daycareStats['total_students'] + $daycareStats['pending_applications'])) * 100, 1) : 0 ?>%
                            </div>
                            <small class="text-muted">Confirmation rate</small>
                        </div>
                    </div>

                    <!-- Main Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-start border-primary border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-normal text-muted">Currently Enrolled</h6>
                                            <h3 class="mb-1 text-primary"><?= $daycareStats['total_students'] ?></h3>
                                            <small class="text-muted">Confirmed students</small>
                                        </div>
                                        <i class="fas fa-child stat-icon text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-start border-warning border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-normal text-muted">Pending Applications</h6>
                                            <h3 class="mb-1 text-warning"><?= $daycareStats['pending_applications'] ?></h3>
                                            <small class="text-muted">Awaiting review</small>
                                        </div>
                                        <i class="fas fa-clock stat-icon text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-start border-success border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-normal text-muted">New Enrollments</h6>
                                            <h3 class="mb-1 text-success"><?= $daycareStats['new_enrollments'] ?></h3>
                                            <small class="text-muted">This month</small>
                                        </div>
                                        <i class="fas fa-user-plus stat-icon text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card border-start border-info border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-normal text-muted">Confirmation Rate</h6>
                                            <h3 class="mb-1 text-info">
                                                <?= ($daycareStats['total_students'] + $daycareStats['pending_applications']) > 0 ? 
                                                    round(($daycareStats['total_students'] / ($daycareStats['total_students'] + $daycareStats['pending_applications'])) * 100, 1) : 0 ?>%
                                            </h3>
                                            <small class="text-muted">Overall rate</small>
                                        </div>
                                        <i class="fas fa-chart-line stat-icon text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row mt-4">
                        <div class="col-xl-8">
                            <div class="chart-container">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Enrollment Trends - <?= $school_year ?></h5>
                                </div>
                                <div class="card-body pt-3">
                                    <canvas id="enrollmentTrendsChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="chart-container">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Gender Distribution</h5>
                                </div>
                                <div class="card-body pt-3">
                                    <canvas id="genderChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-xl-6">
                            <div class="chart-container">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Age Group Distribution</h5>
                                </div>
                                <div class="card-body pt-3">
                                    <canvas id="ageGroupChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="chart-container">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-language me-2"></i>Language Distribution</h5>
                                </div>
                                <div class="card-body pt-3">
                                    <canvas id="languageChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Demographics -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="chart-container">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Student Demographics - <?= $school_year ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="p-3 border rounded bg-light">
                                                <div class="metric-label text-primary">3-4 Years Old</div>
                                                <div class="metric-value text-primary">
                                                    <?= $daycareStats['age_groups']['3-4 years'] ?? 0 ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="p-3 border rounded bg-light">
                                                <div class="metric-label text-success">5-6 Years Old</div>
                                                <div class="metric-value text-success">
                                                    <?= $daycareStats['age_groups']['5-6 years'] ?? 0 ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="p-3 border rounded bg-light">
                                                <div class="metric-label text-info">Male</div>
                                                <div class="metric-value text-info">
                                                    <?= $daycareStats['gender_distribution']['male'] ?? 0 ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="p-3 border rounded bg-light">
                                                <div class="metric-label text-warning">Female</div>
                                                <div class="metric-value text-warning">
                                                    <?= $daycareStats['gender_distribution']['female'] ?? 0 ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Enrollments -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Enrollment Applications</h5>
                            <a href="daycare_enrollments.php" class="btn btn-sm btn-outline-primary mt-2 mt-md-0">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Child Name</th>
                                            <th>Age</th>
                                            <th>Gender</th>
                                            <th>Guardian</th>
                                            <th>Status</th>
                                            <th>Date Applied</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($daycareStats['recent_enrollments'])): ?>
                                            <?php foreach ($daycareStats['recent_enrollments'] as $enrollment): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($enrollment['child_first_name'] . ' ' . $enrollment['child_last_name']) ?></strong>
                                                    </td>
                                                    <td>
                                                        <?= !empty($enrollment['birthday']) ? 
                                                            date_diff(date_create($enrollment['birthday']), date_create('today'))->y . ' years' : 
                                                            'N/A' ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?= $enrollment['sex'] == 'male' ? 'primary' : 'warning' ?>">
                                                            <?= ucfirst($enrollment['sex'] ?? 'N/A') ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($enrollment['guardian_name'] ?? 'N/A') ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $enrollment['confirmed'] ? 'success' : 'warning' ?>">
                                                            <?= $enrollment['confirmed'] ? 'Confirmed' : 'Pending' ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('M j, Y', strtotime($enrollment['created_at'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">No enrollment applications found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
    <script src="assets/js/script.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enrollment Trends Chart
        const trendsCtx = document.getElementById('enrollmentTrendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($daycareStats['monthly_labels']) ?>,
                datasets: [{
                    label: 'New Enrollments',
                    data: <?= json_encode($daycareStats['monthly_trend']) ?>,
                    borderColor: '#1D3557',
                    backgroundColor: 'rgba(29, 53, 87, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
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

        // Gender Distribution Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [
                        <?= $daycareStats['gender_distribution']['male'] ?? 0 ?>,
                        <?= $daycareStats['gender_distribution']['female'] ?? 0 ?>
                    ],
                    backgroundColor: ['#36A2EB', '#FF6384'],
                    hoverBackgroundColor: ['#36A2EB', '#FF6384']
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

        // Age Group Chart
        const ageCtx = document.getElementById('ageGroupChart').getContext('2d');
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: ['3-4 years', '5-6 years', 'Other'],
                datasets: [{
                    label: 'Students',
                    data: [
                        <?= $daycareStats['age_groups']['3-4 years'] ?? 0 ?>,
                        <?= $daycareStats['age_groups']['5-6 years'] ?? 0 ?>,
                        <?= $daycareStats['age_groups']['Other'] ?? 0 ?>
                    ],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
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

        // Language Distribution Chart
        const languageCtx = document.getElementById('languageChart').getContext('2d');
        const languageData = <?= json_encode($daycareStats['language_distribution']) ?>;
        new Chart(languageCtx, {
            type: 'polarArea',
            data: {
                labels: Object.keys(languageData),
                datasets: [{
                    data: Object.values(languageData),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ]
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

        // Refresh button functionality
        document.getElementById('refreshBtn').addEventListener('click', function() {
            this.classList.add('fa-spin');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });

        // Auto-submit form when filters change
        document.getElementById('schoolYearFilter').addEventListener('change', function() {
            this.form.submit();
        });
        
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