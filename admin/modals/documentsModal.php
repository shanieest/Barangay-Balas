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
                            <div class="col-sm-4">
                                <p class="mb-0"><strong>Request ID:</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0" id="viewRequestId"></p>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <p class="mb-0"><strong>Document Type:</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0" id="viewDocumentType"></p>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <p class="mb-0"><strong>Date Requested:</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0" id="viewDateRequested"></p>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <p class="mb-0"><strong>Status:</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <span class="badge" id="viewStatusBadge"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Resident Information</h6>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <p class="mb-0"><strong>Name:</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0" id="viewResidentName"></p>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <p class="mb-0"><strong>Address:</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0" id="viewResidentAddress"></p>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <p class="mb-0"><strong>Contact:</strong></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0" id="viewResidentContact"></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6>Purpose</h6>
                    <p class="text-muted" id="viewPurpose"></p>
                </div>
                
                <div class="mb-4">
                    <h6>Admin Notes</h6>
                    <p class="text-muted" id="viewNotes"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="downloadDocumentBtn" style="display:none;">
                    <i class="fas fa-download me-1"></i> Download Document
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Approve Request Modal -->
<div class="modal fade" id="approveRequestModal" tabindex="-1" aria-labelledby="approveRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveRequestModalLabel">Approve Document Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveForm" action="document-approve.php" method="GET">
                <div class="modal-body">
                    <input type="hidden" name="id" id="approveRequestId">
                    <input type="hidden" name="action" value="approve">
                    <p>You are about to approve this document request:</p>
                    <p><strong>Request ID:</strong> <span id="approveRequestIdDisplay"></span></p>
                    <p><strong>Resident:</strong> <span id="approveResidentName"></span></p>
                    <p><strong>Document Type:</strong> <span id="approveDocumentType"></span></p>
                    
                    <div class="mb-3">
                        <label for="approvalNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="approvalNotes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Disapprove Request Modal -->
<div class="modal fade" id="disapproveRequestModal" tabindex="-1" aria-labelledby="disapproveRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="disapproveRequestModalLabel">Disapprove Document Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="disapproveForm" action="document-approve.php" method="GET">
                <div class="modal-body">
                    <input type="hidden" name="id" id="disapproveRequestId">
                    <input type="hidden" name="action" value="disapprove">
                    <p>You are about to disapprove this document request:</p>
                    <p><strong>Request ID:</strong> <span id="disapproveRequestIdDisplay"></span></p>
                    <p><strong>Resident:</strong> <span id="disapproveResidentName"></span></p>
                    <p><strong>Document Type:</strong> <span id="disapproveDocumentType"></span></p>
                    
                    <div class="mb-3">
                        <label for="disapprovalReason" class="form-label">Reason for Disapproval *</label>
                        <select class="form-select" id="disapprovalReason" name="notes" required>
                            <option value="">Select a reason...</option>
                            <option value="Incomplete requirements">Incomplete requirements</option>
                            <option value="Invalid information">Invalid information</option>
                            <option value="Unverified resident">Unverified resident</option>
                            <option value="Other">Other (please specify)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="otherReasonContainer" style="display: none;">
                        <label for="otherReason" class="form-label">Specify Reason</label>
                        <textarea class="form-control" id="otherReason" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Disapprove Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript to handle modal interactions
document.addEventListener('DOMContentLoaded', function() {
    // View Request Modal
    const viewRequestModal = document.getElementById('viewRequestModal');
    if (viewRequestModal) {
        viewRequestModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-id');
            
            // Fetch request details via AJAX
            fetch(`get-request-details.php?id=${requestId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('viewRequestId').textContent = data.id;
                    document.getElementById('viewDocumentType').textContent = data.document_type;
                    document.getElementById('viewDateRequested').textContent = data.date_requested;
                    document.getElementById('viewResidentName').textContent = data.full_name;
                    document.getElementById('viewResidentAddress').textContent = `${data.address}, ${data.purok}`;
                    document.getElementById('viewResidentContact').textContent = data.contact_number || 'N/A';
                    document.getElementById('viewPurpose').textContent = data.purpose;
                    document.getElementById('viewNotes').textContent = data.notes || 'No notes provided';
                    
                    // Set status badge
                    const statusBadge = document.getElementById('viewStatusBadge');
                    statusBadge.textContent = data.status;
                    if (data.status === 'Approved') {
                        statusBadge.className = 'badge bg-success';
                        const downloadBtn = document.getElementById('downloadDocumentBtn');
                        if (data.document_path) {
                            downloadBtn.style.display = 'inline-block';
                            downloadBtn.href = `download-document.php?id=${requestId}`;
                        }
                    } else if (data.status === 'Disapproved') {
                        statusBadge.className = 'badge bg-danger';
                    } else {
                        statusBadge.className = 'badge bg-warning';
                    }
                });
        });
    }
    
    // Approve Request Modal
    const approveRequestModal = document.getElementById('approveRequestModal');
    if (approveRequestModal) {
        approveRequestModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-id');
            
            // Fetch basic request info
            fetch(`get-request-basic.php?id=${requestId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('approveRequestId').value = requestId;
                    document.getElementById('approveRequestIdDisplay').textContent = requestId;
                    document.getElementById('approveResidentName').textContent = data.full_name;
                    document.getElementById('approveDocumentType').textContent = data.document_type;
                });
        });
    }
    
    // Disapprove Request Modal
    const disapproveRequestModal = document.getElementById('disapproveRequestModal');
    if (disapproveRequestModal) {
        disapproveRequestModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-id');
            
            // Fetch basic request info
            fetch(`document-info.php?id=${requestId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('disapproveRequestId').value = requestId;
                    document.getElementById('disapproveRequestIdDisplay').textContent = requestId;
                    document.getElementById('disapproveResidentName').textContent = data.full_name;
                    document.getElementById('disapproveDocumentType').textContent = data.document_type;
                });
        });
        
        // Handle other reason selection
        document.getElementById('disapprovalReason').addEventListener('change', function() {
            const otherReasonContainer = document.getElementById('otherReasonContainer');
            if (this.value === 'Other') {
                otherReasonContainer.style.display = 'block';
            } else {
                otherReasonContainer.style.display = 'none';
            }
        });
    }
});
</script>