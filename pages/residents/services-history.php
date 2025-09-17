<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php';

// Get user's request history
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
        
        .nav-tabs .nav-link {
            color: var(--dark-gray);
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-blue);
            border-bottom: 3px solid var(--primary-blue);
            font-weight: 600;
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
        <?php include '../../includes/sidebar.php'?>

        <!-- Main Content -->
        <div class="main-content">
            <?php include '../../includes/navbar.php'?>

            <div class="content-area">
                <h2 class="mb-4">Request History</h2>
                
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
                                                <th>Action Date</th>
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
                                                        <td><?= $request['date_processed'] ? date("Y-m-d", strtotime($request['date_processed'])) : 'N/A' ?></td>
                                                        <td>
                                                            <?php if ($status === 'Released'): ?>
                                                                <button class="btn btn-sm btn-outline-success view-document" data-id="<?= $request['id'] ?>">
                                                                    <i class="fas fa-eye me-1"></i>View Status
                                                                </button>
                                                            <?php elseif ($status === 'Approved' || $status === 'Processing'): ?>
                                                                <button class="btn btn-sm btn-outline-info view-document" data-id="<?= $request['id'] ?>">
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
                                                        <button class="btn btn-sm btn-outline-primary view-reservation" data-id="<?= $reservation['id'] ?>">
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

    <!-- View Document Modal -->
    <div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDocumentModalLabel">Document Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="documentDetails">
                        <!-- Details will be loaded via AJAX -->
                    </div>
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
        // Check if sidebarToggle exists before adding event listener
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.querySelector('.wrapper').classList.toggle('sidebar-collapsed');
            });
        }
        
        // Filter functionality for document requests
        document.getElementById('documentStatusFilter').addEventListener('change', function() {
            const status = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Filter functionality for service reservations
        document.getElementById('serviceStatusFilter').addEventListener('change', function() {
            const status = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // View document details
        document.querySelectorAll('.view-document').forEach(button => {
            button.addEventListener('click', function() {
                const requestId = this.getAttribute('data-id');
                
                document.getElementById('documentDetails').innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-file-alt fa-3x text-info mb-3"></i>
                        <h5>Document Request #${requestId}</h5>
                        <p>Your document is currently being processed or has been released.</p>
                        <p>Please visit the Barangay Hall to claim your document.</p>
                        <div class="mt-3">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%">Released</div>
                            </div>
                        </div>
                    </div>
                `;
                
                const modal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
                modal.show();
            });
        });
        
        // View reservation details
        document.querySelectorAll('.view-reservation').forEach(button => {
            button.addEventListener('click', function() {
                const reservationId = this.getAttribute('data-id');
                alert(`Reservation ID: ${reservationId} - Details functionality to be implemented`);
            });
        });

        // Cancel document request
        document.querySelectorAll('.cancel-request').forEach(button => {
            button.addEventListener('click', function() {
                const requestId = this.getAttribute('data-id');
                document.getElementById('cancelRequestId').value = requestId;
                document.getElementById('cancelRequestType').value = 'document';
                
                const modal = new bootstrap.Modal(document.getElementById('cancelConfirmModal'));
                modal.show();
            });
        });

        // Cancel service reservation
        document.querySelectorAll('.cancel-reservation').forEach(button => {
            button.addEventListener('click', function() {
                const reservationId = this.getAttribute('data-id');
                document.getElementById('cancelRequestId').value = reservationId;
                document.getElementById('cancelRequestType').value = 'service';
                
                const modal = new bootstrap.Modal(document.getElementById('cancelConfirmModal'));
                modal.show();
            });
        });

        // Handle the actual cancellation
        document.getElementById('confirmCancel').addEventListener('click', function() {
            const requestId = document.getElementById('cancelRequestId').value;
            const requestType = document.getElementById('cancelRequestType').value;
            
            // Create a form to submit the cancellation
            const formData = new FormData();
            formData.append('request_id', requestId);
            formData.append('type', requestType);
            
            fetch('services-history-backend.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message and reload the page
                    alert('Request cancelled successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while cancelling the request.');
            });
        });
    </script>
</body>
</html>