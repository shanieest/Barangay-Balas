
<?php
// Alternative: Generate PDF directly from HTML template
function generateBarangayIDHTML($data, $idNumber, $photoPath, $signaturePath, $year) {
    $fullName = trim($data['first_name'] . ' ' . 
                   ($data['middle_name'] ? $data['middle_name'] . ' ' : '') . 
                   $data['last_name'] . ' ' . 
                   ($data['suffix'] ?? ''));
    
    $birthdateFormatted = date('F d, Y', strtotime($data['birthdate']));
    $validUntil = "DECEMBER 31, " . $year;
 } ?>
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Barangay ID - {$fullName}</title>
        <style>
            @page {
                margin: 0;
                padding: 0;
            }
            body {
                margin: 0;
                padding: 20px;
                font-family: Arial, sans-serif;
                background: white;
            }
            .id-card {
                width: 3.375in;
                height: 2.125in;
                border: 2px solid #1a237e;
                border-radius: 10px;
                padding: 15px;
                position: relative;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            .header {
                text-align: center;
                border-bottom: 2px solid #1a237e;
                padding-bottom: 8px;
                margin-bottom: 12px;
            }
            .header h1 {
                margin: 0;
                font-size: 14px;
                color: #1a237e;
                font-weight: bold;
            }
            .header .subtitle {
                font-size: 10px;
                color: #666;
                margin-top: 2px;
            }
            .content {
                display: flex;
                gap: 12px;
                margin-bottom: 10px;
            }
            .photo-section {
                flex-shrink: 0;
            }
            .photo-container {
                width: 1.2in;
                height: 1.4in;
                border: 1px solid #ccc;
                background: white;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }
            .photo-container img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .info-section {
                flex: 1;
                font-size: 9px;
                line-height: 1.2;
            }
            .info-row {
                margin-bottom: 3px;
                display: flex;
            }
            .info-label {
                font-weight: bold;
                color: #1a237e;
                min-width: 55px;
            }
            .info-value {
                flex: 1;
            }
            .valid-until {
                text-align: center;
                font-size: 8px;
                font-weight: bold;
                color: #d32f2f;
                margin: 5px 0;
                padding: 2px;
                background: #ffebee;
                border-radius: 3px;
            }
            .footer {
                border-top: 1px solid #1a237e;
                padding-top: 8px;
                font-size: 8px;
                text-align: center;
            }
            .certification {
                font-style: italic;
                margin-bottom: 5px;
                color: #333;
            }
            .signature-section {
                margin: 3px 0;
            }
            .signature-container {
                height: 25px;
                border-bottom: 1px solid #000;
                margin: 2px auto;
                width: 80%;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .signature-container img {
                max-height: 20px;
                max-width: 100%;
            }
            .signature-label {
                font-weight: bold;
                font-size: 7px;
                margin-top: 2px;
            }
            .lost-info {
                font-size: 7px;
                color: #666;
                margin-top: 3px;
                line-height: 1.1;
            }
            .logos {
                position: absolute;
                top: 10px;
                left: 10px;
                display: flex;
                gap: 5px;
            }
            .logo {
                width: 20px;
                height: 20px;
                background: #1a237e;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 8px;
                font-weight: bold;
            }
            .id-number {
                position: absolute;
                top: 10px;
                right: 10px;
                font-size: 9px;
                font-weight: bold;
                color: #1a237e;
                background: #e8eaf6;
                padding: 2px 5px;
                border-radius: 3px;
            }
        </style>
    </head>
    <body>
        <div class='id-card'>
            <!-- Logos -->
            <div class='logos'>
                <div class='logo'>BB</div>
                <div class='logo'>BS</div>
            </div>
            
            <!-- ID Number -->
            <div class='id-number'>ID No.: {$idNumber}</div>
            
            <!-- Header -->
            <div class='header'>
                <h1>BARANGAY IDENTIFICATION CARD</h1>
                <div class='subtitle'>Barangay Balas, Mexico, Pampanga</div>
            </div>
            
            <!-- Main Content -->
            <div class='content'>
                <!-- Photo Section -->
                <div class='photo-section'>
                    <div class='photo-container'>
                        <img src='{$photoPath}' alt='Formal Photo'>
                    </div>
                </div>
                
                <!-- Information Section -->
                <div class='info-section'>
                    <div class='info-row'>
                        <span class='info-label'>Name:</span>
                        <span class='info-value'>{$fullName}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>Address:</span>
                        <span class='info-value'>{$data['address']}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>Birthdate:</span>
                        <span class='info-value'>{$birthdateFormatted}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>Place of Birth:</span>
                        <span class='info-value'>{$data['place_of_birth'] ?? 'N/A'}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>Contact no.:</span>
                        <span class='info-value'>{$data['contact_number']}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>Civil Status:</span>
                        <span class='info-value'>{$data['civil_status'] ?? 'N/A'}</span>
                    </div>
                    <div class='info-row'>
                        <span class='info-label'>Sex:</span>
                        <span class='info-value'>" . ucfirst($data['sex']) . "</span>
                    </div>
                </div>
            </div>
            
            <!-- Valid Until -->
            <div class='valid-until'>
                Valid Until: {$validUntil}
            </div>
            
            <!-- Footer -->
            <div class='footer'>
                <div class='certification'>
                    This is to certify that the person
                </div>
                <div class='signature-section'>
                    <div class='signature-container'>
                        <img src='{$signaturePath}' alt='Signature'>
                    </div>
                    <div class='signature-label'>
                        CARDHOLDER'S SIGNATURE
                    </div>
                </div>
                <div class='lost-info'>
                    In case of lost, please return to the Office of Punong Barangay at<br>
                    Barangay Hall, Balas, Mexico, Pampanga.
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
}

<?
// Usage in your existing code:
function generatePDFWithHTMLTemplate($data, $idNumber, $photoPath, $signaturePath, $outputDir, $year) {
    require_once('tcpdf/tcpdf.php');
    
    // Create PDF instance
    $pdf = new TCPDF('L', 'in', array(3.375, 2.125), true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Barangay Balas Management System');
    $pdf->SetAuthor('Barangay Balas');
    $pdf->SetTitle('Barangay ID - ' . $data['first_name'] . ' ' . $data['last_name']);
    $pdf->SetSubject('Barangay Identification Card');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(0.2, 0.2, 0.2);
    $pdf->SetAutoPageBreak(false, 0);
    
    // Add a page
    $pdf->AddPage();
    
    // Generate HTML content
    $html = generateBarangayIDHTML($data, $idNumber, $photoPath, $signaturePath, $year);
    
    // Output HTML content
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Generate filename
    $filename = "Barangay_ID_" . $idNumber . ".pdf";
    $filepath = $outputDir . $filename;
    
    // Save PDF file
    $pdf->Output($filepath, 'F');
    
    return "uploads/digital_ids/" . $filename;
}

// Replace your PDF conversion section with this:
// After the template processing section, add this alternative:

// Try HTML to PDF conversion as primary method
try {
    // Get absolute paths for images
    $cleanPhotoPath = str_replace('pages/', '', $data['photo_path']);
    $photoAbsolutePath = realpath(__DIR__ . '/../pages/' . $cleanPhotoPath);
    $cleanSignaturePath = str_replace('pages/', '', $data['signature_path']);
    $signatureAbsolutePath = realpath(__DIR__ . '/../pages/' . $cleanSignaturePath);
    
    // Generate PDF using HTML template
    $pdfPathDB = generatePDFWithHTMLTemplate($data, $idNumber, $photoAbsolutePath, $signatureAbsolutePath, $outputDir, $year);
    
    error_log("HTML PDF generation successful: " . $pdfPathDB);
    
} catch (Exception $e) {
    error_log("HTML PDF generation failed: " . $e->getMessage());
    
    // Fallback to original Word template method
    error_log("Falling back to Word template method...");
    // ... your existing Word template conversion code here
}
?>