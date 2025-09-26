<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_relationship') {
        $member_id = intval($_POST['member_id']);
        $relationship = mysqli_real_escape_string($conn, $_POST['relationship']);
        
        $update_query = "UPDATE residents SET relationship_to_head = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $relationship, $member_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'batch_update_relationships') {
        $updates = json_decode($_POST['updates'], true);
        $success = true;
        
        mysqli_begin_transaction($conn);
        
        try {
            foreach ($updates as $update) {
                $member_id = intval($update['member_id']);
                $relationship = mysqli_real_escape_string($conn, $update['relationship']);
                
                $update_query = "UPDATE residents SET relationship_to_head = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, "si", $relationship, $member_id);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_error($conn));
                }
            }
            
            mysqli_commit($conn);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}

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
            WHEN relationship_to_head = 'Head of Household' OR relationship_to_head IS NULL THEN 0
            WHEN relationship_to_head = 'Spouse' THEN 1
            ELSE 2
        END,
        age DESC
";
$stmt = mysqli_prepare($conn, $family_query);
mysqli_stmt_bind_param($stmt, "ss", $current_resident['house_number'], $current_resident['purok']);
mysqli_stmt_execute($stmt);
$family_members = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Determine head of household if not set
$head_found = false;
foreach ($family_members as $member) {
    if ($member['relationship_to_head'] === 'Head of Household') {
        $head_found = true;
        break;
    }
}

// If no head found, set the oldest married person or oldest person as head
if (!$head_found && !empty($family_members)) {
    $head_candidate = $family_members[0];
    foreach ($family_members as $member) {
        if (strtolower($member['civil_status']) === 'married' && $member['age'] >= $head_candidate['age']) {
            $head_candidate = $member;
            break;
        }
    }
    
    // Update the head
    $update_head_query = "UPDATE residents SET relationship_to_head = 'Head of Household' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_head_query);
    mysqli_stmt_bind_param($stmt, "i", $head_candidate['id']);
    mysqli_stmt_execute($stmt);
    
    // Refresh data
    $stmt = mysqli_prepare($conn, $family_query);
    mysqli_stmt_bind_param($stmt, "ss", $current_resident['house_number'], $current_resident['purok']);
    mysqli_stmt_execute($stmt);
    $family_members = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}

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

