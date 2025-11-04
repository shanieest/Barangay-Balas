<?php
require_once  '../../config/emailer.php';
require_once '../includes/auth.php';
require_once  '../includes/db.php';

requireAuth();

// Check if user is admin
if (!isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin only.']);
    exit();
}

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'list_archived':
            handleListArchived();
            break;
        case 'restore':
            handleRestoreAccount();
            break;
        case 'view_history':
            handleViewHistory();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
}

function handleListArchived() {
    global $conn, $response;
    
    $page = $_GET['page'] ?? 1;
    $perPage = $_GET['per_page'] ?? 10;
    $search = $_GET['search'] ?? '';
    
    $offset = ($page - 1) * $perPage;
    
    $query = "SELECT r.*, 
                     ra.account_status, ra.is_archived, ra.archived_at, ra.archived_reason,
                     ra.last_login, ra.date_processed
              FROM residents r
              JOIN resident_accounts ra ON r.id = ra.resident_id
              WHERE ra.is_archived = 1";
    
    $countQuery = "SELECT COUNT(*) as total
                   FROM residents r
                   JOIN resident_accounts ra ON r.id = ra.resident_id
                   WHERE ra.is_archived = 1";
    
    $params = [];
    $types = '';
    
    if ($search) {
        $searchTerm = "%$search%";
        $query .= " AND (CONCAT(r.first_name, ' ', r.last_name) LIKE ? 
                    OR r.email LIKE ? 
                    OR r.contact_number LIKE ?)";
        $countQuery .= " AND (CONCAT(r.first_name, ' ', r.last_name) LIKE ? 
                         OR r.email LIKE ? 
                         OR r.contact_number LIKE ?)";
        $params = [$searchTerm, $searchTerm, $searchTerm];
        $types = 'sss';
    }
    
    // Count total
    $countStmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();

    if ($countResult && $row = $countResult->fetch_assoc()) {
        $total = (int)$row['total'];
    } else {
        $total = 0;
    }
    
    // Get data
    $query .= " ORDER BY ra.archived_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $accounts = [];
    while ($row = $result->fetch_assoc()) {
        $accounts[] = $row;
    }
    
    $response['success'] = true;
    $response['data'] = $accounts;
    $response['pagination'] = [
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total / $perPage)
    ];
    
    echo json_encode($response);
}

function handleRestoreAccount() {
    global $conn, $response;
    
    $residentId = $_POST['resident_id'] ?? 0;
    $adminId = getUserId();
    $notes = $_POST['notes'] ?? 'Account restored by admin';
    
    if (!$residentId) {
        throw new Exception("Resident ID is required");
    }
    
    // Check if account exists and is archived
    $checkStmt = $conn->prepare("SELECT r.first_name, r.last_name, r.email, ra.is_archived
                                 FROM residents r
                                 JOIN resident_accounts ra ON r.id = ra.resident_id
                                 WHERE r.id = ?");
    $checkStmt->bind_param("i", $residentId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Account not found");
    }
    
    $account = $result->fetch_assoc();
    
    if ($account['is_archived'] == 0) {
        throw new Exception("Account is not archived");
    }
    
    try {
        $conn->begin_transaction();
        
        // Restore account
        $restoreStmt = $conn->prepare("UPDATE resident_accounts 
                                      SET is_archived = 0, 
                                          archived_at = NULL,
                                          archived_reason = NULL,
                                          account_status = 'Approved'
                                      WHERE resident_id = ?");
        $restoreStmt->bind_param("i", $residentId);
        $restoreStmt->execute();
        
        // Log to history
        $historyStmt = $conn->prepare("INSERT INTO account_archive_history 
                                      (resident_id, action, reason, performed_by)
                                      VALUES (?, 'restored', ?, ?)");
        $historyStmt->bind_param("isi", $residentId, $notes, $adminId);
        $historyStmt->execute();
        
        $conn->commit();
        
        // Send email notification
        if (!empty($account['email'])) {
            sendRestoreNotificationEmail(
                $account['email'], 
                $account['first_name'] . ' ' . $account['last_name']
            );
        }
        
        $response['success'] = true;
        $response['message'] = 'Account restored successfully';
        
    } catch (Exception $e) {
        $conn->rollback();
        throw new Exception("Failed to restore account: " . $e->getMessage());
    }
    
    echo json_encode($response);
}

function handleViewHistory() {
    global $conn, $response;
    
    $residentId = $_GET['resident_id'] ?? 0;
    
    if (!$residentId) {
        throw new Exception("Resident ID is required");
    }
    
    $query = "SELECT h.*, 
                     CONCAT(a.first_name, ' ', a.last_name) as performed_by_name
              FROM account_archive_history h
              LEFT JOIN admin_users a ON h.performed_by = a.id
              WHERE h.resident_id = ?
              ORDER BY h.performed_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $residentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    $response['success'] = true;
    $response['data'] = $history;
    
    echo json_encode($response);
}

function sendRestoreNotificationEmail($email, $name) {
    require_once __DIR__ . '/../config/emailer.php';
    
    $subject = "Barangay Balas - Account Reactivated";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #28a745; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f8f9fa; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Account Reactivated!</h2>
            </div>
            <div class='content'>
                <p>Dear $name,</p>
                
                <p>Good news! Your Barangay Balas resident account has been successfully reactivated.</p>
                
                <p>You can now log in and access all portal services:</p>
                <ul>
                    <li>Request documents</li>
                    <li>Book services</li>
                    <li>View announcements</li>
                    <li>Update your profile</li>
                </ul>
                
                <p>If you did not request this reactivation, please contact us immediately.</p>
                
                <p>Thank you!</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    sendEmail($email, $subject, $message);
}
?>