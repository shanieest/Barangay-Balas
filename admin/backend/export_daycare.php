<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

requireAuth();

$schoolYear = $_GET['school_year'] ?? date('Y') . '-' . (date('Y') + 1);

// Fetch data
$sql = "SELECT 
    child_first_name, child_middle_name, child_last_name, sex, 
    address, birthday, guardian, relationship_to_child,
    first_language, secondary_language, guardian_name, email,
    mother_name, mother_contact, father_name, father_contact,
    emergency_name, emergency_relationship, emergency_contact,
    confirmed, confirmed_at, created_at
    FROM daycare_enrollments 
    WHERE school_year = ?
    ORDER BY confirmed ASC, created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $schoolYear);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Daycare_Enrollments_SY_' . str_replace('-', '_', $schoolYear) . '_' . date('Ymd') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Output Excel content
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head><meta charset="utf-8"><style>table{border-collapse:collapse;}th,td{border:1px solid black;padding:8px;}</style></head>';
echo '<body>';
echo '<h2>Barangay Balas Daycare Center</h2>';
echo '<h3>Enrollment Records - School Year ' . htmlspecialchars($schoolYear) . '</h3>';
echo '<p>Generated on: ' . date('F d, Y h:i A') . '</p>';
echo '<table border="1">';

// Headers
echo '<thead>';
echo '<tr style="background-color: #4472C4; color: white; font-weight: bold;">';
echo '<th>No.</th>';
echo '<th>Child First Name</th>';
echo '<th>Child Middle Name</th>';
echo '<th>Child Last Name</th>';
echo '<th>Sex</th>';
echo '<th>Birthday</th>';
echo '<th>Age</th>';
echo '<th>Address</th>';
echo '<th>Guardian Name</th>';
echo '<th>Relationship</th>';
echo '<th>First Language</th>';
echo '<th>Secondary Language</th>';
echo '<th>Email</th>';
echo '<th>Mother Name</th>';
echo '<th>Mother Contact</th>';
echo '<th>Father Name</th>';
echo '<th>Father Contact</th>';
echo '<th>Emergency Contact Name</th>';
echo '<th>Emergency Relationship</th>';
echo '<th>Emergency Contact Number</th>';
echo '<th>Status</th>';
echo '<th>Confirmed At</th>';
echo '<th>Enrolled At</th>';
echo '</tr>';
echo '</thead>';

// Data rows
echo '<tbody>';
$count = 1;
while ($row = $result->fetch_assoc()) {
    // Calculate age
    $birthday = new DateTime($row['birthday']);
    $today = new DateTime();
    $age = $today->diff($birthday)->y;
    
    $status = $row['confirmed'] ? 'Confirmed' : 'Pending';
    $confirmedAt = $row['confirmed_at'] ? date('M d, Y', strtotime($row['confirmed_at'])) : '-';
    $enrolledAt = date('M d, Y', strtotime($row['created_at']));
    
    echo '<tr>';
    echo '<td>' . $count . '</td>';
    echo '<td>' . htmlspecialchars($row['child_first_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['child_middle_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['child_last_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['sex']) . '</td>';
    echo '<td>' . htmlspecialchars($row['birthday']) . '</td>';
    echo '<td>' . $age . '</td>';
    echo '<td>' . htmlspecialchars($row['address']) . '</td>';
    echo '<td>' . htmlspecialchars($row['guardian_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['relationship_to_child']) . '</td>';
    echo '<td>' . htmlspecialchars($row['first_language']) . '</td>';
    echo '<td>' . htmlspecialchars($row['secondary_language']) . '</td>';
    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
    echo '<td>' . htmlspecialchars($row['mother_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['mother_contact']) . '</td>';
    echo '<td>' . htmlspecialchars($row['father_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['father_contact']) . '</td>';
    echo '<td>' . htmlspecialchars($row['emergency_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['emergency_relationship']) . '</td>';
    echo '<td>' . htmlspecialchars($row['emergency_contact']) . '</td>';
    echo '<td style="background-color: ' . ($row['confirmed'] ? '#d4edda' : '#fff3cd') . ';">' . $status . '</td>';
    echo '<td>' . $confirmedAt . '</td>';
    echo '<td>' . $enrolledAt . '</td>';
    echo '</tr>';
    $count++;
}
echo '</tbody>';
echo '</table>';

// Summary
$totalStmt = $conn->prepare("SELECT COUNT(*) as total, SUM(confirmed) as confirmed FROM daycare_enrollments WHERE school_year = ?");
$totalStmt->bind_param("s", $schoolYear);
$totalStmt->execute();
$summary = $totalStmt->get_result()->fetch_assoc();

echo '<br><br>';
echo '<h3>Summary</h3>';
echo '<table border="1" style="width: 300px;">';
echo '<tr><td><strong>Total Enrollments:</strong></td><td>' . $summary['total'] . '</td></tr>';
echo '<tr><td><strong>Confirmed:</strong></td><td>' . $summary['confirmed'] . '</td></tr>';
echo '<tr><td><strong>Pending:</strong></td><td>' . ($summary['total'] - $summary['confirmed']) . '</td></tr>';
echo '</table>';

echo '</body></html>';

$conn->close();
?>