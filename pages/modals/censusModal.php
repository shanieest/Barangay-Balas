<!-- Enhanced Household Details Modal with Smart Resident Selection -->
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
            
            <div class="d-flex gap-2 mb-2">
              <button type="button" class="btn btn-sm btn-success" id="addMemberBtn">
                <i class="fas fa-plus"></i> Add New Member
              </button>
              <button type="button" class="btn btn-sm btn-info" id="addExistingBtn">
                <i class="fas fa-search"></i> Add Existing Resident
              </button>
            </div>

            <!-- Smart Resident Search Modal -->
            <div class="modal fade" id="residentSearchModal" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h6 class="modal-title">Search for Existing Resident</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Search Resident</label>
                      <input type="text" class="form-control" id="residentSearch" 
                             placeholder="Type name to search...">
                      <small class="form-text text-muted">Type at least 2 characters</small>
                    </div>
                    <div id="searchResults" class="list-group" style="max-height: 200px; overflow-y: auto;"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Members table -->
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
              <table class="table table-bordered table-sm" id="membersTable">
                <thead class="table-light sticky-top">
                  <tr>
                    <th style="min-width: 150px;">Name</th>
                    <th style="min-width: 120px;">Relationship</th>
                    <th style="min-width: 80px;">Age</th>
                    <th style="min-width: 100px;">Gender</th>
                    <th style="min-width: 120px;">Civil Status</th>
                    <th style="min-width: 120px;">Occupation</th>
                    <th style="min-width: 120px;">Education</th>
                    <th style="min-width: 100px;">PhilHealth</th>
                    <th style="min-width: 80px;">4Ps</th>
                    <th style="min-width: 100px;">Indigent</th>
                    <th style="min-width: 150px;">Medical History</th>
                    <th style="min-width: 80px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($household_members)): ?>
                      <?php foreach ($household_members as $member): ?>
                          <tr data-resident-id="<?= $member['resident_id'] ?>">
                            <td>
                                <input type="hidden" name="member_id[]" value="<?= $member['resident_id'] ?>">
                                <input type="text" class="form-control form-control-sm" name="name[]" 
                                       value="<?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?>" 
                                       readonly style="background-color: #e9ecef;">
                                <small class="text-muted">Existing resident</small>
                            </td>
                            <td>
                                <select class="form-select form-select-sm" name="relationship[]">
                                    <option value="Head" <?= ($member['relationship_to_head'] == 'Head') ? 'selected' : '' ?>>Head</option>
                                    <option value="Spouse" <?= ($member['relationship_to_head'] == 'Spouse') ? 'selected' : '' ?>>Spouse</option>
                                    <option value="Child" <?= ($member['relationship_to_head'] == 'Child') ? 'selected' : '' ?>>Child</option>
                                    <option value="Parent" <?= ($member['relationship_to_head'] == 'Parent') ? 'selected' : '' ?>>Parent</option>
                                    <option value="Sibling" <?= ($member['relationship_to_head'] == 'Sibling') ? 'selected' : '' ?>>Sibling</option>
                                    <option value="Relative" <?= ($member['relationship_to_head'] == 'Relative') ? 'selected' : '' ?>>Relative</option>
                                    <option value="Other" <?= ($member['relationship_to_head'] == 'Other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </td>
                            <td><input type="number" class="form-control form-control-sm" name="age[]" value="<?= htmlspecialchars($member['age'] ?? '') ?>" required></td>
                            <td>
                              <select class="form-select form-select-sm" name="gender[]" required>
                                <option value="male" <?= ($member['sex'] == 'male') ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($member['sex'] == 'female') ? 'selected' : '' ?>>Female</option>
                              </select>
                            </td>
                            <td>
                              <select class="form-select form-select-sm" name="civil_status[]" required>
                                <option value="Single" <?= ($member['civil_status'] == 'Single') ? 'selected' : '' ?>>Single</option>
                                <option value="Married" <?= ($member['civil_status'] == 'Married') ? 'selected' : '' ?>>Married</option>
                                <option value="Widowed" <?= ($member['civil_status'] == 'Widowed') ? 'selected' : '' ?>>Widowed</option>
                                <option value="Separated" <?= ($member['civil_status'] == 'Separated') ? 'selected' : '' ?>>Separated</option>
                                <option value="Live-in" <?= ($member['civil_status'] == 'Live-in') ? 'selected' : '' ?>>Live-in</option>
                              </select>
                            </td>
                            <td><input type="text" class="form-control form-control-sm" name="occupation[]" value="<?= htmlspecialchars($member['occupation'] ?? '') ?>"></td>
                            <td><input type="text" class="form-control form-control-sm" name="education[]" value="<?= htmlspecialchars($member['educational_attainment'] ?? '') ?>"></td>
                            <td>
                              <select class="form-select form-select-sm" name="philhealth[]" required>
                                <option value="Yes" <?= !empty($member['philhealth_number']) ? 'selected' : '' ?>>Yes</option>
                                <option value="No" <?= empty($member['philhealth_number']) ? 'selected' : '' ?>>No</option>
                              </select>
                            </td>
                            <td>
                              <select class="form-select form-select-sm" name="4ps[]" required>
                                <option value="Yes" <?= !empty($member['is_4ps_member']) ? 'selected' : '' ?>>Yes</option>
                                <option value="No" <?= empty($member['is_4ps_member']) ? 'selected' : '' ?>>No</option>
                              </select>
                            </td>
                            <td>
                              <select class="form-select form-select-sm" name="indigent[]" required>
                                <option value="Yes" <?= !empty($member['is_indigent']) ? 'selected' : '' ?>>Yes</option>
                                <option value="No" <?= empty($member['is_indigent']) ? 'selected' : '' ?>>No</option>
                              </select>
                            </td>
                            <td><input type="text" class="form-control form-control-sm" name="medical_history[]" value="<?= htmlspecialchars($member['medical_history'] ?? '') ?>"></td>
                            <td><button type="button" class="btn btn-sm btn-danger removeMember">Remove</button></td>
                          </tr>
                      <?php endforeach; ?>
                  <?php else: ?>
                      <!-- Fallback empty row if no members -->
                      <tr>
                        <td>
                            <input type="hidden" name="member_id[]" value="">
                            <input type="text" class="form-control form-control-sm" name="name[]" required>
                        </td>
                        <td>
                            <select class="form-select form-select-sm" name="relationship[]">
                                <option value="Head">Head</option>
                                <option value="Spouse">Spouse</option>
                                <option value="Child">Child</option>
                                <option value="Parent">Parent</option>
                                <option value="Sibling">Sibling</option>
                                <option value="Relative">Relative</option>
                                <option value="Other">Other</option>
                            </select>
                        </td>
                        <td><input type="number" class="form-control form-control-sm" name="age[]" required></td>
                        <td>
                          <select class="form-select form-select-sm" name="gender[]" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                          </select>
                        </td>
                        <td>
                          <select class="form-select form-select-sm" name="civil_status[]" required>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                            <option value="Live-in">Live-in</option>
                          </select>
                        </td>
                        <td><input type="text" class="form-control form-control-sm" name="occupation[]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="education[]"></td>
                        <td>
                          <select class="form-select form-select-sm" name="philhealth[]" required>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                          </select>
                        </td>
                        <td>
                          <select class="form-select form-select-sm" name="4ps[]" required>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                          </select>
                        </td>
                        <td>
                          <select class="form-select form-select-sm" name="indigent[]" required>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                          </select>
                        </td>
                        <td><input type="text" class="form-control form-control-sm" name="medical_history[]"></td>
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
document.addEventListener('DOMContentLoaded', function() {
    let searchTimeout;
    
    // Add new member functionality
    document.getElementById('addMemberBtn').addEventListener('click', function() {
        addNewMemberRow();
    });

    // Add existing resident functionality
    document.getElementById('addExistingBtn').addEventListener('click', function() {
        const searchModal = new bootstrap.Modal(document.getElementById('residentSearchModal'));
        searchModal.show();
        document.getElementById('residentSearch').focus();
    });

    // Smart resident search with debouncing
    document.getElementById('residentSearch').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                searchResidents(query);
            }, 300); // 300ms debounce
        } else {
            document.getElementById('searchResults').innerHTML = '';
        }
    });

    // Remove member functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('removeMember')) {
            const tableBody = document.querySelector('#membersTable tbody');
            if (tableBody.children.length > 1) {
                e.target.closest('tr').remove();
            } else {
                alert('You must have at least one household member.');
            }
        }
    });

    // Handle resident selection from search results
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('resident-search-item')) {
            const residentData = JSON.parse(e.target.dataset.resident);
            addExistingResidentRow(residentData);
            
            // Close search modal
            const searchModal = bootstrap.Modal.getInstance(document.getElementById('residentSearchModal'));
            searchModal.hide();
            
            // Clear search
            document.getElementById('residentSearch').value = '';
            document.getElementById('searchResults').innerHTML = '';
        }
    });

    function addNewMemberRow() {
        const tableBody = document.querySelector('#membersTable tbody');
        const newRow = document.createElement('tr');
        
        newRow.innerHTML = `
            <td>
                <input type="hidden" name="member_id[]" value="">
                <input type="text" class="form-control form-control-sm" name="name[]" required>
            </td>
            <td>
                <select class="form-select form-select-sm" name="relationship[]">
                    <option value="Head">Head</option>
                    <option value="Spouse">Spouse</option>
                    <option value="Child">Child</option>
                    <option value="Parent">Parent</option>
                    <option value="Sibling">Sibling</option>
                    <option value="Relative">Relative</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td><input type="number" class="form-control form-control-sm" name="age[]" required></td>
            <td>
                <select class="form-select form-select-sm" name="gender[]" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm" name="civil_status[]" required>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Widowed">Widowed</option>
                    <option value="Separated">Separated</option>
                    <option value="Live-in">Live-in</option>
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm" name="occupation[]"></td>
            <td><input type="text" class="form-control form-control-sm" name="education[]"></td>
            <td>
                <select class="form-select form-select-sm" name="philhealth[]" required>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm" name="4ps[]" required>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm" name="indigent[]" required>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm" name="medical_history[]"></td>
            <td><button type="button" class="btn btn-sm btn-danger removeMember">Remove</button></td>
        `;
        
        tableBody.appendChild(newRow);
    }

    function addExistingResidentRow(residentData) {
        // Check if resident is already in the table
        const existingRows = document.querySelectorAll('#membersTable tbody tr[data-resident-id="' + residentData.id + '"]');
        if (existingRows.length > 0) {
            alert('This resident is already added to the household.');
            return;
        }

        const tableBody = document.querySelector('#membersTable tbody');
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-resident-id', residentData.id);
        
        newRow.innerHTML = `
            <td>
                <input type="hidden" name="member_id[]" value="${residentData.id}">
                <input type="hidden" name="existing_member[]" value="${residentData.id}">
                <input type="text" class="form-control form-control-sm" name="name[]" 
                       value="${residentData.text}" readonly style="background-color: #e9ecef;">
                <small class="text-muted">${residentData.address}</small>
            </td>
            <td>
                <select class="form-select form-select-sm" name="relationship[]">
                    <option value="Head">Head</option>
                    <option value="Spouse">Spouse</option>
                    <option value="Child">Child</option>
                    <option value="Parent">Parent</option>
                    <option value="Sibling">Sibling</option>
                    <option value="Relative">Relative</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td><input type="number" class="form-control form-control-sm" name="age[]" required></td>
            <td>
                <select class="form-select form-select-sm" name="gender[]" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm" name="civil_status[]" required>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Widowed">Widowed</option>
                    <option value="Separated">Separated</option>
                    <option value="Live-in">Live-in</option>
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm" name="occupation[]"></td>
            <td><input type="text" class="form-control form-control-sm" name="education[]"></td>
            <td>
                <select class="form-select form-select-sm" name="philhealth[]" required>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm" name="4ps[]" required>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm" name="indigent[]" required>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm" name="medical_history[]"></td>
            <td><button type="button" class="btn btn-sm btn-danger removeMember">Remove</button></td>
        `;
        
        tableBody.appendChild(newRow);
    }

    function searchResidents(query) {
        fetch(`census-backend.php?action=search_residents&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                const resultsContainer = document.getElementById('searchResults');
                resultsContainer.innerHTML = '';
                
                if (data.length === 0) {
                    resultsContainer.innerHTML = '<div class="text-muted p-3">No residents found</div>';
                    return;
                }
                
                data.forEach(resident => {
                    const resultItem = document.createElement('button');
                    resultItem.className = 'list-group-item list-group-item-action resident-search-item';
                    resultItem.dataset.resident = JSON.stringify(resident);
                    resultItem.innerHTML = `
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${resident.text}</h6>
                            <small>ID: ${resident.id}</small>
                        </div>
                        <small class="text-muted">${resident.address}</small>
                    `;
                    resultsContainer.appendChild(resultItem);
                });
            })
            .catch(error => {
                console.error('Search error:', error);
                document.getElementById('searchResults').innerHTML = 
                    '<div class="text-danger p-3">Search failed. Please try again.</div>';
            });
    }
});
</script>