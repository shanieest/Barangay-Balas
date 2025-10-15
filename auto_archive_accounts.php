<?php
/**
 * auto_archive_accounts.php
 * 
 * This script should be run daily via cron job
 * Setup cron: 0 2 * * * /usr/bin/php /path/to/auto_archive_accounts.php
 * (Runs daily at 2 AM)
 */

require_once __DIR__ . '/config/db.php';

// Configuration
define('INACTIVE_DAYS', 365); // 1 year = 365 days

// Log file
$logFile = __DIR__ . '/logs/archive_' . date('Y-m-d') . '.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    logMessage("Starting automatic account archive process...");
    
    // Calculate cutoff date (1 year ago from today)
    $cutoffDate = date('Y-m-d H:i:s', strtotime('-' . INACTIVE_DAYS . ' days'));
    
    logMessage("Cutoff date: $cutoffDate");
    
    // Find accounts that haven't logged in for 1 year
    // IMPORTANT: Only archive approved accounts
    $query = "SELECT ra.resident_id, ra.last_login, 
                     r.first_name, r.last_name, r.email
              FROM resident_accounts ra
              JOIN residents r ON ra.resident_id = r.id
              WHERE ra.account_status = 'Approved'
                AND ra.is_archived = 0
                AND (
                    (ra.last_login IS NOT NULL AND ra.last_login < ?)
                    OR (ra.last_login IS NULL AND ra.date_processed < ?)
                )";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $cutoffDate, $cutoffDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $archivedCount = 0;
    $errors = [];
    
    while ($row = $result->fetch_assoc()) {
        $residentId = $row['resident_id'];
        $lastLogin = $row['last_login'] ?? 'Never';
        $fullName = $row['first_name'] . ' ' . $row['last_name'];
        
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Archive the account
            $archiveStmt = $conn->prepare("UPDATE resident_accounts 
                                          SET is_archived = 1, 
                                              archived_at = NOW(),
                                              archived_reason = 'Inactive for 1 year'
                                          WHERE resident_id = ?");
            $archiveStmt->bind_param("i", $residentId);
            $archiveStmt->execute();
            
            // Log to archive history
            $historyStmt = $conn->prepare("INSERT INTO account_archive_history 
                                          (resident_id, action, reason)
                                          VALUES (?, 'archived', 'Automatically archived due to 1 year inactivity')");
            $historyStmt->bind_param("i", $residentId);
            $historyStmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $archivedCount++;
            logMessage("✓ Archived account: $fullName (ID: $residentId) - Last login: $lastLogin");
            
            // Optional: Send email notification
            if (!empty($row['email'])) {
                sendArchiveNotificationEmail($row['email'], $fullName);
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to archive $fullName (ID: $residentId): " . $e->getMessage();
            $errors[] = $error;
            logMessage("✗ $error");
        }
    }
    
    logMessage("\nArchive process completed:");
    logMessage("- Total accounts archived: $archivedCount");
    logMessage("- Errors: " . count($errors));
    
    if (!empty($errors)) {
        logMessage("\nError details:");
        foreach ($errors as $error) {
            logMessage("  - $error");
        }
    }
    
} catch (Exception $e) {
    logMessage("CRITICAL ERROR: " . $e->getMessage());
    error_log("Auto archive failed: " . $e->getMessage());
}

function sendArchiveNotificationEmail($email, $name) {
    // Include your email configuration
    require_once __DIR__ . '/config/emailer.php';
    
    $subject = "Barangay Balas - Account Archived Due to Inactivity";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f8f9fa; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Account Archived</h2>
            </div>
            <div class='content'>
                <p>Dear $name,</p>
                
                <p>Your Barangay Balas resident account has been automatically archived due to inactivity for more than 1 year.</p>
                
                <p><strong>What does this mean?</strong></p>
                <ul>
                    <li>You will no longer be able to access the resident portal</li>
                    <li>Your account data has been preserved</li>
                    <li>You can request account reactivation at any time</li>
                </ul>
                
                <p><strong>To reactivate your account:</strong></p>
                <p>Please visit the Barangay Balas office or contact us at:</p>
                <ul>
                    <li>Email: barangaybalas@mexico.gov.ph</li>
                    <li>Phone: (045) XXX-XXXX</li>
                </ul>
                
                <p>We apologize for any inconvenience. This measure helps us maintain accurate records and system security.</p>
            </div>
            <div class='footer'>
                <p>Barangay Balas, Mexico, Pampanga<br>
                This is an automated message. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    try {
        sendEmail($email, $subject, $message);
        logMessage("  → Email notification sent to: $email");
    } catch (Exception $e) {
        logMessage("  → Failed to send email to $email: " . $e->getMessage());
    }
}

logMessage("Script execution completed.\n" . str_repeat("-", 80) . "\n");
?>