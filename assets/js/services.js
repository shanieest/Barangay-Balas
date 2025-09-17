//services.js - Simplified reservation functionality without availability checking

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

  // Initialize form handlers
  initializeFormHandlers();
});

// Initialize form handlers
function initializeFormHandlers() {
    // Vehicle reservation form
    const vehicleForm = document.getElementById('vehicleReservationForm');
    if (vehicleForm) {
        vehicleForm.addEventListener('submit', handleVehicleReservation);
        
        // Date validation
        const startDate = vehicleForm.querySelector('[name="reservation_date_start"]');
        const endDate = vehicleForm.querySelector('[name="reservation_date_end"]');
        
        if (startDate && endDate) {
            startDate.addEventListener('change', function() {
                endDate.min = this.value;
                if (endDate.value && endDate.value < this.value) {
                    endDate.value = this.value;
                }
            });
        }
    }

    // Tent reservation form
    const tentForm = document.getElementById('tentReservationForm');
    if (tentForm) {
        tentForm.addEventListener('submit', handleTentReservation);
        
        // Date validation
        const startDate = tentForm.querySelector('[name="reservation_date_start"]');
        const endDate = tentForm.querySelector('[name="reservation_date_end"]');
        
        if (startDate && endDate) {
            startDate.addEventListener('change', function() {
                endDate.min = this.value;
                if (endDate.value && endDate.value < this.value) {
                    endDate.value = this.value;
                }
            });
        }
    }
}

// Handle vehicle reservation submission
function handleVehicleReservation(e) {
    e.preventDefault();
    submitReservation(this, 'vehicleReservationMessage', 'Vehicle');
}

// Handle tent reservation submission
function handleTentReservation(e) {
    e.preventDefault();
    submitReservation(this, 'tentReservationMessage', 'Tent');
}

// Submit reservation form
function submitReservation(form, messageElementId, serviceTypeName) {
    const messageElement = document.getElementById(messageElementId);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
    
    // Clear previous messages
    messageElement.classList.add('d-none');
    
    // Prepare form data
    const formData = new FormData(form);
    
    // Combine names
    const firstName = formData.get('first_name');
    const lastName = formData.get('last_name');
    formData.append('resident_name', `${firstName} ${lastName}`);
    
    // Convert FormData to URLSearchParams for proper encoding
    const urlParams = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        urlParams.append(key, value);
    }
    
    fetch('../../pages/residents/reservation-backend.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: urlParams.toString()
    })
    .then(response => response.json())
    .then(data => {
        showMessage(messageElement, data.message, data.success ? 'success' : 'danger');
        
        if (data.success) {
            // Reset form after successful submission
            setTimeout(() => {
                form.reset();
                const modal = form.closest('.modal');
                if (modal) {
                    const bootstrapModal = bootstrap.Modal.getInstance(modal);
                    if (bootstrapModal) {
                        bootstrapModal.hide();
                    }
                }
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Submission error:', error);
        showMessage(messageElement, 'An error occurred while submitting your reservation. Please try again.', 'danger');
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
}

// Show message in alert
function showMessage(element, message, type) {
    element.className = `alert alert-${type}`;
    element.innerHTML = message;
    element.classList.remove('d-none');
}