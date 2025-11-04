<?php
require_once '../../auth/auth.php';
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../signup.php");
    exit;
}

$userId = $_SESSION['user_id'];

$query = "
    SELECT digital_id_path, valid_until
    FROM barangay_id_applications
    WHERE resident_id = ? AND status = 'Approved'
    ORDER BY application_date DESC
    LIMIT 1
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    header("Location: profile.php?message=" . urlencode("You do not have an approved Barangay ID yet."));
    exit;
}

$digitalIdPath = "../../" . htmlspecialchars($data['digital_id_path']);
$validUntil = date('F d, Y', strtotime($data['valid_until']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Digital Barangay ID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <a href="profile.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i> Back to Profile
    </a>

    <div class="card shadow border-0">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>My Digital Barangay ID</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>Your Barangay ID is valid until <strong><?= $validUntil ?></strong>.
            </div>

            <div class="ratio ratio-4x3 border rounded shadow-sm">
                <iframe src="<?= $digitalIdPath ?>#toolbar=0" width="100%" height="600px" style="border:none;"></iframe>
            </div>

            <p class="text-muted text-center mt-3">
                <small>This digital ID is view-only and cannot be downloaded or printed.</small>
            </p>
        </div>
    </div>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
