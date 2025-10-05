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
    <link rel="stylesheet" href="../../assets/css/services.css">

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
                                    <button class="btn btn-success btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#vehicleReservationModal">
                                        Reserve
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Reservation Others -->
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-campground fa-3x text-warning mb-3"></i>
                                    <h5>Barangay Services</h5>
                                    <p class="text-muted">Other Services</p>
                                    <button class="btn btn-warning btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#tentReservationModal">
                                        Reserve
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Services Information -->
                <section class="mt-5">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Service Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-bus text-success me-2"></i>Service Vehicle</h6>
                                    <ul class="list-unstyled ms-3">
                                        <li>• Available for medical emergencies</li>
                                        <li>• Transportation for official business</li>
                                        <li>• Community event transportation</li>
                                        <li>• Request at least 1 day in advance</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-campground text-warning me-2"></i>Other Barangay Services</h6>
                                    <ul class="list-unstyled ms-3">
                                        <li>• Perfect for outdoor events</li>
                                        <li>• Family gatherings and celebrations</li>
                                        <li>• Community activities</li>
                                        <li>• Setup assistance available</li>
                                    </ul>
                                </div>
                            </div>
                            <hr>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> All reservations are subject to approval by Administrator.
                                You will be contacted within 24 hours regarding your reservation status.
                                However, priority will be given to emergency requests.
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
<script src="../../assets/js/services.js"></script>
</body>
</html>