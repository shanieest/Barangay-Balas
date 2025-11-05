<?php
require_once '../backend/announcements-backend.php';

$announcements = $conn->query(
    "SELECT a.*, u.first_name, u.last_name, 
        GROUP_CONCAT(ai.image_path) AS image_paths
     FROM announcements a
     LEFT JOIN admin_users u ON a.posted_by = u.id
     LEFT JOIN announcement_images ai ON a.id = ai.announcement_id
     GROUP BY a.id
     ORDER BY a.created_at DESC"
);

if (!$announcements) {
    $error = "Database error: " . $conn->error;
}

// FIXED: Helper function to get correct image path from admin directory
function getAdminImagePath($dbPath) {
    if (empty($dbPath)) return null;
    
    // From admin/pages directory: ../uploads/announcements/file.jpg
    // Database stores: uploads/announcements/file.jpg
    return '../' . $dbPath;
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
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/announcements.css">
</head>
<body class="sb-nav-fixed">
<?php include '../includes/navbar.php'; ?>
<div id="layoutSidenav">
<?php include '../includes/sidebar.php'; ?>
<div id="layoutSidenav_content">
<main>
    <div>
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-icon">
                        <i class="fas fa-bullhorn fa-2x"></i>
                    </div>
                    <h1 class="mb-2">Announcements</h1>
                    <p class="mb-0 opacity-75">Keep the community informed with important updates and news</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-flex flex-wrap justify-content-md-end gap-2">
                        <button class="btn btn-outline-light">
                            <i class="fas fa-calendar me-1"></i> <?= date('F j, Y') ?>
                        </button>
                        <button class="btn btn-outline-light" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active"><i class="fas fa-bullhorn me-1"></i>Announcements</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list-alt me-2"></i>Manage Announcements</span>
                <button class="btn btn-light btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                    <i class="fas fa-plus me-1"></i> Add Announcement
                </button>
            </div>
            <div class="card-body">
                <?php if (is_object($announcements) && $announcements->num_rows > 0): ?>
                    <div class="row">
                        <?php $i=1; while($row=$announcements->fetch_assoc()): ?>
                            <div class="col-lg-6 col-xl-4 mb-4">
                                <div class="announcement-card">
                                    <div class="announcement-header">
                                        <div class="announcement-title">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </div>
                                        <div class="announcement-meta">
                                            <i class="fas fa-user me-1"></i>
                                            <?= htmlspecialchars($row['first_name']." ".$row['last_name']) ?>
                                            <br>
                                            <i class="fas fa-clock me-1"></i>
                                            <?= timeAgo($row['created_at']) ?>
                                            <small class="text-muted ms-1">
                                                (<?= date('M d, Y', strtotime($row['date_posted'])) ?>)
                                            </small>
                                        </div>
                                    </div>
                                    <div class="announcement-body">
                                        <div class="announcement-content">
                                            <?= strlen($row['content'])>150 ?
                                                htmlspecialchars(substr($row['content'],0,150)).'...' :
                                                htmlspecialchars($row['content']); ?>
                                        </div>
                                        
                                        <?php if ($row['image_paths']): ?>
                                            <div class="image-gallery">
                                                <?php
                                                $images = explode(',', $row['image_paths']);
                                                $displayCount = 0;
                                                foreach ($images as $img):
                                                    $img = trim($img);
                                                    if ($img):
                                                        // FIXED: Get correct path from admin directory
                                                        $fullPath = getAdminImagePath($img);
                                                        if ($fullPath && file_exists('../' . $img)):
                                                            if ($displayCount < 4):
                                                ?>
                                                    <img src="<?= htmlspecialchars($fullPath) ?>" 
                                                         class="gallery-image" 
                                                         onclick="showImageModal('<?= htmlspecialchars($fullPath) ?>')"
                                                         data-bs-toggle="tooltip" 
                                                         title="Click to view full image"
                                                         onerror="this.style.display='none'">
                                                <?php 
                                                            $displayCount++;
                                                            endif;
                                                        endif;
                                                    endif;
                                                endforeach; 
                                                if (count($images) > 4): ?>
                                                    <div class="gallery-image d-flex align-items-center justify-content-center bg-light text-muted fw-bold">
                                                        +<?= count($images) - 4 ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="action-buttons">
                                            <button class="btn btn-warning btn-action"
                                                onclick='editAnnouncement(<?= json_encode($row) ?>)'
                                                data-bs-toggle="tooltip" title="Edit Announcement">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-action"
                                                onclick="deleteAnnouncement(<?= $row['id'] ?>,'<?= htmlspecialchars($row['title']) ?>','<?= $row['date_posted'] ?>')"
                                                data-bs-toggle="tooltip" title="Delete Announcement">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-bullhorn"></i>
                        <h4 class="mt-3 mb-2">No Announcements Yet</h4>
                        <p class="text-muted mb-0">Get started by creating your first announcement to keep the community informed.</p>
                        <button class="btn btn-primary mt-3 rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                            <i class="fas fa-plus me-1"></i> Create Announcement
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
</div>
</div>

<?php include '../modals/announcementsModal.php'; ?>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <img id="modalImage" src="" class="img-fluid w-100 rounded shadow" alt="Announcement Image">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/announcement.js"></script>
<script src="../assets/js/script.js"></script>
<script>
    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    
    // Image modal function
    function showImageModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }
</script>
</body>
</html>