

//modal
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

