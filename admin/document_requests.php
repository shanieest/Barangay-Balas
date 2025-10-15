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
  <title>Document Requests | Barangay Balas Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

         <!-- Reports Section -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-chart-bar me-1"></i> Document Requests Reports
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="report-type" class="form-label">Report Type</label>
                        <select class="form-select" id="report-type">
                            <option value="monthly">Monthly Report</option>
                            <option value="yearly">Yearly Report</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="month-selection">
                        <label for="report-month" class="form-label">Month</label>
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
                        <label for="report-year" class="form-label">Year</label>
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
                                    <i class="fas fa-chart-pie me-1"></i> Status Distribution
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" width="400" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-chart-bar me-1"></i> Document Type Breakdown
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
                                    <i class="fas fa-table me-1"></i> Detailed Breakdown
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

        <div class="card mb-4">
          <div class="card-header">
            <i class="fas fa-file-alt me-1"></i> Manage Document Requests
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="requestsTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'pending' ? 'active' : '' ?>" 
                   href="?tab=pending&page=1">
                  Pending <span class="badge bg-warning ms-1"><?= $doc_counts['Pending'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'approved' ? 'active' : '' ?>" 
                   href="?tab=approved&page=1">
                  Approved <span class="badge bg-success ms-1"><?= $doc_counts['Approved'] ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= $tab === 'disapproved' ? 'active' : '' ?>" 
                   href="?tab=disapproved&page=1">
                  Disapproved <span class="badge bg-danger ms-1"><?= $doc_counts['Disapproved'] ?></span>
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

            <div class="table-responsive">
              <table class="table table-striped table-bordered">
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
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['resident_name']) ?></td>
                        <td><?= htmlspecialchars($row['document_type']) ?></td>
                        <td><?= htmlspecialchars($row['date_requested']) ?></td>
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
                          <td><?= htmlspecialchars($row['date_processed']) ?></td>
                          <?php if ($tab === 'disapproved'): ?>
                            <td><?= htmlspecialchars($row['notes']) ?></td>
                          <?php endif; ?>
                          <?php if (canModify()) : ?>
                          <td class="action-buttons">
                            <button class="btn btn-sm btn-info me-1 mb-1" data-bs-toggle="modal" data-bs-target="#viewRequestModal" data-id="<?= $row['id'] ?>">
                              <i class="fas fa-eye"></i> View
                            </button>
                            <?php if ($tab === 'approved' && !empty($row['document_file_path'])): ?>
                              <a href="download-document.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-primary mb-1">
                                  <i class="fas fa-download"></i> Download
                              </a>
                            <?php endif; ?>
                          </td>
                          <?php endif; ?>
                        <?php endif; ?>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="<?= $tab === 'disapproved' ? '7' : '6' ?>" class="text-center">No <?= $tab ?> requests</td></tr>
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

<script>
window.USER_CAN_MODIFY = <?php echo canModify() ? 'true' : 'false'; ?>;

