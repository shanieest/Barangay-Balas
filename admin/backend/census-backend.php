<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../../vendor/autoload.php'; 

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
    case 'export_pdf':
        exportPurokToPDF();
        break;
    case 'get_statistics':
        getStatisticsEnhanced();
        break;
    case 'get_household_details':
        getHouseholdDetails();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function getHouseholdsEnhanced() {
    global $conn;
    
    $search = $_GET['search'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 25);
    $offset = ($page - 1) * $limit;
    
    // Use account_status = 'Approved' from resident_accounts table
    $whereConditions = ["ra.account_status = 'Approved'", "r.resident_status = 'Active'"];
    
    if (!empty($search)) {
        $search_term = mysqli_real_escape_string($conn, $search);
        $whereConditions[] = "(CONCAT(r.first_name, ' ', COALESCE(r.middle_name, ''), ' ', r.last_name) LIKE '%$search_term%' 
                             OR r.house_number LIKE '%$search_term%' 
                             OR r.address LIKE '%$search_term%'
                             OR r.occupation LIKE '%$search_term%')";
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $household_query = "
        SELECT 
            r.house_number,
            COUNT(*) as member_count,
            MIN(r.id) as household_id,
            MIN(r.address) as address,
            GROUP_CONCAT(
                CONCAT(
                    r.id, '|',
                    r.first_name, '|', 
                    COALESCE(r.middle_name, ''), '|', 
                    r.last_name, '|',
                    COALESCE(r.suffix, ''), '|',
                    r.age, '|', 
                    r.sex, '|', 
                    r.civil_status, '|', 
                    COALESCE(r.educational_attainment, 'N/A'), '|',
                    COALESCE(r.religion, 'N/A'), '|',
                    COALESCE(r.occupation, 'N/A'), '|',
                    COALESCE(r.contact_number, 'N/A'), '|',
                    COALESCE(r.email, 'N/A'), '|',
                    COALESCE(r.philhealth_number, 'N/A'), '|',
                    COALESCE(r.relationship_to_head, 'UNDETERMINED'), '|',
                    r.is_indigent, '|',
                    r.is_4ps_member, '|',
                    COALESCE(r.medical_history, 'None'), '|',
                    r.birthdate
                )
                ORDER BY 
                    CASE 
                        WHEN r.relationship_to_head = 'HEAD' THEN 0
                        WHEN r.relationship_to_head = 'SPOUSE' THEN 1
                        WHEN r.relationship_to_head IN ('SON', 'DAUGHTER') THEN 2
                        ELSE 3
                    END,
                    r.age DESC
                SEPARATOR ':::'
            ) as members_data
        FROM residents r
        INNER JOIN resident_accounts ra ON r.id = ra.resident_id
        WHERE $whereClause
        GROUP BY r.house_number
        ORDER BY CAST(SUBSTRING_INDEX(r.house_number, '#', -1) AS UNSIGNED)
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
        
        $household_members = smartRelationshipDetection($household_members, $row['house_number'], false);
        
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
            'address' => $row['address'],
            'member_count' => $row['member_count'],
            'household_id' => 'HH-' . str_pad($row['house_number'], 4, '0', STR_PAD_LEFT),
            'members' => $members
        ];
    }
    
    $count_query = "
        SELECT COUNT(DISTINCT r.house_number) as total
        FROM residents r
        INNER JOIN resident_accounts ra ON r.id = ra.resident_id
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
function smartRelationshipDetection($members, $house_number, $updateDatabase = false) {
    global $conn;
    
    if (empty($members)) return $members;
    
    usort($members, function($a, $b) {
        $a_priority = getHeadPriority($a);
        $b_priority = getHeadPriority($b);
        
        if ($a_priority == $b_priority) {
            return $b['age'] - $a['age'];
        }
        return $a_priority - $b_priority;
    });
    
    $head_assigned = false;
    $spouse_assigned = false;
    
    foreach ($members as &$member) {
        if (!empty($member['relationship']) && $member['relationship'] !== 'UNDETERMINED') {
            if ($member['relationship'] === 'HEAD') $head_assigned = true;
            if ($member['relationship'] === 'SPOUSE') $spouse_assigned = true;
            continue;
        }
        
        if (!$head_assigned && $member['age'] >= 18) {
            $member['relationship'] = 'HEAD';
            $head_assigned = true;
            if ($updateDatabase) {
                updateMemberRelationship($member['id'], 'HEAD');
            }
            continue;
        }
        
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
        
        if ($member['age'] < 18) {
            $member['relationship'] = $member['sex'] === 'male' ? 'SON' : 'DAUGHTER';
        } elseif ($member['age'] >= 60) {
            $older_adults = array_filter($members, function($m) use ($member) { 
                return $m['age'] > $member['age'] && $m['age'] >= 18; 
            });
            
            if (empty($older_adults)) {
                $member['relationship'] = $member['sex'] === 'male' ? 'GRANDFATHER' : 'GRANDMOTHER';
            } else {
                $member['relationship'] = $member['sex'] === 'male' ? 'FATHER' : 'MOTHER';
            }
        } else {
            $head_age = 0;
            foreach ($members as $m) {
                if ($m['relationship'] === 'HEAD') {
                    $head_age = $m['age'];
                    break;
                }
            }
            
            $age_difference = abs($member['age'] - $head_age);
            
            if ($age_difference < 10) {
                $member['relationship'] = $member['sex'] === 'male' ? 'BROTHER' : 'SISTER';
            } else {
                $member['relationship'] = $member['sex'] === 'male' ? 'SON' : 'DAUGHTER';
            }
        }
        
        if ($updateDatabase) {
            updateMemberRelationship($member['id'], $member['relationship']);
        }
    }
    
    return $members;
}

