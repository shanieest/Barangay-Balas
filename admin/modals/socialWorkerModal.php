<?php
// admin/modals/socialWorkerModal.php
?>
    <!-- Add Social Worker Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Add New Social Worker</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="social_worker-backend.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Note:</strong> Username will be auto-generated based on the social worker's name.
                        </div>

                        <h6 class="text-primary mb-3">Personal Information</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" required minlength="6">
                                    <span class="input-group-text password-toggle" onclick="togglePassword('password')">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                </div>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" id="confirm_password" class="form-control" required minlength="6">
                                    <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password')">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                </div>
                                <small class="text-muted">Re-enter the password</small>
                            </div>
                        </div>

                        <h6 class="text-primary mb-3 mt-3">Professional Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Position</label>
                                <input type="text" name="position" class="form-control" 
                                       value="Daycare Social Worker" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control" 
                                       value="Daycare Center">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Specialization</label>
                                <input type="text" name="specialization" class="form-control" 
                                       placeholder="e.g., Early Childhood Development">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" onclick="return validatePassword()">
                            <i class="bi bi-check-circle"></i> Add Social Worker
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Social Worker</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="social_worker-backend.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <h6 class="text-primary mb-3">Personal Information</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">First Name</label>
                                <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" id="edit_middle_name" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Last Name</label>
                                <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Contact Number</label>
                                <input type="text" name="contact_number" id="edit_contact_number" class="form-control" required>
                            </div>
                        </div>

                        <h6 class="text-primary mb-3 mt-3">Professional Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Position</label>
                                <input type="text" name="position" id="edit_position" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" id="edit_department" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Specialization</label>
                                <input type="text" name="specialization" id="edit_specialization" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Status</label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Social Worker
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="social_worker-backend.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        
                        <p>Are you sure you want to delete this social worker?</p>
                        <p><strong id="delete_name"></strong></p>
                        <p class="text-danger"><small>This action cannot be undone!</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>