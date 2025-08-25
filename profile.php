                <section id="profile" class="d-none">
                    <h2 class="mb-4">My Profile</h2>
                    <div class="card">
                        <div class="card-header">
                            <span>Personal Information</span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <img src="https://via.placeholder.com/150" alt="Profile" class="profile-img mb-3">
                                    <button class="btn btn-sm btn-outline-primary">Change Photo</button>
                                </div>
                                <div class="col-md-9">
                                    <form>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">First Name</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($profile['first_name']) ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Middle Name</label>
                                                <input type="text" class="form-control" value="">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control" value="Dela Cruz">
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Birthdate</label>
                                                <input type="date" class="form-control" value="1985-06-12">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Gender</label>
                                                <select class="form-select">
                                                    <option>Male</option>
                                                    <option>Female</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Civil Status</label>
                                                <select class="form-select">
                                                    <option>Single</option>
                                                    <option selected>Married</option>
                                                    <option>Widowed</option>
                                                    <option>Separated</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Blood Type</label>
                                                <select class="form-select">
                                                    <option>A+</option>
                                                    <option>A-</option>
                                                    <option>B+</option>
                                                    <option>B-</option>
                                                    <option selected>O+</option>
                                                    <option>O-</option>
                                                    <option>AB+</option>
                                                    <option>AB-</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" value="juan.delacruz@example.com">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Mobile Number</label>
                                                <input type="tel" class="form-control" value="09123456789">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <input type="text" class="form-control" value="123 Balas Street, Barangay Balas">
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Purok</label>
                                                <select class="form-select">
                                                    <option>1</option>
                                                    <option selected>2</option>
                                                    <option>3</option>
                                                    <option>4</option>
                                                    <option>5</option>
                                                    <option>6</option>
                                                    <option>7</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Voter Status</label>
                                                <select class="form-select">
                                                    <option selected>Registered Voter</option>
                                                    <option>Not Registered</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Occupation</label>
                                                <input type="text" class="form-control" value="Teacher">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-outline-secondary me-2">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Family Members Section -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Family Members</span>
                            <button class="btn btn-sm btn-primary">Add Family Member</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Birthdate</th>
                                            <th>Civil Status</th>
                                            <th>Occupation</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Maria Dela Cruz</td>
                                            <td>Spouse</td>
                                            <td>1988-03-15</td>
                                            <td>Married</td>
                                            <td>Nurse</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">Edit</button>
                                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                                            </td>
                                        </tr>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">Edit</button>
                                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>