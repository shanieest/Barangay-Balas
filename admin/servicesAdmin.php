<?php
require 'includes/db.php';

require_once __DIR__ . '/includes/auth.php';
requireAuth();

$sql = "SELECT dr.id, dr.purpose, dr.status, 
               dr.date_requested, dr.date_processed, dr.notes, 
               dt.document_type, 
               CONCAT(r.first_name, ' ', r.last_name) AS resident_name
        FROM document_requests dr
        LEFT JOIN document_types dt ON dr.document_type_id = dt.id
        LEFT JOIN residents r ON dr.resident_id = r.id
        ORDER BY dr.date_requested DESC";

$result = $conn->query($sql);

$pending = [];
$approved = [];
$disapproved = [];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'Pending') {
        $pending[] = $row;
    } elseif ($row['status'] === 'Approved') {
        $approved[] = $row;
    } elseif ($row['status'] === 'Disapproved') {
        $disapproved[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Document Requests | Barangay Balas Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="sb-nav-fixed">
<?php include 'includes/navbar.php'; ?>
<div id="layoutSidenav">
  <?php include 'includes/sidebar.php'; ?>
  <div id="layoutSidenav_content">
    <main>
      <div class="container-fluid px-4">
        <h1 class="mt-4">Document Requests</h1>
        <ol class="breadcrumb mb-4">
          <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item active">Document Requests</li>
        </ol>

        <div class="card mb-4">
          <div class="card-header">
            <i class="fas fa-table me-1"></i> Manage Document Requests
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-4" id="requestsTab" role="tablist">
              <li class="nav-item"><button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">Pending <span class="badge bg-warning ms-1"><?= count($pending) ?></span></button></li>
              <li class="nav-item"><button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button">Approved <span class="badge bg-success ms-1"><?= count($approved) ?></span></button></li>
              <li class="nav-item"><button class="nav-link" id="disapproved-tab" data-bs-toggle="tab" data-bs-target="#disapproved" type="button">Disapproved <span class="badge bg-danger ms-1"><?= count($disapproved) ?></span></button></li>
            </ul>

            <div class="tab-content" id="requestsTabContent">
              <!-- Pending -->
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
                      <?php if (count($pending) > 0): ?>
                        <?php foreach ($pending as $row): ?>
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

              <!-- Approved -->
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
                      <?php if (count($approved) > 0): ?>
                        <?php foreach ($approved as $row): ?>
                          <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['resident_name']) ?></td>
                            <td><?= htmlspecialchars($row['document_type']) ?></td>
                            <td><?= htmlspecialchars($row['date_requested']) ?></td>
                            <td><?= htmlspecialchars($row['date_processed']) ?></td>
                            <td>
                              <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewRequestModal" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-eye"></i> View
                              </button>
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

              <!-- Disapproved -->
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
                      <?php if (count($disapproved) > 0): ?>
                        <?php foreach ($disapproved as $row): ?>
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
      </div>
    </main>
    <?php include 'includes/footer.php'; ?>
  </div>
</div>

<?php include 'modals/documentsModal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
