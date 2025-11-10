// Profile.js - Complete version with all functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile page initialized');
    
    // Initialize all functionality
    initializeSidebar();
    initializeAlerts();
    initializePhotoUpload();
    initializeAddressManagement();
    initializeFormValidation();
    initializePhoneFormatting();
});

// Sidebar functionality
function initializeSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const wrapper = document.querySelector('.wrapper');
            if (wrapper) {
                wrapper.classList.toggle('sidebar-collapsed');
                console.log('Sidebar toggled');
            }
        });
    }
}

// Auto-dismiss alerts
function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert && alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
}

// Photo upload functionality
function initializePhotoUpload() {
    const profilePhotoInput = document.getElementById('profile_photo');
    const photoUploadModal = document.getElementById('photoUploadModal');
    
    // Photo upload preview
    if (profilePhotoInput) {
        profilePhotoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    this.value = '';
                    return;
                }
                
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Please upload a valid image file (JPG, JPEG, PNG, or GIF)');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.onerror = function() {
                    alert('Error reading file. Please try again.');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Reset modal when closed
    if (photoUploadModal) {
        photoUploadModal.addEventListener('hidden.bs.modal', function() {
            const imagePreview = document.getElementById('imagePreview');
            const photoUploadForm = document.getElementById('photoUploadForm');
            
            if (imagePreview) {
                imagePreview.style.display = 'none';
                imagePreview.src = '';
            }
            if (photoUploadForm) {
                photoUploadForm.reset();
            }
        });
    }
}

// Address Management
function initializeAddressManagement() {
    const toggleAddressBtn = document.getElementById('toggleAddressEdit');
    const houseNumberInput = document.getElementById('houseNumberInput');
    const purokSelect = document.getElementById('purokSelect');
    const addressInput = document.getElementById('addressInput');
    
    // Check if address elements exist
    if (!houseNumberInput || !purokSelect || !addressInput) {
        console.log('Address elements not found');
        return;
    }
    
    let isEditing = false;
    
    // Generate address function
    function generateAddress() {
        const houseNumber = houseNumberInput.value.trim();
        const purokValue = purokSelect.value;
        const barangay = "Barangay Balas, Mexico, Pampanga, Philippines";
        
        console.log('Generating address - House:', houseNumber, 'Purok:', purokValue);
        
        let generatedAddress = barangay; // Default
        
        if (houseNumber && purokValue) {
            generatedAddress = `${houseNumber}, ${purokValue}, ${barangay}`;
        } else if (purokValue) {
            generatedAddress = `${purokValue}, ${barangay}`;
        } else if (houseNumber) {
            generatedAddress = `${houseNumber}, ${barangay}`;
        }
        
        console.log('Final address:', generatedAddress);
        addressInput.value = generatedAddress;
        
        return generatedAddress;
    }
    
    // Toggle address editing
    if (toggleAddressBtn) {
        toggleAddressBtn.addEventListener('click', function(e) {
            e.preventDefault();
            isEditing = !isEditing;
            
            if (isEditing) {
                // Enable editing
                houseNumberInput.removeAttribute('readonly');
                houseNumberInput.classList.remove('bg-light');
                
                purokSelect.removeAttribute('disabled');
                purokSelect.classList.remove('bg-light');
                
                // Update button
                toggleAddressBtn.innerHTML = '<i class="fas fa-save me-1"></i>Done';
                toggleAddressBtn.classList.remove('btn-outline-primary');
                toggleAddressBtn.classList.add('btn-success');
                
                // Focus on house number input
                houseNumberInput.focus();
                
                console.log('Address editing enabled');
            } else {
                // Disable editing
                houseNumberInput.setAttribute('readonly', 'readonly');
                houseNumberInput.classList.add('bg-light');
                
                purokSelect.setAttribute('disabled', 'disabled');
                purokSelect.classList.add('bg-light');
                
                // Update button
                toggleAddressBtn.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
                toggleAddressBtn.classList.remove('btn-success');
                toggleAddressBtn.classList.add('btn-outline-primary');
                
                // Generate final address
                generateAddress();
                
                console.log('Address editing disabled');
            }
        });
    }
    
    // Auto-generate address when house number or purok changes
    houseNumberInput.addEventListener('input', function() {
        generateAddress();
    });
    
    purokSelect.addEventListener('change', function() {
        generateAddress();
    });
    
    // Initialize fields on page load
    houseNumberInput.classList.add('bg-light');
    purokSelect.classList.add('bg-light');
    addressInput.classList.add('bg-light');
    purokSelect.setAttribute('disabled', 'disabled');
    
    // Generate initial address
    setTimeout(function() {
        generateAddress();
        console.log('Initial address generated');
    }, 100);
}

