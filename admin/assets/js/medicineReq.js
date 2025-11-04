
document.addEventListener('DOMContentLoaded', function() {
    initializeMedicineModals();
    initializeRequestModals();
    initializeFormValidations();
    initializeDeleteHandlers();
    initializeStatusUpdateHandlers();
    initializeViewRequestHandlers();
    initializeSearchFunctionality();
    initializeExportButtons();
});

function initializeMedicineModals() {
    // Edit Medicine Modal
    const editMedicineModal = document.getElementById('editMedicineModal');
    if (editMedicineModal) {
        editMedicineModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const modal = this;
            
            modal.querySelector('#edit_medicine_id').value = button.getAttribute('data-id');
            modal.querySelector('#edit_medicine_name').value = button.getAttribute('data-name');
            modal.querySelector('#edit_category').value = button.getAttribute('data-category');
            modal.querySelector('#edit_description').value = button.getAttribute('data-description');
            modal.querySelector('#edit_stock_quantity').value = button.getAttribute('data-stock');
            modal.querySelector('#edit_minimum_stock').value = button.getAttribute('data-min-stock');
            modal.querySelector('#edit_unit').value = button.getAttribute('data-unit');
            modal.querySelector('#edit_is_active').checked = button.getAttribute('data-active') === '1';
        });
    }
}

function initializeRequestModals() {
    // Update Status Modal
    const updateStatusModal = document.getElementById('updateStatusModal');
    if (updateStatusModal) {
        updateStatusModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const status = button.getAttribute('data-status');
            const modal = this;
            
            modal.querySelector('#status_request_id').value = button.getAttribute('data-id');
            modal.querySelector('#status_value').value = status;
            modal.querySelector('#statusModalTitle').textContent = `${status} Request`;
            
            const reasonField = modal.querySelector('#disapproval_reason_field');
            const reasonTextarea = reasonField.querySelector('textarea');
            
            if (status === 'Disapproved') {
                reasonField.style.display = 'block';
                reasonTextarea.required = true;
            } else {
                reasonField.style.display = 'none';
                reasonTextarea.required = false;
                reasonTextarea.value = ''; 
            }
        });
    }
}

function initializeStatusUpdateHandlers() {
    // Handle approve/disapprove buttons
    const statusButtons = document.querySelectorAll('.update-status-btn');
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            const status = this.getAttribute('data-status');
            
            // Set the values in the update status modal
            document.getElementById('status_request_id').value = requestId;
            document.getElementById('status_value').value = status;
            
            // Update modal title and show/hide disapproval reason field
            const modalTitle = document.getElementById('statusModalTitle');
            const reasonField = document.getElementById('disapproval_reason_field');
            const reasonTextarea = document.querySelector('textarea[name="disapproval_reason"]');
            
            modalTitle.textContent = `${status} Request`;
            
            if (status === 'Disapproved') {
                reasonField.style.display = 'block';
                reasonTextarea.required = true;
            } else {
                reasonField.style.display = 'none';
                reasonTextarea.required = false;
                reasonTextarea.value = ''; 
            }
        });
    });
}

function initializeViewRequestHandlers() {
    // Handle view request buttons
    const viewButtons = document.querySelectorAll('.view-request-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const requestData = this.getAttribute('data-request');
            if (requestData) {
                try {
                    const request = JSON.parse(requestData);
                    populateViewRequestModal(request);
                } catch (e) {
                    console.error('Error parsing request data:', e);
                }
            }
        });
    });
}

