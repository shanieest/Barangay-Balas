<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php';

$docRequestsQuery = "SELECT COUNT(*) AS total FROM document_requests WHERE status = 'Pending'";
$stmt = $conn->prepare($docRequestsQuery);
$stmt->execute();
$docRequestsResult = $stmt->get_result();
$docRequestsCount = $docRequestsResult->fetch_assoc()['total'] ?? 0;
$stmt->close();

$userId = $_SESSION['user_id'];
$latestRequestsQuery = "SELECT document_type_id, purpose, status, date_requested 
                        FROM document_requests 
                        WHERE resident_id = ?
                        ORDER BY date_requested DESC LIMIT 3";
$stmt = $conn->prepare($latestRequestsQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$latestRequests = $stmt->get_result();
$stmt->close();

$announcementsQuery = "SELECT COUNT(*) AS total FROM announcements 
                       WHERE date_posted >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$stmt = $conn->prepare($announcementsQuery);
$stmt->execute();
$announcementsResult = $stmt->get_result();
$announcementsCount = $announcementsResult->fetch_assoc()['total'] ?? 0;
$stmt->close();

$latestAnnouncementsQuery = "SELECT title, content, date_posted 
                             FROM announcements 
                             ORDER BY date_posted DESC LIMIT 3";
$stmt = $conn->prepare($latestAnnouncementsQuery);
$stmt->execute();
$latestAnnouncements = $stmt->get_result();
$stmt->close();

$alertsQuery = "SELECT COUNT(*) AS total FROM document_requests 
                WHERE status IN ('Pending','Disapproved')
                AND resident_id = ?";
$stmt = $conn->prepare($alertsQuery);
$stmt->bind_param("i", $userId); 
$stmt->execute();
$alertsResult = $stmt->get_result();
$alertsCount = $alertsResult->fetch_assoc()['total'] ?? 0;
$stmt->close();


$activityQuery = "SELECT activity, timestamp 
                  FROM activity_logs 
                    WHERE user_id = ?
                  ORDER BY timestamp DESC LIMIT 3";
$stmt = $conn->prepare($activityQuery);
$stmt->bind_param("i", $userId); 
$stmt->execute();
$activities = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Barangay Balas Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'?>

        <!-- Main Content -->
        <div class="main-content">
            <?php include '../../includes/navbar.php'?>

            <div class="content-area">
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
                                                <div><?= htmlspecialchars($row['document_type_id']) ?></div>
                                                <small class="<?= $row['status']=='Pending' ? 'text-warning' : 'text-success' ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </small>
                                            </div>
                                            <small class="text-muted">Requested: <?= date("M d, Y", strtotime($row['date_requested'])) ?></small>
                                        </a>
                                    <?php endwhile; ?>
                                </div>
                                <a href="services-history.php" class="btn btn-sm btn-primary mt-3">View All Requests</a>
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
                                        <a href="services-history.php" class="list-group-item list-group-item-action">
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
                                <a href="services.php" class="btn btn-primary"><i class="fas fa-file-alt me-2"></i> Request Document</a>
                                <a href="announcements.php" class="btn btn-warning"><i class="fas fa-calendar-alt me-2"></i> View Events</a>
                                <a href="services.php" class="btn btn-danger"><i class="fas fa-exclamation-triangle me-2"></i> Report Issue</a>
                                <a href="profile.php" class="btn btn-outline-primary"><i class="fas fa-user-edit me-2"></i> Update Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>