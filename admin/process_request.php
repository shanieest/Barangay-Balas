<?php
require 'includes/db.php';
require 'includes/auth.php';
require '../vendor/autoload.php';
require_once __DIR__ . '/../lib/phpqrcode/qrlib.php';

use PhpOffice\PhpWord\TemplateProcessor;

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'file_path' => '',
    'auto_download' => false
];

$current_admin_id = null;
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $admin_check = $conn->prepare("SELECT id FROM admin_users WHERE id = ?");
    $admin_check->bind_param('i', $_SESSION['user_id']);
    $admin_check->execute();
    $admin_result = $admin_check->get_result();
    
    if ($admin_result->num_rows > 0) {
        $current_admin_id = (int) $_SESSION['user_id'];
        error_log("DEBUG: Admin found in database with ID: " . $current_admin_id);
    } else {
        error_log("DEBUG: No admin found with user_id: " . $_SESSION['user_id']);
    }
    $admin_check->close();
} else {
    error_log("DEBUG: No user_id found in session");
}

error_log("DEBUG: Session variables: " . print_r($_SESSION, true));
error_log("DEBUG: Determined admin ID: " . $current_admin_id);

if ($current_admin_id === null) {
    error_log("Warning: No valid admin ID found in session for document processing");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $request_id   = (int) $_POST['request_id'];
    $action       = $_POST['action'];
    $notes        = $_POST['notes'] ?? null;
    $auto_download = isset($_POST['auto_download']) && $_POST['auto_download'] === '1';

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("
            SELECT dr.*, dt.document_type, dr.first_name, dr.middle_name, dr.last_name, dr.email, dr.houseno, dr.purok
            FROM document_requests dr
            JOIN document_types dt ON dr.document_type_id = dt.id
            JOIN residents r ON dr.resident_id = r.id
            WHERE dr.id = ?
        ");
        $stmt->bind_param('i', $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        if (!$request) throw new Exception('Request not found.');
        $stmt->close();

        $status = ($action === 'approve') ? 'Approved' : 'Disapproved';

        if ($current_admin_id !== null) {
            $stmt = $conn->prepare("UPDATE document_requests SET status = ?, notes = ?, processed_by = ?, date_processed = NOW() WHERE id = ?");
            $stmt->bind_param('ssii', $status, $notes, $current_admin_id, $request_id);
            error_log("DEBUG: Updating with admin ID: " . $current_admin_id . " for request: " . $request_id);
        } else {
            $stmt = $conn->prepare("UPDATE document_requests SET status = ?, notes = ?, date_processed = NOW() WHERE id = ?");
            $stmt->bind_param('ssi', $status, $notes, $request_id);
            error_log("WARNING: No admin ID available, updating without processed_by for request: " . $request_id);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update request status: " . $stmt->error);
        }
        
        if ($current_admin_id !== null) {
            $check_stmt = $conn->prepare("SELECT processed_by FROM document_requests WHERE id = ?");
            $check_stmt->bind_param('i', $request_id);
            $check_stmt->execute();
            $check_stmt->bind_result($actual_processed_by);
            $check_stmt->fetch();
            $check_stmt->close();
            
            error_log("DEBUG: Actual processed_by value in database: " . ($actual_processed_by ?? 'NULL'));
        }
        
        $stmt->close();

        if ($status === 'Approved') {
            $timestamp = time();

            $outDir = __DIR__ . '/../public/uploads/generated_docs';
            $qrDir  = __DIR__ . '/../public/uploads/qr_codes';
            if (!is_dir($outDir)) mkdir($outDir, 0777, true);
            if (!is_dir($qrDir)) mkdir($qrDir, 0777, true);

            $templatePath = __DIR__ . "/../services/certificates/templates/{$request['document_type']}.docx";
            if (!file_exists($templatePath)) throw new Exception("Template not found: ".$request['document_type']);
            $templateProcessor = new TemplateProcessor($templatePath);

            $qr_code = bin2hex(random_bytes(8));
            $qrPath  = $qrDir . "/qr_{$request_id}_{$timestamp}.png";
            $verifyUrl = "verify.php?code={$qr_code}";
            QRcode::png($verifyUrl, $qrPath, QR_ECLEVEL_L, 4);

            $stmt = $conn->prepare("INSERT INTO document_qr_codes (request_id, qr_code, qr_code_image_path) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $request_id, $qr_code, $qrPath);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert QR code: " . $stmt->error);
            }
            $stmt->close();

            $templateProcessor->setValue('first_name', htmlspecialchars($request['first_name']));
            $templateProcessor->setValue('middle_name', htmlspecialchars($request['middle_name'] ?? ''));
            $templateProcessor->setValue('last_name', htmlspecialchars($request['last_name']));
            $templateProcessor->setValue('date', date('F j, Y'));
            $templateProcessor->setImageValue('qr_code', [
                'path' => $qrPath,
                'width' => 100,
                'height' => 100,
                'ratio' => false
            ]);

            $docxPath = $outDir . "/request_{$request_id}_{$timestamp}.docx";
            $templateProcessor->saveAs($docxPath);

            $pdfPath = $outDir . "/request_{$request_id}_{$timestamp}.pdf";
            $libreOfficePath = '"C:\\Program Files\\LibreOffice\\program\\soffice.exe"';
            $cmd = "$libreOfficePath --headless --convert-to pdf --outdir \"" . $outDir . "\" \"" . $docxPath . "\"";
            exec($cmd, $output, $return_var);
            if ($return_var !== 0 || !file_exists($pdfPath)) {
                throw new Exception("PDF generation via LibreOffice failed. Ensure LibreOffice is installed and path is correct.");
            }

            $relativePdfPath = "uploads/generated_docs/request_{$request_id}_{$timestamp}.pdf";
            $stmt = $conn->prepare("UPDATE document_requests SET document_file_path = ? WHERE id = ?");
            $stmt->bind_param('si', $relativePdfPath, $request_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update document file path: " . $stmt->error);
            }
            $stmt->close();

            $response['success'] = true;
            $response['message'] = 'Request approved and document generated successfully.';
            $response['file_path'] = $relativePdfPath;
            $response['auto_download'] = $auto_download;
        } else {
            $response['success'] = true;
            $response['message'] = 'Request disapproved successfully.';
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
        error_log("ERROR: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);
?>