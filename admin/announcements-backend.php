<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/../config/emailer.php';
require_once __DIR__ . '/../email_templates/announcement.php';


date_default_timezone_set('Asia/Manila');

function timeAgo($datetime) {
    $dt = new DateTime($datetime, new DateTimeZone('Asia/Manila'));
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $diff = $now->getTimestamp() - $dt->getTimestamp();

    if ($diff < 60) return $diff . ' seconds ago';
    elseif ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    elseif ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    elseif ($diff < 604800) return floor($diff / 86400) . ' days ago';
    elseif ($diff < 2419200) return floor($diff / 604800) . ' weeks ago';
    return $dt->format('M d, Y');
}

if (isset($_POST['addAnnouncement'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $date = $_POST['date'];
    $userId = $_SESSION['admin_id'];
    $now = date('Y-m-d H:i:s');

    if (empty($title) || empty($content) || empty($date)) {
        $_SESSION['error'] = "All fields except image are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO announcements (title, content, date_posted, posted_by, created_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $title, $content, $now, $userId, $now);

        if ($stmt->execute()) {
            $announcementId = $stmt->insert_id;
            $stmt->close();

            // === Image Uploads ===
            if (!empty($_FILES['images']['name'][0])) {
                $targetDir = "uploads/announcements/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                foreach ($_FILES['images']['name'] as $key => $name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileInfo = pathinfo($name);
                        $extension = strtolower($fileInfo['extension']);
                        if (in_array($extension, $allowedTypes) && $_FILES['images']['size'][$key] <= 5000000) {
                            $filename = time() . "_" . uniqid() . "." . $extension;
                            $targetFile = $targetDir . $filename;
                            if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetFile)) {
                                $imgStmt = $conn->prepare("INSERT INTO announcement_images (announcement_id, image_path) VALUES (?, ?)");
                                $imgStmt->bind_param("is", $announcementId, $targetFile);
                                $imgStmt->execute();
                                $imgStmt->close();
                            }
                        }
                    }
                }
            }

            $res = $conn->query("
                SELECT CONCAT(r.first_name, ' ', r.last_name) AS full_name, r.email 
                FROM resident_accounts ra
                INNER JOIN residents r ON ra.resident_id = r.id
                WHERE ra.account_status = 'Approved' AND r.email IS NOT NULL
            ");
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $emailData = announcementEmail($row['full_name'], $title, $content, $date);
                    sendEmail($row['email'], $emailData['subject'], $emailData['message']); 
                    // uses your emailer.php sendEmail() function
                }
            }

            $_SESSION['success'] = "Announcement added successfully and notifications sent to approved residents.";
        } else {
            $_SESSION['error'] = "Error adding announcement: " . $conn->error;
            $stmt->close();
        }
    }

    header("Location: announcements.php");
    exit();
}

if (isset($_POST['editAnnouncement'])) {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $date = $_POST['date'];
    $now = date('Y-m-d H:i:s'); 

    if ($id <= 0 || empty($title) || empty($content) || empty($date)) {
        $_SESSION['error'] = "Invalid data provided.";
    } else {
        if (!isset($_SESSION['error'])) {
            $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, date_posted = ?, updated_at = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $title, $content, $date, $now, $id);

            if ($stmt->execute()) {
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

if (isset($_POST['deleteAnnouncement'])) {
    $id = (int)$_POST['id'];
    if ($id <= 0) {
        $_SESSION['error'] = "Invalid announcement ID.";
    } else {
        // Delete images
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

        // Delete announcement
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
?>