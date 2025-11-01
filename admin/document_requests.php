<?php
//document_requests.php
require 'includes/db.php';
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $records_per_page;

$doc_count_sql = "SELECT status, COUNT(*) as count 
                  FROM document_requests 
                  GROUP BY status";
$doc_count_result = $conn->query($doc_count_sql);
$doc_counts = ['Pending' => 0, 'Approved' => 0, 'Disapproved' => 0];
while ($row = $doc_count_result->fetch_assoc()) {
    $doc_counts[$row['status']] = $row['count'];
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';
$status_filter = match($tab) {
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
$doc_stmt->bind_param('sii', $status_filter, $records_per_page, $offset);
$doc_stmt->execute();
$doc_result = $doc_stmt->get_result();

$documents = [];
while ($row = $doc_result->fetch_assoc()) {
    $documents[] = $row;
}

$total_pages = ceil($doc_counts[$status_filter] / $records_per_page);

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
  <title>Document Requests | Barangay Balas Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/documentReq.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="sb-nav-fixed">
<?php include 'includes/navbar.php'; ?>
<div id="layoutSidenav">
  <?php include 'includes/sidebar.php'; ?>
  <div id="layoutSidenav_content">
    <main>
      <div>
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-icon">
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                    <h1 class="mb-2">Document Requests</h1>
                    <p class="mb-0 opacity-75">Manage and process barangay document requests</p>
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
                <li class="breadcrumb-item active"><i class="fas fa-file-alt me-1"></i>Document Requests</li>
            </ol>
        </nav>

        <!-- Reports Section -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i> Document Requests Reports
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
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 id="report-title" class="mb-0"></h5>
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
                                    <i class="fas fa-chart-bar me-2"></i> Document Type Breakdown
                                </div>
                                <div class="card-body">
                                    <div id="document-breakdown-table">
                                        <!-- Document type breakdown table will be populated by JavaScript -->
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
                                                    <th>Pending</th>
                                                    <th>Approved</th>
                                                    <th>Disapproved</th>
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

        <div class="card">
          <div class="card-header">
            <i class="fas fa-list-alt me-2"></i> Manage Document Requests
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="requestsTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'pending' ? 'active' : '' ?>" 
                   href="?tab=pending&page=1">
                  <i class="fas fa-clock me-1"></i> Pending <span class="badge bg-warning ms-1"><?= $doc_counts['Pending'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'approved' ? 'active' : '' ?>" 
                   href="?tab=approved&page=1">
                  <i class="fas fa-check-circle me-1"></i> Approved <span class="badge bg-success ms-1"><?= $doc_counts['Approved'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'disapproved' ? 'active' : '' ?>" 
                   href="?tab=disapproved&page=1">
                  <i class="fas fa-times-circle me-1"></i> Disapproved <span class="badge bg-danger ms-1"><?= $doc_counts['Disapproved'] ?></span>
                </a>
              </li>
            </ul>

            <!-- Pagination Info -->
            <?php if ($total_pages > 1): ?>
              <div class="pagination-info">
                Showing <?= (($page - 1) * $records_per_page) + 1 ?> to 
                <?= min($page * $records_per_page, $doc_counts[$status_filter]) ?> 
                of <?= $doc_counts[$status_filter] ?> entries
              </div>
            <?php endif; ?>

            <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
              <table class="table table-striped" style="width:100%; min-width: 800px;">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Resident</th>
                    <th>Document</th>
                    <th>Date Requested</th>
                    <?php if ($tab === 'pending'): ?>
                      <th>Purpose</th>
                    <?php else: ?>
                      <th>Date <?= ucfirst($tab) ?></th>
                      <?php if ($tab === 'disapproved'): ?>
                        <th>Reason</th>
                      <?php endif; ?>
                    <?php endif; ?>
                    <?php if (canModify()) { ?>
                      <th>Actions</th>
                    <?php } ?>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($documents) > 0): ?>
                    <?php foreach ($documents as $row): ?>
                      <tr>
                        <td><strong>DR-<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= htmlspecialchars($row['resident_name']) ?></td>
                        <td><span class="badge badge-default"><?= htmlspecialchars($row['document_type']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($row['date_requested'])) ?></td>
                        <?php if ($tab === 'pending'): ?>
                          <td><?= htmlspecialchars($row['purpose']) ?></td>
                          <?php if (canModify()) : ?>
                          <td class="action-buttons">
                            <button class="btn btn-sm btn-info me-1 mb-1" data-bs-toggle="modal" data-bs-target="#viewRequestModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-sm btn-success me-1 mb-1" data-bs-toggle="modal" data-bs-target="#approveRequestModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn-sm btn-danger mb-1" data-bs-toggle="modal" data-bs-target="#disapproveRequestModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-times"></i> Disapprove
                            </button>
                          </td>
                          <?php endif; ?>
                        <?php else: ?>
                          <td><?= date('M d, Y', strtotime($row['date_processed'])) ?></td>
                          <?php if ($tab === 'disapproved'): ?>
                            <td><?= htmlspecialchars($row['notes']) ?></td>
                          <?php endif; ?>
                          <?php if (canModify()) : ?>
                          <td class="action-buttons">
                            <button class="btn btn-sm btn-info me-1 mb-1" data-bs-toggle="modal" data-bs-target="#viewRequestModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-eye"></i> View
                            </button>
                            <?php if ($tab === 'approved' && !empty($row['document_file_path'])): ?>
                              <a href="/barangay-balas/services/download-document.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-primary mb-1">
                                  <i class="fas fa-download"></i> Download
                              </a>
                            <?php endif; ?>
                          </td>
                          <?php endif; ?>
                        <?php endif; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="<?= $tab === 'disapproved' ? '7' : '6' ?>" class="text-center text-muted py-4">No <?= $tab ?> document requests</td></tr>
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
                  'document_requests.php', 
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

<?php include 'modals/documentsModal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/js/script.js"></script>
<script src="assets/js/services.js"></script>
<script src="assets/js/documentReq.js"></script>

<script>
    window.USER_CAN_MODIFY = <?php echo canModify() ? 'true' : 'false'; ?>;
</script>
</body>
</html>