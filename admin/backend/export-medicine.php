<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAuth();

if (!isset($_GET['type'])) {
    die('Export type not specified');
}

$type = $_GET['type'];

if ($type === 'inventory') {
    exportInventoryToExcel($conn);
} elseif ($type === 'requests') {
    exportRequestsToExcel($conn);
} else {
    die('Invalid export type');
}

function exportInventoryToExcel($conn) {
    // Fetch all inventory data
    $query = "SELECT * FROM medicine_inventory ORDER BY medicine_name";
    $result = $conn->query($query);
    
    if (!$result) {
        die('Error fetching inventory data');
    }
    
    $filename = 'medicine_inventory_' . date('Y-m-d_His') . '.xls';
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Start Excel XML
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<?mso-application progid="Excel.Sheet"?>';
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
    echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
    echo '<Worksheet ss:Name="Medicine Inventory">';
    echo '<Table>';
    
    // Header row with styling
    echo '<Row>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">ID</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Medicine Name</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Category</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Description</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Stock Quantity</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Minimum Stock</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Unit</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Status</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Created At</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Updated At</Data></Cell>';
    echo '</Row>';
    
    // Data rows
    while ($row = $result->fetch_assoc()) {
        echo '<Row>';
        echo '<Cell><Data ss:Type="Number">' . htmlspecialchars($row['id']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['medicine_name']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['category']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['description']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . htmlspecialchars($row['stock_quantity']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . htmlspecialchars($row['minimum_stock']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['unit']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . ($row['is_active'] ? 'Active' : 'Inactive') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['created_at']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['updated_at']) . '</Data></Cell>';
        echo '</Row>';
    }
    
    echo '</Table>';
    echo '</Worksheet>';
    
    // Add styles
    echo '<Styles>';
    echo '<Style ss:ID="header">';
    echo '<Font ss:Bold="1"/>';
    echo '<Interior ss:Color="#4472C4" ss:Pattern="Solid"/>';
    echo '<Font ss:Color="#FFFFFF"/>';
    echo '</Style>';
    echo '</Styles>';
    
    echo '</Workbook>';
    exit;
}

function exportRequestsToExcel($conn) {
    // Get status filter if provided
    $status_filter = isset($_GET['status']) && $_GET['status'] !== 'all' ? $_GET['status'] : '';
    
    $where_clause = '';
    if ($status_filter !== '') {
        $status = $conn->real_escape_string($status_filter);
        $where_clause = "WHERE mr.status = '$status'";
    }
    
    // Fetch all request data with resident information
    $query = "
        SELECT 
            mr.id,
            mr.request_number,
            mr.resident_id,
            r.first_name,
            r.last_name,
            r.email,
            mr.medicine_name,
            mr.medical_condition,
            mr.urgency_level,
            mr.additional_notes,
            mr.prescription_path,
            mr.status,
            mr.admin_notes,
            mr.disapproval_reason,
            mr.date_requested,
            mr.date_processed,
            mr.created_at,
            mr.updated_at
        FROM medicine_requests mr
        JOIN residents r ON mr.resident_id = r.id
        $where_clause
        ORDER BY mr.created_at DESC
    ";
    
    $result = $conn->query($query);
    
    if (!$result) {
        die('Error fetching request data');
    }
    
    // Set filename
    $filename = 'medicine_requests';
    if ($status_filter !== '') {
        $filename .= '_' . strtolower($status_filter);
    }
    $filename .= '_' . date('Y-m-d_His') . '.xls';
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Start Excel XML
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<?mso-application progid="Excel.Sheet"?>';
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
    echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
    echo '<Worksheet ss:Name="Medicine Requests">';
    echo '<Table>';
    
    // Header row
    echo '<Row>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Request ID</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Request Number</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Resident ID</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">First Name</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Last Name</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Email</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Medicine Name</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Medical Condition</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Urgency Level</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Additional Notes</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Has Prescription</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Status</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Admin Notes</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Disapproval Reason</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Date Requested</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Date Processed</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Created At</Data></Cell>';
    echo '<Cell ss:StyleID="header"><Data ss:Type="String">Updated At</Data></Cell>';
    echo '</Row>';
    
    // Data rows
    while ($row = $result->fetch_assoc()) {
        echo '<Row>';
        echo '<Cell><Data ss:Type="Number">' . htmlspecialchars($row['id']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['request_number']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . htmlspecialchars($row['resident_id']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['first_name']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['last_name']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['email']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['medicine_name']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['medical_condition']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars(ucfirst($row['urgency_level'])) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['additional_notes'] ?? '') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . ($row['prescription_path'] ? 'Yes' : 'No') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['status']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['admin_notes'] ?? '') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['disapproval_reason'] ?? '') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['date_requested']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['date_processed'] ?? '') . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['created_at']) . '</Data></Cell>';
        echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['updated_at']) . '</Data></Cell>';
        echo '</Row>';
    }
    
    echo '</Table>';
    echo '</Worksheet>';
    
    // Add styles
    echo '<Styles>';
    echo '<Style ss:ID="header">';
    echo '<Font ss:Bold="1"/>';
    echo '<Interior ss:Color="#4472C4" ss:Pattern="Solid"/>';
    echo '<Font ss:Color="#FFFFFF"/>';
    echo '</Style>';
    echo '</Styles>';
    
    echo '</Workbook>';
    exit;
}
?>