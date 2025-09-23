<?php
// Enhanced census-backend.php with improved relationship detection
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once '../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

requireAuth();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'get_households':
        getHouseholdsEnhanced();
        break;
    case 'export_excel':
        exportToExcelEnhanced();
        break;
    case 'get_statistics':
        getStatisticsEnhanced();
        break;
    // Removed edit capabilities - census is read-only for admins
    // case 'update_relationship':
    //     updateRelationship();
    //     break;
    // case 'auto_detect_relationships':
    //     autoDetectAllRelationships();
    //     break;
    case 'get_household_details':
        getHouseholdDetails();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function getHouseholdsEnhanced() {
    global $conn;
    
    $purok = $_GET['purok'] ?? '';
    $search = $_GET['search'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 25);
    $offset = ($page - 1) * $limit;
    
    $whereConditions = ["resident_status = 'Active'"];
    
    if (!empty($purok)) {
        $whereConditions[] = "purok = '" . mysqli_real_escape_string($conn, $purok) . "'";
    }
    
    if (!empty($search)) {
        $search_term = mysqli_real_escape_string($conn, $search);
        $whereConditions[] = "(CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) LIKE '%$search_term%' 
                             OR house_number LIKE '%$search_term%' 
                             OR address LIKE '%$search_term%'
                             OR occupation LIKE '%$search_term%'
                             OR religion LIKE '%$search_term%')";
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Enhanced household query with better relationship handling
    $household_query = "
        SELECT 
            house_number,
            purok,
            COUNT(*) as member_count,
            MIN(id) as household_id,
            GROUP_CONCAT(
                CONCAT(
                    id, '|',
                    first_name, '|', 
                    COALESCE(middle_name, ''), '|', 
                    last_name, '|',
                    COALESCE(suffix, ''), '|',
                    age, '|', 
                    sex, '|', 
                    civil_status, '|', 
                    COALESCE(educational_attainment, 'N/A'), '|',
                    COALESCE(religion, 'N/A'), '|',
                    COALESCE(occupation, 'N/A'), '|',
                    COALESCE(contact_number, 'N/A'), '|',
                    COALESCE(email, 'N/A'), '|',
                    COALESCE(philhealth_number, 'N/A'), '|',
                    COALESCE(relationship_to_head, 'UNDETERMINED'), '|',
                    is_indigent, '|',
                    is_4ps_member, '|',
                    COALESCE(medical_history, 'None'), '|',
                    birthdate
                )
                ORDER BY 
                    CASE 
                        WHEN relationship_to_head = 'HEAD' THEN 0
                        WHEN relationship_to_head = 'SPOUSE' THEN 1
                        WHEN relationship_to_head IN ('SON', 'DAUGHTER') THEN 2
                        WHEN relationship_to_head IN ('FATHER', 'MOTHER') THEN 3
                        WHEN relationship_to_head IN ('BROTHER', 'SISTER') THEN 4
                        WHEN relationship_to_head IN ('GRANDFATHER', 'GRANDMOTHER') THEN 5
                        WHEN relationship_to_head IN ('GRANDSON', 'GRANDDAUGHTER') THEN 6
                        WHEN relationship_to_head IS NULL THEN 10
                        ELSE 7
                    END,
                    age DESC
                SEPARATOR ':::'
            ) as members_data
        FROM residents 
        WHERE $whereClause
        GROUP BY house_number, purok
        ORDER BY 
            CAST(SUBSTRING_INDEX(house_number, '#', -1) AS UNSIGNED),
            purok
        LIMIT $limit OFFSET $offset
    ";
    
    $result = mysqli_query($conn, $household_query);
    
    if (!$result) {
        echo json_encode(['error' => mysqli_error($conn)]);
        return;
    }
    
    $households = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $members = [];
        $member_data = explode(':::', $row['members_data']);
        
        // Auto-detect relationships if not set
        $household_members = [];
        foreach ($member_data as $member) {
            $member_info = explode('|', $member);
            if (count($member_info) >= 19) {
                $household_members[] = [
                    'id' => $member_info[0],
                    'first_name' => $member_info[1],
                    'middle_name' => $member_info[2],
                    'last_name' => $member_info[3],
                    'suffix' => $member_info[4],
                    'age' => (int)$member_info[5],
                    'sex' => $member_info[6],
                    'civil_status' => $member_info[7],
                    'education' => $member_info[8],
                    'religion' => $member_info[9],
                    'occupation' => $member_info[10],
                    'contact' => $member_info[11],
                    'email' => $member_info[12],
                    'philhealth' => $member_info[13],
                    'relationship' => $member_info[14],
                    'is_indigent' => $member_info[15] == '1',
                    'is_4ps' => $member_info[16] == '1',
                    'medical_history' => $member_info[17],
                    'birthdate' => $member_info[18]
                ];
            }
        }
        
        // Apply smart detection only in read-only mode for viewing
        // Relationships are determined by the system but not editable by admin
        $household_members = smartRelationshipDetection($household_members, $row['house_number'], $row['purok'], false);
        
        // Format for display
        foreach ($household_members as $member) {
            $full_name = trim($member['first_name'] . ' ' . $member['middle_name'] . ' ' . $member['last_name']);
            if (!empty($member['suffix'])) {
                $full_name .= ' ' . $member['suffix'];
            }
            
            $members[] = [
                'id' => $member['id'],
                'name' => trim($full_name),
                'age' => $member['age'],
                'sex' => ucfirst($member['sex']),
                'civil_status' => ucfirst($member['civil_status']),
                'education' => $member['education'],
                'religion' => $member['religion'],
                'occupation' => $member['occupation'],
                'contact' => $member['contact'],
                'email' => $member['email'],
                'philhealth' => $member['philhealth'] != 'N/A' ? 'Member' : 'Not Registered',
                'relationship' => $member['relationship'] ?: 'UNDETERMINED',
                'is_indigent' => $member['is_indigent'],
                'is_4ps' => $member['is_4ps'],
                'medical_history' => $member['medical_history'],
                'birthdate' => $member['birthdate']
            ];
        }
        
        $households[] = [
            'house_number' => $row['house_number'],
            'purok' => $row['purok'],
            'member_count' => $row['member_count'],
            'household_id' => 'HH-' . $row['purok'] . '-' . str_pad($row['house_number'], 4, '0', STR_PAD_LEFT),
            'members' => $members
        ];
    }
    
    // Get total count for pagination
    $count_query = "
        SELECT COUNT(DISTINCT house_number, purok) as total
        FROM residents 
        WHERE $whereClause
    ";
    $count_result = mysqli_query($conn, $count_query);
    $total_households = mysqli_fetch_assoc($count_result)['total'];
    
    echo json_encode([
        'households' => $households,
        'total' => $total_households,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total_households / $limit)
    ]);
}

