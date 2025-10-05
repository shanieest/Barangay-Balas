    <div class="modal fade" id="photoUploadModal" tabindex="-1" aria-labelledby="photoUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photoUploadModalLabel">Update Profile Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="photoUploadForm" method="POST" action="profile-backend.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="upload_photo">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="mb-3">
                            <label for="profile_photo" class="form-label">Select a photo</label>
                            <input class="form-control" type="file" id="profile_photo" name="profile_photo" accept="image/*" required>
                            <div class="form-text">Allowed formats: JPG, PNG, GIF. Maximum size: 5MB.</div>
                        </div>
                        
                        <div class="text-center">
                            <img id="imagePreview" src="#" alt="Preview" class="img-thumbnail mt-3" style="display: none; max-width: 200px;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload Photo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>