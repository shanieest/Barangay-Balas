<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

function bindParams(mysqli_stmt $stmt, string $types, array $params) {
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

try {
    $action = $_GET['action'] ?? '';
    $user_id = getUserId();

    switch ($action) {
        case 'list':
            handleListResidents();
            break;
        case 'account_requests':
            handleAccountRequests();
            break;
        case 'add':
            handleAddResident();
            break;
        case 'edit':
            handleEditResident();
            break;
        case 'delete':
            handleDeleteResident();
            break;
        case 'process_request':
            handleProcessRequest();
            break;
        case 'export':
            handleExportResidents();
            break;
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            exit();
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit();
}

function handleListResidents() {
    global $conn, $response;

    $page = $_GET['page'] ?? 1;
    $per_page = $_GET['per_page'] ?? 10;
    $search = $_GET['search'] ?? '';
    $id = $_GET['id'] ?? null;

    $offset = ($page - 1) * $per_page;

    $query = "SELECT r.*, 
              ra.account_status, 
              ra.notes as account_notes, 
              ra.date_processed as account_date_processed, 
              CONCAT(a.first_name, ' ', a.last_name) as account_processed_by
              FROM residents r
              LEFT JOIN resident_accounts ra ON r.id = ra.resident_id
              LEFT JOIN admin_users a ON ra.processed_by = a.id";

    $where = [];
    $params = [];
    $types = '';

    if ($id) {
        $where[] = "r.id = ?";
        $params[] = $id;
        $types .= 'i';
    } else {
        $where[] = "ra.account_status = 'Approved'";
        if ($search) {
            $where[] = "(CONCAT(r.first_name, ' ', r.last_name) LIKE ? 
                       OR r.contact_number LIKE ? 
                       OR r.email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    // Count query
    $countQuery = "SELECT COUNT(*) as total 
                   FROM residents r
                   INNER JOIN resident_accounts ra ON r.id = ra.resident_id";
    if (!empty($where)) {
        $countQuery .= " WHERE " . implode(" AND ", $where);
    }

    $countStmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        bindParams($countStmt, $types, $params);
    }
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];

    // Data query
    $query .= " ORDER BY r.last_name, r.first_name LIMIT ? OFFSET ?";
    $params_with_limit = array_merge($params, [$per_page, $offset]);
    $types_with_limit = $types . 'ii';

    $stmt = $conn->prepare($query);
    bindParams($stmt, $types_with_limit, $params_with_limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $residents = [];
    while ($row = $result->fetch_assoc()) {
        // Compute age
        if (!empty($row['birthdate'])) {
            $dob = new DateTime($row['birthdate']);
            $today = new DateTime();
            $row['age'] = $dob->diff($today)->y;
        } else {
            $row['age'] = null;
        }
        // Remove verification_status from output
        unset($row['verification_status']);
        $residents[] = $row;
    }

    $response['success'] = true;
    $response['data'] = $id ? ($residents[0] ?? null) : $residents;
    $response['pagination'] = [
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total / $per_page)
    ];

    echo json_encode($response);
}

function handleAccountRequests() {
    global $conn, $response;

    $page = $_GET['page'] ?? 1;
    $per_page = $_GET['per_page'] ?? 10;
    $status = $_GET['status'] ?? 'all';
    $id = $_GET['id'] ?? null;

    $offset = ($page - 1) * $per_page;

    $query = "SELECT r.*, 
              ra.id as account_id, 
              ra.account_status, 
              ra.notes, 
              ra.date_processed, 
              ra.date_requested,
              CONCAT(a.first_name, ' ', a.last_name) as processed_by
              FROM residents r
              JOIN resident_accounts ra ON r.id = ra.resident_id
              LEFT JOIN admin_users a ON ra.processed_by = a.id";

    $where = [];
    $params = [];
    $types = '';

    if ($id) {
        // ✅ Accept either account_id or resident_id
        $where[] = "(ra.id = ? OR r.id = ?)";
        $params[] = $id;
        $params[] = $id;
        $types .= 'ii';
    } else {
        if ($status !== 'all') {
            $where[] = "ra.account_status = ?";
            $params[] = $status;
            $types .= 's';
        }
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    // Count query
    $countQuery = "SELECT COUNT(*) as total,
                   SUM(CASE WHEN ra.account_status = 'Pending' THEN 1 ELSE 0 END) as pending_count
                   FROM residents r
                   JOIN resident_accounts ra ON r.id = ra.resident_id";
    if (!empty($where)) {
        $countQuery .= " WHERE " . implode(" AND ", $where);
    }

    $countStmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        bindParams($countStmt, $types, $params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result()->fetch_assoc();
    $total = $countResult['total'];
    $pending_count = $countResult['pending_count'] ?? 0;

    // Data query
    $query .= " ORDER BY ra.date_requested DESC LIMIT ? OFFSET ?";
    $params_with_limit = array_merge($params, [$per_page, $offset]);
    $types_with_limit = $types . 'ii';

    $stmt = $conn->prepare($query);
    bindParams($stmt, $types_with_limit, $params_with_limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        // Compute age
        if (!empty($row['birthdate'])) {
            $dob = new DateTime($row['birthdate']);
            $today = new DateTime();
            $row['age'] = $dob->diff($today)->y;
        } else {
            $row['age'] = null;
        }
        $requests[] = $row;
    }

    if (empty($requests)) {
        $response['success'] = false;
        $response['message'] = $id 
            ? "No account request found for ID $id"
            : "No account requests found";
        $response['data'] = [];
    } else {
        $response['success'] = true;
        $response['data'] = $requests;
    }

    $response['pending_count'] = $pending_count;
    $response['pagination'] = [
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total / $per_page)
    ];

    echo json_encode($response);
}


function handleAddResident() {
    global $conn, $response;
    
    header('Content-Type: application/json');
    
    try {
        // Get input data
        $data = $_POST;

        // DEBUG: Log received data
        error_log('Received POST data: ' . print_r($data, true));

        // Validate required fields
        $required = [
            'firstName' => 'First name',
            'lastName' => 'Last name',
            'sex' => 'Sex',
            'contactNumber' => 'Contact number',
            'houseNumber' => 'House number',
            'purok' => 'Purok',
            'birthdate' => 'Birthdate'
        ];
        
        $missingFields = [];
        foreach ($required as $field => $name) {
            if (empty($data[$field])) {
                $missingFields[] = $name;
            }
        }
        
        if (!empty($missingFields)) {
            throw new Exception('Missing required fields: ' . implode(', ', $missingFields));
        }
        
        // Process birthdate
        $birthdateInput = trim($data['birthdate']);
        
        if (preg_match('/^\d{4}$/', $birthdateInput)) {
            $birthdate = $birthdateInput . '-01-01';
        }
        elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdateInput)) {
            // Validate the date is real
            $dateParts = explode('-', $birthdateInput);
            if (!checkdate($dateParts[1], $dateParts[2], $dateParts[0])) {
                throw new Exception('Invalid birthdate. Please enter a valid date.');
            }
            $birthdate = $birthdateInput;
        }
        else {
            $timestamp = strtotime($birthdateInput);
            if ($timestamp === false) {
                throw new Exception('Invalid birthdate format. Please use YYYY-MM-DD or year only.');
            }
            $birthdate = date('Y-m-d', $timestamp);
        }

        // Calculate age in PHP (backup calculation)
        $age = 0;
        if (isset($data['age']) && is_numeric($data['age'])) {
            $age = (int)$data['age'];
            error_log('Using provided age: ' . $age);
        } else {
            // Calculate age from birthdate
            $birthdateObj = new DateTime($birthdate);
            $today = new DateTime();
            $age = $today->diff($birthdateObj)->y;
            error_log('Calculated age from birthdate: ' . $age);
        }

        // Ensure age is not negative
        $age = max(0, $age);

        // Generate address
        $address = "House {$data['houseNumber']}, Purok {$data['purok']}, Balas, Mexico, Pampanga, Philippines";

        // Handle optional fields
        $middleName = !empty($data['middleName']) ? $data['middleName'] : null;
        $suffix = !empty($data['suffix']) ? $data['suffix'] : null;
        $email = !empty($data['email']) ? $data['email'] : null;
        $createAccount = isset($data['createAccount']) && $data['createAccount'] === 'true';

        // Get current user ID for processed_by field
        $processedBy = getUserId();

        // DEBUG: Log final values
        error_log("Final values - birthdate: $birthdate, age: $age");

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert resident - FIXED: Proper parameter binding with correct types
            $stmt = $conn->prepare("INSERT INTO residents 
                (first_name, last_name, middle_name, suffix, sex, birthdate, age, contact_number, email, house_number, purok, address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception('Database error (prepare residents): ' . $conn->error);
            }

            // FIXED: Correct parameter binding - age should be 'i' for integer
            $stmt->bind_param("ssssssisssss", 
                $data['firstName'],      // s - string
                $data['lastName'],       // s - string  
                $middleName,            // s - string (can be null)
                $suffix,                // s - string (can be null)
                $data['sex'],           // s - string
                $birthdate,             // s - string (date)
                $age,                   // i - integer
                $data['contactNumber'], // s - string
                $email,                 // s - string (can be null)
                $data['houseNumber'],   // s - string
                $data['purok'],         // s - string
                $address                // s - string
            );

            if (!$stmt->execute()) {
                throw new Exception('Failed to add resident: ' . $stmt->error);
            }

            $residentId = $stmt->insert_id;
            error_log("Resident inserted with ID: $residentId");

            // If account creation is requested
            if ($createAccount) {
                if (empty($data['password'])) {
                    throw new Exception('Password is required when creating an account');
                }

                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $accountStatus = 'Approved';
                $dateRequested = date('Y-m-d H:i:s');
                
                // Include processed_by field when creating account
                $stmt = $conn->prepare("INSERT INTO resident_accounts 
                    (resident_id, email, password, account_status, date_requested, processed_by, date_processed)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())");
                
                if (!$stmt) {
                    throw new Exception('Database error (prepare accounts): ' . $conn->error);
                }

                $stmt->bind_param("issssi", 
                    $residentId,
                    $email,
                    $hashedPassword,
                    $accountStatus,
                    $dateRequested,
                    $processedBy
                );

                if (!$stmt->execute()) {
                    throw new Exception('Failed to create resident account: ' . $stmt->error);
                }
            }

            // Commit transaction
            $conn->commit();

            $response = [
                'success' => true,
                'message' => 'Resident added successfully.' . ($createAccount ? ' Account created.' : ''),
                'resident_id' => $residentId,
                'debug_info' => [
                    'birthdate' => $birthdate,
                    'age' => $age
                ]
            ];

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }

    } catch (Exception $e) {
        error_log('Add Resident Error: ' . $e->getMessage());
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
    
    echo json_encode($response);
    exit();
}
function handleEditResident() {
    global $conn, $response;

    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (empty($data['id'])) {
        throw new Exception("Resident ID is required");
    }

    $required = ['firstName', 'lastName', 'sex', 'contactNumber', 'houseNumber', 'purok', 'birthdate'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Calculate age
    $birthdate = new DateTime($data['birthdate']);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;

    // Generate address
    $address = "House {$data['houseNumber']}, Purok {$data['purok']}, Balas, Mexico, Pampanga, Philippines";

    // ✅ Store optional fields in variables so they can be passed by reference
    $middleName    = $data['middleName'] ?? '';
    $suffix        = $data['suffix'] ?? '';
    $email         = $data['email'] ?? '';

    // Update resident
    $stmt = $conn->prepare("UPDATE residents SET 
        first_name = ?, last_name = ?, middle_name = ?, suffix = ?, sex = ?, 
        birthdate = ?, age = ?, contact_number = ?, email = ?, 
        house_number = ?, purok = ?, address = ?
        WHERE id = ?");

    $stmt->bind_param(
        "ssssssisssssi",
        $data['firstName'],
        $data['lastName'],
        $middleName,
        $suffix,
        $data['sex'],
        $data['birthdate'],
        $age,
        $data['contactNumber'],
        $email,
        $data['houseNumber'],
        $data['purok'],
        $address,
        $data['id']
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to update resident: " . $stmt->error);
    }

    $response['success'] = true;
    $response['message'] = 'Resident updated successfully';
    echo json_encode($response);
}

function handleDeleteResident() {
    global $conn, $response;

    // Accept JSON or form-data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST; // fallback if form-urlencoded
    }

    if (empty($data['id'])) {
        throw new Exception("Resident ID is required");
    }

    $stmt = $conn->prepare("DELETE FROM residents WHERE id = ?");
    $stmt->bind_param("i", $data['id']);

    if (!$stmt->execute()) {
        throw new Exception("Failed to delete resident: " . $stmt->error);
    }

    $response['success'] = true;
    $response['message'] = 'Resident deleted successfully';
    echo json_encode($response);
}


function handleProcessRequest() {
    global $conn, $response;
    
    $id = intval($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $note = $_POST['note'] ?? '';

    if (!$id || !$action) {
        throw new Exception("Request ID and action are required");
    }
    
    $user_id = intval(getUserId());
    $status = $action === 'approve' ? 'Approved' : 'Disapproved';

    error_log("Processing request id=$id action=$action user_id=$user_id note=$note");

    $stmt = $conn->prepare("UPDATE resident_accounts SET 
        account_status = ?, processed_by = ?, date_processed = NOW(), notes = ?
        WHERE id = ?");

    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sisi", $status, $user_id, $note, $id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to process request: " . $stmt->error);
    }

    $response['success'] = true;
    $response['message'] = "Request {$action}d successfully";
    echo json_encode($response);
}


function handleExportResidents() {
    global $conn;
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="residents_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, [
        'ID', 'First Name', 'Last Name', 'Middle Name', 'Suffix', 'Sex', 'Birthdate', 'Age',
        'Contact Number', 'Email', 'House Number', 'Purok', 'Address', 'Verification Status',
        'Resident Status', 'Date Created', 'Date Updated'
    ]);
    
    // Data rows
    $query = "SELECT * FROM residents ORDER BY last_name, first_name";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['middle_name'],
            $row['suffix'],
            $row['sex'],
            $row['birthdate'],
            $row['age'],
            $row['contact_number'],
            $row['email'],
            $row['house_number'],
            $row['purok'],
            $row['address'],
            $row['verification_status'],
            $row['resident_status'],
            $row['created_at'],
            $row['updated_at']
        ]);
    }
    
    fclose($output);
    exit();
}
?>