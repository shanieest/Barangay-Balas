<?php
//servicesAdmin.php
require 'includes/db.php';
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$doc_sql = "SELECT dr.id, dr.purpose, dr.status, dr.date_requested, dr.date_processed, dr.notes, 
               dr.document_file_path, dt.document_type, 
               CONCAT(r.first_name, ' ', r.last_name) AS resident_name
        FROM document_requests dr
        LEFT JOIN document_types dt ON dr.document_type_id = dt.id
        LEFT JOIN residents r ON dr.resident_id = r.id
        ORDER BY dr.date_requested DESC";

$doc_result = $conn->query($doc_sql);

$pending_docs = [];
$approved_docs = [];
$disapproved_docs = [];

while ($row = $doc_result->fetch_assoc()) {
    if ($row['status'] === 'Pending') {
        $pending_docs[] = $row;
    } elseif ($row['status'] === 'Approved') {
        $approved_docs[] = $row;
    } elseif ($row['status'] === 'Disapproved') {
        $disapproved_docs[] = $row;
    }
}

$service_sql = "SELECT sr.*, 
                   GROUP_CONCAT(st.service_name SEPARATOR ', ') as service_names,
                   CONCAT(sr.reservation_date_start, 
                          CASE WHEN sr.reservation_date_end != sr.reservation_date_start 
                               THEN CONCAT(' to ', sr.reservation_date_end) 
                               ELSE '' 
                          END) as reservation_date,
                   CONCAT(sr.duration_days, ' day', CASE WHEN sr.duration_days > 1 THEN 's' ELSE '' END) as duration
            FROM service_reservations sr
            LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
            LEFT JOIN service_types st ON sri.service_type_id = st.id
            GROUP BY sr.id
            ORDER BY sr.date_requested DESC";

$service_result = $conn->query($service_sql);

$pending_services = [];
$approved_services = [];
$completed_services = [];
$cancelled_services = [];

