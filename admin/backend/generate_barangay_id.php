<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

requireAuth();

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

// absolute path function
function getAbsoluteFilePath($dbPath) {
    if (!$dbPath) return null;
    
    error_log("=== PATH RESOLUTION DEBUG ===");
    error_log("Input DB path: " . $dbPath);
    
    
    $projectRoot = realpath(__DIR__ . '/../../');
    error_log("Project root: " . $projectRoot);
    
    // Clean the path
    $cleanPath = ltrim($dbPath, '/\\');
    error_log("Clean path: " . $cleanPath);
    
    // Try multiple possible locations
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
    
    error_log(" FILE NOT FOUND in any location");
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

    // Handle formal photo - EXACT MATCH TO YOUR TEMPLATE
    if ($data['photo_path']) {
        $photoAbsolutePath = getAbsoluteFilePath($data['photo_path']);
        
        if ($photoAbsolutePath && file_exists($photoAbsolutePath)) {
            try {
                error_log("=== PHOTO INSERTION ===");
                error_log("Inserting photo from: " . $photoAbsolutePath);
                
                // TEMPLATE USES: $formalPhoto (case-sensitive!)
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
                    error_log("Available variables: " . implode(', ', $templateVariables));
                    $template->setValue('formalPhoto', '[PHOTO]');
                }
                error_log("======================");
            } catch (Exception $e) {
                error_log("Photo insertion error: " . $e->getMessage());
                $template->setValue('formalPhoto', '[Photo Error: ' . $e->getMessage() . ']');
            }
        } else {
            error_log("Photo file not accessible: " . ($photoAbsolutePath ?: 'Path resolution failed'));
            error_log("DB path was: " . $data['photo_path']);
            $template->setValue('formalPhoto', '[Photo Not Found]');
        }
    } else {
        error_log("No photo path in database");
        $template->setValue('formalPhoto', '[No Photo]');
    }

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
                    error_log("✓ Signature inserted successfully using 'signature'");
                } else {
                    error_log("✗ 'signature' placeholder not found in template!");
                    error_log("Available variables: " . implode(', ', $templateVariables));
                    $template->setValue('signature', '[SIGNATURE]');
                }
                error_log("===========================");
            } catch (Exception $e) {
                error_log("Signature insertion error: " . $e->getMessage());
                $template->setValue('signature', '[Signature Error: ' . $e->getMessage() . ']');
            }
        } else {
            error_log("Signature file not accessible: " . ($signatureAbsolutePath ?: 'Path resolution failed'));
            error_log("DB path was: " . $data['signature_path']);
            $template->setValue('signature', '[Signature Not Found]');
        }
    } else {
        error_log("No signature path in database");
        $template->setValue('signature', '[No Signature]');
    }

    // Save the filled DOCX template
    $docxFile = $outputDir . "barangay_id_" . $data['resident_id'] . "_" . time() . ".docx";
    $template->saveAs($docxFile);
    
    error_log("DOCX template saved successfully: " . $docxFile);

} catch (Exception $e) {
    error_log("Template processing error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    header("Location: ../pages/barangay_id_records.php?error=template_processing_failed&message=" . urlencode($e->getMessage()));
    exit;
}

// PDF conversion with better error handling
$pdfFile = $outputDir . "barangay_id_" . $data['resident_id'] . "_" . time() . ".pdf";

// Try multiple possible LibreOffice paths
$libreOfficePaths = [
    '/usr/bin/libreoffice',
    '/usr/bin/soffice',
    'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
    'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
    '/Applications/LibreOffice.app/Contents/MacOS/soffice'
];

$libreOfficePath = null;
foreach ($libreOfficePaths as $path) {
    if (file_exists($path)) {
        $libreOfficePath = $path;
        error_log("Found LibreOffice at: " . $path);
        break;
    }
}

if (!$libreOfficePath) {
    exec('which libreoffice 2>/dev/null', $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $libreOfficePath = trim($output[0]);
    } else {
        exec('which soffice 2>/dev/null', $output, $return_var);
        if ($return_var === 0 && !empty($output[0])) {
            $libreOfficePath = trim($output[0]);
        }
    }
}

if ($libreOfficePath) {
    $absDocxFile = realpath($docxFile);
    $absOutputDir = realpath($outputDir);
    
    if (!$absDocxFile || !$absOutputDir) {
        error_log("Cannot get absolute paths for conversion");
        error_log("DOCX file: " . $docxFile);
        error_log("Output dir: " . $outputDir);
        header("Location: ../pages/barangay_id_records.php?error=path_error");
        exit;
    }
    
    // Enhanced conversion command
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $cmd = '"' . $libreOfficePath . '"' . 
               ' --headless --convert-to pdf:writer_pdf_Export --outdir "' . $absOutputDir . '"' . 
               ' "' . $absDocxFile . '" 2>&1';
    } else {
        $cmd = escapeshellarg($libreOfficePath) . 
               " --headless --convert-to pdf:writer_pdf_Export --outdir " . 
               escapeshellarg($absOutputDir) . " " . 
               escapeshellarg($absDocxFile) . " 2>&1";
    }
    
    error_log("=== PDF CONVERSION ===");
    error_log("Command: " . $cmd);
    exec($cmd, $output, $return_var);
    error_log("Return code: " . $return_var);
    error_log("Output: " . implode("\n", $output));
    
    // Wait for conversion
    $maxWait = 15;
    $waited = 0;
    while (!file_exists($pdfFile) && $waited < $maxWait) {
        sleep(1);
        $waited++;
        error_log("Waiting for PDF... " . $waited . "s");
    }
    
    if ($return_var === 0 && file_exists($pdfFile) && filesize($pdfFile) > 0) {
        if (file_exists($docxFile)) {
            unlink($docxFile);
        }
        
        $pdfPathDB = "uploads/digital_ids/" . basename($pdfFile);
        error_log("✓ PDF conversion successful. DB path: " . $pdfPathDB);
        error_log("PDF size: " . filesize($pdfFile) . " bytes");
        $finalFile = $pdfFile;
    } else {
        error_log("✗ PDF conversion failed. Using DOCX fallback.");
        $finalFile = $docxFile;
        $pdfPathDB = "uploads/digital_ids/" . basename($docxFile);
    }
    error_log("=====================");
} else {
    error_log("LibreOffice not found. Using DOCX file.");
    $finalFile = $docxFile;
    $pdfPathDB = "uploads/digital_ids/" . basename($docxFile);
}

