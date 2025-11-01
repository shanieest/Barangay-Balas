document.addEventListener('DOMContentLoaded', function() {
    initializeAdminPanel();
});

function initializeAdminPanel() {
    // Initialize all admin functionalities
    initializeDocumentRequests();
    initializeServiceReservations();
    initializeReportGeneration();
    initializeAdminDownloadSystem();
    initializeSearchAndFilters();
    initializeBulkActions();
}

// Document Requests Management
function initializeDocumentRequests() {
    const viewRequestModal = document.getElementById('viewRequestModal');
    if (viewRequestModal) {
        viewRequestModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-id');
            loadRequestDetails(requestId);
        });
    }

    const approveModal = document.getElementById('approveRequestModal');
    const approveForm = document.getElementById('approveForm');

    if (approveModal) {
        approveModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-id');
            document.getElementById('approveRequestId').value = requestId;
            
            // Set auto-download preference
            const autoDownloadCheckbox = document.getElementById('autoDownload');
            if (autoDownloadCheckbox) {
                autoDownloadCheckbox.checked = getAdminPreference('autoDownload', true);
            }
        });
    }

    if (approveForm) {
        approveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            approveDocumentRequest();
        });
    }

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
            disapproveDocumentRequest();
        });
    }
}

function loadRequestDetails(requestId) {
    showLoading('viewRequestModal');
    
    fetch(`get-request-details.php?id=${requestId}`)
        .then(res => res.json())
        .then(data => {
            console.log('Request Details:', data);
            
            // Populate modal with data
            document.getElementById('viewRequestId').textContent = data.id;
            document.getElementById('viewDocumentType').textContent = data.document_type;
            document.getElementById('viewDateRequested').textContent = formatDate(data.date_requested);
            document.getElementById('viewResidentName').textContent = data.full_name;
            document.getElementById('viewResidentAddress').textContent = `${data.houseno ? 'House #' + data.houseno + ', ' : ''}${data.purok ? 'Purok ' + data.purok : ''}`;   
            document.getElementById('viewResidentContact').textContent = data.contact_number || 'N/A';
            document.getElementById('viewResidentEmail').textContent = data.resident_email || 'N/A';
            
            const accountStatus = document.getElementById('viewAccountStatus');
            accountStatus.textContent = data.account_status || 'N/A';
            accountStatus.className = 'badge ms-2 bg-' + 
                (data.account_status == 'Approved' ? 'success' : 
                 (data.account_status == 'Pending' ? 'warning' : 'secondary'));
            
            const processedByElement = document.getElementById('viewProcessedBy');
            if (processedByElement) {
                processedByElement.textContent = data.processed_by || 'Not processed yet';
            }
            
            document.getElementById('viewPurpose').textContent = data.purpose;
            document.getElementById('viewNotes').textContent = data.notes || 'No notes provided';

            const statusBadge = document.getElementById('viewStatusBadge');
            const downloadBtn = document.getElementById('downloadDocumentBtn');
            statusBadge.textContent = data.status;
            
            if (data.status === 'Approved') {
                statusBadge.className = 'badge bg-success';
                if (data.document_path) {
                    downloadBtn.style.display = 'inline-block';
                    // Admin download - no watermark
                    downloadBtn.href = `/barangay-balas/services/download-document.php?id=${requestId}&admin=true`;
                    downloadBtn.setAttribute('data-filename', data.document_type + '_' + requestId + '.pdf');
                } else {
                    downloadBtn.style.display = 'none';
                }
            } else if (data.status === 'Disapproved') {
                statusBadge.className = 'badge bg-danger';
                downloadBtn.style.display = 'none';
            } else {
                statusBadge.className = 'badge bg-warning';
                downloadBtn.style.display = 'none';
            }
            
            hideLoading('viewRequestModal');
        })
        .catch(err => {
            console.error('Error fetching request details:', err);
            showAlert('Error loading request details', 'danger');
            hideLoading('viewRequestModal');
        });
}

function approveDocumentRequest() {
    const formData = new FormData(document.getElementById('approveForm'));
    const requestId = document.getElementById('approveRequestId').value;
    const autoDownload = document.getElementById('autoDownload')?.checked || false;

    formData.append('auto_download', autoDownload ? '1' : '0');
    
    showLoading('approveRequestModal');
    
    fetch('process_request.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('✅ ' + data.message, 'success');
            
            // Auto-download if enabled and file is generated
            if (data.auto_download && data.file_path) {
                setTimeout(() => {
                    downloadAdminDocument(requestId, data.document_type || 'document');
                }, 1000);
            }

            // Close modal and refresh
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('approveRequestModal')).hide();
                location.reload();
            }, 1500);
        } else {
            showAlert('❌ Error: ' + data.message, 'danger');
            hideLoading('approveRequestModal');
        }
    })
    .catch(err => {
        console.error('Approve error:', err);
        showAlert('❌ Something went wrong while approving', 'danger');
        hideLoading('approveRequestModal');
    });
}

