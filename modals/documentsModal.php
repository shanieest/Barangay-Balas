<div class="modal fade" id="documentRequestModal" tabindex="-1" aria-labelledby="documentRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #0033cc; color: white;">
        <h5 class="modal-title" id="documentRequestModalLabel">Document Request Form</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="documentRequests" action="/balas-2.0/admin/documents/documents_request.php" method="POST">
          <div class="row g-3">

            <!-- Document Type -->
            <div class="col-md-12">
              <label for="document_type" class="form-label">Document Type *</label>
              <select name="document_type" id="document_type" class="form-select" onchange="toggleOther(this.value)" required>
                <option value="">Select Document</option>
                <option value="Indigency">Indigency</option>
                <option value="Barangay Clearance">Barangay Clearance</option>
                <option value="Business Permit">Business Permit</option>
                <option value="Other">Other (Please Specify)</option>
              </select>
            </div>

            <div class="col-md-12" id="other_doc_div" style="display:none;">
              <input type="text" name="other_document" id="other_document" class="form-control" placeholder="Enter document name">
            </div>

            <!-- Name Fields -->
            <div class="col-md-4">
              <label for="first_name" class="form-label">First Name *</label>
              <input name="first_name" id="first_name" type="text" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label for="middle_name" class="form-label">Middle Name</label>
              <input name="middle_name" id="middle_name" type="text" class="form-control">
            </div>
            <div class="col-md-4">
              <label for="last_name" class="form-label">Last Name *</label>
              <input name="last_name" id="last_name" type="text" class="form-control" required>
            </div>

            <!-- Address -->
            <div class="col-md-4">
              <label for="houseno" class="form-label">House No. *</label>
              <input name="houseno" id="houseno" type="text" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label for="purok" class="form-label">Purok *</label>
              <input name="purok" id="purok" type="text" class="form-control" required>
            </div>

            <!-- Personal Info -->
            <div class="col-md-4">
              <label for="civil_status" class="form-label">Civil Status *</label>
              <input name="civil_status" id="civil_status" type="text" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label for="sex" class="form-label">Sex *</label>
              <select name="sex" id="sex" class="form-select" required>
                <option value="">Select Sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>

            <!-- Birthdate & Age -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="birthdate" class="form-label">Birthdate *</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                  <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="age" class="form-label">Age</label>
                <input type="number" class="form-control" id="age" name="age" readonly>
              </div>
            </div>

            <!-- Purpose -->
            <div class="col-md-12">
              <label for="purpose" class="form-label">Purpose *</label>
              <input name="purpose" id="purpose" type="text" class="form-control" required>
            </div>

            <!-- Email and Shipping -->
            <div class="col-md-6">
              <label for="email" class="form-label">Email *</label>
              <input name="email" id="email" type="email" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="shipping_method" class="form-label">Shipping Method *</label>
              <select name="shipping_method" id="shipping_method" class="form-select" required>
                <option value="">Select Shipping Method</option>
                <option value="Pick Up">Pick Up (Claim Anytime)</option>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn" style="background-color: #990000; color: white;">Send Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function toggleOther(val) {
  document.getElementById('other_doc_div').style.display = (val === 'Other') ? 'block' : 'none';
}

document.getElementById("birthdate").addEventListener("change", function() {
  const birthdate = new Date(this.value);
  const today = new Date();
  let age = today.getFullYear() - birthdate.getFullYear();
  const m = today.getMonth() - birthdate.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birthdate.getDate())) {
    age--;
  }
  document.getElementById("age").value = age >= 0 ? age : "";
});
</script>