// Smart relationship detection algorithm (read-only for admin viewing)
function smartRelationshipDetection($members, $house_number, $purok, $updateDatabase = false) {
    global $conn;
    
    if (empty($members)) return $members;
    
    // Sort members by priority for head selection
    usort($members, function($a, $b) {
        // Priority: married adults > single adults > minors
        $a_priority = getHeadPriority($a);
        $b_priority = getHeadPriority($b);
        
        if ($a_priority == $b_priority) {
            return $b['age'] - $a['age']; // Older first if same priority
        }
        return $a_priority - $b_priority; // Lower priority number = higher priority
    });
    
    $head_assigned = false;
    $spouse_assigned = false;
    
    foreach ($members as &$member) {
        // Skip if relationship already determined and not UNDETERMINED
        if (!empty($member['relationship']) && $member['relationship'] !== 'UNDETERMINED' && $member['relationship'] !== null) {
            if ($member['relationship'] === 'HEAD') $head_assigned = true;
            if ($member['relationship'] === 'SPOUSE') $spouse_assigned = true;
            continue;
        }
        
        // Assign HEAD (highest priority adult)
        if (!$head_assigned && $member['age'] >= 18) {
            $member['relationship'] = 'HEAD';
            $head_assigned = true;
            // Only update database if explicitly allowed (not for admin viewing)
            if ($updateDatabase) {
                updateMemberRelationship($member['id'], 'HEAD');
            }
            continue;
        }
        
        // Assign SPOUSE (married person of opposite sex to head, if exists)
        if ($head_assigned && !$spouse_assigned && $member['age'] >= 18) {
            $head_member = array_filter($members, function($m) { return $m['relationship'] === 'HEAD'; });
            $head_member = reset($head_member);
            
            if ($head_member && 
                $member['civil_status'] === 'married' && 
                $head_member['civil_status'] === 'married' &&
                $member['sex'] !== $head_member['sex']) {
                $member['relationship'] = 'SPOUSE';
                $spouse_assigned = true;
                if ($updateDatabase) {
                    updateMemberRelationship($member['id'], 'SPOUSE');
                }
                continue;
            }
        }
        
        // Determine other relationships based on age and context
        if ($member['age'] < 18) {
            // Children
            $member['relationship'] = $member['sex'] === 'male' ? 'SON' : 'DAUGHTER';
        } elseif ($member['age'] >= 60) {
            // Likely parents/grandparents
            $older_adults = array_filter($members, function($m) use ($member) { 
                return $m['age'] > $member['age'] && $m['age'] >= 18; 
            });
            
            if (empty($older_adults)) {
                $member['relationship'] = $member['sex'] === 'male' ? 'GRANDFATHER' : 'GRANDMOTHER';
            } else {
                $member['relationship'] = $member['sex'] === 'male' ? 'FATHER' : 'MOTHER';
            }
        } else {
            // Adult children or siblings
            $head_age = 0;
            foreach ($members as $m) {
                if ($m['relationship'] === 'HEAD') {
                    $head_age = $m['age'];
                    break;
                }
            }
            
            $age_difference = abs($member['age'] - $head_age);
            
            if ($age_difference < 10) {
                // Likely sibling
                $member['relationship'] = $member['sex'] === 'male' ? 'BROTHER' : 'SISTER';
            } else {
                // Likely adult child
                $member['relationship'] = $member['sex'] === 'male' ? 'SON' : 'DAUGHTER';
            }
        }
        
        // Only update database if explicitly allowed
        if ($updateDatabase) {
            updateMemberRelationship($member['id'], $member['relationship']);
        }
    }
    
    return $members;
}

