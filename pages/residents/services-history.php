<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php';

$userId = $_SESSION['user_id'] ?? null;
$documentRequests = [];
$serviceReservations = [];

if ($userId) {
    // Get document requests
    $docQuery = "SELECT dr.*, dt.document_type AS document_name 
                 FROM document_requests dr 
                 LEFT JOIN document_types dt ON dr.document_type_id = dt.id 
                 WHERE dr.resident_id = ? 
                 ORDER BY dr.date_requested DESC";
    $stmt = $conn->prepare($docQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $documentRequests = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Get service reservations
    $resQuery = "SELECT sr.id, sr.date_requested, sr.reservation_date_start, 
                    sr.duration_days, sr.purpose, sr.status, sr.resident_name,
                    GROUP_CONCAT(DISTINCT st.service_name ORDER BY st.service_name SEPARATOR ', ') AS services
                 FROM service_reservations sr
                 LEFT JOIN service_reservation_items sri ON sr.id = sri.reservation_id
                 LEFT JOIN service_types st ON sri.service_type_id = st.id
                 WHERE sr.resident_id = ? OR (sr.resident_id IS NULL AND sr.resident_name = (
                     SELECT CONCAT(r.first_name, ' ', r.last_name) 
                     FROM residents r 
                     WHERE r.id = ?
                 ))
                 GROUP BY sr.id, sr.date_requested, sr.reservation_date_start, 
                          sr.duration_days, sr.purpose, sr.status, sr.resident_name
                 ORDER BY sr.date_requested DESC";

    $stmt = $conn->prepare($resQuery);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $serviceReservations = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Request History - Barangay Balas Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/css/services-history.css">
</head>
<body>
<div class="wrapper">
    <?php include '../../includes/sidebar.php'?>
    <div class="main-content">
        <?php include '../../includes/navbar.php'?>
        <div class="content-area">
            <h2 class="mb-4">Barangay Services History</h2>
            
            <ul class="nav nav-tabs mb-4" id="requestTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="true">
                        <i class="fas fa-file-alt me-2"></i>Document Requests
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservations" type="button" role="tab" aria-controls="reservations" aria-selected="false">
                        <i class="fas fa-calendar-check me-2"></i>Service Reservations
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="requestTabsContent">
                <!-- Document Requests Tab -->
                <div class="tab-pane fade show active" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Document Requests</span>
                            <div>
                                <select class="form-select form-select-sm" id="documentStatusFilter" style="width: 150px;">
                                    <option value="all">All Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Processing">Processing</option>
                                    <option value="Disapproved">Disapproved</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Request Date</th>
                                            <th>Document Type</th>
                                            <th>Purpose</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($documentRequests)): ?>
                                            <?php foreach ($documentRequests as $request): ?>
                                                <?php
                                                $status = $request['status'] ?? 'Pending';
                                                $badgeClass = match($status) {
                                                    'Pending' => 'bg-warning text-dark',
                                                    'Approved', 'Processing' => 'bg-info',
                                                    'Released' => 'bg-success',
                                                    'Cancelled' => 'bg-secondary',
                                                    'Disapproved' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <tr data-status="<?= strtolower($status) ?>">
                                                    <td><?= date("M j, Y", strtotime($request['date_requested'])) ?></td>
                                                    <td><?= htmlspecialchars($request['document_name'] ?? 'Unknown Document') ?></td>
                                                    <td><?= htmlspecialchars($request['purpose'] ?? '') ?></td>
                                                    <td>
                                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($status === 'Released' || $status === 'Approved'): ?>
                                                            <button class="btn btn-sm btn-outline-info view-document" 
                                                                    data-id="<?= $request['id'] ?>" 
                                                                    data-name="<?= htmlspecialchars($request['document_name']) ?>"
                                                                    data-status="<?= htmlspecialchars($status) ?>">
                                                                <i class="fas fa-eye me-1"></i>View Status
                                                            </button>
                                                            <?php if (!empty($request['document_file_path'])): ?>
                                                                <a href="/barangay-balas/services/download-document.php?id=<?= $request['id'] ?>" 
                                                                   class="btn btn-sm btn-outline-success" 
                                                                   target="_blank">
                                                                    <i class="fas fa-download me-1"></i>Download
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php elseif ($status === 'Pending'): ?>
                                                            <button class="btn btn-sm btn-outline-danger cancel-request" data-id="<?= $request['id'] ?>">
                                                                <i class="fas fa-times me-1"></i>Cancel
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-outline-secondary view-document" 
                                                                    data-id="<?= $request['id'] ?>" 
                                                                    data-name="<?= htmlspecialchars($request['document_name']) ?>"
                                                                    data-status="<?= htmlspecialchars($status) ?>">
                                                                <i class="fas fa-eye me-1"></i>View Details
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    <i class="fas fa-folder-open fa-2x mb-3 d-block"></i>
                                                    No document requests found. <a href="services.php" class="text-primary">Make your first request</a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Service Reservations Tab -->
                <div class="tab-pane fade" id="reservations" role="tabpanel" aria-labelledby="reservations-tab">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Service Reservations</span>
                            <div>
                                <select class="form-select form-select-sm" id="serviceStatusFilter" style="width: 150px;">
                                    <option value="all">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="in-progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Request Date</th>
                                            <th>Services</th>
                                            <th>Reservation Date</th>
                                            <th>Duration</th>
                                            <th>Purpose</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($serviceReservations)): ?>
                                            <?php foreach ($serviceReservations as $reservation): ?>
                                                <?php
                                                $status = $reservation['status'] ?? 'Pending';
                                                $badgeClass = match($status) {
                                                    'Pending' => 'bg-warning text-dark',
                                                    'Approved' => 'bg-info',
                                                    'In Progress' => 'bg-primary',
                                                    'Completed' => 'bg-success',
                                                    'Cancelled', 'Rejected', 'Disapproved' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <tr data-status="<?= strtolower(str_replace(' ', '-', $status)) ?>">
                                                    <td><?= date("M j, Y", strtotime($reservation['date_requested'])) ?></td>
                                                    <td><?= htmlspecialchars($reservation['services'] ?? 'No services listed') ?></td>
                                                    <td><?= date("M j, Y", strtotime($reservation['reservation_date_start'])) ?></td>
                                                    <td><?= htmlspecialchars($reservation['duration_days']) ?> day(s)</td>
                                                    <td><?= htmlspecialchars($reservation['purpose'] ?? '') ?></td>
                                                    <td>
                                                        <span class="badge <?= $badgeClass ?>">
                                                            <?= htmlspecialchars($status) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary view-reservation" 
                                                                data-id="<?= $reservation['id'] ?>" 
                                                                data-name="<?= htmlspecialchars($reservation['services']) ?>"
                                                                data-status="<?= htmlspecialchars($status) ?>"
                                                                data-reservation-date="<?= date("Y-m-d", strtotime($reservation['reservation_date_start'])) ?>"
                                                                data-duration="<?= htmlspecialchars($reservation['duration_days']) ?>"
                                                                data-purpose="<?= htmlspecialchars($reservation['purpose'] ?? '') ?>">
                                                            <i class="fas fa-info-circle me-1"></i>View Details
                                                        </button>
                                                        <?php if ($status === 'Pending'): ?>
                                                            <button class="btn btn-sm btn-outline-danger cancel-reservation" data-id="<?= $reservation['id'] ?>">
                                                                <i class="fas fa-times me-1"></i>Cancel
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    <i class="fas fa-calendar-times fa-2x mb-3 d-block"></i>
                                                    No service reservations found. <a href="services.php" class="text-primary">Make your first reservation</a>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
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

<!-- Modals -->
<?php include '../modals/servicesHistoryModal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/servicesHistory.js"></script>
</body>
</html>