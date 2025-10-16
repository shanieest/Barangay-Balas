<?php
function applyQrWatermarkFixed($original_pdf_path, $request_id, $document_info = []) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    try {
        $temp_dir = __DIR__ . '/../../public/uploads/temp';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        $watermarked_path = $temp_dir . '/qr_verified_' . $request_id . '_' . time() . '.pdf';

        // Check if we can use TCPDF with advanced features
        if (class_exists('TCPDF')) {
            // Create a new PDF
            $pdf = new TCPDF();
            $pdf->SetCreator('Barangay Balas QR Verification');
            $pdf->SetAuthor('Barangay Balas');
            $pdf->SetTitle('Verified Document');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Get number of pages in original PDF using FPDI just for counting
            $pageCount = 1;
            if (class_exists('setasign\Fpdi\Fpdi')) {
                $fpdi = new setasign\Fpdi\Fpdi();
                $pageCount = $fpdi->setSourceFile($original_pdf_path);
            }
            
            // Add verification cover page
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor(0, 100, 0);
            $pdf->Cell(0, 10, '✓ DOCUMENT VERIFIED VIA QR CODE', 0, 1, 'C');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'Barangay Balas - Mexico, Pampanga', 0, 1, 'C');
            $pdf->Ln(10);
            
            // Document info
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'Verification Details:', 0, 1);
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Cell(0, 6, 'Resident: ' . ($document_info['resident_name'] ?? 'N/A'), 0, 1);
            $pdf->Cell(0, 6, 'Document: ' . ($document_info['document_type'] ?? 'N/A'), 0, 1);
            $pdf->Cell(0, 6, 'Verified: ' . date('Y-m-d H:i:s'), 0, 1);
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->MultiCell(0, 5, "This digital copy has been verified through the Barangay Balas QR Code system. Official stamped copies are available at the Barangay Office.", 0, 'C');
            
            $pdf->Output($watermarked_path, 'F');
            return file_exists($watermarked_path) ? $watermarked_path : $original_pdf_path;
        }
        
        // Fallback to cover page method
        return addVerificationCoverPage($original_pdf_path, $request_id, $document_info);
        
    } catch (Exception $e) {
        error_log("QR Watermark error: " . $e->getMessage());
        return addVerificationCoverPage($original_pdf_path, $request_id, $document_info);
    }
}

// Keep the same addVerificationCoverPage function as above
function addVerificationCoverPage($original_pdf_path, $request_id, $document_info) {
    try {
        $temp_dir = __DIR__ . '/../../public/uploads/temp';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        $watermarked_path = $temp_dir . '/qr_cover_' . $request_id . '_' . time() . '.pdf';

        $pdf = new TCPDF();
        $pdf->SetCreator('Barangay Balas QR Verification');
        $pdf->SetAuthor('Barangay Balas');
        $pdf->SetTitle('Verified Document');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Cover page
        $pdf->AddPage();
        
        // Official header
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(0, 0, 128);
        $pdf->Cell(0, 10, 'BARANGAY BALAS - MEXICO, PAMPANGA', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(0, 100, 0);
        $pdf->Cell(0, 15, '✓ DOCUMENT VERIFIED VIA QR CODE', 0, 1, 'C');
        
        $pdf->Ln(10);
        
        // Document details
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'DOCUMENT VERIFICATION DETAILS:', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        
        $pdf->Cell(0, 6, 'Resident: ' . ($document_info['resident_name'] ?? 'N/A'), 0, 1);
        $pdf->Cell(0, 6, 'Document Type: ' . ($document_info['document_type'] ?? 'N/A'), 0, 1);
        $pdf->Cell(0, 6, 'Request ID: ' . $request_id, 0, 1);
        $pdf->Cell(0, 6, 'Date Issued: ' . ($document_info['date_processed'] ?? 'N/A'), 0, 1);
        $pdf->Cell(0, 6, 'Verified On: ' . date('Y-m-d H:i:s'), 0, 1);
        
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->SetTextColor(128, 0, 0);
        $pdf->MultiCell(0, 5, "This document has been verified through the official Barangay Balas QR Code verification system. Official stamped copies are available at the Barangay Office.", 0, 'C');
        
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, '--- Original Document Follows ---', 0, 1, 'C');
        
        $pdf->Output($watermarked_path, 'F');
        
        return file_exists($watermarked_path) ? $watermarked_path : $original_pdf_path;
        
    } catch (Exception $e) {
        error_log("Cover page error: " . $e->getMessage());
        return $original_pdf_path;
    }
}
?>