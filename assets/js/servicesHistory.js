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
document.getElementById('confirmCancel').addEventListener('click', function() {
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
document.getElementById('documentStatusFilter').addEventListener('change', function() {
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

document.getElementById('serviceStatusFilter').addEventListener('change', function() {
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

// Download notice for documents
document.addEventListener('click', function(e) {
    const link = e.target.closest('a[href*="/barangay-balas/services/download-document.php"]');
    if (link) {
        e.preventDefault();
        const downloadUrl = link.href;

        // Set the download URL for the proceed button
        const proceedBtn = document.getElementById('proceedDownload');
        proceedBtn.href = downloadUrl;

        // Show the notice modal
        const modal = new bootstrap.Modal(document.getElementById('downloadNoticeModal'));
        modal.show();

        // Ensure clicking proceed closes modal before navigation
        proceedBtn.onclick = function(ev) {
            ev.preventDefault();
            modal.hide();
            setTimeout(() => {
                window.location.href = downloadUrl;
            }, 400); // small delay for modal close animation
        };
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