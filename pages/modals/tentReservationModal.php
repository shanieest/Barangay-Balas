<div class="modal fade" id="tentReservationModal" tabindex="-1" aria-labelledby="tentReservationLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tentReservationLabel">Reserve Tent</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="tentReservationForm">
        <div class="modal-body">
          <div id="tentReservationMessage" class="alert d-none"></div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>First Name</label>
              <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label>Last Name</label>
              <input type="text" name="last_name" class="form-control" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Contact Number</label>
              <input type="text" name="contact_number" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label>Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <label>Date Needed</label>
            <input type="date" name="reservation_date" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
          </div>
          <div class="mb-3">
            <label>Duration</label>
            <input type="text" name="duration" class="form-control" placeholder="e.g. 2 days" required>
          </div>
          <div class="mb-3">
            <label>Purpose</label>
            <textarea name="purpose" class="form-control" required></textarea>
          </div>
          <input type="hidden" name="action" value="reserve_tent">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Reserve</button>
        </div>
      </form>
    </div>
  </div>
</div>