<?php
require '../includes/db.php';
require '../includes/auth.php';
require '../../vendor/autoload.php';
require_once  '../../lib/phpqrcode/qrlib.php';
require_once  '../../config/emailer.php';
require_once  '../../email_templates/document_status.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_report') {
    generateReport($conn);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_report_data') {
    getReportData($conn);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['download_report'])) {
    downloadReport();
    exit;
}
function formatIssuedDate($date = null) {
    if ($date === null) {
        $date = time();
    }
    
    if (is_string($date)) {
        $date = strtotime($date);
    }
    
    $day = date('j', $date);
    $month = date('F', $date);
    $year = date('Y', $date);
    
    // Add ordinal suffix
    if ($day == 1 || $day == 21 || $day == 31) {
        $ordinal = 'st';
    } elseif ($day == 2 || $day == 22) {
        $ordinal = 'nd';
    } elseif ($day == 3 || $day == 23) {
        $ordinal = 'rd';
    } else {
        $ordinal = 'th';
    }
    
    return "Issued this {$day}{$ordinal} day of {$month} {$year}";
}

$response = [
    'success' => false,
    'message' => '',
    'file_path' => '',
    'auto_download' => false
];

$current_admin_id = null;
if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    $session_admin_id = (int)$_SESSION['admin_id'];
    
    $admin_check = $conn->prepare("SELECT id, username, CONCAT(first_name, ' ', last_name) as full_name FROM admin_users WHERE id = ? AND status = 'Active'");
    $admin_check->bind_param('i', $session_admin_id);
    $admin_check->execute();
    $admin_result = $admin_check->get_result();
    
    if ($admin_result->num_rows > 0) {
        $admin_data = $admin_result->fetch_assoc();
        $current_admin_id = (int)$admin_data['id'];
        error_log(" Admin authenticated: ID={$current_admin_id}, Name={$admin_data['full_name']}");
    } else {
        error_log(" ERROR: Session admin_id {$session_admin_id} NOT FOUND in admin_users table or not active!");
        error_log(" Available admins: " . json_encode($conn->query("SELECT id, username FROM admin_users")->fetch_all(MYSQLI_ASSOC)));
    }
    $admin_check->close();
} else {
    error_log("✗ ERROR: No admin_id in session!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];
    $notes = $_POST['notes'] ?? null;
    $auto_download = isset($_POST['auto_download']) && $_POST['auto_download'] === '1';

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("
            SELECT dr.*, dt.document_type, r.email as resident_email
            FROM document_requests dr
            JOIN document_types dt ON dr.document_type_id = dt.id
            LEFT JOIN residents r ON dr.resident_id = r.id
            WHERE dr.id = ?
        ");
        $stmt->bind_param('i', $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        
        if (!$request) {
            throw new Exception('Request not found.');
        }
        $stmt->close();

        $status = ($action === 'approve') ? 'Approved' : 'Disapproved';

       
        if ($current_admin_id !== null) {
            $stmt = $conn->prepare("
                UPDATE document_requests 
                SET status = ?, notes = ?, processed_by = ?, date_processed = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param('ssii', $status, $notes, $current_admin_id, $request_id);
            error_log("→ Updating request {$request_id} with admin_id={$current_admin_id}");
        } else {

            $stmt = $conn->prepare("
                UPDATE document_requests 
                SET status = ?, notes = ?, date_processed = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param('ssi', $status, $notes, $request_id);
            error_log("⚠ Updating request {$request_id} WITHOUT admin_id (session user invalid)");
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update request status: " . $stmt->error);
        }
        $stmt->close();

        $verify = $conn->query("SELECT processed_by FROM document_requests WHERE id = {$request_id}");
        $verify_data = $verify->fetch_assoc();
        error_log(" Request {$request_id} updated. processed_by = " . ($verify_data['processed_by'] ?? 'NULL'));

        if ($status === 'Approved') {
            $timestamp = time();

            $outDir = $_SERVER['DOCUMENT_ROOT'] . '/barangay-balas/public/uploads/generated_docs';
            $qrDir = __DIR__ . '/../public/uploads/qr_codes';
            if (!is_dir($outDir)) mkdir($outDir, 0755, true);
            if (!is_dir($qrDir)) mkdir($qrDir, 0755, true);

            $templatePath = __DIR__ . "/../../services/certificates/templates/{$request['document_type']}.docx";
            if (!file_exists($templatePath)) {
                throw new Exception("Template not found: {$request['document_type']}");
            }
            
            function getBaseUrl() {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
                
                $scriptPath = str_replace('/admin', '', $scriptPath);
                $scriptPath = str_replace('/services/certificates', '', $scriptPath);
                
                return $protocol . '://' . $host . $scriptPath;
            }

            $baseUrl = getBaseUrl();

            $qr_code = bin2hex(random_bytes(16));
            $qrPath = $qrDir . "/qr_{$request_id}_{$timestamp}.png";

            $verifyUrl = $baseUrl . "/services/certificates/direct_pdf_verify.php?token={$qr_code}";

            QRcode::png($verifyUrl, $qrPath, QR_ECLEVEL_L, 4);

            $stmt = $conn->prepare("INSERT INTO document_qr_codes (request_id, qr_code, qr_code_image_path) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $request_id, $qr_code, $qrPath);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert QR code: " . $stmt->error);
            }
            $stmt->close();

            $currentDate = date('Y-m-d H:i:s');
            $issuedDateFormatted = formatIssuedDate($currentDate);

            $templateProcessor = new TemplateProcessor($templatePath);
            $templateProcessor->setValue('first_name', htmlspecialchars($request['first_name']));
            $templateProcessor->setValue('middle_name', htmlspecialchars($request['middle_name'] ?? ''));
            $templateProcessor->setValue('last_name', htmlspecialchars($request['last_name']));
            $templateProcessor->setValue('date', date('F j, Y'));
            $templateProcessor->setValue('issued_date', $issuedDateFormatted); // NEW: Add formatted issued date
            $templateProcessor->setValue('sex', htmlspecialchars($request['sex'] ?? ''));
            $templateProcessor->setValue('birthdate', htmlspecialchars($request['birthdate'] ?? ''));
            $templateProcessor->setValue('age', htmlspecialchars($request['age'] ?? ''));
            $templateProcessor->setValue('houseno', htmlspecialchars($request['houseno'] ?? ''));
            $templateProcessor->setValue('purok', htmlspecialchars($request['purok'] ?? ''));
            $templateProcessor->setValue('civil_status', htmlspecialchars($request['civil_status'] ?? ''));
            $templateProcessor->setValue('purpose', htmlspecialchars($request['purpose'] ?? ''));
            $templateProcessor->setValue('date_requested', htmlspecialchars($request['date_requested'] ?? date('Y-m-d')));

            $templateProcessor->setImageValue('qr_code', [
                'path' => $qrPath,
                'width' => 100,
                'height' => 100,
                'ratio' => false
            ]);

            // Save DOCX
            $docxPath = $outDir . "/request_{$request_id}_{$timestamp}.docx";
            $templateProcessor->saveAs($docxPath);

            // Convert to PDF
            $pdfPath = $outDir . "/request_{$request_id}_{$timestamp}.pdf";
            $libreOfficePath = '"C:\\Program Files\\LibreOffice\\program\\soffice.exe"';
            $cmd = "$libreOfficePath --headless --convert-to pdf --outdir " . escapeshellarg($outDir) . " " . escapeshellarg($docxPath) . " 2>&1";
            exec($cmd, $output, $return_var);

            $maxWait = 5;
            $waited = 0;
            while (!file_exists($pdfPath) && $waited < $maxWait) {
                sleep(1);
                $waited++;
            }

            if ($return_var !== 0 || !file_exists($pdfPath)) {
                error_log("LibreOffice failed: " . implode("\n", $output));
                throw new Exception("PDF generation failed. Please ensure LibreOffice is installed.");
            }

            $relativePdfPath = "uploads/generated_docs/request_{$request_id}_{$timestamp}.pdf";
            $stmt = $conn->prepare("UPDATE document_requests SET document_file_path = ? WHERE id = ?");
            $stmt->bind_param('si', $relativePdfPath, $request_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update document file path: " . $stmt->error);
            }
            $stmt->close();

            if (file_exists($docxPath)) {
                @unlink($docxPath);
            }

            $response['success'] = true;
            $response['message'] = 'Request approved and document generated successfully.';
            $response['file_path'] = $relativePdfPath;
            $response['auto_download'] = $auto_download;

            // Send email notification
            if (!empty($request['resident_email'])) {
                $downloadUrl = isset($_SERVER['REQUEST_SCHEME']) && isset($_SERVER['HTTP_HOST'])
                    ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/barangay-balas/services/download-document.php?id=' . $request_id
                    : '#';

                // Get resident's full name
                $user_query = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM residents WHERE id = ?");
                $user_query->bind_param('i', $request['resident_id']);
                $user_query->execute();
                $user_result = $user_query->get_result();
                $user = $user_result->fetch_assoc();
                $user_query->close();

                if ($user && !empty($user['full_name'])) {
                    $template = requestStatusEmail(
                        $user['full_name'],
                        $request['document_type'],
                        $status,
                        $notes,
                        $downloadUrl
                    );

                    sendEmail($request['resident_email'], $template['subject'], $template['message']);
                    error_log("✓ Email sent to: " . $request['resident_email']);
                }
            } else {
                error_log("⚠ No email found for resident ID: " . $request['resident_id']);
            }

        } else {
            $response['success'] = true;
            $response['message'] = 'Request disapproved successfully.';

            // Send email for disapproval
            if (!empty($request['resident_email'])) {
                // Get resident's full name
                $user_query = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM residents WHERE id = ?");
                $user_query->bind_param('i', $request['resident_id']);
                $user_query->execute();
                $user_result = $user_query->get_result();
                $user = $user_result->fetch_assoc();
                $user_query->close();

                if ($user && !empty($user['full_name'])) {
                    $template = requestStatusEmail(
                        $user['full_name'],
                        $request['document_type'],
                        $status,
                        $notes
                    );

                    sendEmail($request['resident_email'], $template['subject'], $template['message']);
                    error_log("✓ Disapproval email sent to: " . $request['resident_email']);
                }
            }
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
        error_log(" ERROR processing request {$request_id}: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);

// Enhanced Report Generation Functions
function generateReport($conn) {
    $report_type = $_POST['report_type'] ?? '';
    $month = $_POST['month'] ?? '';
    $year = $_POST['year'] ?? '';
    $export_type = $_POST['export_type'] ?? 'excel';
    
    try {
        $data = getReportDataInternal($conn, $report_type, $month, $year);
        
        // Generate file
        $filename = "document_requests_" . $report_type . "_" . ($month ? $month . "_" : "") . $year . "_" . date('Y-m-d');
        
        if ($export_type === 'excel') {
            $filename .= ".xlsx";
            $filepath = generateExcelReport($data, $filename);
        } else {
            $filename .= ".csv";
            $filepath = generateCSVReport($data, $filename);
        }
        
        echo json_encode([
            'success' => true,
            'filename' => $filename,
            'filepath' => '../backend/process_request.php?download_report=' . urlencode($filename),
            'message' => 'Report generated successfully'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error generating report: ' . $e->getMessage()
        ]);
    }
}

function getReportData($conn) {
    $report_type = $_POST['report_type'] ?? '';
    $month = $_POST['month'] ?? '';
    $year = $_POST['year'] ?? '';
    
    try {
        $data = getReportDataInternal($conn, $report_type, $month, $year);
        
        echo json_encode([
            'success' => true,
            'data' => $data,
            'message' => 'Report data fetched successfully'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching report data: ' . $e->getMessage()
        ]);
    }
}

function getReportDataInternal($conn, $report_type, $month, $year) {
    $query = "
        SELECT 
            dr.id,
            dt.document_type,
            CONCAT(r.first_name, ' ', r.last_name) AS resident_name,
            r.house_number,
            r.purok,
            r.contact_number,
            dr.purpose,
            dr.status,
            dr.date_requested,
            dr.date_processed,
            dr.notes,
            CONCAT(au.first_name, ' ', au.last_name) AS processed_by_name
        FROM document_requests dr
        LEFT JOIN document_types dt ON dr.document_type_id = dt.id
        LEFT JOIN residents r ON dr.resident_id = r.id
        LEFT JOIN admin_users au ON dr.processed_by = au.id
        WHERE 1=1
    ";
    
    $params = [];
    $types = '';
    
    if ($report_type === 'monthly' && $month && $year) {
        $query .= " AND YEAR(dr.date_requested) = ? AND MONTH(dr.date_requested) = ?";
        $params[] = $year;
        $params[] = $month;
        $types .= 'ii';
    } elseif ($report_type === 'yearly' && $year) {
        $query .= " AND YEAR(dr.date_requested) = ?";
        $params[] = $year;
        $types .= 'i';
    }
    
    $query .= " ORDER BY dr.date_requested DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get summary statistics
    $summary_query = "
        SELECT 
            status,
            COUNT(*) as count,
            document_type,
            COUNT(*) as type_count
        FROM document_requests dr
        LEFT JOIN document_types dt ON dr.document_type_id = dt.id
        WHERE 1=1
    ";
    
    if ($report_type === 'monthly' && $month && $year) {
        $summary_query .= " AND YEAR(dr.date_requested) = ? AND MONTH(dr.date_requested) = ?";
    } elseif ($report_type === 'yearly' && $year) {
        $summary_query .= " AND YEAR(dr.date_requested) = ?";
    }
    
    $summary_query .= " GROUP BY status, document_type";
    
    $summary_stmt = $conn->prepare($summary_query);
    
    if (!empty($params)) {
        $summary_stmt->bind_param($types, ...$params);
    }
    
    $summary_stmt->execute();
    $summary_result = $summary_stmt->get_result();
    $summary_data = $summary_result->fetch_all(MYSQLI_ASSOC);
    
    // Calculate totals
    $status_counts = [
        'Pending' => 0,
        'Approved' => 0,
        'Disapproved' => 0,
        'Cancelled' => 0
    ];
    
    $document_type_counts = [];
    $total = 0;
    
    foreach ($summary_data as $row) {
        $status_counts[$row['status']] = $row['count'];
        $document_type_counts[$row['document_type']] = ($document_type_counts[$row['document_type']] ?? 0) + $row['count'];
        $total += $row['count'];
    }
    
    // Get monthly breakdown for yearly reports
    $monthly_breakdown = [];
    if ($report_type === 'yearly' && $year) {
        $monthly_query = "
            SELECT 
                MONTH(date_requested) as month,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'Disapproved' THEN 1 ELSE 0 END) as disapproved,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM document_requests 
            WHERE YEAR(date_requested) = ?
            GROUP BY MONTH(date_requested)
            ORDER BY month
        ";
        
        $monthly_stmt = $conn->prepare($monthly_query);
        $monthly_stmt->bind_param('i', $year);
        $monthly_stmt->execute();
        $monthly_result = $monthly_stmt->get_result();
        $monthly_breakdown = $monthly_result->fetch_all(MYSQLI_ASSOC);
    }
    
    return [
        'requests' => $requests,
        'summary' => [
            'total' => $total,
            'status_counts' => $status_counts,
            'document_type_counts' => $document_type_counts
        ],
        'monthly_breakdown' => $monthly_breakdown,
        'report_type' => $report_type,
        'month' => $month,
        'year' => $year
    ];
}

function generateExcelReport($data, $filename) {
    $filepath = __DIR__ . '../../../temp/' . $filename;
    

    if (!is_dir(dirname($filepath))) {
        mkdir(dirname($filepath), 0755, true);
    }
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set headers
    $headers = [
        'ID', 'Document Type', 'Resident Name', 'House No', 'Purok', 
        'Contact Number', 'Purpose', 'Status', 'Date Requested', 
        'Date Processed', 'Notes', 'Processed By'
    ];
    
    $sheet->fromArray($headers, null, 'A1');
    
    // Add data
    $rowData = [];
    foreach ($data['requests'] as $record) {
        $rowData[] = [
            $record['id'],
            $record['document_type'],
            $record['resident_name'],
            $record['house_number'],
            $record['purok'],
            $record['contact_number'],
            $record['purpose'],
            $record['status'],
            $record['date_requested'],
            $record['date_processed'],
            $record['notes'],
            $record['processed_by_name']
        ];
    }
    
    $sheet->fromArray($rowData, null, 'A2');
    
    // Auto-size columns
    foreach (range('A', 'L') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Style headers
    $headerStyle = [
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E6E6FA']
        ]
    ];
    $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
    
    $writer = new Xlsx($spreadsheet);
    $writer->save($filepath);
    
    return $filepath;
}

function generateCSVReport($data, $filename) {
    $filepath = __DIR__ . '/../temp/' . $filename;

    if (!is_dir(dirname($filepath))) {
        mkdir(dirname($filepath), 0755, true);
    }
    
    $fp = fopen($filepath, 'w');
    
    // Add headers
    $headers = [
        'ID', 'Document Type', 'Resident Name', 'House No', 'Purok', 
        'Contact Number', 'Purpose', 'Status', 'Date Requested', 
        'Date Processed', 'Notes', 'Processed By'
    ];
    fputcsv($fp, $headers);
    
    // Add data
    foreach ($data['requests'] as $record) {
        fputcsv($fp, [
            $record['id'],
            $record['document_type'],
            $record['resident_name'],
            $record['house_number'],
            $record['purok'],
            $record['contact_number'],
            $record['purpose'],
            $record['status'],
            $record['date_requested'],
            $record['date_processed'],
            $record['notes'],
            $record['processed_by_name']
        ]);
    }
    
    fclose($fp);
    return $filepath;
}

function downloadReport() {
    $filename = basename($_GET['download_report']);
    $filepath = __DIR__ . '../../../temp/' . $filename;
    
    if (file_exists($filepath)) {
        if (pathinfo($filename, PATHINFO_EXTENSION) === 'xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        } else {
            header('Content-Type: text/csv');
        }
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile($filepath);
        exit;
    } else {
        http_response_code(404);
        echo "File not found.";
    }
}
?>