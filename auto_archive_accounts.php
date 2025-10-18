<?php
require_once 'config/emailer.php';


// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the absolute path to the project root
$projectRoot = __DIR__;

// Include database configuration
require_once $projectRoot . '/config/db.php';

// Configuration
define('INACTIVE_DAYS', 365); // 1 year = 365 days
define('BATCH_SIZE', 50);

// Create logs directory if it doesn't exist
$logDir = $projectRoot . '/logs';
if (!is_dir($logDir)) {
    if (!mkdir($logDir, 0755, true)) {
        die("Failed to create logs directory: $logDir");
    }
}

// Log file
$logFile = $logDir . '/archive_' . date('Y-m-d') . '.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $formattedMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    echo $formattedMessage;
}

try {
    logMessage("=== Starting automatic account archive process ===");
    logMessage("Project root: " . __DIR__);
    
    // Calculate cutoff date (1 year ago from today)
    $cutoffDate = date('Y-m-d H:i:s', strtotime('-' . INACTIVE_DAYS . ' days'));
    
    logMessage("Cutoff date: $cutoffDate");
    logMessage("Looking for accounts inactive since: $cutoffDate");

    // Find accounts to archive
    $query = "SELECT ra.resident_id, ra.last_login, ra.date_processed,
                     r.first_name, r.last_name, r.email, r.contact_number
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
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $limit = BATCH_SIZE;
    $stmt->bind_param("ssi", $cutoffDate, $cutoffDate, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $archivedCount = 0;
    $errors = [];
    
    logMessage("Found " . $result->num_rows . " accounts to process");
    
    while ($row = $result->fetch_assoc()) {
        $residentId = $row['resident_id'];
        $lastLogin = $row['last_login'] ?? 'Never';
        $fullName = $row['first_name'] . ' ' . $row['last_name'];
        $email = $row['email'];
        
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Archive the account 
            $archiveStmt = $conn->prepare("UPDATE resident_accounts 
                                          SET is_archived = 1, 
                                              archived_at = NOW(),
                                              archived_reason = 'Inactive for 1 year',
                                              account_status = 'Pending'
                                          WHERE resident_id = ?");
            $archiveStmt->bind_param("i", $residentId);
            $archiveStmt->execute();
            
            if ($archiveStmt->affected_rows === 0) {
                throw new Exception("No rows affected when archiving account");
            }
            
            // Log to archive history
            $historyStmt = $conn->prepare("INSERT INTO account_archive_history 
                                          (resident_id, action, reason, performed_by)
                                          VALUES (?, 'archived', 'Automatically archived due to 1 year inactivity', NULL)");
            $historyStmt->bind_param("i", $residentId);
            $historyStmt->execute();
            
            // Commit transaction
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
    
    logMessage("\n=== Archive process completed ===");
    logMessage("Total accounts archived: $archivedCount");
    logMessage("Errors: " . count($errors));
    
    if (!empty($errors)) {
        logMessage("Error details:");
        foreach ($errors as $error) {
            logMessage("  - $error");
        }
    }
    
} catch (Exception $e) {
    logMessage("CRITICAL ERROR: " . $e->getMessage());
}

logMessage("Script execution completed at: " . date('Y-m-d H:i:s'));
logMessage(str_repeat("=", 60) . "\n");
?>