function getHeadPriority($member) {
    // Lower number = higher priority
    if ($member['age'] >= 18 && $member['civil_status'] === 'married') return 1;
    if ($member['age'] >= 25 && $member['civil_status'] === 'single') return 2;
    if ($member['age'] >= 18) return 3;
    return 4; // minors
}

function updateMemberRelationship($member_id, $relationship) {
    global $conn;
    $query = "UPDATE residents SET relationship_to_head = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $relationship, $member_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function getHouseholdDetails() {
    global $conn;
    
    $household_id = $_GET['id'] ?? '';
    
    if (empty($household_id)) {
        echo json_encode(['error' => 'Household ID is required']);
        return;
    }
    
    // Parse household ID (format: HH-PUROK-HOUSE_NUMBER)
    $parts = explode('-', $household_id);
    if (count($parts) < 3) {
        echo json_encode(['error' => 'Invalid household ID format']);
        return;
    }
    
    $purok = $parts[1];
    $house_number = ltrim($parts[2], '0'); // Remove leading zeros
    
    $query = "
        SELECT 
            r.*,
            CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name, COALESCE(CONCAT(' ', suffix), '')) as full_name
        FROM residents r
        WHERE house_number = ? AND purok = ? AND resident_status = 'Active'
        ORDER BY 
            CASE 
                WHEN relationship_to_head = 'HEAD' THEN 0
                WHEN relationship_to_head = 'SPOUSE' THEN 1
                ELSE 2
            END,
            age DESC
    ";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $house_number, $purok);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $members = [];
    $household_stats = [
        'has_indigent' => false,
        'has_4ps' => false,
        'senior_count' => 0,
        'total_members' => 0
    ];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = [
            'name' => $row['full_name'],
            'relationship' => $row['relationship_to_head'] ?: 'UNDETERMINED',
            'age' => $row['age'],
            'occupation' => $row['occupation'] ?: 'N/A',
            'is_indigent' => $row['is_indigent'] == 1,
            'is_4ps' => $row['is_4ps_member'] == 1,
            'philhealth' => !empty($row['philhealth_number']) ? 'Member' : 'Not Registered'
        ];
        
        if ($row['is_indigent']) $household_stats['has_indigent'] = true;
        if ($row['is_4ps_member']) $household_stats['has_4ps'] = true;
        if ($row['age'] >= 60) $household_stats['senior_count']++;
        $household_stats['total_members']++;
    }
    
    $response = [
        'house_number' => $house_number,
        'purok' => $purok,
        'members' => $members,
        'household_type' => determineHouseholdType($members),
        ...$household_stats
    ];
    
    echo json_encode($response);
}