// Report generation functionality
document.addEventListener('DOMContentLoaded', function() {
    const reportType = document.getElementById('report-type');
    const monthSelection = document.getElementById('month-selection');
    const generateReportBtn = document.getElementById('generate-report');
    const exportExcelBtn = document.getElementById('export-excel');
    const exportCsvBtn = document.getElementById('export-csv');
    const reportResults = document.getElementById('report-results');
    
    let currentReportData = null;

    // Toggle month selection based on report type
    reportType.addEventListener('change', function() {
        monthSelection.style.display = this.value === 'monthly' ? 'block' : 'none';
    });

    // Generate report
    generateReportBtn.addEventListener('click', function() {
        generateReport();
    });

    // Export buttons
    exportExcelBtn.addEventListener('click', function() {
        exportReport('excel');
    });

    exportCsvBtn.addEventListener('click', function() {
        exportReport('csv');
    });

    function generateReport() {
        const reportTypeVal = document.getElementById('report-type').value;
        const month = document.getElementById('report-month').value;
        const year = document.getElementById('report-year').value;
        
        if (!year) {
            alert('Please select a year');
            return;
        }

        if (reportTypeVal === 'monthly' && !month) {
            alert('Please select a month');
            return;
        }

        const button = generateReportBtn;
        const originalText = button.innerHTML;
        
        // Add loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
        button.disabled = true;

        const formData = new FormData();
        formData.append('action', 'get_report_data');
        formData.append('report_type', reportTypeVal);
        formData.append('month', month);
        formData.append('year', year);

        fetch('process_request.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentReportData = data.data;
                displayReport(data.data);
                reportResults.style.display = 'block';
            } else {
                alert('Error generating report: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Report generation error:', err);
            alert('Something went wrong while generating the report.');
        })
        .finally(() => {
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }

    function displayReport(data) {
        // Set report title
        const reportTitle = document.getElementById('report-title');
        if (data.report_type === 'monthly') {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            reportTitle.textContent = `Document Requests Report - ${monthNames[parseInt(data.month) - 1]} ${data.year}`;
        } else {
            reportTitle.textContent = `Document Requests Report - Year ${data.year}`;
        }

        // Update summary cards
        updateSummaryCards(data.summary);

        // Update charts and tables
        updateStatusChart(data.summary.status_counts);
        updateDocumentTypeTable(data.summary.document_type_counts);
        updateBreakdownTable(data);
    }

    function updateSummaryCards(summary) {
        const summaryCards = document.getElementById('summary-cards');
        const statusColors = {
            'Pending': 'warning',
            'Approved': 'success',
            'Disapproved': 'danger'
        };

        let cardsHtml = `
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-value">${summary.total}</div>
                                <div>Total Requests</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        Object.entries(summary.status_counts).forEach(([status, count]) => {
            const color = statusColors[status] || 'secondary';
            const icon = status === 'Pending' ? 'clock' : 
                        status === 'Approved' ? 'check-circle' : 'times-circle';
            
            cardsHtml += `
                <div class="col-md-3">
                    <div class="card text-white bg-${color} mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-value">${count}</div>
                                    <div>${status}</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-${icon} fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        summaryCards.innerHTML = cardsHtml;
    }

    function updateStatusChart(statusCounts) {
        const ctx = document.getElementById('statusChart').getContext('2d');
        
        // Destroy existing chart if it exists
        if (window.statusChartInstance) {
            window.statusChartInstance.destroy();
        }

        const colors = {
            'Pending': '#ffc107',
            'Approved': '#198754',
            'Disapproved': '#dc3545'
        };

        window.statusChartInstance = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: Object.keys(statusCounts),
                datasets: [{
                    data: Object.values(statusCounts),
                    backgroundColor: Object.keys(statusCounts).map(status => colors[status]),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    function updateDocumentTypeTable(documentTypeCounts) {
        const tableContainer = document.getElementById('document-breakdown-table');
        
        let tableHtml = `
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Document Type</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        const total = Object.values(documentTypeCounts).reduce((sum, count) => sum + count, 0);
        
        Object.entries(documentTypeCounts)
            .sort((a, b) => b[1] - a[1])
            .forEach(([docType, count]) => {
                const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
                tableHtml += `
                    <tr>
                        <td>${docType}</td>
                        <td>${count}</td>
                        <td>${percentage}%</td>
                    </tr>
                `;
            });

        tableHtml += `
                    </tbody>
                </table>
            </div>
        `;

        tableContainer.innerHTML = tableHtml;
    }

    function updateBreakdownTable(data) {
        const tableBody = document.getElementById('breakdown-table-body');
        
        if (data.report_type === 'monthly') {
            // For monthly reports, show daily breakdown
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const monthName = monthNames[parseInt(data.month) - 1];
            
            // This would require additional database queries for daily breakdown
            // For now, showing monthly summary
            const summary = data.summary;
            tableBody.innerHTML = `
                <tr>
                    <td>${monthName} ${data.year}</td>
                    <td>${summary.total}</td>
                    <td>${summary.status_counts.Pending || 0}</td>
                    <td>${summary.status_counts.Approved || 0}</td>
                    <td>${summary.status_counts.Disapproved || 0}</td>
                </tr>
            `;
        } else {
            // For yearly reports, show monthly breakdown
            let tableHtml = '';
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            data.monthly_breakdown.forEach(monthData => {
                tableHtml += `
                    <tr>
                        <td>${monthNames[monthData.month - 1]} ${data.year}</td>
                        <td>${monthData.total}</td>
                        <td>${monthData.pending}</td>
                        <td>${monthData.approved}</td>
                        <td>${monthData.disapproved}</td>
                    </tr>
                `;
            });

            // Add yearly total
            tableHtml += `
                <tr class="table-primary fw-bold">
                    <td>Total ${data.year}</td>
                    <td>${data.summary.total}</td>
                    <td>${data.summary.status_counts.Pending || 0}</td>
                    <td>${data.summary.status_counts.Approved || 0}</td>
                    <td>${data.summary.status_counts.Disapproved || 0}</td>
                </tr>
            `;

            tableBody.innerHTML = tableHtml;
        }
    }

    function exportReport(exportType) {
        if (!currentReportData) {
            alert('Please generate a report first');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'generate_report');
        formData.append('report_type', currentReportData.report_type);
        formData.append('month', currentReportData.month);
        formData.append('year', currentReportData.year);
        formData.append('export_type', exportType);

        fetch('process_request.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Download the generated file
                window.location.href = data.filepath;
            } else {
                alert('Error exporting report: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Something went wrong while exporting the report.');
        });
    }
});
</script>

</body>
</html>