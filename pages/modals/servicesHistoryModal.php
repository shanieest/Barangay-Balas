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

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-labelledby="cancelConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelConfirmModalLabel">Confirm Cancellation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this request? This action cannot be undone.</p>
                <input type="hidden" id="cancelRequestId">
                <input type="hidden" id="cancelRequestType">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep It</button>
                <button type="button" class="btn btn-danger" id="confirmCancel">Yes, Cancel Request</button>
            </div>
        </div>
    </div>
</div>

<!-- Download Notice Modal -->
<div class="modal fade" id="downloadNoticeModal" tabindex="-1" aria-labelledby="downloadNoticeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="downloadNoticeModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>Important Notice
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Digital Copy Notice</h6>
                    <p class="mb-2">This downloaded document is a digital copy and includes a watermark.</p>
                    <p class="mb-0">
                        <strong>To get an official hard copy without watermark, please visit the Barangay Office.</strong>
                    </p>
                </div>
                <div class="text-center mt-3">
                    <a href="#" id="proceedDownload" class="btn btn-primary">
                        <i class="fas fa-download me-2"></i>Proceed with Download
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>