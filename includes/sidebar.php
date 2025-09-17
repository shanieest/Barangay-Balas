<!-- sidebar.php -->
<div class="sidebar">
    <div class="sidebar-header d-flex justify-content-between align-items-center">
        <h3 class="m-0">Barangay Balas</h3>
    </div>
    <ul class="sidebar-menu">
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class="fas fa-home"></i>
                <span class="sidebar-text">Dashboard</span>
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : '' ?>">
            <a href="services.php">
                <i class="fas fa-briefcase"></i>
                <span class="sidebar-text">Services</span>
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : '' ?>">
            <a href="announcements.php">
                <i class="fas fa-bullhorn"></i>
                <span class="sidebar-text">Announcements</span>
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
            <a href="profile.php">
                <i class="fas fa-user"></i>
                <span class="sidebar-text">My Profile</span>
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'services-history.php' ? 'active' : '' ?>">
            <a href="services-history.php">
                <i class="fas fa-history"></i>
                <span class="sidebar-text">Services History</span>
            </a>
        </li>
        <li class="<?= basename($_SERVER['PHP_SELF']) == 'census.php' ? 'active' : '' ?>">
            <a href="census.php">
                <i class="fas fa-users"></i>
                <span class="sidebar-text">Census Data</span>
            </a>
        </li>
    </ul>
</div>