// Update database
$valid_until = "$year-12-31";
$admin_id = $_SESSION['admin_id'] ?? 1;

error_log("=== DATABASE UPDATE ===");
error_log("Saving path to DB: " . $pdfPathDB);
error_log("ID Number: " . $idNumber);

$update = $conn->prepare("
    UPDATE barangay_id_applications 
    SET status='Approved', valid_until=?, digital_id_path=?, id_number=?, date_processed=NOW(), processed_by=?
    WHERE id=?
");
$update->bind_param("sssii", $valid_until, $pdfPathDB, $idNumber, $admin_id, $application_id);

if ($update->execute()) {
    $update->close();
    error_log("✓ Database updated successfully");
    error_log("======================");
    header("Location: ../pages/barangay_id_records.php?success=approved&id=" . $application_id);
} else {
    error_log("✗ Database update error: " . $update->error);
    error_log("======================");
    header("Location: ../pages/barangay_id_records.php?error=update_failed&message=" . urlencode($update->error));
}
exit;

// Set ID number and update application status
$valid_until = "$year-12-31";
$admin_id = $_SESSION['admin_id'] ?? 1;
$update = $conn->prepare("
    UPDATE barangay_id_applications 
    SET status='Approved', valid_until=?, digital_id_path=?, id_number=?, date_processed=NOW(), processed_by=?
    WHERE id=?
");
$update->bind_param("sssii", $valid_until, $pdfPathDB, $idNumber, $admin_id, $application_id);

if ($update->execute()) {
    $update->close();
    header("Location: ../pages/barangay_id_records.php?success=approved");
} else {
    error_log("Database update error: " . $update->error);
    header("Location: barangay_id_records.php?error=update_failed");
}
exit;

// Function to verify PDF creation
function verifyPDFCreation($pdfPath, $docxPath) {
    error_log("=== PDF VERIFICATION ===");
    error_log("PDF Path: " . $pdfPath);
    error_log("PDF Exists: " . (file_exists($pdfPath) ? 'YES' : 'NO'));
    
    if (file_exists($pdfPath)) {
        error_log("PDF Size: " . filesize($pdfPath) . " bytes");
        error_log("PDF Readable: " . (is_readable($pdfPath) ? 'YES' : 'NO'));
        
        // Check if it's a valid PDF file
        $fileHeader = file_get_contents($pdfPath, false, null, 0, 5);
        error_log("File Header: " . bin2hex($fileHeader));
        error_log("Is PDF: " . (strpos($fileHeader, '%PDF-') === 0 ? 'YES' : 'NO'));
        
        return filesize($pdfPath) > 0 && strpos($fileHeader, '%PDF-') === 0;
    }
    
    error_log("DOCX Fallback Path: " . $docxPath);
    error_log("DOCX Exists: " . (file_exists($docxPath) ? 'YES' : 'NO'));
    
    return file_exists($docxPath);
}



if ($data['photo_path']) {
    $photoAbsolutePath = getAbsoluteFilePath($data['photo_path']);
    
    if ($photoAbsolutePath) {
        try {
            $photoPlaceholders = ['photo', 'formalPhoto', 'profilePhoto', 'picture', 'userPhoto'];
            $photoInserted = false;
            
            foreach ($photoPlaceholders as $placeholder) {
                if (in_array($placeholder, $templateVariables)) {
                    error_log("Found photo placeholder: " . $placeholder);
                    $template->setImageValue($placeholder, [
                        'path' => $photoAbsolutePath,
                        'width' => 120,
                        'height' => 120,
                        'ratio' => true
                    ]);
                    $photoInserted = true;
                    error_log("Successfully inserted photo for placeholder: " . $placeholder);
                    break;
                }
            }
            
            if (!$photoInserted) {
                error_log("No image placeholder found for photo");
                $template->setValue('photo', '[PHOTO INSERTED]');
            }
            
        } catch (Exception $e) {
            error_log("Photo insertion error: " . $e->getMessage());
            $template->setValue('photo', '[Photo Error]');
        }
    } else {
        error_log("Photo file not found");
        $template->setValue('photo', '[Photo Not Found]');
    }
}

if ($data['signature_path']) {
    $signatureAbsolutePath = getAbsoluteFilePath($data['signature_path']);
    
    if ($signatureAbsolutePath) {
        try {
            $signaturePlaceholders = ['signature', 'digitalSignature', 'sig', 'userSignature'];
            $signatureInserted = false;
            
            foreach ($signaturePlaceholders as $placeholder) {
                if (in_array($placeholder, $templateVariables)) {
                    error_log("Found signature placeholder: " . $placeholder);
                    $template->setImageValue($placeholder, [
                        'path' => $signatureAbsolutePath,
                        'width' => 120,
                        'height' => 40,
                        'ratio' => true
                    ]);
                    $signatureInserted = true;
                    error_log("Successfully inserted signature for placeholder: " . $placeholder);
                    break;
                }
            }
            
            if (!$signatureInserted) {
                error_log("No signature placeholder found");
                $template->setValue('signature', '[SIGNATURE INSERTED]');
            }
            
        } catch (Exception $e) {
            error_log("Signature insertion error: " . $e->getMessage());
            $template->setValue('signature', '[Signature Error]');
        }
    } else {
        error_log("Signature file not found");
        $template->setValue('signature', '[Signature Not Found]');
    }
}


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