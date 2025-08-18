<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// ============================
// Dashboard Queries
// ============================

// Count new document requests (Pending)
$docRequestsQuery = "SELECT COUNT(*) AS total FROM document_requests WHERE status='Pending'";
$docRequestsResult = $conn->query($docRequestsQuery);
$docRequestsCount = $docRequestsResult->fetch_assoc()['total'] ?? 0;

// Fetch latest 5 document requests
$latestRequestsQuery = "SELECT document_type_id, purpose, status, date_requested 
                        FROM document_requests 
                        ORDER BY date_requested DESC LIMIT 5";
$latestRequests = $conn->query($latestRequestsQuery);

// Count new announcements (last 7 days)
$announcementsQuery = "SELECT COUNT(*) AS total FROM announcements WHERE date_posted >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$announcementsResult = $conn->query($announcementsQuery);
$announcementsCount = $announcementsResult->fetch_assoc()['total'] ?? 0;

// Fetch latest 5 announcements
$latestAnnouncementsQuery = "SELECT title, content, date_posted 
                             FROM announcements 
                             ORDER BY date_posted DESC LIMIT 5";
$latestAnnouncements = $conn->query($latestAnnouncementsQuery);

// Count alerts (Pending / Disapproved requests)
$alertsQuery = "SELECT COUNT(*) AS total FROM document_requests WHERE status IN ('Pending','Disapproved')";
$alertsResult = $conn->query($alertsQuery);
$alertsCount = $alertsResult->fetch_assoc()['total'] ?? 0;

// Fetch recent activity logs
$activityQuery = "SELECT activity, timestamp 
                  FROM activity_logs 
                  ORDER BY timestamp DESC LIMIT 5";
