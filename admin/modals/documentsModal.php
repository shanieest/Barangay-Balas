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
                <a href="#" class="btn btn-primary" id="downloadDocumentBtn" style="display:none;">
                    <i class="fas fa-download me-1"></i> Download Document
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Approve Request Modal -->
<div class="modal fade" id="approveRequestModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="approveForm" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Approve Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="request_id" id="approveRequestId">
        <input type="hidden" name="action" value="approve">

        <div class="mb-3">
          <label class="form-label">Notes (optional)</label>
          <textarea name="notes" id="approveNotes" class="form-control"></textarea>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="auto_download" id="autoDownload" value="1">
          <label class="form-check-label" for="autoDownload">Auto-download document after approval</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Confirm Approve</button>
      </div>
    </form>
  </div>
</div>

<!-- Disapprove Request Modal -->
<div class="modal fade" id="disapproveRequestModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="disapproveForm" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Disapprove Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="request_id" id="disapproveRequestId">
        <input type="hidden" name="action" value="disapprove">

        <div class="mb-3">
          <label class="form-label">Reason for Disapproval</label>
          <textarea name="notes" id="disapproveNotes" class="form-control" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Confirm Disapprove</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==============================
    // View Request Modal
    // ==============================
    const viewRequestModal = document.getElementById('viewRequestModal');
    if (viewRequestModal) {
        viewRequestModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-id');

            fetch(`get-request-details.php?id=${requestId}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('viewRequestId').textContent = data.id;
                    document.getElementById('viewDocumentType').textContent = data.document_type;
                    document.getElementById('viewDateRequested').textContent = data.date_requested;
                    document.getElementById('viewResidentName').textContent = data.full_name;
                    document.getElementById('viewResidentAddress').textContent = `${data.address}, ${data.purok}`;
                    document.getElementById('viewResidentContact').textContent = data.contact_number || 'N/A';
                    document.getElementById('viewResidentEmail').textContent = data.resident_email || 'N/A';
                    const accountStatus = document.getElementById('viewAccountStatus');
                    accountStatus.textContent = data.account_status || 'N/A';
                    accountStatus.className = 'badge ms-2 bg-' + 
                        (data.account_status == 'Approved' ? 'success' : 
                         (data.account_status == 'Pending' ? 'warning' : 'danger'));
                    document.getElementById('viewProcessedBy').textContent = data.processed_by;
                    document.getElementById('viewPurpose').textContent = data.purpose;
                    document.getElementById('viewNotes').textContent = data.notes || 'No notes provided';

                    // Status badge and download button
                    const statusBadge = document.getElementById('viewStatusBadge');
                    const downloadBtn = document.getElementById('downloadDocumentBtn');
                    statusBadge.textContent = data.status;
                    if (data.status === 'Approved') {
                        statusBadge.className = 'badge bg-success';
                        if (data.document_path) {
                            downloadBtn.style.display = 'inline-block';
                            downloadBtn.href = `download-document.php?id=${requestId}`;
                        } else downloadBtn.style.display = 'none';
                    } else if (data.status === 'Disapproved') {
                        statusBadge.className = 'badge bg-danger';
                        downloadBtn.style.display = 'none';
                    } else {
                        statusBadge.className = 'badge bg-warning';
                        downloadBtn.style.display = 'none';
                    }
                })
                .catch(err => console.error('Error fetching request details:', err));
        });
    }

    // ==============================
    // Approve Modal
    // ==============================
    const approveModal = document.getElementById('approveRequestModal');
    const approveForm = document.getElementById('approveForm');

    if (approveModal) {
        approveModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('approveRequestId').value = button.getAttribute('data-id');
        });
    }

    if (approveForm) {
        approveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(approveForm);

            fetch('process_request.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("✅ " + data.message);

                    // Auto-download PDF
                    if (data.auto_download && data.file_path) {
                        const link = document.createElement('a');
                        link.href = data.file_path;
                        link.download = data.file_path.split('/').pop();
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                    }

                    // Update "Download Document" button
                    const downloadBtn = document.getElementById('downloadDocumentBtn');
                    if (data.file_path) {
                        downloadBtn.href = `download-document.php?id=${document.getElementById('approveRequestId').value}`;
                        downloadBtn.style.display = 'inline-block';
                    }

                    bootstrap.Modal.getInstance(approveModal).hide();
                } else alert("❌ Error: " + data.message);
            })
            .catch(err => {
                console.error('Approve error:', err);
                alert("❌ Something went wrong while approving. Check console.");
            });
        });
    }

    // ==============================
    // Disapprove Modal
    // ==============================
    const disapproveModal = document.getElementById('disapproveRequestModal');
    const disapproveForm = document.getElementById('disapproveForm');

    if (disapproveModal) {
        disapproveModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('disapproveRequestId').value = button.getAttribute('data-id');
        });
    }

    if (disapproveForm) {
        disapproveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(disapproveForm);

            fetch('process_request.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("🚫 Request disapproved!");
                    location.reload();
                } else alert("❌ Error: " + data.message);
            })
            .catch(err => {
                console.error('Disapprove error:', err);
                alert("❌ Something went wrong while disapproving. Check console.");
            });
        });
    }
});
</script>
