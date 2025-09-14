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
        linear-gradient(to right, rgba(0, 51, 204, 0.7) 0%, rgba(153, 0, 0, 0.7) 80%), 
        url('../assets/img/officials.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      color: white;
      height: 70vh;
      display: flex;
      flex-direction: column;  
      align-items: center;
      justify-content: center;
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

<!-- Hero Section -->
<div class="hero-section">
  <h2 class="fw-bold">BARANGAY BALAS</h2>
  <h1 class="display-5 fw-bold text-warning">OFFICIALS</h1>
</div>

<!-- Officials Section -->
<div class="container form-section">
  <div class="row g-4">
    <div class="col-md-4" data-aos="fade-up" data-aos-duration="1000">
      <div class="card service-box h-100 text-center p-3">
        <img src="../../assets/images/officials/chairman.jpg" class="card-img-top rounded-circle mx-auto" alt="Chairman" style="width: 150px; height: 150px; object-fit: cover;">
        <div class="card-body">
          <h5 class="card-title">Juan Dela Cruz</h5>
          <p class="card-text">Barangay Chairman</p>
        </div>
      </div>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-duration="1200">
      <div class="card service-box h-100 text-center p-3">
        <img src="../../assets/images/officials/kagawad1.jpg" class="card-img-top rounded-circle mx-auto" alt="Kagawad 1" style="width: 150px; height: 150px; object-fit: cover;">
        <div class="card-body">
          <h5 class="card-title">Maria Santos</h5>
          <p class="card-text">Kagawad</p>
        </div>
      </div>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-duration="1400">
      <div class="card service-box h-100 text-center p-3">
        <img src="../../assets/images/officials/kagawad2.jpg" class="card-img-top rounded-circle mx-auto" alt="Kagawad 2" style="width: 150px; height: 150px; object-fit: cover;">
        <div class="card-body">
          <h5 class="card-title">Pedro Reyes</h5>
          <p class="card-text">Kagawad</p>
        </div>
      </div>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-duration="1600">
      <div class="card service-box h-100 text-center p-3">
        <img src="../../assets/images/officials/kagawad3.jpg" class="card-img-top rounded-circle mx-auto" alt="Kagawad 3" style="width: 150px; height: 150px; object-fit: cover;">
        <div class="card-body">
          <h5 class="card-title">Ana Lopez</h5>
          <p class="card-text">Kagawad</p>
        </div>
      </div>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-duration="1800">
      <div class="card service-box h-100 text-center p-3">
        <img src="../../assets/images/officials/kagawad4.jpg" class="card-img-top rounded-circle mx-auto" alt="Kagawad 4" style="width: 150px; height: 150px; object-fit: cover;">
        <div class="card-body">
          <h5 class="card-title">Luis Garcia</h5>
          <p class="card-text">Kagawad</p>
        </div>
      </div>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-duration="2000">
      <div class="card service-box h-100 text-center p-3">
        <img src="../../assets/images/officials/kagawad5.jpg" class="card-img-top rounded-circle mx-auto" alt="Kagawad 5" style="width: 150px; height: 150px; object-fit: cover;">
        <div class="card-body">
          <h5 class="card-title">Carmen Diaz</h5>
          <p class="card-text">Kagawad</p>
        </div>
      </div>
    </div>
  </div>


<?php include '../includes/foot.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
AOS.init();
</script>


</body>
</html>