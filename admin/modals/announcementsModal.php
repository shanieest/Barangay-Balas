<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1" aria-labelledby="addAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addAnnouncementModalLabel">Add New Announcement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAnnouncementForm">
                    <div class="mb-3">
                        <label for="announcementTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="announcementTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="announcementContent" class="form-label">Content</label>
                        <textarea class="form-control" id="announcementContent" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="announcementImage" class="form-label">Image</label>
                        <input class="form-control" type="file" id="announcementImage">
                    </div>
                    <div class="mb-3">
                        <label for="announcementDate" class="form-label">Date</label>
                        <input type="date" class="form-control" id="announcementDate" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAnnouncementBtn">Save Announcement</button>
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
                        <label for="editAnnouncementTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="editAnnouncementTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAnnouncementContent" class="form-label">Content</label>
                        <textarea class="form-control" id="editAnnouncementContent" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editAnnouncementImage" class="form-label">Image</label>
                        <input class="form-control" type="file" id="editAnnouncementImage">
                        <small class="text-muted" id="currentImageInfo"></small>
                    </div>
                    <div class="mb-3">
                        <label for="editAnnouncementDate" class="form-label">Date</label>
                        <input type="date" class="form-control" id="editAnnouncementDate" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning text-white" id="updateAnnouncementBtn">Update Announcement</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Announcement Modal -->
<div class="modal fade" id="deleteAnnouncementModal" tabindex="-1" aria-labelledby="deleteAnnouncementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAnnouncementModalLabel">Delete Announcement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this announcement?</p>
                <p><strong>Title:</strong> <span id="deleteAnnouncementTitle"></span></p>
                <p><strong>Date:</strong> <span id="deleteAnnouncementDate"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteAnnouncementBtn">Delete Announcement</button>
            </div>
        </div>
    </div>
</div>

<script>
// Set Add Modal date to current date automatically
document.addEventListener('DOMContentLoaded', () => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('announcementDate').value = today;
});

// When editing, you can dynamically set the date too using JS
function populateEditModal(title, content, date, image) {
    document.getElementById('editAnnouncementTitle').value = title;
    document.getElementById('editAnnouncementContent').value = content;
    document.getElementById('editAnnouncementDate').value = date;
    document.getElementById('currentImageInfo').textContent = image ? `Current image: ${image}` : '';
}
</script>
