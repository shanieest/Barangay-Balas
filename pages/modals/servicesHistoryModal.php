<!-- Document Details Modal -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDocumentModalLabel">Document Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="documentDetails">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reservation Details Modal -->
<div class="modal fade" id="viewReservationModal" tabindex="-1" aria-labelledby="viewReservationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewReservationModalLabel">Service Reservation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="reservationDetails">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Barangay ID Details Modal -->
<div class="modal fade" id="viewBarangayIdModal" tabindex="-1" aria-labelledby="viewBarangayIdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewBarangayIdModalLabel">
                    <i class="fas fa-id-card me-2"></i>Barangay ID Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="barangayIdDetails">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Medicine Request Details Modal -->
<div class="modal fade" id="viewMedicineModal" tabindex="-1" aria-labelledby="viewMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMedicineModalLabel">
                    <i class="fas fa-pills me-2"></i>Medicine Request Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="medicineDetails">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-labelledby="cancelConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>Confirm Cancellation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <p class="mb-0"><strong>Are you sure you want to cancel this request?</strong></p>
                    <small>This action cannot be undone.</small>
                </div>
                <input type="hidden" id="cancelRequestId">
                <input type="hidden" id="cancelRequestType">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>No, Keep It
                </button>
                <button type="button" class="btn btn-danger" id="confirmCancel">
                    <i class="fas fa-check me-2"></i>Yes, Cancel Request
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Download Notice Modal -->
<div class="modal fade" id="downloadNoticeModal" tabindex="-1" aria-labelledby="downloadNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title" id="downloadNoticeModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>Important Notice
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning border-warning">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-2"></i>Digital Copy Notice
                    </h6>
                    <hr>
                    <p class="mb-2">
                        <i class="fas fa-file-pdf me-2"></i>This downloaded document is a <strong>digital copy</strong> and includes a watermark for verification purposes.
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-building me-2"></i><strong>To get an official hard copy without watermark, please visit the Barangay Office during office hours.</strong>
                    </p>
                </div>
                <div class="text-center mt-3">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-clock me-1"></i>Office Hours: Monday - Friday, 8:00 AM - 5:00 PM
                    </p>
                    <a href="#" id="proceedDownload" class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>Proceed with Download
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Download ID Notice Modal -->
<div class="modal fade" id="downloadIdNoticeModal" tabindex="-1" aria-labelledby="downloadIdNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info bg-opacity-10">
                <h5 class="modal-title" id="downloadIdNoticeModalLabel">
                    <i class="fas fa-id-card text-info me-2"></i>Barangay ID Download
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info border-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-2"></i>Digital ID Information
                    </h6>
                    <hr>
                    <p class="mb-2">
                        <i class="fas fa-check-circle me-2 text-success"></i>This is your <strong>official digital Barangay ID</strong>.
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-print me-2"></i>You may print this ID on PVC card or cardstock paper.
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-building me-2"></i>For assistance with physical ID printing, visit the Barangay Office.
                    </p>
                </div>
                <div class="text-center mt-3">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-shield-alt me-1"></i>Keep your digital ID secure and do not share with unauthorized persons.
                    </p>
                    <a href="#" id="proceedIdDownload" class="btn btn-success">
                        <i class="fas fa-download me-2"></i>Download Barangay ID
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Enhancements */
.modal-content {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
}

.modal-header {
    border-bottom: 2px solid #f0f0f0;
    padding: 1.25rem;
}

.modal-footer {
    border-top: 2px solid #f0f0f0;
    padding: 1rem 1.25rem;
}

.modal-body {
    padding: 1.5rem 1.25rem;
}

/* Alert Styles */
.alert {
    border-radius: 0.5rem;
}

.alert-heading {
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.alert hr {
    margin: 0.75rem 0;
    opacity: 0.3;
}

/* Button Styles */
.modal .btn {
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.modal .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
}

/* Progress Bar Animation */
.progress {
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}

.progress-bar {
    font-weight: 600;
    transition: width 0.6s ease;
}

/* Icon Styles */
.modal i.fa-3x {
    opacity: 0.8;
}

/* Badge Enhancements */
.badge {
    padding: 0.5rem 0.75rem;
    font-weight: 600;
    letter-spacing: 0.025em;
}

/* List Styles */
.modal .list-unstyled li {
    padding: 0.25rem 0;
}

/* Text Muted */
.text-muted.small {
    font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
    
    .modal .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}
</style>