<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../../config/emailer.php'; 
require_once '../../email_templates/medicine_status.php'; 
requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_medicine':
                addMedicine($conn);
                break;
                
            case 'update_medicine':
                updateMedicine($conn);
                break;
                
            case 'delete_medicine':
                deleteMedicine($conn);
                break;
                
            case 'update_request_status':
                updateRequestStatus($conn);
                break;
        }
        header("Location: ../pages/medicineRequest.php");
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'filter_requests') {
    filterRequests($conn);
    exit;
}


function addMedicine($conn) {
    $medicine_name = trim($_POST['medicine_name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $minimum_stock = intval($_POST['minimum_stock']);
    $unit = trim($_POST['unit']);
    
    if (empty($medicine_name) || $stock_quantity < 0 || $minimum_stock < 0) {
        $_SESSION['error'] = "Please fill all required fields with valid values!";
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO medicine_inventory (medicine_name, category, description, stock_quantity, minimum_stock, unit) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiis", $medicine_name, $category, $description, $stock_quantity, $minimum_stock, $unit);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Medicine added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add medicine: " . $stmt->error;
    }
    $stmt->close();
}

function updateMedicine($conn) {
    $id = intval($_POST['id']);
    $medicine_name = trim($_POST['medicine_name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $minimum_stock = intval($_POST['minimum_stock']);
    $unit = trim($_POST['unit']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($medicine_name) || $stock_quantity < 0 || $minimum_stock < 0) {
        $_SESSION['error'] = "Please fill all required fields with valid values!";
        return;
    }
    
    $stmt = $conn->prepare("UPDATE medicine_inventory SET medicine_name=?, category=?, description=?, stock_quantity=?, minimum_stock=?, unit=?, is_active=? WHERE id=?");
    $stmt->bind_param("sssiisii", $medicine_name, $category, $description, $stock_quantity, $minimum_stock, $unit, $is_active, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Medicine updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update medicine: " . $stmt->error;
    }
    $stmt->close();
}

function deleteMedicine($conn) {
    $id = intval($_POST['id']);
    
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM medicine_requests WHERE medicine_name = (SELECT medicine_name FROM medicine_inventory WHERE id = ?)");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $request_count = 0;
    $check_stmt->bind_result($request_count);
    $check_stmt->fetch();
    $check_stmt->close();
    $request_count = intval($request_count);
    
    if ($request_count > 0) {
        $_SESSION['error'] = "Cannot delete medicine that has existing requests!";
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM medicine_inventory WHERE id=?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Medicine deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete medicine: " . $stmt->error;
    }
    $stmt->close();
}

function updateRequestStatus($conn) {
    if (!isset($_POST['request_id']) || !isset($_POST['status'])) {
        $_SESSION['error'] = "Missing required parameters!";
        return;
    }
    
    $request_id = intval($_POST['request_id']);
    $status = trim($_POST['status']);
    $admin_notes = trim($_POST['admin_notes'] ?? '');
    $disapproval_reason = trim($_POST['disapproval_reason'] ?? '');
    
    $allowed_statuses = ['Pending', 'Approved', 'Disapproved', 'Completed'];
    if (!in_array($status, $allowed_statuses)) {
        $_SESSION['error'] = "Invalid status provided!";
        return;
    }
    
    if ($status === 'Disapproved' && empty($disapproval_reason)) {
        $_SESSION['error'] = "Disapproval reason is required!";
        return;
    }
    
    $check_stmt = $conn->prepare("SELECT id FROM medicine_requests WHERE id = ?");
    $check_stmt->bind_param("i", $request_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows === 0) {
        $_SESSION['error'] = "Request not found!";
        $check_stmt->close();
        return;
    }
    $check_stmt->close();
    
    $stmt = $conn->prepare("UPDATE medicine_requests SET status=?, admin_notes=?, disapproval_reason=?, date_processed=NOW(), processed_by=? WHERE id=?");
    $admin_id = $_SESSION['admin_id'] ?? 1;
    $stmt->bind_param("sssii", $status, $admin_notes, $disapproval_reason, $admin_id, $request_id);
    
    if ($stmt->execute()) {
        sendMedicineStatusEmail($conn, $request_id, $status, $admin_notes, $disapproval_reason);
        $_SESSION['success'] = "Request status updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update request: " . $stmt->error;
    }
    $stmt->close();
}

function filterRequests($conn) {
    $status = trim($_GET['status'] ?? '');
    $medicine = trim($_GET['medicine'] ?? '');
    $urgency = trim($_GET['urgency'] ?? '');
    $search = trim($_GET['search'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));
    $per_page = max(1, min(200, intval($_GET['per_page'] ?? 25)));
    $offset = ($page - 1) * $per_page;

    $clauses = [];
    if ($status !== '') $clauses[] = "mr.status = '" . $conn->real_escape_string($status) . "'";
    if ($medicine !== '') $clauses[] = "mr.medicine_name LIKE '%" . $conn->real_escape_string($medicine) . "%'";
    if ($urgency !== '') $clauses[] = "mr.urgency_level = '" . $conn->real_escape_string($urgency) . "'";
    if ($search !== '') {
        $s = $conn->real_escape_string($search);
        $clauses[] = "(
            mr.request_number LIKE '%{$s}%' OR
            mr.medicine_name LIKE '%{$s}%' OR
            r.first_name LIKE '%{$s}%' OR
            r.last_name LIKE '%{$s}%' OR
            r.email LIKE '%{$s}%'
        )";
    }

    $whereSql = count($clauses) ? 'WHERE ' . implode(' AND ', $clauses) : '';

    $countSql = "SELECT COUNT(*) AS total FROM medicine_requests mr JOIN residents r ON mr.resident_id = r.id {$whereSql}";
    $countRes = $conn->query($countSql);
    $total = $countRes ? intval($countRes->fetch_assoc()['total']) : 0;

    $sql = "
        SELECT mr.*, r.first_name, r.last_name, r.email
        FROM medicine_requests mr
        JOIN residents r ON mr.resident_id = r.id
        {$whereSql}
        ORDER BY mr.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ";
    $res = $conn->query($sql);

    $items = [];
    if ($res) while ($r = $res->fetch_assoc()) $items[] = $r;

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['page' => $page, 'per_page' => $per_page, 'total' => $total, 'items' => $items]);
}
?>