<?php
require_once __DIR__ . '/includes/db.php';


function getHouseholds($filters = [], $limit = 10, $offset = 0) {
    global $conn;

    $sql = "SELECT h.*, r.first_name, r.last_name
            FROM households h
            LEFT JOIN residents r ON h.head_of_family_id = r.id
            WHERE 1=1";

    $params = [];
    $types = "";

    if (!empty($filters['barangay'])) {
        $sql .= " AND h.barangay_id = ?";
        $params[] = $filters['barangay'];
        $types .= "i";
    }

    if (!empty($filters['purok'])) {
        $sql .= " AND h.purok = ?";
        $params[] = $filters['purok'];
        $types .= "s";
    }

    if (!empty($filters['house_type'])) {
        $sql .= " AND h.house_type = ?";
        $params[] = $filters['house_type'];
        $types .= "s";
    }

    if (!empty($filters['water_source'])) {
        $sql .= " AND h.water_source = ?";
        $params[] = $filters['water_source'];
        $types .= "s";
    }

    if (!empty($filters['status'])) {
        $sql .= " AND h.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (r.first_name LIKE ? OR r.last_name LIKE ? OR h.household_no LIKE ?)";
        $searchTerm = "%" . $filters['search'] . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    $sql .= " ORDER BY h.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $households = [];
    while ($row = $result->fetch_assoc()) {
        $households[] = $row;
    }

    return $households;
}


function getTotalHouseholdsCount($filters = []) {
    global $conn;

    $sql = "SELECT COUNT(DISTINCT h.id) AS total
            FROM households h
            LEFT JOIN residents r ON h.head_of_family_id = r.id
            WHERE 1=1";

    $params = [];
    $types = "";

    if (!empty($filters['barangay'])) {
        $sql .= " AND h.barangay_id = ?";
        $params[] = $filters['barangay'];
        $types .= "i";
    }

    if (!empty($filters['purok'])) {
        $sql .= " AND h.purok = ?";
        $params[] = $filters['purok'];
        $types .= "s";
    }

    if (!empty($filters['house_type'])) {
        $sql .= " AND h.house_type = ?";
        $params[] = $filters['house_type'];
        $types .= "s";
    }

    if (!empty($filters['water_source'])) {
        $sql .= " AND h.water_source = ?";
        $params[] = $filters['water_source'];
        $types .= "s";
    }

    if (!empty($filters['status'])) {
        $sql .= " AND h.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (r.first_name LIKE ? OR r.last_name LIKE ? OR h.household_no LIKE ?)";
        $searchTerm = "%" . $filters['search'] . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['total'] ?? 0;
}