// Relationship options
$relationship_options = [
    'Head of Household',
    'Spouse',
    'Son',
    'Daughter', 
    'Father',
    'Mother',
    'Brother',
    'Sister',
    'Grandfather',
    'Grandmother',
    'Grandson',
    'Granddaughter',
    'Uncle',
    'Aunt',
    'Nephew',
    'Niece',
    'Cousin',
    'Son-in-law',
    'Daughter-in-law',
    'Father-in-law',
    'Mother-in-law',
    'Other'
];
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

        /* Relationship Management Styles */
        .relationship-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .relationship-card:hover {
            border-color: var(--primary-blue);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .relationship-card.is-head {
            border-color: #198754;
            background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
        }
        
        .member-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-blue), var(--secondary-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 15px;
        }
        
        .head-avatar {
            background: linear-gradient(45deg, #198754, #20c997);
        }
        
        .relationship-select {
            min-width: 180px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 12px;
            transition: all 0.3s;
        }

        .relationship-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 51, 204, 0.25);
        }
        
        .save-btn {
            background: linear-gradient(45deg, var(--primary-blue), var(--secondary-blue));
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            color: white;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 51, 204, 0.3);
            color: white;
        }
        
        .head-indicator {
            background: linear-gradient(45deg, #198754, #20c997);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .relationship-management-section {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8efff 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 2px solid var(--primary-blue);
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
            
            .relationship-card .row {
                flex-direction: column;
                text-align: center;
            }
            
            .member-avatar {
                margin: 0 auto 15px;
            }
            
            .relationship-select {
                margin: 10px 0;
                width: 100%;
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
                    <h5>Processing...</h5>
                    <p class="text-muted">Please wait</p>
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

                <!-- Relationship Management Section -->
                <div class="relationship-management-section">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-users-cog text-primary me-3" style="font-size: 2rem;"></i>
                        <div>
                            <h3 class="mb-1">Manage Household Relationships</h3>
                            <p class="text-muted mb-0">Set family relationships for accurate census records</p>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong> Make sure to designate one person as the "Head of Household" and set appropriate relationships for all family members.
                    </div>

                    <!-- Household Members with Relationship Management -->
                    <div id="householdMembers">
                        <?php foreach ($family_members as $member): 
                            $is_head = ($member['relationship_to_head'] === 'Head of Household');
                            $initials = '';
                            $names = explode(' ', $member['full_name']);
                            foreach ($names as $name) {
                                if (!empty($name)) {
                                    $initials .= strtoupper(substr($name, 0, 1));
                                }
                            }
                            $initials = substr($initials, 0, 2);
                        ?>
                        <div class="relationship-card <?php echo $is_head ? 'is-head' : ''; ?> p-4">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="member-avatar <?php echo $is_head ? 'head-avatar' : ''; ?>"><?php echo $initials; ?></div>
                                </div>
                                <div class="col">
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="mb-0 me-2"><?php echo htmlspecialchars($member['full_name']); ?></h5>
                                        <?php if ($is_head): ?>
                                            <span class="head-indicator">HEAD OF HOUSEHOLD</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-muted mb-0"><?php echo ucfirst($member['sex']); ?>, <?php echo $member['age']; ?> years old</p>
                                </div>
                                <div class="col-auto">
                                    <select class="form-select relationship-select" data-member-id="<?php echo $member['id']; ?>" <?php echo $is_head ? 'disabled' : ''; ?>>
                                        <?php if ($is_head): ?>
                                            <option selected>Head of Household</option>
                                        <?php else: ?>
                                            <option value="">Select Relationship</option>
                                            <?php foreach ($relationship_options as $option): ?>
                                                <?php if ($option !== 'Head of Household'): ?>
                                                    <option value="<?php echo $option; ?>" <?php echo ($member['relationship_to_head'] === $option) ? 'selected' : ''; ?>>
                                                        <?php echo $option; ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <?php if ($is_head): ?>
                                        <button class="btn btn-success btn-sm" disabled>
                                            <i class="fas fa-check me-1"></i>Head
                                        </button>
                                    <?php else: ?>
                                        <button class="btn save-btn btn-sm" onclick="saveRelationship(<?php echo $member['id']; ?>, this)">
                                            <i class="fas fa-save me-1"></i>Save
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <button class="btn btn-primary btn-lg me-3" onclick="saveAllRelationships()">
                            <i class="fas fa-save me-2"></i>Save All Changes
                        </button>
                        <button class="btn btn-outline-secondary btn-lg" onclick="resetRelationships()">
                            <i class="fas fa-undo me-2"></i>Reset Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function saveRelationship(memberId, button) {
            const select = document.querySelector(`select[data-member-id="${memberId}"]`);
            const relationship = select.value;
            
            if (!relationship) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select a relationship first.'
                });
                return;
            }
            
            // Show loading state
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
            button.disabled = true;
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('action', 'update_relationship');
            formData.append('member_id', memberId);
            formData.append('relationship', relationship);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.innerHTML = '<i class="fas fa-check me-1"></i>Saved';
                    button.className = 'btn btn-success btn-sm';
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: 'Relationship updated successfully.',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.className = 'btn save-btn btn-sm';
                        button.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(data.error || 'Failed to save');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error saving relationship: ' + error.message
                });
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        function saveAllRelationships() {
            const selects = document.querySelectorAll('.relationship-select:not([disabled])');
            const updates = [];
            
            selects.forEach(select => {
                if (select.value) {
                    updates.push({
                        member_id: select.getAttribute('data-member-id'),
                        relationship: select.value
                    });
                }
            });
            
            if (updates.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Changes',
                    text: 'No changes to save.'
                });
                return;
            }
            
            // Show loading overlay
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Send batch update
            const formData = new FormData();
            formData.append('action', 'batch_update_relationships');
            formData.append('updates', JSON.stringify(updates));
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingOverlay').style.display = 'none';
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'All relationships saved successfully!',
                        showConfirmButton: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.error || 'Failed to save');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loadingOverlay').style.display = 'none';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error saving relationships: ' + error.message
                });
            });
        }
        
        function resetRelationships() {
            Swal.fire({
                title: 'Reset Changes?',
                text: 'Are you sure you want to reset all changes?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, reset!'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        }
        
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
        
        // Auto-suggest relationships based on age and gender
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('.relationship-select:not([disabled])');
            
            selects.forEach(select => {
                select.addEventListener('change', function() {
                    const card = this.closest('.relationship-card');
                    const saveBtn = card.querySelector('.save-btn');
                    
                    if (this.value) {
                        saveBtn.style.display = 'inline-block';
                        // Highlight the card when relationship is selected
                        card.style.borderColor = '#0d6efd';
                        card.style.boxShadow = '0 4px 12px rgba(13, 110, 253, 0.15)';
                    } else {
                        card.style.borderColor = '#e9ecef';
                        card.style.boxShadow = '0 8px 25px rgba(0,0,0,0.08)';
                    }
                });
            });

            // Auto-suggest relationships based on member info
            autoSuggestRelationships();
        });

        function autoSuggestRelationships() {
            const memberCards = document.querySelectorAll('.relationship-card:not(.is-head)');
            const headCard = document.querySelector('.relationship-card.is-head');
            
            if (!headCard) return;
            
            const headInfo = getCardInfo(headCard);
            
            memberCards.forEach(card => {
                const memberInfo = getCardInfo(card);
                const select = card.querySelector('.relationship-select');
                
                if (select.value === '') {
                    const suggestedRelationship = getSuggestedRelationship(headInfo, memberInfo);
                    if (suggestedRelationship) {
                        select.value = suggestedRelationship;
                        select.style.backgroundColor = '#fff3cd';
                        select.style.borderColor = '#ffc107';
                        
                        // Add tooltip or indicator for suggested relationship
                        select.title = 'Suggested based on age and gender';
                    }
                }
            });
        }

        function getCardInfo(card) {
            const text = card.querySelector('.text-muted').textContent;
            const name = card.querySelector('h5').textContent;
            
            const genderMatch = text.match(/(Male|Female)/i);
            const ageMatch = text.match(/(\d+) years old/);
            
            return {
                name: name,
                gender: genderMatch ? genderMatch[1].toLowerCase() : 'unknown',
                age: ageMatch ? parseInt(ageMatch[1]) : 0
            };
        }

        function getSuggestedRelationship(headInfo, memberInfo) {
            const ageDiff = headInfo.age - memberInfo.age;
            
            // Spouse (similar age, different gender, both adults)
            if (Math.abs(ageDiff) <= 10 && headInfo.gender !== memberInfo.gender && 
                memberInfo.age >= 18 && headInfo.age >= 18) {
                return 'Spouse';
            }
            
            // Children (significant age difference, head is older)
            if (ageDiff >= 15 && memberInfo.age < 40) {
                return memberInfo.gender === 'male' ? 'Son' : 'Daughter';
            }
            
            // Parents (head is younger)
            if (ageDiff <= -15 && memberInfo.age >= 40) {
                return memberInfo.gender === 'male' ? 'Father' : 'Mother';
            }
            
            // Siblings (similar age)
            if (Math.abs(ageDiff) <= 15 && memberInfo.age >= 10) {
                return memberInfo.gender === 'male' ? 'Brother' : 'Sister';
            }
            
            return null;
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

        // Validation functions
        function validateRelationships() {
            const headCards = document.querySelectorAll('.relationship-card.is-head');
            const memberCards = document.querySelectorAll('.relationship-card:not(.is-head)');
            
            if (headCards.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'No Head of Household',
                    text: 'Please designate someone as the head of household.'
                });
                return false;
            }
            
            if (headCards.length > 1) {
                Swal.fire({
                    icon: 'error',
                    title: 'Multiple Heads',
                    text: 'Only one person can be designated as head of household.'
                });
                return false;
            }
            
            let unsetRelationships = 0;
            memberCards.forEach(card => {
                const select = card.querySelector('.relationship-select');
                if (!select.value) {
                    unsetRelationships++;
                }
            });
            
            if (unsetRelationships > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Relationships',
                    text: `${unsetRelationships} member(s) still need their relationship set.`,
                    showConfirmButton: true
                });
                return false;
            }
            
            return true;
        }

        // Enhanced save all with validation
        function saveAllRelationshipsWithValidation() {
            if (!validateRelationships()) {
                return;
            }
            
            saveAllRelationships();
        }
    </script>
</body>
</html>