function determineHouseholdType($members) {
    if (count($members) === 1) return 'Single Person';
    
    $has_head = false;
    $has_spouse = false;
    $has_children = false;
    $has_extended = false;
    
    foreach ($members as $member) {
        switch ($member['relationship']) {
            case 'HEAD':
                $has_head = true;
                break;
            case 'SPOUSE':
                $has_spouse = true;
                break;
            case 'SON':
            case 'DAUGHTER':
                $has_children = true;
                break;
            case 'FATHER':
            case 'MOTHER':
            case 'GRANDFATHER':
            case 'GRANDMOTHER':
            case 'BROTHER':
            case 'SISTER':
                $has_extended = true;
                break;
        }
    }
    
    if ($has_extended) return 'Extended Family';
    if ($has_head && $has_spouse && $has_children) return 'Nuclear Family';
    if ($has_head && $has_spouse) return 'Nuclear Family';
    if ($has_head && $has_children) return 'Single Parent Family';
    
    return 'Composite';
}

// These functions are commented out - admins cannot edit census data
/*
function autoDetectAllRelationships() {
    // Admin editing disabled
    echo json_encode(['error' => 'Census editing is not available for administrators']);
}

function updateRelationship() {
    // Admin editing disabled  
    echo json_encode(['error' => 'Census editing is not available for administrators']);
}
*/

// ... (rest of the existing functions: exportToExcelEnhanced, getStatisticsEnhanced, updateRelationship)
// Keep all other existing functions as they are

