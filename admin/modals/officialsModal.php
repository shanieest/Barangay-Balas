    <!-- Add Official Modal -->
    <div class="modal fade" id="addOfficialModal" tabindex="-1" aria-labelledby="addOfficialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addOfficialModalLabel">Add New Barangay Official</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addOfficialForm" novalidate>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="officialFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="officialFirstName" required>
                                <div class="invalid-feedback">Please provide a first name.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="officialMiddleName" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="officialMiddleName">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="officialLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="officialLastName" required>
                                <div class="invalid-feedback">Please provide a last name.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="officialPosition" class="form-label">Position <span class="text-danger">*</span></label>
                                <select class="form-select position-select" id="officialPosition" required>
                                    <option value="">Select Position...</option>
                                    <option value="Barangay Captain">Barangay Captain</option>
                                    <option value="Barangay Secretary">Barangay Secretary</option>
                                    <option value="Barangay Treasurer">Barangay Treasurer</option>
                                    <option value="Barangay Kagawad">Barangay Kagawad</option>
                                    <option value="SK Chairman">SK Chairman</option>
                                    <option value="Other">Other</option>
                                </select>
                                <div class="invalid-feedback">Please select a position.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="officialStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="officialStatus" required>
                                    <option value="Active" selected>Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="officialEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="officialEmail" required>
                                <div class="form-text">Will be used for login</div>
                                <div class="invalid-feedback">Please provide a valid email.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="officialContact" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="officialContact" pattern="[0-9]{11}" maxlength="11" placeholder="09123456789">
                                <div class="form-text">11-digit phone number</div>
                                <div class="invalid-feedback">Please enter a valid 11-digit phone number.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="officialPassword" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="officialPassword" required minlength="8">
                                <div class="form-text">Minimum 8 characters</div>
                                <div class="invalid-feedback">Password must be at least 8 characters.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="officialConfirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="officialConfirmPassword" required minlength="8">
                                <div class="invalid-feedback">Passwords must match.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Official</button>
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
                    <h5 class="modal-title" id="editOfficialModalLabel">Edit Barangay Official</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editOfficialForm" novalidate>
                    <input type="hidden" id="editOfficialId">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="editOfficialFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editOfficialFirstName" required>
                                <div class="invalid-feedback">Please provide a first name.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="editOfficialMiddleName" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="editOfficialMiddleName">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="editOfficialLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editOfficialLastName" required>
                                <div class="invalid-feedback">Please provide a last name.</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editOfficialPosition" class="form-label">Position <span class="text-danger">*</span></label>
                                <select class="form-select position-select" id="editOfficialPosition" required>
                                    <option value="Barangay Captain">Barangay Captain</option>
                                    <option value="Barangay Secretary">Barangay Secretary</option>
                                    <option value="Barangay Treasurer">Barangay Treasurer</option>
                                    <option value="Barangay Kagawad">Barangay Kagawad</option>
                                    <option value="SK Chairman">SK Chairman</option>
                                    <option value="Other">Other</option>
                                </select>
                                <div class="invalid-feedback">Please select a position.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editOfficialStatus" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="editOfficialStatus" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editOfficialEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="editOfficialEmail" required>
                                <div class="invalid-feedback">Please provide a valid email.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editOfficialContact" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="editOfficialContact" pattern="[0-9]{11}" maxlength="11" placeholder="09123456789">
                                <div class="form-text">11-digit phone number</div>
                                <div class="invalid-feedback">Please enter a valid 11-digit phone number.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editOfficialPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="editOfficialPassword" minlength="8">
                            <div class="form-text">Leave blank to keep current password</div>
                            <div class="invalid-feedback">Password must be at least 8 characters.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-dark">Update Official</button>
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
                    <h5 class="modal-title" id="deleteOfficialModalLabel">Delete Barangay Official</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this barangay official?</p>
                    <p><strong>Name:</strong> <span id="deleteOfficialName"></span></p>
                    <p><strong>Position:</strong> <span id="deleteOfficialPosition"></span></p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>This action cannot be undone.</p>
                    <input type="hidden" id="deleteOfficialId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Official</button>
                </div>
            </div>
        </div>
    </div>