<?php
require_once 'config/emailer.php';
require_once __DIR__ . '/config/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// === CONFIGURATION ===
$secretKey = 'qwertyuiop0asdfghjkl9zxcvbnm2';
$enableEmailReport = true;
$adminEmail = 'balasmexico2026@gmail.com';
$siteName   = 'Barangay Balas Online Services and Management System';

define('INACTIVE_DAYS', 365); // archive accounts inactive for 1 year
define('BATCH_SIZE', 50);

$projectRoot = __DIR__;
$logDir = $projectRoot . '/logs';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

$logFile = $logDir . '/archive_' . date('Y-m-d') . '.log';

if (!isset($_GET['token']) || $_GET['token'] !== $secretKey) {
    http_response_code(403);
    exit('Unauthorized Access');
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $formattedMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    echo nl2br($formattedMessage);
}

try {
    logMessage("=== Starting automatic account archive process ===");

    $cutoffDate = date('Y-m-d H:i:s', strtotime('-' . INACTIVE_DAYS . ' days'));
    logMessage("Cutoff date for inactivity: $cutoffDate");

    $query = "SELECT ra.resident_id, ra.last_login, ra.date_processed,
                     r.first_name, r.last_name, r.email
              FROM resident_accounts ra
              JOIN residents r ON ra.resident_id = r.id
              WHERE ra.account_status = 'Approved'
                AND ra.is_archived = 0
                AND (
                    (ra.last_login IS NOT NULL AND ra.last_login < ?)
                    OR (ra.last_login IS NULL AND ra.date_processed < ?)
                )
              LIMIT ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

    $stmt->bind_param("ssi", $cutoffDate, $cutoffDate, BATCH_SIZE);
    $stmt->execute();
    $result = $stmt->get_result();

    $archivedCount = 0;
    $errors = [];

    logMessage("Found " . $result->num_rows . " accounts to archive.");

    while ($row = $result->fetch_assoc()) {
        $residentId = $row['resident_id'];
        $fullName = $row['first_name'] . ' ' . $row['last_name'];
        $lastLogin = $row['last_login'] ?? 'Never';

        try {
            $conn->begin_transaction();

            $archiveStmt = $conn->prepare(
                "UPDATE resident_accounts 
                 SET is_archived = 1, archived_at = NOW(),
                     archived_reason = 'Inactive for 1 year',
                     account_status = 'Pending'
                 WHERE resident_id = ?"
            );
            $archiveStmt->bind_param("i", $residentId);
            $archiveStmt->execute();

            if ($archiveStmt->affected_rows === 0) {
                throw new Exception("No rows affected when archiving account");
            }

            $historyStmt = $conn->prepare(
                "INSERT INTO account_archive_history 
                 (resident_id, action, reason, performed_by)
                 VALUES (?, 'archived', 'Automatically archived due to 1 year inactivity', NULL)"
            );
            $historyStmt->bind_param("i", $residentId);
            $historyStmt->execute();

            $conn->commit();
            $archivedCount++;
            logMessage("✓ Archived: $fullName (ID: $residentId) - Last login: $lastLogin");

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to archive $fullName (ID: $residentId): " . $e->getMessage();
            $errors[] = $error;
            logMessage("✗ $error");
        }
    }

    $stmt->close();
    logMessage("=== Archive process completed ===");
    logMessage("Total accounts archived: $archivedCount");
    logMessage("Total errors: " . count($errors));

    if (!empty($errors)) {
        logMessage("Error details:");
        foreach ($errors as $error) logMessage("  - $error");
    }

} catch (Exception $e) {
    logMessage("CRITICAL ERROR: " . $e->getMessage());
}

logMessage("Script execution completed at: " . date('Y-m-d H:i:s'));
logMessage(str_repeat("=", 60) . "\n");

if ($enableEmailReport) {
    $subject = "🗄️ Auto Archive Report - " . date('Y-m-d H:i');
    $body = "<p>Hello Admin,</p>
             <p>The automatic archive process completed successfully on <b>" . date('F j, Y g:i A') . "</b>.</p>
             <p>Total accounts archived: $archivedCount<br>Total errors: " . count($errors) . "</p>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: $siteName <no-reply@yourdomain.com>\r\n";

    @mail($adminEmail, $subject, $body, $headers);
}

$conn->close();
?>
