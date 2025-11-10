<!-- Application Details Modal -->
<div class="modal fade" id="applicationDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i>Application Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="applicationDetailsContent">
          <!-- Content will be loaded via AJAX -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="approveFromModal">
          <i class="fas fa-check me-1"></i>Approve Application
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Digital Barangay ID Preview Modal - UPDATED -->
<div class="modal fade" id="viewIdModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-id-card me-2"></i>Digital Barangay ID</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="pdfViewerContent">
          <!-- Content will be loaded dynamically -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="downloadCurrentFile()">
          <i class="fas fa-download me-2"></i>Download File
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Edit ID Number Modal -->
<div class="modal fade" id="editIdModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit ID Number</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editIdForm">
          <input type="hidden" id="editAppId" name="application_id">
          <div class="mb-3">
            <label for="newIdNumber" class="form-label">ID Number</label>
            <input type="text" class="form-control" id="newIdNumber" name="id_number" required 
                   placeholder="e.g., BALAS-2024-0001" pattern="^BALAS-\d{4}-\d{4}$">
            <small class="text-muted">Format: BALAS-YYYY-XXXX</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="saveIdNumber()">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Reject Application Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject Application</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="rejectForm">
          <input type="hidden" id="rejectAppId" name="application_id">
          <div class="mb-3">
            <label for="rejectReason" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
            <textarea class="form-control" id="rejectReason" name="reject_reason" rows="4" 
                      placeholder="Please provide the reason for rejecting this application..." required></textarea>
            <small class="text-muted">This reason will be visible to the resident.</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="rejectApplication()">Confirm Rejection</button>
      </div>
    </div>
  </div>
</div>

<!-- View Notes Modal -->
<div class="modal fade" id="viewNotesModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-sticky-note me-2"></i>Application Notes</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="notesContent"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Rejection Details Modal -->
<div class="modal fade" id="rejectionDetailsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="fas fa-ban me-2"></i>Rejection Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="rejectionDetailsContent"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Export Modal for Yearly Reports -->
<div class="modal fade" id="exportModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-chart-line me-2"></i>Generate Yearly Report</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="exportYear" class="form-label">Select Year <span class="text-danger">*</span></label>
          <select class="form-select" id="exportYear" name="year" required>
            <option value="">-- Choose Year --</option>
            <?php 
            $currentYear = date('Y');
            for ($y = $currentYear; $y >= 2020; $y--) {
                $selected = $y == $currentYear ? 'selected' : '';
                echo "<option value='$y' $selected>$y</option>";
            }
            ?>
          </select>
        </div>
      </div>
    </div>
  </div>
</div>