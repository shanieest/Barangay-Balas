<?php
// tentReservationModal.php
?>
<div class="modal fade" id="tentReservationModal" tabindex="-1" aria-labelledby="tentReservationLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tentReservationLabel">
          <i class="fas fa-campground me-2"></i>Reserve Barangay Tent
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="tentReservationForm">
        <div class="modal-body">
          <div id="tentReservationMessage" class="alert d-none"></div>
          
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
              <label class="form-label">Setup Time</label>
              <input type="time" name="setup_time" class="form-control" value="08:00">
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

          <!-- Service Selection -->
          <div class="mb-3">
            <label class="form-label">Additional Services</label>
            <div class="row">
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="service_types[]" value="1" id="tentService" checked>
                  <label class="form-check-label" for="tentService">
                    <i class="fas fa-campground text-warning me-1"></i>Tent
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="service_types[]" value="4" id="tablesChairs">
                  <label class="form-check-label" for="tablesChairs">
                    <i class="fas fa-chair text-success me-1"></i>Tables and Chairs
                  </label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="service_types[]" value="3" id="soundSystem">
                  <label class="form-check-label" for="soundSystem">
                    <i class="fas fa-volume-up text-info me-1"></i>Sound System
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Event Location</label>
            <input type="text" name="event_location" class="form-control" 
                   placeholder="Where will the tent be set up?">
          </div>

          <div class="mb-3">
            <label class="form-label">Purpose/Event Type <span class="text-danger">*</span></label>
            <textarea name="purpose" class="form-control" rows="3" 
                      placeholder="Please describe your event (e.g., birthday party, wedding, meeting)..." required></textarea>
          </div>

          <input type="hidden" name="action" value="create_reservation">
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning" id="submitTentReservation">
            <i class="fas fa-paper-plane me-1"></i>Submit Reservation
          </button>
        </div>
      </form>
    </div>
  </div>
</div>