function disapproveDocumentRequest() {
    const formData = new FormData(document.getElementById('disapproveForm'));
    
    showLoading('disapproveRequestModal');
    
    fetch('process_request.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('✅ Request disapproved successfully', 'success');
            
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('disapproveRequestModal')).hide();
                location.reload();
            }, 1000);
        } else {
            showAlert('❌ Error: ' + data.message, 'danger');
            hideLoading('disapproveRequestModal');
        }
    })
    .catch(err => {
        console.error('Disapprove error:', err);
        showAlert('❌ Something went wrong while disapproving', 'danger');
        hideLoading('disapproveRequestModal');
    });
}

// Service Reservations Management
function initializeServiceReservations() {
    const viewServiceModal = document.getElementById('viewServiceModal');
    if (viewServiceModal) {
        viewServiceModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const reservationId = button.getAttribute('data-id');
            loadServiceReservationDetails(reservationId);
        });
    }

    // Service status update handlers
    document.querySelectorAll('.update-service-status').forEach(button => {
        button.addEventListener('click', function() {
            const reservationId = this.getAttribute('data-id');
            const newStatus = this.getAttribute('data-status');
            updateServiceStatus(reservationId, newStatus);
        });
    });
}

function loadServiceReservationDetails(reservationId) {
    showLoading('viewServiceModal');
    
    fetch(`get-service-details.php?id=${reservationId}`)
        .then(res => res.json())
        .then(data => {
            // Populate service reservation details
            // ... existing service details code ...
            
            hideLoading('viewServiceModal');
        })
        .catch(err => {
            console.error('Error fetching service details:', err);
            showAlert('Error loading service details', 'danger');
            hideLoading('viewServiceModal');
        });
}

