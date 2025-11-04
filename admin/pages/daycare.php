<?php
// admin/daycare.php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAuth();



// Get current school year
$currentYear = date('Y');
$currentMonth = date('n');
$defaultSchoolYear = ($currentMonth >= 6) ? "$currentYear-" . ($currentYear + 1) : ($currentYear - 1) . "-$currentYear";

// Get selected school year from filter
$selectedYear = $_GET['school_year'] ?? $defaultSchoolYear;

// Get all unique school years for filter dropdown
$yearsQuery = "SELECT DISTINCT school_year FROM daycare_enrollments ORDER BY school_year DESC";
$yearsResult = $conn->query($yearsQuery);

$sql = "SELECT id, child_first_name, child_middle_name, child_last_name, sex, address, birthday, confirmed, 
        confirmed_by, confirmed_at, created_at, guardian_name, email
        FROM daycare_enrollments
        WHERE school_year = ?
        ORDER BY confirmed ASC, id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selectedYear);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daycare Enrolled Children - S.Y. <?= htmlspecialchars($selectedYear) ?> | Barangay Balas Social Worker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <style>
    body {
      background-color: #f5f6fa;
      font-family: 'Segoe UI', sans-serif;
    }

    .container-fluid {
      margin-top: 20px;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      font-weight: bold;
      margin-bottom: 20px;
      color: #2c3e50;
    }

    .table th {
      background-color: #007bff;
      color: white;
      text-align: center;
    }

    .table td {
      vertical-align: middle;
      text-align: center;
    }

    .btn-confirm {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 5px;
      cursor: pointer;
    }

    .btn-confirm:hover {
      background-color: #218838;
    }

    .btn-confirm:disabled {
      background-color: #6c757d;
      cursor: not-allowed;
    }

    .confirmed-badge {
      background-color: #28a745;
      color: white;
      padding: 5px 10px;
      border-radius: 5px;
      font-size: 0.9em;
    }

    .filter-section {
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .export-btn {
      background-color: #17a2b8;
      color: white;
    }

    .role-badge {
      margin-bottom: 15px;
    }

    /* ✅ Fix for hidden bottom rows / scroll issue */
    #layoutSidenav_content {
      overflow-y: auto;
      max-height: 100vh;
      padding-bottom: 80px;
    }

    .table-responsive {
      overflow-x: auto;
      max-height: 70vh; /* Table scroll area */
      overflow-y: auto;
    }

    /* ✅ Optional: sticky table headers while scrolling */
    .table thead th {
      position: sticky;
      top: 0;
      z-index: 2;
    }
  </style>

