<?php
require 'includes/db.php';
require 'includes/auth.php';
require '../vendor/autoload.php';
require_once __DIR__ . '/../lib/phpqrcode/qrlib.php';
require_once __DIR__ . '/../config/emailer.php';
require_once __DIR__ . '/../email_templates/document_status.php';


use PhpOffice\PhpWord\TemplateProcessor;

header('Content-Type: application/json');

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
            SELECT dr.*, dt.document_type
            FROM document_requests dr
            JOIN document_types dt ON dr.document_type_id = dt.id
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

        // Update request with or without processed_by
        if ($current_admin_id !== null) {
            // Admin exists - save with processed_by
            $stmt = $conn->prepare("
                UPDATE document_requests 
                SET status = ?, notes = ?, processed_by = ?, date_processed = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param('ssii', $status, $notes, $current_admin_id, $request_id);
            error_log("→ Updating request {$request_id} with admin_id={$current_admin_id}");
        } else {
            // No valid admin - save without processed_by
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

            $outDir = __DIR__ . '/../public/uploads/generated_docs';
            $qrDir = __DIR__ . '/../public/uploads/qr_codes';
            if (!is_dir($outDir)) mkdir($outDir, 0755, true);
            if (!is_dir($qrDir)) mkdir($qrDir, 0755, true);

            $templatePath = __DIR__ . "/../services/certificates/templates/{$request['document_type']}.docx";
            if (!file_exists($templatePath)) {
                throw new Exception("Template not found: {$request['document_type']}");
            }

            // Generate QR code
            $qr_code = bin2hex(random_bytes(16));
            $qrPath = $qrDir . "/qr_{$request_id}_{$timestamp}.png";
            $verifyUrl = "verify.php?code={$qr_code}";
            QRcode::png($verifyUrl, $qrPath, QR_ECLEVEL_L, 4);

            // Insert QR code record
            $stmt = $conn->prepare("INSERT INTO document_qr_codes (request_id, qr_code, qr_code_image_path) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $request_id, $qr_code, $qrPath);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert QR code: " . $stmt->error);
            }
            $stmt->close();

            // Process template
            $templateProcessor = new TemplateProcessor($templatePath);
            $templateProcessor->setValue('first_name', htmlspecialchars($request['first_name']));
            $templateProcessor->setValue('middle_name', htmlspecialchars($request['middle_name'] ?? ''));
            $templateProcessor->setValue('last_name', htmlspecialchars($request['last_name']));
            $templateProcessor->setValue('date', date('F j, Y'));
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

            // Fetch user's email and name
$user_query = $conn->prepare("SELECT email, CONCAT(first_name, ' ', last_name) AS full_name FROM residents WHERE id = ?");
$user_query->bind_param('i', $request['resident_id']);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_query->close();

if ($user && !empty($user['email'])) {
    $template = requestStatusEmail(
        $user['full_name'],
        $request['document_type'],
        $status,
        $notes,
        $relativePdfPath ? (isset($_SERVER['REQUEST_SCHEME']) && isset($_SERVER['HTTP_HOST'])
            ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . $relativePdfPath
            : $relativePdfPath)
        : null
    );

    sendEmail($user['email'], $template['subject'], $template['message']);
}

        } else {
            $response['success'] = true;
$response['message'] = 'Request disapproved successfully.';

// Send email for disapproval too
$user_query = $conn->prepare("SELECT email, CONCAT(first_name, ' ', last_name) AS full_name FROM residents WHERE id = ?");
$user_query->bind_param('i', $request['resident_id']);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_query->close();

if ($user && !empty($user['email'])) {
    $template = requestStatusEmail($user['full_name'], $request['document_type'], $status, $notes);
    sendEmail($user['email'], $template['subject'], $template['message']);
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
?>