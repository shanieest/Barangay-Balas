<div class="modal fade" id="vehicleReservationModal" tabindex="-1" aria-labelledby="vehicleReservationLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="backend/vehicle_reservation.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="vehicleReservationLabel">Reserve Service Vehicle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Date Needed</label>
          <input type="date" name="reservation_date" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Time Needed</label>
          <input type="time" name="reservation_time" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Purpose</label>
          <textarea name="purpose" class="form-control" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Reserve</button>
      </div>
    </form>
  </div>
</div>
