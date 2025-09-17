<?php
require_once '../../auth/auth.php';
require_once __DIR__ . '/../../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$household_data = [];
$household_members = [];

$householdId = $_SESSION['household_id'] ?? ($_GET['household_id'] ?? null);

if ($householdId) {
    $stmt = $conn->prepare("
        SELECT id, house_number, purok, type_of_water_source, type_of_toilet_facility
        FROM households
        WHERE id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $householdId);
    $stmt->execute();
    $result = $stmt->get_result();
    $household_data = $result ? ($result->fetch_assoc() ?? []) : [];
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT 
            hm.id as member_id,
            r.id as resident_id,
            r.first_name, 
            r.last_name, 
            hm.relationship_to_head, 
            r.sex, 
            hm.civil_status,
            hm.occupation, 
            hm.educational_attainment, 
            hm.philhealth_number,
            hm.is_4ps_member, 
            hm.is_indigent, 
            hm.medical_history,
            hm.age
        FROM household_members hm
        JOIN residents r ON hm.resident_id = r.id
        WHERE hm.household_id = ?
        ORDER BY hm.relationship_to_head = 'Head' DESC, r.last_name ASC, r.first_name ASC
    ");
    $stmt->bind_param("i", $householdId);
    $stmt->execute();
    $res = $stmt->get_result();
    $household_members = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
}

// Get all verified residents for the dropdown
$all_residents = [];
$res_stmt = $conn->prepare("SELECT id, first_name, last_name FROM residents WHERE verification_status = 'Verified' ORDER BY last_name, first_name");
if ($res_stmt) {
    $res_stmt->execute();
    $result = $res_stmt->get_result();
    $all_residents = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $res_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Census Data - Barangay Balas Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Your existing CSS styles */
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
                <h2 class="mb-4">Census Data</h2>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Household information updated successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Error updating household information: <?= htmlspecialchars($_GET['message'] ?? 'Unknown error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Household Information</span>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateHouseholdModal">
                            Update Household
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Household Details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Household No:</strong> <?= htmlspecialchars($household_data['house_number'] ?? 'N/A'); ?></p>
                                                <p><strong>Purok:</strong> <?= htmlspecialchars($household_data['purok'] ?? 'N/A'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Household Amenities</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Water Source:</strong> <?= htmlspecialchars($household_data['type_of_water_source'] ?? 'N/A'); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Toilet Facility:</strong> <?= htmlspecialchars($household_data['type_of_toilet_facility'] ?? 'N/A'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3">Household Members</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Relationship</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Civil Status</th>
                                        <th>Occupation</th>
                                        <th>Education</th>
                                        <th>PhilHealth</th>
                                        <th>4Ps</th>
                                        <th>Indigent</th>
                                        <th>Medical History</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($household_members)): ?>
                                        <?php foreach ($household_members as $member): ?>
                                            <tr>
                                                <td><?= htmlspecialchars(trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''))); ?></td>
                                                <td><?= htmlspecialchars($member['relationship_to_head'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($member['age'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars(ucfirst($member['sex'] ?? '')); ?></td>
                                                <td><?= htmlspecialchars($member['civil_status'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($member['occupation'] ?? ''); ?></td>
                                                <td><?= htmlspecialchars($member['educational_attainment'] ?? ''); ?></td>
                                                <td><?= (!empty($member['philhealth_number'])) ? 'Yes' : 'No'; ?></td>
                                                <td><?= !empty($member['is_4ps_member']) ? 'Yes' : 'No'; ?></td>
                                                <td><?= !empty($member['is_indigent']) ? 'Yes' : 'No'; ?></td>
                                                <td><?= htmlspecialchars($member['medical_history'] ?? ''); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="11" class="text-center">No household members found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "../../pages/modals/censusModal.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.wrapper').classList.toggle('sidebar-collapsed');
        });
    </script>
</body>
</html>