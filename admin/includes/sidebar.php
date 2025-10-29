<?php
require_once 'includes/db.php';

// Default admin name
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
                <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                    <span class="nav-text">Dashboard</span>
                </a>

                <!-- Services Menu with Dropdown -->
                <div class="nav-item">
                    <a class="nav-link collapsed <?= (in_array($currentPage, ['servicesAdmin.php', 'reservations.php', 'document_requests.php'])) ? 'active' : '' ?>" 
                       href="#" 
                       data-bs-toggle="collapse" 
                       data-bs-target="#collapseServices" 
                       aria-expanded="<?= (in_array($currentPage, ['reservations.php', 'document_requests.php'])) ? 'true' : 'false' ?>" 
                       aria-controls="collapseServices">
                        <div class="sb-nav-link-icon d-flex align-items-center">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <span class="nav-text">Services</span>
                        <div class="sb-sidenav-collapse-arrow d-flex align-items-center">
                            <i class="fas fa-angle-down"></i>
                        </div>
                    </a>
                    <div class="collapse <?= (in_array($currentPage, [ 'reservations.php', 'document_requests.php'])) ? 'show' : '' ?>" 
                         id="collapseServices" 
                         aria-labelledby="headingServices" 
                         data-bs-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
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
                        </nav>
                    </div>
                </div>

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

                <a class="nav-link <?= $currentPage == 'announcements.php' ? 'active' : '' ?>" href="announcements.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-bullhorn"></i></div>
                    <span class="nav-text">Announcements</span>
                </a>

                <a class="nav-link <?= $currentPage == 'census.php' ? 'active' : '' ?>" href="census.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-chart-bar"></i></div>
                    <span class="nav-text">Census Data</span>
                </a>

                <a class="nav-link <?= $currentPage == 'barangay-officials.php' ? 'active' : '' ?>" href="barangay-officials.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-user-tie"></i></div>
                    <span class="nav-text">Barangay Officials</span>
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <div class="admin-name"><?= htmlspecialchars($adminName) ?></div>
        </div>
    </nav>
</div>

