<?php
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

<div class="modal fade" id="tentReservationModal" tabindex="-1" aria-labelledby="tentReservationLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tentReservationLabel">
          <i class="fas fa-campground me-2"></i>Reserve Barangay Services
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="tentReservationForm">
        <div class="modal-body">
          <div id="tentReservationMessage" class="alert d-none"></div>
          
          <!-- Resident Information Alert -->
          <?php if ($resident_data): ?>
          <div class="alert alert-info">
            <i class="fas fa-user me-2"></i>
            <strong>Reserving as:</strong> <?= htmlspecialchars($resident_data['first_name'] . ' ' . $resident_data['last_name']) ?>
            <br><small>Your information is pre-filled from your account and cannot be modified.</small>
          </div>
          <?php else: ?>
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Account Required:</strong> You need to be logged in to make a reservation. Please log in or create an account first.
          </div>
          <?php endif; ?>
          
          <?php if ($resident_data): ?>
          <!-- Contact Information -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Contact Number <span class="text-danger">*</span></label>
              <input type="text" name="contact_number" class="form-control" 
                     pattern="[0-9]{11}" 
                     value="<?= htmlspecialchars($resident_data['contact_number']) ?>"
                     readonly>
              <div class="form-text">Contact number from your account.</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-control"
                     value="<?= htmlspecialchars($resident_data['account_email'] ?: $resident_data['email'] ?: '') ?>" readonly>
              <div class="form-text">Email address from your account.</div>
            </div>
          </div>

          <!-- Reservation Details -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Start Date <span class="text-danger">*</span></label>
              <input type="date" name="reservation_date_start" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">End Date</label>
              <input type="date" name="reservation_date_end" class="form-control">
              <div class="form-text">Leave empty for single day reservation</div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Setup Time</label>
              <input type="time" name="setup_time" class="form-control">
              <div class="form-text">Preferred setup time</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Event Duration</label>
              <select name="duration_type" class="form-select">
                <option value="half_day">Half Day (4 hours)</option>
                <option value="full_day" selected>Full Day (8 hours)</option>
                <option value="overnight">Overnight</option>
                <option value="multiple_days">Multiple Days</option>
              </select>
            </div>
          </div>

          <!-- Service Selection with Quantities -->
          <div class="mb-3">
            <label class="form-label">Services Required <span class="text-danger">*</span></label>
            <div class="row">
              <div class="col-md-6">
                <!-- Tent Service -->
                <div class="card border-warning mb-3">
                  <div class="card-body p-3">
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" name="service_types[]" value="1" id="tentService">
                      <label class="form-check-label fw-bold" for="tentService">
                        <i class="fas fa-campground text-warning me-2"></i>Tent
                      </label>
                    </div>
                    <div class="quantity-control" id="tentQuantityControl" style="display: none;">
                      <label class="form-label small">Quantity:</label>
                      <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity('tent_qty', -1)">-</button>
                        <input type="number" name="tent_qty" id="tent_qty" class="form-control text-center" min="1" max="10" value="1">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity('tent_qty', 1)">+</button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Tables and Chairs Service -->
                <div class="card border-success mb-3">
                  <div class="card-body p-3">
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" name="service_types[]" value="4" id="tablesChairsService">
                      <label class="form-check-label fw-bold" for="tablesChairsService">
                        <i class="fas fa-chair text-success me-2"></i>Tables and Chairs
                      </label>
                    </div>
                    <div class="quantity-control" id="tablesChairsQuantityControl" style="display: none;">
                      <label class="form-label small">Sets (1 table + 4 chairs):</label>
                      <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity('tables_chairs_qty', -1)">-</button>
                        <input type="number" name="tables_chairs_qty" id="tables_chairs_qty" class="form-control text-center" min="1" max="20" value="1">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity('tables_chairs_qty', 1)">+</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-md-6">
                <!-- Sound System Service -->
                <div class="card border-info mb-3">
                  <div class="card-body p-3">
                    <div class="form-check mb-2">
                      <input class="form-check-input" type="checkbox" name="service_types[]" value="3" id="soundSystemService">
                      <label class="form-check-label fw-bold" for="soundSystemService">
                        <i class="fas fa-volume-up text-info me-2"></i>Sound System
                      </label>
                    </div>
                    <div class="quantity-control" id="soundSystemQuantityControl" style="display: none;">
                      <label class="form-label small">Units:</label>
                      <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity('sound_system_qty', -1)">-</button>
                        <input type="number" name="sound_system_qty" id="sound_system_qty" class="form-control text-center" min="1" max="5" value="1">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity('sound_system_qty', 1)">+</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Event Location</label>
            <input type="text" name="event_location" class="form-control" 
                   placeholder="Where will the services be used?"
                   value="House <?= htmlspecialchars($resident_data['house_number']) ?>, Purok <?= htmlspecialchars($resident_data['purok']) ?>, Balas, Mexico, Pampanga" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label">Purpose/Event Type <span class="text-danger">*</span></label>
            <textarea name="purpose" class="form-control" rows="3" 
                      placeholder="Please describe your event (e.g., birthday party, wedding, meeting)..." required></textarea>
          </div>

          <!-- Hidden inputs for resident data -->
          <input type="hidden" name="action" value="create_reservation">
          <input type="hidden" name="resident_id" value="<?= $resident_data['id'] ?>">
          <input type="hidden" name="first_name" value="<?= htmlspecialchars($resident_data['first_name']) ?>">
          <input type="hidden" name="last_name" value="<?= htmlspecialchars($resident_data['last_name']) ?>">
          <?php endif; ?>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <?php if ($resident_data): ?>
          <button type="submit" class="btn btn-warning" id="submitReservation">
            <i class="fas fa-paper-plane me-1"></i>Submit Reservation
          </button>
          <?php else: ?>
          <button type="button" class="btn btn-warning" disabled>
            <i class="fas fa-lock me-1"></i>Login Required
          </button>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Service checkboxes and quantity controls
