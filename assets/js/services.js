// services.js - Enhanced reservation functionality with vehicle type selection

document.addEventListener("DOMContentLoaded", function () {
  const requestButtons = document.querySelectorAll(".request-btn");
  const documentTypeInput = document.getElementById("document_type");
  const modalTitle = document.getElementById("documentRequestModalLabel");

  requestButtons.forEach(button => {
    button.addEventListener("click", function () {
      const docType = this.getAttribute("data-document");
      if (documentTypeInput) {
        documentTypeInput.value = docType;
      }
      if (modalTitle) {
        modalTitle.textContent = "Request " + docType;
      }
    });
  });

  // Initialize form handlers
  initializeFormHandlers();
  initializeQuantityControls();
  initializeVehicleTypeSelection();
});

// Initialize vehicle type selection
function initializeVehicleTypeSelection() {
  const vehicleTypeSelect = document.getElementById('vehicleType');
  const vehicleServiceInput = document.getElementById('vehicleServiceInput');
  
  if (vehicleTypeSelect && vehicleServiceInput) {
    vehicleTypeSelect.addEventListener('change', function() {
      const selectedValue = this.value;
      vehicleServiceInput.value = selectedValue;
    });
  }
}

// Initialize quantity controls
function initializeQuantityControls() {
  // Service checkboxes and quantity controls mapping
  const services = [
    { checkbox: 'tentService', quantityControl: 'tentQuantityControl', quantityInput: 'tent_qty' },
    { checkbox: 'tablesChairsService', quantityControl: 'tablesChairsQuantityControl', quantityInput: 'tables_chairs_qty' },
    { checkbox: 'soundSystemService', quantityControl: 'soundSystemQuantityControl', quantityInput: 'sound_system_qty' }
  ];

  // Set up checkbox event listeners
  services.forEach(service => {
    const checkbox = document.getElementById(service.checkbox);
    const quantityControl = document.getElementById(service.quantityControl);
    const quantityInput = document.getElementById(service.quantityInput);
    
    if (checkbox && quantityControl) {
      checkbox.addEventListener('change', function() {
        if (this.checked) {
          quantityControl.style.display = 'block';
          // Ensure quantity input has a valid value
          if (quantityInput && (!quantityInput.value || quantityInput.value < 1)) {
            quantityInput.value = 1;
          }
        } else {
          quantityControl.style.display = 'none';
          // Reset quantity to 1 when unchecked (but keep the value for potential re-checking)
          if (quantityInput) {
            quantityInput.value = 1;
          }
        }
      });
    }
  });
}

// Initialize form handlers
function initializeFormHandlers() {
    // Vehicle reservation form
    const vehicleForm = document.getElementById('vehicleReservationForm');
    if (vehicleForm) {
        vehicleForm.addEventListener('submit', handleVehicleReservation);
        setupVehicleDateValidation(vehicleForm);
    }

    // Tent reservation form
    const tentForm = document.getElementById('tentReservationForm');
    if (tentForm) {
        tentForm.addEventListener('submit', handleTentReservation);
        setupDateValidation(tentForm);
    }
}

// Set up date validation specifically for vehicle forms (2-day limit)
function setupVehicleDateValidation(form) {
    const startDate = form.querySelector('[name="reservation_date_start"]');
    const endDate = form.querySelector('[name="reservation_date_end"]');
    const durationMessage = document.createElement('div');
    durationMessage.className = 'text-danger small mt-1 d-none';
    durationMessage.id = 'vehicleDurationMessage';
    
    if (startDate && endDate) {
        // Insert duration message after end date input
        endDate.parentNode.appendChild(durationMessage);
        
        const validateDuration = () => {
            if (startDate.value && endDate.value) {
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);
                const durationDays = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                
                if (durationDays > 2) {
                    durationMessage.textContent = `Vehicle reservations cannot exceed 2 days (${durationDays} days selected)`;
                    durationMessage.classList.remove('d-none');
                    endDate.setCustomValidity('Vehicle reservations cannot exceed 2 days');
                } else {
                    durationMessage.textContent = `Duration: ${durationDays} day(s)`;
                    durationMessage.className = 'text-success small mt-1';
                    durationMessage.classList.remove('d-none');
                    endDate.setCustomValidity('');
                }
            } else {
                durationMessage.classList.add('d-none');
                endDate.setCustomValidity('');
            }
        };
        
        startDate.addEventListener('change', validateDuration);
        endDate.addEventListener('change', validateDuration);
    }
    
    if (startDate) {
        // Set minimum date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowString = tomorrow.toISOString().split('T')[0];
        startDate.setAttribute('min', tomorrowString);
        
        if (endDate) {
            endDate.setAttribute('min', tomorrowString);
            
            startDate.addEventListener('change', function() {
                endDate.min = this.value;
                if (endDate.value && endDate.value < this.value) {
                    endDate.value = this.value;
                }
                if (typeof validateDuration === 'function') {
                    validateDuration();
                }
            });
        }
    }
}

