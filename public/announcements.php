<?php
require_once __DIR__ . '/../config/db.php';

function getAnnouncements($conn) {
    try {
        $query = "
            SELECT 
                a.id,
                a.title,
                a.content,
                a.date_posted,
                a.created_at,
                a.updated_at,
                CONCAT(au.first_name, ' ', au.last_name) as posted_by_name,
                au.position as posted_by_position
            FROM announcements a
            LEFT JOIN admin_users au ON a.posted_by = au.id
            ORDER BY a.date_posted DESC, a.created_at DESC
        ";
        
        $result = $conn->query($query);
        $announcements = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $images = getAnnouncementImages($conn, $row['id']);
                $row['images'] = $images;
                $announcements[] = $row;
            }
        }
        
        return $announcements;
        
    } catch (Exception $e) {
        error_log("Error fetching announcements: " . $e->getMessage());
        return [];
    }
}

function getAnnouncementImages($conn, $announcementId) {
    try {
        $stmt = $conn->prepare("SELECT image_path FROM announcement_images WHERE announcement_id = ? ORDER BY created_at ASC");
        $stmt->bind_param("i", $announcementId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $imagePath = $row['image_path'];
            
            if (file_exists($imagePath)) {
                $images[] = $imagePath;
            } else if (file_exists('../admin/' . $imagePath)) {
                $images[] = '../admin/' . $imagePath;
            } else if (file_exists('admin/' . $imagePath)) {
                $images[] = 'admin/' . $imagePath;
            }
        }
        
        return $images;
        
    } catch (Exception $e) {
        error_log("Error fetching announcement images: " . $e->getMessage());
        return [];
    }
}

function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