const services = [
  { checkbox: 'tentService', quantityControl: 'tentQuantityControl', quantityInput: 'tent_qty' },
  { checkbox: 'tablesChairsService', quantityControl: 'tablesChairsQuantityControl', quantityInput: 'tables_chairs_qty' },
  { checkbox: 'soundSystemService', quantityControl: 'soundSystemQuantityControl', quantityInput: 'sound_system_qty' }
];

// Initialize service controls when modal is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Set minimum date to tomorrow
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  const tomorrowString = tomorrow.toISOString().split('T')[0];
  
  const startDateInput = document.querySelector('input[name="reservation_date_start"]');
  const endDateInput = document.querySelector('input[name="reservation_date_end"]');
  
  if (startDateInput) {
    startDateInput.setAttribute('min', tomorrowString);
  }
  if (endDateInput) {
    endDateInput.setAttribute('min', tomorrowString);
  }

  // Initialize service controls
  services.forEach(service => {
    const checkbox = document.getElementById(service.checkbox);
    const quantityControl = document.getElementById(service.quantityControl);
    
    if (checkbox && quantityControl) {
      checkbox.addEventListener('change', function() {
        if (this.checked) {
          quantityControl.style.display = 'block';
        } else {
          quantityControl.style.display = 'none';
          // Reset quantity to 1 when unchecked
          const quantityInput = document.getElementById(service.quantityInput);
          if (quantityInput) {
            quantityInput.value = 1;
          }
        }
      });
    }
  });

  // Date validation
  if (startDateInput && endDateInput) {
    startDateInput.addEventListener('change', function() {
      endDateInput.min = this.value;
      if (endDateInput.value && endDateInput.value < this.value) {
        endDateInput.value = this.value;
      }
    });
  }
});

