<?php
require_once '../../config/db.php';

// Get document types
$docTypes = $conn->query("SELECT id, document_type FROM document_types ORDER BY document_type ASC");

// Get resident data if logged in
$resident_data = null;
if (isset($_SESSION['user_id'])) {
    $resident_sql = "SELECT r.*, ra.email as account_email 
                    FROM residents r 
                    LEFT JOIN resident_accounts ra ON r.id = ra.resident_id 
                    WHERE r.id = ?";
    $resident_stmt = $conn->prepare($resident_sql);
    $resident_stmt->bind_param("i", $_SESSION['user_id']);
    $resident_stmt->execute();
    $resident_result = $resident_stmt->get_result();
    
    if ($resident_result->num_rows > 0) {
        $resident_data = $resident_result->fetch_assoc();
    }
    $resident_stmt->close();
}
?>

<div class="modal fade" id="documentRequestModal" tabindex="-1" aria-labelledby="documentRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="documentRequestModalLabel">
          <i class="fas fa-file-alt me-2"></i>Document Request Form
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="documentRequestForm" method="POST" action="/barangay-balas/services/certificates/submit_request.php">
        <div class="modal-body">
          <div id="documentRequestMessage" class="alert d-none"></div>
          
          <!-- Resident Information Alert -->
          <?php if ($resident_data): ?>
          <div class="alert alert-info">
            <i class="fas fa-user me-2"></i>
            <strong>Requesting as:</strong> <?= htmlspecialchars($resident_data['first_name'] . ' ' . $resident_data['last_name']) ?>
            <br><small>Your information below is pre-filled from your account. You can update if needed for this request.</small>
          </div>
          <?php else: ?>
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Account Required:</strong> You need to be logged in to request a document. Please log in or create an account first.
          </div>
          <?php endif; ?>
          
          <?php if ($resident_data): ?>
          <!-- Document Type -->
          <div class="mb-3">
            <label for="document_type_id" class="form-label">Document Type <span class="text-danger">*</span></label>
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
              <label class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="first_name" 
                     value="<?= htmlspecialchars($resident_data['first_name']) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Middle Name</label>
              <input type="text" class="form-control" name="middle_name"
                     value="<?= htmlspecialchars($resident_data['middle_name']) ?>">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="last_name"
                     value="<?= htmlspecialchars($resident_data['last_name']) ?>" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">House No. <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="houseno"
                     value="<?= htmlspecialchars($resident_data['house_number']) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Purok <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="purok"
                     value="<?= htmlspecialchars($resident_data['purok']) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Civil Status <span class="text-danger">*</span></label>
              <select class="form-select" name="civil_status" required>
                <option value="">-- Select --</option>
                <option value="Single" <?= $resident_data['civil_status'] == 'Single' ? 'selected' : '' ?>>Single</option>
                <option value="Married" <?= $resident_data['civil_status'] == 'Married' ? 'selected' : '' ?>>Married</option>
                <option value="Widowed" <?= $resident_data['civil_status'] == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                <option value="Separated" <?= $resident_data['civil_status'] == 'Separated' ? 'selected' : '' ?>>Separated</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Sex <span class="text-danger">*</span></label>
              <select class="form-select" name="sex" required>
                <option value="">-- Select --</option>
                <option value="male" <?= $resident_data['sex'] == 'male' ? 'selected' : '' ?>>Male</option>
                <option value="female" <?= $resident_data['sex'] == 'female' ? 'selected' : '' ?>>Female</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Birthdate <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="birthdate" id="birthdate"
                     value="<?= $resident_data['birthdate'] ?>" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Age</label>
              <input type="number" class="form-control" name="age" id="age" readonly
                     value="<?= $resident_data['age'] ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Contact Number</label>
              <input type="text" class="form-control" name="contact_number"
                     value="<?= htmlspecialchars($resident_data['contact_number']) ?>">
              <div class="form-text">You can update your contact number if needed.</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Purpose <span class="text-danger">*</span></label>
            <textarea class="form-control" name="purpose" rows="2" placeholder="Please specify the purpose of this document request..." required></textarea>
          </div>

          <!-- Hidden inputs for resident data -->
          <input type="hidden" name="resident_id" value="<?= $resident_data['id'] ?>">
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <?php if ($resident_data): ?>
          <button type="submit" class="btn btn-primary" id="submitDocumentRequest">
            <i class="fas fa-paper-plane me-1"></i>Submit Request
          </button>
          <?php else: ?>
          <button type="button" class="btn btn-primary" disabled>
            <i class="fas fa-lock me-1"></i>Login Required
          </button>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const birthdateInput = document.getElementById("birthdate");
  const ageInput = document.getElementById("age");
  const documentRequestForm = document.getElementById("documentRequestForm");
  const messageDiv = document.getElementById("documentRequestMessage");

  function calculateAge() {
    const birthdate = new Date(birthdateInput.value);
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
  }

  // Calculate on change
  if (birthdateInput) {
    birthdateInput.addEventListener("change", calculateAge);
  }

  // Handle form submission with AJAX (only if resident is logged in)
  <?php if ($resident_data): ?>
  documentRequestForm.addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = document.getElementById('submitDocumentRequest');
    
    // Clear any existing messages
    messageDiv.className = 'alert d-none';
    messageDiv.textContent = '';
    
    // Disable submit button to prevent double submission
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
    
    fetch('/barangay-balas/services/certificates/submit_request.php', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        messageDiv.className = 'alert alert-success';
        messageDiv.textContent = data.message;
        messageDiv.classList.remove('d-none');
        
        // Reset form after a short delay to let user see the success message
        setTimeout(() => {
          documentRequestForm.reset();
          // Reset age field as well
          if (ageInput) ageInput.value = '<?= $resident_data['age'] ?>';
          
          // Close modal after showing success
          setTimeout(() => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('documentRequestModal'));
            if (modal) {
              modal.hide();
            }
          }, 1500);
        }, 1000);
        
      } else {
        messageDiv.className = 'alert alert-danger';
        messageDiv.textContent = data.message || 'An error occurred while submitting the request.';
        messageDiv.classList.remove('d-none');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      messageDiv.className = 'alert alert-danger';
      messageDiv.textContent = 'An error occurred while submitting the request.';
      messageDiv.classList.remove('d-none');
    })
    .finally(() => {
      // Re-enable submit button
      submitButton.disabled = false;
      submitButton.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Request';
    });
  });
  <?php endif; ?>
});
</script>