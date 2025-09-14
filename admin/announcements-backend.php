<?php
// announcements-backend.php admin backend
require_once __DIR__ . '/includes/auth.php';
requireAuth();
require_once __DIR__ . '/includes/db.php';

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return $diff . ' seconds ago';
    elseif ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    elseif ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    elseif ($diff < 604800) return floor($diff / 86400) . ' days ago';
    elseif ($diff < 2419200) return floor($diff / 604800) . ' weeks ago';
    return date('M d, Y', $time);
}

/* ---------- ADD ANNOUNCEMENT ---------- */
if (isset($_POST['addAnnouncement'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $date = $_POST['date'];
    $userId = $_SESSION['user_id'];

    if (empty($title) || empty($content) || empty($date)) {
        $_SESSION['error'] = "All fields except image are required.";
    } else {
        // Insert announcement first (without images)
        if (!isset($_SESSION['error'])) {
            $stmt = $conn->prepare("INSERT INTO announcements (title, content, date_posted, posted_by, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssi", $title, $content, $date, $userId);

            if ($stmt->execute()) {
                $announcementId = $stmt->insert_id;
                $stmt->close();

                // Handle multiple image uploads
                if (!empty($_FILES['images']['name'][0])) {
                    $targetDir = "uploads/announcements/";
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    foreach ($_FILES['images']['name'] as $key => $name) {
                        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                            $fileInfo = pathinfo($name);
                            $extension = strtolower($fileInfo['extension']);
                            if (in_array($extension, $allowedTypes) && $_FILES['images']['size'][$key] <= 5000000) {
                                $filename = time() . "_" . uniqid() . "." . $extension;
                                $targetFile = $targetDir . $filename;
                                if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetFile)) {
                                    // Insert image path into announcement_images table
                                    $imgStmt = $conn->prepare("INSERT INTO announcement_images (announcement_id, image_path) VALUES (?, ?)");
                                    $imgStmt->bind_param("is", $announcementId, $targetFile);
                                    $imgStmt->execute();
                                    $imgStmt->close();
                                }
                            }
                        }
                    }
                }

                $_SESSION['success'] = "Announcement added successfully.";
            } else {
                $_SESSION['error'] = "Error adding announcement: " . $conn->error;
                $stmt->close();
            }
        }
    }

    header("Location: announcements.php");
    exit();
}

/* ---------- EDIT ANNOUNCEMENT ---------- */
if (isset($_POST['editAnnouncement'])) {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $date = $_POST['date'];

    if ($id <= 0 || empty($title) || empty($content) || empty($date)) {
        $_SESSION['error'] = "Invalid data provided.";
    } else {
        // Update announcement info
        if (!isset($_SESSION['error'])) {
            $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, date_posted = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("sssi", $title, $content, $date, $id);

            if ($stmt->execute()) {
                // Handle new image uploads
                if (!empty($_FILES['images']['name'][0])) {
                    $targetDir = "uploads/announcements/";
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    foreach ($_FILES['images']['name'] as $key => $name) {
                        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                            $fileInfo = pathinfo($name);
                            $extension = strtolower($fileInfo['extension']);
                            if (in_array($extension, $allowedTypes) && $_FILES['images']['size'][$key] <= 5000000) {
                                $filename = time() . "_" . uniqid() . "." . $extension;
                                $targetFile = $targetDir . $filename;
                                if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetFile)) {
                                    // Insert image path into announcement_images table
                                    $imgStmt = $conn->prepare("INSERT INTO announcement_images (announcement_id, image_path) VALUES (?, ?)");
                                    $imgStmt->bind_param("is", $id, $targetFile);
                                    $imgStmt->execute();
                                    $imgStmt->close();
                                }
                            }
                        }
                    }
                }
                $_SESSION['success'] = "Announcement updated successfully.";
            } else {
                $_SESSION['error'] = "Error updating announcement: " . $conn->error;
            }
            $stmt->close();
        }
    }

    header("Location: announcements.php");
    exit();
}

/* ---------- DELETE ANNOUNCEMENT ---------- */
if (isset($_POST['deleteAnnouncement'])) {
    $id = (int)$_POST['id'];
    if ($id <= 0) {
        $_SESSION['error'] = "Invalid announcement ID.";
    } else {
        // Delete images from server
        $imgStmt = $conn->prepare("SELECT image_path FROM announcement_images WHERE announcement_id = ?");
        $imgStmt->bind_param("i", $id);
        $imgStmt->execute();
        $imgStmt->bind_result($imagePath);
        while ($imgStmt->fetch()) {
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $imgStmt->close();

        // Delete announcement (images will be deleted due to ON DELETE CASCADE)
        $delStmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        $delStmt->bind_param("i", $id);
        if ($delStmt->execute()) {
            $_SESSION['success'] = "Announcement deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting announcement: " . $conn->error;
        }
        $delStmt->close();
    }
    header("Location: announcements.php");
    exit();
}