function getHeadPriority($member) {
    if ($member['age'] >= 18 && $member['civil_status'] === 'married') return 1;
    if ($member['age'] >= 25 && $member['civil_status'] === 'single') return 2;
    if ($member['age'] >= 18) return 3;
    return 4;
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
    
    $house_number = ltrim(str_replace('HH-', '', $household_id), '0');
    
    $query = "
        SELECT 
            r.*,
            CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name, COALESCE(CONCAT(' ', suffix), '')) as full_name
        FROM residents r
        WHERE house_number = ? AND resident_status = 'Active'
        ORDER BY 
            CASE 
                WHEN relationship_to_head = 'HEAD' THEN 0
                WHEN relationship_to_head = 'SPOUSE' THEN 1
                ELSE 2
            END,
            age DESC
    ";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $house_number);
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

function exportToExcelEnhanced() {
    global $conn;
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $spreadsheet->getProperties()
        ->setCreator('Barangay Balas')
        ->setTitle('Complete Census Data Export')
        ->setDescription('Comprehensive Household Census Data');
    
    $sheet->setCellValue('A1', 'BARANGAY BALAS - COMPREHENSIVE HOUSEHOLD CENSUS REPORT');
    $sheet->mergeCells('A1:R1');
    $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    $sheet->setCellValue('A2', 'Generated on: ' . date('F d, Y h:i A'));
    $sheet->mergeCells('A2:R2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    $headers = [
        'A4' => 'House Number',
        'B4' => 'Purok',
        'C4' => 'Full Name',
        'D4' => 'Relationship',
        'E4' => 'Age',
        'F4' => 'Sex',
        'G4' => 'Birthdate',
        'H4' => 'Civil Status',
        'I4' => 'Education',
        'J4' => 'Religion',
        'K4' => 'Occupation',
        'L4' => 'Contact',
        'M4' => 'Email',
        'N4' => 'PhilHealth',
        'O4' => 'Indigent',
        'P4' => '4Ps',
        'Q4' => 'Medical History',
        'R4' => 'Household Size',
        'S4' => 'Address'
    ];
    
    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }
    
    $sheet->getStyle('A4:R4')->getFont()->setBold(true);
    $sheet->getStyle('A4:R4')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('4472C4');
    $sheet->getStyle('A4:R4')->getFont()->getColor()->setRGB('FFFFFF');
    
    $query = "
        SELECT 
            r.*,
            CONCAT(r.first_name, ' ', COALESCE(r.middle_name, ''), ' ', r.last_name, COALESCE(CONCAT(' ', r.suffix), '')) as full_name,
            (SELECT COUNT(*) FROM residents r2 
             INNER JOIN resident_accounts ra2 ON r2.id = ra2.resident_id
             WHERE r2.house_number = r.house_number 
             AND r2.resident_status = 'Active'
             AND ra2.account_status = 'Approved') as household_size
        FROM residents r
        INNER JOIN resident_accounts ra ON r.id = ra.resident_id
        WHERE ra.account_status = 'Approved' AND r.resident_status = 'Active'
        ORDER BY 
            CAST(SUBSTRING_INDEX(r.house_number, '#', -1) AS UNSIGNED),
            CASE 
                WHEN r.relationship_to_head = 'HEAD' THEN 0
                WHEN r.relationship_to_head = 'SPOUSE' THEN 1
                ELSE 2
            END,
            r.age DESC
    ";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die('Query failed: ' . mysqli_error($conn));
    }
    
    $row = 5;
    
    while ($data = mysqli_fetch_assoc($result)) {
        $relationship = $data['relationship_to_head'] ?: 'UNDETERMINED';
        
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
        
        if ($relationship === 'HEAD') {
            $sheet->getStyle("A$row:R$row")->getFont()->setBold(true);
            $sheet->getStyle("A$row:R$row")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E7F3FF');
        }
        
        $row++;
    }
    
    foreach (range('A', 'R') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    $sheet->getStyle("A4:R" . ($row - 1))->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Barangay_Balas_Census_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
function exportPurokToPDF() {
    global $conn;
    
    $purok = $_POST['purok'] ?? $_GET['purok'] ?? '';
    
    if (empty($purok)) {
        echo json_encode(['error' => 'Purok is required']);
        return;
    }
    
    try {
        // Create PDF with landscape orientation
        $pdf = new TCPDF('L', 'mm', array(215.9, 330.2), true, 'UTF-8', false);
        
        $pdf->SetCreator('Barangay Balas');
        $pdf->SetAuthor('Barangay Balas');
        $pdf->SetTitle(($purok === 'all' ? 'All Puroks' : $purok) . ' - Complete Census Report');
        $pdf->SetSubject('Resident Census Data');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 15, 10);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->AddPage();
        
        // DEBUG: Log the purok being processed
        error_log("PROCESSING PUROK: " . $purok);
        
        // WHERE condition for filtering - FIXED PUROK FILTERING
        $where_conditions = [
            "ra.account_status = 'Approved'",
            "r.resident_status = 'Active'"
        ];
        
        if ($purok !== 'all') {
            $purok_escaped = mysqli_real_escape_string($conn, $purok);
            $where_conditions[] = "r.purok = '$purok_escaped'";
            error_log("APPLYING PUROK FILTER: " . $purok_escaped);
        } else {
            error_log("PROCESSING ALL PUROKS");
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        error_log("FINAL WHERE CLAUSE: " . $where_clause);
        
        // CRITICAL FIX: Get EVERY SINGLE RESIDENT INDIVIDUALLY - NO GROUPING AT ALL!
        $all_residents_query = "
            SELECT 
                r.*,
                CONCAT(r.first_name, ' ', COALESCE(r.middle_name, ''), ' ', r.last_name, COALESCE(CONCAT(' ', r.suffix), '')) as full_name,
                (SELECT COUNT(*) FROM residents r2 
                 INNER JOIN resident_accounts ra2 ON r2.id = ra2.resident_id 
                 WHERE r2.house_number = r.house_number 
                 AND r2.purok = r.purok 
                 AND ra2.account_status = 'Approved' 
                 AND r2.resident_status = 'Active') as household_member_count
            FROM residents r
            INNER JOIN resident_accounts ra ON r.id = ra.resident_id
            WHERE $where_clause
            ORDER BY 
                r.purok,
                CAST(SUBSTRING_INDEX(r.house_number, '#', -1) AS UNSIGNED),
                CASE 
                    WHEN r.relationship_to_head = 'HEAD' THEN 0
                    WHEN r.relationship_to_head = 'SPOUSE' THEN 1
                    WHEN r.relationship_to_head IN ('SON', 'DAUGHTER') THEN 2
                    ELSE 3
                END,
                r.age DESC
        ";
        
        error_log("FINAL QUERY: " . $all_residents_query);
        
        $all_residents_result = mysqli_query($conn, $all_residents_query);
        
        if (!$all_residents_result) {
            throw new Exception('Database query failed: ' . mysqli_error($conn));
        }
        
        $total_residents_in_query = mysqli_num_rows($all_residents_result);
        error_log("CRITICAL CHECK - Total Residents Retrieved from Database: " . $total_residents_in_query);
        
        if ($total_residents_in_query === 0) {
            error_log("WARNING: NO RESIDENTS FOUND FOR PUROK: " . $purok);
        }
        
        // Group residents by household for organized printing - BUT KEEP ALL MEMBERS
        $households = [];
        $puroks_found = [];
        
        while ($resident = mysqli_fetch_assoc($all_residents_result)) {
            $house_key = $resident['purok'] . '|' . $resident['house_number'];
            
            if (!isset($households[$house_key])) {
                $households[$house_key] = [
                    'purok' => $resident['purok'],
                    'house_number' => $resident['house_number'],
                    'address' => $resident['address'],
                    'members' => []
                ];
            }
            
            $households[$house_key]['members'][] = $resident;
            
            // Track puroks found
            if (!in_array($resident['purok'], $puroks_found)) {
                $puroks_found[] = $resident['purok'];
            }
            
            // DEBUG: Log each resident found
            error_log("FOUND RESIDENT: " . $resident['full_name'] . " | Purok: " . $resident['purok'] . " | House: " . $resident['house_number'] . " | Relationship: " . $resident['relationship_to_head']);
        }
        
        error_log("CRITICAL CHECK - Total Households: " . count($households));
        error_log("PUROKS FOUND IN DATA: " . implode(', ', $puroks_found));
        
        // Get statistics - USING THE SAME WHERE CLAUSE
        $stats_query = "
            SELECT 
                COUNT(DISTINCT r.house_number) as total_households,
                COUNT(*) as total_residents,
                SUM(CASE WHEN r.sex = 'male' THEN 1 ELSE 0 END) as male_population,
                SUM(CASE WHEN r.sex = 'female' THEN 1 ELSE 0 END) as female_population,
                SUM(CASE WHEN r.age < 18 THEN 1 ELSE 0 END) as children,
                SUM(CASE WHEN r.age >= 18 AND r.age < 60 THEN 1 ELSE 0 END) as adults,
                SUM(CASE WHEN r.age >= 60 THEN 1 ELSE 0 END) as seniors,
                SUM(CASE WHEN r.is_indigent = 1 THEN 1 ELSE 0 END) as indigent_count,
                SUM(CASE WHEN r.is_4ps_member = 1 THEN 1 ELSE 0 END) as fourps_count,
                SUM(CASE WHEN r.philhealth_number IS NOT NULL AND r.philhealth_number != '' THEN 1 ELSE 0 END) as philhealth_count,
                COUNT(DISTINCT r.purok) as purok_count
            FROM residents r
            INNER JOIN resident_accounts ra ON r.id = ra.resident_id
            WHERE $where_clause
        ";
        
        $stats_result = mysqli_query($conn, $stats_query);
        $stats = mysqli_fetch_assoc($stats_result);
        
        error_log("CRITICAL CHECK - Total Residents in Stats: " . $stats['total_residents']);
        error_log("CRITICAL CHECK - Puroks in Stats: " . $stats['purok_count']);
        
        // Generate PDF with ALL members
        generateAllMembersPDF($pdf, $purok, $households, $stats);
        
        $filename = ($purok === 'all' ? 'All_Puroks_Complete_Census' : str_replace(' ', '_', $purok) . '_Complete_Census') . 
                   '_' . date('Y-m-d') . '.pdf';
        
        $pdf->Output($filename, 'I');
        
    } catch (Exception $e) {
        error_log('PDF Generation Error: ' . $e->getMessage());
        
        if (!headers_sent()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'PDF generation failed: ' . $e->getMessage()]);
        }
        exit;
    }
}

