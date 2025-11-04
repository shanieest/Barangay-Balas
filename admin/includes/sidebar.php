<?php
require_once 'db.php';
require_once 'auth.php';

$adminName = "Admin";

if (isset($_SESSION['admin_id'])) {
    $adminId = $_SESSION['admin_id'];
    $stmt = $conn->prepare("SELECT first_name, last_name FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $stmt->bind_result($firstName, $lastName);
    if ($stmt->fetch()) {
        $adminName = $firstName . ' ' . $lastName;
    }
    $stmt->close();
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar Navigation -->
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                
                <?php if (isSocialWorker()): ?>
                <!-- SOCIAL WORKER SIDEBAR -->
                <a class="nav-link <?= $currentPage == '../social_worker_dashboard.php' ? 'active' : '' ?>" href="../social_worker_dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    <span class="nav-text">Daycare Dashboard</span>
                </a>

                <?php else: ?>
                <!-- ADMIN/OFFICIAL SIDEBAR -->
                <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" href="./dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                    <span class="nav-text">Dashboard</span>
                </a>
                <?php endif; ?>

                <!-- Services Menu - VISIBLE TO ALL ROLES BUT DIFFERENT STRUCTURE -->
                <?php if (isSocialWorker()): ?>
                    <!-- SOCIAL WORKER: Only Enrollments link, no dropdown -->
                    <a class="nav-link <?= $currentPage == 'daycare.php' ? 'active' : '' ?>" href="../pages/daycare.php">
                        <div class="sb-nav-link-icon d-flex align-items-center">
                            <i class="fas fa-child"></i>
                        </div>
                        <span class="nav-text">Enrollments</span>
                    </a>

                <?php else: ?>
                    <!-- ADMIN/OFFICIAL: Full Services Dropdown -->
                    <div class="nav-item">
                        <a class="nav-link collapsed <?= (in_array($currentPage, ['servicesAdmin.php', 'reservations.php', 'document_requests.php', 'medicineRequest.php', 'daycare.php'])) ? 'active' : '' ?>" 
                        href="#" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#collapseServices" 
                        aria-expanded="<?= (in_array($currentPage, ['reservations.php', 'document_requests.php', 'medicineRequest.php', 'daycare.php'])) ? 'true' : 'false' ?>" 
                        aria-controls="collapseServices">
                            <div class="sb-nav-link-icon d-flex align-items-center">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <span class="nav-text">Services</span>
                            <div class="sb-sidenav-collapse-arrow d-flex align-items-center">
                                <i class="fas fa-angle-down"></i>
                            </div>
                        </a>
                        <div class="collapse <?= (in_array($currentPage, ['reservations.php', 'document_requests.php', 'medicineRequest.php', 'daycare.php'])) ? 'show' : '' ?>" 
                            id="collapseServices" 
                            aria-labelledby="headingServices" 
                            data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?= $currentPage == 'daycare.php' ? 'active' : '' ?>" href="daycare.php">
                                    <i class="fas fa-child me-2"></i>
                                    Enrollments
                                </a>
                                <a class="nav-link <?= $currentPage == 'reservations.php' ? 'active' : '' ?>" href="reservations.php">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Reservations
                                </a>
                                <a class="nav-link <?= $currentPage == 'document_requests.php' ? 'active' : '' ?>" href="document_requests.php">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Document Requests
                                </a>
                                <a class="nav-link <?= $currentPage == 'barangay_id_records.php' ? 'active' : '' ?>" href="barangay_id_records.php">
                                    <i class="fas fa-id-card me-2"></i>
                                    Barangay ID
                                </a>
                                <a class="nav-link <?= $currentPage == 'medicineRequest.php' ? 'active' : '' ?>" href="medicineRequest.php">
                                    <i class="fas fa-pills me-2"></i>
                                    Medicine Requests
                                </a>
                            </nav>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!isSocialWorker()): ?>
                <!-- ADMIN/OFFICIAL ONLY MENU ITEMS -->

                <!-- Residents Menu with Dropdown -->
                <div class="nav-item">
                    <a class="nav-link collapsed <?= (in_array($currentPage, ['residents.php', 'archived_accounts.php'])) ? 'active' : '' ?>" 
                       href="#" 
                       data-bs-toggle="collapse" 
                       data-bs-target="#collapseResidents" 
                       aria-expanded="<?= (in_array($currentPage, ['residents.php', 'archived_accounts.php'])) ? 'true' : 'false' ?>" 
                       aria-controls="collapseResidents">
                        <div class="sb-nav-link-icon d-flex align-items-center">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="nav-text">Residents</span>
                        <div class="sb-sidenav-collapse-arrow d-flex align-items-center">
                            <i class="fas fa-angle-down"></i>
                        </div>
                    </a>
                    <div class="collapse <?= (in_array($currentPage, ['residents.php', 'archived_accounts.php'])) ? 'show' : '' ?>" 
                         id="collapseResidents" 
                         aria-labelledby="headingResidents" 
                         data-bs-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <a class="nav-link <?= $currentPage == 'residents.php' ? 'active' : '' ?>" href="residents.php">
                                <i class="fas fa-user-check me-2"></i>
                                Active Residents
                            </a>
                            <a class="nav-link <?= $currentPage == 'archived_accounts.php' ? 'active' : '' ?>" href="archived_accounts.php">
                                <i class="fas fa-archive me-2"></i>
                                Archived Accounts
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Officials Menu with Dropdown -->
                <div class="nav-item">
                    <a class="nav-link collapsed <?= (in_array($currentPage, ['barangay-officials.php', 'social_worker.php'])) ? 'active' : '' ?>" 
                       href="#" 
                       data-bs-toggle="collapse" 
                       data-bs-target="#collapseOfficials" 
                       aria-expanded="<?= (in_array($currentPage, ['barangay-officials.php', 'social_worker.php'])) ? 'true' : 'false' ?>" 
                       aria-controls="collapseOfficials">
                        <div class="sb-nav-link-icon d-flex align-items-center">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <span class="nav-text">Officials</span>
                        <div class="sb-sidenav-collapse-arrow d-flex align-items-center">
                            <i class="fas fa-angle-down"></i>
                        </div>
                    </a>
                    <div class="collapse <?= (in_array($currentPage, ['barangay-officials.php', 'social_worker.php'])) ? 'show' : '' ?>" 
                         id="collapseOfficials" 
                         aria-labelledby="headingOfficials" 
                         data-bs-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <a class="nav-link <?= $currentPage == 'barangay-officials.php' ? 'active' : '' ?>" href="barangay-officials.php">
                                <i class="fas fa-users-cog me-2"></i>
                                All Officials
                            </a>
                            <?php if (isAdmin()): ?>
                            <a class="nav-link <?= $currentPage == 'social_worker.php' ? 'active' : '' ?>" href="social_worker.php">
                                <i class="fas fa-hands-helping me-2"></i>
                                Social Worker
                            </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>

                <a class="nav-link <?= $currentPage == 'announcements.php' ? 'active' : '' ?>" href="announcements.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-bullhorn"></i></div>
                    <span class="nav-text">Announcements</span>
                </a>

                <a class="nav-link <?= $currentPage == 'census.php' ? 'active' : '' ?>" href="census.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-chart-bar"></i></div>
                    <span class="nav-text">Census Data</span>
                </a>

                <?php endif; ?>
                <!-- END ROLE-BASED SIDEBAR -->

            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <div class="admin-name"><?= htmlspecialchars($adminName) ?></div>
            <div class="small text-muted mt-1">
                <i class="fas fa-user-shield me-1"></i>
                <?= htmlspecialchars($_SESSION['role'] ?? 'User') ?>
            </div>
        </div>
    </nav>
</div>