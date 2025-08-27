<?php
require_once __DIR__ . '/includes/auth.php';
requireAuth();
require_once __DIR__ . '/includes/db.php';

if (isset($_POST['addAnnouncement'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $date_posted = mysqli_real_escape_string($conn, $_POST['date']);
    $posted_by = $_SESSION['user_id']; // from auth session

    $image_path = NULL;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/announcements/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    $sql = "INSERT INTO announcements (title, content, image_path, date_posted, posted_by) 
            VALUES ('$title', '$content', " . ($image_path ? "'$image_path'" : "NULL") . ", '$date_posted', '$posted_by')";
    mysqli_query($conn, $sql);

    header("Location: announcements.php");
    exit();
}

if (isset($_POST['editAnnouncement'])) {
    $id = intval($_POST['id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $date_posted = mysqli_real_escape_string($conn, $_POST['date']);
    $current_image = $_POST['current_image'];

    $image_path = $current_image;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/announcements/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;

            // delete old image if exists
            if (!empty($current_image) && file_exists($current_image)) {
                unlink($current_image);
            }
        }
    }

    $sql = "UPDATE announcements 
            SET title='$title', content='$content', image_path=" . ($image_path ? "'$image_path'" : "NULL") . ", date_posted='$date_posted' 
            WHERE id=$id";
    mysqli_query($conn, $sql);

    header("Location: announcements.php");
    exit();
}

if (isset($_POST['deleteAnnouncement'])) {
    $id = intval($_POST['id']);

    $res = mysqli_query($conn, "SELECT image_path FROM announcements WHERE id=$id");
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['image_path']) && file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
    }

    $sql = "DELETE FROM announcements WHERE id=$id";
    mysqli_query($conn, $sql);

    header("Location: announcements.php");
    exit();
}

$sql = "SELECT a.*, u.first_name, u.last_name 
        FROM announcements a
        JOIN admin_users u ON a.posted_by = u.id
        ORDER BY a.date_posted DESC";
$announcements = mysqli_query($conn, $sql);
