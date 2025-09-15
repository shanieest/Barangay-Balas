<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Barangay Balas, Mexico, Pampanga</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />

  <style>
    .hero-section {
     background-image: 
        linear-gradient(to right, rgba(0, 51, 204, 0.7) 0%, rgba(153, 0, 0, 0.7) 80%), 
        url('assets/img/arc.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      color: white;
      height: 70vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
    }
    .section-title { margin-top: 4rem; margin-bottom: 2rem; text-align: center; }
    .info-card { transition: transform 0.3s ease; }
    .info-card:hover { transform: translateY(-5px); }
    .news-img { max-height: 420px; object-fit: cover; }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <h1 class="display-4 fw-bolder">Barangay Balas Online Management System</h1>
    <p class="lead">Serving the community of Balas, Mexico, Pampanga</p>
  </div>
</section>

<!-- About Us Section -->
<section id="about" class="py-5">
  <div class="container">
    <h2 class="section-title fw-bolder">About Barangay Balas</h2>
    <div class="row align-items-center">
      <div class="col-md-6">
        <img src="assets/img/balas-logo.png" alt="Barangay Balas Logo" class="img-fluid"/>
      </div>
      <div class="col-md-6">
        <p class="fs-5">
          Barangay Balas is a vibrant and growing community in Mexico, Pampanga. Our mission is to
          provide accessible and transparent local governance to our residents. We aim to offer
          streamlined public services and foster community involvement.
        </p>
        <p class="text-muted">Through this online system, we are bringing government services closer to you.</p>
      </div>
    </div>
  </div>
</section>

<!-- News and Announcements Section (index.php) -->
<section id="news" class="py-5 bg-light">
  <div class="container">
    <h2 class="section-title">Latest News & Announcements</h2>

    <?php
      require_once __DIR__ . '/config/db.php';
      
      // Fixed query to get images from announcement_images table
      $q = $conn->prepare("SELECT a.id, a.title, a.content, 
                                  a.date_posted, au.first_name, au.last_name,
                                  GROUP_CONCAT(ai.image_path) AS image_paths
                           FROM announcements a
                           LEFT JOIN admin_users au ON au.id = a.posted_by
                           LEFT JOIN announcement_images ai ON ai.announcement_id = a.id
                           GROUP BY a.id
                           ORDER BY a.date_posted DESC, a.created_at DESC
                           LIMIT 5");
      $q->execute();
      $res = $q->get_result();
      $announcements = $res->fetch_all(MYSQLI_ASSOC);
      $hasAnnouncements = count($announcements) > 0;

      // Helper for image display - Fixed path construction
    function news_img($image_paths) {
    if (!$image_paths || !trim($image_paths)) {
        return 'assets/img/news-placeholder.jpg';
    }

    $images = explode(',', $image_paths);
    $firstImage = trim($images[0]);

    // Try with admin folder prefix
    if (file_exists(__DIR__ . '/admin/' . $firstImage)) {
        return 'admin/' . $firstImage;
    }

    // Fallback: if stored path works directly
    if (file_exists(__DIR__ . '/' . $firstImage)) {
        return $firstImage;
    }

    return 'assets/img/news-placeholder.jpg';
}

    ?>

    <?php if ($hasAnnouncements): ?>
      <div id="newsCarousel"
           class="carousel slide"
           data-bs-ride="carousel"
           data-bs-interval="5000"
           data-bs-pause="hover">

        <!-- Indicators -->
        <div class="carousel-indicators">
          <?php foreach ($announcements as $i => $_): ?>
            <button type="button"
                    data-bs-target="#newsCarousel"
                    data-bs-slide-to="<?= $i ?>"
                    class="<?= $i === 0 ? 'active' : '' ?>"
                    aria-label="Slide <?= $i+1 ?>"></button>
          <?php endforeach; ?>
        </div>

        <!-- Slides -->
        <div class="carousel-inner">
          <?php foreach ($announcements as $i => $row): ?>
            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
              <div class="card shadow-sm mx-auto" style="max-width: 900px;">
                <?php 
                $imagePath = news_img($row['image_paths']);
                $hasImage = $imagePath !== 'assets/img/news-placeholder.jpg';
                ?>
                
                <?php if ($hasImage): ?>
                  <div class="position-relative">
                    <img src="<?= $imagePath ?>" class="card-img-top news-img" alt="Announcement image" style="height: 300px; object-fit: cover; cursor: pointer;" onclick="showImageModal('<?= $imagePath ?>')">
                    <div class="position-absolute top-0 end-0 p-2">
                      <span class="badge bg-primary">
                        <i class="bi bi-eye"></i> Click to view
                      </span>
                    </div>
                  </div>
                <?php endif; ?>
                
                <div class="card-body text-center">
                  <h5 class="card-title mb-2"><?= htmlspecialchars($row['title']) ?></h5>
                  <p class="small text-muted mb-3">
                    <i class="bi bi-calendar-event"></i>
                    <?= date('F j, Y', strtotime($row['date_posted'])) ?>
                    <?php if (!empty($row['first_name']) || !empty($row['last_name'])): ?>
                      • <i class="bi bi-person"></i> by <?= htmlspecialchars(trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? ''))) ?>
                    <?php endif; ?>
                  </p>
                  <p class="card-text">
                    <?= htmlspecialchars(mb_strimwidth(strip_tags($row['content']), 0, 160, '…')) ?>
                  </p>
                  <div class="d-flex justify-content-center gap-2">
                    <a href="public/announcements.php#announcement-<?= (int)$row['id'] ?>" class="btn btn-outline-primary btn-sm">
                      <i class="bi bi-eye"></i> Read More
                    </a>
                    <?php if ($hasImage): ?>
                      <button class="btn btn-outline-secondary btn-sm" onclick="showAllImages(<?= (int)$row['id'] ?>)">
                        <i class="bi bi-images"></i> View Images
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Navigation arrows -->
        <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
      
      <!-- Image Modal -->
      <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0">
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
              <img id="modalImage" src="" class="img-fluid w-100 rounded" alt="Announcement Image">
            </div>
          </div>
        </div>
      </div>

      <!-- Multiple Images Modal -->
      <div class="modal fade" id="multiImageModal" tabindex="-1" aria-labelledby="multiImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="multiImageModalLabel">Announcement Images</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div id="imageGallery" class="row g-3">
                <!-- Images will be loaded here -->
              </div>
            </div>
          </div>
        </div>
      </div>

    <?php else: ?>
      <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="bi bi-info-circle me-2"></i>
        No announcements yet. Please check back later.
      </div>
    <?php endif; ?>
  </div>
