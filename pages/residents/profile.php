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
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" name="middle_name" value="<?= htmlspecialchars($profile['middle_name'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($profile['last_name'] ?? '') ?>" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Suffix</label>
                                        <select class="form-select" name="suffix" readonly>
                                            <option value="" readonly>None</option>
                                            <option value="Jr." <?= ($profile['suffix'] ?? '') == 'Jr.' ? 'selected' : '' ?> readonly>Jr.</option>
                                            <option value="Sr." <?= ($profile['suffix'] ?? '') == 'Sr.' ? 'selected' : '' ?> readonly>Sr.</option>
                                            <option value="II" <?= ($profile['suffix'] ?? '') == 'II' ? 'selected' : '' ?> readonly>II</option>
                                            <option value="III" <?= ($profile['suffix'] ?? '') == 'III' ? 'selected' : '' ?> readonly>III</option>
                                            <option value="IV" <?= ($profile['suffix'] ?? '') == 'IV' ? 'selected' : '' ?> readonly>IV</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Birthdate</label>
                                        <input type="date" class="form-control" name="birthdate" value="<?= $profile['birthdate'] ?? '' ?>" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Place of Birth</label>
                                        <input type="text" class="form-control" name="place_of_birth" value="<?= htmlspecialchars($profile['place_of_birth'] ?? '') ?>" required placeholder="e.g., Mexico, Pampanga">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Sex</label>
                                        <select class="form-select" name="sex" readonly>
                                            <option value="">Select Sex</option>
                                            <option value="male" <?= ($profile['sex'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                            <option value="female" <?= ($profile['sex'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Civil Status</label>
                                        <select class="form-select" name="civil_status" required>
                                            <option value="">Select Status</option>
                                            <option value="Single" <?= ($profile['civil_status'] ?? '') == 'Single' ? 'selected' : '' ?>>Single</option>
                                            <option value="Married" <?= ($profile['civil_status'] ?? '') == 'Married' ? 'selected' : '' ?>>Married</option>
                                            <option value="Widowed" <?= ($profile['civil_status'] ?? '') == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                            <option value="Separated" <?= ($profile['civil_status'] ?? '') == 'Separated' ? 'selected' : '' ?>>Separated</option>
                                            <option value="Divorced" <?= ($profile['civil_status'] ?? '') == 'Divorced' ? 'selected' : '' ?>>Divorced</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Contact Information -->
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
                                                                
                                <div class="section-divider">
                                    <div class="section-title">
                                        <i class="fas fa-home me-2"></i>Address Information
                                        <!--<button type="button" class="btn btn-sm btn-outline-primary ms-2" id="toggleAddressEdit">
                                            <i class="fas fa-edit me-1"></i>Edit-->
                                        </button>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">House Number</label>
                                            <input type="text" class="form-control" name="house_number" id="houseNumberInput" 
                                                value="<?= htmlspecialchars($profile['house_number'] ?? '') ?>" required readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Purok</label>
                                            <select class="form-select" name="purok" id="purokSelect" required>
                                                <option value="">Select Purok</option>
                                                <option value="Purok 1" <?= ($profile['purok'] ?? '') == 'Purok 1' ? 'selected' : '' ?>>Purok 1</option>
                                                <option value="Purok 2" <?= ($profile['purok'] ?? '') == 'Purok 2' ? 'selected' : '' ?>>Purok 2</option>
                                                <option value="Purok 3" <?= ($profile['purok'] ?? '') == 'Purok 3' ? 'selected' : '' ?>>Purok 3</option>
                                                <option value="Purok 4" <?= ($profile['purok'] ?? '') == 'Purok 4' ? 'selected' : '' ?>>Purok 4</option>
                                                <option value="Purok 5" <?= ($profile['purok'] ?? '') == 'Purok 5' ? 'selected' : '' ?>>Purok 5</option>
                                                <option value="Purok 6" <?= ($profile['purok'] ?? '') == 'Purok 6' ? 'selected' : '' ?>>Purok 6</option>
                                                <option value="Purok 7" <?= ($profile['purok'] ?? '') == 'Purok 7' ? 'selected' : '' ?>>Purok 7</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Complete Address</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="address" id="addressInput" 
                                                value="<?= htmlspecialchars($profile['address'] ?? '') ?>" required readonly>
                                        </div>
                                        <div class="form-text text-muted">
                                            <small>Address is automatically generated. Click Edit to update house number and purok.</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Educational & Professional Information -->
                                <div class="section-divider">
                                    <div class="section-title"><i class="fas fa-graduation-cap me-2"></i>Educational & Professional Information</div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Educational Attainment</label>
                                            <select class="form-select" name="educational_attainment">
                                                <option value="">Select Educational Level</option>
                                                <option value="No Formal Education" <?= ($profile['educational_attainment'] ?? '') == 'No Formal Education' ? 'selected' : '' ?>>No Formal Education</option>
                                                <option value="Elementary" <?= ($profile['educational_attainment'] ?? '') == 'Elementary' ? 'selected' : '' ?>>Elementary</option>
                                                <option value="Elementary Graduate" <?= ($profile['educational_attainment'] ?? '') == 'Elementary Graduate' ? 'selected' : '' ?>>Elementary Graduate</option>
                                                <option value="High School" <?= ($profile['educational_attainment'] ?? '') == 'High School' ? 'selected' : '' ?>>High School</option>
                                                <option value="High School Graduate" <?= ($profile['educational_attainment'] ?? '') == 'High School Graduate' ? 'selected' : '' ?>>High School Graduate</option>
                                                <option value="Vocational" <?= ($profile['educational_attainment'] ?? '') == 'Vocational' ? 'selected' : '' ?>>Vocational</option>
                                                <option value="College" <?= ($profile['educational_attainment'] ?? '') == 'College' ? 'selected' : '' ?>>College</option>
                                                <option value="College Graduate" <?= ($profile['educational_attainment'] ?? '') == 'College Graduate' ? 'selected' : '' ?>>College Graduate</option>
                                                <option value="Post Graduate" <?= ($profile['educational_attainment'] ?? '') == 'Post Graduate' ? 'selected' : '' ?>>Post Graduate</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Occupation</label>
                                            <input type="text" class="form-control" name="occupation" value="<?= htmlspecialchars($profile['occupation'] ?? '') ?>" placeholder="e.g. Teacher, Farmer, Student">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Personal Information -->
                                <div class="section-divider">
                                    <div class="section-title"><i class="fas fa-info-circle me-2"></i>Personal Information</div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Religion</label>
                                            <input type="text" class="form-control" name="religion" value="<?= htmlspecialchars($profile['religion'] ?? '') ?>" placeholder="e.g. Roman Catholic, Islam, Protestant">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" name="has_philhealth" id="has_philhealth" value="1" <?= (!empty($profile['philhealth_number'])) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="has_philhealth">
                                                    <strong>PhilHealth Member</strong>
                                                    <small class="text-muted d-block">Check if you have PhilHealth coverage</small>
                                                </label>
                                            </div>
                                            <div id="philhealthNumberContainer" style="<?= (!empty($profile['philhealth_number'])) ? '' : 'display: none;' ?>">
                                                <label class="form-label mt-2">PhilHealth Number</label>
                                                <input type="text" class="form-control" name="philhealth_number" value="<?= htmlspecialchars($profile['philhealth_number'] ?? '') ?>" placeholder="12-345678901-2">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Social Services -->
                                <div class="section-divider">
                                    <div class="section-title"><i class="fas fa-heart me-2"></i>Social Services</div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_indigent" id="is_indigent" value="1" <?= (!empty($profile['is_indigent']) && $profile['is_indigent'] == 1) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_indigent">
                                                    <strong>Indigent Member</strong>
                                                    <small class="text-muted d-block">Member of an indigent family</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_4ps_member" id="is_4ps_member" value="1" <?= (!empty($profile['is_4ps_member']) && $profile['is_4ps_member'] == 1) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_4ps_member">
                                                    <strong>4Ps Member</strong>
                                                    <small class="text-muted d-block">Pantawid Pamilyang Pilipino Program beneficiary</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Medical Information -->
                                <div class="section-divider">
                                    <div class="section-title"><i class="fas fa-notes-medical me-2"></i>Medical Information</div>
                                    <div class="mb-3">
                                        <label class="form-label">Medical History</label>
                                        <textarea class="form-control" name="medical_history" rows="4" placeholder="Please list any chronic conditions, allergies, medications, or other relevant medical information..."><?= htmlspecialchars($profile['medical_history'] ?? '') ?></textarea>
                                        <div class="form-text">Optional: This information helps us provide better services and emergency response.</div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="reset" class="btn btn-outline-secondary me-2">Reset Form</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
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