<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php';

// Get user profile data
$userId = $_SESSION['user_id'] ?? null;
$profile = [];

if ($userId) {
    $profileQuery = "SELECT * FROM residents WHERE id = ?";
    $stmt = $conn->prepare($profileQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc() ?? [];
    $stmt->close();
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
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-blue);
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
                <div class="card">
                    <div class="card-header">
                        <span>Personal Information</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <img src="<?= $profile['photo_path'] ?? 'https://via.placeholder.com/150' ?>" alt="Profile" class="profile-img mb-3">
                                <button class="btn btn-sm btn-outline-primary">Change Photo</button>
                            </div>
                            <div class="col-md-9">
                                <form>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Middle Name</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['middle_name'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['last_name'] ?? '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Birthdate</label>
                                            <input type="date" class="form-control" value="<?= $profile['date_of_birth'] ?? '' ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Gender</label>
                                            <select class="form-select">
                                                <option <?= ($profile['sex'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                                <option <?= ($profile['sex'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Civil Status</label>
                                            <select class="form-select">
                                                <option <?= ($profile['civil_status'] ?? '') == 'Single' ? 'selected' : '' ?>>Single</option>
                                                <option <?= ($profile['civil_status'] ?? '') == 'Married' ? 'selected' : '' ?>>Married</option>
                                                <option <?= ($profile['civil_status'] ?? '') == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                                <option <?= ($profile['civil_status'] ?? '') == 'Separated' ? 'selected' : '' ?>>Separated</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" value="<?= htmlspecialchars($profile['email'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Mobile Number</label>
                                            <input type="tel" class="form-control" value="<?= htmlspecialchars($profile['contact_number'] ?? '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-mb-3 mb-3">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($profile['address'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Purok</label>
                                            <select class="form-select">
                                                <option <?= ($profile['purok'] ?? '') == '1' ? 'selected' : '' ?>>1</option>
                                                <option <?= ($profile['purok'] ?? '') == '2' ? 'selected' : '' ?>>2</option>
                                                <option <?= ($profile['purok'] ?? '') == '3' ? 'selected' : '' ?>>3</option>
                                                <option <?= ($profile['purok'] ?? '') == '4' ? 'selected' : '' ?>>4</option>
                                                <option <?= ($profile['purok'] ?? '') == '5' ? 'selected' : '' ?>>5</option>
                                                <option <?= ($profile['purok'] ?? '') == '6' ? 'selected' : '' ?>>6</option>
                                                <option <?= ($profile['purok'] ?? '') == '7' ? 'selected' : '' ?>>7</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Occupation</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['occupation'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Educational Attainment</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($profile['educational_attainment'] ?? '') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-outline-secondary me-2">Cancel</button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.wrapper').classList.toggle('sidebar-collapsed');
        });
    </script>
</body>
</html>