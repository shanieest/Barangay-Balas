<?php
//generate_barangay_id.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

requireAuth();

// Check if LibreOffice is installed and available

function isLibreOfficeAvailable() {
    $libreOfficePaths = [
        'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
        'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
        '/usr/bin/libreoffice',
        '/usr/bin/soffice',
        '/usr/local/bin/libreoffice',
        '/Applications/LibreOffice.app/Contents/MacOS/soffice'
    ];
    
    foreach ($libreOfficePaths as $path) {
        if (file_exists($path)) {
            error_log("✓ Found LibreOffice at: " . $path);
            return $path;
        }
    }
    
    // Try to find it in system PATH
    $output = [];
    $returnVar = 0;
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec('where soffice 2>nul', $output, $returnVar);
    } else {
        exec('which libreoffice 2>/dev/null', $output, $returnVar);
        if ($returnVar !== 0) {
            exec('which soffice 2>/dev/null', $output, $returnVar);
        }
    }
    
    if ($returnVar === 0 && !empty($output[0])) {
        $path = trim($output[0]);
        error_log("✓ Found LibreOffice via PATH: " . $path);
        return $path;
    }
    
    error_log("✗ LibreOffice not found - will use DOCX format");
    return false;
}

/**
 * Convert DOCX to PDF using LibreOffice
 * Returns array with 'success' (bool), 'path' (string), and 'type' (pdf/docx)
 */
function convertDocxToPdf($docxPath, $outputDir) {
    $libreOfficePath = isLibreOfficeAvailable();
    
    if (!$libreOfficePath) {
        error_log("⚠ LibreOffice not available - keeping DOCX format");
        return [
            'success' => false,
            'path' => $docxPath,
            'type' => 'docx',
            'message' => 'LibreOffice not installed'
        ];
    }
    
    $absDocxPath = realpath($docxPath);
    $absOutputDir = realpath($outputDir);
    
    if (!$absDocxPath || !$absOutputDir) {
        error_log("✗ Cannot resolve absolute paths");
        return [
            'success' => false,
            'path' => $docxPath,
            'type' => 'docx',
            'message' => 'Path resolution failed'
        ];
    }
    
    error_log("=== PDF CONVERSION ATTEMPT ===");
    error_log("DOCX path: " . $absDocxPath);
    error_log("Output dir: " . $absOutputDir);
    
    // Build conversion command based on OS
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows command
        if (strpos($libreOfficePath, ' ') !== false && strpos($libreOfficePath, '"') === false) {
            $libreOfficePath = '"' . $libreOfficePath . '"';
        }
        $cmd = $libreOfficePath . ' --headless --convert-to pdf:writer_pdf_Export --outdir "' . 
               $absOutputDir . '" "' . $absDocxPath . '" 2>&1';
    } else {
        // Unix/Linux/Mac command
        $cmd = escapeshellarg($libreOfficePath) . 
               " --headless --convert-to pdf:writer_pdf_Export --outdir " . 
               escapeshellarg($absOutputDir) . " " . 
               escapeshellarg($absDocxPath) . " 2>&1";
    }
    
    error_log("Command: " . $cmd);
    
    exec($cmd, $output, $returnVar);
    
    error_log("Return code: " . $returnVar);
    error_log("Output: " . implode("\n", $output));
    
    // Generate expected PDF path
    $pdfPath = $outputDir . '/' . basename($docxPath, '.docx') . '.pdf';
    
    // Wait for PDF to be created (max 15 seconds)
    $maxWait = 15;
    $waited = 0;
    while (!file_exists($pdfPath) && $waited < $maxWait) {
        sleep(1);
        $waited++;
        error_log("Waiting for PDF... " . $waited . "s");
    }
    
    // Check if PDF was created successfully
    if ($returnVar === 0 && file_exists($pdfPath) && filesize($pdfPath) > 0) {
        error_log("✓ PDF conversion successful");
        error_log("PDF size: " . filesize($pdfPath) . " bytes");
        error_log("==============================");
        
        // Delete the temporary DOCX file
        if (file_exists($docxPath)) {
            @unlink($docxPath);
            error_log("✓ Temporary DOCX deleted");
        }
        
        return [
            'success' => true,
            'path' => $pdfPath,
            'type' => 'pdf',
            'message' => 'PDF generated successfully'
        ];
    } else {
        error_log("✗ PDF conversion failed");
        error_log("Falling back to DOCX format");
        error_log("==============================");
        
        return [
            'success' => false,
            'path' => $docxPath,
            'type' => 'docx',
            'message' => 'PDF conversion failed - using DOCX'
        ];
    }
}

