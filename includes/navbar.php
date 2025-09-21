<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/auth.php';

$userId = $_SESSION['user_id'] ?? null;
$user = null;
$notifCount = 0;
$notifResult = null;

if ($userId) {
    $notifSql = "SELECT activity, timestamp 
                 FROM activity_logs
                 WHERE user_id = ?
                 ORDER BY timestamp DESC 
                 LIMIT 5";
    $stmt = $conn->prepare($notifSql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $notifResult = $stmt->get_result();
    $notifCount = $notifResult->num_rows;
    $stmt->close();
}

if ($userId) {
    $userSql = "SELECT first_name, last_name, photo_path 
                FROM residents 
                WHERE id = ?";
    $stmt = $conn->prepare($userSql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $user = $userResult->fetch_assoc();
    $stmt->close();
}


$profilePhoto = "https://via.placeholder.com/40"; // default

if ($user && !empty($user['photo_path'])) {
    $photoPath = $user['photo_path'];

    if (preg_match('/^https?:\/\//', $photoPath)) {

        $profilePhoto = $photoPath;
    } else {

        $profilePhoto = "/barangay-balas/" . htmlspecialchars($photoPath);
    }
}

$userName = $user 
    ? htmlspecialchars($user['first_name'] . " " . $user['last_name']) 
    : "User";
?>

<!-- Top Navbar -->
<nav class="top-navbar navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <!-- Sidebar Toggle Button -->
        <button class="btn btn-link" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Right Side -->
        <div class="d-flex align-items-center ms-auto">
            
            <!-- Notifications Dropdown -->
            <div class="dropdown me-3">
                <button class="btn btn-link position-relative" type="button" 
                        id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell fa-lg"></i>
                    <?php if ($notifCount > 0): ?>
                        <span class="notification-badge"><?php echo $notifCount; ?></span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 300px;">
                    <li><h6 class="dropdown-header">Notifications</h6></li>

                    <?php if ($notifCount > 0 && $notifResult): ?>
                        <?php while ($row = $notifResult->fetch_assoc()): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="me-3">
                                        <i class="fas fa-info-circle text-primary"></i>
                                    </div>
                                    <div>
                                        <div><?php echo htmlspecialchars($row['activity']); ?></div>
                                        <small class="text-muted">
                                            <?php echo date("M d, Y h:i A", strtotime($row['timestamp'])); ?>
                                        </small>
                                    </div>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li><a class="dropdown-item text-center text-muted">No notifications</a></li>
                    <?php endif; ?>

                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center text-primary" href="/barangay-balas/pages/all_notifications.php">View All Notifications</a></li>
                </ul>
            </div>

            <!-- Profile Dropdown -->
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle d-flex align-items-center" type="button" 
                        id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $profilePhoto; ?>" 
                         alt="Profile" class="rounded-circle me-2" width="40" height="40">
                    <span><?php echo $userName; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="/barangay-balas/pages/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/barangay-balas/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script>
    document.getElementById('sidebarToggle').addEventListener('click', function () {
        document.body.classList.toggle('sidebar-collapsed');
    });
</script>