// Set up date validation for general forms (10-day limit)
function setupDateValidation(form) {
    const startDate = form.querySelector('[name="reservation_date_start"]');
    const endDate = form.querySelector('[name="reservation_date_end"]');
    const durationMessage = document.createElement('div');
    durationMessage.className = 'text-danger small mt-1 d-none';
    durationMessage.id = 'durationMessage';
    
    if (startDate && endDate) {
        // Insert duration message after end date input
        endDate.parentNode.appendChild(durationMessage);
        
        const validateDuration = () => {
            if (startDate.value && endDate.value) {
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);
                const durationDays = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
                
                if (durationDays > 10) {
                    durationMessage.textContent = `Duration exceeds 10 days (${durationDays} days selected)`;
                    durationMessage.classList.remove('d-none');
                    endDate.setCustomValidity('Duration cannot exceed 10 days');
                } else {
                    durationMessage.textContent = `Duration: ${durationDays} day(s)`;
                    durationMessage.className = 'text-success small mt-1';
                    durationMessage.classList.remove('d-none');
                    endDate.setCustomValidity('');
                }
            } else {
                durationMessage.classList.add('d-none');
                endDate.setCustomValidity('');
            }
        };
        
        startDate.addEventListener('change', validateDuration);
        endDate.addEventListener('change', validateDuration);
    }
    
    if (startDate) {
        // Set minimum date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowString = tomorrow.toISOString().split('T')[0];
        startDate.setAttribute('min', tomorrowString);
        
        if (endDate) {
            endDate.setAttribute('min', tomorrowString);
            
            startDate.addEventListener('change', function() {
                endDate.min = this.value;
                if (endDate.value && endDate.value < this.value) {
                    endDate.value = this.value;
                }
                if (typeof validateDuration === 'function') {
                    validateDuration();
                }
            });
        }
    }
}

// Validate reservation duration with type-specific limits
function validateReservationDuration(startDate, endDate, serviceTypes = []) {
    if (!startDate) return false;
    
    const start = new Date(startDate);
    const end = new Date(endDate || startDate);
    const durationDays = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
    
    // Check if this is a vehicle reservation
    const vehicleServiceTypes = [2, 5, 6]; // Patrol Car, Van, Motorcycle
    const isVehicleReservation = serviceTypes.some(type => vehicleServiceTypes.includes(parseInt(type)));
    
    const maxDays = isVehicleReservation ? 2 : 10;
    
    return {
        isValid: durationDays <= maxDays,
        days: durationDays,
        maxDays: maxDays,
        isVehicle: isVehicleReservation
    };
}

