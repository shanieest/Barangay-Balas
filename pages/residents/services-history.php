<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php';

$userId = $_SESSION['user_id'] ?? null;
$documentRequests = [];
$serviceReservations = [];

if ($userId) {
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
.sidebar { background: linear-gradient(180deg, var(--primary-blue), var(--secondary-blue)); color: white; height: 100vh; position: fixed; width: 250px; transition: all 0.3s; z-index: 1000; }
.sidebar-header { padding: 20px; background-color: rgba(0, 0, 0, 0.1); }
.sidebar-menu { padding: 0; list-style: none; }
.sidebar-menu li { padding: 10px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); transition: all 0.3s; }
.sidebar-menu li:hover { background-color: rgba(255, 255, 255, 0.1); }
.sidebar-menu li.active { background-color: rgba(255, 255, 255, 0.2); border-left: 4px solid var(--accent-yellow); }
.sidebar-menu li a { color: white; text-decoration: none; display: block; }
.sidebar-menu li i { margin-right: 10px; color: var(--accent-yellow); }
.main-content { margin-left: 250px; transition: all 0.3s; }
.top-navbar { background-color: white; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); padding: 15px 20px; }
.content-area { padding: 20px; min-height: calc(100vh - 70px); }
.card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); margin-bottom: 20px; border: none; }
.card-header { background-color: white; border-bottom: 1px solid rgba(0, 0, 0, 0.1); font-weight: 600; padding: 15px 20px; }
.notification-badge { position: absolute; top: -5px; right: -5px; background-color: var(--accent-red); color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; display: flex; align-items: center; justify-content: center; }
.btn-primary { background-color: var(--primary-blue); border-color: var(--primary-blue); }
.btn-warning { background-color: var(--accent-yellow); border-color: var(--accent-yellow); color: #333; }
.btn-danger { background-color: var(--accent-red); border-color: var(--accent-red); }
.nav-tabs .nav-link { color: var(--dark-gray); font-weight: 500; }
.nav-tabs .nav-link.active { color: var(--primary-blue); border-bottom: 3px solid var(--primary-blue); font-weight: 600; }
@media (max-width: 768px) {
    .sidebar { width: 80px; }
    .sidebar .sidebar-text { display: none; }
    .main-content { margin-left: 80px; }
    .sidebar-header h3 { display: none; }
    .sidebar-menu li { text-align: center; }
    .sidebar-menu li i { margin-right: 0; font-size: 1.2rem; }
}
</style>
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
                                    <option value="Released">Released</option>
                                    <option value="Disapproved">Disapproved</option>
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
                                                    'Cancelled' => 'bg-danger',
                                                    'Disapproved' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <tr data-status="<?= strtolower($status) ?>">
                                                    <td><?= date("Y-m-d", strtotime($request['date_requested'])) ?></td>
                                                    <td><?= htmlspecialchars($request['document_name'] ?? 'Unknown Document') ?></td>
                                                    <td><?= htmlspecialchars($request['purpose'] ?? '') ?></td>
                                                    <td>
                                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($status === 'Released' || $status === 'Approved' || $status === 'Processing'): ?>
                                                            <button class="btn btn-sm btn-outline-info view-document" 
                                                                    data-id="<?= $request['id'] ?>" 
                                                                    data-name="<?= htmlspecialchars($request['document_name']) ?>"
                                                                    data-status="<?= htmlspecialchars($status) ?>">
                                                                <i class="fas fa-eye me-1"></i>View Status
                                                            </button>
                                                        <?php elseif ($status === 'Pending'): ?>
                                                            <button class="btn btn-sm btn-outline-danger cancel-request" data-id="<?= $request['id'] ?>">
                                                                <i class="fas fa-times me-1"></i>Cancel
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-outline-secondary" disabled>No Actions</button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
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
                                                    'Cancelled', 'Rejected' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <tr data-status="<?= strtolower(str_replace(' ', '-', $status)) ?>">
                                                    <td><?= date("Y-m-d", strtotime($reservation['date_requested'])) ?></td>
                                                    <td><?= htmlspecialchars($reservation['services'] ?? 'No services listed') ?></td>
                                                    <td><?= date("Y-m-d", strtotime($reservation['reservation_date_start'])) ?></td>
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
                                                                data-duration="<?= htmlspecialchars($reservation['duration_days']) ?>">
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

    <!-- Document Modal -->
    <div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDocumentModalLabel">Document Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="documentDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reservation Modal -->
    <div class="modal fade" id="viewReservationModal" tabindex="-1" aria-labelledby="viewReservationLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReservationLabel">Reservation Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="reservationDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-labelledby="cancelConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelConfirmModalLabel">Confirm Cancellation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this request? This action cannot be undone.</p>
                    <input type="hidden" id="cancelRequestId">
                    <input type="hidden" id="cancelRequestType" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep It</button>
                    <button type="button" class="btn btn-danger" id="confirmCancel">Yes, Cancel Request</button>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Helper: get progress info
function getProgressBar(status) {
    switch (status) {
        case 'Pending': return { width: '25%', class: 'bg-warning text-dark', text: 'Pending', message: 'Your request is pending approval.' };
        case 'Approved': return { width: '50%', class: 'bg-info', text: 'Approved', message: 'Your request has been approved.' };
        case 'Processing': return { width: '75%', class: 'bg-primary', text: 'Processing', message: 'Your request is being processed.' };
        case 'Released':
        case 'Completed': return { width: '100%', class: 'bg-success', text: 'Completed', message: 'Your request has been released/completed.' };
        case 'Cancelled':
        case 'Rejected': return { width: '100%', class: 'bg-danger', text: status, message: 'This request was cancelled or rejected.' };
        default: return { width: '0%', class: 'bg-secondary', text: status, message: 'Status unknown.' };
    }
}

// View document modal
document.querySelectorAll('.view-document').forEach(button => {
    button.addEventListener('click', function() {
        const name = this.getAttribute('data-name');
        const status = this.getAttribute('data-status');
        const progress = getProgressBar(status);

        document.getElementById('documentDetails').innerHTML = `
            <div class="text-center">
                <i class="fas fa-file-alt fa-3x text-info mb-3"></i>
                <h5>${name}</h5>
                <p>${progress.message}</p>
                <div class="mt-3">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated ${progress.class}" style="width: ${progress.width}">${progress.text}</div>
                    </div>
                </div>
            </div>
        `;
        new bootstrap.Modal(document.getElementById('viewDocumentModal')).show();
    });
});

// View reservation modal
document.querySelectorAll('.view-reservation').forEach(button => {
    button.addEventListener('click', function() {
        const services = this.getAttribute('data-name').split(',').map(s => s.trim());
        const status = this.getAttribute('data-status');
        const date = this.getAttribute('data-reservation-date');
        const duration = this.getAttribute('data-duration');
        const progress = getProgressBar(status);

        document.getElementById('reservationDetails').innerHTML = `
            <div class="text-center">
                <i class="fas fa-calendar-check fa-3x text-info mb-3"></i>
                <h5>Reservation Details</h5>
                <p><strong>Services:</strong></p>
                <ul class="list-unstyled">
                    ${services.map(s => `<li>• ${s}</li>`).join('')}
                </ul>
                <p><strong>Reservation Date:</strong> ${date}</p>
                <p><strong>Duration:</strong> ${duration} day(s)</p>
                <p>${progress.message}</p>
                <div class="mt-3">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated ${progress.class}" style="width: ${progress.width}">${progress.text}</div>
                    </div>
                </div>
            </div>
        `;
        new bootstrap.Modal(document.getElementById('viewReservationModal')).show();
    });
});

// Cancel request/reservation
document.querySelectorAll('.cancel-request, .cancel-reservation').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const type = this.classList.contains('cancel-request') ? 'document' : 'service';
        document.getElementById('cancelRequestId').value = id;
        document.getElementById('cancelRequestType').value = type;
        new bootstrap.Modal(document.getElementById('cancelConfirmModal')).show();
    });
});

document.getElementById('confirmCancel').addEventListener('click', function() {
    const formData = new FormData();
    formData.append('request_id', document.getElementById('cancelRequestId').value);
    formData.append('type', document.getElementById('cancelRequestType').value);

    fetch('services-history-backend.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { alert('Request cancelled successfully!'); location.reload(); }
            else alert('Error: ' + data.message);
        })
        .catch(err => { console.error(err); alert('Error cancelling request.'); });
});

document.getElementById('documentStatusFilter').addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const rows = this.closest('.card').querySelectorAll('tbody tr');
    rows.forEach(r => r.style.display = (status === 'all' || r.getAttribute('data-status') === status) ? '' : 'none');
});
document.getElementById('serviceStatusFilter').addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const rows = this.closest('.card').querySelectorAll('tbody tr');
    rows.forEach(r => r.style.display = (status === 'all' || r.getAttribute('data-status') === status) ? '' : 'none');
});
</script>
</body>
</html>
