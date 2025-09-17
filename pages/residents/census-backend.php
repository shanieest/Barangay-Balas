<?php
// Enhanced census-backend.php with better duplicate prevention
require_once '../../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $household_id = $_POST['household_id'] ?? null;
    $house_number = $_POST['householdNo'] ?? '';
    $purok = $_POST['purok'] ?? '';
    $water_source = $_POST['waterSource'] ?? '';
    $toilet_facility = $_POST['toilet'] ?? '';
    $current_user_id = $_SESSION['user_id'] ?? null;
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // 1. ENHANCED HOUSEHOLD DUPLICATE CHECK
        $existing_household = null;
        if ($household_id) {
            // Use existing household ID
            $existing_household = ['id' => $household_id];
        } else {
            // Check for existing household by house_number AND purok combination
            $check_stmt = $conn->prepare("SELECT id FROM households WHERE house_number = ? AND purok = ? LIMIT 1");
            $check_stmt->bind_param("ss", $house_number, $purok);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $existing_household = $result->fetch_assoc();
                $household_id = $existing_household['id'];
            }
            $check_stmt->close();
        }
        
        // Update or create household information
        if ($household_id) {
            $stmt = $conn->prepare("UPDATE households SET house_number = ?, purok = ?, 
                                  type_of_water_source = ?, type_of_toilet_facility = ? 
                                  WHERE id = ?");
            $stmt->bind_param("ssssi", $house_number, $purok, $water_source, $toilet_facility, $household_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO households (house_number, purok, 
                                  type_of_water_source, type_of_toilet_facility) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $house_number, $purok, $water_source, $toilet_facility);
        }
        
        $stmt->execute();
        $stmt->close();
        
        if (!$household_id) {
            $household_id = $conn->insert_id;
        }
        
        // 2. ENHANCED MEMBER PROCESSING WITH COMPREHENSIVE DUPLICATE PREVENTION
        $member_ids = $_POST['member_id'] ?? [];
        $existing_members = $_POST['existing_member'] ?? [];
        $names = $_POST['name'] ?? [];
        $relationships = $_POST['relationship'] ?? [];
        $ages = $_POST['age'] ?? [];
        $genders = $_POST['gender'] ?? [];
        $civil_statuses = $_POST['civil_status'] ?? [];
        $occupations = $_POST['occupation'] ?? [];
        $educations = $_POST['education'] ?? [];
        $philhealths = $_POST['philhealth'] ?? [];
        $_4ps = $_POST['4ps'] ?? [];
        $indigents = $_POST['indigent'] ?? [];
        $medical_histories = $_POST['medical_history'] ?? [];
        
        // Track processed residents to avoid duplicates in this submission
        $processed_residents = [];
        
        // Process each member with enhanced duplicate detection
        for ($i = 0; $i < count($names); $i++) {
            if (empty(trim($names[$i]))) continue;
            
            $resident_id = null;
            
            // Priority 1: Use existing member ID if provided
            if (!empty($existing_members[$i])) {
                $resident_id = intval($existing_members[$i]);
            }
            // Priority 2: Use member_id if provided (for updates)
            elseif (!empty($member_ids[$i])) {
                $resident_id = intval($member_ids[$i]);
            }
            // Priority 3: ENHANCED SEARCH for existing resident
            else {
                $resident_id = findExistingResident($names[$i], $house_number, $purok, $conn);
            }
            
            // Skip if this resident was already processed in this submission
            if ($resident_id && in_array($resident_id, $processed_residents)) {
                continue;
            }
            
            // If no existing resident found, create a new one
            if (!$resident_id) {
                $resident_id = createNewResident($names[$i], $ages[$i], $genders[$i], 
                    $civil_statuses[$i], $house_number, $purok, $conn);
            }
            
            // 3. ENHANCED HOUSEHOLD MEMBER DUPLICATE CHECK
            if (!isResidentAlreadyInHousehold($household_id, $resident_id, $conn)) {
                // Add new household member
                addHouseholdMember($household_id, $resident_id, $relationships[$i], $ages[$i], 
                    $civil_statuses[$i], $educations[$i], $occupations[$i], $philhealths[$i], 
                    $_4ps[$i], $indigents[$i], $medical_histories[$i], $current_user_id, $conn);
            } else {
                // Update existing household member
                updateHouseholdMember($household_id, $resident_id, $relationships[$i], $ages[$i], 
                    $civil_statuses[$i], $educations[$i], $occupations[$i], $philhealths[$i], 
                    $_4ps[$i], $indigents[$i], $medical_histories[$i], $current_user_id, $conn);
            }
            
            // Mark this resident as processed
            $processed_residents[] = $resident_id;
        }
        
        // Log census activity
        logCensusActivity($current_user_id, $house_number, $purok, $conn);
        
        // Commit transaction
        $conn->commit();
        
        // Update session with household_id
        $_SESSION['household_id'] = $household_id;
        
        // Redirect with success message
        header("Location: census.php?success=1&household_id=" . $household_id);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Redirect with error message
        header("Location: census.php?error=1&message=" . urlencode($e->getMessage()));
        exit();
    }
}