function exportToExcelEnhanced() {
    global $conn;
    
    $type = $_POST['type'] ?? 'admin';
    
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Barangay Balas Management System')
        ->setTitle('Complete Census Data Export')
        ->setDescription('Comprehensive Household Census Data with Family Relationships');
    
    // Title
    $sheet->setCellValue('A1', 'BARANGAY BALAS - COMPREHENSIVE HOUSEHOLD CENSUS REPORT');
    $sheet->mergeCells('A1:S1');
    $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Date
    $sheet->setCellValue('A2', 'Generated on: ' . date('F d, Y h:i A'));
    $sheet->mergeCells('A2:S2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Headers
    $headers = [
        'A4' => 'House Number',
        'B4' => 'Purok', 
        'C4' => 'Full Name',
        'D4' => 'Relationship to Head',
        'E4' => 'Age',
        'F4' => 'Sex',
        'G4' => 'Birthdate',
        'H4' => 'Civil Status',
        'I4' => 'Educational Attainment',
        'J4' => 'Religion',
        'K4' => 'Occupation',
        'L4' => 'Contact Number',
        'M4' => 'Email',
        'N4' => 'PhilHealth Status',
        'O4' => 'Indigent Status',
        'P4' => '4Ps Member',
        'Q4' => 'Medical History',
        'R4' => 'Household Size',
        'S4' => 'Address'
    ];
    
    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }
    
    // Style headers
    $sheet->getStyle('A4:S4')->getFont()->setBold(true);
    $sheet->getStyle('A4:S4')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('4472C4');
    $sheet->getStyle('A4:S4')->getFont()->getColor()->setRGB('FFFFFF');
    
    // Get all census data with proper relationships
    $query = "
        SELECT 
            r.*,
            CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name, COALESCE(CONCAT(' ', suffix), '')) as full_name,
            (SELECT COUNT(*) FROM residents r2 
             WHERE r2.house_number = r.house_number 
             AND r2.purok = r.purok 
             AND r2.resident_status = 'Active') as household_size
        FROM residents r
        WHERE resident_status = 'Active'
        ORDER BY 
            CAST(SUBSTRING_INDEX(house_number, '#', -1) AS UNSIGNED),
            purok,
            CASE 
                WHEN relationship_to_head = 'HEAD' THEN 0
                WHEN relationship_to_head = 'SPOUSE' THEN 1
                WHEN relationship_to_head IN ('SON', 'DAUGHTER') THEN 2
                WHEN relationship_to_head IN ('FATHER', 'MOTHER') THEN 3
                WHEN relationship_to_head IN ('BROTHER', 'SISTER') THEN 4
                WHEN relationship_to_head IN ('GRANDFATHER', 'GRANDMOTHER') THEN 5
                WHEN relationship_to_head IN ('GRANDSON', 'GRANDDAUGHTER') THEN 6
                ELSE 7
            END,
            age DESC
    ";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die('Query failed: ' . mysqli_error($conn));
    }
    
    $row = 5;
    
    while ($data = mysqli_fetch_assoc($result)) {
        // Determine relationship if not set
        $relationship = $data['relationship_to_head'] ?: 'UNDETERMINED';
        
        // Fill data
        $sheet->setCellValue("A$row", $data['house_number']);
        $sheet->setCellValue("B$row", $data['purok']);
        $sheet->setCellValue("C$row", $data['full_name']);
        $sheet->setCellValue("D$row", $relationship);
        $sheet->setCellValue("E$row", $data['age']);
        $sheet->setCellValue("F$row", ucfirst($data['sex']));
        $sheet->setCellValue("G$row", date('M d, Y', strtotime($data['birthdate'])));
        $sheet->setCellValue("H$row", ucfirst($data['civil_status']));
        $sheet->setCellValue("I$row", $data['educational_attainment'] ?: 'N/A');
        $sheet->setCellValue("J$row", $data['religion'] ?: 'N/A');
        $sheet->setCellValue("K$row", $data['occupation'] ?: 'N/A');
        $sheet->setCellValue("L$row", $data['contact_number'] ?: 'N/A');
        $sheet->setCellValue("M$row", $data['email'] ?: 'N/A');
        $sheet->setCellValue("N$row", !empty($data['philhealth_number']) ? 'Member' : 'Not Registered');
        $sheet->setCellValue("O$row", $data['is_indigent'] ? 'Yes' : 'No');
        $sheet->setCellValue("P$row", $data['is_4ps_member'] ? 'Yes' : 'No');
        $sheet->setCellValue("Q$row", $data['medical_history'] ?: 'None');
        $sheet->setCellValue("R$row", $data['household_size']);
        $sheet->setCellValue("S$row", $data['address']);
        
        // Highlight head of household
        if ($relationship === 'HEAD') {
            $sheet->getStyle("A$row:S$row")->getFont()->setBold(true);
            $sheet->getStyle("A$row:S$row")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E7F3FF');
        }
        
        $row++;
    }
    
    // Auto-size columns
    foreach (range('A', 'S') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Add borders
    $sheet->getStyle("A4:S" . ($row - 1))->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
    
    // Output file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Barangay_Balas_Census_With_Relationships_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

function getStatisticsEnhanced() {
    global $conn;
    
    $query = "
        SELECT 
            COUNT(DISTINCT house_number, purok) as total_households,
            COUNT(*) as total_residents,
            SUM(CASE WHEN sex = 'male' THEN 1 ELSE 0 END) as male_population,
            SUM(CASE WHEN sex = 'female' THEN 1 ELSE 0 END) as female_population,
            SUM(CASE WHEN age < 18 THEN 1 ELSE 0 END) as children,
            SUM(CASE WHEN age >= 18 AND age < 60 THEN 1 ELSE 0 END) as adults,
            SUM(CASE WHEN age >= 60 THEN 1 ELSE 0 END) as seniors,
            SUM(CASE WHEN is_indigent = 1 THEN 1 ELSE 0 END) as indigent_families,
            SUM(CASE WHEN is_4ps_member = 1 THEN 1 ELSE 0 END) as fourps_members,
            SUM(CASE WHEN philhealth_number IS NOT NULL AND philhealth_number != '' THEN 1 ELSE 0 END) as philhealth_members,
            SUM(CASE WHEN relationship_to_head = 'HEAD' THEN 1 ELSE 0 END) as household_heads,
            SUM(CASE WHEN relationship_to_head IS NULL OR relationship_to_head = '' THEN 1 ELSE 0 END) as undetermined_relationships
        FROM residents 
        WHERE resident_status = 'Active'
    ";
    
    $result = mysqli_query($conn, $query);
    $stats = mysqli_fetch_assoc($result);
    
    echo json_encode($stats);
}

function updateRelationship() {
    global $conn;
    
    $resident_id = $_POST['resident_id'] ?? 0;
    $relationship = $_POST['relationship'] ?? '';
    
    if (!$resident_id || !$relationship) {
        echo json_encode(['error' => 'Missing required parameters']);
        return;
    }
    
    $query = "UPDATE residents SET relationship_to_head = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $relationship, $resident_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Relationship updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update relationship']);
    }
}
?>