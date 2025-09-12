<!-- Request Documents Section -->
 <!-- services.php -->

<section id="documents" class="d-none">
  <h2 class="mb-4">Services</h2>

    <div class="card-body">
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
              <h5>Request Documents</h5>
              <p class="text-muted">Character certification</p>
              <button class="btn btn-primary btn-sm request-btn" 
                      data-bs-toggle="modal" 
                      data-bs-target="#documentRequestModal" 
                      data-document="Documents">
                Request
              </button>
            </div>
          </div>
        </div>

        <!-- Reservation of Service Vehicle -->
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="fas fa-bus fa-3x text-success mb-3"></i>
              <h5>Service Vehicle</h5>
              <p class="text-muted">Reserve a barangay vehicle</p>
              <button class="btn btn-success btn-sm" 
                      data-bs-toggle="modal" 
                      data-bs-target="#vehicleReservationModal">
                Reserve
              </button>
            </div>
          </div>
        </div>

        <!-- Reservation of Tent -->
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="fas fa-campground fa-3x text-warning mb-3"></i>
              <h5>Barangay Tent</h5>
              <p class="text-muted">Reserve a barangay tent</p>
              <button class="btn btn-warning btn-sm" 
                      data-bs-toggle="modal" 
                      data-bs-target="#tentReservationModal">
                Reserve
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<?php include '../../pages/modals/documentsModal.php'; ?>
<?php include '../../pages/modals/vehicleReservationModal.php'; ?>
<?php include '../../pages/modals/tentReservationModal.php'; ?>

<script>

</script>
