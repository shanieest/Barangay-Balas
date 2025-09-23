<?php
// public/census.php - Residents' Household Census Page
require_once '../../auth/auth.php';
require_once '../../config/db.php';


// Get resident's household data
$resident_id = $_SESSION['user_id'];
$household_query = "
    SELECT 
        r.*,
        CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) as full_name,
        house_number,
        purok,
        address
    FROM residents r
    WHERE id = ?
";
$stmt = mysqli_prepare($conn, $household_query);
mysqli_stmt_bind_param($stmt, "i", $resident_id);
mysqli_stmt_execute($stmt);
$current_resident = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get all household members
$family_query = "
    SELECT 
        r.*,
        CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) as full_name
    FROM residents r
    WHERE house_number = ? 
    AND purok = ? 
    AND resident_status = 'Active'
    ORDER BY 
        CASE 
            WHEN LOWER(civil_status) = 'married' AND age = (
                SELECT MIN(age) FROM residents r2 
                WHERE r2.house_number = r.house_number 
                AND r2.purok = r.purok 
                AND LOWER(r2.civil_status) = 'married'
                AND r2.resident_status = 'Active'
            ) THEN 0
            ELSE age
        END,
        age
";
$stmt = mysqli_prepare($conn, $family_query);
mysqli_stmt_bind_param($stmt, "ss", $current_resident['house_number'], $current_resident['purok']);
mysqli_stmt_execute($stmt);
$family_members = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Calculate statistics
$total_members = count($family_members);
$adults = 0;
$children = 0;
$working_members = 0;