// Form validation
function initializeFormValidation() {
    const profileForm = document.getElementById('profileForm');
    if (!profileForm) {
        console.log('Profile form not found');
        return;
    }
    
    profileForm.addEventListener('submit', function(e) {
        console.log('Form submission started');
        
        // IMPORTANT: Re-enable purok select before form submission
        const purokSelect = document.getElementById('purokSelect');
        if (purokSelect) {
            purokSelect.removeAttribute('disabled');
            console.log('Purok select enabled for submission');
        }
        
        let isValid = true;
        let errorMessage = '';
        
        // Validate required fields
        const requiredFields = this.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
                if (!errorMessage) {
                    errorMessage = 'Please fill in all required fields.';
                }
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Validate phone number
        const contactNumber = document.querySelector('[name="contact_number"]');
        if (contactNumber && contactNumber.value.trim()) {
            const phoneRegex = /^(09\d{9}|\d{7,10})$/;
            const cleanedPhone = contactNumber.value.replace(/\D/g, '');
            
            if (!phoneRegex.test(cleanedPhone)) {
                contactNumber.classList.add('is-invalid');
                isValid = false;
                errorMessage = 'Please enter a valid Philippine mobile number (09XXXXXXXXX) or landline number.';
            } else {
                contactNumber.classList.remove('is-invalid');
            }
        }
        
        // Validate email if provided
        const email = document.querySelector('[name="email"]');
        if (email && email.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
                errorMessage = 'Please enter a valid email address.';
            } else {
                email.classList.remove('is-invalid');
            }
        }
        
        // Validate birthdate and age
        const birthdate = document.querySelector('[name="birthdate"]');
        if (birthdate && birthdate.value) {
            const today = new Date();
            const birth = new Date(birthdate.value);
            const age = Math.floor((today - birth) / (365.25 * 24 * 60 * 60 * 1000));
            
            if (age < 1) {
                birthdate.classList.add('is-invalid');
                isValid = false;
                errorMessage = 'You must be at least 1 year old.';
            } else if (age > 120) {
                birthdate.classList.add('is-invalid');
                isValid = false;
                errorMessage = 'Please enter a valid birthdate.';
            } else {
                birthdate.classList.remove('is-invalid');
            }
        }
        
        // If validation fails, prevent submission
        if (!isValid) {
            e.preventDefault();
            console.log('Form validation failed:', errorMessage);
            
            // Re-disable purok select if validation fails
            if (purokSelect) {
                purokSelect.setAttribute('disabled', 'disabled');
            }
            
            alert(errorMessage);
            
            // Focus on first invalid field
            const firstInvalidField = this.querySelector('.is-invalid');
            if (firstInvalidField) {
                firstInvalidField.focus();
            }
            
            return false;
        }
        
        console.log('Form validation passed - submitting...');
        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            submitButton.disabled = true;
        }
    });
    
    // Real-time validation for better UX
    setupRealTimeValidation();
}

