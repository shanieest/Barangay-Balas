<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php';
require_once __DIR__ . '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'export_household':
        exportHouseholdData();
        break;
    case 'get_household_data':
        getHouseholdData();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function exportHouseholdData() {
    global $conn;
    
    $resident_id = $_SESSION['user_id'];
    
    $resident_query = "SELECT house_number, purok FROM residents WHERE id = ?";
    $stmt = mysqli_prepare($conn, $resident_query);
    mysqli_stmt_bind_param($stmt, "i", $resident_id);
    mysqli_stmt_execute($stmt);
    $resident_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if (!$resident_info) {
        echo json_encode(['error' => 'Resident not found']);
        return;
    }
    
    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('Barangay Balas Management System')
        ->setTitle('My Household Census Report')
        ->setDescription('Personal Household Census Data');
    
    // Title
    $sheet->setCellValue('A1', 'MY HOUSEHOLD CENSUS REPORT');
    $sheet->mergeCells('A1:J1');
    $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Household Info
    $sheet->setCellValue('A2', 'House Number: #' . $resident_info['house_number'] . ', ' . $resident_info['purok'] . ', Barangay Balas');
    $sheet->mergeCells('A2:J2');
    $sheet->getStyle('A2')->getFont()->setSize(12)->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Date
    $sheet->setCellValue('A3', 'Generated on: ' . date('F d, Y h:i A'));
    $sheet->mergeCells('A3:J3');
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Headers
    $headers = [
        'A5' => 'Full Name',
        'B5' => 'Relationship to Head',
        'C5' => 'Age',
        'D5' => 'Sex',
        'E5' => 'Civil Status',
        'F5' => 'Educational Attainment',
        'G5' => 'Occupation',
        'H5' => 'Contact Number',
        'I5' => 'Email',
        'J5' => 'PhilHealth Status'
    ];
    
    foreach ($headers as $cell => $header) {
        $sheet->setCellValue($cell, $header);
    }
    
    // Style headers
    $sheet->getStyle('A5:J5')->getFont()->setBold(true);
    $sheet->getStyle('A5:J5')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('4472C4');
    $sheet->getStyle('A5:J5')->getFont()->getColor()->setRGB('FFFFFF');
    $sheet->getStyle('A5:J5')->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
    
    // Get household members - ONLY BY HOUSE NUMBER
    $query = "
        SELECT 
            r.*,
            CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) as full_name
        FROM residents r
        WHERE house_number = ? 
        AND resident_status = 'Active'
        ORDER BY 
            CASE 
                WHEN relationship_to_head = 'Head of Household' THEN 0
                WHEN relationship_to_head = 'Spouse' THEN 1
                ELSE 2
            END,
            age DESC
    ";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $resident_info['house_number']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $row = 6;
    $member_count = 0;
    
    while ($data = mysqli_fetch_assoc($result)) {
        $member_count++;
        
        // Get relationship from database
        $relationship = $data['relationship_to_head'] ?: 'MEMBER';
        
        // PhilHealth status
        $philhealth_status = 'Not Registered';
        if (!empty($data['philhealth_number'])) {
            $philhealth_status = 'Member';
        } elseif ($data['age'] < 18) {
            $philhealth_status = 'Dependent';
        }
        
        // Fill data
        $sheet->setCellValue("A$row", $data['full_name']);
        $sheet->setCellValue("B$row", $relationship);
        $sheet->setCellValue("C$row", $data['age']);
        $sheet->setCellValue("D$row", ucfirst($data['sex']));
        $sheet->setCellValue("E$row", ucfirst($data['civil_status']) ?: 'Single');
        $sheet->setCellValue("F$row", $data['educational_attainment'] ?: 'N/A');
        $sheet->setCellValue("G$row", $data['occupation'] ?: 'N/A');
        $sheet->setCellValue("H$row", $data['contact_number']);
        $sheet->setCellValue("I$row", $data['email'] ?: 'N/A');
        $sheet->setCellValue("J$row", $philhealth_status);
        
        // Highlight head of household
        if ($relationship === 'Head of Household') {
            $sheet->getStyle("A$row:J$row")->getFont()->setBold(true);
            $sheet->getStyle("A$row:J$row")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E7F3FF');
        }
        
        $row++;
    }
    
    // Add borders to data
    if ($member_count > 0) {
        $sheet->getStyle("A6:J" . ($row - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
    }
    
    // Summary section
    $summary_row = $row + 2;
    $sheet->setCellValue("A$summary_row", 'HOUSEHOLD SUMMARY');
    $sheet->mergeCells("A$summary_row:D$summary_row");
    $sheet->getStyle("A$summary_row")->getFont()->setBold(true)->setSize(14);
    
    // Calculate statistics
    $adults = 0;
    $children = 0;
    $working_members = 0;
    $males = 0;
    $females = 0;
    
    mysqli_data_seek($result, 0);
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $resident_info['house_number']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($member = mysqli_fetch_assoc($result)) {
        if ($member['age'] >= 18) {
            $adults++;
            if (!empty($member['occupation']) && 
                strtolower($member['occupation']) !== 'student' && 
                strtolower($member['occupation']) !== 'n/a') {
                $working_members++;
            }
        } else {
            $children++;
        }
        
        if (strtolower($member['sex']) === 'male') {
            $males++;
        } else {
            $females++;
        }
    }
    
    $summary_row++;
    $sheet->setCellValue("A$summary_row", 'Total Family Members:');
    $sheet->setCellValue("B$summary_row", $member_count);
    $summary_row++;
    $sheet->setCellValue("A$summary_row", 'Adults (18+ years):');
    $sheet->setCellValue("B$summary_row", $adults);
    $summary_row++;
    $sheet->setCellValue("A$summary_row", 'Children (Under 18):');
    $sheet->setCellValue("B$summary_row", $children);
    $summary_row++;
    $sheet->setCellValue("A$summary_row", 'Working Members:');
    $sheet->setCellValue("B$summary_row", $working_members);
    $summary_row++;
    $sheet->setCellValue("A$summary_row", 'Male Members:');
    $sheet->setCellValue("B$summary_row", $males);
    $summary_row++;
    $sheet->setCellValue("A$summary_row", 'Female Members:');
    $sheet->setCellValue("B$summary_row", $females);
    
    // Auto-size columns
    foreach (range('A', 'J') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Footer
    $footer_row = $summary_row + 3;
    $sheet->setCellValue("A$footer_row", 'This document is generated electronically by the Barangay Balas Management System.');
    $sheet->mergeCells("A$footer_row:J$footer_row");
    $sheet->getStyle("A$footer_row")->getFont()->setItalic(true)->setSize(10);
    $sheet->getStyle("A$footer_row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Output file
    $filename = 'My_Household_Census_' . $resident_info['house_number'] . '_' . date('Y-m-d') . '.xlsx';
    
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

function getHouseholdData() {
    global $conn;
    
    $resident_id = $_SESSION['user_id'];
    
    // Get current resident's house info
    $resident_query = "SELECT house_number, purok FROM residents WHERE id = ?";
    $stmt = mysqli_prepare($conn, $resident_query);
    mysqli_stmt_bind_param($stmt, "i", $resident_id);
    mysqli_stmt_execute($stmt);
    $resident_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if (!$resident_info) {
        echo json_encode(['error' => 'Resident not found']);
        return;
    }
    
    // Get all household members - ONLY BY HOUSE NUMBER
    $query = "
        SELECT 
            r.*,
            CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) as full_name
        FROM residents r
        WHERE house_number = ? 
        AND resident_status = 'Active'
        ORDER BY 
            CASE 
                WHEN relationship_to_head = 'Head of Household' THEN 0
                WHEN relationship_to_head = 'Spouse' THEN 1
                ELSE 2
            END,
            age DESC
    ";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $resident_info['house_number']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $members = [];
    
    while ($member = mysqli_fetch_assoc($result)) {
        $relationship = $member['relationship_to_head'] ?: 'MEMBER';
        
        $members[] = [
            'id' => $member['id'],
            'full_name' => $member['full_name'],
            'age' => $member['age'],
            'sex' => ucfirst($member['sex']),
            'civil_status' => ucfirst($member['civil_status']) ?: 'Single',
            'occupation' => $member['occupation'] ?: 'N/A',
            'educational_attainment' => $member['educational_attainment'] ?: 'N/A',
            'contact_number' => $member['contact_number'],
            'email' => $member['email'] ?: 'N/A',
            'philhealth_status' => !empty($member['philhealth_number']) ? 'Member' : ($member['age'] < 18 ? 'Dependent' : 'Not Registered'),
            'relationship' => $relationship,
            'is_head' => $relationship === 'Head of Household'
        ];
    }
    
    echo json_encode([
        'house_number' => $resident_info['house_number'],
        'purok' => $resident_info['purok'],
        'members' => $members,
        'total_members' => count($members)
    ]);
}
?>