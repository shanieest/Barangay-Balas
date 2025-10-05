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

                <a class="nav-link <?= $currentPage == 'servicesAdmin.php' ? 'active' : '' ?>" href="servicesAdmin.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-briefcase"></i></div>
                    <span class="nav-text">Services</span>
                </a>

                <a class="nav-link <?= $currentPage == 'announcements.php' ? 'active' : '' ?>" href="announcements.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-bullhorn"></i></div>
                    <span class="nav-text">Announcements</span>
                </a>

                <a class="nav-link <?= $currentPage == 'residents.php' ? 'active' : '' ?>" href="residents.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                    <span class="nav-text">Residents</span>
                </a>

                <a class="nav-link <?= $currentPage == 'census.php' ? 'active' : '' ?>" href="census.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
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
