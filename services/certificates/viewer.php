<?php
require '../../config/db.php';
session_start();

$token = $_GET['token'] ?? '';
if (!$token || empty($_SESSION['viewer_tokens'][$token])) {
    http_response_code(403);
    echo "Invalid verification token.";
    exit;
}

$data = $_SESSION['viewer_tokens'][$token];
$request_id = intval($data['request_id']);
$is_qr_verified = !empty($data['qr_verified']);

// Get document info
$stmt = $mysqli->prepare("SELECT dr.document_file_path, dr.status, dt.document_type, 
                         CONCAT(r.first_name, ' ', r.last_name) as resident_name,
                         dr.date_processed
                         FROM document_requests dr
                         JOIN document_types dt ON dt.id = dr.document_type_id
                         JOIN residents r ON r.id = dr.resident_id
                         WHERE dr.id = ?");
$stmt->bind_param('i', $request_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404);
    echo "Document not found.";
    exit;
}

$document = $res->fetch_assoc();
$stmt->close();

if ($document['status'] !== 'Approved') {
    http_response_code(403);
    echo "Document is not approved for viewing.";
    exit;
}

$pdfPath = $document['document_file_path'];
if (!file_exists($pdfPath)) {
    http_response_code(404);
    echo "PDF file not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Balas - Document Verification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .verification-banner {
            background: #27ae60;
            color: white;
            padding: 0.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .document-info {
            background: white;
            padding: 1rem;
            margin: 1rem;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .info-item {
            padding: 0.25rem 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
        }
        
        .viewer-container {
            flex: 1;
            display: flex;
            margin: 0 1rem 1rem 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        iframe {
            flex: 1;
            border: none;
        }
        
        .watermark-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 0.75rem;
            margin: 0 1rem 1rem 1rem;
            border-radius: 5px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .permanent-notice {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 0.75rem;
            margin: 0 1rem;
            border-radius: 5px;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Barangay Balas Document Verification</h1>
        <p>Official Document Viewer</p>
    </div>
    
    <div class="verification-banner">
        ✅ Document Verified via QR Code - Barangay Balas, Mexico, Pampanga
    </div>
    
    <div class="permanent-notice">
        🔒 This verification link is permanent and will not expire.
    </div>
    
    <div class="document-info">
        <h3>Document Information</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Resident:</span>
                <?php echo htmlspecialchars($document['resident_name']); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Document Type:</span>
                <?php echo htmlspecialchars($document['document_type']); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Date Issued:</span>
                <?php echo htmlspecialchars($document['date_processed']); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Verification Time:</span>
                <?php echo date('Y-m-d H:i:s'); ?>
            </div>
        </div>
    </div>
    
    <?php if ($is_qr_verified): ?>
    <div class="watermark-notice">
        This document displays a "VERIFIED VIA QR CODE" watermark when viewed via QR code scanning. 
        Official stamped copies are available at the Barangay Balas Office.
    </div>
    <?php endif; ?>
    
    <div class="viewer-container">
        <iframe src="/barangay-balas/services/certificates/stream_pdf.php?token=<?php echo urlencode($token); ?>&qr_verified=<?php echo $is_qr_verified ? '1' : '0'; ?>"></iframe>
    </div>
</body>
</html>

<?php
if (isset($watermarked_pdf) && file_exists($watermarked_pdf)) {
    register_shutdown_function(function() use ($watermarked_pdf) {
        @unlink($watermarked_pdf);
    });
}
?>