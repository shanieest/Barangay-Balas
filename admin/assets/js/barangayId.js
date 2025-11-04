let currentApplicationId = null;

// View application details with preview
function viewApplicationDetails(id) {
    currentApplicationId = id;
    
    document.getElementById('applicationDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading application preview...</p>
        </div>
    `;
    
    fetch(`../backend/get_application_details.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const resident = data.resident;
                const application = data.application;
                
                const getCorrectImagePath = (dbPath) => {
                    if (!dbPath) return '';
                    let cleanPath = dbPath.replace(/^uploads\//, '');
                    const correctPath = `../../pages/uploads/${cleanPath}`;
                    return correctPath;
                };
                
                const photoPath = getCorrectImagePath(application.photo_path);
                const signaturePath = getCorrectImagePath(application.signature_path);
                
                document.getElementById('applicationDetailsContent').innerHTML = `
                    <div class="row">
                        <!-- ID Preview Card -->
                        <div class="col-md-12 mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-id-card me-2"></i>Barangay ID Preview</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            ${photoPath ? `
                                                <div class="photo-container">
                                                    <img src="${photoPath}" 
                                                         alt="ID Photo" 
                                                         class="img-thumbnail mb-2" 
                                                         style="max-width: 150px; max-height: 150px; object-fit: cover;"
                                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPlBob3RvIE5vdCBGb3VuZDwvdGV4dD48L3N2Zz4=';">
                                                    <p class="small text-muted">ID Photo</p>
                                                </div>
                                            ` : '<p class="text-muted">No photo uploaded</p>'}
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="mb-3">${resident.first_name} ${resident.middle_name || ''} ${resident.last_name} ${resident.suffix || ''}</h5>
                                            <p class="mb-2"><strong>ID Number:</strong> ${application.id_number || '<span class="text-muted">Will be generated upon approval</span>'}</p>
                                            <p class="mb-2"><strong>Date of Birth:</strong> ${resident.birthdate}</p>
                                            <p class="mb-2"><strong>Address:</strong> ${resident.address}</p>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            ${signaturePath ? `
                                                <div class="signature-container">
                                                    <img src="${signaturePath}" 
                                                         alt="Signature" 
                                                         class="img-thumbnail mb-2" 
                                                         style="max-width: 150px; max-height: 75px; background: white; object-fit: contain;"
                                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9Ijc1IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9IiNmZmYiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+U2lnbmF0dXJlIE5vdCBGb3VuZDwvdGV4dD48L3N2Zz4=';">
                                                    <p class="small text-muted">Signature</p>
                                                </div>
                                            ` : '<p class="text-muted">No signature</p>'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Contact Information</h6>
                            <p><strong>Contact Number:</strong> ${resident.contact_number || 'Not provided'}</p>
                            <p><strong>Place of Birth:</strong> ${resident.place_of_birth || 'Not specified'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Application Details</h6>
                            <p><strong>Application Date:</strong> ${application.application_date}</p>
                            <p><strong>Status:</strong> <span class="badge bg-${getStatusBadgeClass(application.status)}">${application.status}</span></p>
                            ${application.notes ? `<p><strong>Notes:</strong> ${application.notes}</p>` : ''}
                        </div>
                    </div>
                    
                    ${application.status === 'Rejected' && application.reject_reason ? `
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-ban me-2"></i>Rejection Details</h6>
                                <p class="mb-1"><strong>Reason:</strong> ${application.reject_reason}</p>
                                ${application.updated_at ? `<p class="mb-0"><strong>Rejected On:</strong> ${application.updated_at}</p>` : ''}
                            </div>
                        </div>
                    </div>
                    ` : ''}
                `;
                
                const approveBtn = document.getElementById('approveFromModal');
                if (approveBtn) {
                    approveBtn.onclick = function() {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('applicationDetailsModal'));
                        modal.hide();
                        approveApplication(id);
                    };
                    
                    if (application.status !== 'Pending') {
                        approveBtn.style.display = 'none';
                    } else {
                        approveBtn.style.display = 'inline-block';
                    }
                }
            } else {
                document.getElementById('applicationDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Error loading application details: ${data.message || 'Unknown error'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('applicationDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Error loading application details. Please try again.
                </div>
            `;
        });
    
    const modal = new bootstrap.Modal(document.getElementById('applicationDetailsModal'));
    modal.show();
}

// PDF Path Construction
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-id')) {
            const btn = e.target.closest('.view-id');
            const dbPath = btn.dataset.path;
            
            console.log('=== PDF VIEWER - CRITICAL FIX ===');
            console.log('Database path:', dbPath);
            
            if (!dbPath) {
                showToast('Error: PDF path not found in database', 'error');
                return;
            }
            
            const getCorrectPDFPath = (dbPath) => {
                let cleanPath = dbPath.replace(/^\.\.\//, '').replace(/^\.\//, '').replace(/^\//, '');
                
                const correctPath = `../../${cleanPath}`;
                
                console.log('Clean path:', cleanPath);
                console.log('Corrected path:', correctPath);
                return correctPath;
            };
            
            const pdfPath = getCorrectPDFPath(dbPath);
            
            console.log('Final PDF path:', pdfPath);
            console.log('================================');
            
            // Show modal with working PDF embed
            const modalContent = `
                <div class="pdf-viewer-container">
                    <div class="text-center mb-3">
                        <div class="btn-group" role="group">
                            <button class="btn btn-primary" onclick="openPDFInNewTab('${pdfPath}')">
                                <i class="fas fa-external-link-alt me-2"></i>Open in New Tab
                            </button>
                            <a href="${pdfPath}" download class="btn btn-success">
                                <i class="fas fa-download me-2"></i>Download PDF
                            </a>
                        </div>
                    </div>
                    
                    <!-- PDF Embed with fallback -->
                    <div class="pdf-embed-wrapper" style="height: 600px; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;">
                        <iframe 
                            src="${pdfPath}#toolbar=1&navpanes=0&scrollbar=1" 
                            width="100%" 
                            height="100%" 
                            style="border: none;"
                            onload="console.log('PDF loaded successfully')"
                            onerror="handlePDFLoadError('${pdfPath}')">
                        </iframe>
                    </div>
                
                </div>
            `;
            
            document.getElementById('pdfViewerContent').innerHTML = modalContent;
            
            const modal = new bootstrap.Modal(document.getElementById('viewIdModal'));
            modal.show();
        }
    });
});

// Handle PDF load errors
function handlePDFLoadError(pdfPath) {
    console.error('PDF failed to load:', pdfPath);
    
    document.getElementById('pdfViewerContent').innerHTML = `
        <div class="text-center py-5">
            <i class="fas fa-file-pdf text-danger" style="font-size: 4rem;"></i>
            <h4 class="mt-3 mb-3">Unable to Display PDF</h4>
            <p class="text-muted mb-4">The PDF viewer couldn't load the file. Try these options:</p>
            
            <div class="row g-3 justify-content-center">
                <div class="col-md-4">
                    <button class="btn btn-primary w-100 p-3" onclick="openPDFInNewTab('${pdfPath}')">
                        <i class="fas fa-external-link-alt fa-2x mb-2"></i><br>
                        <strong>Open in New Tab</strong>
                    </button>
                </div>
                <div class="col-md-4">
                    <a href="${pdfPath}" download class="btn btn-success w-100 p-3">
                        <i class="fas fa-download fa-2x mb-2"></i><br>
                        <strong>Download PDF</strong>
                    </a>
                </div>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded">
                <small class="text-muted">
                    <strong>Troubleshooting:</strong><br>
                    1. Check if the file exists at: ${pdfPath}<br>
                    2. Try downloading the file instead<br>
                    3. Open in a new tab for better compatibility
                </small>
            </div>
        </div>
    `;
}

// Open PDF in new tab
function openPDFInNewTab(pdfPath) {
    console.log('Opening PDF in new tab:', pdfPath);
    window.open(pdfPath, '_blank', 'noopener,noreferrer');
}

// Download current PDF
function downloadCurrentPDF() {
    const downloadLink = document.querySelector('#pdfViewerContent a[download]');
    if (downloadLink) {
        downloadLink.click();
    } else {
        showToast('No download link available', 'error');
    }
}

// Get badge class based on status
function getStatusBadgeClass(status) {
    switch (status) {
        case 'Approved': return 'success';
        case 'Pending': return 'warning text-dark';
        case 'Rejected': return 'danger';
        default: return 'secondary';
    }
}

// Approve application
function approveApplication(id) {
    if (!id) {
        console.error('No application ID provided');
        showToast('Error: No application ID provided', 'error');
        return;
    }

    if (confirm('Are you sure you want to approve this application and generate Barangay ID?\n\nThis action cannot be undone.')) {
        showLoadingOverlay('Generating Barangay ID...', 'Please wait while we create the digital ID');
        window.location.href = '../backend/generate_barangay_id.php?id=' + id;
    }
}

// Show reject modal
function showRejectModal(id) {
    if (!id) {
        console.error('No application ID provided');
        return;
    }
    
    document.getElementById('rejectAppId').value = id;
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectReason').classList.remove('is-invalid');
    
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
    
    modal._element.addEventListener('shown.bs.modal', function() {
        document.getElementById('rejectReason').focus();
    });
}

// Reject application
function rejectApplication() {
    const appId = document.getElementById('rejectAppId').value;
    const reason = document.getElementById('rejectReason').value.trim();
    
    if (!appId) {
        showToast('Error: No application ID provided', 'error');
        return;
    }
    
    if (!reason) {
        document.getElementById('rejectReason').classList.add('is-invalid');
        return;
    }
    
    if (reason.length < 10) {
        document.getElementById('rejectReason').classList.add('is-invalid');
        showToast('Please provide a more detailed reason (minimum 10 characters)', 'error');
        return;
    }
    
    document.getElementById('rejectReason').classList.remove('is-invalid');
    
    const rejectBtn = document.querySelector('#rejectModal .btn-danger');
    const originalText = rejectBtn.innerHTML;
    rejectBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    rejectBtn.disabled = true;
    
    const formData = new FormData();
    formData.append('application_id', appId);
    formData.append('reject_reason', reason);
    
    fetch('../backend/reject_barangay_id.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('Application rejected successfully!', 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
            modal.hide();
            setTimeout(() => {
                window.location.href = '../backend/barangay_id_records.php?success=rejected';
            }, 1000);
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error rejecting application: ' + error.message, 'error');
        rejectBtn.innerHTML = originalText;
        rejectBtn.disabled = false;
    });
}

// View notes
function viewNotes(notes) {
    document.getElementById('notesContent').textContent = notes || 'No notes available.';
    new bootstrap.Modal(document.getElementById('viewNotesModal')).show();
}

// View rejection details
function viewRejectionDetails(id) {
    if (!id) {
        console.error('No application ID provided');
        return;
    }
    
    fetch(`../backend/get_application_details.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.application.reject_reason) {
                document.getElementById('rejectionDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-ban me-2"></i>Rejection Details</h6>
                        <hr>
                        <p><strong>Reason:</strong> ${data.application.reject_reason}</p>
                        <p><strong>Rejected On:</strong> ${data.application.updated_at || 'Not specified'}</p>
                    </div>
                `;
            } else {
                document.getElementById('rejectionDetailsContent').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>No rejection details available.
                    </div>
                `;
            }
            new bootstrap.Modal(document.getElementById('rejectionDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('rejectionDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Error loading rejection details.
                </div>
            `;
            new bootstrap.Modal(document.getElementById('rejectionDetailsModal')).show();
        });
}

// Export functions
function performExport() {
    const exportType = document.querySelector('input[name="exportType"]:checked').value;
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
    }
    
    const exportUrl = `../backend/generate_barangay_id.php?action=export_excel&status=${exportType}&t=${new Date().getTime()}`;
    
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = exportUrl;
    
    iframe.onload = function() {
        setTimeout(() => {
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
            document.body.removeChild(iframe);
            showToast('Export completed successfully!', 'success');
        }, 1000);
    };
    
    iframe.onerror = function() {
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
        document.body.removeChild(iframe);
        showToast('Export failed. Please try again.', 'error');
    };
    
    document.body.appendChild(iframe);
    
    const exportModal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
    if (exportModal) {
        exportModal.hide();
    }
}

// Utility functions
function showLoadingOverlay(title = 'Processing...', message = 'Please wait') {
    let overlay = document.getElementById('loadingOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-content text-center text-white">
                <div class="spinner-border mb-3" style="width: 3rem; height: 3rem;"></div>
                <h5>${title}</h5>
                <p class="text-light">${message}</p>
            </div>
        `;
        document.body.appendChild(overlay);
        
        if (!document.querySelector('#loadingOverlayStyles')) {
            const styles = document.createElement('style');
            styles.id = 'loadingOverlayStyles';
            styles.textContent = `
                .loading-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.8);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                    backdrop-filter: blur(5px);
                }
                .loading-content {
                    background: rgba(0, 0, 0, 0.9);
                    padding: 2rem;
                    border-radius: 10px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                }
            `;
            document.head.appendChild(styles);
        }
    }
    overlay.style.display = 'flex';
}

function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type === 'error' ? 'danger' : type} border-0`;
    toast.id = toastId;
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${getToastIcon(type)} me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function getToastIcon(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-triangle';
        case 'warning': return 'exclamation-triangle';
        default: return 'info-circle';
    }
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Barangay ID initialized.');
});