function updateServiceStatus(reservationId, newStatus) {
    if (!confirm(`Are you sure you want to mark this reservation as ${newStatus}?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('reservation_id', reservationId);
    formData.append('status', newStatus);
    formData.append('action', 'update_service_status');

    fetch('process_request.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert(`✅ Reservation marked as ${newStatus}`, 'success');
            location.reload();
        } else {
            showAlert('❌ Error: ' + data.message, 'danger');
        }
    })
    .catch(err => {
        console.error('Service status update error:', err);
        showAlert('❌ Something went wrong while updating status', 'danger');
    });
}

// Report Generation
function initializeReportGeneration() {
    const monthlyReportForm = document.getElementById('monthlyReportForm');
    const yearlyReportForm = document.getElementById('yearlyReportForm');
    
    // Set current year and month as default
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
    
    if (document.getElementById('year_monthly')) {
        document.getElementById('year_monthly').value = currentYear;
    }
    if (document.getElementById('month')) {
        document.getElementById('month').value = currentMonth;
    }
    if (document.getElementById('year_yearly')) {
        document.getElementById('year_yearly').value = currentYear;
    }
    
    if (monthlyReportForm) {
        monthlyReportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            generateReport('monthly');
        });
    }
    
    if (yearlyReportForm) {
        yearlyReportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            generateReport('yearly');
        });
    }
}

function generateReport(reportType) {
    const form = reportType === 'monthly' ? document.getElementById('monthlyReportForm') : document.getElementById('yearlyReportForm');
    const formData = new FormData(form);
    const button = form.querySelector('button[type="submit"]');
    const originalText = button.innerHTML;
    
    // Add loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating Report...';
    button.disabled = true;
    
    fetch('process_request.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Download the generated file
            const downloadLink = document.createElement('a');
            downloadLink.href = data.filepath;
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
            
            showAlert('Report generated successfully! Your download should start shortly.', 'success');
        } else {
            showAlert('Error generating report: ' + data.message, 'danger');
        }
    })
    .catch(err => {
        console.error('Report generation error:', err);
        showAlert('Something went wrong while generating the report.', 'danger');
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Admin Download System with Watermark Control
function initializeAdminDownloadSystem() {
    // Handle all admin download links
    document.addEventListener('click', function(e) {
        const downloadLink = e.target.closest('a[href*="barangay-balas/services/download-document.php"]');
        if (downloadLink && !downloadLink.href.includes('admin=true')) {
            e.preventDefault();
            handleAdminDownload(downloadLink);
        }
    });

    // Add admin download notice
    addAdminDownloadNotice();
}

function handleAdminDownload(link) {
    const url = new URL(link.href);
    const requestId = url.searchParams.get('id');
    
    // Add admin parameter for clean download (no watermark)
    const adminUrl = link.href + (link.href.includes('?') ? '&' : '?') + 'admin=true';
    
    // Trigger download
    const downloadLink = document.createElement('a');
    downloadLink.href = adminUrl;
    downloadLink.target = '_blank';
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
    
    // Log admin download activity
    logAdminActivity('document_download', { requestId: requestId, adminDownload: true });
}

function downloadAdminDocument(requestId, documentType) {
    const downloadUrl = `/barangay-balas/services/download-document.php?id=${requestId}&admin=true`;
    const filename = `${documentType}_${requestId}.pdf`;
    
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = filename;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function addAdminDownloadNotice() {
    // Show admin download info only once per session
    if (!sessionStorage.getItem('adminDownloadNoticeShown')) {
        setTimeout(() => {
            showAlert(
                '<i class="fas fa-info-circle me-2"></i><strong>Admin Note:</strong> Downloaded documents are clean copies without watermarks. Residents receive watermarked copies for authenticity.',
                'info',
                8000
            );
            sessionStorage.setItem('adminDownloadNoticeShown', 'true');
        }, 2000);
    }
}

// Search and Filter Functionality
function initializeSearchAndFilters() {
    const searchInput = document.getElementById('searchRequests');
    const filterSelect = document.getElementById('filterStatus');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            filterTable('requestsTable', searchInput.value, filterSelect?.value);
        }, 300));
    }
    
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            filterTable('requestsTable', searchInput?.value, filterSelect.value);
        });
    }
}

function filterTable(tableId, searchTerm, statusFilter) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    const searchLower = searchTerm.toLowerCase();
    
    rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        const rowStatus = row.getAttribute('data-status') || row.querySelector('.badge')?.textContent.toLowerCase() || '';
        
        const matchesSearch = !searchTerm || rowText.includes(searchLower);
        const matchesStatus = !statusFilter || statusFilter === 'all' || rowStatus.includes(statusFilter.toLowerCase());
        
        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

// Bulk Actions
function initializeBulkActions() {
    const bulkActionSelect = document.getElementById('bulkAction');
    const applyBulkActionBtn = document.getElementById('applyBulkAction');
    
    if (bulkActionSelect && applyBulkActionBtn) {
        applyBulkActionBtn.addEventListener('click', function() {
            const selectedRows = document.querySelectorAll('table tbody tr.table-active');
            const action = bulkActionSelect.value;
            
            if (selectedRows.length === 0) {
                showAlert('Please select at least one request to perform bulk action.', 'warning');
                return;
            }
            
            if (action === '') {
                showAlert('Please select an action to perform.', 'warning');
                return;
            }
            
            const requestIds = Array.from(selectedRows).map(row => {
                return row.querySelector('button[data-id]')?.getAttribute('data-id');
            }).filter(id => id);
            
            performBulkAction(requestIds, action);
        });
    }
    
    // Row selection for bulk actions
    document.addEventListener('click', function(e) {
        if (e.target.closest('table tbody tr') && !e.target.closest('button') && !e.target.closest('a')) {
            const row = e.target.closest('tr');
            row.classList.toggle('table-active');
            updateBulkActionUI();
        }
    });
}

function updateBulkActionUI() {
    const selectedCount = document.querySelectorAll('table tbody tr.table-active').length;
    const bulkActionSection = document.querySelector('.bulk-actions');
    
    if (bulkActionSection) {
        const counter = bulkActionSection.querySelector('.selected-count') || 
                       document.createElement('span');
        counter.className = 'selected-count badge bg-primary ms-2';
        counter.textContent = selectedCount + ' selected';
        
        if (!bulkActionSection.querySelector('.selected-count')) {
            bulkActionSection.appendChild(counter);
        }
    }
}

function performBulkAction(requestIds, action) {
    if (!confirm(`Are you sure you want to ${action} ${requestIds.length} selected request(s)?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'bulk_' + action);
    formData.append('request_ids', JSON.stringify(requestIds));
    
    fetch('process_request.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert(`✅ ${data.message}`, 'success');
            location.reload();
        } else {
            showAlert(`❌ Error: ${data.message}`, 'danger');
        }
    })
    .catch(err => {
        console.error('Bulk action error:', err);
        showAlert('❌ Something went wrong while performing bulk action.', 'danger');
    });
}

