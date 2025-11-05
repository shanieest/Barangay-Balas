<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php'; 

$sql = "SELECT a.id, a.title, a.content, a.date_posted, 
               CONCAT(u.first_name, ' ', u.last_name) AS posted_by,
               GROUP_CONCAT(ai.image_path) AS image_paths
        FROM announcements a
        JOIN admin_users u ON a.posted_by = u.id
        LEFT JOIN announcement_images ai ON a.id = ai.announcement_id
        GROUP BY a.id, a.title, a.content, a.date_posted, u.first_name, u.last_name
        ORDER BY a.date_posted DESC";
$result = $conn->query($sql);

function getImagePath($imagePath) {
    if (!$imagePath) return null;
    
    $fullPath = '../../admin/' . $imagePath;
    
    // Verify file exists
    if (file_exists($fullPath)) {
        return $fullPath;
    }
    
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Barangay Balas Portal</title>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="text-primary me-2"></i>Announcements</h2>
                    <div class="text-muted">
                        <i class="bi bi-clock"></i> Stay updated with the latest news
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-newspaper me-2"></i>Latest Announcements</span>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <small class="text-muted"><?= $result->num_rows ?> announcement(s) available</small>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <div class="col-md-6 col-lg-4 mb-4" id="announcement-<?= $row['id'] ?>">
                                        <div class="card announcement-item h-100">
                                            <?php 
                                            $images = [];
                                            if ($row['image_paths']) {
                                                $imagePaths = explode(',', $row['image_paths']);
                                                foreach ($imagePaths as $path) {
                                                    $path = trim($path);
                                                    $fullPath = getImagePath($path);
                                                    if ($fullPath) {
                                                        $images[] = $fullPath;
                                                    }
                                                }
                                            }
                                            ?>
                                            
                                            <?php if (!empty($images)): ?>
                                                <div class="position-relative">
                                                    <img src="<?= htmlspecialchars($images[0]) ?>" 
                                                         class="card-img-top" 
                                                         style="height: 200px; object-fit: cover; cursor: pointer;" 
                                                         onclick="showImageModal('<?= htmlspecialchars($images[0]) ?>')"
                                                         alt="Announcement image">
                                                    <?php if (count($images) > 1): ?>
                                                        <div class="position-absolute top-0 end-0 p-2">
                                                            <span class="badge bg-dark">
                                                                <i class="bi bi-images"></i> +<?= count($images) - 1 ?>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title">
                                                    <i class="bi bi-pin-fill text-warning me-1"></i>
                                                    <?= htmlspecialchars($row['title']) ?>
                                                </h5>
                                                
                                                <p class="card-text flex-grow-1">
                                                    <?= nl2br(htmlspecialchars(mb_strimwidth($row['content'], 0, 150, '…'))) ?>
                                                </p>
                                                
                                                <div class="mt-auto">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar-event"></i>
                                                            <?= date("F j, Y", strtotime($row['date_posted'])) ?>
                                                        </small>
                                                        <small class="text-muted">
                                                            <i class="bi bi-person"></i>
                                                            <?= htmlspecialchars($row['posted_by']) ?>
                                                        </small>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <button class="btn btn-primary btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#announcementModal"
                                                                onclick="loadAnnouncement(<?= htmlspecialchars(json_encode($row)) ?>, <?= htmlspecialchars(json_encode($images)) ?>)">
                                                            <i class="bi bi-eye"></i> Read More
                                                        </button>
                                                        
                                                        <?php if (!empty($images)): ?>
                                                            <button class="btn btn-outline-secondary btn-sm" 
                                                                    onclick="showImageGallery(<?= htmlspecialchars(json_encode($images)) ?>)">
                                                                <i class="bi bi-images"></i> Images (<?= count($images) ?>)
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-megaphone-fill text-muted" style="font-size: 4rem;"></i>
                                <h4 class="text-muted mt-3">No announcements available</h4>
                                <p class="text-muted">Please check back later for updates.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcement Details Modal -->
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="announcementModalLabel">
                        <i class="bi bi-megaphone text-primary me-2"></i>
                        <span id="modalTitle"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalImages" class="mb-3"></div>
                    <div id="modalContent"></div>
                    <div id="modalMeta" class="mt-3 pt-3 border-top text-muted small"></div>
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
                        <i class="bi bi-images me-2"></i>Announcement Images
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="imageGallery" class="row g-3"></div>
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

        function loadAnnouncement(announcement, images) {
            document.getElementById('modalTitle').textContent = announcement.title;
            document.getElementById('modalContent').innerHTML = announcement.content.replace(/\n/g, '<br>');
            
            const modalImages = document.getElementById('modalImages');
            modalImages.innerHTML = '';
            
            if (images && images.length > 0) {
                const imageContainer = document.createElement('div');
                imageContainer.className = 'row g-2 mb-3';
                
                images.forEach(image => {
                    const colDiv = document.createElement('div');
                    colDiv.className = images.length === 1 ? 'col-12' : 'col-md-6';
                    colDiv.innerHTML = `
                        <img src="${image}" class="img-fluid rounded shadow-sm announcement-image-large" 
                             style="cursor: pointer; width: 100%; height: 200px; object-fit: cover;" 
                             onclick="showImageModal('${image}')" alt="Announcement Image">
                    `;
                    imageContainer.appendChild(colDiv);
                });
                
                modalImages.appendChild(imageContainer);
            }
            
            document.getElementById('modalMeta').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <i class="bi bi-calendar-event"></i> Posted: ${new Date(announcement.date_posted).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                    </div>
                    <div class="col-md-6">
                        <i class="bi bi-person"></i> By: ${announcement.posted_by}
                    </div>
                </div>
            `;
        }

        function showImageGallery(images) {
            const gallery = document.getElementById('imageGallery');
            gallery.innerHTML = '';
            
            if (images && images.length > 0) {
                images.forEach(image => {
                    const colDiv = document.createElement('div');
                    colDiv.className = 'col-md-4 col-sm-6';
                    colDiv.innerHTML = `
                        <div class="card">
                            <img src="${image}" class="card-img-top" 
                                 style="height: 250px; object-fit: cover; cursor: pointer;" 
                                 onclick="showImageModal('${image}')" alt="Announcement Image">
                        </div>
                    `;
                    gallery.appendChild(colDiv);
                });
            } else {
                gallery.innerHTML = '<div class="col-12"><p class="text-center text-muted">No images available</p></div>';
            }
            
            new bootstrap.Modal(document.getElementById('galleryModal')).show();
        }
    </script>
</body>
</html>