// Quantity change function
function changeQuantity(inputId, change) {
  const input = document.getElementById(inputId);
  if (input) {
    let currentValue = parseInt(input.value) || 1;
    let newValue = currentValue + change;
    let min = parseInt(input.getAttribute('min')) || 1;
    let max = parseInt(input.getAttribute('max')) || 100;
    
    if (newValue >= min && newValue <= max) {
      input.value = newValue;
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

// Submit reservation form with automatic resident data
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
    
    // Check if resident is logged in (hidden inputs should be present)
    const residentId = formData.get('resident_id');
    const firstName = formData.get('first_name');
    const lastName = formData.get('last_name');
    
    if (!residentId || !firstName || !lastName) {
        showMessage(messageElement, 'You must be logged in to make a reservation.', 'danger');
        resetSubmitButton(submitBtn, originalBtnText);
        return;
    }
    
    // Get service types for validation
    const serviceTypes = formData.getAll('service_types[]');
    
    // Validate duration before submission
    const startDate = formData.get('reservation_date_start');
    const endDate = formData.get('reservation_date_end') || startDate;
    const durationValidation = validateReservationDuration(startDate, endDate, serviceTypes);
    
    if (!durationValidation.isValid) {
        const serviceType = durationValidation.isVehicle ? 'Vehicle' : 'Service';
        showMessage(messageElement, `${serviceType} reservations cannot exceed ${durationValidation.maxDays} days. You selected ${durationValidation.days} days. Please adjust your dates.`, 'danger');
        resetSubmitButton(submitBtn, originalBtnText);
        return;
    }
    
    // Check if at least one service is selected
    if (serviceTypes.length === 0) {
        showMessage(messageElement, 'Please select at least one service.', 'danger');
        resetSubmitButton(submitBtn, originalBtnText);
        return;
    }
    
    // Combine names for resident_name field
    formData.append('resident_name', `${firstName} ${lastName}`);
    
    // Ensure all quantity fields are present with valid values
    const quantityFields = ['tent_qty', 'tables_chairs_qty', 'sound_system_qty', 'vehicle_qty'];
    quantityFields.forEach(field => {
        if (!formData.has(field)) {
            formData.append(field, '1');
        } else {
            const value = parseInt(formData.get(field)) || 1;
            formData.set(field, Math.max(1, value).toString());
        }
    });
    
    // Add action if not present
    if (!formData.has('action')) {
        formData.append('action', 'create_reservation');
    }
    
    // Handle time fields for vehicle reservations (add to notes)
    if (form.id === 'vehicleReservationForm') {
        const startTime = formData.get('start_time');
        const endTime = formData.get('end_time');
        let notes = '';
        
        if (startTime) notes += `Start Time: ${startTime}\n`;
        if (endTime) notes += `End Time: ${endTime}\n`;
        
        if (notes) {
            formData.set('notes', notes);
        }
    }
    
    fetch('../../pages/residents/reservation-backend.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        showMessage(messageElement, data.message, data.success ? 'success' : 'danger');
        
        if (data.success) {
            // Reset form after successful submission
            setTimeout(() => {
                resetForm(form);
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
        resetSubmitButton(submitBtn, originalBtnText);
    });
}

// Reset form and quantity controls
function resetForm(form) {
    // Don't reset the entire form since we want to keep resident data
    // Instead, reset only the user-input fields
    
    if (form.id === 'tentReservationForm') {
        // Reset tent form specific fields
        const fieldsToReset = [
            'reservation_date_start', 'reservation_date_end', 
            'setup_time', 'purpose', 'event_location'
        ];
        
        fieldsToReset.forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                if (fieldName === 'duration_type') {
                    field.value = 'full_day';
                } else if (fieldName === 'event_location') {
                    // Keep the pre-filled address
                } else {
                    field.value = '';
                }
            }
        });
        
        // Reset service checkboxes and quantities
        const services = [
            { checkbox: 'tentService', quantityControl: 'tentQuantityControl', quantityInput: 'tent_qty' },
            { checkbox: 'tablesChairsService', quantityControl: 'tablesChairsQuantityControl', quantityInput: 'tables_chairs_qty' },
            { checkbox: 'soundSystemService', quantityControl: 'soundSystemQuantityControl', quantityInput: 'sound_system_qty' }
        ];
        
        services.forEach(service => {
            const checkbox = document.getElementById(service.checkbox);
            const quantityControl = document.getElementById(service.quantityControl);
            const quantityInput = document.getElementById(service.quantityInput);
            
            if (checkbox) checkbox.checked = false;
            if (quantityControl) quantityControl.style.display = 'none';
            if (quantityInput) quantityInput.value = 1;
        });
        
    } else if (form.id === 'vehicleReservationForm') {
        // Reset vehicle form specific fields
        const fieldsToReset = [
            'reservation_date_start', 'reservation_date_end',
            'start_time', 'end_time', 'purpose', 'vehicleType'
        ];
        
        fieldsToReset.forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                if (fieldName === 'vehicleType') {
                    field.value = '2'; // Default to Patrol Car
                } else {
                    field.value = '';
                }
            }
        });
    }
    
    // Hide duration message
    const durationMessage = form.querySelector('#durationMessage, #vehicleDurationMessage');
    if (durationMessage) {
        durationMessage.classList.add('d-none');
    }
}

// Reset submit button state
function resetSubmitButton(button, originalText) {
    button.disabled = false;
    button.innerHTML = originalText;
}

// Show message in alert
function showMessage(element, message, type) {
    element.className = `alert alert-${type}`;
    element.innerHTML = message;
    element.classList.remove('d-none');
    
    // Auto-hide success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            element.classList.add('d-none');
        }, 5000);
    }
}

// Global function to make changeQuantity available to onclick handlers
window.changeQuantity = changeQuantity;
window.validateReservationDuration = validateReservationDuration;