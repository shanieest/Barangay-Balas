<?php
require 'includes/db.php';
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$records_per_page = 5;
$doc_page = isset($_GET['doc_page']) ? max(1, intval($_GET['doc_page'])) : 1;
$service_page = isset($_GET['service_page']) ? max(1, intval($_GET['service_page'])) : 1;
$doc_offset = ($doc_page - 1) * $records_per_page;
$service_offset = ($service_page - 1) * $records_per_page;

$doc_count_sql = "SELECT status, COUNT(*) as count 
                  FROM document_requests 
                  GROUP BY status";
$doc_count_result = $conn->query($doc_count_sql);
$doc_counts = ['Pending' => 0, 'Approved' => 0, 'Disapproved' => 0];
while ($row = $doc_count_result->fetch_assoc()) {
    $doc_counts[$row['status']] = $row['count'];
}

$doc_tab = isset($_GET['doc_tab']) ? $_GET['doc_tab'] : 'pending';
$doc_status_filter = match($doc_tab) {
    'approved' => 'Approved',
    'disapproved' => 'Disapproved',
    default => 'Pending'
};

$doc_sql = "SELECT dr.id, dr.purpose, dr.status, dr.date_requested, dr.date_processed, dr.notes, 
               dr.document_file_path, dt.document_type, 
               CONCAT(r.first_name, ' ', r.last_name) AS resident_name
        FROM document_requests dr
        LEFT JOIN document_types dt ON dr.document_type_id = dt.id
        LEFT JOIN residents r ON dr.resident_id = r.id
        WHERE dr.status = ?
        ORDER BY dr.date_requested DESC
        LIMIT ? OFFSET ?";

$doc_stmt = $conn->prepare($doc_sql);
$doc_stmt->bind_param('sii', $doc_status_filter, $records_per_page, $doc_offset);
$doc_stmt->execute();
$doc_result = $doc_stmt->get_result();

$current_docs = [];
while ($row = $doc_result->fetch_assoc()) {
    $current_docs[] = $row;
}

$total_doc_pages = ceil($doc_counts[$doc_status_filter] / $records_per_page);

$service_count_sql = "SELECT 
                        CASE 
                            WHEN status = 'Pending' THEN 'Pending'
                            WHEN status IN ('Approved', 'In Progress') THEN 'Approved'
                            WHEN status = 'Completed' THEN 'Completed'
                            WHEN status IN ('Cancelled', 'Rejected') THEN 'Cancelled'
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

$service_tab = isset($_GET['service_tab']) ? $_GET['service_tab'] : 'pending';
$service_status_filter = match($service_tab) {
    'approved' => ['Approved', 'In Progress'],
    'completed' => ['Completed'],
    'cancelled' => ['Cancelled', 'Rejected'],
    default => ['Pending']
};

$service_placeholders = str_repeat('?,', count($service_status_filter) - 1) . '?';
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
                   CONCAT(sr.reservation_date_start, 
                          CASE WHEN sr.reservation_date_end != sr.reservation_date_start 
                               THEN CONCAT(' to ', sr.reservation_date_end) 
                               ELSE '' 
                          END) as reservation_date,
                   CONCAT(sr.duration_days, ' day', CASE WHEN sr.duration_days > 1 THEN 's' ELSE '' END) as duration
            FROM service_reservations sr
            LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
            LEFT JOIN service_types st ON sri.service_type_id = st.id
            LEFT JOIN residents r ON sr.resident_id = r.id
            WHERE sr.status IN ($service_placeholders)
            GROUP BY sr.id
            ORDER BY sr.date_requested DESC
            LIMIT ? OFFSET ?";

$service_stmt = $conn->prepare($service_sql);
$types = str_repeat('s', count($service_status_filter)) . 'ii';
$params = array_merge($service_status_filter, [$records_per_page, $service_offset]);
$service_stmt->bind_param($types, ...$params);
$service_stmt->execute();
$service_result = $service_stmt->get_result();

$current_services = [];
while ($row = $service_result->fetch_assoc()) {
    $service_badges = '';
    if ($row['service_names']) {
        $services = explode(', ', $row['service_names']);
        foreach ($services as $service) {
            // Check if service has quantity info
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
            
            $service_badges .= '<span class="badge ' . $badge_class . ' me-1">' . 
                              htmlspecialchars($service_name) . 
                              ($quantity_text ? ' <small>' . $quantity_text . '</small>' : '') . 
                              '</span>';
        }
    }
    $row['service_badges'] = $service_badges;
    $current_services[] = $row;
}

$total_service_pages = ceil($service_counts[$service_tab === 'pending' ? 'Pending' : ucfirst($service_tab)] / $records_per_page);

