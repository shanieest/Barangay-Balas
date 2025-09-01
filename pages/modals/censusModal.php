<div class="modal fade" id="updateHouseholdModal" tabindex="-1" aria-labelledby="updateHouseholdModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateHouseholdModalLabel">Update Household Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form method="POST" action="census-backend.php">
        <!-- Scrollable body -->
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
          <!-- Household Details Form -->
          <div id="householdForm">
            <input type="hidden" name="household_id" value="<?= $household_data['id'] ?? ''; ?>">
            
            <div class="mb-3 row">
              <label for="householdNo" class="col-sm-3 col-form-label">Household No</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="householdNo" name="householdNo" 
                       value="<?= htmlspecialchars($household_data['house_number'] ?? ''); ?>" required>
              </div>
            </div>
            
            <!-- Purok -->
            <div class="mb-3 row">
              <label for="purok" class="col-sm-3 col-form-label">Purok</label>
              <div class="col-sm-9">
                <select class="form-select" id="purok" name="purok" required>
                  <option value="" disabled <?= empty($household_data['purok']) ? 'selected' : ''; ?>>Select Purok</option>
                  <option value="Purok 1" <?= ($household_data['purok'] ?? '') == 'Purok 1' ? 'selected' : ''; ?>>Purok 1</option>
                  <option value="Purok 2" <?= ($household_data['purok'] ?? '') == 'Purok 2' ? 'selected' : ''; ?>>Purok 2</option>
                  <option value="Purok 3" <?= ($household_data['purok'] ?? '') == 'Purok 3' ? 'selected' : ''; ?>>Purok 3</option>
                  <option value="Purok 4" <?= ($household_data['purok'] ?? '') == 'Purok 4' ? 'selected' : ''; ?>>Purok 4</option>
                  <option value="Purok 5" <?= ($household_data['purok'] ?? '') == 'Purok 5' ? 'selected' : ''; ?>>Purok 5</option>
                  <option value="Purok 6" <?= ($household_data['purok'] ?? '') == 'Purok 6' ? 'selected' : ''; ?>>Purok 6</option>
                  <option value="Purok 7" <?= ($household_data['purok'] ?? '') == 'Purok 7' ? 'selected' : ''; ?>>Purok 7</option>
                </select>
              </div>
            </div>

            <!-- Household Amenities -->
            <h6 class="mt-4">Household Amenities</h6>
            <div class="mb-3 row">
              <label for="waterSource" class="col-sm-3 col-form-label">Water Source</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="waterSource" name="waterSource" 
                       value="<?= htmlspecialchars($household_data['type_of_water_source'] ?? ''); ?>">
              </div>
            </div>
            
            <div class="mb-3 row">
              <label for="toilet" class="col-sm-3 col-form-label">Toilet Facility</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="toilet" name="toilet" 
                       value="<?= htmlspecialchars($household_data['type_of_toilet_facility'] ?? ''); ?>">
              </div>
            </div>

            <!-- Household Members -->
            <h6 class="mt-4">Household Members</h6>
            
            <!-- Existing Residents Selection -->
            <div class="mb-3">
                <label class="form-label">Select Existing Residents</label>
                <select class="form-select" id="existingResidents">
                    <option value="">-- Select resident to add --</option>
                    <?php foreach ($all_residents as $resident): ?>
                        <option value="<?= $resident['id'] ?>" 
                                data-firstname="<?= htmlspecialchars($resident['first_name']) ?>"
                                data-lastname="<?= htmlspecialchars($resident['last_name']) ?>">
                            <?= htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="button" class="btn btn-sm btn-success mb-2" id="addMemberBtn">Add New Member</button>

            <!-- Members table -->
            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
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
                    <th>PhilHealth</th>
                    <th>4Ps</th>
                    <th>Indigent</th>
                    <th>Medical History</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($residents)): ?>
                      <?php foreach ($residents as $resident): ?>
                          <tr>
                            <td>
                                <input type="hidden" name="resident_id[]" value="<?= $resident['id'] ?>">
                                <input type="text" class="form-control" name="name[]" value="<?= htmlspecialchars(($resident['first_name'] ?? '') . ' ' . ($resident['last_name'] ?? '')) ?>" required>
                            </td>
                            <td>
                                <select class="form-select" name="relationship[]">
                                    <option value="Head" <?= ($resident['relationship_to_head'] == 'Head') ? 'selected' : '' ?>>Head</option>
                                    <option value="Spouse" <?= ($resident['relationship_to_head'] == 'Spouse') ? 'selected' : '' ?>>Spouse</option>
                                    <option value="Child" <?= ($resident['relationship_to_head'] == 'Child') ? 'selected' : '' ?>>Child</option>
                                    <option value="Parent" <?= ($resident['relationship_to_head'] == 'Parent') ? 'selected' : '' ?>>Parent</option>
                                    <option value="Sibling" <?= ($resident['relationship_to_head'] == 'Sibling') ? 'selected' : '' ?>>Sibling</option>
                                    <option value="Relative" <?= ($resident['relationship_to_head'] == 'Relative') ? 'selected' : '' ?>>Relative</option>
                                    <option value="Other" <?= ($resident['relationship_to_head'] == 'Other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </td>
                            <td><input type="number" class="form-control" name="age[]" value="<?= htmlspecialchars($resident['age'] ?? '') ?>" required></td>
                            <td>
                              <select class="form-select" name="gender[]" required>
                                <option value="male" <?= ($resident['sex'] == 'male') ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($resident['sex'] == 'female') ? 'selected' : '' ?>>Female</option>
                              </select>
                            </td>
                            <td>
                              <select class="form-select" name="civil_status[]" required>
                                <option value="Single" <?= ($resident['civil_status'] == 'Single') ? 'selected' : '' ?>>Single</option>
                                <option value="Married" <?= ($resident['civil_status'] == 'Married') ? 'selected' : '' ?>>Married</option>
                                <option value="Widowed" <?= ($resident['civil_status'] == 'Widowed') ? 'selected' : '' ?>>Widowed</option>
                                <option value="Separated" <?= ($resident['civil_status'] == 'Separated') ? 'selected' : '' ?>>Separated</option>
                                <option value="Live-in" <?= ($resident['civil_status'] == 'Live-in') ? 'selected' : '' ?>>Live-in</option>
                              </select>
                            </td>
                            <td><input type="text" class="form-control" name="occupation[]" value="<?= htmlspecialchars($resident['occupation'] ?? '') ?>"></td>
                            <td><input type="text" class="form-control" name="education[]" value="<?= htmlspecialchars($resident['educational_attainment'] ?? '') ?>"></td>
                            <td>
                              <select class="form-select" name="philhealth[]" required>
                                <option value="Yes" <?= !empty($resident['philhealth_number']) ? 'selected' : '' ?>>Yes</option>
                                <option value="No" <?= empty($resident['philhealth_number']) ? 'selected' : '' ?>>No</option>
                              </select>
                            </td>
                            <td>
                              <select class="form-select" name="4ps[]" required>
                                <option value="Yes" <?= !empty($resident['is_4ps_member']) ? 'selected' : '' ?>>Yes</option>
                                <option value="No" <?= empty($resident['is_4ps_member']) ? 'selected' : '' ?>>No</option>
                              </select>
                            </td>
                            <td>
                              <select class="form-select" name="indigent[]" required>
                                <option value="Yes" <?= !empty($resident['is_indigent']) ? 'selected' : '' ?>>Yes</option>
                                <option value="No" <?= empty($resident['is_indigent']) ? 'selected' : '' ?>>No</option>
                              </select>
                            </td>
                            <td><input type="text" class="form-control" name="medical_history[]" value="<?= htmlspecialchars($resident['medical_history'] ?? '') ?>"></td>
                            <td><button type="button" class="btn btn-sm btn-danger removeMember">Remove</button></td>
                          </tr>
                      <?php endforeach; ?>
                  <?php else: ?>
                      <!-- Fallback empty row if no residents -->
                      <tr>
                        <td><input type="text" class="form-control" name="name[]" required></td>
                        <td>
                            <select class="form-select" name="relationship[]">
                                <option value="Head">Head</option>
                                <option value="Spouse">Spouse</option>
                                <option value="Child">Child</option>
                                <option value="Parent">Parent</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Relative">Relative</option>
                                <option value="Other">Other</option>
                            </select>
                        </td>
                        <td><input type="number" class="form-control" name="age[]" required></td>
                        <td>
                          <select class="form-select" name="gender[]" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                          </select>
                        </td>
                        <td>
                          <select class="form-select" name="civil_status[]" required>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                            <option value="Live-in">Live-in</option>
                          </select>
                        </td>
                        <td><input type="text" class="form-control" name="occupation[]"></td>
                        <td><input type="text" class="form-control" name="education[]"></td>
                        <td>
                          <select class="form-select" name="philhealth[]" required>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                          </select>
                        </td>
                        <td>
                          <select class="form-select" name="4ps[]" required>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                          </select>
                        </td>
                        <td>
                          <select class="form-select" name="indigent[]" required>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                          </select>
                        </td>
                        <td><input type="text" class="form-control" name="medical_history[]"></td>
                        <td><button type="button" class="btn btn-sm btn-danger removeMember">Remove</button></td>
                      </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Fixed footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const addMemberBtn = document.getElementById('addMemberBtn');
  const membersTable = document.getElementById('membersTable').querySelector('tbody');
  const existingResidents = document.getElementById('existingResidents');

  // Add new member row
  addMemberBtn.addEventListener('click', function () {
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
      <td><input type="text" class="form-control" name="name[]" required></td>
      <td>
          <select class="form-select" name="relationship[]">
              <option value="Head">Head</option>
              <option value="Spouse">Spouse</option>
              <option value="Child">Child</option>
              <option value="Parent">Parent</option>
              <option value="Sibling">Sibling</option>
              <option value="Relative">Relative</option>
              <option value="Other">Other</option>
          </select>
      </td>
      <td><input type="number" class="form-control" name="age[]" required></td>
      <td>
        <select class="form-select" name="gender[]" required>
          <option value="male">Male</option>
          <option value="female">Female</option>
        </select>
      </td>
      <td>
        <select class="form-select" name="civil_status[]" required>
          <option value="Single">Single</option>
          <option value="Married">Married</option>
          <option value="Widowed">Widowed</option>
          <option value="Separated">Separated</option>
          <option value="Live-in">Live-in</option>
        </select>
      </td>
      <td><input type="text" class="form-control" name="occupation[]"></td>
      <td><input type="text" class="form-control" name="education[]"></td>
      <td>
        <select class="form-select" name="philhealth[]" required>
          <option value="Yes">Yes</option>
          <option value="No">No</option>
        </select>
      </td>
      <td>
        <select class="form-select" name="4ps[]" required>
          <option value="Yes">Yes</option>
          <option value="No">No</option>
        </select>
      </td>
      <td>
        <select class="form-select" name="indigent[]" required>
          <option value="Yes">Yes</option>
          <option value="No">No</option>
        </select>
      </td>
      <td><input type="text" class="form-control" name="medical_history[]"></td>
      <td><button type="button" class="btn btn-sm btn-danger removeMember">Remove</button></td>
    `;
    membersTable.appendChild(newRow);
  });

  // Add existing resident
  existingResidents.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const firstName = selectedOption.getAttribute('data-firstname');
        const lastName = selectedOption.getAttribute('data-lastname');
        
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <input type="hidden" name="resident_id[]" value="${selectedOption.value}">
                <input type="text" class="form-control" name="name[]" value="${firstName} ${lastName}" required readonly>
            </td>
            <td>
                <select class="form-select" name="relationship[]">
                    <option value="Head">Head</option>
                    <option value="Spouse">Spouse</option>
                    <option value="Child">Child</option>
                    <option value="Parent">Parent</option>
                    <option value="Sibling">Sibling</option>
                    <option value="Relative">Relative</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td><input type="number" class="form-control" name="age[]" required></td>
            <td>
                <select class="form-select" name="gender[]" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </td>
            <td>
                <select class="form-select" name="civil_status[]" required>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Widowed">Widowed</option>
                    <option value="Separated">Separated</option>
                    <option value="Live-in">Live-in</option>
                </select>
            </td>
            <td><input type="text" class="form-control" name="occupation[]"></td>
            <td><input type="text" class="form-control" name="education[]"></td>
            <td>
                <select class="form-select" name="philhealth[]" required>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
            <td>
                <select class="form-select" name="4ps[]" required>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
            <td>
                <select class="form-select" name="indigent[]" required>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
            <td><input type="text" class="form-control" name="medical_history[]"></td>
            <td><button type="button" class="btn btn-sm btn-danger removeMember">Remove</button></td>
        `;
        membersTable.appendChild(newRow);
        
        // Reset selection
        this.value = '';
    }
  });

  // Remove member row
  membersTable.addEventListener('click', function(e){
    if(e.target && e.target.classList.contains('removeMember')){
      if (membersTable.querySelectorAll('tr').length > 1) {
        e.target.closest('tr').remove();
      } else {
        alert('You must have at least one household member.');
      }
    }
  });
});
</script>