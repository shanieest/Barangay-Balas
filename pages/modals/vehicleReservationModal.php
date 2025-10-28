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

<div class="modal fade" id="vehicleReservationModal" tabindex="-1" aria-labelledby="vehicleReservationLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="vehicleReservationLabel">
          <i class="fas fa-bus me-2"></i>Reserve Service Vehicle
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="vehicleReservationForm">
        <div class="modal-body">
          <div id="vehicleReservationMessage" class="alert d-none"></div>
          
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
            <strong>Account Required:</strong> You need to be logged in to make a vehicle reservation. Please log in or create an account first.
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
              <div class="form-text">Email address from your account</div>
            </div>
          </div>

          <!-- Vehicle Type Selection -->
          <div class="row mb-3">
            <div class="col-12">
              <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
              <select name="vehicleType" id="vehicleType" class="form-select" required>
                <option value="">Select Vehicle Type</option>
                <option value="2">Patrol Car</option>
                <option value="5">Van</option>
                <option value="6">Motorcycle</option>
              </select>
              <div class="form-text">Only 1 vehicle can be reserved per request. Maximum reservation duration is 2 days.</div>
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
              <div class="form-text">Leave empty for single day reservation (max 2 days)</div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Start Time <span class="text-danger">*</span></label>
              <input type="time" name="start_time" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">End Time</label>
              <input type="time" name="end_time" class="form-control">
              <div class="form-text">Estimated return time</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Purpose of Use <span class="text-danger">*</span></label>
            <textarea name="purpose" class="form-control" rows="3" 
                      placeholder="Please describe the purpose for using the vehicle..." required></textarea>
          </div>

          <!-- Hidden inputs for resident data and service type -->
          <input type="hidden" name="action" value="create_reservation">
          <input type="hidden" name="service_types[]" id="vehicleServiceInput" value="2">
          <input type="hidden" name="resident_id" value="<?= $resident_data['id'] ?>">
          <input type="hidden" name="first_name" value="<?= htmlspecialchars($resident_data['first_name']) ?>">
          <input type="hidden" name="last_name" value="<?= htmlspecialchars($resident_data['last_name']) ?>">
          <input type="hidden" name="vehicle_qty" value="1">
          <?php endif; ?>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <?php if ($resident_data): ?>
          <button type="submit" class="btn btn-success" id="submitVehicleReservation">
            <i class="fas fa-paper-plane me-1"></i>Submit Reservation
          </button>
          <?php else: ?>
          <button type="button" class="btn btn-success" disabled>
            <i class="fas fa-lock me-1"></i>Login Required
          </button>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>