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
          
          <!-- Personal Information -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" name="last_name" class="form-control" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Contact Number <span class="text-danger">*</span></label>
              <input type="text" name="contact_number" class="form-control" 
                     pattern="[0-9]{11}" placeholder="09XXXXXXXXX" required>
              <div class="form-text">Enter 11-digit mobile number</div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-control" 
                     placeholder="your.email@example.com">
            </div>
          </div>

          <!-- Reservation Details -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Start Date <span class="text-danger">*</span></label>
              <input type="date" name="reservation_date_start" class="form-control" 
                     min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">End Date</label>
              <input type="date" name="reservation_date_end" class="form-control" 
                     min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
              <div class="form-text">Leave empty for single day reservation</div>
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

          <input type="hidden" name="action" value="create_reservation">
          <input type="hidden" name="service_types[]" value="2"> <!-- Vehicle service type ID -->
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="submitVehicleReservation">
            <i class="fas fa-paper-plane me-1"></i>Submit Reservation
          </button>
        </div>
      </form>
    </div>
  </div>
</div>