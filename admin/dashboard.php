<?php 
require_once __DIR__ . '/includes/auth.php';
requireAuth();
require_once __DIR__ . '/includes/db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Barangay Balas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="sb-nav-fixed sb-sidenav-toggled">
    <?php include 'includes/navbar.php'; ?>
    
    <div id="layoutSidenav">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Dashboard</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>

                    <?php
                    function getCount($conn, $query) {
                        $result = $conn->query($query);
                        return ($result && $row = $result->fetch_assoc()) ? (int)$row['total'] : 0;
                    }

                    $residents_count  = getCount($conn, "SELECT COUNT(*) AS total FROM resident_accounts WHERE account_status = 'approved'");
                    $pending_requests = getCount($conn, "SELECT COUNT(*) AS total FROM document_requests WHERE status='Pending'");
                    $approved_today   = getCount($conn, "SELECT COUNT(*) AS total FROM document_requests WHERE status='Approved' AND DATE(date_processed) = CURDATE()");
                    $announcements    = getCount($conn, "SELECT COUNT(*) AS total FROM announcements WHERE DATE(date_posted) = CURDATE()");

                  
                    $requestsData = [];
                    $labels = [];
                    $reqQuery = $conn->query("
                        SELECT dt.document_type, COUNT(*) as total
                        FROM document_requests dr
                        JOIN document_types dt ON dr.document_type_id = dt.id
                        WHERE YEARWEEK(dr.date_requested, 1) = YEARWEEK(CURDATE(), 1)
                        GROUP BY dt.document_type
                    ");
                    if ($reqQuery) {
                        while ($row = $reqQuery->fetch_assoc()) {
                            $labels[] = $row['document_type'];
                            $requestsData[] = $row['total'];
                        }
                    }

                    $residentLabels = ['Approved', 'Pending', 'Disapproved'];
                    $residentData = [0, 0, 0];
                    $resQuery = $conn->query("
                        SELECT ra.account_status, COUNT(*) as total
                        FROM resident_accounts ra
                        JOIN residents r ON ra.resident_id = r.id
                        GROUP BY ra.account_status
                    ");
                    if ($resQuery) {
                        while ($row = $resQuery->fetch_assoc()) {
                            if ($row['account_status'] === 'Approved') $residentData[0] = $row['total'];
                            if ($row['account_status'] === 'Pending')  $residentData[1] = $row['total'];
                            if ($row['account_status'] === 'Disapproved') $residentData[2] = $row['total'];
                        }
                    }
                    ?>

                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-normal">Total Approved Residents</h6>
                                        <h3 class="mb-0"><?= $residents_count ?></h3>
                                    </div>
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="residents.php">View Details</a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-warning text-white mb-4">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-normal">Pending Requests</h6>
                                        <h3 class="mb-0"><?= $pending_requests ?></h3>
                                    </div>
                                    <i class="fas fa-file-alt fa-2x"></i>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="servicesAdmin.php">View Details</a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>

                        <!-- Approved Today -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-success text-white mb-4">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-normal">Approved Today</h6>
                                        <h3 class="mb-0"><?= $approved_today ?></h3>
                                    </div>
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="servicesAdmin.php">View Details</a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>

                        <!-- New Announcements -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-danger text-white mb-4">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-normal">New Announcements</h6>
                                        <h3 class="mb-0"><?= $announcements ?></h3>
                                    </div>
                                    <i class="fas fa-bullhorn fa-2x"></i>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between">
                                    <a class="small text-white stretched-link" href="announcements.php">View Details</a>
                                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header"><i class="fas fa-chart-bar me-1"></i>Recent Document Requests</div>
                                <div class="card-body"><canvas id="requestsChart"></canvas></div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card mb-4">
                                <div class="card-header"><i class="fas fa-chart-pie me-1"></i>Resident Registration Status</div>
                                <div class="card-body"><canvas id="residentsChart"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="card mb-4">
                        <div class="card-header"><i class="fas fa-table me-1"></i>Recent Activities</div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
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
                                        LIMIT 5
                                    ");
                                    if ($logs && $logs->num_rows > 0) {
                                        while ($row = $logs->fetch_assoc()) {
                                            echo "<tr>
                                                    <td>{$row['timestamp']}</td>
                                                    <td>{$row['activity']}</td>
                                                    <td>{$row['first_name']} {$row['last_name']}</td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='3'>No activities found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
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
    const requestsCtx = document.getElementById('requestsChart').getContext('2d');
    new Chart(requestsCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Requests This Week',
                data: <?= json_encode($requestsData) ?>,
                backgroundColor: [
                    '#E63946','#1D3557','#FFD166','#A8DADC','#457B9D'
                ],
                borderWidth: 1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    const residentsCtx = document.getElementById('residentsChart').getContext('2d');
    new Chart(residentsCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($residentLabels) ?>,
            datasets: [{
                data: <?= json_encode($residentData) ?>,
                backgroundColor: ['#29ac55ff','#FFD166','#E63946'],
                hoverBackgroundColor: ['#29ac55ff','#C1121F']
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right' } }
        }
    });
});
</script>
</body>
</html>
