<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is authenticated
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different operations based on method and parameters
switch ($method) {
    case 'GET':
        handleGetRequest();
        break;
    case 'POST':
        handlePostRequest();
        break;
    case 'PUT':
        handlePutRequest();
        break;
    case 'DELETE':
        handleDeleteRequest();
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

// Close database connection
$conn->close();

/**
 * Handle GET requests
 */
function handleGetRequest() {
    global $conn;
    
    // Get household details
    if (isset($_GET['household_id'])) {
        $householdId = $_GET['household_id'];
        getHouseholdDetails($householdId);
    }
    // Get household members
    elseif (isset($_GET['house_number']) && isset($_GET['purok'])) {
        $houseNumber = $_GET['house_number'];
        $purok = $_GET['purok'];
        getHouseholdMembers($houseNumber, $purok);
    }
    // Get census data with filters
    else {
        getCensusData();
    }
}

/**
 * Handle POST requests (create new household)
 */
function handlePostRequest() {
    // This would handle creating new households
    // Implementation depends on your form structure
    http_response_code(501);
    echo json_encode(['success' => false, 'message' => 'Not implemented']);
}

/**
 * Handle PUT requests (update household)
 */
function handlePutRequest() {
    // This would handle updating households
    // Implementation depends on your form structure
    http_response_code(501);
    echo json_encode(['success' => false, 'message' => 'Not implemented']);
}

/**
 * Handle DELETE requests
 */
function handleDeleteRequest() {
    global $conn;
    
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing household ID']);
        return;
    }
    
    $householdId = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // First, get the house number and purok to delete all members
        $stmt = $conn->prepare("SELECT house_number, purok FROM residents WHERE id = ?");
        $stmt->bind_param("i", $householdId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Household not found");
        }
        
        $household = $result->fetch_assoc();
        $houseNumber = $household['house_number'];
        $purok = $household['purok'];
        
        // Delete all members of this household
        $stmt = $conn->prepare("DELETE FROM residents WHERE house_number = ? AND purok = ?");
        $stmt->bind_param("ss", $houseNumber, $purok);
        $stmt->execute();
        
        // Log the activity
        $activity = "Deleted household $houseNumber in Purok $purok";
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity, ip_address, user_agent) VALUES (?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $stmt->bind_param("isss", $user_id, $activity, $ip, $agent);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Household deleted successfully']);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting household: ' . $e->getMessage()]);
    }
}

/**
 * Get household details
 */
function getHouseholdDetails($householdId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM residents WHERE id = ?");
    $stmt->bind_param("i", $householdId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Household not found']);
        return;
    }
    
    $household = $result->fetch_assoc();
    echo json_encode(['success' => true, 'household' => $household]);
}

/**
 * Get household members
 */
function getHouseholdMembers($houseNumber, $purok) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM residents WHERE house_number = ? AND purok = ? ORDER BY 
                          CASE WHEN relationship_to_head = 'Head' THEN 1
                               WHEN relationship_to_head = 'Spouse' THEN 2
                               ELSE 3 END, first_name ASC");
    $stmt->bind_param("ss", $houseNumber, $purok);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'members' => $members]);
}

/**
 * Get census data with filtering and pagination
 */
function getCensusData() {
    global $conn;
    
    // Get parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    $offset = ($page - 1) * $limit;
    
    // Filter parameters
    $purok_filter = isset($_GET['purok']) ? $_GET['purok'] : '';
    $water_filter = isset($_GET['water_source']) ? $_GET['water_source'] : '';
    $toilet_filter = isset($_GET['toilet_facility']) ? $_GET['toilet_facility'] : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Build WHERE clause for filters
    $where_conditions = [];
    $params = [];
    $types = '';
    
    if (!empty($purok_filter)) {
        $where_conditions[] = "r.purok = ?";
        $params[] = $purok_filter;
        $types .= 's';
    }
    
    if (!empty($water_filter)) {
        $where_conditions[] = "r.type_of_water_source = ?";
        $params[] = $water_filter;
        $types .= 's';
    }
    
    if (!empty($toilet_filter)) {
        $where_conditions[] = "r.type_of_toilet_facility = ?";
        $params[] = $toilet_filter;
        $types .= 's';
    }
    
    if (!empty($status_filter)) {
        $where_conditions[] = "r.resident_status = ?";
        $params[] = $status_filter;
        $types .= 's';
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(r.first_name LIKE ? OR r.last_name LIKE ? OR r.house_number LIKE ? OR r.address LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'ssss';
    }
    
    $where_sql = '';
    if (!empty($where_conditions)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Get total count of households
    $count_sql = "SELECT COUNT(DISTINCT CONCAT(r.house_number, '-', r.purok)) as total 
                  FROM residents r 
                  $where_sql";
    
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_households = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
    
    // Reset params for main query
    $main_params = array_slice($params, 0, count($params));
    $main_types = $types;
    
    // Get households data with pagination
    $sql = "SELECT r.*, 
                   (SELECT COUNT(*) FROM residents r2 
                    WHERE r2.house_number = r.house_number 
                    AND r2.purok = r.purok) as member_count
            FROM residents r 
            $where_sql 
            GROUP BY r.house_number, r.purok
            ORDER BY r.purok, r.house_number 
            LIMIT ? OFFSET ?";
    
    // Add limit and offset to params
    $main_params[] = $limit;
    $main_params[] = $offset;
    $main_types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    if (!empty($main_params)) {
        $stmt->bind_param($main_types, ...$main_params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $households = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Get statistics
    $stats_sql = "SELECT 
        (SELECT COUNT(DISTINCT CONCAT(house_number, '-', purok)) FROM residents) as total_households,
        (SELECT COUNT(*) FROM residents) as total_residents,
        (SELECT COUNT(DISTINCT CONCAT(house_number, '-', purok)) FROM residents WHERE type_of_water_source IS NOT NULL AND type_of_water_source != '') as water_coverage,
        (SELECT COUNT(DISTINCT CONCAT(house_number, '-', purok)) FROM residents WHERE type_of_toilet_facility IS NOT NULL AND type_of_toilet_facility != '') as toilet_coverage";
    
    $stats_result = $conn->query($stats_sql);
    $stats = $stats_result->fetch_assoc();
    $stats_result->close();
    
    // Calculate percentages
    $water_percentage = $stats['total_households'] > 0 ? round(($stats['water_coverage'] / $stats['total_households']) * 100) : 0;
    $toilet_percentage = $stats['total_households'] > 0 ? round(($stats['toilet_coverage'] / $stats['total_households']) * 100) : 0;
    
    // Calculate pagination
    $total_pages = ceil($total_households / $limit);
    $showing_from = ($page - 1) * $limit + 1;
    $showing_to = min($page * $limit, $total_households);
    
    // Return all data
    echo json_encode([
        'success' => true,
        'households' => $households,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total_households' => $total_households,
            'total_pages' => $total_pages,
            'showing_from' => $showing_from,
            'showing_to' => $showing_to
        ],
        'stats' => [
            'total_households' => $stats['total_households'],
            'total_residents' => $stats['total_residents'],
            'water_coverage' => $stats['water_coverage'],
            'toilet_coverage' => $stats['toilet_coverage'],
            'water_percentage' => $water_percentage,
            'toilet_percentage' => $toilet_percentage
        ],
        'filters' => [
            'purok' => $purok_filter,
            'water_source' => $water_filter,
            'toilet_facility' => $toilet_filter,
            'status' => $status_filter,
            'search' => $search
        ]
    ]);
}
?>