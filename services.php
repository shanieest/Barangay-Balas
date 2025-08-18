<!-- Request Documents Section -->
<section id="documents" class="d-none">
  <h2 class="mb-4">Services</h2>
  
  <div class="card">
    <div class="card-header">
      <span>Request Documents</span>
    </div>
    <div class="card-body">
      <div class="row">
        <!-- Certificate of Good Moral -->
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
              <h5>Request Documents</h5>
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
        <!-- add more col-md-4 for other document types -->
      </div>
    </div>
  </div>
</section>

<?php include 'modals/documentsModal.php'; ?>

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
