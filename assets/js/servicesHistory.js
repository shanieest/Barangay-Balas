// Helper: get progress info
function getProgressBar(status) {
    switch (status) {
        case 'Pending': return { width: '25%', class: 'bg-warning text-dark', text: 'Pending', message: 'Your request is pending approval.' };
        case 'Approved': return { width: '50%', class: 'bg-info', text: 'Approved', message: 'Your request has been approved.' };
        case 'Processing': return { width: '75%', class: 'bg-primary', text: 'Processing', message: 'Your request is being processed.' };
        case 'Released':
        case 'Completed': return { width: '100%', class: 'bg-success', text: 'Completed', message: 'Your request has been released/completed.' };
        case 'Cancelled':
        case 'Rejected': return { width: '100%', class: 'bg-danger', text: status, message: 'This request was cancelled or rejected.' };
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
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated ${progress.class}" style="width: ${progress.width}">${progress.text}</div>
                    </div>
                </div>
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
        const progress = getProgressBar(status);

        document.getElementById('reservationDetails').innerHTML = `
            <div class="text-center">
                <i class="fas fa-calendar-check fa-3x text-info mb-3"></i>
                <h5>Reservation Details</h5>
                <p><strong>Services:</strong></p>
                <ul class="list-unstyled">
                    ${services.map(s => `<li>• ${s}</li>`).join('')}
                </ul>
                <p><strong>Reservation Date:</strong> ${date}</p>
                <p><strong>Duration:</strong> ${duration} day(s)</p>
                <p>${progress.message}</p>
                <div class="mt-3">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated ${progress.class}" style="width: ${progress.width}">${progress.text}</div>
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

document.getElementById('confirmCancel').addEventListener('click', function() {
    const formData = new FormData();
    formData.append('request_id', document.getElementById('cancelRequestId').value);
    formData.append('type', document.getElementById('cancelRequestType').value);

    fetch('services-history-backend.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { alert('Request cancelled successfully!'); location.reload(); }
            else alert('Error: ' + data.message);
        })
        .catch(err => { console.error(err); alert('Error cancelling request.'); });
});

document.getElementById('documentStatusFilter').addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const rows = this.closest('.card').querySelectorAll('tbody tr');
    rows.forEach(r => r.style.display = (status === 'all' || r.getAttribute('data-status') === status) ? '' : 'none');
});
document.getElementById('serviceStatusFilter').addEventListener('change', function() {
    const status = this.value.toLowerCase();
    const rows = this.closest('.card').querySelectorAll('tbody tr');
    rows.forEach(r => r.style.display = (status === 'all' || r.getAttribute('data-status') === status) ? '' : 'none');
});
