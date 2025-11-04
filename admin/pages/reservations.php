<?php
// reservations.php
require '../includes/db.php';
require_once '../includes/auth.php';
requireAuth();

$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $records_per_page;

$service_count_sql = "SELECT 
                        CASE 
                            WHEN status = 'Pending' THEN 'Pending'
                            WHEN status IN ('Approved', 'In Progress') THEN 'Approved'
                            WHEN status = 'Completed' THEN 'Completed'
                            WHEN status IN ('Cancelled', 'Disapproved') THEN 'Cancelled'
                        END as status_group,
                        COUNT(*) as count
                      FROM service_reservations 
                      GROUP BY status_group";
$service_count_result = $conn->query($service_count_sql);
$service_counts = ['Pending' => 0, 'Approved' => 0, 'Completed' => 0, 'Cancelled' => 0];
while ($row = $service_count_result->fetch_assoc()) {
    if ($row['status_group']) {
        $service_counts[$row['status_group']] = $row['count'];
    }
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
$status_filter = match($tab) {
    'approved' => ['Approved', 'In Progress'],
    'completed' => ['Completed'],
    'cancelled' => ['Cancelled', 'Disapproved'],
    default => ['Pending']
};

$placeholders = str_repeat('?,', count($status_filter) - 1) . '?';
$service_sql = "SELECT sr.*, 
                   GROUP_CONCAT(
                       CONCAT(st.service_name, 
                              CASE WHEN sri.quantity > 1 
                                   THEN CONCAT(' (x', sri.quantity, ')') 
                                   ELSE '' 
                              END
                       ) SEPARATOR ', '
                   ) as service_names,
                   CONCAT(r.first_name, ' ', r.last_name) AS resident_name,
                   CONCAT(
                       DATE_FORMAT(sr.reservation_date_start, '%M %e, %Y'),
                       CASE WHEN sr.reservation_date_end != sr.reservation_date_start 
                            THEN CONCAT(' to ', DATE_FORMAT(sr.reservation_date_end, '%M %e, %Y'))
                            ELSE '' 
                       END
                   ) as reservation_date,
                   CONCAT(sr.duration_days, ' day', CASE WHEN sr.duration_days > 1 THEN 's' ELSE '' END) as duration
            FROM service_reservations sr
            LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
            LEFT JOIN service_types st ON sri.service_type_id = st.id
            LEFT JOIN residents r ON sr.resident_id = r.id
            WHERE sr.status IN ($placeholders)
            GROUP BY sr.id
            ORDER BY sr.date_requested DESC
            LIMIT ? OFFSET ?";

$service_stmt = $conn->prepare($service_sql);
$types = str_repeat('s', count($status_filter)) . 'ii';
$params = array_merge($status_filter, [$records_per_page, $offset]);
$service_stmt->bind_param($types, ...$params);
$service_stmt->execute();
$service_result = $service_stmt->get_result();

$services = [];
while ($row = $service_result->fetch_assoc()) {
    $service_badges = '';
    if ($row['service_names']) {
        $services_list = explode(', ', $row['service_names']);
        foreach ($services_list as $service) {
            if (strpos($service, '(x') !== false) {
                $service_parts = explode(' (x', $service);
                $service_name = $service_parts[0];
                $quantity = str_replace(')', '', $service_parts[1]);
                $quantity_text = 'x' . $quantity;
            } else {
                $service_name = $service;
                $quantity_text = '';
            }
            
            $badge_class = match($service_name) {
                'Tent' => 'badge-tent',
                'Vehicle' => 'badge-vehicle', 
                'Sound System' => 'badge-sound',
                'Tables and Chairs' => 'badge-tables',
                default => 'badge-default'
            };
            
            $service_badges .= '<span class="badge ' . $badge_class . ' me-1 mb-1">' . 
                              htmlspecialchars($service_name) . 
                              ($quantity_text ? ' <small>' . $quantity_text . '</small>' : '') . 
                              '</span>';
        }
    }
    $row['service_badges'] = $service_badges;
    $services[] = $row;
}

$total_pages = ceil($service_counts[$tab === 'pending' ? 'Pending' : ucfirst($tab)] / $records_per_page);

function generatePaginationLinks($current_page, $total_pages, $base_url, $additional_params = []) {
    $links = '';
    $params = http_build_query($additional_params);
    $separator = $params ? '&' : '';
    
    if ($total_pages <= 1) return $links;
    
    $links .= '<nav aria-label="Page navigation"><ul class="pagination justify-content-center mb-0">';
    
    $prev_disabled = $current_page <= 1 ? 'disabled' : '';
    $prev_page = max(1, $current_page - 1);
    $links .= '<li class="page-item ' . $prev_disabled . '">';
    $links .= '<a class="page-link" href="' . $base_url . '?' . $params . $separator . 'page=' . $prev_page . '">Previous</a>';
    $links .= '</li>';
    
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    if ($start_page > 1) {
        $links .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?' . $params . $separator . 'page=1">1</a></li>';
        if ($start_page > 2) {
            $links .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = $i == $current_page ? 'active' : '';
        $links .= '<li class="page-item ' . $active . '">';
        $links .= '<a class="page-link" href="' . $base_url . '?' . $params . $separator . 'page=' . $i . '">' . $i . '</a>';
        $links .= '</li>';
    }
    
    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            $links .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $links .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?' . $params . $separator . 'page=' . $total_pages . '">' . $total_pages . '</a></li>';
    }
    
    $next_disabled = $current_page >= $total_pages ? 'disabled' : '';
    $next_page = min($total_pages, $current_page + 1);
    $links .= '<li class="page-item ' . $next_disabled . '">';
    $links .= '<a class="page-link" href="' . $base_url . '?' . $params . $separator . 'page=' . $next_page . '">Next</a>';
    $links .= '</li>';
    
    $links .= '</ul></nav>';
    return $links;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Service Reservations | Barangay Balas Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/servicesAdmin.css">

</head>
<body class="sb-nav-fixed">
<?php include '../includes/navbar.php'; ?>
<div id="layoutSidenav">
  <?php include '../includes/sidebar.php'; ?>
  <div id="layoutSidenav_content">
    <main>
      <div>
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-icon">
                        <i class="fas fa-concierge-bell fa-2x"></i>
                    </div>
                    <h1 class="mb-2">Service Reservations</h1>
                    <p class="mb-0 opacity-75">Manage and track barangay service reservations</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-flex flex-wrap justify-content-md-end gap-2">
                        <button class="btn btn-outline-light" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active"><i class="fas fa-concierge-bell me-1"></i>Service Reservations</li>
            </ol>
        </nav>

        <!-- Reports Section -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Service Reservation Reports
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="report-type" class="form-label fw-semibold">Report Type</label>
                        <select class="form-select" id="report-type">
                            <option value="monthly">Monthly Report</option>
                            <option value="yearly">Yearly Report</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="month-selection">
                        <label for="report-month" class="form-label fw-semibold">Month</label>
                        <select class="form-select" id="report-month">
                            <?php
                            $months = [
                                '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                                '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
                                '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
                            ];
                            $current_month = date('m');
                            foreach ($months as $num => $name) {
                                $selected = $num == $current_month ? 'selected' : '';
                                echo "<option value='$num' $selected>$name</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="report-year" class="form-label fw-semibold">Year</label>
                        <select class="form-select" id="report-year">
                            <?php
                            $current_year = date('Y');
                            for ($year = $current_year; $year >= 2020; $year--) {
                                $selected = $year == $current_year ? 'selected' : '';
                                echo "<option value='$year' $selected>$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="generate-report">
                            <i class="fas fa-chart-bar me-1"></i> Generate Report
                        </button>
                    </div>
                </div>

                <!-- Report Results -->
                <div id="report-results" style="display: none;">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 id="report-title"></h5>
                                <div>
                                    <button type="button" class="btn btn-success btn-sm me-2" id="export-excel">
                                        <i class="fas fa-file-excel me-1"></i> Export Excel
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" id="export-csv">
                                        <i class="fas fa-file-csv me-1"></i> Export CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4" id="summary-cards">
                        <!-- Summary cards will be populated by JavaScript -->
                    </div>

                    <!-- Charts and Tables -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-chart-pie me-2"></i> Status Distribution
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" width="400" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-chart-bar me-2"></i> Service Type Breakdown
                                </div>
                                <div class="card-body">
                                    <div id="service-breakdown-table">
                                        <!-- Service breakdown table will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Breakdown -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-table me-2"></i> Detailed Breakdown
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="breakdown-table">
                                            <thead>
                                                <tr>
                                                    <th>Period</th>
                                                    <th>Total</th>
                                                    <th>Approved</th>
                                                    <th>Pending</th>
                                                    <th>In Progress</th>
                                                    <th>Completed</th>
                                                    <th>Cancelled</th>
                                                </tr>
                                            </thead>
                                            <tbody id="breakdown-table-body">
                                                <!-- Breakdown data will be populated by JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Availability Section -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar-alt me-2"></i> Reservation Calendar - Date Availability
            </div>
            <div class="card-body p-3">
                <div id="availability-calendar"></div>
            </div>
        </div>

        <div class="card">
          <div class="card-header">
            <i class="fas fa-list-alt me-2"></i> Manage Service Reservations
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="servicesTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'pending' ? 'active' : '' ?>" 
                   href="?tab=pending&page=1">
                  <i class="fas fa-clock me-1"></i> Pending <span class="badge bg-warning ms-1"><?= $service_counts['Pending'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'approved' ? 'active' : '' ?>" 
                   href="?tab=approved&page=1">
                  <i class="fas fa-check-circle me-1"></i> Approved <span class="badge bg-success ms-1"><?= $service_counts['Approved'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'completed' ? 'active' : '' ?>" 
                   href="?tab=completed&page=1">
                  <i class="fas fa-check-double me-1"></i> Completed <span class="badge bg-info ms-1"><?= $service_counts['Completed'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'cancelled' ? 'active' : '' ?>" 
                   href="?tab=cancelled&page=1">
                  <i class="fas fa-times-circle me-1"></i> Cancelled <span class="badge bg-danger ms-1"><?= $service_counts['Cancelled'] ?></span>
                </a>
              </li>
            </ul>

            <!-- Pagination Info -->
            <?php if ($total_pages > 1): ?>
              <div class="pagination-info">
                Showing <?= (($page - 1) * $records_per_page) + 1 ?> to 
                <?= min($page * $records_per_page, $service_counts[$tab === 'pending' ? 'Pending' : ucfirst($tab)]) ?> 
                of <?= $service_counts[$tab === 'pending' ? 'Pending' : ucfirst($tab)] ?> entries
              </div>
            <?php endif; ?>

            <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
              <table class="table table-striped" style="width:100%; min-width: 800px;">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Resident</th>
                    <th>Services</th>
                    <th>Reservation Date</th>
                    <th>Duration</th>
                    <?php if ($tab === 'pending'): ?>
                      <th>Purpose</th>
                    <?php elseif ($tab === 'cancelled'): ?>
                      <th>Reason</th>
                    <?php else: ?>
                      <th>Status</th>
                    <?php endif; ?>
                    <?php if (canModify()) { ?>
                    <th>Actions</th>
                    <?php } ?>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($services) > 0): ?>
                    <?php foreach ($services as $row): ?>
                      <tr>
                        <td><strong>SR-<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= htmlspecialchars($row['resident_name']) ?></td>
                        <td><?= $row['service_badges'] ?></td>
                        <td><?= htmlspecialchars($row['reservation_date']) ?></td>
                        <td><?= htmlspecialchars($row['duration']) ?></td>
                        <?php if ($tab === 'pending'): ?>
                          <td><?= htmlspecialchars($row['purpose']) ?></td>
                          <?php if (canModify()) : ?>
                          <td class="action-buttons">
                            <button class="btn btn-sm btn-info me-1 mb-1" data-bs-toggle="modal" data-bs-target="#viewServiceModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-sm btn-success me-1 mb-1" data-bs-toggle="modal" data-bs-target="#approveServiceModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn-sm btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#rejectServiceModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-times"></i> Disapprove
                            </button>
                          </td>
                          <?php endif; ?>
                        <?php elseif ($tab === 'cancelled'): ?>
                          <td><?= htmlspecialchars($row['rejection_reason'] ?: $row['notes'] ?: 'N/A') ?></td>
                          <td class="action-buttons">
                            <button class="btn btn-sm btn-info mb-1" data-bs-toggle="modal" data-bs-target="#viewServiceModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-eye"></i> View
                            </button>
                          </td>
                        <?php else: ?>
                          <td>
                            <?php
                            $badge_class = match($row['status']) {
                                'Approved' => 'bg-success',
                                'In Progress' => 'bg-info',
                                'Completed' => 'bg-info',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $badge_class ?>"><?= $row['status'] ?></span>
                          </td>
                          <?php if (canModify()) : ?>
                          <td class="action-buttons">
                            <button class="btn btn-sm btn-info me-1 mb-1" data-bs-toggle="modal" data-bs-target="#viewServiceModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-eye"></i> View
                            </button>
                            <?php if ($tab === 'approved'): ?>
                              <button class="btn btn-sm btn-warning mb-1" data-bs-toggle="modal" data-bs-target="#updateServiceModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-edit"></i> Update
                              </button>
                            <?php endif; ?>
                          </td>
                          <?php endif; ?>
                        <?php endif; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No <?= $tab ?> service reservations</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="row mt-3">
              <div class="col-12">
                <?php 
                echo generatePaginationLinks(
                  $page, 
                  $total_pages, 
                  'serviceReservations.php', 
                  ['tab' => $tab]
                );
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    <?php include '../includes/footer.php'; ?>
  </div>
</div>

<?php include '../modals/reservationModal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../assets/js/script.js"></script>
<script src="../assets/js/calendar.js"></script>
<script src="../assets/js/reservation.js"></script>

<script>
window.USER_CAN_MODIFY = <?php echo canModify() ? 'true' : 'false'; ?>;
</script>

</body>
</html>