<!-- Update Household Modal -->
<div class="modal fade" id="updateHouseholdModal" tabindex="-1" aria-labelledby="updateHouseholdModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateHouseholdModalLabel">Update Household Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Household Details Form -->
        <form id="householdForm">
          <div class="mb-3 row">
            <label for="householdNo" class="col-sm-3 col-form-label">Household No</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="householdNo" name="householdNo" value="BL-2023-0456">
            </div>
          </div>
          <div class="mb-3 row">
            <label for="purok" class="col-sm-3 col-form-label">Purok</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="purok" name="purok" value="2">
            </div>
          </div>
          <div class="mb-3 row">
            <label for="address" class="col-sm-3 col-form-label">Address</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="address" name="address" value="123 Balas Street">
            </div>
          </div>
          <div class="mb-3 row">
            <label for="houseType" class="col-sm-3 col-form-label">House Type</label>
            <div class="col-sm-9">
              <select class="form-select" id="houseType" name="houseType">
                <option selected>Single-detached</option>
                <option>Duplex</option>
                <option>Apartment</option>
              </select>
            </div>
          </div>
          <div class="mb-3 row">
            <label for="ownership" class="col-sm-3 col-form-label">Ownership</label>
            <div class="col-sm-9">
              <select class="form-select" id="ownership" name="ownership">
                <option selected>Owned</option>
                <option>Rented</option>
                <option>Leased</option>
              </select>
            </div>
          </div>
          <div class="mb-3 row">
            <label for="yearBuilt" class="col-sm-3 col-form-label">Year Built</label>
            <div class="col-sm-9">
              <input type="number" class="form-control" id="yearBuilt" name="yearBuilt" value="2010">
            </div>
          </div>

          <!-- Household Amenities -->
          <h6 class="mt-4">Household Amenities</h6>
          <div class="mb-3 row">
            <label for="waterSource" class="col-sm-3 col-form-label">Water Source</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="waterSource" name="waterSource" value="Level III (Piped)">
            </div>
          </div>
          <div class="mb-3 row">
            <label for="electricity" class="col-sm-3 col-form-label">Electricity</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="electricity" name="electricity" value="With Meter">
            </div>
          </div>
          <div class="mb-3 row">
            <label for="toilet" class="col-sm-3 col-form-label">Toilet Facility</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="toilet" name="toilet" value="Water-sealed">
            </div>
          </div>

          <!-- Household Members -->
          <h6 class="mt-4">Household Members</h6>
          <button type="button" class="btn btn-sm btn-success mb-2" id="addMemberBtn">Add Member</button>
          <div class="table-responsive">
            <table class="table table-bordered" id="membersTable">
              <thead class="table-light">
                <tr>
                  <th>Name</th>
                  <th>Relationship</th>
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
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <!-- Start with one empty row -->
                <tr>
                  <td><input type="text" class="form-control" name="name[]"></td>
                  <td><input type="text" class="form-control" name="relationship[]"></td>
                  <td><input type="number" class="form-control" name="age[]"></td>
                  <td>
                    <select class="form-select" name="gender[]">
                      <option>Male</option>
                      <option>Female</option>
                    </select>
                  </td>
                  <td>
                    <select class="form-select" name="civil_status[]">
                      <option>Single</option>
                      <option>Married</option>
                      <option>Widowed</option>
                    </select>
                  </td>
                  <td><input type="text" class="form-control" name="occupation[]"></td>
                  <td><input type="text" class="form-control" name="education[]"></td>
                  <td>
                    <select class="form-select" name="voter[]">
                      <option>Yes</option>
                      <option>No</option>
                    </select>
                  </td>
                  <td>
                    <select class="form-select" name="philhealth[]">
                      <option>Yes</option>
                      <option>No</option>
                    </select>
                  </td>
                  <td>
                    <select class="form-select" name="4ps[]">
                      <option>Yes</option>
                      <option>No</option>
                    </select>
                  </td>
                  <td>
                    <select class="form-select" name="indigent[]">
                      <option>Yes</option>
                      <option>No</option>
                    </select>
                  </td>
                  <td><input type="text" class="form-control" name="medical_history[]"></td>
                  <td><button type="button" class="btn btn-sm btn-danger removeMember">Remove</button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript for adding/removing members -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const addMemberBtn = document.getElementById('addMemberBtn');
  const membersTable = document.getElementById('membersTable').querySelector('tbody');

  // Add new member row
  addMemberBtn.addEventListener('click', function () {
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
      <td><input type="text" class="form-control" name="name[]"></td>
      <td><input type="text" class="form-control" name="relationship[]"></td>
      <td><input type="number" class="form-control" name="age[]"></td>
      <td>
        <select class="form-select" name="gender[]">
          <option>Male</option>
          <option>Female</option>
        </select>
      </td>
      <td>
        <select class="form-select" name="civil_status[]">
          <option>Single</option>
          <option>Married</option>
          <option>Widowed</option>
        </select>
      </td>
      <td><input type="text" class="form-control" name="occupation[]"></td>
      <td><input type="text" class="form-control" name="education[]"></td>
      <td>
        <select class="form-select" name="voter[]">
          <option>Yes</option>
          <option>No</option>
        </select>
      </td>
      <td>
        <select class="form-select" name="philhealth[]">
          <option>Yes</option>
          <option>No</option>
        </select>
      </td>
      <td>
        <select class="form-select" name="4ps[]">
          <option>Yes</option>
          <option>No</option>
        </select>
      </td>
      <td>
        <select class="form-select" name="indigent[]">
          <option>Yes</option>
          <option>No</option>
        </select>
      </td>
      <td><input type="text" class="form-control" name="medical_history[]"></td>
      <td><button type="button" class="btn btn-sm btn-danger removeMember">Remove</button></td>
    `;
    membersTable.appendChild(newRow);
  });

  // Remove member row
  membersTable.addEventListener('click', function(e){
    if(e.target && e.target.classList.contains('removeMember')){
      e.target.closest('tr').remove();
    }
  });
});
</script>
