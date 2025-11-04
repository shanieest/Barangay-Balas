// Helper: get progress info
function getProgressBar(status) {
    switch (status) {
        case 'Pending': return { width: '25%', class: 'bg-warning text-dark', text: 'Pending', message: 'Your request is pending approval.' };
        case 'Approved': return { width: '50%', class: 'bg-info', text: 'Approved', message: 'Your request has been approved.' };
        case 'Processing': return { width: '75%', class: 'bg-primary', text: 'Processing', message: 'Your request is being processed.' };
        case 'Released':
        case 'Completed': return { width: '100%', class: 'bg-success', text: 'Completed', message: 'Your request has been released/completed.' };
        case 'Cancelled':
        case 'Rejected':
        case 'Disapproved': return { width: '100%', class: 'bg-danger', text: status, message: 'This request was cancelled or rejected.' };
        default: return { width: '0%', class: 'bg-secondary', text: status, message: 'Status unknown.' };
    }
}

// View document modal
document.querySelectorAll('.view-document').forEach(button => {
    button.addEventListener('click', function() {
        const name = this.getAttribute('data-name');
        const status = this.getAttribute('data-status');
        const progress = getProgressBar(status);

        document.getElementById('documentDetails').innerHTML = `
            <div class="text-center">
                <i class="fas fa-file-alt fa-3x text-info mb-3"></i>
                <h5>${name}</h5>
                <p>${progress.message}</p>
                <div class="mt-3">
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated ${progress.class}" 
                             style="width: ${progress.width}; line-height: 25px;">
                            ${progress.text}
                        </div>
                    </div>
                </div>
                ${status === 'Released' || status === 'Approved' ? `
                <div class="alert alert-info mt-3">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        Downloaded copies include a watermark. For official hard copies, visit the Barangay Office.
                    </small>
                </div>
                ` : ''}
            </div>
        `;
        new bootstrap.Modal(document.getElementById('viewDocumentModal')).show();
    });
});

// View reservation modal
document.querySelectorAll('.view-reservation').forEach(button => {
    button.addEventListener('click', function() {
        const services = this.getAttribute('data-name').split(',').map(s => s.trim());
        const status = this.getAttribute('data-status');
        const date = this.getAttribute('data-reservation-date');
        const duration = this.getAttribute('data-duration');
        const purpose = this.getAttribute('data-purpose');
        const progress = getProgressBar(status);

        document.getElementById('reservationDetails').innerHTML = `
            <div class="text-center">
                <i class="fas fa-calendar-check fa-3x text-info mb-3"></i>
                <h5>Reservation Details</h5>
                <div class="text-start">
                    <p><strong>Services Requested:</strong></p>
                    <ul class="list-unstyled ps-3">
                        ${services.map(s => `<li>• ${s}</li>`).join('')}
                    </ul>
                    <p><strong>Reservation Date:</strong> ${new Date(date).toLocaleDateString()}</p>
                    <p><strong>Duration:</strong> ${duration} day(s)</p>
                    <p><strong>Purpose:</strong> ${purpose}</p>
                </div>
                <p class="mt-3">${progress.message}</p>
                <div class="mt-3">
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated ${progress.class}" 
                             style="width: ${progress.width}; line-height: 25px;">
                            ${progress.text}
                        </div>
                    </div>
                </div>
            </div>
        `;
        new bootstrap.Modal(document.getElementById('viewReservationModal')).show();
    });
});

