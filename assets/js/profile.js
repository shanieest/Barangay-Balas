document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.querySelector('.wrapper').classList.toggle('sidebar-collapsed');
});

document.getElementById('profile_photo').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

document.getElementById('photoUploadModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('photoUploadForm').reset();
});

// Address management functions
let isAddressEditable = false;

function toggleAddressEdit() {
    const houseNumberInput = document.getElementById('houseNumberInput');
    const purokSelect = document.getElementById('purokSelect');
    const addressInput = document.getElementById('addressInput');
    const toggleButton = document.getElementById('toggleAddressEdit');
    const lockIcon = document.getElementById('addressLockIcon');
    
    isAddressEditable = !isAddressEditable;
    
    if (isAddressEditable) {
        // Enable editing
        houseNumberInput.readOnly = false;
        purokSelect.readOnly = false;
        addressInput.readOnly = false;
        
        houseNumberInput.classList.remove('bg-light');
        purokSelect.classList.remove('bg-light');
        addressInput.classList.remove('bg-light');
        
        toggleButton.innerHTML = '<i class="fas fa-times me-1"></i>Cancel';
        toggleButton.classList.remove('btn-outline-primary');
        toggleButton.classList.add('btn-outline-danger');
        
        lockIcon.className = 'fas fa-unlock text-success';
        
        // Focus on house number field
        houseNumberInput.focus();
    } else {
        // Disable editing and reset to original values
        houseNumberInput.readOnly = true;
        purokSelect.readOnly = true;
        addressInput.readOnly = true;
        
        houseNumberInput.classList.add('bg-light');
        purokSelect.classList.add('bg-light');
        addressInput.classList.add('bg-light');
        
        toggleButton.innerHTML = '<i class="fas fa-edit me-1"></i>Edit';
        toggleButton.classList.remove('btn-outline-danger');
        toggleButton.classList.add('btn-outline-primary');
        
        lockIcon.className = 'fas fa-lock text-muted';
        
        // Reset form to original values if needed
        document.getElementById('profileForm').dispatchEvent(new Event('reset'));
        // Re-populate with original data (you might need to reload from server in a real scenario)
        updateAddress(); // Regenerate address based on current values
    }
}

// Auto-generate address when house number or purok changes
function updateAddress() {
    if (!isAddressEditable) return; // Only update when in edit mode
    
    const houseNumber = document.getElementById('houseNumberInput').value;
    const purok = document.getElementById('purokSelect').value;
    const barangay = "Barangay Balas, Mexico, Pampanga, Philippines";
    
    let address = "";
    if (houseNumber && purok) {
        address = `${houseNumber}, Purok ${purok}, ${barangay}`;
    } else if (purok) {
        address = `Purok ${purok}, ${barangay}`;
    } else if (houseNumber) {
        address = `${houseNumber}, ${barangay}`;
    } else {
        address = barangay;
    }
    
    document.getElementById('addressInput').value = address;
}

// Event listeners
document.getElementById('toggleAddressEdit').addEventListener('click', toggleAddressEdit);

document.getElementById('purokSelect').addEventListener('change', function() {
    if (isAddressEditable) {
        updateAddress();
    }
});

document.getElementById('houseNumberInput').addEventListener('input', function() {
    if (isAddressEditable) {
        updateAddress();
    }
});

// Form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const requiredFields = this.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});

// Initialize address fields as read-only on page load
document.addEventListener('DOMContentLoaded', function() {
    const houseNumberInput = document.getElementById('houseNumberInput');
    const purokSelect = document.getElementById('purokSelect');
    const addressInput = document.getElementById('addressInput');
    
    houseNumberInput.classList.add('bg-light');
    purokSelect.classList.add('bg-light');
    addressInput.classList.add('bg-light');
});