while ($row = $service_result->fetch_assoc()) {
    // Format service types with badges
    $service_badges = '';
    if ($row['service_names']) {
        $services = explode(', ', $row['service_names']);
        foreach ($services as $service) {
            $badge_class = match($service) {
                'Tent' => 'bg-primary',
                'Vehicle' => 'bg-info', 
                'Sound System' => 'bg-warning',
                'Tables and Chairs' => 'bg-success',
                default => 'bg-secondary'
            };
            $service_badges .= '<span class="badge ' . $badge_class . ' me-1">' . htmlspecialchars($service) . '</span>';
        }
    }
    $row['service_badges'] = $service_badges;
    
    // Categorize by status
    switch ($row['status']) {
        case 'Pending':
            $pending_services[] = $row;
            break;
        case 'Approved':
        case 'In Progress':
            $approved_services[] = $row;
            break;
        case 'Completed':
            $completed_services[] = $row;
            break;
        case 'Cancelled':
        case 'Rejected':
            $cancelled_services[] = $row;
            break;
    }
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
    .service-badge { font-size: 0.8rem; margin-left: 5px; }
    .table td { vertical-align: middle; }
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
              <li class="nav-item"><button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">Pending <span class="badge bg-warning ms-1"><?= count($pending_docs) ?></span></button></li>
              <li class="nav-item"><button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button">Approved <span class="badge bg-success ms-1"><?= count($approved_docs) ?></span></button></li>
              <li class="nav-item"><button class="nav-link" id="disapproved-tab" data-bs-toggle="tab" data-bs-target="#disapproved" type="button">Disapproved <span class="badge bg-danger ms-1"><?= count($disapproved_docs) ?></span></button></li>
            </ul>

            <div class="tab-content" id="requestsTabContent">
              <!-- Pending Documents -->
              <div class="tab-pane fade show active" id="pending">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Request ID</th>
                        <th>Resident</th>
                        <th>Document Type</th>
                        <th>Date Requested</th>
                        <th>Purpose</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($pending_docs) > 0): ?>
                        <?php foreach ($pending_docs as $row): ?>
                          <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['resident_name']) ?></td>
                            <td><?= htmlspecialchars($row['document_type']) ?></td>
                            <td><?= htmlspecialchars($row['date_requested']) ?></td>
                            <td><?= htmlspecialchars($row['purpose']) ?></td>
                            <td>
                              <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveRequestModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-check"></i> Approve
                              </button>
                              <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#disapproveRequestModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-times"></i> Disapprove
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="6" class="text-center">No pending requests</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Approved Documents -->
              <div class="tab-pane fade" id="approved">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Request ID</th>
                        <th>Resident</th>
                        <th>Document Type</th>
                        <th>Date Requested</th>
                        <th>Date Approved</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($approved_docs) > 0): ?>
                        <?php foreach ($approved_docs as $row): ?>
                          <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['resident_name']) ?></td>
                            <td><?= htmlspecialchars($row['document_type']) ?></td>
                            <td><?= htmlspecialchars($row['date_requested']) ?></td>
                            <td><?= htmlspecialchars($row['date_processed']) ?></td>
                            <td>
                              <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#viewRequestModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-eye"></i> View
                              </button>
                              <?php if (!empty($row['document_file_path'])): ?>
                                <a href="download-document.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>

                              <?php else: ?>
                                <span class="text-muted">No file</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="6" class="text-center">No approved requests</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Disapproved Documents -->
              <div class="tab-pane fade" id="disapproved">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Request ID</th>
                        <th>Resident</th>
                        <th>Document Type</th>
                        <th>Date Requested</th>
                        <th>Date Disapproved</th>
                        <th>Reason</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($disapproved_docs) > 0): ?>
                        <?php foreach ($disapproved_docs as $row): ?>
                          <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['resident_name']) ?></td>
                            <td><?= htmlspecialchars($row['document_type']) ?></td>
                            <td><?= htmlspecialchars($row['date_requested']) ?></td>
                            <td><?= htmlspecialchars($row['date_processed']) ?></td>
                            <td><?= htmlspecialchars($row['notes']) ?></td>
                            <td>
                              <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewRequestModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-eye"></i> View
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="7" class="text-center">No disapproved requests</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
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
                <button class="nav-link active" id="pending-services-tab" data-bs-toggle="tab" data-bs-target="#pending-services" type="button">
                  Pending Reservations <span class="badge bg-warning ms-1"><?= count($pending_services) ?></span>
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" id="approved-services-tab" data-bs-toggle="tab" data-bs-target="#approved-services" type="button">
                  Approved Reservations <span class="badge bg-success ms-1"><?= count($approved_services) ?></span>
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" id="completed-services-tab" data-bs-toggle="tab" data-bs-target="#completed-services" type="button">
                  Completed <span class="badge bg-info ms-1"><?= count($completed_services) ?></span>
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" id="cancelled-services-tab" data-bs-toggle="tab" data-bs-target="#cancelled-services" type="button">
                  Cancelled <span class="badge bg-danger ms-1"><?= count($cancelled_services) ?></span>
                </button>
              </li>
            </ul>

            <div class="tab-content" id="servicesTabContent">
              <!-- Pending Services -->
              <div class="tab-pane fade show active" id="pending-services">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Reservation ID</th>
                        <th>Resident</th>
                        <th>Service Type</th>
                        <th>Reservation Date</th>
                        <th>Duration</th>
                        <th>Purpose</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($pending_services) > 0): ?>
                        <?php foreach ($pending_services as $row): ?>
                          <tr>
                            <td>SR-<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($row['resident_name']) ?></td>
                            <td><?= $row['service_badges'] ?></td>
                            <td><?= htmlspecialchars($row['reservation_date']) ?></td>
                            <td><?= htmlspecialchars($row['duration']) ?></td>
                            <td><?= htmlspecialchars($row['purpose']) ?></td>
                            <td>
                              <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#approveServiceModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-check"></i> Approve
                              </button>
                              <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectServiceModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-times"></i> Reject
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="7" class="text-center">No pending service reservations</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Approved Services -->
              <div class="tab-pane fade" id="approved-services">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Reservation ID</th>
                        <th>Resident</th>
                        <th>Service Type</th>
                        <th>Reservation Date</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($approved_services) > 0): ?>
                        <?php foreach ($approved_services as $row): ?>
                          <tr>
                            <td>SR-<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($row['resident_name']) ?></td>
                            <td><?= $row['service_badges'] ?></td>
                            <td><?= htmlspecialchars($row['reservation_date']) ?></td>
                            <td><?= htmlspecialchars($row['duration']) ?></td>
                            <td>
                              <?php
                              $badge_class = match($row['status']) {
                                  'Approved' => 'bg-success',
                                  'In Progress' => 'bg-info',
                                  default => 'bg-secondary'
                              };
                              ?>
                              <span class="badge <?= $badge_class ?>"><?= $row['status'] ?></span>
                            </td>
                            <td>
                              <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#viewServiceModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-eye"></i> View
                              </button>
                              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateServiceModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-edit"></i> Update
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="7" class="text-center">No approved service reservations</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Completed Services -->
              <div class="tab-pane fade" id="completed-services">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Reservation ID</th>
                        <th>Resident</th>
                        <th>Service Type</th>
                        <th>Reservation Date</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($completed_services) > 0): ?>
                        <?php foreach ($completed_services as $row): ?>
                          <tr>
                            <td>SR-<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($row['resident_name']) ?></td>
                            <td><?= $row['service_badges'] ?></td>
                            <td><?= htmlspecialchars($row['reservation_date']) ?></td>
                            <td><?= htmlspecialchars($row['duration']) ?></td>
                            <td><span class="badge bg-info">Completed</span></td>
                            <td>
                              <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewServiceModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-eye"></i> View
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="7" class="text-center">No completed service reservations</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Cancelled Services -->
              <div class="tab-pane fade" id="cancelled-services">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Reservation ID</th>
                        <th>Resident</th>
                        <th>Service Type</th>
                        <th>Reservation Date</th>
                        <th>Duration</th>
                        <th>Reason</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($cancelled_services) > 0): ?>
                        <?php foreach ($cancelled_services as $row): ?>
                          <tr>
                            <td>SR-<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($row['resident_name']) ?></td>
                            <td><?= $row['service_badges'] ?></td>
                            <td><?= htmlspecialchars($row['reservation_date']) ?></td>
                            <td><?= htmlspecialchars($row['duration']) ?></td>
                            <td><?= htmlspecialchars($row['rejection_reason'] ?: $row['notes'] ?: 'N/A') ?></td>
                            <td>
                              <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewServiceModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-eye"></i> View
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="7" class="text-center">No cancelled service reservations</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
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

</body>
</html>