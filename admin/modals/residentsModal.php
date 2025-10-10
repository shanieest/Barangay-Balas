<!-- Add Resident Modal -->
    <div class="modal fade" id="addResidentModal" tabindex="-1" aria-labelledby="addResidentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addResidentModalLabel">Add New Resident</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addResidentForm" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="createAccount" id="createAccount" value="false">
                        <div class="form-section">
                            <h5 class="form-section-title"><i class="fas fa-user"></i> Personal Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="firstName" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" required>
                                        <div class="invalid-feedback">Please provide a first name.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lastName" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" required>
                                        <div class="invalid-feedback">Please provide a last name.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="middleName" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="middleName" name="middleName">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="suffix" class="form-label">Suffix</label>
                                        <input type="text" class="form-control" id="suffix" name="suffix">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sex" class="form-label">Sex *</label>
                                        <select class="form-select" id="sex" name="sex" required>
                                            <option value="">Select...</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a sex.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h5 class="form-section-title"><i class="fas fa-address-card"></i> Contact Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contactNumber" class="form-label">Contact Number *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="tel" class="form-control" id="contactNumber" name="contactNumber" required>
                                        </div>
                                        <div class="invalid-feedback">Please provide a contact number.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                           <div class="mb-3">
                                    <label for="houseNumber" class="form-label">House no *</label>
                                    <input type="text" class="form-control" id="houseNumber" name="houseNumber" required>
                                    <div class="invalid-feedback">Please provide a house number.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="purok" class="form-label">Purok *</label>
                                    <input type="text" class="form-control" id="purok" name="purok" required>
                                    <div class="invalid-feedback">Please provide a purok/zone.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Full Address *</label>
                                    <textarea class="form-control" id="address" name="address" rows="2" required readonly></textarea>
                                    <div class="invalid-feedback">Please provide a complete address.</div>
                                </div>
                        </div>
                        
                        <div class="form-section">
                            <h5 class="form-section-title"><i class="fas fa-calendar-alt"></i> Birth Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="birthdate" class="form-label">Birthdate *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                                        </div>
                                        <div class="invalid-feedback">Please provide a birthdate.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="age" class="form-label">Age</label>
                                        <input type="number" class="form-control" id="age" name="age" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h5 class="form-section-title"><i class="fas fa-user-plus"></i> Account Creation</h5>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="createAccountCheck">
                                <label class="form-check-label" for="createAccountCheck">
                                    Create resident account for portal access
                                </label>
                            </div>
                            
                            <div id="accountFields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password *</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="password" name="password">
                                            </div>
                                            <div class="invalid-feedback">Please provide a password.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveResidentBtn">Add Resident</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Resident Modal -->
    <div class="modal fade" id="editResidentModal" tabindex="-1" aria-labelledby="editResidentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editResidentModalLabel">Edit Resident</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editResidentForm" enctype="multipart/form-data" novalidate>
                        <input type="hidden" id="editResidentId" name="id">
                        <div class="form-section">
                            <h5 class="form-section-title"><i class="fas fa-user"></i> Personal Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editFirstName" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="editFirstName" name="firstName" required>
                                        <div class="invalid-feedback">Please provide a first name.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editLastName" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="editLastName" name="lastName" required>
                                        <div class="invalid-feedback">Please provide a last name.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editMiddleName" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="editMiddleName" name="middleName">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editSuffix" class="form-label">Suffix</label>
                                        <input type="text" class="form-control" id="editSuffix" name="suffix">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editSex" class="form-label">Sex *</label>
                                        <select class="form-select" id="editSex" name="sex" required>
                                            <option value="">Select...</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a sex.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h5 class="form-section-title"><i class="fas fa-address-card"></i> Contact Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editContactNumber" class="form-label">Contact Number *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="tel" class="form-control" id="editContactNumber" name="contactNumber" required>
                                        </div>
                                        <div class="invalid-feedback">Please provide a contact number.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editEmail" class="form-label">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="editEmail" name="email">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                    <label for="editHouseNumber" class="form-label">House Number *</label>
                                    <input type="text" class="form-control" id="editHouseNumber" name="houseNumber" required>
                                    <div class="invalid-feedback">Please provide a house number.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="editPurok" class="form-label">Purok (Zone) *</label>
                                    <input type="text" class="form-control" id="editPurok" name="purok" required>
                                    <div class="invalid-feedback">Please provide a purok/zone.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="editAddress" class="form-label">Complete Address *</label>
                                    <textarea class="form-control" id="editAddress" name="address" rows="2" required readonly></textarea>
                                    <div class="invalid-feedback">Please provide a complete address.</div>
                                </div>
                        </div>
                        
                        <div class="form-section">
                            <h5 class="form-section-title"><i class="fas fa-calendar-alt"></i> Birth Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editBirthdate" class="form-label">Birthdate *</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            <input type="date" class="form-control" id="editBirthdate" name="birthdate" required>
                                        </div>
                                        <div class="invalid-feedback">Please provide a birthdate.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editAge" class="form-label">Age</label>
                                        <input type="number" class="form-control" id="editAge" name="age" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateResidentBtn">Update Resident</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Resident Modal -->
    <div class="modal fade" id="viewResidentModal" tabindex="-1" aria-labelledby="viewResidentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewResidentModalLabel">Resident Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="img/default-profile.jpg" class="img-thumbnail mb-3 resident-photo" width="150">
                        <h5 class="resident-name"></h5>
                        <p class="text-muted resident-id"></p>
                        <span class="badge resident-status-badge"></span>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row mb-3">
                                    <div class="col-sm-5">
                                        <p class="mb-0"><strong>Birthdate:</strong></p>
                                    </div>
                                    <div class="col-sm-7">
                                        <p class="text-muted mb-0 resident-birthdate"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-5">
                                        <p class="mb-0"><strong>Sex:</strong></p>
                                    </div>
                                    <div class="col-sm-7">
                                        <p class="text-muted mb-0 resident-sex"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-3">
                                    <div class="col-sm-5">
                                        <p class="mb-0"><strong>Contact:</strong></p>
                                    </div>
                                    <div class="col-sm-7">
                                        <p class="text-muted mb-0 resident-contact"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-5">
                                        <p class="mb-0"><strong>Email:</strong></p>
                                    </div>
                                    <div class="col-sm-7">
                                        <p class="text-muted mb-0 resident-email"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-5">
                                        <p class="mb-0"><strong>Age:</strong></p>
                                    </div>
                                    <div class="col-sm-7">
                                        <p class="text-muted mb-0 resident-age"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0"><strong>Address:</strong></p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0 resident-address"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- UPDATED: Two ID Photos -->
                        <h6 class="mb-3">Valid ID Photos:</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Front</label>
                                <div class="border rounded p-2">
                                    <img src="img/default-id.jpg" class="img-fluid resident-valid-id" alt="Valid ID Front">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3 id-photo-2-container">
                                <label class="text-muted small">Back</label>
                                <div class="border rounded p-2">
                                    <img src="img/default-id.jpg" class="img-fluid resident-valid-id-2" alt="Valid ID Back">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Information Section -->
                        <div class="account-details mt-4" style="display: none;">
                            <h6>Account Information:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            <p class="mb-0"><strong>Status:</strong></p>
                                        </div>
                                        <div class="col-sm-8">
                                            <span class="badge account-status-badge"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            <p class="mb-0"><strong>Processed By:</strong></p>
                                        </div>
                                        <div class="col-sm-8">
                                            <p class="text-muted mb-0 resident-processed-by"></p>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            <p class="mb-0"><strong>Date Processed:</strong></p>
                                        </div>
                                        <div class="col-sm-8">
                                            <p class="text-muted mb-0 resident-date-processed"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="row mb-2">
                                        <div class="col-sm-2">
                                            <p class="mb-0"><strong>Notes:</strong></p>
                                        </div>
                                        <div class="col-sm-10">
                                            <p class="text-muted mb-0 resident-account-notes"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
    </div>
    
    <!-- View Account Request Modal -->
    <div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewRequestModalLabel">Account Request Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="img/default-profile.jpg" class="img-thumbnail mb-3 request-photo" width="150">
                        <h5 class="request-name"></h5>
                        <p class="text-muted request-id"></p>
                        <span class="badge request-status-badge mb-2"></span>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row mb-3">
                                    <div class="col-sm-5">
                                        <p class="mb-0"><strong>Birthdate:</strong></p>
                                    </div>
                                    <div class="col-sm-7">
                                        <p class="text-muted mb-0 request-birthdate"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-5">
                                        <p class="mb-0"><strong>Sex:</strong></p>
                                    </div>
                                    <div class="col-sm-7">
                                        <p class="text-muted mb-0 request-sex"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-3">
                                    <div class="col-sm-5">
                                        <p class="mb-0"><strong>Contact:</strong></p>
                                    </div>
                                    <div class="col-sm-7">
                                        <p class="text-muted mb-0 request-contact"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-5">
                                        <p class="mb-0"><strong>Email:</strong></p>
                                    </div>
                                    <div class="col-sm-7">
                                        <p class="text-muted mb-0 request-email"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="mb-0"><strong>Address:</strong></p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="text-muted mb-0 request-address"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- UPDATED: Two ID Photos -->
                        <h6 class="mb-3">Valid ID Photos:</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Front</label>
                                <div class="border rounded p-2">
                                    <img src="img/default-id.jpg" class="img-fluid request-valid-id" alt="Valid ID Front">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3 id-photo-2-container">
                                <label class="text-muted small">Back</label>
                                <div class="border rounded p-2">
                                    <img src="img/default-id.jpg" class="img-fluid request-valid-id-2" alt="Valid ID Back">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Request Details -->
                        <div class="account-details mt-4">
                            <h6>Request Information:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            <p class="mb-0"><strong>Date Requested:</strong></p>
                                        </div>
                                        <div class="col-sm-8">
                                            <p class="text-muted mb-0 request-date-requested"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Only show if processed -->
                            <div id="requestProcessedInfo" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <p class="mb-0"><strong>Processed By:</strong></p>
                                            </div>
                                            <div class="col-sm-8">
                                                <p class="text-muted mb-0 request-processed-by"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <p class="mb-0"><strong>Date Processed:</strong></p>
                                            </div>
                                            <div class="col-sm-8">
                                                <p class="text-muted mb-0 request-date-processed"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="row mb-2">
                                            <div class="col-sm-2">
                                                <p class="mb-0"><strong>Notes:</strong></p>
                                            </div>
                                            <div class="col-sm-10">
                                                <p class="text-muted mb-0 request-notes"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="approveRequestBtn">Approve</button>
                <button type="button" class="btn btn-danger" id="rejectRequestBtn">Reject</button>
            </div>
        </div>
    </div>
    </div>
    
    <!-- Approve/Reject Request Modal -->
    <div class="modal fade" id="processRequestModal" tabindex="-1" aria-labelledby="processRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" id="processRequestModalHeader">
                    <h5 class="modal-title" id="processRequestModalLabel">Process Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="processRequestMessage">Please provide any notes for this action:</p>
                    <div class="mb-3">
                        <textarea class="form-control note-textarea" id="requestNote" placeholder="Enter notes..." required></textarea>
                        <div class="invalid-feedback">Please provide a note for this action.</div>
                    </div>
                    <input type="hidden" id="requestIdForProcess">
                    <input type="hidden" id="requestActionType">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn" id="confirmProcessRequestBtn">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Resident Modal -->
    <div class="modal fade" id="deleteResidentModal" tabindex="-1" aria-labelledby="deleteResidentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteResidentModalLabel">Delete Resident</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this resident record?</p>
                    <p><strong>Name:</strong> <span id="deleteResidentName"></span></p>
                    <p><strong>Resident ID:</strong> <span id="deleteResidentId"></span></p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Resident</button>
                </div>
            </div>
        </div>
    </div>

    <style>
.id-photo-2-container {
    display: block; /* Will be controlled by JavaScript */
}

.id-photo-2-container img {
    max-height: 300px;
    object-fit: contain;
}

.resident-valid-id, 
.resident-valid-id-2,
.request-valid-id,
.request-valid-id-2 {
    max-height: 300px;
    object-fit: contain;
    cursor: pointer;
    transition: transform 0.2s;
}

.resident-valid-id:hover, 
.resident-valid-id-2:hover,
.request-valid-id:hover,
.request-valid-id-2:hover {
    transform: scale(1.05);
}

/* Optional: Add modal for full-size image viewing */
.img-preview {
    cursor: pointer;
}
</style>