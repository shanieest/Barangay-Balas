<?php
require_once '../../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get household data
    $household_id = $_POST['household_id'] ?? null;
    $house_number = $_POST['householdNo'] ?? '';
    $purok = $_POST['purok'] ?? '';
    $water_source = $_POST['waterSource'] ?? '';
    $toilet_facility = $_POST['toilet'] ?? '';
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Update or create household information
        if ($household_id) {
            // Update existing household
            $stmt = $conn->prepare("UPDATE households SET house_number = ?, purok = ?, 
                                  type_of_water_source = ?, type_of_toilet_facility = ? 
                                  WHERE id = ?");
            $stmt->bind_param("ssssi", $house_number, $purok, $water_source, $toilet_facility, $household_id);
        } else {
            // Create new household
            $stmt = $conn->prepare("INSERT INTO households (house_number, purok, 
                                  type_of_water_source, type_of_toilet_facility) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $house_number, $purok, $water_source, $toilet_facility);
        }
        
        $stmt->execute();
        
        if (!$household_id) {
            $household_id = $conn->insert_id;
        }
        
        // Process household members
        $resident_ids = $_POST['resident_id'] ?? [];
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
        
        // First, remove all current members from this household
        $stmt = $conn->prepare("UPDATE residents SET household_id = NULL WHERE household_id = ?");
        $stmt->bind_param("i", $household_id);
        $stmt->execute();
        
        // Process each member
        for ($i = 0; $i < count($names); $i++) {
            if (empty(trim($names[$i]))) continue;
            
            $name_parts = explode(' ', trim($names[$i]), 2);
            $first_name = $name_parts[0] ?? '';
            $last_name = $name_parts[1] ?? '';
            
            $resident_id = $resident_ids[$i] ?? null;
            
            if ($resident_id) {
                // Update existing resident
                $stmt = $conn->prepare("UPDATE residents SET 
                    first_name = ?, last_name = ?, relationship_to_head = ?, 
                    age = ?, sex = ?, civil_status = ?, occupation = ?, 
                    educational_attainment = ?, philhealth_number = ?, 
                    is_4ps_member = ?, is_indigent = ?, medical_history = ?,
                    household_id = ?, house_number = ?, purok = ?
                    WHERE id = ?");
                
                $has_philhealth = ($philhealths[$i] === 'Yes') ? $philhealths[$i] : NULL;
                $is_4ps = ($_4ps[$i] === 'Yes') ? 1 : 0;
                $is_indigent = ($indigents[$i] === 'Yes') ? 1 : 0;
                
                $stmt->bind_param("ssssissssiisssi", 
                    $first_name, $last_name, $relationships[$i], 
                    $ages[$i], $genders[$i], $civil_statuses[$i], 
                    $occupations[$i], $educations[$i], $has_philhealth, 
                    $is_4ps, $is_indigent, $medical_histories[$i],
                    $household_id, $house_number, $purok,
                    $resident_id
                );
            } else {
                // Insert new resident
                $stmt = $conn->prepare("INSERT INTO residents (
                    first_name, last_name, relationship_to_head, age, sex, 
                    civil_status, occupation, educational_attainment, 
                    philhealth_number, is_4ps_member, is_indigent, 
                    medical_history, household_id, house_number, purok
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $has_philhealth = ($philhealths[$i] === 'Yes') ? $philhealths[$i] : NULL;
                $is_4ps = ($_4ps[$i] === 'Yes') ? 1 : 0;
                $is_indigent = ($indigents[$i] === 'Yes') ? 1 : 0;
                
                $stmt->bind_param("sssisssssiisss", 
                    $first_name, $last_name, $relationships[$i], 
                    $ages[$i], $genders[$i], $civil_statuses[$i], 
                    $occupations[$i], $educations[$i], $has_philhealth, 
                    $is_4ps, $is_indigent, $medical_histories[$i],
                    $household_id, $house_number, $purok
                );
            }
            
            $stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Update session with new household_id
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
} else {
    header("Location: census.php");
    exit();
}
?>