// ENHANCED HELPER FUNCTIONS

function findExistingResident($full_name, $house_number, $purok, $conn) {
    $name_parts = explode(' ', trim($full_name), 2);
    $first_name = $name_parts[0] ?? '';
    $last_name = $name_parts[1] ?? '';
    
    // Search with multiple criteria for better matching
    $search_stmt = $conn->prepare("
        SELECT id FROM residents 
        WHERE (first_name = ? AND last_name = ?) 
           OR (house_number = ? AND purok = ? AND first_name = ?)
           OR (CONCAT(first_name, ' ', last_name) = ?)
        ORDER BY 
            CASE 
                WHEN first_name = ? AND last_name = ? THEN 1
                WHEN house_number = ? AND purok = ? THEN 2
                ELSE 3
            END
        LIMIT 1
    ");
    
    $search_stmt->bind_param("ssssssssss", 
        $first_name, $last_name, $house_number, $purok, $first_name, 
        $full_name, $first_name, $last_name, $house_number, $purok
    );
    
    $search_stmt->execute();
    $result = $search_stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $resident_data = $result->fetch_assoc();
        $search_stmt->close();
        return $resident_data['id'];
    }
    
    $search_stmt->close();
    return null;
}

function createNewResident($full_name, $age, $gender, $civil_status, $house_number, $purok, $conn) {
    $name_parts = explode(' ', trim($full_name), 2);
    $first_name = $name_parts[0] ?? '';
    $last_name = $name_parts[1] ?? '';
    
    $birth_year = date('Y') - intval($age);
    $birthdate = $birth_year . '-01-01';
    $address = "House $house_number, Purok $purok, Balas, Mexico, Pampanga, Philippines";
    
    $create_stmt = $conn->prepare("INSERT INTO residents 
        (first_name, last_name, sex, birthdate, age, civil_status, house_number, purok, 
         address, verification_status, contact_number, email) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Unverified', '0', '0')");
    
    $create_stmt->bind_param("ssssissss", 
        $first_name, $last_name, $gender, $birthdate, $age, 
        $civil_status, $house_number, $purok, $address
    );
    
    $create_stmt->execute();
    $resident_id = $conn->insert_id;
    $create_stmt->close();
    
    return $resident_id;
}

function isResidentAlreadyInHousehold($household_id, $resident_id, $conn) {
    $check_stmt = $conn->prepare("SELECT id FROM household_members WHERE household_id = ? AND resident_id = ? LIMIT 1");
    $check_stmt->bind_param("ii", $household_id, $resident_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $exists = $result && $result->num_rows > 0;
    $check_stmt->close();
    
    return $exists;
}

function addHouseholdMember($household_id, $resident_id, $relationship, $age, $civil_status, 
                           $education, $occupation, $philhealth, $_4ps, $indigent, $medical_history, 
                           $current_user_id, $conn) {
    
    $member_stmt = $conn->prepare("INSERT INTO household_members 
        (household_id, resident_id, relationship_to_head, age, civil_status, 
         educational_attainment, occupation, philhealth_number, is_4ps_member, 
         is_indigent, medical_history, is_primary_member, added_by_resident_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $has_philhealth = ($philhealth === 'Yes') ? $philhealth : NULL;
    $is_4ps = ($_4ps === 'Yes') ? 1 : 0;
    $is_indigent_val = ($indigent === 'Yes') ? 1 : 0;
    $is_primary = ($relationship === 'Head') ? 1 : 0;
    
    $member_stmt->bind_param("iisisssssissi", 
        $household_id, $resident_id, $relationship, $age, 
        $civil_status, $education, $occupation, 
        $has_philhealth, $is_4ps, $is_indigent_val, $medical_history, 
        $is_primary, $current_user_id
    );
    
    $member_stmt->execute();
    $member_stmt->close();
}

function updateHouseholdMember($household_id, $resident_id, $relationship, $age, $civil_status, 
                              $education, $occupation, $philhealth, $_4ps, $indigent, $medical_history, 
                              $current_user_id, $conn) {
    
    $update_stmt = $conn->prepare("UPDATE household_members SET 
        relationship_to_head = ?, age = ?, civil_status = ?, 
        educational_attainment = ?, occupation = ?, philhealth_number = ?, 
        is_4ps_member = ?, is_indigent = ?, medical_history = ?, is_primary_member = ?,
        added_by_resident_id = ?, date_added = CURRENT_TIMESTAMP
        WHERE household_id = ? AND resident_id = ?");
    
    $has_philhealth = ($philhealth === 'Yes') ? $philhealth : NULL;
    $is_4ps = ($_4ps === 'Yes') ? 1 : 0;
    $is_indigent_val = ($indigent === 'Yes') ? 1 : 0;
    $is_primary = ($relationship === 'Head') ? 1 : 0;
    
    $update_stmt->bind_param("sisssssissiii", 
        $relationship, $age, $civil_status, $education, 
        $occupation, $has_philhealth, $is_4ps, $is_indigent_val, 
        $medical_history, $is_primary, $current_user_id, $household_id, $resident_id
    );
    
    $update_stmt->execute();
    $update_stmt->close();
}

function logCensusActivity($user_id, $house_number, $purok, $conn) {
    $activity_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $activity = "Updated census data for household $house_number in Purok $purok";
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $activity_stmt->bind_param("isss", $user_id, $activity, $ip, $agent);
    $activity_stmt->execute();
    $activity_stmt->close();
}

// AJAX endpoint for resident search
if (isset($_GET['action']) && $_GET['action'] === 'search_residents') {
    $query = $_GET['q'] ?? '';
    $results = [];
    
    if (strlen($query) >= 2) {
        $search_stmt = $conn->prepare("
            SELECT id, first_name, last_name, house_number, purok, verification_status
            FROM residents 
            WHERE verification_status = 'Verified' 
              AND (CONCAT(first_name, ' ', last_name) LIKE ? 
                   OR first_name LIKE ? 
                   OR last_name LIKE ?)
            ORDER BY last_name, first_name
            LIMIT 10
        ");
        
        $search_query = "%$query%";
        $search_stmt->bind_param("sss", $search_query, $search_query, $search_query);
        $search_stmt->execute();
        $result = $search_stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $results[] = [
                'id' => $row['id'],
                'text' => $row['first_name'] . ' ' . $row['last_name'],
                'address' => 'House ' . $row['house_number'] . ', Purok ' . $row['purok']
            ];
        }
        
        $search_stmt->close();
    }
    
    header('Content-Type: application/json');
    echo json_encode($results);
    exit();
}
?>