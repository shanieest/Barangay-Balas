<?php
// generate_qr.php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Small helper to generate a random token.
 */
function generate_random_token($length = 48) {
    return bin2hex(random_bytes((int)($length / 2)));
}

/**
 * Creates a QR token row and image file for a request id.
 * Returns array with token and image path or ['error'=>...].
 */
function generate_qr_for_request($mysqli, $request_id) {
    $token = generate_random_token(48);
    $created_at = date('Y-m-d H:i:s');

    // Insert DB row first (keeps behavior similar to your original code)
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

    // Ensure uploads folder exists
    $dir = __DIR__ . '/uploads/qr_codes';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        // delete DB row if folder creation fails
        $mysqli->query("DELETE FROM document_qr_codes WHERE request_id = " . (int)$request_id . " AND qr_code = '" . $mysqli->real_escape_string($token) . "'");
        return ['error' => 'Failed to create uploads directory: ' . $dir];
    }

    $path = __DIR__ . '/' . $image_filename;

    // Try multiple Endroid API styles so this works across versions.
    try {
        // 1) BuilderFactory (v4+ builder/registry style)
        if (class_exists('\Endroid\QrCode\Builder\BuilderFactory')) {
            $factory = new \Endroid\QrCode\Builder\BuilderFactory();
            $result = $factory->create()
                ->data($token)
                ->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
                ->size(300)
                ->margin(10)
                ->build();
            $result->saveToFile($path);
        }

        // 2) Builder::create() (some versions expose a Builder class with static create())
        elseif (class_exists('\Endroid\QrCode\Builder\Builder') && method_exists('\Endroid\QrCode\Builder\Builder', 'create')) {
            $qr = \Endroid\QrCode\Builder\Builder::create()
                ->data($token)
                ->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
                ->size(300)
                ->margin(10)
                ->build();
            $qr->saveToFile($path);
        }

        // 3) QrCode + PngWriter (common v3/v4 pattern)
        elseif (class_exists('\Endroid\QrCode\Writer\PngWriter') && class_exists('\Endroid\QrCode\QrCode')) {
            // Build QrCode object (support both static create() and constructor)
            if (method_exists('\Endroid\QrCode\QrCode', 'create')) {
                $qrCode = \Endroid\QrCode\QrCode::create($token);
                if (method_exists($qrCode, 'setSize')) $qrCode->setSize(300);
                if (method_exists($qrCode, 'setMargin')) $qrCode->setMargin(10);
            } else {
                $qrCode = new \Endroid\QrCode\QrCode($token);
                if (method_exists($qrCode, 'setSize')) $qrCode->setSize(300);
                if (method_exists($qrCode, 'setMargin')) $qrCode->setMargin(10);
            }

            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);
            $result->saveToFile($path);
        }

        // 4) Very old API: QrCode->writeFile (fallback)
        elseif (class_exists('\Endroid\QrCode\QrCode') && method_exists('\Endroid\QrCode\QrCode', 'writeFile')) {
            $qrCode = new \Endroid\QrCode\QrCode($token);
            if (method_exists($qrCode, 'setSize')) $qrCode->setSize(300);
            if (method_exists($qrCode, 'setMargin')) $qrCode->setMargin(10);
            $qrCode->writeFile($path);
        }

        // 5) No compatible API found
        else {
            // cleanup DB row
            $mysqli->query("DELETE FROM document_qr_codes WHERE request_id = " . (int)$request_id . " AND qr_code = '" . $mysqli->real_escape_string($token) . "'");
            return ['error' => 'No compatible endroid/qr-code API found in vendor (version mismatch).'];
        }
    } catch (\Throwable $e) {
        // remove DB row on failure
        $mysqli->query("DELETE FROM document_qr_codes WHERE request_id = " . (int)$request_id . " AND qr_code = '" . $mysqli->real_escape_string($token) . "'");
        return ['error' => 'QR generation failed: ' . $e->getMessage()];
    }

    return ['token' => $token, 'image_path' => $image_filename];
}
