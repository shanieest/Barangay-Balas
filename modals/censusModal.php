<div class="modal fade" id="updateHouseholdModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Household</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Household No</label>
              <input type="text" class="form-control" value="BL-2023-0456">
            </div>
            <div class="col-md-6">
              <label class="form-label">Purok</label>
              <input type="text" class="form-control" value="2">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" value="123 Balas Street">
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">House Type</label>
              <input type="text" class="form-control" value="Single-detached">
            </div>
            <div class="col-md-6">
              <label class="form-label">Ownership</label>
              <input type="text" class="form-control" value="Owned">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Year Built</label>
            <input type="number" class="form-control" value="2010">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Household Details Modal -->
<div class="modal fade" id="editHouseholdDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Household Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <!-- Fields same as Update Household -->
          <p>Form for editing household details goes here.</p>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Amenities Modal -->
<div class="modal fade" id="editAmenitiesModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Household Amenities</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <label class="form-label">Water Source</label>
          <input type="text" class="form-control mb-2" value="Level III (Piped)">
          <label class="form-label">Electricity</label>
          <input type="text" class="form-control mb-2" value="With Meter">
          <label class="form-label">Internet</label>
          <input type="text" class="form-control mb-2" value="DSL">
          <label class="form-label">Toilet Facility</label>
          <input type="text" class="form-control mb-2" value="Water-sealed">
          <label class="form-label">Waste Disposal</label>
          <input type="text" class="form-control mb-2" value="Garbage Collection">
          <label class="form-label">Vehicle</label>
          <input type="text" class="form-control" value="Motorcycle, Car">
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Household Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="row">
            <div class="col-md-6 mb-2"><input type="text" class="form-control" placeholder="Full Name"></div>
            <div class="col-md-6 mb-2"><input type="text" class="form-control" placeholder="Relationship"></div>
            <div class="col-md-4 mb-2"><input type="number" class="form-control" placeholder="Age"></div>
            <div class="col-md-4 mb-2"><input type="text" class="form-control" placeholder="Gender"></div>
            <div class="col-md-4 mb-2"><input type="text" class="form-control" placeholder="Civil Status"></div>
            <div class="col-md-6 mb-2"><input type="text" class="form-control" placeholder="Occupation"></div>
            <div class="col-md-6 mb-2"><input type="text" class="form-control" placeholder="Education"></div>
            <div class="col-md-6 mb-2">
              <select class="form-select">
                <option>Voter?</option>
                <option>Yes</option>
                <option>No</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-success">Add Member</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Household Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <p>Form for editing member details goes here.</p>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Livelihood Modal -->
<div class="modal fade" id="editLivelihoodModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Livelihood</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <textarea class="form-control" rows="5">Teaching (Public School)
Nursing (Private Hospital)
Sari-sari Store</textarea>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Government Assistance Modal -->
<div class="modal fade" id="editGovAssistModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Government Assistance</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <textarea class="form-control" rows="5">4Ps Beneficiary (2018-2022)
TUPAD (2021)
DSWD Educational Assistance (2022)</textarea>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</div>