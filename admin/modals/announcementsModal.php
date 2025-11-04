<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addAnnouncementModalLabel">Add New Announcement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAnnouncementForm" action="backend/announcements-backend.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="announcementTitle" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="announcementTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="announcementContent" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="announcementContent" name="content" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="announcementImages" class="form-label">Images <span class="text-muted">(Optional - Max 5 images, 5MB each)</span></label>
                        <input class="form-control" type="file" id="announcementImages" name="images[]" multiple accept="image/*">
                        <small class="text-muted">Supported formats: JPG, JPEG, PNG, GIF, WebP</small>
                    </div>
                    <div class="mb-3">
                        <label for="announcementDate" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="announcementDate" name="date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAnnouncementBtn">
                    <i class="fas fa-save me-1"></i>Save Announcement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editAnnouncementModalLabel">Edit Announcement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAnnouncementForm">
                    <div class="mb-3">
                        <label for="editAnnouncementTitle" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editAnnouncementTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAnnouncementContent" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="editAnnouncementContent" name="content" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editannouncementImages" class="form-label">Add New Images <span class="text-muted">(Optional - Max 5 images, 5MB each)</span></label>
                        <input class="form-control" type="file" id="editannouncementImages" name="images[]" multiple accept="image/*">
                        <small class="text-muted" id="currentImageInfo"></small>
                        <div class="mt-2">
                            <small class="text-info">
                                <i class="fas fa-info-circle me-1"></i>
                                Tip: New images will be added alongside existing ones. To remove existing images, delete and recreate the announcement.
                            </small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editAnnouncementDate" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="editAnnouncementDate" name="date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning text-white" id="updateAnnouncementBtn">
                    <i class="fas fa-edit me-1"></i>Update Announcement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Announcement Modal -->
<div class="modal fade" id="deleteAnnouncementModal" tabindex="-1" aria-labelledby="deleteAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAnnouncementModalLabel">
                    <i class="fas fa-exclamation-triangle me-1"></i>Delete Announcement
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-warning me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. All associated images will also be deleted.
                </div>
                <p>Are you sure you want to delete this announcement?</p>
                <div class="card">
                    <div class="card-body">
                        <p class="mb-1"><strong>Title:</strong> <span id="deleteAnnouncementTitle"></span></p>
                        <p class="mb-0"><strong>Date:</strong> <span id="deleteAnnouncementDate"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="deleteAnnouncementBtn">
                    <i class="fas fa-trash me-1"></i>Delete Announcement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal for viewing full images -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-header border-0">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-0">
        <img id="modalImage" src="" class="img-fluid rounded shadow" alt="Announcement Image" style="max-height: 80vh;">
      </div>
    </div>
  </div>
</div>
