<?php
require_once '../../config/db.php';

// Get available medicines from inventory
$medicines = $conn->query("SELECT id, medicine_name, category, stock_quantity FROM medicine_inventory WHERE is_active = 1 AND stock_quantity > 0 ORDER BY medicine_name ASC");

// Get resident data if logged in
$resident_data = null;
if (!isset($_SESSION['user_id']) || !isset($_SESSION['resident_id'])) {
    $resident_sql = "SELECT r.*, ra.email as account_email 
                    FROM residents r 
                    LEFT JOIN resident_accounts ra ON r.id = ra.resident_id 
                    WHERE r.id = ?";
    $resident_stmt = $conn->prepare($resident_sql);
    $resident_stmt->bind_param("i", $_SESSION['user_id']);
    $resident_stmt->execute();
    $resident_result = $resident_stmt->get_result();
    
    if ($resident_result->num_rows > 0) {
        $resident_data = $resident_result->fetch_assoc();
    }
    $resident_stmt->close();
}
?>

<!-- Medicine Request Modal resident-->
<div class="modal fade" id="medicineRequestModal" tabindex="-1" aria-labelledby="medicineRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="medicineRequestModalLabel">
                    <i class="fas fa-pills me-2"></i>Medicine Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="medicineRequestForm" method="POST">
                <div class="modal-body">
                    <div id="medicineRequestMessage" class="alert d-none"></div>
                    
                    <!-- Resident Information Alert -->
                    <?php if ($resident_data): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-user me-2"></i>
                        <strong>Requesting as:</strong> <?= htmlspecialchars($resident_data['first_name'] . ' ' . $resident_data['last_name']) ?>
                        <br><small>Your information is pre-filled from your account.</small>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Account Required:</strong> You need to be logged in to request medicine. Please log in or create an account first.
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($resident_data): ?>
                    <!-- Medicine Selection -->
                    <div class="mb-3">
                        <label for="medicine_id" class="form-label">Medicine Name <span class="text-danger">*</span></label>
                        <select class="form-select" name="medicine_id" id="medicine_id" required>
                            <option value="">-- Select Medicine --</option>
                            <?php while ($medicine = $medicines->fetch_assoc()): ?>
                                <option value="<?= $medicine['id'] ?>" 
                                        data-stock="<?= $medicine['stock_quantity'] ?>"
                                        data-category="<?= htmlspecialchars($medicine['category']) ?>">
                                    <?= htmlspecialchars($medicine['medicine_name']) ?> 
                                    (<?= htmlspecialchars($medicine['category']) ?>)
                                    - Stock: <?= $medicine['stock_quantity'] ?>
                                </option>
                            <?php endwhile; ?>
                            <option value="other">Other Medicine (Please specify in notes)</option>
                        </select>
                        <div class="form-text" id="medicineStockInfo"></div>
                    </div>

                    <div class="mb-3">
                        <label for="medical_condition" class="form-label">Medical Condition <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="medical_condition" id="medical_condition" rows="3" required 
                                  placeholder="Describe the medical condition or symptoms..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="urgency_level" class="form-label">Urgency Level <span class="text-danger">*</span></label>
                        <select class="form-select" name="urgency_level" id="urgency_level" required>
                            <option value="">-- Select urgency level --</option>
                            <option value="low">Low - Routine medication</option>
                            <option value="medium">Medium - Needed within 2-3 days</option>
                            <option value="high">High - Urgent need</option>
                            <option value="emergency">Emergency - Immediate need</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="prescription" class="form-label">Prescription (if any)</label>
                        <input type="file" class="form-control" name="prescription" id="prescription" 
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <div class="form-text">Accepted formats: JPG, PNG, PDF, DOC (Max: 5MB)</div>
                    </div>

                    <div class="mb-3">
                        <label for="additional_notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" name="additional_notes" id="additional_notes" rows="2" 
                                  placeholder="Any additional information, dosage requirements, or specify other medicine..."></textarea>
                    </div>

                    <!-- Hidden inputs for resident data -->
                    <input type="hidden" name="resident_id" value="<?= $resident_data['id'] ?>">
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <?php if ($resident_data): ?>
                    <button type="submit" class="btn btn-danger" id="submitMedicineRequest">
                        <i class="fas fa-paper-plane me-1"></i>Submit Request
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn btn-danger" disabled>
                        <i class="fas fa-lock me-1"></i>Login Required
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const medicineRequestForm = document.getElementById("medicineRequestForm");
    const messageDiv = document.getElementById("medicineRequestMessage");
    const medicineSelect = document.getElementById("medicine_id");
    const stockInfo = document.getElementById("medicineStockInfo");

    // Update stock information when medicine is selected
    if (medicineSelect) {
        medicineSelect.addEventListener("change", function() {
            const selectedOption = this.options[this.selectedIndex];
            const stock = selectedOption.getAttribute('data-stock');
            const category = selectedOption.getAttribute('data-category');
            
            if (stock && category) {
                stockInfo.innerHTML = `<i class="fas fa-boxes me-1"></i>Category: ${category} | Stock Available: ${stock} units`;
                stockInfo.className = 'form-text text-success';
            } else if (this.value === 'other') {
                stockInfo.innerHTML = '<i class="fas fa-info-circle me-1"></i>Please specify the medicine name in the additional notes below';
                stockInfo.className = 'form-text text-info';
            } else {
                stockInfo.innerHTML = '';
            }
        });
    }

    // Handle form submission with AJAX (only if resident is logged in)
    <?php if ($resident_data): ?>
    if (medicineRequestForm) {
        medicineRequestForm.addEventListener("submit", function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = document.getElementById('submitMedicineRequest');
            
            // Clear any existing messages
            messageDiv.className = 'alert d-none';
            messageDiv.textContent = '';
            
            // Validate medicine selection
            const medicineId = medicineSelect.value;
            if (!medicineId) {
                showMessage('Please select a medicine.', 'danger');
                return;
            }

            // Validate medical condition
            const medicalCondition = document.getElementById('medical_condition').value.trim();
            if (!medicalCondition) {
                showMessage('Please describe your medical condition.', 'danger');
                return;
            }

            // Validate urgency level
            const urgencyLevel = document.getElementById('urgency_level').value;
            if (!urgencyLevel) {
                showMessage('Please select an urgency level.', 'danger');
                return;
            }

            // Validate file size if file is selected
            const prescriptionFile = document.getElementById('prescription').files[0];
            if (prescriptionFile && prescriptionFile.size > 5 * 1024 * 1024) {
                showMessage('File size too large. Maximum size is 5MB.', 'danger');
                return;
            }
            
            // Disable submit button to prevent double submission
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
            
            // Use the correct path for the AJAX request
            fetch('../residents/medicineRequest.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    
                    // Reset form after a short delay
                    setTimeout(() => {
                        medicineRequestForm.reset();
                        if (stockInfo) {
                            stockInfo.innerHTML = '';
                        }
                        // Close modal after showing success
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('medicineRequestModal'));
                            if (modal) {
                                modal.hide();
                            }
                            // Reload page to show updated request status
                            window.location.reload();
                        }, 1500);
                    }, 1000);
                    
                } else {
                    showMessage(data.message || 'Failed to submit request. Please try again.', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while submitting the request. Please try again.', 'danger');
            })
            .finally(() => {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Request';
            });
        });
    }
    <?php endif; ?>

    function showMessage(message, type) {
        messageDiv.className = `alert alert-${type}`;
        messageDiv.textContent = message;
        messageDiv.classList.remove('d-none');
        
        // Scroll to message
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
</script>