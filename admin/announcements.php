<?php 
require_once __DIR__ . '/includes/auth.php';
requireAuth();
require_once __DIR__ . '/includes/db.php';

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } elseif ($diff < 2419200) {
        return floor($diff / 604800) . ' weeks ago';
    } else {
        return date('M d, Y', $time);
    }
}


// Handle Add Announcement
if (isset($_POST['addAnnouncement'])) {
    $title   = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $date    = $_POST['date'];
    $userId  = $_SESSION['user_id'];

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/announcements/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    $sql = "INSERT INTO announcements (title, content, image_path, date_posted, posted_by) 
            VALUES ('$title', '$content', " . ($imagePath ? "'$imagePath'" : "NULL") . ", '$date', '$userId')";
    mysqli_query($conn, $sql);
    header("Location: announcements.php");
    exit();
}

// Handle Edit Announcement
if (isset($_POST['editAnnouncement'])) {
    $id      = (int) $_POST['id'];
    $title   = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $date    = $_POST['date'];
    $currentImage = $_POST['current_image'];

    $imagePath = $currentImage;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/announcements/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            if ($currentImage && file_exists($currentImage)) {
                unlink($currentImage);
            }
            $imagePath = $targetFile;
        }
    }

    $sql = "UPDATE announcements 
            SET title='$title', content='$content', image_path=" . ($imagePath ? "'$imagePath'" : "NULL") . ", date_posted='$date' 
            WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: announcements.php");
    exit();
}

// Handle Delete Announcement
if (isset($_POST['deleteAnnouncement'])) {
    $id = (int) $_POST['id'];

    // delete image first
    $result = mysqli_query($conn, "SELECT image_path FROM announcements WHERE id=$id");
    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['image_path'] && file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
    }

    mysqli_query($conn, "DELETE FROM announcements WHERE id=$id");
    header("Location: announcements.php");
    exit();
}

// Fetch Announcements with user info
$sql = "SELECT a.*, u.first_name, u.last_name 
        FROM announcements a 
        JOIN admin_users u ON a.posted_by = u.id
        ORDER BY a.date_posted DESC";

$announcements = mysqli_query($conn, $sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | Barangay Balas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="sb-nav-fixed">
    <?php include 'includes/navbar.php'; ?>
    
    <div id="layoutSidenav">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Announcements</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Announcements</li>
                    </ol>
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-table me-1"></i>
                                    Manage Announcements
                                </div>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                                    <i class="fas fa-plus me-1"></i> Add Announcement
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="announcementsTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>Image</th>
                                        <th>Date Posted</th>
                                        <th>Posted By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=1; while($row = mysqli_fetch_assoc($announcements)): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($row['title']) ?></td>
                                        <td><?= htmlspecialchars($row['content']) ?></td>
                                        <td>
                                            <?php if($row['image_path']): ?>
                                                <img src="<?= $row['image_path'] ?>" width="50" class="img-thumbnail">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= timeAgo($row['date_posted']) ?></td>
                                        <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editAnnouncementModal<?= $row['id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteAnnouncementModal<?= $row['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal (Dynamic) -->
                                    <div class="modal fade" id="editAnnouncementModal<?= $row['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-header bg-warning text-white">
                                                        <h5 class="modal-title">Edit Announcement</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="current_image" value="<?= $row['image_path'] ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Title</label>
                                                            <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($row['title']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Content</label>
                                                            <textarea class="form-control" name="content" rows="5" required><?= htmlspecialchars($row['content']) ?></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Image</label>
                                                            <input class="form-control" type="file" name="image">
                                                            <?php if($row['image_path']): ?>
                                                                <small class="text-muted">Current: <?= basename($row['image_path']) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Date</label>
                                                            <input type="date" class="form-control" name="date" value="<?= $row['date_posted'] ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="editAnnouncement" class="btn btn-warning text-white">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteAnnouncementModal<?= $row['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Delete Announcement</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <p>Are you sure you want to delete this announcement?</p>
                                                        <p><strong>Title:</strong> <?= htmlspecialchars($row['title']) ?></p>
                                                        <p><strong>Date:</strong> <?= $row['date_posted'] ?></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="deleteAnnouncement" class="btn btn-danger">Delete</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
             
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addAnnouncementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Add New Announcement</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input class="form-control" type="file" name="image">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="addAnnouncement" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/script.js"></script>

</body>
</html>