function generatePaginationLinks($current_page, $total_pages, $base_url, $additional_params = []) {
    $links = '';
    $params = http_build_query($additional_params);
    $separator = $params ? '&' : '';
    
    if ($total_pages <= 1) return $links;
    
    $links .= '<nav aria-label="Page navigation"><ul class="pagination pagination-sm justify-content-center">';
    
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
  <title>Services | Barangay Balas Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .service-badge { 
      font-size: 0.8rem; 
      margin-left: 5px; 
    }
    .service-badge small {
      font-size: 0.7rem;
      font-weight: bold;
    }
    .table td { 
      vertical-align: middle; 
    }
    .pagination-info {
      font-size: 0.9em;
      color: #6c757d;
      margin-bottom: 1rem;
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
        <h1 class="mt-4">Services</h1>
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Services</li>
        </ol>

        <!-- Document Requests Section -->
        <div class="card mb-4">
          <div class="card-header"><i class="fas fa-table me-1"></i> Manage Document Requests</div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-4" id="requestsTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link <?= $doc_tab === 'pending' ? 'active' : '' ?>" 
                   href="?doc_tab=pending&doc_page=1&service_tab=<?= $service_tab ?>&service_page=<?= $service_page ?>">
                  Pending <span class="badge bg-warning ms-1"><?= $doc_counts['Pending'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $doc_tab === 'approved' ? 'active' : '' ?>" 
                   href="?doc_tab=approved&doc_page=1&service_tab=<?= $service_tab ?>&service_page=<?= $service_page ?>">
                  Approved <span class="badge bg-success ms-1"><?= $doc_counts['Approved'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $doc_tab === 'disapproved' ? 'active' : '' ?>" 
                   href="?doc_tab=disapproved&doc_page=1&service_tab=<?= $service_tab ?>&service_page=<?= $service_page ?>">
                  Disapproved <span class="badge bg-danger ms-1"><?= $doc_counts['Disapproved'] ?></span>
                </a>
              </li>
            </ul>

            <!-- Pagination Info -->
            <?php if ($total_doc_pages > 1): ?>
              <div class="pagination-info">
                Showing <?= (($doc_page - 1) * $records_per_page) + 1 ?> to 
                <?= min($doc_page * $records_per_page, $doc_counts[$doc_status_filter]) ?> 
                of <?= $doc_counts[$doc_status_filter] ?> entries
              </div>
            <?php endif; ?>

            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Request ID</th>
                    <th>Resident</th>
                    <th>Document Type</th>
                    <th>Date Requested</th>
                    <?php if ($doc_tab === 'pending'): ?>
                      <th>Purpose</th>
                    <?php else: ?>
                      <th>Date <?= ucfirst($doc_tab) ?></th>
                      <?php if ($doc_tab === 'disapproved'): ?>
                        <th>Reason</th>
                      <?php endif; ?>
                    <?php endif; ?>
                     <?php if (canModify()) { ?>
                      <th>Actions</th>
                      <?php } ?>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($current_docs) > 0): ?>
                    <?php foreach ($current_docs as $row): ?>
                      <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['resident_name']) ?></td>
                        <td><?= htmlspecialchars($row['document_type']) ?></td>
                        <td><?= htmlspecialchars($row['date_requested']) ?></td>
                        <?php if ($doc_tab === 'pending'): ?>
                          <td><?= htmlspecialchars($row['purpose']) ?></td>
                          <td>
                            <?php if (canModify()) : ?>
                            <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveRequestModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#disapproveRequestModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-times"></i> Disapprove
                            </button>
                          </td>
                          <?php endif; ?>
                        <?php else: ?>
                          <td><?= htmlspecialchars($row['date_processed']) ?></td>
                          <?php if ($doc_tab === 'disapproved'): ?>
                            <td><?= htmlspecialchars($row['notes']) ?></td>
                          <?php endif; ?>
                          <td>
                            <?php if (canModify()) : ?>
                            <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#viewRequestModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-eye"></i> View
                            </button>
                            <?php if ($doc_tab === 'approved' && !empty($row['document_file_path'])): ?>
                              <a href="download-document.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-primary">
                                  <i class="fas fa-download"></i> Download
                              </a>
                            <?php endif; ?>
                            <?php endif; ?>
                          </td>
                            <?php endif; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="<?= $doc_tab === 'disapproved' ? '7' : '6' ?>" class="text-center">No <?= $doc_tab ?> requests</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Document Pagination -->
            <?php 
            echo generatePaginationLinks(
              $doc_page, 
              $total_doc_pages, 
              'servicesAdmin.php', 
              [
                'doc_tab' => $doc_tab,
                'service_tab' => $service_tab,
                'service_page' => $service_page,
                'doc_page' => ''
              ]
            );
            ?>
          </div>
        </div>

        <!-- Service Reservations Section -->
        <div class="card mb-4">
          <div class="card-header">
            <i class="fas fa-concierge-bell me-1"></i> Service Reservations
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-4" id="servicesTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link <?= $service_tab === 'pending' ? 'active' : '' ?>" 
                   href="?service_tab=pending&service_page=1&doc_tab=<?= $doc_tab ?>&doc_page=<?= $doc_page ?>">
                  Pending Reservations <span class="badge bg-warning ms-1"><?= $service_counts['Pending'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $service_tab === 'approved' ? 'active' : '' ?>" 
                   href="?service_tab=approved&service_page=1&doc_tab=<?= $doc_tab ?>&doc_page=<?= $doc_page ?>">
                  Approved Reservations <span class="badge bg-success ms-1"><?= $service_counts['Approved'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $service_tab === 'completed' ? 'active' : '' ?>" 
                   href="?service_tab=completed&service_page=1&doc_tab=<?= $doc_tab ?>&doc_page=<?= $doc_page ?>">
                  Completed <span class="badge bg-info ms-1"><?= $service_counts['Completed'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $service_tab === 'cancelled' ? 'active' : '' ?>" 
                   href="?service_tab=cancelled&service_page=1&doc_tab=<?= $doc_tab ?>&doc_page=<?= $doc_page ?>">
                  Cancelled <span class="badge bg-danger ms-1"><?= $service_counts['Cancelled'] ?></span>
                </a>
              </li>
            </ul>

            <!-- Service Pagination Info -->
            <?php if ($total_service_pages > 1): ?>
              <div class="pagination-info">
                Showing <?= (($service_page - 1) * $records_per_page) + 1 ?> to 
                <?= min($service_page * $records_per_page, $service_counts[$service_tab === 'pending' ? 'Pending' : ucfirst($service_tab)]) ?> 
                of <?= $service_counts[$service_tab === 'pending' ? 'Pending' : ucfirst($service_tab)] ?> entries
              </div>
            <?php endif; ?>

            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Reservation ID</th>
                    <th>Resident</th>
                    <th>Service Type & Quantity</th>
                    <th>Reservation Date</th>
                    <th>Duration</th>
                    <?php if ($service_tab === 'pending'): ?>
                      <th>Purpose</th>
                    <?php elseif ($service_tab === 'cancelled'): ?>
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
                  <?php if (count($current_services) > 0): ?>
                    <?php foreach ($current_services as $row): ?>
                      <tr>
                        <td>SR-<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
                        <td><?= htmlspecialchars($row['resident_name']) ?></td>
                        <td><?= $row['service_badges'] ?></td>
                        <td><?= htmlspecialchars($row['reservation_date']) ?></td>
                        <td><?= htmlspecialchars($row['duration']) ?></td>
                        <?php if ($service_tab === 'pending'): ?>
                          <td><?= htmlspecialchars($row['purpose']) ?></td>
                          <td>
                            <?php if (canModify()) : ?>
                            <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveServiceModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectServiceModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-times"></i> Reject
                            </button>
                            <?php endif; ?>
                          </td>
                        <?php elseif ($service_tab === 'cancelled'): ?>
                          <td><?= htmlspecialchars($row['rejection_reason'] ?: $row['notes'] ?: 'N/A') ?></td>
                          <td>

                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewServiceModal" data-id="<?= $row['id'] ?>">
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
                          <td>
                            <?php if (canModify()) : ?>
                            <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#viewServiceModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-eye"></i> View
                            </button>
                            <?php if ($service_tab === 'approved'): ?>
                              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateServiceModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-edit"></i> Update
                              </button>
                            <?php endif; ?>
                            <?php endif; ?>
                          </td>
                        <?php endif; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="7" class="text-center">No <?= $service_tab ?> service reservations</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Service Pagination -->
            <?php 
            echo generatePaginationLinks(
              $service_page, 
              $total_service_pages, 
              'servicesAdmin.php', 
              [
                'service_tab' => $service_tab,
                'doc_tab' => $doc_tab,
                'doc_page' => $doc_page,
                'service_page' => ''
              ]
            );
            ?>
          </div>
        </div>
      </div>
    </main>
    <?php include 'includes/footer.php'; ?>
  </div>
</div>

<?php include 'modals/documentsModal.php'; ?>
<?php include 'modals/reservationModal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/js/script.js"></script>
<script src="assets/js/reservation.js"></script> 

<script>
document.addEventListener('DOMContentLoaded', function() {
    const docPagination = document.querySelector('.card:first-of-type .pagination');
    if (docPagination) {
        docPagination.querySelectorAll('a').forEach(link => {
            let href = link.getAttribute('href');
            if (href && href.includes('page=')) {
                href = href.replace('page=', 'doc_page=');
                link.setAttribute('href', href);
            }
        });
    }
    
    const servicePagination = document.querySelector('.card:last-of-type .pagination');
    if (servicePagination) {
        servicePagination.querySelectorAll('a').forEach(link => {
            let href = link.getAttribute('href');
            if (href && href.includes('page=')) {
                href = href.replace('page=', 'service_page=');
                link.setAttribute('href', href);
            }
        });
    }
});

        window.USER_CAN_MODIFY = <?php echo canModify() ? 'true' : 'false'; ?>;

</script>

</body>
</html>