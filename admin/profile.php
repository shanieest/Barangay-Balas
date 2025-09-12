<?php 
require_once __DIR__ . '/includes/auth.php';
requireAuth();
require_once __DIR__ . '/includes/db.php'; 
global $conn; 

$user_id = getUserId();

$stmt = $conn->prepare("SELECT id, username, first_name, last_name, email, position, contact_number, photo_path FROM admin_users WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error); 
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$profile_photo = $user['photo_path'] ?? 'assets/admin-avatar.jpg';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Barangay Balas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="sb-nav-fixed">
    <?php include 'includes/navbar.php'; ?>
    
    <div id="layoutSidenav">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">My Profile</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Profile</li>
                    </ol>
                    
                    <div class="row">
                        <div class="col-xl-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-user me-1"></i>
                                    Profile Picture
                                </div>
                                <div class="card-body text-center">
                                    <img src="<?= htmlspecialchars($profile_photo) ?>" id="profilePhoto" class="rounded-circle mb-3" width="150" height="150">
                                    <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 5 MB</div>
                                    <form id="photoUploadForm" enctype="multipart/form-data">
                                        <input type="file" id="photoInput" name="photo" accept="image/*" style="display: none;">
                                        <button type="button" class="btn btn-primary" onclick="document.getElementById('photoInput').click()">
                                            <i class="fas fa-upload me-1"></i> Upload new photo
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Account Details
                                </div>
                                <div class="card-body">
                                    <form id="profileForm">
                                        <div class="row gx-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="profileFirstName">First name</label>
                                                <input class="form-control" id="profileFirstName" name="first_name" type="text" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="profileLastName">Last name</label>
                                                <input class="form-control" id="profileLastName" name="last_name" type="text" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="small mb-1" for="profileEmail">Email address</label>
                                            <input class="form-control" id="profileEmail" name="email" type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                        </div>
                                        <div class="row gx-3 mb-3">
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="profilePosition">Position</label>
                                                <input class="form-control" id="profilePosition" type="text" value="<?= htmlspecialchars($user['position'] ?? '') ?>" disabled>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small mb-1" for="profileContact">Contact Number</label>
                                                <input class="form-control" id="profileContact" name="contact_number" type="tel" value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-lock me-1"></i>
                            Change Password
                        </div>
                        <div class="card-body">
                            <form id="passwordForm">
                                <div class="row gx-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="small mb-1" for="currentPassword">Current Password</label>
                                        <input class="form-control" id="currentPassword" name="current_password" type="password" placeholder="Enter current password" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small mb-1" for="newPassword">New Password</label>
                                        <input class="form-control" id="newPassword" name="new_password" type="password" placeholder="Enter new password" required minlength="8">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small mb-1" for="confirmPassword">Confirm Password</label>
                                        <input class="form-control" id="confirmPassword" name="confirm_password" type="password" placeholder="Confirm new password" required minlength="8">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Danger Zone
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Delete Account</h5>
                            <p class="card-text">Once you delete your account, there is no going back. Please be certain.</p>
                            <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                <i class="fas fa-trash me-1"></i> Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </main>
            
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

     <?php include 'modals/profileModal.php'; ?>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/profile.js"></script>
</body>
</html>