</head>
<body class="sb-nav-fixed sb-sidenav-toggled">
  <?php include '../includes/navbar.php'; ?>
  
  <div id="layoutSidenav">
    <?php include '../includes/sidebar.php'; ?>
    
    <div id="layoutSidenav_content">
      <main>
        <div class="container-fluid px-3 px-md-4 py-4">
          <div class="dashboard-header">
            <div class="row align-items-center">
              <div class="col-md-8">
                <h1 class="h2 mb-2"><i class="fas fa-child me-2"></i>Daycare Enrolled Children</h1>
                <p class="mb-0">Manage and confirm daycare enrollment applications.</p>
              </div>
              <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="d-flex flex-wrap justify-content-md-end">
                  <button class="btn btn-outline-light me-2 mb-2"><i class="fas fa-calendar me-1"></i> <?= date('F j, Y') ?></button>
                  <button class="btn btn-outline-light mb-2" id="refreshBtn"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Role Badge -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="role-badge">
                <span class="badge bg-info">
                  <i class="fas fa-hands-helping me-1"></i>
                  Social Worker - Daycare Management
                </span>
              </div>
            </div>
          </div>

          <!-- Filter Section -->
          <div class="filter-section">
            <div>
              <label for="schoolYearFilter">School Year:</label>
              <select id="schoolYearFilter" class="form-select d-inline-block w-auto" onchange="filterByYear(this.value)">
                <?php while ($year = $yearsResult->fetch_assoc()): ?>
                  <option value="<?= htmlspecialchars($year['school_year']) ?>" 
                          <?= $year['school_year'] === $selectedYear ? 'selected' : '' ?>>
                    S.Y. <?= htmlspecialchars($year['school_year']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            
            <button class="btn export-btn" onclick="exportToExcel()">
              <i class="fas fa-download me-1"></i> Export to Excel
            </button>
          </div>

          <div class="table-responsive">
            <table class="table table-striped table-bordered" id="enrollmentTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Full Name</th>
                  <th>Sex</th>
                  <th>Address</th>
                  <th>Birthday</th>
                  <th>Age</th>
                  <th>Guardian</th>
                  <th>Email</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($result->num_rows > 0) {
                  $count = 1;
                  while ($row = $result->fetch_assoc()) {
                    $birthday = new DateTime($row['birthday']);
                    $today = new DateTime();
                    $age = $today->diff($birthday)->y;

                    $statusBadge = $row['confirmed'] 
                      ? "<span class='confirmed-badge'>Confirmed</span>" 
                      : "<span class='badge bg-warning'>Pending</span>";

                    if ($row['confirmed']) {
                        $button = "<button class='btn btn-secondary btn-sm' disabled>Confirmed</button>";
                    } else {
                        //Only show confirm button if user is Admin or Social Worker
                        if (isAdmin() || isSocialWorker()) {
                            $button = "<button class='btn-confirm btn-sm' onclick='confirmChild({$row['id']}, this)'>Confirm & Notify</button>";
                        } else {
                            $button = "<span class='text-muted'>No Action</span>";
                        }
                    }

                    echo "<tr>
                            <td>{$count}</td>
                            <td>{$row['child_first_name']} {$row['child_middle_name']} {$row['child_last_name']}</td>
                            <td>{$row['sex']}</td>
                            <td>{$row['address']}</td>
                            <td>{$row['birthday']}</td>
                            <td>{$age}</td>
                            <td>{$row['guardian_name']}</td>
                            <td>{$row['email']}</td>
                            <td>{$statusBadge}</td>
                            <td>{$button}</td>
                          </tr>";
                    $count++;
                  }
                } else {
                  echo "<tr><td colspan='10' class='text-center py-4'>No enrolled children found for S.Y. {$selectedYear}.</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
      <?php include '../includes/footer.php'; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/script.js"></script>
  
  <script>
    function confirmChild(id, btn) {
      if (!confirm("Are you sure you want to confirm this child's enrollment?\n\nAn email notification will be sent to the parent/guardian.")) {
        return;
      }

      btn.disabled = true;
      btn.textContent = 'Processing...';

      axios.post('../backend/confirm_enrollment.php', { id: id })
        .then(response => {
          if (response.data.success) {
            btn.textContent = 'Confirmed';
            btn.classList.remove('btn-confirm');
            btn.classList.add('btn-secondary');
            
            // Update status badge
            const row = btn.closest('tr');
            const statusCell = row.cells[8];
            statusCell.innerHTML = "<span class='confirmed-badge'>Confirmed</span>";
            
            alert(' ' + response.data.message);
          } else {
            btn.disabled = false;
            btn.textContent = 'Confirm & Notify';
            alert(' ' + response.data.message);
          }
        })
        .catch(error => {
          btn.disabled = false;
          btn.textContent = 'Confirm & Notify';
          alert(' Error connecting to server: ' + error.message);
        });
    }

    function filterByYear(year) {
      window.location.href = 'daycare.php?school_year=' + encodeURIComponent(year);
    }

    function exportToExcel() {
      const year = document.getElementById('schoolYearFilter').value;
      window.location.href = '../backend/export_daycare.php?school_year=' + encodeURIComponent(year);
    }
    
    // Refresh button functionality
    document.getElementById('refreshBtn').addEventListener('click', function() {
      this.classList.add('fa-spin');
      setTimeout(() => {
        window.location.reload();
      }, 500);
    });
  </script>
</body>
</html>