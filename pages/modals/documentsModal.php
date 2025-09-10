<?php
// residents/documentsModal.php
require_once '../../config/db.php';
$docTypes = $conn->query("SELECT id, document_type FROM document_types ORDER BY document_type ASC");

$resident_id = isset($_SESSION['resident_id']) ? $_SESSION['resident_id'] : null;
$resident_data = null;

if ($resident_id) {
    $stmt = $conn->prepare("SELECT * FROM residents WHERE id = ?");
    $stmt->bind_param('i', $resident_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resident_data = $result->fetch_assoc();
    $stmt->close();
}
?>

<div class="modal fade" id="documentRequestModal" tabindex="-1" aria-labelledby="documentRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="documentRequestModalLabel">Document Request Form</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="documentRequestForm" method="POST" action="/barangay-balas/services/certificates/submit_request.php">
        <input type="hidden" name="resident_id" value="<?= $resident_id ?>">
        
        <div class="modal-body">
          <!-- Document Type -->
          <div class="mb-3">
            <label for="document_type_id" class="form-label">Document Type</label>
            <select class="form-select" name="document_type_id" id="document_type_id" required>
              <option value="">-- Select Document Type --</option>
              <?php while ($row = $docTypes->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['document_type']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Requester Info -->
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">First Name</label>
              <input type="text" class="form-control" name="first_name" 
                     value="<?= $resident_data ? htmlspecialchars($resident_data['first_name']) : '' ?>" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Middle Name</label>
              <input type="text" class="form-control" name="middle_name"
                     value="<?= $resident_data ? htmlspecialchars($resident_data['middle_name']) : '' ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Last Name</label>
              <input type="text" class="form-control" name="last_name"
                     value="<?= $resident_data ? htmlspecialchars($resident_data['last_name']) : '' ?>" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">House No.</label>
              <input type="text" class="form-control" name="houseno"
                     value="<?= $resident_data ? htmlspecialchars($resident_data['house_number']) : '' ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Purok</label>
              <input type="text" class="form-control" name="purok"
                     value="<?= $resident_data ? htmlspecialchars($resident_data['purok']) : '' ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Civil Status</label>
              <select class="form-select" name="civil_status">
                <option value="">-- Select --</option>
                <option value="Single" <?= $resident_data && $resident_data['civil_status'] == 'Single' ? 'selected' : '' ?>>Single</option>
                <option value="Married" <?= $resident_data && $resident_data['civil_status'] == 'Married' ? 'selected' : '' ?>>Married</option>
                <option value="Widowed" <?= $resident_data && $resident_data['civil_status'] == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                <option value="Separated" <?= $resident_data && $resident_data['civil_status'] == 'Separated' ? 'selected' : '' ?>>Separated</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Sex</label>
              <select class="form-select" name="sex" required>
                <option value="">-- Select --</option>
                <option value="male" <?= $resident_data && $resident_data['sex'] == 'male' ? 'selected' : '' ?>>Male</option>
                <option value="female" <?= $resident_data && $resident_data['sex'] == 'female' ? 'selected' : '' ?>>Female</option>
                <option value="other" <?= $resident_data && $resident_data['sex'] == 'other' ? 'selected' : '' ?>>Other</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Birthdate</label>
              <input type="date" class="form-control" name="birthdate" id="birthdate"
                     value="<?= $resident_data ? $resident_data['birthdate'] : '' ?>">
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Age</label>
              <input type="number" class="form-control" name="age" id="age" readonly
                     value="<?= $resident_data ? $resident_data['age'] : '' ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email"
                     value="<?= $resident_data ? htmlspecialchars($resident_data['email']) : '' ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Purpose</label>
            <textarea class="form-control" name="purpose" rows="2"></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Shipping Method</label>
            <select class="form-select" name="shipping_method">
              <option value="Claim Anytime">Claim Anytime</option>
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Submit Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const birthdateInput = document.getElementById("birthdate");
  const ageInput = document.getElementById("age");

  birthdateInput.addEventListener("change", function () {
    const birthdate = new Date(this.value);
    if (!isNaN(birthdate)) {
      const today = new Date();
      let age = today.getFullYear() - birthdate.getFullYear();
      const monthDiff = today.getMonth() - birthdate.getMonth();
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
        age--;
      }
      ageInput.value = age >= 0 ? age : "";
    } else {
      ageInput.value = "";
    }
  });
});
</script>
