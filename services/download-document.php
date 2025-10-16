<?php
require '../config/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    die("Error: No document ID specified. Usage: download-document.php?id=1");
}

$request_id = (int) $_GET['id'];
$is_admin_download = isset($_GET['admin']) && $_GET['admin'] === 'true';

try {
    $stmt = $conn->prepare("SELECT document_file_path FROM document_requests WHERE id = ? AND status = 'Approved'");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if (!$request || empty($request['document_file_path'])) {
        http_response_code(404);
        die("Error: Document not found or not approved yet for ID: " . $request_id);
    }

    $file = basename($request['document_file_path']);
    $full_path = __DIR__ . "/../public/" . $request['document_file_path'];
    
    if (!file_exists($full_path)) {
        http_response_code(404);
        die("Error: File not found at path: " . htmlspecialchars($request['document_file_path']));
    }

    if (!$is_admin_download) {
        $watermarked_pdf = addWatermarkWithFPDI($full_path, $request_id);
        if ($watermarked_pdf && $watermarked_pdf !== $full_path && file_exists($watermarked_pdf)) {
            $full_path = $watermarked_pdf;
        }
    }

    // Serve the file
    servePdfFile($full_path, $file);

    // Clean up temporary watermarked file if it was created
    if (!$is_admin_download && isset($watermarked_pdf) && file_exists($watermarked_pdf) && $watermarked_pdf !== $full_path) {
        @unlink($watermarked_pdf);
    }

} catch (Exception $e) {
    error_log("Download error: " . $e->getMessage());
    http_response_code(500);
    die("Database error: " . $e->getMessage());
}

exit;


function servePdfFile($file_path, $filename) {
    if (!file_exists($file_path)) {
        http_response_code(404);
        die("Error: File not found");
    }

    $file_size = filesize($file_path);
    if ($file_size === false) {
        http_response_code(500);
        die("Error: Could not determine file size.");
    }

    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
    header("Content-Length: " . $file_size);
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");

    if (ob_get_level()) {
        ob_end_clean();
    }

    readfile($file_path);
}


function addWatermarkWithFPDI($original_pdf_path, $request_id) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    try {
        // Check if FPDI is available
        if (!class_exists('setasign\Fpdi\Fpdi')) {
            throw new Exception('FPDI library not found');
        }

        // Create temporary directory
        $temp_dir = __DIR__ . '/../public/uploads/temp';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        $watermarked_path = $temp_dir . '/watermarked_' . $request_id . '_' . time() . '.pdf';

        // Extend FPDI to add rotation capability
        if (!class_exists('FPDIWithRotation')) {
            class FPDIWithRotation extends \setasign\Fpdi\Fpdi {
                protected $angle = 0;
                function Rotate($angle, $x = -1, $y = -1) {
                    if ($x == -1)
                        $x = $this->GetX();
                    if ($y == -1)
                        $y = $this->GetY();
                    if ($this->angle != 0)
                        $this->_out('Q');
                    $this->angle = $angle;
                    if ($angle != 0) {
                        $angle *= M_PI / 180;
                        $c = cos($angle);
                        $s = sin($angle);
                        $cx = $x * $this->k;
                        $cy = ($this->h - $y) * $this->k;
                        $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.5F %.5F cm', $c, $s, -$s, $c, $cx - $c * $cx + $s * $cy, $cy - $s * $cx - $c * $cy));
                    }
                }
                function _endpage() {
                    if ($this->angle != 0) {
                        $this->angle = 0;
                        $this->_out('Q');
                    }
                    parent::_endpage();
                }
            }
        }

        // Initialize FPDI with rotation
        $pdf = new FPDIWithRotation();
        
        // Set source file
        $pageCount = $pdf->setSourceFile($original_pdf_path);
        
        // Process each page
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            // Import page
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            
            // Add page
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            
            // Add watermark text (without transparency since FPDI doesn't support SetAlpha)
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->SetTextColor(96, 96, 96); // Dark red color
            
            $text = "Official stamped copies are available at the Barangay Office";
            $textWidth = $pdf->GetStringWidth($text);
            
            // Calculate position (center of page)
            $x = ($size['width'] - $textWidth) / 2;
            $y = $size['height'] / 2;
            
            // Add rotated text
            $pdf->Rotate(45, $x + ($textWidth / 2), $y);
            $pdf->Text($x, $y, $text);
            $pdf->Rotate(0);
        }
        
        // Output PDF
        $pdf->Output($watermarked_path, 'F');
        
        return file_exists($watermarked_path) ? $watermarked_path : $original_pdf_path;
        
    } catch (Exception $e) {
        error_log("FPDI Watermark error: " . $e->getMessage());
        return addWatermarkCoverPage($original_pdf_path, $request_id);
    }
}


function addWatermarkCoverPage($original_pdf_path, $request_id) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    try {
        $temp_dir = __DIR__ . '/../public/uploads/temp';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        $watermarked_path = $temp_dir . '/watermarked_cover_' . $request_id . '_' . time() . '.pdf';

        // Create cover page
        $pdf = new TCPDF();
        $pdf->SetCreator('Barangay Balas System');
        $pdf->SetAuthor('Barangay Balas');
        $pdf->SetTitle('Document - Digital Copy');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Add cover page
        $pdf->AddPage();
        
        // Add prominent notice
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Write(0, "DIGITAL COPY\n\n", '', 0, 'C', true);
        
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Write(0, "TO GET HARD COPY, GO TO OFFICE\n\n", '', 0, 'C', true);
        
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, "This is a digital copy for reference purposes only.\n", '', 0, 'C', true);
        $pdf->Write(0, "Official stamped copies are available at the Barangay Office.\n\n", '', 0, 'C', true);
        
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Write(0, "Barangay Balas Office\n", '', 0, 'C', true);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, "Mexico, Pampanga\n\n", '', 0, 'C', true);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Write(0, "Document: " . basename($original_pdf_path) . "\n", '', 0, 'C', true);
        $pdf->Write(0, "Request ID: " . $request_id . "\n", '', 0, 'C', true);
        $pdf->Write(0, "Downloaded: " . date('Y-m-d H:i:s') . "\n\n", '', 0, 'C', true);
        
        // Add separator
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Write(0, "--- Original Document Follows ---", '', 0, 'C', true);
        
        $pdf->Output($watermarked_path, 'F');
        
        return file_exists($watermarked_path) ? $watermarked_path : $original_pdf_path;
        
    } catch (Exception $e) {
        error_log("Cover page error: " . $e->getMessage());
        return $original_pdf_path;
    }
}
?>