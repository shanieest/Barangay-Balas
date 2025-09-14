<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Barangay Balas Portal</title>
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
        
        .availability-badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .available {
            color: #198754;
        }
        
        .limited {
            color: #fd7e14;
        }
        
        .unavailable {
            color: #dc3545;
        }
        
        .calendar-icon {
            cursor: pointer;
        }
        
        #availabilityResults {
            max-height: 300px;
            overflow-y: auto;
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
                <!-- Services Section -->
                <h2 class="mb-4">Barangay Services</h2>
                <div class="card-body">
                    <div class="row">
                        <!-- Request Documents Card -->
                     <div class="card-body">
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
              <h5>Request Documents</h5>
              <p class="text-muted">Character certification</p>
              <button class="btn btn-primary btn-sm request-btn" 
                      data-bs-toggle="modal" 
                      data-bs-target="#documentRequestModal" 
                      data-document="Documents">
                Request
              </button>
            </div>
          </div>
        </div>

                        <!-- Reservation of Service Vehicle -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-bus fa-3x text-success mb-3"></i>
                                    <h5>Service Vehicle</h5>
                                    <p class="text-muted">Reserve a barangay vehicle</p>
                                    <div class="availability mb-2">
                                        <span class="availability-badge bg-success text-white" id="vehicleAvailability">
                                            <i class="fas fa-spinner fa-spin"></i> Checking availability...
                                        </span>
                                    </div>
                                    <button class="btn btn-success btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#vehicleReservationModal"
                                            id="vehicleReserveBtn">
                                        Reserve
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Reservation of Tent -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-campground fa-3x text-warning mb-3"></i>
                                    <h5>Barangay Tent</h5>
                                    <p class="text-muted">Reserve a barangay tent</p>
                                    <div class="availability mb-2">
                                        <span class="availability-badge bg-success text-white" id="tentAvailability">
                                            <i class="fas fa-spinner fa-spin"></i> Checking availability...
                                        </span>
                                    </div>
                                    <button class="btn btn-warning btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#tentReservationModal"
                                            id="tentReserveBtn">
                                        Reserve
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Availability Check Section -->
                <section class="mt-5">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Check Availability</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Select Service</label>
                                        <select class="form-select" id="availabilityService">
                                            <option value="vehicle">Service Vehicle</option>
                                            <option value="tent">Barangay Tent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Select Date</label>
                                        <div class="input-group">
                                            <input type="date" class="form-control" id="availabilityDate" 
                                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                            <span class="input-group-text calendar-icon" id="datePickerIcon">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary" id="checkAvailabilityBtn">
                                <i class="fas fa-search me-1"></i> Check Availability
                            </button>
                            
                            <div class="mt-4" id="availabilityResults" style="display: none;">
                                <h6>Availability Results:</h6>
                                <div class="list-group" id="availabilityList">
                                    <!-- Results will be populated here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <?php include '../../pages/modals/documentsModal.php'; ?>
    <?php include '../../pages/modals/vehicleReservationModal.php'; ?>
    <?php include '../../pages/modals/tentReservationModal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/services.js"></script>
</body>
</html>