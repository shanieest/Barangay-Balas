<?php
// barangay_id_records.php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAuth();

// Get filter status from URL parameter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query based on filter
$query = "
SELECT a.id, r.first_name, r.middle_name, r.last_name, a.status, a.application_date, 
       a.valid_until, a.digital_id_path, a.id_number, r.contact_number, a.notes,
       r.address, r.birthdate, r.civil_status, r.place_of_birth
FROM barangay_id_applications a
JOIN residents r ON a.resident_id = r.id
";

// Add WHERE clause based on filter
if ($status_filter !== 'all') {
    $query .= " WHERE a.status = ?";
}

$query .= " ORDER BY a.application_date DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if ($status_filter !== 'all') {
    $stmt->bind_param("s", $status_filter);
}
$stmt->execute();
$result = $stmt->get_result();

// Get counts for each status
$count_query = "
SELECT status, COUNT(*) as count 
FROM barangay_id_applications 
GROUP BY status
";
$count_result = $conn->query($count_query);
$status_counts = [];
while ($row = $count_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

$total_count = array_sum($status_counts);

// Get statistics for cards
$stats_query = "
SELECT 
    COUNT(*) as total_applications,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected_count
FROM barangay_id_applications
";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay ID Applications - Barangay Balas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
     <link rel="stylesheet" href="./assets/css/barangayId.css">
</head>
<body class="sb-nav-fixed">
    <?php include 'includes/navbar.php'; ?>
    <div id="layoutSidenav">
        <?php include 'includes/sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="main-container">
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="header-icon">
                                    <i class="fas fa-id-card fa-2x"></i>
                                </div>
                                <h1 class="mb-2">Barangay ID Applications</h1>
                                <p class="mb-0 opacity-75">Manage and process barangay ID applications</p>
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
                            <li class="breadcrumb-item active"><i class="fas fa-id-card me-1"></i>ID Applications</li>
                        </ol>
                    </nav>

                    <!-- Success/Error Messages -->
                    <?php if (isset($_GET['success']) && $_GET['success'] == 'approved'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            Application approved successfully! Barangay ID has been generated.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['success']) && $_GET['success'] == 'id_updated'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>ID Number updated successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['success']) && $_GET['success'] == 'rejected'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>Application rejected successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php
                            switch ($_GET['error']) {
                                case 'application_not_found':
                                    echo "Application not found.";
                                    break;
                                case 'pdf_conversion_failed':
                                    echo "Failed to generate PDF. Please try again.";
                                    break;
                                case 'update_failed':
                                    echo "Failed to update application status.";
                                    break;
                                case 'file_not_found':
                                    echo "Generated file not found.";
                                    break;
                                default:
                                    echo "An error occurred. Please try again.";
                            }
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Export Section -->
                    <div class="export-section">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h3 class="mb-2">
                                    <i class="fas fa-file-excel me-2 text-success"></i>Export Applications
                                </h3>
                                <p class="mb-0 text-muted">Download application records for viewing and analysis.</p>
                            </div>
                            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                                <button class="btn export-btn" data-bs-toggle="modal" data-bs-target="#exportModal">
                                    <i class="fas fa-download me-2"></i>Export to Excel
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
                            <div class="stats-card" style="border-left-color: #667eea;">
                                <i class="fas fa-file-alt text-primary"></i>
                                <div class="number text-primary"><?= number_format($stats['total_applications']) ?></div>
                                <div class="label">Total Applications</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
                            <div class="stats-card" style="border-left-color: #ffc107;">
                                <i class="fas fa-clock text-warning"></i>
                                <div class="number text-warning"><?= number_format($stats['pending_count']) ?></div>
                                <div class="label">Pending Review</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
                            <div class="stats-card" style="border-left-color: #28a745;">
                                <i class="fas fa-check-circle text-success"></i>
                                <div class="number text-success"><?= number_format($stats['approved_count']) ?></div>
                                <div class="label">Approved IDs</div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
                            <div class="stats-card" style="border-left-color: #dc3545;">
                                <i class="fas fa-times-circle text-danger"></i>
                                <div class="number text-danger"><?= number_format($stats['rejected_count']) ?></div>
                                <div class="label">Rejected Applications</div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="filter-card">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-filter me-1"></i>Filter by Status
                                </label>
                                <form method="GET">
                                    <div class="input-group">
                                        <select class="form-select" id="statusFilter" name="status" onchange="this.form.submit()">
                                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>
                                                All Applications (<?= $total_count ?>)
                                            </option>
                                            <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>
                                                Pending (<?= $status_counts['Pending'] ?? 0 ?>)
                                            </option>
                                            <option value="Approved" <?= $status_filter === 'Approved' ? 'selected' : '' ?>>
                                                Approved (<?= $status_counts['Approved'] ?? 0 ?>)
                                            </option>
                                            <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>
                                                Rejected (<?= $status_counts['Rejected'] ?? 0 ?>)
                                            </option>
                                        </select>
                                        <?php if ($status_filter !== 'all'): ?>
                                        <a href="?status=all" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>Clear
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Applications Table -->
                    <div class="card">
                        <div class="card-body">
                            <?php if ($result->num_rows > 0): ?>
                                <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                                <table class="table table-striped" style="width:100%; min-width: 800px;">
                                            <tr>
                                                <th>ID Number</th>
                                                <th>Resident Name</th>
                                                <th>Contact</th>
                                                <th>Status</th>
                                                <th>Application Date</th>
                                                <th>Valid Until</th>
                                                <th>Notes</th>
                                                <?php if (canModify()) { ?>
                                                <th>Actions</th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php while ($row = $result->fetch_assoc()) { ?>
                                            <tr>
                                                <td>
                                                    <?php if ($row['id_number']) { ?>
                                                        <span class="badge bg-dark"><?= htmlspecialchars($row['id_number']) ?></span>
                                                    <?php } else { ?>
                                                        <span class="text-muted">Not Set</span>
                                                    <?php } ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'][0] . '. ' : '') . $row['last_name']) ?></td>
                                                <td><?= htmlspecialchars($row['contact_number']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?=
                                                        $row['status'] === 'Approved' ? 'success' :
                                                        ($row['status'] === 'Pending' ? 'warning text-dark' : 'danger')
                                                    ?>">
                                                        <?= $row['status'] ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($row['application_date'])) ?></td>
                                                <td><?= $row['valid_until'] ? date('M d, Y', strtotime($row['valid_until'])) : '-' ?></td>
                                                <td>
                                                    <?php if ($row['notes']): ?>
                                                        <button class="btn btn-sm btn-outline-info" onclick="viewNotes('<?= htmlspecialchars($row['notes'], ENT_QUOTES) ?>')">
                                                            <i class="fas fa-sticky-note"></i> View
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($row['status'] === 'Pending') { ?>
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-info" onclick="viewApplicationDetails(<?= $row['id'] ?>)" title="Preview Application">
                                                                <i class="fas fa-eye"></i> Preview
                                                            </button>
                                                            <button class="btn btn-success" onclick="approveApplication(<?= $row['id'] ?>)" title="Approve & Generate ID">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                            <button class="btn btn-danger" onclick="showRejectModal(<?= $row['id'] ?>)" title="Reject Application">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>
                                                        </div>
                                                    <?php } elseif ($row['status'] === 'Approved' && $row['digital_id_path']) { ?>
                                                        <?php if (canModify()) { ?>
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-info view-id" data-path="../<?= $row['digital_id_path'] ?>" title="View Digital ID">
                                                                <i class="fas fa-eye"></i> View ID
                                                            </button>
                                                            <a href="download_barangayid.php?id=<?= $row['id'] ?>" class="btn btn-primary" title="Download ID">
                                                                <i class="fas fa-download"></i> Download
                                                            </a>
                                                        </div>
                                                        <?php } ?>
                                                    <?php } elseif ($row['status'] === 'Rejected') { ?>
                                                        <button class="btn btn-secondary btn-sm" onclick="viewRejectionDetails(<?= $row['id'] ?>)" title="View Rejection Details">
                                                            <i class="fas fa-info-circle"></i> Details
                                                        </button>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox fa-3x"></i>
                                    <h4>No Applications Found</h4>
                                    <p>
                                        <?php if ($status_filter !== 'all'): ?>
                                            No <?= strtolower($status_filter) ?> applications found.
                                        <?php else: ?>
                                            No barangay ID applications have been submitted yet.
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($status_filter !== 'all'): ?>
                                        <a href="?status=all" class="btn btn-primary">
                                            <i class="fas fa-eye me-1"></i>View All Applications
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <?php include 'modals/barangayidModal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/barangayId.js"></script>
    <script src="assets/js/script.js"></script>

</body>
</html>