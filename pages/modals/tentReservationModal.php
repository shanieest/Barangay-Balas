<div class="modal fade" id="tentReservationModal" tabindex="-1" aria-labelledby="tentReservationLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="backend/tent_reservation.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tentReservationLabel">Reserve Tent</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Date Needed</label>
          <input type="date" name="reservation_date" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Duration</label>
          <input type="text" name="duration" class="form-control" placeholder="e.g. 2 days" required>
        </div>
        <div class="mb-3">
          <label>Purpose</label>
          <textarea name="purpose" class="form-control" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning">Reserve</button>
      </div>
    </form>
  </div>
</div>
