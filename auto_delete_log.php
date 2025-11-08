<?php
require_once __DIR__ . '/config/db.php';

$secretKey = 'qwertyuiop0asdfghjkl9zxcvbnm2';
$logFile   = __DIR__ . '/auto_delete_log.txt';
$enableEmailReport = true;

$adminEmail = 'balasmexico2026@gmail.com';
$fromEmail  = 'no-reply@yourdomain.com';
$siteName   = 'Barangay Balas Online Services and Management System';

if (!isset($_GET['token']) || $_GET['token'] !== $secretKey) {
    http_response_code(403);
    exit('Unauthorized Access');
}

function logMessage($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    echo nl2br($logEntry);
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

logMessage("=== Starting Automated Cleanup Process ===", $logFile);

$deletedSummary = [];


$query = "DELETE FROM activity_logs WHERE timestamp < DATE_SUB(NOW(), INTERVAL 7 DAY)";
if ($conn->query($query)) {
    $count = $conn->affected_rows;
    logMessage("🧹 Deleted $count activity_logs older than 7 days.", $logFile);
    $deletedSummary[] = ["Activity Logs", "$count deleted (older than 7 days)"];
}

$query = "
    DELETE FROM daycare_enrollments 
    WHERE CONCAT(SUBSTRING_INDEX(school_year, '-', -1), '-12-31')
    < DATE_SUB(NOW(), INTERVAL 13 MONTH)
";
if ($conn->query($query)) {
    $count = $conn->affected_rows;
    logMessage("🧹 Deleted $count daycare_enrollments older than 13 months.", $logFile);
    $deletedSummary[] = ["Daycare Enrollments", "$count deleted (older than 13 months)"];
}

$query = "DELETE FROM document_qr_codes WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
if ($conn->query($query)) {
    $count = $conn->affected_rows;
    logMessage("🧹 Deleted $count document_qr_codes older than 7 days.", $logFile);
    $deletedSummary[] = ["Document QR Codes", "$count deleted (older than 7 days)"];
}

$query = "
    DELETE FROM document_requests 
    WHERE status IN ('Approved', 'Cancelled')
    AND (
        (date_processed IS NOT NULL AND date_processed < DATE_SUB(NOW(), INTERVAL 15 DAY))
        OR (date_processed IS NULL AND date_requested < DATE_SUB(NOW(), INTERVAL 15 DAY))
    )
";
if ($conn->query($query)) {
    $count = $conn->affected_rows;
    logMessage("🧹 Deleted $count document_requests (Approved/Cancelled) older than 15 days.", $logFile);
    $deletedSummary[] = ["Document Requests (Approved/Cancelled)", "$count deleted (older than 15 days)"];
}

$query = "
    DELETE FROM document_requests 
    WHERE status = 'Disapproved'
    AND (
        (date_processed IS NOT NULL AND date_processed < DATE_SUB(NOW(), INTERVAL 1 MONTH))
        OR (date_processed IS NULL AND date_requested < DATE_SUB(NOW(), INTERVAL 1 MONTH))
    )
";
if ($conn->query($query)) {
    $count = $conn->affected_rows;
    logMessage("🧹 Deleted $count document_requests (Disapproved) older than 1 month.", $logFile);
    $deletedSummary[] = ["Document Requests (Disapproved)", "$count deleted (older than 1 month)"];
}

$query = "
    DELETE FROM service_reservations 
    WHERE status IN ('Completed', 'Cancelled', 'Disapproved')
    AND (
        (date_processed IS NOT NULL AND date_processed < DATE_SUB(NOW(), INTERVAL 15 DAY))
        OR (date_processed IS NULL AND date_requested < DATE_SUB(NOW(), INTERVAL 15 DAY))
    )
";
if ($conn->query($query)) {
    $count = $conn->affected_rows;
    logMessage("🧹 Deleted $count service_reservations older than 15 days.", $logFile);
    $deletedSummary[] = ["Service Reservations", "$count deleted (older than 15 days)"];
}

$query = "
    DELETE FROM medicine_requests 
    WHERE status IN ('Completed', 'Disapproved')
    AND (
        (date_processed IS NOT NULL AND date_processed < DATE_SUB(NOW(), INTERVAL 15 DAY))
        OR (date_processed IS NULL AND date_requested < DATE_SUB(NOW(), INTERVAL 15 DAY))
    )
";
if ($conn->query($query)) {
    $count = $conn->affected_rows;
    logMessage("🧹 Deleted $count medicine_requests older than 15 days.", $logFile);
    $deletedSummary[] = ["Medicine Requests", "$count deleted (older than 15 days)"];
}

$qrDir = __DIR__ . '/admin/public/uploads/qr_codes/';
if (is_dir($qrDir)) {
    $files = glob($qrDir . 'qr_*.png');
    $cleanedFiles = 0;

    foreach ($files as $file) {
        $filename = basename($file);
        $checkQuery = "SELECT COUNT(*) AS cnt FROM document_qr_codes WHERE qr_code_image_path LIKE '%" . $conn->real_escape_string($filename) . "%'";
        $result = $conn->query($checkQuery);
        if ($result && ($row = $result->fetch_assoc()) && $row['cnt'] == 0) {
            if (@unlink($file)) {
                $cleanedFiles++;
            }
        }
    }

    if ($cleanedFiles > 0) {
        logMessage("🗑️ Cleaned $cleanedFiles orphaned QR code files.", $logFile);
        $deletedSummary[] = ["Orphaned QR Code Files", "$cleanedFiles files deleted"];
    }
}

$conn->close();
logMessage("=== Cleanup Process Completed ===\n", $logFile);

if ($enableEmailReport) {
    $subject = "🧹 Auto Cleanup Report - " . date('Y-m-d H:i');
    $dateNow = date('F j, Y g:i A');

    $rows = '';
    if (!empty($deletedSummary)) {
        foreach ($deletedSummary as [$title, $detail]) {
            $rows .= "<tr>
                        <td style='padding:8px;border:1px solid #ddd;'>$title</td>
                        <td style='padding:8px;border:1px solid #ddd;color:#333;'>$detail</td>
                      </tr>";
        }
    } else {
        $rows = "<tr><td colspan='2' style='padding:10px;text-align:center;'>No deletions performed.</td></tr>";
    }

    $body = "
    <html>
    <body style='font-family:Arial, sans-serif;background:#f8f9fa;padding:20px;'>
        <div style='max-width:600px;margin:auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 0 10px rgba(0,0,0,0.1);'>
            <div style='background:#007bff;color:white;padding:15px;text-align:center;font-size:18px;'>
                🧹 Auto Cleanup Report
            </div>
            <div style='padding:20px;'>
                <p>Hello Admin,</p>
                <p>The automatic cleanup process completed successfully on <b>$dateNow</b>.</p>
                <table style='width:100%;border-collapse:collapse;margin-top:15px;'>
                    <thead>
                        <tr style='background:#007bff;color:white;'>
                            <th style='padding:8px;border:1px solid #ddd;'>Table / Task</th>
                            <th style='padding:8px;border:1px solid #ddd;'>Summary</th>
                        </tr>
                    </thead>
                    <tbody>$rows</tbody>
                </table>
                <p style='margin-top:15px;'>Cleanup completed successfully.</p>
            </div>
            <div style='background:#f1f1f1;color:#555;padding:10px;text-align:center;font-size:12px;'>
                $siteName &middot; Automated Maintenance System
            </div>
        </div>
    </body>
    </html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: $siteName <$fromEmail>\r\n";

    @mail($adminEmail, $subject, $body, $headers);
}
?>