// Utility Functions
function showAlert(message, type = 'info', duration = 5000) {
    // Remove existing alerts
    const existingAlert = document.querySelector('.alert-dismissible');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-dismiss after duration
    if (duration > 0) {
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, duration);
    }
}

function showLoading(modalId) {
    const modal = document.getElementById(modalId);
    const loadingElement = modal.querySelector('.loading-spinner') || createLoadingSpinner();
    modal.querySelector('.modal-content').appendChild(loadingElement);
}

function hideLoading(modalId) {
    const modal = document.getElementById(modalId);
    const loadingElement = modal.querySelector('.loading-spinner');
    if (loadingElement) {
        loadingElement.remove();
    }
}

function createLoadingSpinner() {
    const spinner = document.createElement('div');
    spinner.className = 'loading-spinner position-absolute top-50 start-50 translate-middle';
    spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
    spinner.style.zIndex = '9999';
    return spinner;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getAdminPreference(key, defaultValue) {
    return localStorage.getItem(`admin_${key}`) || defaultValue;
}

function setAdminPreference(key, value) {
    localStorage.setItem(`admin_${key}`, value);
}

function logAdminActivity(action, data = {}) {
    // Log admin activities for audit trail
    console.log('Admin Activity:', action, data);
    
    // You can send this to your backend for logging
    fetch('log-admin-activity.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, data, timestamp: new Date().toISOString() })
    }).catch(err => console.error('Activity logging failed:', err));
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Quick Actions
function quickApprove(requestId) {
    if (confirm('Are you sure you want to approve this request?')) {
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('action', 'approve');
        formData.append('notes', 'Quick approval');
        formData.append('auto_download', '0');
        
        fetch('process_request.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('✅ Request approved successfully', 'success');
                location.reload();
            } else {
                showAlert('❌ Error: ' + data.message, 'danger');
            }
        })
        .catch(err => {
            console.error('Quick approve error:', err);
            showAlert('❌ Something went wrong while approving', 'danger');
        });
    }
}

function quickDisapprove(requestId) {
    if (confirm('Are you sure you want to disapprove this request?')) {
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('action', 'disapprove');
        formData.append('notes', 'Quick disapproval');
        
        fetch('process_request.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('✅ Request disapproved successfully', 'success');
                location.reload();
            } else {
                showAlert('❌ Error: ' + data.message, 'danger');
            }
        })
        .catch(err => {
            console.error('Quick disapprove error:', err);
            showAlert('❌ Something went wrong while disapproving', 'danger');
        });
    }
}

// Print functionality
function printRequestDetails(requestId) {
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    
    fetch(`get-request-details.php?id=${requestId}`)
        .then(res => res.json())
        .then(data => {
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Request Details - ${requestId}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @media print { 
                            .no-print { display: none; } 
                            body { margin: 20px; font-size: 14px; }
                        }
                        .header { border-bottom: 2px solid #333; margin-bottom: 20px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="header text-center">
                            <h2>Barangay Balas - Request Details</h2>
                            <p class="text-muted">Request ID: ${requestId}</p>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Document Type:</strong> ${data.document_type || 'N/A'}</div>
                            <div class="col-6"><strong>Status:</strong> ${data.status || 'N/A'}</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6"><strong>Resident Name:</strong> ${data.full_name || 'N/A'}</div>
                            <div class="col-6"><strong>Date Requested:</strong> ${data.date_requested || 'N/A'}</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12"><strong>Purpose:</strong> ${data.purpose || 'N/A'}</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12"><strong>Notes:</strong> ${data.notes || 'No notes provided'}</div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 text-muted">
                                <small>Printed on: ${new Date().toLocaleString()}</small>
                            </div>
                        </div>
                    </div>
                    <div class="no-print text-center mt-4">
                        <button onclick="window.print()" class="btn btn-primary me-2">Print</button>
                        <button onclick="window.close()" class="btn btn-secondary">Close</button>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
        })
        .catch(err => {
            printWindow.document.write('<p>Error loading request details.</p>');
            printWindow.document.close();
        });
}

// Export data functionality
function exportTableData(tableId, filename = 'export') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Skip action columns
            if (cols[j].querySelector('button') || cols[j].querySelector('a')) {
                continue;
            }
            row.push(cols[j].innerText);
        }
        
        csv.push(row.join(','));
    }
    
    // Download CSV file
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = `${filename}_${new Date().toISOString().split('T')[0]}.csv`;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initializeAdminPanel);