function generateAllMembersPDF($pdf, $requested_purok, $households, $stats) {
    // Title section
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 15, 'BARANGAY BALAS - COMPLETE CENSUS REPORT', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 12, $requested_purok === 'all' ? 'ALL PUROKS - ALL FAMILY MEMBERS' : strtoupper($requested_purok) . ' - ALL FAMILY MEMBERS', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->SetTextColor(0, 0, 0);
    
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, 'Generated on: ' . date('F d, Y h:i A'), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Statistics Section
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'COMPREHENSIVE POPULATION STATISTICS', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetFillColor(240, 240, 240);
    
    $columnWidth = 65;
    
    $pdf->Cell($columnWidth, 10, 'Total Households: ' . $stats['total_households'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Total Residents: ' . $stats['total_residents'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Puroks Covered: ' . $stats['purok_count'], 1, 1, 'L', true);
    
    $pdf->Cell($columnWidth, 10, 'Male Population: ' . $stats['male_population'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Female Population: ' . $stats['female_population'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Gender Ratio: ' . ($stats['male_population'] > 0 ? 
        round(($stats['female_population'] / $stats['male_population']) * 100, 1) . '%' : 'N/A'), 1, 1, 'L', true);
    
    $pdf->Cell($columnWidth, 10, 'Children (<18): ' . $stats['children'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Adults (18-59): ' . $stats['adults'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Seniors (60+): ' . $stats['seniors'], 1, 1, 'L', true);
    
    $pdf->Cell($columnWidth, 10, 'Indigent Members: ' . $stats['indigent_count'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, '4Ps Members: ' . $stats['fourps_count'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'PhilHealth Members: ' . $stats['philhealth_count'], 1, 1, 'L', true);
    
    $pdf->Ln(12);
    
    // Print ALL households with ALL members
    $total_residents_printed = 0;
    $total_households_printed = 0;
    $current_purok = '';
    
    // Sort households by purok and house number
    uasort($households, function($a, $b) {
        if ($a['purok'] === $b['purok']) {
            return strnatcmp($a['house_number'], $b['house_number']);
        }
        return strnatcmp($a['purok'], $b['purok']);
    });
    
    foreach ($households as $household) {
        $total_households_printed++;
        $members = $household['members'];
        $member_count = count($members);
        
        error_log("========================================");
        error_log("PUROK: {$household['purok']} - HOUSEHOLD #{$household['house_number']} - HAS {$member_count} FAMILY MEMBERS");
        error_log("========================================");
        
        // Purok header when purok changes
        if ($current_purok !== $household['purok']) {
            $current_purok = $household['purok'];
            
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
            }
            
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetFillColor(100, 100, 200);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 12, 'PUROK: ' . strtoupper($current_purok), 1, 1, 'C', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Ln(5);
            
            error_log("DISPLAYING PUROK HEADER: " . $current_purok);
        }
        
        // Check page break
        if ($pdf->GetY() > 270) {
            $pdf->AddPage();
        }
        
        // Household Header - SHOW TOTAL FAMILY MEMBERS
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(220, 220, 220);
        $household_header = 'HOUSEHOLD #' . $household['house_number'] . ' - ' . $household['address'] . ' - TOTAL FAMILY MEMBERS: ' . $member_count;
        $pdf->Cell(0, 8, $household_header, 1, 1, 'L', true);
        
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->Cell(0, 6, 'Purok: ' . $household['purok'] . ' | This household has ' . $member_count . ' family members (ALL LISTED BELOW)', 0, 1);
        $pdf->Ln(2);
        
        // Table headers
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(180, 180, 180);
        
        $headers = [
            'No.' => 8,
            'Full Name' => 40,
            'Relationship' => 22,
            'Age' => 8,
            'Sex' => 8,
            'Birthdate' => 22,
            'Civil Status' => 18,
            'Occupation' => 30,
            'Contact' => 22,
            'PhilHealth' => 15,
            'Indigent' => 12,
            '4Ps' => 10
        ];
        
        foreach ($headers as $header => $width) {
            $pdf->Cell($width, 6, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // CRITICAL: Print EVERY SINGLE FAMILY MEMBER
        $pdf->SetFont('helvetica', '', 6.5);
        $member_number = 0;
        $fill = false;
        
        // LOOP THROUGH ALL FAMILY MEMBERS - NO EXCEPTIONS
        foreach ($members as $member) {
            $member_number++;
            $total_residents_printed++;
            
            // LOG EVERY MEMBER BEING PRINTED
            error_log("  → Member #{$member_number}: {$member['full_name']} (Age: {$member['age']}, Relationship: {$member['relationship_to_head']})");
            
            // Page break check
            if ($pdf->GetY() > 285) {
                $pdf->AddPage();
                // Reprint headers
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->SetFillColor(180, 180, 180);
                foreach ($headers as $header => $width) {
                    $pdf->Cell($width, 6, $header, 1, 0, 'C', true);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 6.5);
            }
            
            $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            
            // Format data
            $birthdate = $member['birthdate'] != '0000-00-00' && !empty($member['birthdate']) ? 
                date('M d, Y', strtotime($member['birthdate'])) : 'N/A';
            $relationship = $member['relationship_to_head'] ?: 'UNDETERMINED';
            $philhealth_status = !empty($member['philhealth_number']) ? 'Yes' : 'No';
            $indigent_status = $member['is_indigent'] ? 'Yes' : 'No';
            $fourps_status = $member['is_4ps_member'] ? 'Yes' : 'No';
            
            // Print this family member's complete information
            $pdf->Cell(8, 5, $member_number, 1, 0, 'C', $fill);
            $pdf->Cell(40, 5, substr($member['full_name'], 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell(22, 5, $relationship, 1, 0, 'C', $fill);
            $pdf->Cell(8, 5, $member['age'], 1, 0, 'C', $fill);
            $pdf->Cell(8, 5, strtoupper(substr($member['sex'], 0, 1)), 1, 0, 'C', $fill);
            $pdf->Cell(22, 5, $birthdate, 1, 0, 'C', $fill);
            $pdf->Cell(18, 5, substr($member['civil_status'], 0, 10), 1, 0, 'C', $fill);
            $pdf->Cell(30, 5, substr($member['occupation'] ?: 'N/A', 0, 18), 1, 0, 'L', $fill);
            $pdf->Cell(22, 5, substr($member['contact_number'] ?: 'N/A', 0, 10), 1, 0, 'C', $fill);
            $pdf->Cell(15, 5, $philhealth_status, 1, 0, 'C', $fill);
            $pdf->Cell(12, 5, $indigent_status, 1, 0, 'C', $fill);
            $pdf->Cell(10, 5, $fourps_status, 1, 1, 'C', $fill);
            
            $fill = !$fill;
        }
        
        error_log("HOUSEHOLD #{$household['house_number']} - SUCCESSFULLY PRINTED ALL {$member_number} MEMBERS");
        error_log("========================================");
        
        $pdf->Ln(3);
        
        // Household summary
        $pdf->SetFont('helvetica', 'I', 6);
        $adults = count(array_filter($members, function($m) { return $m['age'] >= 18 && $m['age'] < 60; }));
        $children = count(array_filter($members, function($m) { return $m['age'] < 18; }));
        $seniors = count(array_filter($members, function($m) { return $m['age'] >= 60; }));
        $males = count(array_filter($members, function($m) { return $m['sex'] === 'male'; }));
        $females = count(array_filter($members, function($m) { return $m['sex'] === 'female'; }));
        
        $summary = "Household #{$household['house_number']} Complete - {$member_number} Total Members: ";
        $parts = [];
        if ($adults > 0) $parts[] = "{$adults} Adult" . ($adults > 1 ? 's' : '');
        if ($children > 0) $parts[] = "{$children} Child" . ($children > 1 ? 'ren' : '');
        if ($seniors > 0) $parts[] = "{$seniors} Senior" . ($seniors > 1 ? 's' : '');
        $parts[] = "{$males}M/{$females}F";
        $summary .= implode(', ', $parts);
        
        $pdf->Cell(0, 4, $summary, 0, 1);
        $pdf->Ln(5);
    }
    
    // Final verification
    error_log("================== FINAL VERIFICATION ==================");
    error_log("REQUESTED PUROK: " . $requested_purok);
    error_log("TOTAL HOUSEHOLDS PRINTED: {$total_households_printed}");
    error_log("TOTAL RESIDENTS PRINTED: {$total_residents_printed}");
    error_log("EXPECTED RESIDENTS: {$stats['total_residents']}");
    error_log("MATCH: " . ($total_residents_printed == $stats['total_residents'] ? 'YES ✓' : 'NO ✗'));
    error_log("========================================================");
    
    // Footer
    $pdf->SetY(-20);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 6, "COMPLETE CENSUS - ALL {$total_households_printed} HOUSEHOLDS WITH ALL {$total_residents_printed} FAMILY MEMBERS INCLUDED", 0, 1, 'C');
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 6, 'Coverage: ' . ($requested_purok === 'all' ? 'All Puroks' : $requested_purok) . ' | Generated: ' . date('F d, Y h:i A') . ' | Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 1, 'C');
}

function generateCompleteHouseholdPDFContent($pdf, $purok, $households_result, $stats) {
    // Title section
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 15, 'BARANGAY BALAS - COMPLETE CENSUS REPORT', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->Cell(0, 12, $purok === 'all' ? 'ALL PUROKS - COMPLETE HOUSEHOLD DATA' : strtoupper($purok) . ' - COMPLETE HOUSEHOLD DATA', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, 'Generated on: ' . date('F d, Y h:i A'), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Comprehensive Statistics Section
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'COMPREHENSIVE POPULATION STATISTICS', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    
    // Statistics table
    $pdf->SetFillColor(240, 240, 240);
    
    $columnWidth = 65;
    
    // First row of statistics
    $pdf->Cell($columnWidth, 10, 'Total Households: ' . $stats['total_households'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Total Residents: ' . $stats['total_residents'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Puroks Covered: ' . $stats['purok_count'], 1, 1, 'L', true);
    
    // Second row
    $pdf->Cell($columnWidth, 10, 'Male Population: ' . $stats['male_population'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Female Population: ' . $stats['female_population'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Gender Ratio: ' . ($stats['male_population'] > 0 ? 
        round(($stats['female_population'] / $stats['male_population']) * 100, 1) . '%' : 'N/A'), 1, 1, 'L', true);
    
    // Third row
    $pdf->Cell($columnWidth, 10, 'Children (<18): ' . $stats['children'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Adults (18-59): ' . $stats['adults'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'Seniors (60+): ' . $stats['seniors'], 1, 1, 'L', true);
    
    // Fourth row
    $pdf->Cell($columnWidth, 10, 'Indigent Members: ' . $stats['indigent_count'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, '4Ps Members: ' . $stats['fourps_count'], 1, 0, 'L', true);
    $pdf->Cell($columnWidth, 10, 'PhilHealth Members: ' . $stats['philhealth_count'], 1, 1, 'L', true);
    
    $pdf->Ln(12);
    
    // Process each household with ALL members - CRITICAL SECTION
    $total_residents_printed = 0;
    $total_households_printed = 0;
    $current_purok = '';
    
    while ($household = mysqli_fetch_assoc($households_result)) {
        $total_households_printed++;
        
        // DEBUG: Log household processing
        error_log("Processing Household #" . $household['house_number'] . " - Member Count: " . $household['member_count']);
        
        // Parse ALL household members data - VERIFIED TO INCLUDE ALL MEMBERS
        $household_members = [];
        $member_data = explode(':::', $household['members_data']);
        
        error_log("Household #" . $household['house_number'] . " - Member Data Array Count: " . count($member_data));
        
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
        
        // VERIFY: Log parsed member count
        error_log("Household #" . $household['house_number'] . " - Parsed Members: " . count($household_members));
        
        // Process relationships for display (without updating database)
        $household_members = smartRelationshipDetection($household_members, $household['house_number'], false);
        
        // Add purok section header if purok changes
        if ($current_purok !== $household['purok']) {
            $current_purok = $household['purok'];
            
            // Check if we need a new page
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
            }
            
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetFillColor(150, 150, 150);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 12, 'PUROK: ' . strtoupper($current_purok), 1, 1, 'C', true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Ln(5);
        }
        
        // Check if we need a new page before printing household
        if ($pdf->GetY() > 270) {
            $pdf->AddPage();
        }
        
        // Household Header
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(220, 220, 220);
        $household_header = 'HOUSEHOLD #' . $household['house_number'] . ' - ' . $household['address'];
        $pdf->Cell(0, 8, $household_header, 1, 1, 'L', true);
        
        // Household sub-header - SHOW ACTUAL MEMBER COUNT
        $pdf->SetFont('helvetica', 'I', 9);
        $actual_member_count = count($household_members);
        $pdf->Cell(0, 6, 'Members: ' . $actual_member_count . ' | Purok: ' . $household['purok'], 0, 1);
        $pdf->Ln(2);
        
        // CRITICAL: Verify member count
        error_log("Household #" . $household['house_number'] . " - About to print " . $actual_member_count . " members");
        
        // Table header for household members
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(180, 180, 180);
        
        $headers = [
            'No.' => 8,
            'Full Name' => 40,
            'Relationship' => 22,
            'Age' => 8,
            'Sex' => 8,
            'Birthdate' => 22,
            'Civil Status' => 18,
            'Occupation' => 30,
            'Contact' => 22,
            'PhilHealth' => 15,
            'Indigent' => 12,
            '4Ps' => 10
        ];
        
        foreach ($headers as $header => $width) {
            $pdf->Cell($width, 6, $header, 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // Household members data - PRINT ALL MEMBERS - CRITICAL LOOP
        $pdf->SetFont('helvetica', '', 6.5);
        $member_count = 0;
        $fill = false;
        
        // CRITICAL: Loop through ALL members
        foreach ($household_members as $member) {
            $member_count++;
            $total_residents_printed++;
            
            // DEBUG: Log each member being printed
            $member_name = trim($member['first_name'] . ' ' . $member['last_name']);
            error_log("Printing member #$member_count: $member_name (Household #{$household['house_number']})");
            
            // Check if we need a new page within household members
            if ($pdf->GetY() > 285) {
                $pdf->AddPage();
                // Reprint header on new page
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->SetFillColor(180, 180, 180);
                foreach ($headers as $header => $width) {
                    $pdf->Cell($width, 6, $header, 1, 0, 'C', true);
                }
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 6.5);
            }
            
            $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            
            // Format full name
            $full_name = trim($member['first_name'] . ' ' . $member['middle_name'] . ' ' . $member['last_name']);
            if (!empty($member['suffix'])) {
                $full_name .= ' ' . $member['suffix'];
            }
            
            // Format data for display
            $birthdate = $member['birthdate'] != '0000-00-00' && !empty($member['birthdate']) ? 
                date('M d, Y', strtotime($member['birthdate'])) : 'N/A';
            $relationship = $member['relationship'] ?: 'UNDETERMINED';
            $philhealth_status = $member['philhealth'] != 'N/A' && !empty($member['philhealth']) ? 'Yes' : 'No';
            $indigent_status = $member['is_indigent'] ? 'Yes' : 'No';
            $fourps_status = $member['is_4ps'] ? 'Yes' : 'No';
            
            // Print member row - EACH ROW REPRESENTS ONE MEMBER
            $pdf->Cell(8, 5, $member_count, 1, 0, 'C', $fill);
            $pdf->Cell(40, 5, substr($full_name, 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell(22, 5, $relationship, 1, 0, 'C', $fill);
            $pdf->Cell(8, 5, $member['age'], 1, 0, 'C', $fill);
            $pdf->Cell(8, 5, strtoupper(substr($member['sex'], 0, 1)), 1, 0, 'C', $fill);
            $pdf->Cell(22, 5, $birthdate, 1, 0, 'C', $fill);
            $pdf->Cell(18, 5, substr($member['civil_status'], 0, 10), 1, 0, 'C', $fill);
            $pdf->Cell(30, 5, substr($member['occupation'] ?: 'N/A', 0, 18), 1, 0, 'L', $fill);
            $pdf->Cell(22, 5, substr($member['contact'] ?: 'N/A', 0, 10), 1, 0, 'C', $fill);
            $pdf->Cell(15, 5, $philhealth_status, 1, 0, 'C', $fill);
            $pdf->Cell(12, 5, $indigent_status, 1, 0, 'C', $fill);
            $pdf->Cell(10, 5, $fourps_status, 1, 1, 'C', $fill);
            
            $fill = !$fill;
        }
        
        // VERIFY: Log printed member count
        error_log("Household #" . $household['house_number'] . " - Printed " . $member_count . " members");
        
        $pdf->Ln(3);
        
        // Add household summary
        $pdf->SetFont('helvetica', 'I', 6);
        $household_summary = 'Household ' . $household['house_number'] . ' Summary: ' . $member_count . ' member(s) - ' . 
                           getHouseholdCompositionSummary($household_members);
        $pdf->Cell(0, 4, $household_summary, 0, 1);
        $pdf->Ln(5);
    }
    
    // Final comprehensive summary footer
    error_log("PDF Generation Complete - Total Households: $total_households_printed, Total Residents: $total_residents_printed");
    
    $pdf->SetY(-20);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 6, 'BARANGAY BALAS - COMPLETE CENSUS DOCUMENT (INCLUDES ALL ' . $total_households_printed . ' HOUSEHOLDS WITH ALL ' . $total_residents_printed . ' RESIDENTS)', 0, 1, 'C');
    $pdf->Cell(0, 6, 'Coverage: ' . ($purok === 'all' ? 'All Puroks' : $purok) . ' | Generated: ' . date('F d, Y h:i A') . ' | Page ' . $pdf->getAliasNumPage() . ' of ' . $pdf->getAliasNbPages(), 0, 1, 'C');
}
function getHouseholdCompositionSummary($members) {
    $adults = 0;
    $children = 0;
    $seniors = 0;
    $male_count = 0;
    $female_count = 0;
    $head_found = false;
    $spouse_found = false;
    
    foreach ($members as $member) {
        $age = (int)$member['age'];
        if ($age < 18) {
            $children++;
        } elseif ($age >= 60) {
            $seniors++;
        } else {
            $adults++;
        }
        
        if ($member['sex'] === 'male') {
            $male_count++;
        } else {
            $female_count++;
        }
        
        if ($member['relationship'] === 'HEAD') $head_found = true;
        if ($member['relationship'] === 'SPOUSE') $spouse_found = true;
    }
    
    $parts = [];
    if ($head_found) $parts[] = 'Has Head';
    if ($spouse_found) $parts[] = 'Has Spouse';
    if ($adults > 0) $parts[] = $adults . ' Adult' . ($adults > 1 ? 's' : '');
    if ($children > 0) $parts[] = $children . ' Child' . ($children > 1 ? 'ren' : '');
    if ($seniors > 0) $parts[] = $seniors . ' Senior' . ($seniors > 1 ? 's' : '');
    $parts[] = $male_count . 'M/' . $female_count . 'F';
    
    return implode(', ', $parts);
}
function getStatisticsEnhanced() {
    global $conn;
    
    $query = "
        SELECT 
            COUNT(DISTINCT r.house_number) as total_households,
            COUNT(*) as total_residents,
            SUM(CASE WHEN r.sex = 'male' THEN 1 ELSE 0 END) as male_population,
            SUM(CASE WHEN r.sex = 'female' THEN 1 ELSE 0 END) as female_population,
            SUM(CASE WHEN r.age < 18 THEN 1 ELSE 0 END) as children,
            SUM(CASE WHEN r.age >= 18 AND r.age < 60 THEN 1 ELSE 0 END) as adults,
            SUM(CASE WHEN r.age >= 60 THEN 1 ELSE 0 END) as seniors,
            SUM(CASE WHEN r.is_indigent = 1 THEN 1 ELSE 0 END) as indigent_families,
            SUM(CASE WHEN r.is_4ps_member = 1 THEN 1 ELSE 0 END) as fourps_members,
            SUM(CASE WHEN r.philhealth_number IS NOT NULL AND r.philhealth_number != '' THEN 1 ELSE 0 END) as philhealth_members,
            SUM(CASE WHEN r.relationship_to_head = 'HEAD' THEN 1 ELSE 0 END) as household_heads,
            SUM(CASE WHEN r.relationship_to_head IS NULL OR r.relationship_to_head = '' THEN 1 ELSE 0 END) as undetermined_relationships
        FROM residents r
        INNER JOIN resident_accounts ra ON r.id = ra.resident_id
        WHERE ra.account_status = 'Approved' AND r.resident_status = 'Active'
    ";
    
    $result = mysqli_query($conn, $query);
    $stats = mysqli_fetch_assoc($result);
    
    echo json_encode($stats);
}
?>