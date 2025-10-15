<?php
require 'includes/db.php';
require_once __DIR__ . '/includes/auth.php';
requireAuth();

// Pagination setup
$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $records_per_page;

// Filters
$search = $_GET['search'] ?? '';
$user = $_GET['user'] ?? '';
$activity_type = $_GET['activity-type'] ?? '';
$date_from = $_GET['date-from'] ?? '';
$date_to = $_GET['date-to'] ?? '';
$status = $_GET['status'] ?? '';

try {
    // Base query
    $sql = "SELECT al.*, 
                   CASE 
                       WHEN au.role = 'Admin' THEN CONCAT(au.first_name, ' ', au.last_name)
                       WHEN au.role = 'Official' THEN CONCAT(au.first_name, ' ', au.last_name)
                       WHEN r.id IS NOT NULL THEN CONCAT(r.first_name, ' ', r.last_name)
                       ELSE CONCAT('User ', al.user_id)
                   END AS user_display_name
            FROM activity_logs al
            LEFT JOIN admin_users au ON al.user_id = au.id
            LEFT JOIN residents r ON al.user_id = r.id
            WHERE 1=1";

    $params = [];
    $types = '';

    // Filters
    if (!empty($search)) {
        $sql .= " AND (al.activity LIKE ? OR al.user_agent LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= 'ss';
    }

    if (!empty($user)) {
        if ($user === 'admin') {
            $sql .= " AND au.role = 'Admin'";
        } elseif ($user === 'officials') {
            $sql .= " AND au.role = 'Official'";
        } elseif ($user === 'residents') {
            $sql .= " AND r.id IS NOT NULL";
        }
    }

    if (!empty($activity_type)) {
        $sql .= " AND (";
        switch ($activity_type) {
            case 'login':
                $sql .= "al.activity LIKE '%Logged in%'";
                break;
            case 'document':
                $sql .= "al.activity LIKE '%document%' OR al.activity LIKE '%Approved service%'";
                break;
            case 'profile':
                $sql .= "al.activity LIKE '%profile%'";
                break;
            case 'service':
                $sql .= "al.activity LIKE '%service reservation%'";
                break;
        }
        $sql .= ")";
    }

    if (!empty($date_from)) {
        $sql .= " AND DATE(al.timestamp) >= ?";
        $params[] = $date_from;
        $types .= 's';
    }

    if (!empty($date_to)) {
        $sql .= " AND DATE(al.timestamp) <= ?";
        $params[] = $date_to;
        $types .= 's';
    }

    $count_sql = preg_replace('/SELECT(.*?)FROM/', 'SELECT COUNT(*) as total FROM', $sql);
    $count_stmt = $conn->prepare($count_sql);
    if ($types && $params) $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_items = $count_result->fetch_assoc()['total'] ?? 0;
    $count_stmt->close();

    $total_pages = ceil($total_items / $records_per_page);

    $sql .= " ORDER BY al.timestamp DESC LIMIT ? OFFSET ?";
    $params[] = $records_per_page;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);
    if ($types && $params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $error = "Error loading activities: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Activities | Barangay Balas Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/logs.css">
</head>
<body class="sb-nav-fixed">
<?php include 'includes/navbar.php'; ?>
<div id="layoutSidenav">
  <?php include 'includes/sidebar.php'; ?>
  <div id="layoutSidenav_content">
    <main>
      <div class="container-fluid px-4">
        <h1 class="mt-4">System Activities</h1>
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Activity Logs</li>
        </ol>

        <div class="card mb-4">
          <div class="card-header">
            <i class="fas fa-clipboard-list me-1"></i> Activity Logs
          </div>
          <div class="card-body">
            
            <!-- Filter Form -->
            <form class="row g-3 mb-4" method="GET" action="">
              <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
              </div>
              <div class="col-md-2">
                <select name="user" class="form-select">
                  <option value="">All Users</option>
                  <option value="admin" <?= $user === 'admin' ? 'selected' : '' ?>>Admin</option>
                  <option value="officials" <?= $user === 'officials' ? 'selected' : '' ?>>Officials</option>
                  <option value="residents" <?= $user === 'residents' ? 'selected' : '' ?>>Residents</option>
                </select>
              </div>
              <div class="col-md-2">
                <select name="activity-type" class="form-select">
                  <option value="">All Types</option>
                  <option value="login" <?= $activity_type === 'login' ? 'selected' : '' ?>>Login</option>
                  <option value="document" <?= $activity_type === 'document' ? 'selected' : '' ?>>Document</option>
                  <option value="profile" <?= $activity_type === 'profile' ? 'selected' : '' ?>>Profile</option>
                  <option value="service" <?= $activity_type === 'service' ? 'selected' : '' ?>>Service</option>
                </select>
              </div>
              <div class="col-md-2">
                <input type="date" name="date-from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
              </div>
              <div class="col-md-2">
                <input type="date" name="date-to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
              </div>
              <div class="col-md-1 d-flex">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i></button>
              </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Activity</th>
                    <th>IP</th>
                    <th>User Agent</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($activities)): ?>
                    <tr><td colspan="6" class="text-center">No activities found.</td></tr>
                  <?php else: ?>
                    <?php foreach ($activities as $a): ?>
                      <tr>
                        <td><?= $a['id'] ?></td>
                        <td>
                          <?= date('M j, Y', strtotime($a['timestamp'])) ?><br>
                          <small class="text-muted"><?= date('g:i A', strtotime($a['timestamp'])) ?></small>
                        </td>
                        <td><?= htmlspecialchars($a['user_display_name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($a['activity']) ?></td>
                        <td><?= htmlspecialchars($a['ip_address'] ?? 'N/A') ?></td>
                        <td title="<?= htmlspecialchars($a['user_agent'] ?? '') ?>">
                          <?= strlen($a['user_agent']) > 50 
                              ? htmlspecialchars(substr($a['user_agent'], 0, 50)) . '...' 
                              : htmlspecialchars($a['user_agent'] ?? 'N/A') ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
              <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mt-3">
                  <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])) ?>">Previous</a>
                  </li>
                  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                      <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                  <?php endfor; ?>
                  <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => min($total_pages, $page + 1)])) ?>">Next</a>
                  </li>
                </ul>
              </nav>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </main>
    <?php include 'includes/footer.php'; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
