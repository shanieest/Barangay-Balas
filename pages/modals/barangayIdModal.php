<?php
// barangayIdModal.php resident
$residentQuery = "SELECT first_name, middle_name, last_name, suffix, birthdate, place_of_birth, contact_number, email 
                  FROM residents WHERE id = ?";
$stmt = $conn->prepare($residentQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$resResult = $stmt->get_result();
$resInfo = $resResult->fetch_assoc();
$stmt->close();

$fullName = trim($resInfo['first_name'] . ' ' .
                ($resInfo['middle_name'] ? $resInfo['middle_name'] . ' ' : '') .
                $resInfo['last_name'] . ' ' .
                ($resInfo['suffix'] ?? ''));
?>
<!-- Barangay ID Application Modal -->
<div class="modal fade" id="barangayIdModal" tabindex="-1" aria-labelledby="barangayIdModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="barangayIdModalLabel">
          <i class="fas fa-id-card me-2"></i>Barangay ID Application
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="barangayIdForm" enctype="multipart/form-data">
          <input type="hidden" name="resident_id" value="<?= htmlspecialchars($userId) ?>">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($fullName) ?>" readonly>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Date of Birth</label>
              <input type="date" class="form-control" value="<?= htmlspecialchars($resInfo['birthdate'] ?? '') ?>" readonly>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Place of Birth</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($resInfo['place_of_birth'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Contact Number</label>
              <input type="tel" class="form-control" value="<?= htmlspecialchars($resInfo['contact_number'] ?? '') ?>" readonly>
              <label class="form-label mt-2">Email Address</label>
              <input type="email" class="form-control" value="<?= htmlspecialchars($resInfo['email'] ?? '') ?>" readonly>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Address</label>
              <textarea class="form-control" rows="2" readonly>Balas, Mexico, Pampanga</textarea>
            </div>
          </div>

          <!-- Formal Photo Upload -->
          <div class="mb-3">
            <label for="formalPhoto" class="form-label">Formal Photo <span class="text-danger">*</span></label>
            <input type="file" class="form-control" id="formalPhoto" name="formalPhoto" accept="image/png, image/jpeg, image/jpg" required>
            <div class="form-text">
              <strong>Requirements:</strong>
              <ul class="small mb-0">
                <li>Recent 2x2 formal photo</li>
                <li>White background</li>
                <li>Professional attire</li>
                <li>Clear and visible face</li>
                <li>Maximum file size: 5MB</li>
                <li>Accepted formats: JPG, JPEG, PNG</li>
              </ul>
            </div>
            <div class="mt-2">
              <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                The photo will be used for your Barangay ID card.
              </small>
            </div>
          </div>

          <!-- Signature Options -->
          <div class="mb-3">
            <label class="form-label">Digital Signature <span class="text-danger">*</span></label>
            <ul class="nav nav-tabs" id="signatureTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="draw-tab" data-bs-toggle="tab" data-bs-target="#drawTab" type="button" role="tab">Draw Signature</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#uploadTab" type="button" role="tab">Upload Signature</button>
              </li>
            </ul>

            <div class="tab-content border border-top-0 rounded-bottom p-3 bg-light" id="signatureTabsContent">
              <!-- Draw Tab -->
              <div class="tab-pane fade show active" id="drawTab" role="tabpanel">
                <canvas id="signatureCanvas" width="500" height="200" style="border: 1px solid #ccc; cursor: crosshair;"></canvas>
                <div class="mt-2">
                  <button type="button" id="clearSignature" class="btn btn-warning btn-sm">Clear</button>
                  <small class="text-muted ms-2">Draw your signature above</small>
                </div>
                <input type="hidden" id="signatureData" name="signatureData">
              </div>

              <!-- Upload Tab -->
              <div class="tab-pane fade" id="uploadTab" role="tabpanel">
                <label for="signatureFile" class="form-label">Upload a signature image (PNG with transparent background)</label>
                <input type="file" class="form-control" id="signatureFile" name="signatureFile" accept="image/png">
                <small class="text-muted">Maximum size: 2MB. Please use PNG format with transparent background for best results.</small>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="additionalNotes" class="form-label">Additional Notes</label>
            <textarea class="form-control" id="additionalNotes" name="additionalNotes" rows="2" placeholder="Any additional information..."></textarea>
          </div>

          <div class="alert alert-warning">
            <small><i class="fas fa-exclamation-triangle me-1"></i>
              Please bring the original copies of your requirements for verification when claiming your Barangay ID.
              Processing time is 3–5 working days.
            </small>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-info" id="submitBarangayId">Submit Application</button>
      </div>
    </div>
  </div>
</div>

<script>
const canvas = document.getElementById('signatureCanvas');
const ctx = canvas.getContext('2d');
let isDrawing = false, lastX = 0, lastY = 0;

// Initialize canvas function
function initializeCanvas() {
    // Clear everything
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Set transparent background
    ctx.fillStyle = 'transparent';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Set drawing styles
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineJoin = 'round';
    ctx.lineCap = 'round';
}

// Initialize canvas when page loads
initializeCanvas();

function startDraw(e){ 
    isDrawing = true; 
    [lastX, lastY] = [e.offsetX, e.offsetY]; 
}

function draw(e){
    if(!isDrawing) return;
    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();
    [lastX, lastY] = [e.offsetX, e.offsetY];
    // Save as PNG with transparent background
    document.getElementById('signatureData').value = canvas.toDataURL('image/png');
}

function stopDraw(){ 
    isDrawing = false; 
}

// Mouse events
canvas.addEventListener('mousedown', startDraw);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDraw);
canvas.addEventListener('mouseout', stopDraw);

// Touch events for mobile devices
canvas.addEventListener('touchstart', (e) => {
    e.preventDefault();
    const touch = e.touches[0];
    const rect = canvas.getBoundingClientRect();
    const mouseEvent = new MouseEvent('mousedown', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
});

canvas.addEventListener('touchmove', (e) => {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousemove', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
});

canvas.addEventListener('touchend', (e) => {
    e.preventDefault();
    const mouseEvent = new MouseEvent('mouseup', {});
    canvas.dispatchEvent(mouseEvent);
});

// Clear signature - FIXED
document.getElementById('clearSignature').addEventListener('click', () => {
    initializeCanvas();
    document.getElementById('signatureData').value = '';
});

// Initialize canvas when modal opens
document.getElementById('barangayIdModal').addEventListener('show.bs.modal', function () {
    initializeCanvas();
    document.getElementById('signatureData').value = '';
});

// Form submission
document.getElementById('submitBarangayId').addEventListener('click', function () {
    const form = document.getElementById('barangayIdForm');
    const drawnSig = document.getElementById('signatureData').value;
    const uploadedFile = document.getElementById('signatureFile').files.length;
    const formalPhoto = document.getElementById('formalPhoto').files.length;

    // Validation
    if (!formalPhoto) {
        alert('Please upload a formal photo.');
        return;
    }

    if (!drawnSig && !uploadedFile) {
        alert('Please provide a signature by drawing or uploading.');
        return;
    }

    // Validate file types and sizes
    const formalPhotoFile = document.getElementById('formalPhoto').files[0];
    if (formalPhotoFile && formalPhotoFile.size > 5 * 1024 * 1024) { // 5MB
        alert('Formal photo file is too large. Maximum size is 5MB.');
        return;
    }

    const signatureFile = document.getElementById('signatureFile').files[0];
    if (signatureFile) {
        if (signatureFile.size > 2 * 1024 * 1024) { // 2MB
            alert('Signature file is too large. Maximum size is 2MB.');
            return;
        }
        // Validate uploaded signature is PNG
        if (signatureFile.type !== 'image/png') {
            alert('Signature file must be in PNG format with transparent background.');
            return;
        }
    }

    // Show loading state
    const submitBtn = document.getElementById('submitBarangayId');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    submitBtn.disabled = true;

    const fd = new FormData(form);
    
    fetch('../residents/barangay_id_process.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Application submitted successfully!');
            const modalEl = document.getElementById('barangayIdModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            form.reset();
            
            // Reset canvas
            initializeCanvas();
            document.getElementById('signatureData').value = '';
            
            // Reload page to show updated application status
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Submission error:', err);
        alert('An error occurred while submitting your application. Please try again.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Tab switching behavior - clear other signature method when switching tabs
document.getElementById('draw-tab').addEventListener('click', function() {
    document.getElementById('signatureFile').value = '';
});

document.getElementById('upload-tab').addEventListener('click', function() {
    document.getElementById('signatureData').value = '';
    initializeCanvas();
});
</script>