<?php
require_once __DIR__ . '/../../vendor/autoload.php';

function generate_random_token($length = 48) {
    return bin2hex(random_bytes((int)($length / 2)));
}

function generate_qr_for_request($mysqli, $request_id) {
    $token = generate_random_token(48);
    $created_at = date('Y-m-d H:i:s');

    $stmt = $mysqli->prepare(
        "INSERT INTO document_qr_codes (request_id, qr_code, qr_code_image_path, created_at) VALUES (?, ?, ?, ?)"
    );
    $image_filename = 'uploads/qr_codes/qr_' . $request_id . '_' . time() . '.png';
    if (!$stmt) {
        return ['error' => 'DB prepare failed: ' . $mysqli->error];
    }
    $stmt->bind_param('isss', $request_id, $token, $image_filename, $created_at);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        return ['error' => 'DB insert failed: ' . $err];
    }
    $stmt->close();

    $dir = __DIR__ . '/uploads/qr_codes';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        // delete DB row if folder creation fails
        $mysqli->query("DELETE FROM document_qr_codes WHERE request_id = " . (int)$request_id . " AND qr_code = '" . $mysqli->real_escape_string($token) . "'");
        return ['error' => 'Failed to create uploads directory: ' . $dir];
    }

    $path = __DIR__ . '/' . $image_filename;

    try {
        // Use TCPDF for QR code generation
        if (class_exists('TCPDF')) {
            generate_qr_with_tcpdf($token, $path);
        } else {
            // Fallback to simple GD image
            generate_simple_barcode($token, $path);
        }
        
    } catch (\Throwable $e) {
        $mysqli->query("DELETE FROM document_qr_codes WHERE request_id = " . (int)$request_id . " AND qr_code = '" . $mysqli->real_escape_string($token) . "'");
        return ['error' => 'QR generation failed: ' . $e->getMessage()];
    }

    return ['token' => $token, 'image_path' => $image_filename];
}

// TCPDF QR Code Generator
function generate_qr_with_tcpdf($data, $filepath) {
    // Create a minimal PDF with QR code
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    
    // QR code style
    $style = array(
        'border' => 0,
        'vpadding' => 'auto',
        'hpadding' => 'auto',
        'fgcolor' => array(0,0,0),
        'bgcolor' => false,
        'module_width' => 1,
        'module_height' => 1
    );
    
    // Generate QR code - position it in the center
    $pdf->write2DBarcode($data, 'QRCODE,L', 50, 50, 100, 100, $style, 'N');
    
    // Save as PNG
    $pdf->Output($filepath, 'F');
}

// Simple GD fallback - creates a barcode-like image
function generate_simple_barcode($data, $filepath) {
    $width = 300;
    $height = 300;
    
    // Create image
    $image = imagecreate($width, $height);
    
    // Colors
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    
    // Fill background
    imagefill($image, 0, 0, $white);
    
    // Add border
    imagerectangle($image, 5, 5, $width-5, $height-5, $black);
    
    // Convert token to binary pattern for simple "barcode"
    $binary = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $binary .= sprintf('%08b', ord($data[$i]));
    }
    
    // Draw pattern based on binary data
    $bin_length = strlen($binary);
    $block_size = min(($width - 20) / $bin_length, 5);
    
    for ($i = 0; $i < $bin_length; $i++) {
        if ($binary[$i] === '1') {
            $x = 10 + ($i * $block_size);
            imagefilledrectangle($image, $x, 50, $x + $block_size - 1, 80, $black);
        }
    }
    
    // Add text indicating this is a verification code
    $text = "Verification Code:";
    $text2 = substr($data, 0, 20) . "...";
    imagestring($image, 3, 50, 100, $text, $black);
    imagestring($image, 3, 30, 120, $text2, $black);
    imagestring($image, 2, 40, 150, "Scan with Barangay Balas App", $black);
    
    // Save image
    imagepng($image, $filepath);
    imagedestroy($image);
}
?>