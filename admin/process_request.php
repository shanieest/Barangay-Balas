<?php
require '../config/db.php';
require '../vendor/autoload.php';
require_once __DIR__ . '/../lib/phpqrcode/qrlib.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'file_path' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $request_id   = (int) $_POST['request_id'];
    $action       = $_POST['action'];
    $notes        = $_POST['notes'] ?? null;
    $autoDownload = isset($_POST['auto_download']) ? true : false;

    try {
        $conn->begin_transaction();

        // Fetch request + resident info
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

        $status = ($action === 'approve') ? 'Approved' : 'Disapproved';

        // Update request status
        $stmt = $conn->prepare("UPDATE document_requests SET status = ?, notes = ?, date_processed = NOW() WHERE id = ?");
        $stmt->bind_param('ssi', $status, $notes, $request_id);
        $stmt->execute();

        if ($status === 'Approved') {
            $timestamp = time();

            // Directories
            $outDir = __DIR__ . '/../public/uploads/generated_docs';
            $qrDir  = __DIR__ . '/../public/uploads/qr_codes';
            if (!is_dir($outDir)) mkdir($outDir, 0777, true);
            if (!is_dir($qrDir)) mkdir($qrDir, 0777, true);

            // Load DOCX template
            $templatePath = __DIR__ . "/../services/certificates/templates/{$request['document_type']}.docx";
            if (!file_exists($templatePath)) throw new Exception("Template not found: ".$request['document_type']);
            $templateProcessor = new TemplateProcessor($templatePath);

            // Generate QR
            $qr_code = bin2hex(random_bytes(8));
            $qrPath  = $qrDir . "/qr_{$request_id}_{$timestamp}.png";
            $verifyUrl = "https://yourdomain.com/verify.php?code={$qr_code}";
            QRcode::png($verifyUrl, $qrPath, QR_ECLEVEL_L, 4);

            // Insert QR into DB
            $stmt = $conn->prepare("INSERT INTO document_qr_codes (request_id, qr_code, qr_code_image_path) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $request_id, $qr_code, $qrPath);
            $stmt->execute();

            // Fill DOCX placeholders
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

            // Save DOCX
            $docxPath = $outDir . "/request_{$request_id}_{$timestamp}.docx";
            $templateProcessor->saveAs($docxPath);

            // Convert DOCX → PDF via LibreOffice (Windows)
            $pdfPath = $outDir . "/request_{$request_id}_{$timestamp}.pdf";
            $libreOfficePath = '"C:\\Program Files\\LibreOffice\\program\\soffice.exe"';
            $cmd = "$libreOfficePath --headless --convert-to pdf --outdir \"" . $outDir . "\" \"" . $docxPath . "\"";
            exec($cmd, $output, $return_var);
            if ($return_var !== 0 || !file_exists($pdfPath)) {
                throw new Exception("PDF generation via LibreOffice failed. Ensure LibreOffice is installed and path is correct.");
            }

            // Save relative path to DB
            $relativePdfPath = "uploads/generated_docs/request_{$request_id}_{$timestamp}.pdf";
            $stmt = $conn->prepare("UPDATE document_requests SET document_file_path = ? WHERE id = ?");
            $stmt->bind_param('si', $relativePdfPath, $request_id);
            $stmt->execute();

            $response['success'] = true;
            $response['message'] = 'Request approved and document generated successfully.';
            $response['file_path'] = $relativePdfPath;
            $response['auto_download'] = $autoDownload;

            // Auto-download
            if ($autoDownload) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="'.basename($pdfPath).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($pdfPath));
                readfile($pdfPath);
                exit;
            }
        } else {
            $response['success'] = true;
            $response['message'] = 'Request disapproved successfully.';
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);
