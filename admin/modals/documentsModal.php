<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewRequestModalLabel">Request Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Request Information</h6>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Request ID:</strong></div>
                            <div class="col-sm-8"><span id="viewRequestId"></span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Document Type:</strong></div>
                            <div class="col-sm-8"><span id="viewDocumentType"></span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Date Requested:</strong></div>
                            <div class="col-sm-8"><span id="viewDateRequested"></span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Status:</strong></div>
                            <div class="col-sm-8"><span class="badge" id="viewStatusBadge"></span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Resident Information</h6>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Name:</strong></div>
                            <div class="col-sm-8"><span id="viewResidentName"></span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Address:</strong></div>
                            <div class="col-sm-8"><span id="viewResidentAddress"></span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Contact:</strong></div>
                            <div class="col-sm-8"><span id="viewResidentContact"></span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Resident Account:</strong></div>
                            <div class="col-sm-8">
                                <span id="viewResidentEmail"></span>
                                <span class="badge ms-2" id="viewAccountStatus"></span>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4"><strong>Processed By:</strong></div>
                            <div class="col-sm-8"><span id="viewProcessedBy"></span></div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6>Purpose</h6>
                    <p id="viewPurpose"></p>
                </div>

                <div class="mb-4">
                    <h6>Admin Notes</h6>
                    <p id="viewNotes"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="download-document.php" class="btn btn-primary" id="downloadDocumentBtn" style="display:none;">
                    <i class="fas fa-download me-1"></i> Download Document
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Approve Request Modal -->
<div class="modal fade" id="approveRequestModal" tabindex="-1" aria-labelledby="approveRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="approveForm" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="approveRequestModalLabel">Approve Request</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="request_id" id="approveRequestId">
        <input type="hidden" name="action" value="approve">

        <div class="mb-3">
          <label for="approveNotes" class="form-label">Notes (optional)</label>
          <textarea name="notes" id="approveNotes" class="form-control" rows="3" placeholder="Add any additional notes..."></textarea>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="auto_download" id="autoDownload" value="1">
          <label class="form-check-label" for="autoDownload">
            Auto-download document after approval
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">
          <i class="fas fa-check me-1"></i> Confirm Approve
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Disapprove Request Modal -->
<div class="modal fade" id="disapproveRequestModal" tabindex="-1" aria-labelledby="disapproveRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="disapproveForm" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="disapproveRequestModalLabel">Disapprove Request</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="request_id" id="disapproveRequestId">
        <input type="hidden" name="action" value="disapprove">

        <div class="mb-3">
          <label for="disapproveNotes" class="form-label">Reason for Disapproval <span class="text-danger">*</span></label>
          <textarea name="notes" id="disapproveNotes" class="form-control" rows="3" required 
                    placeholder="Please provide a reason for disapproving this request..."></textarea>
          <div class="form-text">This reason will be visible to the resident.</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">
          <i class="fas fa-times me-1"></i> Confirm Disapprove
        </button>
      </div>
    </form>
  </div>
</div>

<script src="assets/js/services.js"></script>