// Quantity change function
function changeQuantity(inputId, change) {
  const input = document.getElementById(inputId);
  if (input) {
    let currentValue = parseInt(input.value) || 1;
    let newValue = currentValue + change;
    let min = parseInt(input.getAttribute('min')) || 1;
    let max = parseInt(input.getAttribute('max')) || 100;
    
    if (newValue >= min && newValue <= max) {
      input.value = newValue;
    }
  }
}

// Form submission - only if resident is logged in
<?php if ($resident_data): ?>
document.getElementById('tentReservationForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const submitButton = document.getElementById('submitReservation');
  const messageDiv = document.getElementById('tentReservationMessage');
  
  // Check if at least one service is selected
  const selectedServices = formData.getAll('service_types[]');
  if (selectedServices.length === 0) {
    messageDiv.className = 'alert alert-danger';
    messageDiv.textContent = 'Please select at least one service.';
    messageDiv.classList.remove('d-none');
    return;
  }
  
  // Ensure quantities are included in form data
  const serviceQuantities = {
    'tent_qty': document.getElementById('tent_qty').value || '1',
    'tables_chairs_qty': document.getElementById('tables_chairs_qty').value || '1',
    'sound_system_qty': document.getElementById('sound_system_qty').value || '1',
    'vehicle_qty': '1'
  };
  
  // Add quantities to form data
  Object.entries(serviceQuantities).forEach(([key, value]) => {
    formData.set(key, value);
  });
  
  submitButton.disabled = true;
  submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
  
  fetch('pages/residents/reservation-backend.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      messageDiv.className = 'alert alert-success';
      messageDiv.textContent = data.message;
      messageDiv.classList.remove('d-none');
      
      // Reset form after successful submission
      setTimeout(() => {
        const form = document.getElementById('tentReservationForm');
        
        // Reset non-hidden fields
        form.querySelector('[name="contact_number"]').value = '<?= htmlspecialchars($resident_data['contact_number']) ?>';
        form.querySelector('[name="email"]').value = '<?= htmlspecialchars($resident_data['account_email'] ?: $resident_data['email'] ?: '') ?>';
        form.querySelector('[name="event_location"]').value = 'House <?= htmlspecialchars($resident_data['house_number']) ?>, Purok <?= htmlspecialchars($resident_data['purok']) ?>, Balas, Mexico, Pampanga';
        form.querySelector('[name="reservation_date_start"]').value = '';
        form.querySelector('[name="reservation_date_end"]').value = '';
        form.querySelector('[name="setup_time"]').value = '';
        form.querySelector('[name="duration_type"]').value = 'full_day';
        form.querySelector('[name="purpose"]').value = '';
        
        // Reset quantity controls
        services.forEach(service => {
          const checkbox = document.getElementById(service.checkbox);
          const quantityControl = document.getElementById(service.quantityControl);
          const quantityInput = document.getElementById(service.quantityInput);
          if (checkbox) checkbox.checked = false;
          if (quantityControl) quantityControl.style.display = 'none';
          if (quantityInput) quantityInput.value = 1;
        });
        
        document.getElementById('tentReservationModal').querySelector('[data-bs-dismiss="modal"]').click();
      }, 2000);
    } else {
      messageDiv.className = 'alert alert-danger';
      messageDiv.textContent = data.message || 'An error occurred while submitting your reservation.';
      messageDiv.classList.remove('d-none');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    messageDiv.className = 'alert alert-danger';
    messageDiv.textContent = 'Network error. Please try again.';
    messageDiv.classList.remove('d-none');
  })
  .finally(() => {
    submitButton.disabled = false;
    submitButton.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Reservation';
  });
});
<?php endif; ?>
</script>

<style>
.quantity-control {
  margin-top: 10px;
}

.quantity-control .input-group {
  max-width: 120px;
}

.card {
  transition: all 0.3s ease;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.form-check-input:checked + .form-check-label {
  color: #0d6efd;
  font-weight: bold;
}
</style>