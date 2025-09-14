<?php
// announcements.php admin view
require_once __DIR__.'/announcements-backend.php';

$announcements = $conn->query(
    "SELECT a.*, u.first_name, u.last_name, 
        GROUP_CONCAT(ai.image_path) AS image_paths
     FROM announcements a
     LEFT JOIN admin_users u ON a.posted_by = u.id
     LEFT JOIN announcement_images ai ON a.id = ai.announcement_id
     GROUP BY a.id
     ORDER BY a.date_posted DESC"
);

if (!$announcements) {
    $error = "Database error: " . $conn->error;
}
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
<main class="container-fluid px-4">
    <h1 class="mt-4">Announcements</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Announcements</li>
    </ol>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div><i class="fas fa-bullhorn me-1"></i>Manage Announcements</div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                <i class="fas fa-plus me-1"></i>Add Announcement
            </button>
        </div>
        <div class="card-body">
            <?php if (is_object($announcements) && $announcements->num_rows > 0): ?>
                <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th><th>Title</th><th>Content</th><th>Image</th>
                            <th>Date Posted</th><th>Posted By</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $i=1; while($row=$announcements->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                            <td><?= strlen($row['content'])>100 ?
                                    htmlspecialchars(substr($row['content'],0,100)).'...' :
                                    htmlspecialchars($row['content']); ?></td>
                            <td>
                                <?php
                                if ($row['image_paths']) {
                                    $images = explode(',', $row['image_paths']);
                                    foreach ($images as $img) {
                                        $img = trim($img);
                                        if ($img) {
                                            echo '<img src="'.htmlspecialchars($img).'" width="50" height="50" class="img-thumbnail rounded me-1 mb-1" style="object-fit:cover;cursor:pointer;" onclick="showImageModal(\''.htmlspecialchars($img).'\')" data-bs-toggle="tooltip" title="Click to view full image">';
                                        }
                                    }
                                } else {
                                    echo '<span class="text-muted">No image</span>';
                                }
                                ?>
                            </td>
                            <td><small class="text-muted"><?= timeAgo($row['date_posted']) ?></small></td>
                            <td><?= htmlspecialchars($row['first_name']." ".$row['last_name']) ?></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-warning"
                                        onclick='editAnnouncement(<?= json_encode($row) ?>)'
                                        title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteAnnouncement(<?= $row['id'] ?>,'<?= htmlspecialchars($row['title']) ?>','<?= $row['date_posted'] ?>')"
                                        title="Delete"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-bullhorn fa-3x mb-3"></i>
                    <h5>No announcements yet</h5>
                    <p>Click "Add Announcement" to create your first announcement.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
</div>
</div>

<?php include 'modals/announcementsModal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/announcement.js"></script>
<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0">
      <img id="modalImage" src="" class="img-fluid rounded shadow" alt="Announcement Image">
    </div>
  </div>
</div>
<script>
function showImageModal(src) {
    document.getElementById('modalImage').src = src;
    var modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
</script>
</body>
</html>
