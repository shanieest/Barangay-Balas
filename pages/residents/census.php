<!-- Census Data Section (Hidden by default) -->
<section id="census" class="d-none">
    <h2 class="mb-4">Census Data</h2>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Household Information</span>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateHouseholdModal">
                Update Household
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Household Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Household No:</strong> BL-2023-0456</p>
                                    <p><strong>Purok:</strong> 2</p>
                                    <p><strong>Address:</strong> 123 Balas Street</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>House Type:</strong> Single-detached</p>
                                    <p><strong>Ownership:</strong> Owned</p>
                                    <p><strong>Year Built:</strong> 2010</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Household Amenities</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Water Source:</strong> Level III (Piped)</p>
                                    <p><strong>Electricity:</strong> With Meter</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Toilet Facility:</strong> Water-sealed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="mb-3">Household Members</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Relationship to Head</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Civil Status</th>
                            <th>Occupation</th>
                            <th>Education</th>
                            <th>Voter</th>
                            <th>PhilHealth</th>
                            <th>4Ps</th>
                            <th>Indigent</th>
                            <th>Medical History</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Juan Dela Cruz</td>
                            <td>Head</td>
                            <td>38</td>
                            <td>Male</td>
                            <td>Married</td>
                            <td>Teacher</td>
                            <td>College Graduate</td>
                            <td>Yes</td>
                            <td>Yes</td>
                            <td>No</td>
                            <td>No</td>
                            <td>Hypertension</td>
                        </tr>
                        <tr>
                            <td>Maria Dela Cruz</td>
                            <td>Spouse</td>
                            <td>35</td>
                            <td>Female</td>
                            <td>Married</td>
                            <td>Nurse</td>
                            <td>College Graduate</td>
                            <td>Yes</td>
                            <td>Yes</td>
                            <td>No</td>
                            <td>No</td>
                            <td>None</td>
                        </tr>
                        <tr>
                            <td>Pedro Dela Cruz</td>
                            <td>Son</td>
                            <td>12</td>
                            <td>Male</td>
                            <td>Single</td>
                            <td>Student</td>
                            <td>Elementary</td>
                            <td>No</td>
                            <td>No</td>
                            <td>No</td>
                            <td>No</td>
                            <td>Asthma</td>
                        </tr>
                        <tr>
                            <td>Juanita Dela Cruz</td>
                            <td>Daughter</td>
                            <td>7</td>
                            <td>Female</td>
                            <td>Single</td>
                            <td>Student</td>
                            <td>Elementary</td>
                            <td>No</td>
                            <td>No</td>
                            <td>No</td>
                            <td>No</td>
                            <td>None</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include "../../pages/modals/censusModal.php"; ?>
