<!-- Service Reservation Modals -->

<!-- View Service Reservation Modal -->
<div class="modal fade" id="viewServiceModal" tabindex="-1" aria-labelledby="viewServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewServiceModalLabel">View Service Reservation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="fw-bold">Reservation Details</h6>
            <p><strong>Reservation ID:</strong> <span id="view-reservation-id"></span></p>
            <p><strong>Resident:</strong> <span id="view-resident-name"></span></p>
            <p><strong>Service Type:</strong> <span id="view-service-type"></span></p>
            <p><strong>Reservation Date:</strong> <span id="view-reservation-date"></span></p>
            <p><strong>Duration:</strong> <span id="view-duration"></span></p>
            <p><strong>Status:</strong> <span id="view-status"></span></p>
          </div>
          <div class="col-md-6">
            <h6 class="fw-bold">Additional Information</h6>
            <p><strong>Purpose:</strong> <span id="view-purpose"></span></p>
            
            <!-- Setup Time Section -->
            <div id="setup-time-section" style="display: none;">
              <p><strong>Setup Time:</strong> <span id="view-setup-time"></span></p>
            </div>
            
            <!-- Duration Type Section -->
            <div id="duration-type-section" style="display: none;">
              <p><strong>Duration Type:</strong> <span id="view-duration-type"></span></p>
            </div>
            
            <!-- Event Location Section -->
            <div id="event-location-section" style="display: none;">
              <p><strong>Event Location:</strong> <span id="view-event-location"></span></p>
            </div>
            
            <p><strong>Contact Number:</strong> <span id="view-contact"></span></p>
            <p><strong>Email:</strong> <span id="view-email"></span></p>
            <p><strong>Date Requested:</strong> <span id="view-date-requested"></span></p>
            
            <div id="scheduled-datetime-section" style="display: none;">
              <p><strong>Scheduled Date/Time:</strong> <span id="view-scheduled-datetime"></span></p>
            </div>
            <div id="processed-by-section" style="display: none;">
              <p><strong>Processed By:</strong> <span id="view-processed-by"></span></p>
            </div>
            <div id="rejection-reason-section" style="display: none;">
              <p><strong>Disapproval Reason:</strong> <span id="view-rejection-reason"></span></p>
            </div>
            <p><strong>Admin Notes:</strong> <span id="view-notes"></span></p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Approve Service Reservation Modal -->
<div class="modal fade" id="approveServiceModal" tabindex="-1" aria-labelledby="approveServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="approveServiceModalLabel">Approve Service Reservation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="approveServiceForm" method="POST" action="reservation-backend.php">
        <div class="modal-body">
          <input type="hidden" id="approve-service-id" name="reservation_id">
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Are you sure you want to approve this service reservation?
          </div>
          <div class="mb-3">
            <label for="approve-notes" class="form-label">Approval Notes (Optional)</label>
            <textarea class="form-control" id="approve-notes" name="notes" rows="3" placeholder="Enter any notes for the approval..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-check"></i> Approve Reservation
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Disapprove Service Reservation Modal -->
<div class="modal fade" id="rejectServiceModal" tabindex="-1" aria-labelledby="rejectServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rejectServiceModalLabel">Disapprove Service Reservation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="rejectServiceForm" method="POST" action="reservation-backend.php">
        <div class="modal-body">
          <input type="hidden" id="reject-service-id" name="reservation_id">
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Are you sure you want to disapprove this service reservation?
          </div>
          <div class="mb-3">
            <label for="reject-reason" class="form-label">Reason for Disapproval <span class="text-danger">*</span></label>
            <textarea class="form-control" id="reject-reason" name="rejection_reason" rows="3" placeholder="Please provide a reason for disapproval..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-times"></i> Disapprove Reservation
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Update Service Status Modal -->
<div class="modal fade" id="updateServiceModal" tabindex="-1" aria-labelledby="updateServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateServiceModalLabel">Update Service Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="updateServiceForm" method="POST" action="reservation-backend.php">
        <div class="modal-body">
          <input type="hidden" id="update-service-id" name="reservation_id">
          <div class="mb-3">
            <label for="update-status" class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-control" id="update-status" name="status" required>
              <option value="">Select Status</option>
              <option value="In Progress">In Progress</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="update-notes" class="form-label">Notes</label>
            <textarea class="form-control" id="update-notes" name="notes" rows="3" placeholder="Enter any additional notes..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Update Status
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Date Details Modal -->
<div class="modal fade" id="dateDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dateDetailsTitle">Service Availability</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="dateDetailsContent">
                    Loading service availability...
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto">
                    <i class="fas fa-circle text-danger me-1"></i>Reserved Services 
                    <i class="fas fa-circle text-warning ms-2 me-1"></i>Services in Used 
                    <i class="fas fa-circle text-success ms-2 me-1"></i>Available
                </small>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
    
</div>