foreach ($family_members as $member) {
    if ($member['age'] >= 18) {
        $adults++;
        if (!empty($member['occupation']) && strtolower($member['occupation']) !== 'student' && strtolower($member['occupation']) !== 'n/a') {
            $working_members++;
        }
    } else {
        $children++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Household - Barangay Balas Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0033cc;
            --secondary-blue: #3a7cb9;
            --accent-red: #e63946;
            --accent-yellow: #ffbe0b;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
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
            width: calc(100% - 250px);
        }
        
        .top-navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header-card {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            border-radius: 20px;
            color: white;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0,51,204,0.2);
        }

        .export-section {
            background: linear-gradient(135deg, var(--accent-yellow) 0%, #ffd60a 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            color: var(--dark-gray);
        }

        .export-btn {
            background: rgba(0,0,0,0.1);
            border: 2px solid rgba(0,0,0,0.2);
            color: var(--dark-gray);
            padding: 12px 30px;
            border-radius: 25px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .export-btn:hover {
            background: rgba(0,0,0,0.2);
            border-color: rgba(0,0,0,0.3);
            color: var(--dark-gray);
            transform: translateY(-2px);
        }

        .member-card {
            background: white;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s;
            border: none;
        }

        .member-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .member-header {
            padding: 20px;
            border-bottom: 2px solid #f8f9fa;
            position: relative;
        }

        .head-member {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
        }

        .head-member::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-yellow);
        }

        .member-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--accent-yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--dark-gray);
            margin-right: 15px;
        }

        .head-avatar {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .relationship-badge {
            background: var(--accent-yellow);
            color: var(--dark-gray);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .head-badge {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .member-details {
            padding: 20px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
        }

        .detail-label i {
            margin-right: 8px;
            color: var(--primary-blue);
            width: 16px;
        }

        .detail-value {
            color: #6c757d;
            font-weight: 500;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-blue);
        }

        .stat-label {
            color: #6c757d;
            margin-top: 5px;
        }

        .amenities-card {
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .amenity-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .amenity-item:last-child {
            margin-bottom: 0;
        }

        .amenity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #28a745;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
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
                width: calc(100% - 80px);
            }
            
            .main-container {
                padding: 10px;
            }
            
            .header-card {
                padding: 20px;
            }
            
            .member-header {
                flex-direction: column;
                text-align: center;
            }
            
            .member-avatar {
                margin: 0 auto 15px;
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
            
            <!-- Loading Overlay -->
            <div class="loading-overlay" id="loadingOverlay">
                <div class="text-center">
                    <div class="loading-spinner mb-3"></div>
                    <h5>Generating Your Household Report...</h5>
                    <p class="text-muted">Please wait while we prepare your Excel file</p>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="main-container">
                <!-- Header -->
                <div class="header-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="display-6 fw-bold mb-2">
                                <i class="fas fa-home me-3"></i>My Household
                            </h1>
                            <p class="mb-0 opacity-75 fs-5">House #<?php echo htmlspecialchars($current_resident['house_number']); ?>, Purok <?php echo htmlspecialchars($current_resident['purok']); ?>, Barangay Balas</p>
                            <small class="opacity-75">Household ID: HH-<?php echo substr($current_resident['purok'], -1); ?>-<?php echo str_pad($current_resident['house_number'], 4, '0', STR_PAD_LEFT); ?></small>
                        </div>
                        <div class="col-md-4 text-end mt-3 mt-md-0">
                            <div class="d-flex flex-column align-items-end">
                                <div class="badge bg-light text-dark fs-6 mb-2"><?php echo $total_members; ?> Family Member<?php echo $total_members > 1 ? 's' : ''; ?></div>
                                <small class="opacity-75">Last Updated: <?php echo date('F d, Y'); ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Section -->
                <div class="export-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-2">
                                <i class="fas fa-file-excel me-2"></i>Download Your Household Report
                            </h4>
                            <p class="mb-0">Get a complete Excel report of your family's census information for official use</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn export-btn" onclick="exportHouseholdData()">
                                <i class="fas fa-download me-2"></i>Export My Data
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Household Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_members; ?></div>
                        <div class="stat-label">Family Members</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $adults; ?></div>
                        <div class="stat-label">Adults</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $children; ?></div>
                        <div class="stat-label">Children</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $working_members; ?></div>
                        <div class="stat-label">Working Members</div>
                    </div>
                </div>

                <!-- Family Members -->
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-4">
                            <i class="fas fa-users me-2"></i>Family Members
                        </h4>
                    </div>
                </div>

                <?php foreach ($family_members as $index => $member): 
                    // Determine relationship
                    $relationship = 'MEMBER';
                    if ($index === 0) {
                        $relationship = 'HEAD';
                    } elseif ($index === 1 && strtolower($member['civil_status']) === 'married') {
                        $relationship = 'SPOUSE';
                    } elseif ($index > 1 || ($index === 1 && strtolower($member['civil_status']) !== 'married')) {
                        $relationship = (strtolower($member['sex']) === 'male') ? 'SON' : 'DAUGHTER';
                    }
                    
                    $isHead = ($relationship === 'HEAD');
                    $initials = '';
                    $names = explode(' ', $member['full_name']);
                    foreach ($names as $name) {
                        if (!empty($name)) {
                            $initials .= strtoupper(substr($name, 0, 1));
                        }
                    }
                    $initials = substr($initials, 0, 2);
                ?>

                <!-- Family Member Card -->
                <div class="member-card">
                    <div class="member-header <?php echo $isHead ? 'head-member' : ''; ?>">
                        <div class="d-flex align-items-center">
                            <div class="member-avatar <?php echo $isHead ? 'head-avatar' : ''; ?>"><?php echo $initials; ?></div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?php echo htmlspecialchars($member['full_name']); ?></h5>
                                <p class="mb-0 <?php echo $isHead ? 'opacity-75' : 'text-muted'; ?>"><?php echo $isHead ? 'Head of Family' : ucwords(strtolower($relationship)); ?></p>
                            </div>
                            <span class="relationship-badge <?php echo $isHead ? 'head-badge' : ''; ?>"><?php echo $relationship; ?></span>
                        </div>
                    </div>
                    <div class="member-details">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        <i class="fas fa-birthday-cake"></i>Age
                                    </span>
                                    <span class="detail-value"><?php echo $member['age']; ?> years old</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">
                                        <i class="fas fa-<?php echo $member['sex'] === 'male' ? 'mars' : 'venus'; ?>"></i>Gender
                                    </span>
                                    <span class="detail-value"><?php echo ucfirst($member['sex']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">
                                        <i class="fas fa-heart"></i>Civil Status
                                    </span>
                                    <span class="detail-value"><?php echo ucfirst($member['civil_status']) ?: 'Single'; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">
                                        <i class="fas fa-briefcase"></i>Occupation
                                    </span>
                                    <span class="detail-value"><?php echo $member['occupation'] ?: 'N/A'; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <span class="detail-label">
                                        <i class="fas fa-graduation-cap"></i>Education
                                    </span>
                                    <span class="detail-value"><?php echo $member['educational_attainment'] ?: 'N/A'; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">
                                        <i class="fas fa-phone"></i>Contact
                                    </span>
                                    <span class="detail-value"><?php echo $member['contact_number'] ?: 'N/A'; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">
                                        <i class="fas fa-envelope"></i>Email
                                    </span>
                                    <span class="detail-value"><?php echo $member['email'] ?: 'N/A'; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">
                                        <i class="fas fa-id-card"></i>PhilHealth
                                    </span>
                                    <span class="detail-value <?php echo !empty($member['philhealth_number']) ? 'text-success' : 'text-warning'; ?>">
                                        <?php echo !empty($member['philhealth_number']) ? 'Member' : ($member['age'] < 18 ? 'Dependent' : 'Not Registered'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/census-resident.js"></script>

    <script>
        function exportHouseholdData() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.style.display = 'flex';

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'census-backend.php';
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.name = 'action';
            actionInput.value = 'export_household';
            
            form.appendChild(actionInput);
            document.body.appendChild(form);
            
            form.submit();
            
            setTimeout(() => {
                overlay.style.display = 'none';
                document.body.removeChild(form);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Export Successful!',
                    text: 'Your household report has been downloaded successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 2000);
        }
        
        // Mobile sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            function toggleSidebar() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                }
            }
            
            const sidebarItems = document.querySelectorAll('.sidebar-menu li');
            sidebarItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        toggleSidebar();
                    }
                });
            });
        });
    </script>
</body>
</html>