// Real-time validation setup
function setupRealTimeValidation() {
    const profileForm = document.getElementById('profileForm');
    if (!profileForm) return;
    
    // Phone number validation
    const contactNumberField = document.querySelector('[name="contact_number"]');
    if (contactNumberField) {
        contactNumberField.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value) {
                const phoneRegex = /^(09\d{9}|\d{7,10})$/;
                const cleanedPhone = value.replace(/\D/g, '');
                
                if (!phoneRegex.test(cleanedPhone)) {
                    this.classList.add('is-invalid');
                    this.title = 'Please enter a valid Philippine mobile number (09XXXXXXXXX) or landline number.';
                } else {
                    this.classList.remove('is-invalid');
                    this.title = '';
                }
            }
        });
    }
    
    // Email validation
    const emailField = document.querySelector('[name="email"]');
    if (emailField) {
        emailField.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    this.classList.add('is-invalid');
                    this.title = 'Please enter a valid email address.';
                } else {
                    this.classList.remove('is-invalid');
                    this.title = '';
                }
            }
        });
    }
    
    // Birthdate validation
    const birthdateField = document.querySelector('[name="birthdate"]');
    if (birthdateField) {
        birthdateField.addEventListener('change', function() {
            const value = this.value;
            if (value) {
                const today = new Date();
                const birth = new Date(value);
                const age = Math.floor((today - birth) / (365.25 * 24 * 60 * 60 * 1000));
                
                if (age < 1 || age > 120) {
                    this.classList.add('is-invalid');
                    this.title = 'Please enter a valid birthdate (age must be between 1 and 120 years).';
                } else {
                    this.classList.remove('is-invalid');
                    this.title = '';
                }
            }
        });
    }
    
    // Clear validation on input
    const allInputs = profileForm.querySelectorAll('input, select, textarea');
    allInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            this.title = '';
        });
    });
}

// Phone number formatting
function initializePhoneFormatting() {
    const phoneInput = document.querySelector('[name="contact_number"]');
    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            const formatted = formatPhoneNumber(this.value);
            if (formatted !== this.value) {
                this.value = formatted;
            }
        });
        
        // Allow only numbers and specific characters
        phoneInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^\d\s\-\(\)\+]/g, '');
        });
    }
}

// Phone number formatting utility
function formatPhoneNumber(phone) {
    if (!phone) return '';
    
    // Remove all non-digit characters
    const cleaned = phone.replace(/\D/g, '');
    
    // Philippine mobile numbers (09XXXXXXXXX)
    if (cleaned.length === 11 && cleaned.startsWith('09')) {
        return cleaned.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3');
    }
    
    // Landline numbers
    if (cleaned.length === 7) {
        return cleaned.replace(/(\d{3})(\d{4})/, '$1 $2');
    }
    
    if (cleaned.length === 8) {
        return cleaned.replace(/(\d{4})(\d{4})/, '$1 $2');
    }
    
    if (cleaned.length === 9) {
        return cleaned.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3');
    }
    
    if (cleaned.length === 10) {
        return cleaned.replace(/(\d{4})(\d{3})(\d{3})/, '$1 $2 $3');
    }
    
    return phone; // Return original if no match
}

// Utility function to manually trigger address generation (for debugging)
function manualGenerateAddress() {
    const houseNumberInput = document.getElementById('houseNumberInput');
    const purokSelect = document.getElementById('purokSelect');
    const addressInput = document.getElementById('addressInput');
    
    if (houseNumberInput && purokSelect && addressInput) {
        const houseNumber = houseNumberInput.value.trim();
        const purokValue = purokSelect.value;
        const barangay = "Barangay Balas, Mexico, Pampanga, Philippines";
        
        let generatedAddress = barangay;
        
        if (houseNumber && purokValue) {
            generatedAddress = `${houseNumber}, ${purokValue}, ${barangay}`;
        } else if (purokValue) {
            generatedAddress = `${purokValue}, ${barangay}`;
        } else if (houseNumber) {
            generatedAddress = `${houseNumber}, ${barangay}`;
        }
        
        addressInput.value = generatedAddress;
        console.log('Manual address generation:', generatedAddress);
        return generatedAddress;
    }
    return null;
}

// Make functions available globally for debugging
window.profileJS = {
    generateAddress: manualGenerateAddress,
    formatPhoneNumber: formatPhoneNumber,
    validateForm: function() {
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            const event = new Event('submit', { cancelable: true });
            return profileForm.dispatchEvent(event);
        }
        return false;
    }
};

console.log('Profile JS loaded successfully');