function populateViewRequestModal(request) {
    const modalBody = document.getElementById('requestDetails');
    
    const details = `
        <div class="row">
            <div class="col-md-6">
                <h6>Request Information</h6>
                <p><strong>Request ID:</strong> ${escapeHtml(request.request_number)}</p>
                <p><strong>Medicine:</strong> ${escapeHtml(request.medicine_name)}</p>
                <p><strong>Medical Condition:</strong> ${escapeHtml(request.medical_condition)}</p>
                <p><strong>Urgency Level:</strong> <span class="badge bg-${getUrgencyBadgeColor(request.urgency_level)}">${escapeHtml(request.urgency_level)}</span></p>
            </div>
            <div class="col-md-6">
                <h6>Resident Information</h6>
                <p><strong>Name:</strong> ${escapeHtml(request.first_name)} ${escapeHtml(request.last_name)}</p>
                <p><strong>Email:</strong> ${escapeHtml(request.email)}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Additional Information</h6>
                <p><strong>Additional Notes:</strong> ${escapeHtml(request.additional_notes || 'None')}</p>
                <p><strong>Prescription:</strong> ${request.prescription_path ? '<a href="../' + escapeHtml(request.prescription_path) + '" target="_blank">View Prescription</a>' : 'None'}</p>
                ${request.admin_notes ? `<p><strong>Admin Notes:</strong> ${escapeHtml(request.admin_notes)}</p>` : ''}
                ${request.disapproval_reason ? `<p><strong>Disapproval Reason:</strong> ${escapeHtml(request.disapproval_reason)}</p>` : ''}
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Status Information</h6>
                <p><strong>Status:</strong> <span class="badge ${getStatusBadgeClass(request.status)}">${escapeHtml(request.status)}</span></p>
                <p><strong>Date Requested:</strong> ${formatDateTime(request.date_requested)}</p>
                ${request.date_processed ? `<p><strong>Date Processed:</strong> ${formatDateTime(request.date_processed)}</p>` : ''}
            </div>
        </div>
    `;
    
    modalBody.innerHTML = details;
}

function initializeFormValidations() {
    // Form validation for stock quantities
    const stockQuantityInputs = document.querySelectorAll('input[name="stock_quantity"], input[name="minimum_stock"]');
    stockQuantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    });
}

function initializeDeleteHandlers() {
    // Delete medicine buttons
    const deleteButtons = document.querySelectorAll('.delete-medicine-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const medicineId = this.getAttribute('data-id');
            const medicineName = this.getAttribute('data-name');
            
            // Set data for the delete modal
            document.getElementById('delete_medicine_id').value = medicineId;
            document.getElementById('delete_medicine_name').textContent = medicineName;
            
            // Show the delete confirmation modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteMedicineModal'));
            deleteModal.show();
        });
    });
}

function initializeSearchFunctionality() {
    // Medicine search
    const searchMedicine = document.getElementById('searchMedicine');
    if (searchMedicine) {
        searchMedicine.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#inventory tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Request search
    const searchRequest = document.getElementById('searchRequest');
    if (searchRequest) {
        searchRequest.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#requests tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
}

function initializeExportButtons() {
    // Export Inventory Button
    const exportInventoryBtn = document.getElementById('exportInventoryBtn');
    if (exportInventoryBtn) {
        exportInventoryBtn.addEventListener('click', function() {
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Exporting...';
            this.disabled = true;
            
            // Trigger download
            window.location.href = '../backend/export-medicine.php?type=inventory';
            
            // Reset button after a delay
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 2000);
        });
    }
    
    // Export Requests Button
    const exportRequestsBtn = document.getElementById('exportRequestsBtn');
    if (exportRequestsBtn) {
        exportRequestsBtn.addEventListener('click', function() {
            // Get current status filter
            const urlParams = new URLSearchParams(window.location.search);
            const statusFilter = urlParams.get('status') || 'all';
            
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Exporting...';
            this.disabled = true;
            
            // Trigger download with status filter
            window.location.href = `../backend/export-medicine.php?type=requests&status=${statusFilter}`;
            
            // Reset button after a delay
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 2000);
        });
    }
}

function getUrgencyBadgeColor(urgency) {
    switch(urgency) {
        case 'emergency': return 'danger';
        case 'high': return 'warning';
        case 'medium': return 'info';
        case 'low': return 'secondary';
        default: return 'secondary';
    }
}

function getStatusBadgeClass(status) {
    switch(status.toLowerCase()) {
        case 'pending': return 'bg-warning';
        case 'approved': return 'bg-success';
        case 'disapproved': return 'bg-danger';
        case 'completed': return 'bg-info';
        default: return 'bg-secondary';
    }
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return 'N/A';
    const date = new Date(dateTimeString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}