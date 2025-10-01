<!-- Add Official Modal -->
<div class="modal fade" id="addOfficialModal" tabindex="-1" aria-labelledby="addOfficialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addOfficialModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New Official
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addOfficialForm">
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> A username will be automatically generated based on the name provided.
                    </div>

                    <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="officialFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="officialFirstName" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="officialMiddleName" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="officialMiddleName">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="officialLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="officialLastName" required>
                        </div>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Position & Access</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="officialPosition" class="form-label">Position <span class="text-danger">*</span></label>
                            <select class="form-select" id="officialPosition" required>
                                <option value="">Select Position...</option>
                                <option value="Barangay Captain">Barangay Captain</option>
                                <option value="Barangay Secretary">Barangay Secretary</option>
                                <option value="Barangay Treasurer">Barangay Treasurer</option>
                                <option value="Barangay Kagawad">Barangay Kagawad</option>
                                <option value="SK Chairman">SK Chairman</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="officialRole" class="form-label">System Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="officialRole" required>
                                <option value="">Select Role...</option>
                                <option value="Admin">Admin - Full System Access</option>
                                <option value="Official">Official - Limited Access</option>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-shield-alt me-1"></i>
                                Admin can manage everything | Official has restricted access
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="officialStatus" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="officialStatus" required>
                                <option value="Active" selected>Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="officialContact" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="officialContact" pattern="[0-9]{11}" maxlength="11" placeholder="09123456789">
                            <div class="form-text">11-digit phone number</div>
                        </div>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Login Credentials</h6>
                    <div class="mb-3">
                        <label for="officialEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="officialEmail" required>
                        <div class="form-text">
                            <i class="fas fa-envelope me-1"></i>
                            Will be used for login and notifications
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="officialPassword" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="officialPassword" required minlength="8">
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="officialConfirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="officialConfirmPassword" required minlength="8">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Add Official
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Official Modal -->
<div class="modal fade" id="editOfficialModal" tabindex="-1" aria-labelledby="editOfficialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editOfficialModalLabel">
                    <i class="fas fa-user-edit me-2"></i>Edit Official
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editOfficialForm">
                <input type="hidden" id="editOfficialId">
                <div class="modal-body">
                    <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="editOfficialFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editOfficialFirstName" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editOfficialMiddleName" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="editOfficialMiddleName">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editOfficialLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editOfficialLastName" required>
                        </div>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Position & Access</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editOfficialPosition" class="form-label">Position <span class="text-danger">*</span></label>
                            <select class="form-select" id="editOfficialPosition" required>
                                <option value="Barangay Captain">Barangay Captain</option>
                                <option value="Barangay Secretary">Barangay Secretary</option>
                                <option value="Barangay Treasurer">Barangay Treasurer</option>
                                <option value="Barangay Kagawad">Barangay Kagawad</option>
                                <option value="SK Chairman">SK Chairman</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editOfficialRole" class="form-label">System Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="editOfficialRole" required>
                                <option value="Admin">Admin - Full System Access</option>
                                <option value="Official">Official - Limited Access</option>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-shield-alt me-1"></i>
                                Admin can manage everything | Official has restricted access
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editOfficialStatus" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="editOfficialStatus" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editOfficialContact" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="editOfficialContact" pattern="[0-9]{11}" maxlength="11" placeholder="09123456789">
                            <div class="form-text">11-digit phone number</div>
                        </div>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Contact & Login</h6>
                    <div class="mb-3">
                        <label for="editOfficialEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="editOfficialEmail" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editOfficialPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="editOfficialPassword" minlength="8">
                        <div class="form-text">
                            <i class="fas fa-key me-1"></i>
                            Leave blank to keep current password
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="fas fa-save me-1"></i>Update Official
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Official Modal -->
<div class="modal fade" id="deleteOfficialModal" tabindex="-1" aria-labelledby="deleteOfficialModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteOfficialModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Official
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
                
                <p class="mb-3">Are you sure you want to delete this official?</p>
                
                <div class="card">
                    <div class="card-body">
                        <p class="mb-2"><strong>Name:</strong> <span id="deleteOfficialName"></span></p>
                        <p class="mb-2"><strong>Position:</strong> <span id="deleteOfficialPosition"></span></p>
                        <p class="mb-0"><strong>Role:</strong> <span id="deleteOfficialRole"></span></p>
                    </div>
                </div>
                
                <input type="hidden" id="deleteOfficialId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-1"></i>Delete Official
                </button>
            </div>
        </div>
    </div>
</div>