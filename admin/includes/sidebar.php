<?php
require_once 'includes/db.php';

// Default admin name
$adminName = "Admin";

// Get admin name if logged in
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
?>
<!-- Sidebar Navigation -->
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading"></div>
                <a class="nav-link" href="dashboard.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>

                <div class="sb-sidenav-menu-heading"></div>
                <a class="nav-link" href="announcements.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-bullhorn"></i></div>
                    Announcements
                </a>
                <a class="nav-link" href="residents.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Residents
                </a>
                <a class="nav-link" href="census.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                    Census Data
                </a>
                <a class="nav-link" href="servicesAdmin.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-briefcase"></i></div>
                    Services
                </a>
                <a class="nav-link" href="barangay-officials.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-user-tie"></i></div>
                    Barangay Officials
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <?= htmlspecialchars($adminName) ?>
        </div>
    </nav>
</div>
