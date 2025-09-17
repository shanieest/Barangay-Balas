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

$profileQuery = "SELECT * FROM residents WHERE id = ?";
$stmt = $conn->prepare($profileQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc() ?? [];
$stmt->close();

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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue:  #0033cc;
            --secondary-blue: #3a7cb9;
            --accent-red: #e63946;
            --accent-yellow: #ffbe0b;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-blue), var(--secondary-blue));
            color: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            padding: 10px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }
        
        .sidebar-menu li:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu li.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid var(--accent-yellow);
        }
        
        .sidebar-menu li a {
            color: white;
            text-decoration: none;
            display: block;
        }
        
        .sidebar-menu li i {
            margin-right: 10px;
            color: var(--accent-yellow);
        }
        
        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        .top-navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 20px;
        }
        
        .content-area {
            padding: 20px;
            min-height: calc(100vh - 70px);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--accent-red);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-warning {
            background-color: var(--accent-yellow);
            border-color: var(--accent-yellow);
            color: #333;
        }
        
        .btn-danger {
            background-color: var(--accent-red);
            border-color: var(--accent-red);
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-blue);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .profile-img:hover {
            opacity: 0.8;
        }
        
        .alert {
            border-radius: 10px;
        }
        
        .photo-upload-container {
            position: relative;
            display: inline-block;
        }
        
        .photo-upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .photo-upload-container:hover .photo-upload-overlay {
            opacity: 1;
        }
        
        .photo-upload-text {
            color: white;
            text-align: center;
            font-size: 14px;
        }
        
        .modal-content {
            border-radius: 10px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar .sidebar-text {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .sidebar-header h3 {
                display: none;
            }
            
            .sidebar-menu li {
                text-align: center;
            }
            
            .sidebar-menu li i {
                margin-right: 0;
                font-size: 1.2rem;
            }
        }
    </style>
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
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <span>Personal Information</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
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
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#photoUploadModal">
                                    Change Photo
                                </button>
                            </div>
                            <div class="col-md-9">
                                <form id="profileForm" method="POST" action="profile-backend.php">
                                    <input type="hidden" name="action" value="update_profile">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Middle Name</label>
                                            <input type="text" class="form-control" name="middle_name" value="<?= htmlspecialchars($profile['middle_name'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($profile['last_name'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Birthdate</label>
                                            <input type="date" class="form-control" name="birthdate" value="<?= $profile['birthdate'] ?? '' ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Gender</label>
                                            <select class="form-select" name="sex">
                                                <option value="male" <?= ($profile['sex'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                                <option value="female" <?= ($profile['sex'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Civil Status</label>
                                            <select class="form-select" name="civil_status">
                                                <option value="Single" <?= ($profile['civil_status'] ?? '') == 'Single' ? 'selected' : '' ?>>Single</option>
                                                <option value="Married" <?= ($profile['civil_status'] ?? '') == 'Married' ? 'selected' : '' ?>>Married</option>
                                                <option value="Widowed" <?= ($profile['civil_status'] ?? '') == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                                <option value="Separated" <?= ($profile['civil_status'] ?? '') == 'Separated' ? 'selected' : '' ?>>Separated</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($profile['email'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Mobile Number</label>
                                            <input type="tel" class="form-control" name="contact_number" value="<?= htmlspecialchars($profile['contact_number'] ?? '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">House Number</label>
                                            <input type="text" class="form-control" name="house_number" value="<?= htmlspecialchars($profile['house_number'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Purok</label>
                                            <select class="form-select" name="purok" id="purokSelect">
                                                <option value="1" <?= ($profile['purok'] ?? '') == '1' ? 'selected' : '' ?>>1</option>
                                                <option value="2" <?= ($profile['purok'] ?? '') == '2' ? 'selected' : '' ?>>2</option>
                                                <option value="3" <?= ($profile['purok'] ?? '') == '3' ? 'selected' : '' ?>>3</option>
                                                <option value="4" <?= ($profile['purok'] ?? '') == '4' ? 'selected' : '' ?>>4</option>
                                                <option value="5" <?= ($profile['purok'] ?? '') == '5' ? 'selected' : '' ?>>5</option>
                                                <option value="6" <?= ($profile['purok'] ?? '') == '6' ? 'selected' : '' ?>>6</option>
                                                <option value="7" <?= ($profile['purok'] ?? '') == '7' ? 'selected' : '' ?>>7</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Occupation</label>
                                            <input type="text" class="form-control" name="occupation" value="<?= htmlspecialchars($profile['occupation'] ?? '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" name="address" id="addressInput" value="<?= htmlspecialchars($profile['address'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Educational Attainment</label>
                                            <input type="text" class="form-control" name="educational_attainment" value="<?= htmlspecialchars($profile['educational_attainment'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Religion</label>
                                            <input type="text" class="form-control" name="religion" value="<?= htmlspecialchars($profile['religion'] ?? '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <button type="reset" class="btn btn-outline-secondary me-2">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Upload Modal -->
    <div class="modal fade" id="photoUploadModal" tabindex="-1" aria-labelledby="photoUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoUploadModalLabel">Update Profile Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="photoUploadForm" method="POST" action="profile-backend.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="upload_photo">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="mb-3">
                            <label for="profile_photo" class="form-label">Select a photo</label>
                            <input class="form-control" type="file" id="profile_photo" name="profile_photo" accept="image/*" required>
                            <div class="form-text">Allowed formats: JPG, PNG, GIF. Maximum size: 5MB.</div>
                        </div>
                        
                        <div class="text-center">
                            <img id="imagePreview" src="#" alt="Preview" class="img-thumbnail mt-3" style="display: none; max-width: 200px;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload Photo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.wrapper').classList.toggle('sidebar-collapsed');
        });
        
        document.getElementById('profile_photo').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Clear preview when modal is closed
        document.getElementById('photoUploadModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('photoUploadForm').reset();
        });
        
        document.getElementById('purokSelect').addEventListener('change', function() {
            const houseNumber = document.querySelector('input[name="house_number"]').value;
            const purok = this.value;
            const barangay = "Barangay Balas";
            
            if (houseNumber) {
                document.getElementById('addressInput').value = `${houseNumber}, Purok ${purok}, ${barangay}`;
            } else {
                document.getElementById('addressInput').value = `Purok ${purok}, ${barangay}`;
            }
        });
        
        document.querySelector('input[name="house_number"]').addEventListener('change', function() {
            const houseNumber = this.value;
            const purok = document.getElementById('purokSelect').value;
            const barangay = "Barangay Balas";
            
            if (houseNumber) {
                document.getElementById('addressInput').value = `${houseNumber}, Purok ${purok}, ${barangay}`;
            } else {
                document.getElementById('addressInput').value = `Purok ${purok}, ${barangay}`;
            }
        });
    </script>
</body>
</html>