<?php
//announcements.php client view - FIXED VERSION
require_once '../../auth/auth.php';
require_once '../../config/db.php'; 

// Fixed SQL query to properly join with announcement_images table
$sql = "SELECT a.id, a.title, a.content, a.date_posted, 
               CONCAT(u.first_name, ' ', u.last_name) AS posted_by,
               GROUP_CONCAT(ai.image_path) AS image_paths
        FROM announcements a
        JOIN admin_users u ON a.posted_by = u.id
        LEFT JOIN announcement_images ai ON a.id = ai.announcement_id
        GROUP BY a.id, a.title, a.content, a.date_posted, u.first_name, u.last_name
        ORDER BY a.date_posted DESC";
$result = $conn->query($sql);

// Helper function to check if image exists
function getImagePath($imagePath) {
    if (!$imagePath) return null;
    
    // Check if file exists
    if (file_exists('../../admin/' . $imagePath)) {
        return '../../admin/' . $imagePath;
    }
    
    // If the path is already from admin folder
    if (file_exists($imagePath)) {
        return $imagePath;
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
    <style>
        :root {
            --primary-blue:  #0033cc;
            --secondary-blue: #3a7cb9;
            --accent-red: #e63946;
            --accent-yellow: #ffbe0b;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-blue), var(--secondary-blue));
            color: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            padding: 10px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }
        
        .sidebar-menu li:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu li.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid var(--accent-yellow);
        }
        
        .sidebar-menu li a {
            color: white;
            text-decoration: none;
            display: block;
        }
        
        .sidebar-menu li i {
            margin-right: 10px;
            color: var(--accent-yellow);
        }
        
        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
        }
        
        .top-navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 20px;
        }
        
        .content-area {
            padding: 20px;
            min-height: calc(100vh - 70px);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .announcement-item {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .announcement-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        
        .announcement-images {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .announcement-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s ease;
            border: 2px solid #dee2e6;
        }
        
        .announcement-image:hover {
            transform: scale(1.1);
            border-color: var(--primary-blue);
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-warning {
            background-color: var(--accent-yellow);
            border-color: var(--accent-yellow);
            color: #333;
        }
        
        .btn-danger {
            background-color: var(--accent-red);
            border-color: var(--accent-red);
        }
        
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.8);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar .sidebar-text {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .sidebar-header h3 {
                display: none;
            }
            
            .sidebar-menu li {
                text-align: center;
            }
            
            .sidebar-menu li i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .announcement-image {
                width: 60px;
                height: 60px;
            }
        }
    </style>
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
                    <h2><i class="bi bi-megaphone text-primary me-2"></i>Announcements</h2>
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
        // Toggle sidebar
        if (document.getElementById('sidebarToggle')) {
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                document.querySelector('.wrapper').classList.toggle('sidebar-collapsed');
            });
        }

        // Show single image in modal
        function showImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }

        // Load announcement details into modal
        function loadAnnouncement(announcement, images) {
            document.getElementById('modalTitle').textContent = announcement.title;
            document.getElementById('modalContent').innerHTML = announcement.content.replace(/\n/g, '<br>');
            
            // Load images
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
            
            // Load metadata
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

        // Show image gallery
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