// Check if export action is requested
if (isset($_GET['action']) && $_GET['action'] === 'export_excel') {
    exportToExcel($conn);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'yearly_report') {
    exportYearlyReport($conn);
    exit;
}

$application_id = $_GET['id'] ?? null;
if (!$application_id) {
    header("Location: ../pages/barangay_id_records.php?error=no_id");
    exit;
}

$query = "
SELECT a.id, a.resident_id, a.signature_path, a.photo_path, a.id_number, a.notes,
       r.first_name, r.middle_name, r.last_name, r.suffix, r.address,
       r.birthdate, r.contact_number, r.civil_status, r.sex, r.place_of_birth
FROM barangay_id_applications a
JOIN residents r ON a.resident_id = r.id
WHERE a.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header("Location: ../pages/barangay_id_records.php?error=application_not_found");
    exit;
}

$data = $res->fetch_assoc();
$stmt->close();

// Check if already approved
if (!empty($data['id_number'])) {
    header("Location: ../pages/barangay_id_records.php?error=already_approved&id=" . $application_id);
    exit;
}

// Generate ID number
$year = date('Y');
$countQuery = "SELECT COUNT(*) as count FROM barangay_id_applications WHERE id_number IS NOT NULL AND id_number LIKE ?";
$countStmt = $conn->prepare($countQuery);
$pattern = "BALAS-$year-%";
$countStmt->bind_param("s", $pattern);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countData = $countResult->fetch_assoc();
$nextNumber = $countData['count'] + 1;
$countStmt->close();

$idNumber = sprintf('BALAS-%d-%04d', $year, $nextNumber);

$templatePath = "../../services/barangayID/BARANGAY ID OFFICIAL.docx";
$outputDir = "../../uploads/digital_ids/";
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

if (!file_exists($templatePath)) {
    error_log("Template file not found: " . $templatePath);
    header("Location: ../pages/barangay_id_records.php?error=template_not_found");
    exit;
}

// Function to get absolute file path
function getAbsoluteFilePath($dbPath) {
    if (!$dbPath) return null;
    
    error_log("=== PATH RESOLUTION DEBUG ===");
    error_log("Input DB path: " . $dbPath);
    
    $projectRoot = realpath(__DIR__ . '/../../');
    error_log("Project root: " . $projectRoot);
    
    $cleanPath = ltrim($dbPath, '/\\');
    error_log("Clean path: " . $cleanPath);
    
    $possiblePaths = [
        $projectRoot . DIRECTORY_SEPARATOR . $cleanPath, 
        $projectRoot . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $cleanPath,
    ];
    
    foreach ($possiblePaths as $path) {
        error_log("Trying path: " . $path);
        if (file_exists($path)) {
            error_log("✓ FILE FOUND: " . $path);
            error_log("File size: " . filesize($path) . " bytes");
            return $path;
        }
    }
    
    error_log("✗ FILE NOT FOUND in any location");
    error_log("===========================");
    return null;
}

