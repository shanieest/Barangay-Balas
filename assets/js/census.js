//modaal
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
