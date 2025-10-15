<?php
// reservations.php
require 'includes/db.php';
require_once __DIR__ . '/includes/auth.php';
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
                'Tent' => 'bg-primary',
                'Vehicle' => 'bg-info', 
                'Sound System' => 'bg-warning text-dark',
                'Tables and Chairs' => 'bg-success',
                default => 'bg-secondary'
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
    
    $links .= '<nav aria-label="Page navigation"><ul class="pagination pagination-sm justify-content-center mb-0">';
    
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
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="sb-nav-fixed">
<?php include 'includes/navbar.php'; ?>
<div id="layoutSidenav">
  <?php include 'includes/sidebar.php'; ?>
  <div id="layoutSidenav_content">
    <main>
      <div class="container-fluid px-4">
        <h1 class="mt-4">Service Reservations</h1>
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Service Reservations</li>
        </ol>

        <div class="card mb-4">
          <div class="card-header">
            <i class="fas fa-concierge-bell me-1"></i> Manage Service Reservations
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="servicesTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'pending' ? 'active' : '' ?>" 
                   href="?tab=pending&page=1">
                  Pending <span class="badge bg-warning ms-1"><?= $service_counts['Pending'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'approved' ? 'active' : '' ?>" 
                   href="?tab=approved&page=1">
                  Approved <span class="badge bg-success ms-1"><?= $service_counts['Approved'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'completed' ? 'active' : '' ?>" 
                   href="?tab=completed&page=1">
                  Completed <span class="badge bg-info ms-1"><?= $service_counts['Completed'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'cancelled' ? 'active' : '' ?>" 
                   href="?tab=cancelled&page=1">
                  Cancelled <span class="badge bg-danger ms-1"><?= $service_counts['Cancelled'] ?></span>
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

            <div class="table-responsive">
              <table class="table table-striped table-bordered">
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
                        <td>SR-<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
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
                    <tr><td colspan="7" class="text-center">No <?= $tab ?> service reservations</td></tr>
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
    <?php include 'includes/footer.php'; ?>
  </div>
</div>

<?php include 'modals/reservationModal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/js/script.js"></script>
<script src="assets/js/reservation.js"></script>

<script>
window.USER_CAN_MODIFY = <?php echo canModify() ? 'true' : 'false'; ?>;
</script>

</body>
</html>