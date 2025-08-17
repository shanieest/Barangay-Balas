<!-- Request Documents Section  -->
<section id="documents" class="d-none">
    <h2 class="mb-4">Request Documents</h2>
    <div class="card">
        <div class="card-header">
            <span>Available Documents</span>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Barangay Clearance -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-file-certificate fa-3x text-primary mb-3"></i>
                            <h5>Barangay Clearance</h5>
                            <p class="text-muted">Required for various transactions</p>
                            <button class="btn btn-primary btn-sm request-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#documentRequestModal" 
                                    data-document="Barangay Clearance">
                                Request
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Certificate of Residency -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-home fa-3x text-primary mb-3"></i>
                            <h5>Certificate of Residency</h5>
                            <p class="text-muted">Proof of residency in Barangay Balas</p>
                            <button class="btn btn-primary btn-sm request-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#documentRequestModal" 
                                    data-document="residency">
                                Request
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Barangay ID -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-id-card fa-3x text-primary mb-3"></i>
                            <h5>Barangay ID</h5>
                            <p class="text-muted">Identification card for residents</p>
                            <button class="btn btn-primary btn-sm request-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#documentRequestModal" 
                                    data-document="Barangay ID">
                                Request
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Business Permit -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
                            <h5>Business Permit</h5>
                            <p class="text-muted">Required for operating businesses</p>
                            <button class="btn btn-primary btn-sm request-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#documentRequestModal" 
                                    data-document="Business Permit">
                                Request
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Certificate of Good Moral -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                            <h5>Certificate of Good Moral</h5>
                            <p class="text-muted">Character certification</p>
                            <button class="btn btn-primary btn-sm request-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#documentRequestModal" 
                                    data-document="Certificate of Good Moral">
                                Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'modals/documentsModal.php' ;?>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const requestButtons = document.querySelectorAll(".request-btn");
  const documentTypeInput = document.getElementById("document_type");
  const modalTitle = document.getElementById("documentRequestModalLabel");

  requestButtons.forEach(button => {
    button.addEventListener("click", function () {
      const docType = this.getAttribute("data-document");
      documentTypeInput.value = docType;
      modalTitle.textContent = "Request " + docType;
    });
  });
});
</script>
