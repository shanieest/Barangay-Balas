<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Barangay Balas - Indigency Certification</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <style>
    .hero-section {
      background: linear-gradient(to right, #0033cc 0%, #990000 97%);
      color: white;
      padding: 100px 20px;
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
<?php include '../../includes/header.php'; ?>

<!-- Hero Section -->
<div class="hero-section">
  <h2 class="fw-bold">CERTIFICATION FOR</h2>
  <h1 class="display-5 fw-bold text-warning">INDIGENCY</h1>
</div>

<!-- Form Section -->
<div class="form-section">
  <div class="container">
    <form id="documentRequests" action="documents_request.php" method="POST">
      <div class="row g-3">
        <input type="hidden" name="document_type" value="indigency">

        <!-- Name Fields -->
        <div class="col-md-4">
          <input name="first_name" type="text" class="form-control" placeholder="First Name *" required>
        </div>
        <div class="col-md-4">
          <input name="middle_name" type="text" class="form-control" placeholder="Middle Name">
        </div>
        <div class="col-md-4">
          <input name="last_name" type="text" class="form-control" placeholder="Last Name *" required>
        </div>


        <!-- Address -->
        <div class="col-md-4">
          <input name="address" type="text" class="form-control" placeholder="House No. *" required>
        </div>
        <div class="col-md-4">
          <input name="purok" type="text" class="form-control" placeholder="Purok *" required>
        </div>

        <!-- Personal Info -->
        <div class="col-md-4">
          <input name="age" type="number" class="form-control" placeholder="Age *" required>
        </div>
        <div class="col-md-4">
          <input name="civil_status" type="text" class="form-control" placeholder="Civil Status *" required>
        </div>
        <div class="col-md-4">
          <select name="sex" class="form-select" required>
            <option value="">Select Gender *</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>

        <!-- Purpose -->
        <div class="col-md-4">
          <input name="purpose" type="text" class="form-control" placeholder="Purpose *" required>
        </div>

        <!-- Email and Shipping -->
        <div class="col-md-6">
          <input name="email" type="email" class="form-control" placeholder="Email *" required>
        </div>
        <div class="col-md-6">
          <select name="shipping_method" class="form-select" required>
            <option value="">Select Shipping Method *</option>
            <option value="Pick Up">Pick Up (Claim Anytime)</option>
         </select>
        </div>
      </div>

      <div class="text-center mt-4">
        <button type="submit" form="documentRequests" class="btn btn-primary px-5">Send Request</button>
      </div>
    </form>
  </div>
</div>

<?php include '../../includes/foot.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
AOS.init();
</script>


</body>
</html>
