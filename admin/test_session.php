<?php
// test_session.php - Use this to debug your session
session_start();

echo "<h2>Session Debug Information</h2>";
echo "<pre>";

echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n\n";

echo "All Session Variables:\n";
print_r($_SESSION);

echo "\nSpecific Session Checks:\n";
echo "isset(\$_SESSION['user_id']): " . (isset($_SESSION['user_id']) ? 'YES' : 'NO') . "\n";
echo "empty(\$_SESSION['user_id']): " . (empty($_SESSION['user_id']) ? 'YES' : 'NO') . "\n";
echo "\$_SESSION['user_id'] value: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";

if (isset($_SESSION['user_id'])) {
    // Test database connection
    require_once 'includes/db.php';
    
    $admin_check = $conn->prepare("SELECT id, username, first_name, last_name FROM admin_users WHERE id = ?");
    $admin_check->bind_param('i', $_SESSION['user_id']);
    $admin_check->execute();
    $admin_result = $admin_check->get_result();
    
    echo "\nDatabase Check:\n";
    if ($admin_result->num_rows > 0) {
        $admin_data = $admin_result->fetch_assoc();
        echo "Admin found in database: YES\n";
        echo "Admin ID: " . $admin_data['id'] . "\n";
        echo "Admin Username: " . $admin_data['username'] . "\n";
        echo "Admin Name: " . $admin_data['first_name'] . " " . $admin_data['last_name'] . "\n";
    } else {
        echo "Admin found in database: NO\n";
        echo "No admin user found with ID: " . $_SESSION['user_id'] . "\n";
    }
    $admin_check->close();
} else {
    echo "\nNo user_id in session - you are not logged in.\n";
}

echo "</pre>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    pre { background: #f5f5f5; padding: 15px; border-radius: 5px; }
    h2 { color: #333; }
</style>