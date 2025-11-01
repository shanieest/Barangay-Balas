<?php
require_once '../config/db.php';

// Fetch officials from database
$query = "SELECT id, first_name, last_name, position, committee_position, photo_path 
          FROM admin_users 
          WHERE status = 'active' 
          ORDER BY FIELD(position, 'Barangay Captain', 'Barangay Secretary', 'Barangay Treasurer', 'Barangay Kagawad', 'SK Chairman')";
$result = $conn->query($query);

// Group officials by position
$officials = [
  'captain' => null,
  'secretary' => [],
  'treasurer' => [],
  'kagawad' => [],
  'sk' => []
];

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $pos = strtolower($row['position']);
    if ($pos === 'barangay captain') $officials['captain'] = $row;
    elseif ($pos === 'barangay secretary') $officials['secretary'][] = $row;
    elseif ($pos === 'barangay treasurer') $officials['treasurer'][] = $row;
    elseif ($pos === 'barangay kagawad') $officials['kagawad'][] = $row;
    elseif ($pos === 'sk chairman') $officials['sk'][] = $row;
  }
}

// Function to format name with Hon.
function formatOfficialName($firstName, $lastName) {
    return "Hon. " . $firstName . " " . $lastName;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Barangay Balas - Officials</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/officials.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="hero-section">
  <h2 class="fw-bold">BARANGAY BALAS</h2>
  <h1 class="display-5 fw-bold text-warning">OFFICIALS</h1>
</div>

<div class="container py-5">
  <div class="org-chart">

    <!-- Barangay Captain - Level 1 -->
    <?php if ($officials['captain']): ?>
    <div class="org-level" data-aos="fade-up">
      <div class="official-card captain-card">
        <img src="<?= $officials['captain']['photo_path'] ? '../admin/'.$officials['captain']['photo_path'] : '../assets/images/default-avatar.jpg' ?>" alt="">
        <div class="official-info">
          <h5 class="official-name"><?= formatOfficialName($officials['captain']['first_name'], $officials['captain']['last_name']) ?></h5>
          <p class="official-position">Barangay Captain</p>
          <p class="official-committee"><?= $officials['captain']['committee_position'] ?></p>
        </div>
      </div>
    </div>
    <div class="vertical-connector"></div>
    <?php endif; ?>

    <!-- Secretary, Treasurer & Kagawads - Level 2 -->
    <div class="org-level position-relative" data-aos="fade-up">
      <div class="horizontal-connector"></div>
      
      <?php 
        $secretary = !empty($officials['secretary']) ? $officials['secretary'][0] : null;
        $treasurer = !empty($officials['treasurer']) ? $officials['treasurer'][0] : null;
      ?>

      <!-- Secretary -->
      <?php if ($secretary): ?>
        <div class="official-card position-relative">
          <div class="node-connector"></div>
          <img src="<?= $secretary['photo_path'] ? '../admin/'.$secretary['photo_path'] : '../assets/images/default-avatar.jpg' ?>" alt="">
          <div class="official-info">
            <h5 class="official-name"><?= formatOfficialName($secretary['first_name'], $secretary['last_name']) ?></h5>
            <p class="official-position">Barangay Secretary</p>
            <p class="official-committee"><?= $secretary['committee_position'] ?></p>
          </div>
        </div>
      <?php endif; ?>

      <!-- Treasurer -->
      <?php if ($treasurer): ?>
        <div class="official-card position-relative">
          <div class="node-connector"></div>
          <img src="<?= $treasurer['photo_path'] ? '../admin/'.$treasurer['photo_path'] : '../assets/images/default-avatar.jpg' ?>" alt="">
          <div class="official-info">
            <h5 class="official-name"><?= formatOfficialName($treasurer['first_name'], $treasurer['last_name']) ?></h5>
            <p class="official-position">Barangay Treasurer</p>
            <p class="official-committee"><?= $treasurer['committee_position'] ?></p>
          </div>
        </div>
      <?php endif; ?>

      <!-- Kagawads -->
      <?php if (!empty($officials['kagawad'])): ?>
        <?php foreach ($officials['kagawad'] as $official): ?>
          <div class="official-card position-relative" data-aos="zoom-in">
            <div class="node-connector"></div>
            <img src="<?= $official['photo_path'] ? '../admin/'.$official['photo_path'] : '../assets/images/default-avatar.jpg' ?>" alt="">
            <div class="official-info">
              <h6 class="official-name"><?= formatOfficialName($official['first_name'], $official['last_name']) ?></h6>
              <p class="official-position">Barangay Kagawad</p>
              <p class="official-committee"><?= $official['committee_position'] ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="vertical-connector"></div>

    <!-- SK Chairman - Level 3 -->
    <?php if (!empty($officials['sk'])): ?>
    <div class="org-level" data-aos="fade-up">
      <?php $sk = $officials['sk'][0]; ?>
      <div class="official-card">
        <img src="<?= $sk['photo_path'] ? '../admin/'.$sk['photo_path'] : '../assets/images/default-avatar.jpg' ?>" alt="">
        <div class="official-info">
          <h5 class="official-name"><?= formatOfficialName($sk['first_name'], $sk['last_name']) ?></h5>
          <p class="official-position">SK Chairman</p>
          <p class="official-committee"><?= $sk['committee_position'] ?></p>
        </div>
      </div>
    </div>
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