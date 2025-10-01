<?php
// Include your database connection
require_once '../config/db.php'; // adjust path if needed

// Fetch officials from database
$query = "SELECT id, first_name, last_name, position, photo_path FROM admin_users";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Barangay Balas - Officials</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <style>
    .hero-section {
      background-image: 
        linear-gradient(to right, rgba(0, 51, 204, 0.7), rgba(153, 0, 0, 0.7)), 
        url('../assets/img/officials.jpg');
      background-size: cover;
      background-position: center;
      height: 70vh;
      display: flex;
      flex-direction: column;  
      align-items: center;
      justify-content: center;
      color: white;
      text-align: center;
    }
    .form-section {
      padding: 40px 20px;
      background-color: #f8f9fa;
    }
    .service-box:hover {
      background-color:  #f8f9fa;
      transform: translateY(-10px) scale(1.05);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="hero-section">
  <h2 class="fw-bold">BARANGAY BALAS</h2>
  <h1 class="display-5 fw-bold text-warning">OFFICIALS</h1>
</div>

<div class="container form-section">
  <div class="row g-4">
    <?php if($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="col-md-4" data-aos="fade-up" data-aos-duration="1200">
              <div class="card service-box h-100 text-center p-3">
              <img src="<?= $row['photo_path'] ? '../admin/'.$row['photo_path'] : '../assets/images/default-avatar.jpg' ?>" 
     class="card-img-top rounded-circle mx-auto" 
     alt="<?= $row['first_name'].' '.$row['last_name'] ?>" 
     style="width: 150px; height: 150px; object-fit: cover;">

                <div class="card-body">
                  <h5 class="card-title"><?= $row['first_name'].' '.$row['last_name'] ?></h5>
                  <p class="card-text"><?= $row['position'] ?></p>
                </div>
              </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-center">No officials found.</p>
    <?php endif; ?>
  </div>
</div>

<?php include '../includes/foot.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init();
</script>
</body>
</html>