</section>


<!-- Services / Features -->
<section id="services" class="py-5 bg-light">
  <div class="container">
    <h2 class="section-title fw-bolder">Barangay Online Services</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card info-card shadow-sm p-3 text-center">
          <div class="card-body">
            <i class="bi bi-person-badge display-5 text-warning mb-3"></i>
            <h5 class="card-title">Barangay Clearance</h5>
            <p class="card-text">Quick and easy processing of barangay clearance documents online.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card info-card shadow-sm p-3 text-center">
          <div class="card-body">
            <i class="bi bi-house-door display-5 text-warning mb-3"></i>
            <h5 class="card-title">Residency Certificate</h5>
            <p class="card-text">Get your certificate of residency without visiting the barangay hall.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card info-card shadow-sm p-3 text-center">
          <div class="card-body">
            <i class="bi bi-people-fill display-5 text-warning mb-3"></i>
            <h5 class="card-title">Indigency</h5>
            <p class="card-text">Accessing medical assistance, and educational support.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Map Section -->
<section id="map" class="py-5">
  <div class="container">
    <h2 class="section-title fw-bolder">Barangay Balas Location</h2>
    <div class="row">
      <div class="h-100 rounded shadow overflow-hidden">
        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d30819.63833916192!2d120.71717999999998!3d15.078242999999999!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3396fa72b5b92d3f%3A0x4e5f0a93bbada2a0!2sBalas%2C%20Mexico%2C%20Pampanga!5e0!3m2!1sen!2sph!4v1757904223740!5m2!1sen!2sph"
           width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </div>
  </div>
</section>

<!-- Contact Us Section -->
<section id="contact" class="py-5 bg-light">
  <div class="container">
    <div class="row align-items-center g-5">
      <!-- Left -->
      <div class="col-md-6">
        <div class="mb-4">
          <h2 class="fw-bolder">Get In Touch</h2>
          <p class="text-muted">We're happy to assist you. Reach out anytime, and we'll respond as soon as we can.</p>
        </div>

        <div class="d-flex align-items-start mb-3">
          <div class="me-3 fs-4 text-warning"><i class="bi bi-geo-alt-fill"></i></div>
          <div>
            <h6 class="mb-1 fw-semibold">Address</h6>
            <p class="mb-0">Barangay Hall, Balas, Mexico, Pampanga</p>
          </div>
        </div>

        <div class="d-flex align-items-start mb-3">
          <div class="me-3 fs-4 text-warning"><i class="bi bi-telephone-fill"></i></div>
          <div>
            <h6 class="mb-1 fw-semibold">Phone</h6>
            <p class="mb-0">+63 123 456 7890</p>
          </div>
        </div>

        <div class="d-flex align-items-start">
          <div class="me-3 fs-4 text-warning"><i class="bi bi-envelope-fill"></i></div>
          <div>
            <h6 class="mb-1 fw-semibold">Email</h6>
            <p class="mb-0">balas@gmail.com</p>
          </div>
        </div>
      </div>

      <!-- Right -->
      <div class="col-md-6">
        <div class="card border-0 shadow-sm p-4 rounded-4">
          <form>
            <div class="mb-3">
              <label class="form-label">Your Name</label>
              <input type="text" class="form-control" placeholder="Juan Dela Cruz" />
            </div>
            <div class="mb-3">
              <label class="form-label">Your Email</label>
              <input type="email" class="form-control" placeholder="juan@example.com" />
            </div>
            <div class="mb-3">
              <label class="form-label">Message</label>
              <textarea class="form-control" rows="4" placeholder="Write your message here..."></textarea>
            </div>
            <button class="btn btn-warning w-100" type="submit">Send Message</button>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/foot.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>

</body>
</html>
