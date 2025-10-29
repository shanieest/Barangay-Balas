<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../signup.php");
    exit;
}

$userId = $_SESSION['user_id'];
$profile = [];
$message = isset($_GET['message']) ? $_GET['message'] : '';

// Fetch profile data
$profileQuery = "SELECT * FROM residents WHERE id = ?";
$stmt = $conn->prepare($profileQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc() ?? [];
$stmt->close();

// Fetch Barangay ID status (if any)
$idQuery = "
    SELECT status, digital_id_path, valid_until
    FROM barangay_id_applications
    WHERE resident_id = ?
    ORDER BY application_date DESC
    LIMIT 1
";
$stmt = $conn->prepare($idQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$idResult = $stmt->get_result();
$barangayId = $idResult->fetch_assoc();
$stmt->close();

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Barangay Balas Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/profile.css">
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <?php include '../../includes/sidebar.php'?>

    <!-- Main Content -->
    <div class="main-content">
        <?php include '../../includes/navbar.php'?>

        <div class="content-area">
            <h2 class="mb-4">My Profile</h2>
            
            <?php if (!empty($message)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <span>Personal Information</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Profile Picture + ID Button -->
                        <div class="col-md-3 text-center">
                            <div class="photo-upload-container mb-3">
                                <img src="<?= htmlspecialchars(!empty($profile['photo_path']) ? '../../' . $profile['photo_path'] : 'https://via.placeholder.com/150?text=Upload+Photo') ?>" 
                                     alt="Profile" 
                                     class="profile-img"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#photoUploadModal">
                                <div class="photo-upload-overlay" data-bs-toggle="modal" data-bs-target="#photoUploadModal">
                                    <div class="photo-upload-text">
                                        <i class="fas fa-camera fa-2x mb-2"></i>
                                        <p>Change Photo</p>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#photoUploadModal">
                                Change Photo
                            </button>

                            <!-- Barangay ID Button -->
                            <?php if (!empty($barangayId) && $barangayId['status'] === 'Approved' && !empty($barangayId['digital_id_path'])): ?>
                                <a href="view_digital_id.php" class="btn btn-sm btn-success w-100 mb-1">
                                    <i class="fas fa-id-card me-1"></i> View Digital Barangay ID
                                </a>
                                <small class="text-muted d-block mb-3">
                                    Valid until <?= date('F d, Y', strtotime($barangayId['valid_until'])) ?>
                                </small>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary w-100 mb-3" disabled>
                                    <i class="fas fa-id-card me-1"></i> No Digital ID Yet
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Profile Form -->
                        <div class="col-md-9">
                            <form id="profileForm" method="POST" action="profile-backend.php">
                                <input type="hidden" name="action" value="update_profile">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

                                <!-- Basic Information -->
                                <div class="section-title"><i class="fas fa-user me-2"></i>Basic Information</div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" name="middle_name" value="<?= htmlspecialchars($profile['middle_name'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($profile['last_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Suffix</label>
                                        <select class="form-select" name="suffix">
                                            <option value="">None</option>
                                            <option value="Jr." <?= ($profile['suffix'] ?? '') == 'Jr.' ? 'selected' : '' ?>>Jr.</option>
                                            <option value="Sr." <?= ($profile['suffix'] ?? '') == 'Sr.' ? 'selected' : '' ?>>Sr.</option>
                                            <option value="II" <?= ($profile['suffix'] ?? '') == 'II' ? 'selected' : '' ?>>II</option>
                                            <option value="III" <?= ($profile['suffix'] ?? '') == 'III' ? 'selected' : '' ?>>III</option>
                                            <option value="IV" <?= ($profile['suffix'] ?? '') == 'IV' ? 'selected' : '' ?>>IV</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Birthdate <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="birthdate" value="<?= $profile['birthdate'] ?? '' ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Place of Birth <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="place_of_birth" value="<?= htmlspecialchars($profile['place_of_birth'] ?? '') ?>" required placeholder="e.g., Mexico, Pampanga">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                                        <select class="form-select" name="sex" required>
                                            <option value="">Select Gender</option>
                                            <option value="male" <?= ($profile['sex'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                            <option value="female" <?= ($profile['sex'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Civil Status <span class="text-danger">*</span></label>
                                        <select class="form-select" name="civil_status">
                                            <option value="">Select Status</option>
                                            <option value="Single" <?= ($profile['civil_status'] ?? '') == 'Single' ? 'selected' : '' ?>>Single</option>
                                            <option value="Married" <?= ($profile['civil_status'] ?? '') == 'Married' ? 'selected' : '' ?>>Married</option>
                                            <option value="Widowed" <?= ($profile['civil_status'] ?? '') == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                            <option value="Separated" <?= ($profile['civil_status'] ?? '') == 'Separated' ? 'selected' : '' ?>>Separated</option>
                                            <option value="Divorced" <?= ($profile['civil_status'] ?? '') == 'Divorced' ? 'selected' : '' ?>>Divorced</option>
                                        </select>
                                    </div>
                                
                                </div>

                                <!-- Contact Info -->
                                <div class="section-divider">
                                    <div class="section-title"><i class="fas fa-phone me-2"></i>Contact Information</div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Email Address</label>
                                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($profile['email'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" name="contact_number" value="<?= htmlspecialchars($profile['contact_number'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address Info -->
                                <div class="section-divider">
                                    <div class="section-title">
                                        <i class="fas fa-home me-2"></i>Address Information
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">House Number <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="house_number" value="<?= htmlspecialchars($profile['house_number'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Purok <span class="text-danger">*</span></label>
                                            <select class="form-select" name="purok" required>
                                                <option value="">Select Purok</option>
                                                <?php for ($i=1; $i<=7; $i++): ?>
                                                    <option value="<?= $i ?>" <?= ($profile['purok'] ?? '') == $i ? 'selected' : '' ?>>Purok <?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Complete Address <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($profile['address'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="reset" class="btn btn-outline-secondary me-2">Reset</button>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../modals/profileModal.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/profile.js"></script>
</body>
</html>