<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php'; 

$announcementId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($announcementId <= 0) {
    header("Location: announcements.php");
    exit();
}

$sql = "SELECT a.id, a.title, a.content, a.date_posted, a.created_at,
               CONCAT(u.first_name, ' ', u.last_name) AS posted_by,
               u.position,
               GROUP_CONCAT(ai.image_path ORDER BY ai.created_at ASC) AS image_paths
        FROM announcements a
        JOIN admin_users u ON a.posted_by = u.id
        LEFT JOIN announcement_images ai ON a.id = ai.announcement_id
        WHERE a.id = ?
        GROUP BY a.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $announcementId);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();

if (!$announcement) {
    header("Location: announcements.php");
    exit();
}

$images = [];
if ($announcement['image_paths']) {
    $imagePaths = explode(',', $announcement['image_paths']);
    foreach ($imagePaths as $path) {
        $path = trim($path);
        if ($path) {
            $fullPath = '../../admin/' . $path;
            
            // Verify the file exists before adding
            if (file_exists($fullPath)) {
                $images[] = $fullPath;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($announcement['title']) ?> - Barangay Balas Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/announcements.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'?>

        <!-- Main Content -->
        <div class="main-content">
            <?php include '../../includes/navbar.php'?>

            <div class="content-area">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="../../../index.php" class="text-decoration-none">
                                <i class="bi bi-house"></i> Home
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="announcements.php" class="text-decoration-none">
                                <i class="bi bi-megaphone"></i> Announcements
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= htmlspecialchars($announcement['title']) ?>
                        </li>
                    </ol>
                </nav>

                <!-- Announcement Header -->
                <div class="announcement-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-3">
                                <i class="bi bi-pin-fill me-2"></i>
                                <?= htmlspecialchars($announcement['title']) ?>
                            </h1>
                            <div class="row">
                                <div class="col-sm-6 mb-2">
                                    <i class="bi bi-calendar-event me-2"></i>
                                    <strong>Posted:</strong> <?= date("F j, Y", strtotime($announcement['date_posted'])) ?>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <i class="bi bi-person me-2"></i>
                                    <strong>By:</strong> <?= htmlspecialchars($announcement['posted_by']) ?>
                                    <?php if ($announcement['position']): ?>
                                        <br><small class="text-light"><?= htmlspecialchars($announcement['position']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-light" onclick="window.history.back()">
                                <i class="bi bi-arrow-left me-2"></i>Back
                            </button>
                            <?php if (!empty($images)): ?>
                                <button class="btn btn-warning ms-2" onclick="showImageGallery()">
                                    <i class="bi bi-images me-2"></i>View Images (<?= count($images) ?>)
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Announcement Content -->
                <div class="announcement-content">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-4">
                                <i class="bi bi-file-text text-primary me-2"></i>Announcement Details
                            </h3>
                            <div class="announcement-text" style="font-size: 1.1rem; line-height: 1.6;">
                                <?= nl2br(htmlspecialchars($announcement['content'])) ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($images)): ?>
                            <div class="col-lg-4">
                                <h4 class="mb-3">
                                    <i class="bi bi-images text-primary me-2"></i>Images
                                </h4>
                                <div class="image-gallery">
                                    <?php foreach (array_slice($images, 0, 3) as $index => $image): ?>
                                        <div class="gallery-image" onclick="showImageModal('<?= htmlspecialchars($image) ?>')">
                                            <img src="<?= htmlspecialchars($image) ?>" alt="Announcement Image <?= $index + 1 ?>">
                                            <div class="gallery-overlay">
                                                <i class="bi bi-zoom-in zoom-icon"></i>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($images) > 3): ?>
                                        <div class="gallery-image bg-secondary d-flex align-items-center justify-content-center" onclick="showImageGallery()" style="color: white; cursor: pointer;">
                                            <div class="text-center">
                                                <i class="bi bi-plus-circle" style="font-size: 2rem;"></i>
                                                <br>
                                                <strong>+<?= count($images) - 3 ?> more</strong>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Share Section -->
                <div class="announcement-content">
                    <h4 class="mb-3">
                        <i class="bi bi-share text-primary me-2"></i>Share this Announcement
                    </h4>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-outline-primary" onclick="copyToClipboard()">
                            <i class="bi bi-link me-2"></i>Copy Link
                        </button>
                        <button class="btn btn-outline-success" onclick="shareWhatsApp()">
                            <i class="bi bi-whatsapp me-2"></i>Share on WhatsApp
                        </button>
                        <button class="btn btn-outline-info" onclick="shareFacebook()">
                            <i class="bi bi-facebook me-2"></i>Share on Facebook
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Image Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalLabel">
                        <i class="bi bi-images me-2"></i>All Images - <?= htmlspecialchars($announcement['title']) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="card">
                                    <img src="<?= htmlspecialchars($image) ?>" 
                                         class="card-img-top" 
                                         style="height: 250px; object-fit: cover; cursor: pointer;" 
                                         onclick="showImageModal('<?= htmlspecialchars($image) ?>')"
                                         alt="Image <?= $index + 1 ?>">
                                    <div class="card-body p-2 text-center">
                                        <small class="text-muted">Image <?= $index + 1 ?> of <?= count($images) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if (document.getElementById('sidebarToggle')) {
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                document.querySelector('.wrapper').classList.toggle('sidebar-collapsed');
            });
        }

        function showImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        function showImageGallery() {
            new bootstrap.Modal(document.getElementById('galleryModal')).show();
        }

        function copyToClipboard() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check me-2"></i>Copied!';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-success');
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 2000);
            });
        }

        function shareWhatsApp() {
            const title = <?= json_encode($announcement['title']) ?>;
            const url = window.location.href;
            const text = `Check out this announcement: ${title} - ${url}`;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }

        function shareFacebook() {
            const url = window.location.href;
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
        }
    </script>
</body>
</html>