// Fill the .docx template
try {
    $template = new TemplateProcessor($templatePath);
    
    // Get available template variables
    $templateVariables = $template->getVariables();
    error_log("=== TEMPLATE VARIABLES ===");
    error_log("Available: " . implode(', ', $templateVariables));
    error_log("==========================");

    // Set basic information
    $template->setValue('firstName', $data['first_name']);
    $template->setValue('mName', $data['middle_name'] ?? '');
    $template->setValue('LastName', $data['last_name']);
    $template->setValue('suffix', $data['suffix'] ?? '');
    $template->setValue('address', $data['address']);
    $template->setValue('birthdate', date('F d, Y', strtotime($data['birthdate'])));
    $template->setValue('contactNumber', $data['contact_number']);
    $template->setValue('idNumber', $idNumber);
    $template->setValue('year', $year);
    $template->setValue('birthPlace', $data['place_of_birth'] ?? 'N/A');
    $template->setValue('civilStatus', $data['civil_status'] ?? 'N/A');
    $template->setValue('sex', ucfirst($data['sex']));

    // Calculate age
    $birthdate = new DateTime($data['birthdate']);
    $today = new DateTime();
    $age = $birthdate->diff($today)->y;
    $template->setValue('age', $age);

    // Handle formal photo
    if ($data['photo_path']) {
        $photoAbsolutePath = getAbsoluteFilePath($data['photo_path']);
        
        if ($photoAbsolutePath && file_exists($photoAbsolutePath)) {
            try {
                error_log("=== PHOTO INSERTION ===");
                error_log("Inserting photo from: " . $photoAbsolutePath);
                
                if (in_array('formalPhoto', $templateVariables)) {
                    $template->setImageValue('formalPhoto', [
                        'path' => $photoAbsolutePath,
                        'width' => 120,
                        'height' => 120,
                        'ratio' => true
                    ]);
                    error_log("✓ Photo inserted successfully using 'formalPhoto'");
                } else {
                    error_log("✗ 'formalPhoto' placeholder not found in template!");
                    $template->setValue('formalPhoto', '[PHOTO]');
                }
                error_log("======================");
            } catch (Exception $e) {
                error_log("Photo insertion error: " . $e->getMessage());
                $template->setValue('formalPhoto', '[Photo Error: ' . $e->getMessage() . ']');
            }
        } else {
            error_log("Photo file not accessible");
            $template->setValue('formalPhoto', '[Photo Not Found]');
        }
    } else {
        error_log("No photo path in database");
        $template->setValue('formalPhoto', '[No Photo]');
    }

    // Handle signature
    if ($data['signature_path']) {
        $signatureAbsolutePath = getAbsoluteFilePath($data['signature_path']);
        
        if ($signatureAbsolutePath && file_exists($signatureAbsolutePath)) {
            try {
                error_log("=== SIGNATURE INSERTION ===");
                error_log("Inserting signature from: " . $signatureAbsolutePath);
                
                if (in_array('signature', $templateVariables)) {
                    $template->setImageValue('signature', [
                        'path' => $signatureAbsolutePath,
                        'width' => 120,
                        'height' => 40,
                        'ratio' => true
                    ]);
                    error_log("✓ Signature inserted successfully");
                } else {
                    error_log("✗ 'signature' placeholder not found in template!");
                    $template->setValue('signature', '[SIGNATURE]');
                }
                error_log("===========================");
            } catch (Exception $e) {
                error_log("Signature insertion error: " . $e->getMessage());
                $template->setValue('signature', '[Signature Error: ' . $e->getMessage() . ']');
            }
        } else {
            error_log("Signature file not accessible");
            $template->setValue('signature', '[Signature Not Found]');
        }
    } else {
        error_log("No signature path in database");
        $template->setValue('signature', '[No Signature]');
    }

    // Save the filled DOCX template
    $timestamp = time();
    $docxFile = $outputDir . "barangay_id_" . $data['resident_id'] . "_" . $timestamp . ".docx";
    $template->saveAs($docxFile);
    
    error_log("✓ DOCX template saved successfully: " . $docxFile);

} catch (Exception $e) {
    error_log("✗ Template processing error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    header("Location: ../pages/barangay_id_records.php?error=template_processing_failed&message=" . urlencode($e->getMessage()));
    exit;
}

// Try PDF conversion with fallback to DOCX
$conversionResult = convertDocxToPdf($docxFile, $outputDir);

$finalFile = $conversionResult['path'];
$fileType = $conversionResult['type'];
$finalFileBasename = basename($finalFile);
$pdfPathDB = "uploads/digital_ids/" . $finalFileBasename;

error_log("=== FINAL FILE INFO ===");
error_log("File type: " . $fileType);
error_log("File path: " . $finalFile);
error_log("DB path: " . $pdfPathDB);
error_log("File exists: " . (file_exists($finalFile) ? 'Yes' : 'No'));
if (file_exists($finalFile)) {
    error_log("File size: " . filesize($finalFile) . " bytes");
}
error_log("======================");

// Update database
$valid_until = "$year-12-31";
$admin_id = $_SESSION['admin_id'] ?? 1;

error_log("=== DATABASE UPDATE ===");
error_log("Saving path to DB: " . $pdfPathDB);
error_log("ID Number: " . $idNumber);
error_log("File Type: " . $fileType);

// Add a note about the file format if DOCX was used
$approvalNotes = $data['notes'] ?? '';
if ($fileType === 'docx') {
    $formatNote = "\n[System Note: Generated as DOCX format - PDF conversion unavailable]";
    $approvalNotes .= $formatNote;
}

$update = $conn->prepare("
    UPDATE barangay_id_applications 
    SET status='Approved', valid_until=?, digital_id_path=?, id_number=?, 
        date_processed=NOW(), processed_by=?, notes=?
    WHERE id=?
");
$update->bind_param("sssisi", $valid_until, $pdfPathDB, $idNumber, $admin_id, $approvalNotes, $application_id);

if ($update->execute()) {
    $update->close();
    error_log("✓ Database updated successfully");
    error_log("======================");
    
    // Add success message with format info
    $successParam = $fileType === 'pdf' ? 'approved' : 'approved_docx';
    header("Location: ../pages/barangay_id_records.php?success=" . $successParam . "&id=" . $application_id);
} else {
    error_log("✗ Database update error: " . $update->error);
    error_log("======================");
    header("Location: ../pages/barangay_id_records.php?error=update_failed&message=" . urlencode($update->error));
}
exit;

// EXPORT TO EXCEL FUNCTION
function exportToExcel($conn) {
    try {
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $status_filter = $_GET['status'] ?? 'all';
        
        $query = "
        SELECT a.id, r.first_name, r.middle_name, r.last_name, r.suffix, 
               a.status, a.application_date, a.valid_until, a.digital_id_path, 
               a.id_number, r.contact_number, a.notes, r.address, r.birthdate, 
               r.civil_status, r.place_of_birth, r.sex, r.email, r.occupation
        FROM barangay_id_applications a
        JOIN residents r ON a.resident_id = r.id
        ";
        
        if ($status_filter !== 'all') {
            $query .= " WHERE a.status = ?";
        }
        
        $query .= " ORDER BY a.application_date DESC";
        
        if ($status_filter !== 'all') {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $status_filter);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($query);
        }
        
        if (!$result || $result->num_rows === 0) {
            throw new Exception('No data found for export');
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $spreadsheet->getProperties()
            ->setCreator("Barangay Balas Management System")
            ->setTitle("Barangay ID Applications Export")
            ->setSubject("Barangay ID Applications Data")
            ->setDescription("Export of Barangay ID applications from " . date('Y-m-d'));
        
        $headers = [
            'A' => 'Application ID',
            'B' => 'ID Number',
            'C' => 'Full Name',
            'D' => 'First Name',
            'E' => 'Middle Name',
            'F' => 'Last Name',
            'G' => 'Suffix',
            'H' => 'Address',
            'I' => 'Birthdate',
            'J' => 'Age',
            'K' => 'Sex',
            'L' => 'Civil Status',
            'M' => 'Place of Birth',
            'N' => 'Contact Number',
            'O' => 'Email',
            'P' => 'Occupation',
            'Q' => 'Application Status',
            'R' => 'Application Date',
            'S' => 'Valid Until',
            'T' => 'Notes'
        ];
        
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setARGB('FFFFFFFF');
        }
        
        $row = 2;
        while ($data = $result->fetch_assoc()) {
            $birthdate = new DateTime($data['birthdate']);
            $today = new DateTime();
            $age = $birthdate->diff($today)->y;
            
            $fullName = trim($data['first_name'] . ' ' . 
                       ($data['middle_name'] ? $data['middle_name'] . ' ' : '') . 
                       $data['last_name'] . ' ' .
                       ($data['suffix'] ? $data['suffix'] : ''));
            
            $sheet->setCellValue('A' . $row, $data['id']);
            $sheet->setCellValue('B' . $row, $data['id_number'] ?: 'Not Set');
            $sheet->setCellValue('C' . $row, $fullName);
            $sheet->setCellValue('D' . $row, $data['first_name']);
            $sheet->setCellValue('E' . $row, $data['middle_name']);
            $sheet->setCellValue('F' . $row, $data['last_name']);
            $sheet->setCellValue('G' . $row, $data['suffix']);
            $sheet->setCellValue('H' . $row, $data['address']);
            $sheet->setCellValue('I' . $row, date('M d, Y', strtotime($data['birthdate'])));
            $sheet->setCellValue('J' . $row, $age);
            $sheet->setCellValue('K' . $row, ucfirst($data['sex']));
            $sheet->setCellValue('L' . $row, $data['civil_status']);
            $sheet->setCellValue('M' . $row, $data['place_of_birth']);
            $sheet->setCellValue('N' . $row, $data['contact_number']);
            $sheet->setCellValue('O' . $row, $data['email']);
            $sheet->setCellValue('P' . $row, $data['occupation']);
            $sheet->setCellValue('Q' . $row, $data['status']);
            $sheet->setCellValue('R' . $row, date('M d, Y', strtotime($data['application_date'])));
            $sheet->setCellValue('S' . $row, $data['valid_until'] ? date('M d, Y', strtotime($data['valid_until'])) : 'N/A');
            $sheet->setCellValue('T' . $row, $data['notes'] ?: 'None');
            
            $statusCell = 'Q' . $row;
            switch ($data['status']) {
                case 'Approved':
                    $sheet->getStyle($statusCell)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FF90EE90');
                    break;
                case 'Pending':
                    $sheet->getStyle($statusCell)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFFD700');
                    break;
                case 'Rejected':
                    $sheet->getStyle($statusCell)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFF6B6B');
                    break;
            }
            
            $row++;
        }
        
        foreach (range('A', 'T') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $sheet->setAutoFilter('A1:T' . ($row - 1));
        
        $summaryRow = $row + 2;
        $sheet->setCellValue('A' . $summaryRow, 'Export Summary');
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(14);
        
        $sheet->setCellValue('A' . ($summaryRow + 1), 'Total Records:');
        $sheet->setCellValue('B' . ($summaryRow + 1), $row - 2);
        $sheet->getStyle('A' . ($summaryRow + 1))->getFont()->setBold(true);
        
        $sheet->setCellValue('A' . ($summaryRow + 2), 'Export Date:');
        $sheet->setCellValue('B' . ($summaryRow + 2), date('Y-m-d H:i:s'));
        $sheet->getStyle('A' . ($summaryRow + 2))->getFont()->setBold(true);
        
        $sheet->setCellValue('A' . ($summaryRow + 3), 'Filter Applied:');
        $sheet->setCellValue('B' . ($summaryRow + 3), $status_filter === 'all' ? 'All Applications' : ucfirst($status_filter));
        $sheet->getStyle('A' . ($summaryRow + 3))->getFont()->setBold(true);
        
        $filename = "Barangay_ID_Applications_" . date('Y-m-d_His') . ".xlsx";
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        
        if (isset($stmt)) {
            $stmt->close();
        }
        exit;
        
    } catch (Exception $e) {
        error_log("Excel Export Error: " . $e->getMessage());
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ]);
        } else {
            header("Location: barangay_id_records.php?error=export_failed&message=" . urlencode($e->getMessage()));
        }
        exit;
    }
}