$activities = $conn->query($activityQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Balas Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue:  #0033cc;
            --secondary-blue: #3a7cb9;
            --accent-red: #e63946;
            --accent-yellow: #ffbe0b;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-blue), var(--secondary-blue));
            color: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            padding: 10px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }
        
        .sidebar-menu li:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu li.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid var(--accent-yellow);
        }
        
        .sidebar-menu li a {
            color: white;
            text-decoration: none;
            display: block;
        }
        
        .sidebar-menu li i {
            margin-right: 10px;
            color: var(--accent-yellow);
        }
        
        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        .top-navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 20px;
        }
        
        .content-area {
            padding: 20px;
            min-height: calc(100vh - 70px);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--accent-red);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-warning {
            background-color: var(--accent-yellow);
            border-color: var(--accent-yellow);
            color: #333;
        }
        
        .btn-danger {
            background-color: var(--accent-red);
            border-color: var(--accent-red);
        }
        
        .badge-primary {
            background-color: var(--primary-blue);
        }
        
        .badge-warning {
            background-color: var(--accent-yellow);
            color: #333;
        }
        
        .badge-danger {
            background-color: var(--accent-red);
        }
        
        .sidebar-collapsed .sidebar {
            width: 80px;
            overflow: hidden;
        }
        
        .sidebar-collapsed .sidebar .sidebar-text {
            display: none;
        }
        
        .sidebar-collapsed .main-content {
            margin-left: 80px;
        }
        
        .sidebar-collapsed .sidebar-header h3 {
            display: none;
        }
        
        .sidebar-collapsed .sidebar-menu li {
            text-align: center;
        }
        
        .sidebar-collapsed .sidebar-menu li i {
            margin-right: 0;
            font-size: 1.2rem;
        }
        
        .document-request-card {
            border-left: 4px solid var(--primary-blue);
        }
        
        .announcement-card {
            border-left: 4px solid var(--accent-yellow);
        }
        
        .alert-card {
            border-left: 4px solid var(--accent-red);
        }
        
        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-blue);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar .sidebar-text {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .sidebar-header h3 {
                display: none;
            }
            
            .sidebar-menu li {
                text-align: center;
            }
            
            .sidebar-menu li i {
                margin-right: 0;
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'?>

    <!-- Main Content -->
    <div class="main-content">
        <?php include 'includes/navbar.php'?>

        <div class="content-area">
            <section id="dashboard">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-calendar-alt me-2"></i><?= date("F j, Y"); ?>
                    </span>
                </div>

                <div class="row">
                    <!-- Document Requests -->
                    <div class="col-md-4">
                        <div class="card document-request-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Document Requests</span>
                                <span class="badge bg-primary"><?= $docRequestsCount ?> New</span>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php while($row = $latestRequests->fetch_assoc()): ?>
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between">
                                                <div><?= htmlspecialchars($row['document_type']) ?></div>
                                                <small class="<?= $row['status']=='Pending' ? 'text-warning' : 'text-success' ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </small>
                                            </div>
                                            <small class="text-muted">Requested: <?= date("M d, Y", strtotime($row['date_requested'])) ?></small>
                                        </a>
                                    <?php endwhile; ?>
                                </div>
                                <a href="documentRequests.php" class="btn btn-sm btn-primary mt-3">View All Requests</a>
                            </div>
                        </div>
                    </div>

                    <!-- Announcements -->
                    <div class="col-md-4">
                        <div class="card announcement-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Announcements</span>
                                <span class="badge bg-warning text-dark"><?= $announcementsCount ?> New</span>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php while($row = $latestAnnouncements->fetch_assoc()): ?>
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <h6 class="mb-1"><?= htmlspecialchars($row['title']) ?></h6>
                                            <small class="text-muted">Posted: <?= date("M d, Y", strtotime($row['date_posted'])) ?></small>
                                            <p class="mb-1 mt-1"><?= htmlspecialchars(substr($row['content'], 0, 80)) ?>...</p>
                                        </a>
                                    <?php endwhile; ?>
                                </div>
                                <a href="announcements.php" class="btn btn-sm btn-warning mt-3">View All Announcements</a>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <div class="col-md-4">
                        <div class="card alert-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Alerts</span>
                                <span class="badge bg-danger"><?= $alertsCount ?> Action Required</span>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php if ($alertsCount > 0): ?>
                                        <a href="documentRequests.php" class="list-group-item list-group-item-action">
                                            <div class="d-flex">
                                                <i class="fas fa-exclamation-circle text-danger me-3"></i>
                                                <div>
                                                    <h6 class="mb-1">Pending/Disapproved Requests</h6>
                                                    <small class="text-muted">You have <?= $alertsCount ?> request(s) to review.</small>
                                                </div>
                                            </div>
                                        </a>
                                    <?php else: ?>
                                        <div class="text-muted">No alerts at this time.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">Recent Activities</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr><th>Date</th><th>Activity</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = $activities->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= date("M d, Y h:i A", strtotime($row['timestamp'])) ?></td>
                                                    <td><?= htmlspecialchars($row['activity']) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Quick Actions -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">Quick Actions</div>
                            <div class="card-body d-grid gap-2">
                                <a href="requestDocument.php" class="btn btn-primary"><i class="fas fa-file-alt me-2"></i> Request Document</a>
                                <a href="events.php" class="btn btn-warning"><i class="fas fa-calendar-alt me-2"></i> View Events</a>
                                <a href="reportIssue.php" class="btn btn-danger"><i class="fas fa-exclamation-triangle me-2"></i> Report Issue</a>
                                <a href="profile.php" class="btn btn-outline-primary"><i class="fas fa-user-edit me-2"></i> Update Profile</a>
                            </div>
                        </div>
                    </div>
            
                </div>
            </section>
            <?php include 'services.php';  ?>
                    <?php include 'requestsHistory.php';  ?>
                    <?php include 'announcements.php'; ?>                 
                    <?php include 'profile.php';  ?>
                    <?php include 'census.php';  ?>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.wrapper').classList.toggle('sidebar-collapsed');
        });
        
        // Simple navigation between sections
        document.querySelectorAll('.sidebar-menu li a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Hide all sections
                document.querySelectorAll('section').forEach(section => {
                    section.classList.add('d-none');
                });
                
                // Show the selected section
                const target = this.getAttribute('href').substring(1);
                document.getElementById(target).classList.remove('d-none');
                
                // Update active menu item
                document.querySelectorAll('.sidebar-menu li').forEach(item => {
                    item.classList.remove('active');
                });
                this.parentElement.classList.add('active');
            });
        });
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>