// View Barangay ID modal
document.querySelectorAll('.view-barangay-id').forEach(button => {
    button.addEventListener('click', function() {
        const idNumber = this.getAttribute('data-id-number');
        const status = this.getAttribute('data-status');
        const date = this.getAttribute('data-date');
        const validUntil = this.getAttribute('data-valid-until');
        const progress = getProgressBar(status);

        const modalContent = `
            <div class="text-center">
                <i class="fas fa-id-card fa-3x text-info mb-3"></i>
                <h5>Barangay ID Application</h5>
                <div class="text-start mt-3">
                    <p><strong>ID Number:</strong> ${idNumber}</p>
                    <p><strong>Application Date:</strong> ${date}</p>
                    <p><strong>Valid Until:</strong> ${validUntil}</p>
                </div>
                <p class="mt-3">${progress.message}</p>
                <div class="mt-3">
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated ${progress.class}" 
                             style="width: ${progress.width}; line-height: 25px;">
                            ${progress.text}
                        </div>
                    </div>
                </div>
                ${status === 'Approved' ? `
                <div class="alert alert-success mt-3">
                    <small>
                        <i class="fas fa-check-circle me-1"></i>
                        Your Barangay ID has been approved! You can download it using the button above.
                    </small>
                </div>
                ` : ''}
            </div>
        `;

        // Check if modal exists, if not create it
        let modal = document.getElementById('viewBarangayIdModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'viewBarangayIdModal';
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Barangay ID Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="barangayIdDetails"></div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        document.getElementById('barangayIdDetails').innerHTML = modalContent;
        new bootstrap.Modal(modal).show();
    });
});

// View Medicine Request modal
document.querySelectorAll('.view-medicine').forEach(button => {
    button.addEventListener('click', function() {
        const requestNumber = this.getAttribute('data-request-number');
        const medicine = this.getAttribute('data-medicine');
        const condition = this.getAttribute('data-condition');
        const urgency = this.getAttribute('data-urgency');
        const status = this.getAttribute('data-status');
        const date = this.getAttribute('data-date');
        const notes = this.getAttribute('data-notes');
        const reason = this.getAttribute('data-reason');
        const progress = getProgressBar(status);

        const urgencyBadge = urgency === 'emergency' || urgency === 'high' ? 'danger' : 
                           urgency === 'medium' ? 'warning' : 'secondary';

        const modalContent = `
            <div class="text-center">
                <i class="fas fa-pills fa-3x text-info mb-3"></i>
                <h5>Medicine Request Details</h5>
                <div class="text-start mt-3">
                    <p><strong>Request Number:</strong> ${requestNumber}</p>
                    <p><strong>Medicine:</strong> ${medicine}</p>
                    <p><strong>Medical Condition:</strong> ${condition}</p>
                    <p><strong>Urgency Level:</strong> <span class="badge bg-${urgencyBadge}">${urgency.toUpperCase()}</span></p>
                    <p><strong>Request Date:</strong> ${date}</p>
                    ${notes ? `<p><strong>Admin Notes:</strong> ${notes}</p>` : ''}
                    ${reason ? `<p><strong>Disapproval Reason:</strong> <span class="text-danger">${reason}</span></p>` : ''}
                </div>
                <p class="mt-3">${progress.message}</p>
                <div class="mt-3">
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated ${progress.class}" 
                             style="width: ${progress.width}; line-height: 25px;">
                            ${progress.text}
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Check if modal exists, if not create it
        let modal = document.getElementById('viewMedicineModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'viewMedicineModal';
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Medicine Request Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="medicineDetails"></div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        document.getElementById('medicineDetails').innerHTML = modalContent;
        new bootstrap.Modal(modal).show();
    });
});

// Cancel request/reservation
document.querySelectorAll('.cancel-request, .cancel-reservation').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const type = this.classList.contains('cancel-request') ? 'document' : 'service';
        document.getElementById('cancelRequestId').value = id;
        document.getElementById('cancelRequestType').value = type;
        new bootstrap.Modal(document.getElementById('cancelConfirmModal')).show();
    });
});

// Confirm cancellation
document.getElementById('confirmCancel')?.addEventListener('click', function() {
    const requestId = document.getElementById('cancelRequestId').value;
    const type = document.getElementById('cancelRequestType').value;

    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('type', type);

    fetch('services-history-backend.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Request cancelled successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => { 
        console.error('Cancellation error:', err); 
        alert('Error cancelling request. Please try again.'); 
    });
});

// Status filtering
document.getElementById('documentStatusFilter')?.addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const rows = document.querySelectorAll('#documents tbody tr');
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (status === 'all' || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

document.getElementById('serviceStatusFilter')?.addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const rows = document.querySelectorAll('#reservations tbody tr');
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (status === 'all' || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

document.getElementById('barangayIdStatusFilter')?.addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const rows = document.querySelectorAll('#barangay-id tbody tr');
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (status === 'all' || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

document.getElementById('medicineStatusFilter')?.addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const rows = document.querySelectorAll('#medicine tbody tr');
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (status === 'all' || rowStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Download notice for documents
document.addEventListener('click', function(e) {
    const link = e.target.closest('a[href*="/barangay-balas/services/download-document.php"]');
    if (link) {
        e.preventDefault();
        const downloadUrl = link.href;

        // Check if modal exists
        let modal = document.getElementById('downloadNoticeModal');
        if (modal) {
            // Set the download URL for the proceed button
            const proceedBtn = document.getElementById('proceedDownload');
            proceedBtn.href = downloadUrl;

            // Show the notice modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            // Ensure clicking proceed closes modal before navigation
            proceedBtn.onclick = function(ev) {
                ev.preventDefault();
                bsModal.hide();
                setTimeout(() => {
                    window.location.href = downloadUrl;
                }, 400);
            };
        } else {
            // If no modal, just navigate
            window.location.href = downloadUrl;
        }
    }
});

// Tab persistence
document.addEventListener('DOMContentLoaded', function() {
    // Remember active tab
    const activeTab = localStorage.getItem('activeServiceHistoryTab');
    if (activeTab) {
        const tab = document.querySelector(`[data-bs-target="${activeTab}"]`);
        if (tab) {
            new bootstrap.Tab(tab).show();
        }
    }

    // Save active tab on change
    const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEls.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            localStorage.setItem('activeServiceHistoryTab', e.target.getAttribute('data-bs-target'));
        });
    });
});