// EXPORT YEARLY REPORT FUNCTION
function exportYearlyReport($conn) {
    try {
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $year = $_GET['year'] ?? date('Y');
        $report_type = $_GET['report_type'] ?? 'summary';
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $spreadsheet->getProperties()
            ->setCreator("Barangay Balas Management System")
            ->setTitle("Barangay ID Yearly Report $year")
            ->setSubject("Barangay ID Applications - Year $year");
        
        if ($report_type === 'summary') {
            $sheet->setCellValue('A1', "Barangay ID Applications - Yearly Summary Report $year");
            $sheet->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            
            $headers = ['Month', 'Total Applications', 'Approved', 'Pending', 'Rejected', 'Approval Rate'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '3', $header);
                $sheet->getStyle($col . '3')->getFont()->setBold(true);
                $col++;
            }
            
            $query = "
                SELECT 
                    MONTH(application_date) as month,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
                FROM barangay_id_applications
                WHERE YEAR(application_date) = ?
                GROUP BY MONTH(application_date)
                ORDER BY MONTH(application_date)
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $row = 4;
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            while ($data = $result->fetch_assoc()) {
                $monthName = $months[$data['month'] - 1];
                $approvalRate = $data['total'] > 0 ? round(($data['approved'] / $data['total']) * 100, 2) : 0;
                
                $sheet->setCellValue('A' . $row, $monthName);
                $sheet->setCellValue('B' . $row, $data['total']);
                $sheet->setCellValue('C' . $row, $data['approved']);
                $sheet->setCellValue('D' . $row, $data['pending']);
                $sheet->setCellValue('E' . $row, $data['rejected']);
                $sheet->setCellValue('F' . $row, $approvalRate . '%');
                
                $row++;
            }
            
            foreach (range('A', 'F') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
        } else {
            $query = "
                SELECT a.*, r.first_name, r.middle_name, r.last_name, r.contact_number, r.address
                FROM barangay_id_applications a
                JOIN residents r ON a.resident_id = r.id
                WHERE YEAR(a.application_date) = ?
                ORDER BY a.application_date DESC
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $headers = ['ID Number', 'Name', 'Contact', 'Status', 'Application Date', 'Valid Until'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $col++;
            }
            
            $row = 2;
            while ($data = $result->fetch_assoc()) {
                $fullName = $data['first_name'] . ' ' . ($data['middle_name'] ? $data['middle_name'][0] . '. ' : '') . $data['last_name'];
                
                $sheet->setCellValue('A' . $row, $data['id_number'] ?: 'Not Set');
                $sheet->setCellValue('B' . $row, $fullName);
                $sheet->setCellValue('C' . $row, $data['contact_number']);
                $sheet->setCellValue('D' . $row, $data['status']);
                $sheet->setCellValue('E' . $row, date('M d, Y', strtotime($data['application_date'])));
                $sheet->setCellValue('F' . $row, $data['valid_until'] ? date('M d, Y', strtotime($data['valid_until'])) : 'N/A');
                
                $row++;
            }
            
            foreach (range('A', 'F') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }
        
        $filename = "Barangay_ID_Report_{$year}_" . date('Y-m-d_His') . ".xlsx";
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        
        exit;
        
    } catch (Exception $e) {
        error_log("Yearly Report Export Error: " . $e->getMessage());
        header("Location: barangay_id_records.php?error=export_failed&message=" . urlencode($e->getMessage()));
        exit;
    }
}
?>