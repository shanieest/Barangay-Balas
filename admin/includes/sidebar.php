<?php
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default admin name
$adminName = "Admin";

// Get admin name if logged in
if (isset($_SESSION['user_id'])) {
    $adminId = $_SESSION['user_id'];
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
                    <div class="sb-nav-link-icon"><i class="fas fa-user-people"></i></div>
                    Census data
                </a>
                <a class="nav-link" href="documentAdmin.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                    Document Requests
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
