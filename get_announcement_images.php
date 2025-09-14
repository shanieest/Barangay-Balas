<?php
// get_announcement_images.php
header('Content-Type: application/json');
require_once __DIR__ . '/config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid announcement ID']);
    exit;
}

$announcementId = (int)$_GET['id'];

try {
    $stmt = $conn->prepare("SELECT image_path FROM announcement_images WHERE announcement_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $announcementId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $imagePath = $row['image_path'];
        
        // Check if image file exists
        if (file_exists($imagePath)) {
            $images[] = $imagePath;
        } else if (file_exists('admin/' . $imagePath)) {
            $images[] = 'admin/' . $imagePath;
        }
    }
    
    echo json_encode(['images' => $images]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>