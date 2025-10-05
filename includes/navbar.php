<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/auth.php';

$userId = $_SESSION['user_id'] ?? null;
$user = null;

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
        
        <div class="d-flex align-items-center ms-auto">

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