$announcements = getAnnouncements($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Balas - News and Announcements</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/announcements.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<!-- Hero Section -->
<div class="hero-section">
  <h2 class="fw-bold">BARANGAY BALAS</h2>
  <h1 class="display-5 fw-bold text-warning">NEWS & ANNOUNCEMENTS</h1>
  <p class="lead mt-3">Stay updated with the latest news and announcements from your barangay</p>
</div>

<!-- Announcements Section -->
<div class="container form-section">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-center mb-4" data-aos="fade-up">Latest Announcements</h2>
        </div>
    </div>
    
    <?php if (empty($announcements)): ?>
        <div class="row">
            <div class="col-12">
                <div class="no-announcements" data-aos="fade-up">
                    <i class="fas fa-bullhorn fa-3x mb-3"></i>
                    <h3>No Announcements Yet</h3>
                    <p>Check back later for the latest news and announcements from Barangay Balas.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php 
            $delay = 0;
            foreach ($announcements as $announcement): 
                $delay += 200;
            ?>
                <div class="col-lg-4 col-md-6 col-sm-12" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="<?php echo $delay; ?>">
                    <div class="card announcement-card">
                        <?php if (!empty($announcement['images'])): ?>
                            <?php if (count($announcement['images']) == 1): ?>
                                <!-- Single image -->
                                <img src="<?php echo htmlspecialchars($announcement['images'][0]); ?>" 
                                     class="announcement-image" 
                                     alt="<?php echo htmlspecialchars($announcement['title']); ?>"
                                     onerror="this.parentElement.innerHTML='<div class=\'announcement-image default-image\'><i class=\'fas fa-image\'></i></div>';">
                            <?php else: ?>
                                <!-- Multiple images - Carousel -->
                                <div id="carousel<?php echo $announcement['id']; ?>" class="carousel slide image-carousel" data-bs-ride="carousel">
                                    <div class="carousel-indicators">
                                        <?php for ($i = 0; $i < count($announcement['images']); $i++): ?>
                                            <button type="button" data-bs-target="#carousel<?php echo $announcement['id']; ?>" 
                                                    data-bs-slide-to="<?php echo $i; ?>" 
                                                    <?php echo $i == 0 ? 'class="active"' : ''; ?>></button>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="carousel-inner">
                                        <?php foreach ($announcement['images'] as $index => $image): ?>
                                            <div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
                                                <img src="<?php echo htmlspecialchars($image); ?>" 
                                                     class="announcement-image" 
                                                     alt="<?php echo htmlspecialchars($announcement['title']); ?>"
                                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjI1MCIgdmlld0JveD0iMCAwIDMwMCAyNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjMwMCIgaGVpZ2h0PSIyNTAiIGZpbGw9IiNmOGY5ZmEiLz48dGV4dCB4PSIxNTAiIHk9IjEyNSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE2IiBmaWxsPSIjNmM3NTdkIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5JbWFnZSBOb3QgRm91bmQ8L3RleHQ+PC9zdmc+';">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?php echo $announcement['id']; ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon"></span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carousel<?php echo $announcement['id']; ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon"></span>
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- No image - Default placeholder -->
                            <div class="announcement-image default-image">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="announcement-content">
                            <h5 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                            <p class="announcement-text">
                                <?php echo htmlspecialchars(truncateText($announcement['content'], 120)); ?>
                            </p>
                            
                            <div class="announcement-meta">
                                <div class="row align-items-center">
                                    <div class="col-12 mb-2">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <small><?php echo formatDate($announcement['date_posted']); ?></small>
                                    </div>
                                    <?php if ($announcement['posted_by_name']): ?>
                                        <div class="col-12">
                                            <i class="fas fa-user me-1"></i>
                                            <small><?php echo htmlspecialchars($announcement['posted_by_name']); ?></small>
                                            <?php if ($announcement['posted_by_position']): ?>
                                                <small class="text-muted"> - <?php echo htmlspecialchars($announcement['posted_by_position']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Read More Button -->
                                <div class="mt-3">
                                    <button class="btn btn-primary btn-sm" onclick="showFullAnnouncement(<?php echo $announcement['id']; ?>)">
                                        <i class="fas fa-eye me-1"></i> Read More
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal for Full Announcement -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="announcementModalLabel">Announcement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalContent">
        <div class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/foot.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
// Initialize AOS
AOS.init({
    duration: 1000,
    once: true,
    offset: 100
});

const announcementsData = <?php echo json_encode($announcements); ?>;

function showFullAnnouncement(announcementId) {
    const announcement = announcementsData.find(a => a.id == announcementId);
    
    if (!announcement) {
        alert('Announcement not found!');
        return;
    }
    
    let imagesHtml = '';
    if (announcement.images && announcement.images.length > 0) {
        if (announcement.images.length === 1) {
            imagesHtml = `<img src="${announcement.images[0]}" class="img-fluid rounded mb-3" alt="${announcement.title}">`;
        } else {
            imagesHtml = `
                <div id="modalCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        ${announcement.images.map((img, index) => 
                            `<button type="button" data-bs-target="#modalCarousel" data-bs-slide-to="${index}" ${index === 0 ? 'class="active"' : ''}></button>`
                        ).join('')}
                    </div>
                    <div class="carousel-inner">
                        ${announcement.images.map((img, index) => 
                            `<div class="carousel-item ${index === 0 ? 'active' : ''}">
                                <img src="${img}" class="d-block w-100 rounded" alt="${announcement.title}" style="max-height: 400px; object-fit: cover;">
                            </div>`
                        ).join('')}
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#modalCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#modalCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            `;
        }
    }
    
    const modalContent = `
        ${imagesHtml}
        <h4>${announcement.title}</h4>
        <div class="mb-3 text-muted">
            <small><i class="fas fa-calendar-alt me-1"></i> ${new Date(announcement.date_posted).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})}</small>
            ${announcement.posted_by_name ? `<small class="ms-3"><i class="fas fa-user me-1"></i> ${announcement.posted_by_name}</small>` : ''}
        </div>
        <div style="white-space: pre-wrap;">${announcement.content}</div>
    `;
    
    document.getElementById('announcementModalLabel').textContent = announcement.title;
    document.getElementById('modalContent').innerHTML = modalContent;
    
    const modal = new bootstrap.Modal(document.getElementById('announcementModal'));
    modal.show();
}


document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.announcement-image');
    images.forEach(img => {
        img.addEventListener('error', function() {
            if (this.tagName === 'IMG') {
                this.style.display = 'none';
                const defaultDiv = document.createElement('div');
                defaultDiv.className = 'announcement-image default-image';
                defaultDiv.innerHTML = '<i class="fas fa-image"></i>';
                this.parentNode.insertBefore(defaultDiv, this);
            }
        });
    });
});
</script>
</body>
</html>