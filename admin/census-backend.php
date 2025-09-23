<?php
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
    case 'update_relationship':
        updateRelationship();
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
    
    // Get households with complete member data
    $household_query = "
        SELECT 
            house_number,
            purok,
            COUNT(*) as member_count,
            MIN(id) as household_id,
            GROUP_CONCAT(
                CONCAT(
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
                    COALESCE(relationship_to_head, 'MEMBER'), '|',
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
        
        foreach ($member_data as $member) {
            $member_info = explode('|', $member);
            if (count($member_info) >= 18) {
                $full_name = trim($member_info[0] . ' ' . $member_info[1] . ' ' . $member_info[2]);
                if (!empty($member_info[3])) {
                    $full_name .= ' ' . $member_info[3]; // suffix
                }
                
                $members[] = [
                    'name' => trim($full_name),
                    'age' => $member_info[4],
                    'sex' => ucfirst($member_info[5]),
                    'civil_status' => ucfirst($member_info[6]),
                    'education' => $member_info[7],
                    'religion' => $member_info[8],
                    'occupation' => $member_info[9],
                    'contact' => $member_info[10],
                    'email' => $member_info[11],
                    'philhealth' => $member_info[12] != 'N/A' ? 'Member' : 'Not Registered',
                    'relationship' => $member_info[13] ?: 'MEMBER',
                    'is_indigent' => $member_info[14] == '1',
                    'is_4ps' => $member_info[15] == '1',
                    'medical_history' => $member_info[16],
                    'birthdate' => $member_info[17]
                ];
            }
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
        ->setDescription('Comprehensive Household Census Data with all information');
    
    // Title
    $sheet->setCellValue('A1', 'BARANGAY BALAS - COMPREHENSIVE HOUSEHOLD CENSUS REPORT');
    $sheet->mergeCells('A1:S1');
    $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Date
    $sheet->setCellValue('A2', 'Generated on: ' . date('F d, Y h:i A'));
    $sheet->mergeCells('A2:S2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Headers - Complete census data
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
    
    // Get all census data
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
    $current_household = '';
    $household_member_count = 0;
    
    while ($data = mysqli_fetch_assoc($result)) {
        $household_key = $data['house_number'] . '-' . $data['purok'];
        
        // Check if this is a new household for member count
        if ($household_key !== $current_household) {
            $current_household = $household_key;
            $household_member_count = 0;
        }
        $household_member_count++;
        
        // Determine relationship if not set
        $relationship = $data['relationship_to_head'] ?: 'MEMBER';
        
        // Fill complete data
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
    
    // Enhanced Summary section
    $summary_row = $row + 2;
    $sheet->setCellValue("A$summary_row", 'DETAILED STATISTICS');
    $sheet->mergeCells("A$summary_row:D$summary_row");
    $sheet->getStyle("A$summary_row")->getFont()->setBold(true);
    
    // Get comprehensive statistics
    $stats_query = "
        SELECT 
            COUNT(DISTINCT house_number, purok) as total_households,
            COUNT(*) as total_residents,
            SUM(CASE WHEN sex = 'male' THEN 1 ELSE 0 END) as male_population,
            SUM(CASE WHEN sex = 'female' THEN 1 ELSE 0 END) as female_population,
            SUM(CASE WHEN age < 18 THEN 1 ELSE 0 END) as children,
            SUM(CASE WHEN age >= 18 AND age < 60 THEN 1 ELSE 0 END) as adults,
            SUM(CASE WHEN age >= 60 THEN 1 ELSE 0 END) as seniors,
            SUM(CASE WHEN is_indigent = 1 THEN 1 ELSE 0 END) as indigent_count,
            SUM(CASE WHEN is_4ps_member = 1 THEN 1 ELSE 0 END) as fourps_count,
            SUM(CASE WHEN philhealth_number IS NOT NULL AND philhealth_number != '' THEN 1 ELSE 0 END) as philhealth_members
        FROM residents 
        WHERE resident_status = 'Active'
    ";
    $stats_result = mysqli_query($conn, $stats_query);
    $stats = mysqli_fetch_assoc($stats_result);
    
    $summary_row++;
    $sheet->setCellValue("A$summary_row", 'Total Households:');
    $sheet->setCellValue("B$summary_row", $stats['total_households']);
    $sheet->setCellValue("C$summary_row", 'Total Residents:');
    $sheet->setCellValue("D$summary_row", $stats['total_residents']);
    
    $summary_row++;
    $sheet->setCellValue("A$summary_row", 'Male Population:');
    $sheet->setCellValue("B$summary_row", $stats['male_population']);
    $sheet->setCellValue("C$summary_row", 'Female Population:');
    $sheet->setCellValue("D$summary_row", $stats['female_population']);
    
    $summary_row++;
    $sheet->setCellValue("A$summary_row", 'Children (0-17):');
    $sheet->setCellValue("B$summary_row", $stats['children']);
    $sheet->setCellValue("C$summary_row", 'Adults (18-59):');
    $sheet->setCellValue("D$summary_row", $stats['adults']);
    
    $summary_row++;
    $sheet->setCellValue("A$summary_row", 'Seniors (60+):');
    $sheet->setCellValue("B$summary_row", $stats['seniors']);
    $sheet->setCellValue("C$summary_row", 'Indigent Families:');
    $sheet->setCellValue("D$summary_row", $stats['indigent_count']);
    
    $summary_row++;
    $sheet->setCellValue("A$summary_row", '4Ps Members:');
    $sheet->setCellValue("B$summary_row", $stats['fourps_count']);
    $sheet->setCellValue("C$summary_row", 'PhilHealth Members:');
    $sheet->setCellValue("D$summary_row", $stats['philhealth_members']);
    
    // Output file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Barangay_Balas_Complete_Census_' . date('Y-m-d') . '.xlsx"');
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
            SUM(CASE WHEN relationship_to_head = 'HEAD' THEN 1 ELSE 0 END) as household_heads
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

// Function to auto-determine relationships based on household data
function autoDetectRelationships($house_number, $purok) {
    global $conn;
    
    // Get all members of the household ordered by age
    $query = "
        SELECT id, age, sex, civil_status 
        FROM residents 
        WHERE house_number = ? AND purok = ? AND resident_status = 'Active'
        ORDER BY 
            CASE WHEN civil_status = 'married' THEN 0 ELSE 1 END,
            age DESC
    ";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $house_number, $purok);
    mysqli_stmt_execute($stmt);
    $members = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    
    if (empty($members)) return;
    
    // Set the first (oldest married or oldest overall) as HEAD
    $update_query = "UPDATE residents SET relationship_to_head = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    
    foreach ($members as $index => $member) {
        $relationship = 'MEMBER'; // default
        
        if ($index === 0) {
            $relationship = 'HEAD';
        } elseif ($index === 1 && $member['civil_status'] === 'married') {
            $relationship = 'SPOUSE';
        } elseif ($index > 1 || ($index === 1 && $member['civil_status'] !== 'married')) {
            // For children, determine based on age and household head's age
            if ($member['age'] < 18) {
                $relationship = ($member['sex'] === 'male') ? 'SON' : 'DAUGHTER';
            } else {
                // Adult children or other relations need manual review
                $relationship = 'MEMBER';
            }
        }
        
        mysqli_stmt_bind_param($update_stmt, "si", $relationship, $member['id']);
        mysqli_stmt_execute($update_stmt);
    }
}
?>