// admin/assets/js/officials.js
document.addEventListener('DOMContentLoaded', function() {
    loadOfficials();
    
    // Only add event listeners if user can modify
    if (window.USER_CAN_MODIFY) {
        document.getElementById('addOfficialForm').addEventListener('submit', addOfficial);
        document.getElementById('editOfficialForm').addEventListener('submit', updateOfficial);
        document.getElementById('confirmDeleteBtn').addEventListener('click', deleteOfficial);
        
        document.getElementById('officialConfirmPassword').addEventListener('input', function() {
            const password = document.getElementById('officialPassword').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword && confirmPassword.length > 0) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function loadOfficials() {
    showLoadingSpinner(true);
    
    fetch('../backend/barangay-officials-backend.php?action=get_officials')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load officials');
            }
            
            const tableBody = document.querySelector('#officialsTable tbody');
            tableBody.innerHTML = '';
            
            if (data.data.length === 0) {
                const colspan = window.USER_CAN_MODIFY ? '6' : '5';
                tableBody.innerHTML = `<tr><td colspan="${colspan}" class="text-center">No officials found</td></tr>`;
                return;
            }
            
            data.data.forEach((official, index) => {
                const row = document.createElement('tr');
                const fullName = `${official.first_name} ${official.middle_name ? official.middle_name + ' ' : ''}${official.last_name}`;
                
                const roleBadgeClass = official.role === 'Admin' ? 'bg-primary' : 'bg-info';
                const statusBadgeClass = official.status === 'Active' ? 'bg-success' : 'bg-secondary';
                
                let rowHTML = `
                    <td>${index + 1}</td>
                    <td>
                        ${escapeHtml(fullName)}<br>
                        <small class="text-muted">@${escapeHtml(official.username)}</small>
                    </td>
                    <td>
                        ${escapeHtml(official.position)}<br>
                        <span class="badge ${roleBadgeClass}">${escapeHtml(official.role)}</span>
                        ${official.committee_position ? '<br><small class="text-muted"><i class="fas fa-users me-1"></i>' + escapeHtml(official.committee_position) + '</small>' : ''}
                    </td>
                    <td>
                        ${escapeHtml(official.email)}<br>
                        <small class="text-muted">${official.contact_number || 'No contact'}</small>
                    </td>
                    <td><span class="badge ${statusBadgeClass}">${official.status}</span></td>
                `;
                
                // Only add action buttons if user can modify
                if (window.USER_CAN_MODIFY) {
                    rowHTML += `
                        <td class="action-buttons">
                            <button class="btn btn-sm btn-warning edit-btn" data-id="${official.id}" data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${official.id}" 
                                data-name="${escapeHtml(fullName)}" 
                                data-position="${escapeHtml(official.position)}"
                                data-role="${escapeHtml(official.role)}"
                                ${official.position === 'Barangay Captain' ? 'disabled data-bs-toggle="tooltip" title="Cannot delete Barangay Captain"' : 'data-bs-toggle="tooltip" title="Delete"'}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                }
                
                row.innerHTML = rowHTML;
                tableBody.appendChild(row);
            });
            
            // Only add button listeners if user can modify
            if (window.USER_CAN_MODIFY) {
                addButtonEventListeners();
            }
        })
        .catch(error => {
            console.error('Error loading officials:', error);
            showAlert('Failed to load officials: ' + error.message, 'error');
            
            const tableBody = document.querySelector('#officialsTable tbody');
            const colspan = window.USER_CAN_MODIFY ? '6' : '5';
            tableBody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-danger">Error loading data</td></tr>`;
        })
        .finally(() => {
            showLoadingSpinner(false);
        });
}

function addButtonEventListeners() {
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            loadOfficialData(id);
        });
    });
    
    document.querySelectorAll('.delete-btn:not([disabled])').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const position = this.getAttribute('data-position');
            const role = this.getAttribute('data-role');
            
            document.getElementById('deleteOfficialId').value = id;
            document.getElementById('deleteOfficialName').textContent = name;
            document.getElementById('deleteOfficialPosition').textContent = position;
            document.getElementById('deleteOfficialRole').textContent = role;
            
            new bootstrap.Modal(document.getElementById('deleteOfficialModal')).show();
        });
    });
    
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function loadOfficialData(id) {
    if (!id) {
        showAlert('Invalid official ID', 'error');
        return;
    }
    
    fetch(`../backend/barangay-officials-backend.php?action=get_official&id=${encodeURIComponent(id)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load official data');
            }
            
            const official = data.data;
            document.getElementById('editOfficialId').value = official.id;
            document.getElementById('editOfficialFirstName').value = official.first_name || '';
            document.getElementById('editOfficialMiddleName').value = official.middle_name || '';
            document.getElementById('editOfficialLastName').value = official.last_name || '';
            document.getElementById('editOfficialPosition').value = official.position || '';
            document.getElementById('editOfficialCommitteePosition').value = official.committee_position || '';
            document.getElementById('editOfficialRole').value = official.role || 'Official';
            document.getElementById('editOfficialContact').value = official.contact_number || '';
            document.getElementById('editOfficialStatus').value = official.status || 'Active';
            
            new bootstrap.Modal(document.getElementById('editOfficialModal')).show();
        })
        .catch(error => {
            console.error('Error loading official data:', error);
            showAlert('Failed to load official data: ' + error.message, 'error');
        });
}

function addOfficial(e) {
    e.preventDefault();
    
    const password = document.getElementById('officialPassword').value;
    const confirmPassword = document.getElementById('officialConfirmPassword').value;
    
    if (password !== confirmPassword) {
        showAlert('Passwords do not match', 'error');
        return;
    }
    
    if (password.length < 8) {
        showAlert('Password must be at least 8 characters long', 'error');
        return;
    }
    
    const formData = {
        first_name: document.getElementById('officialFirstName').value.trim(),
        middle_name: document.getElementById('officialMiddleName').value.trim(),
        last_name: document.getElementById('officialLastName').value.trim(),
        position: document.getElementById('officialPosition').value,
        committee_position: document.getElementById('officialCommitteePosition').value, // Now optional
        role: document.getElementById('officialRole').value,
        email: document.getElementById('officialEmail').value.trim(),
        contact_number: document.getElementById('officialContact').value.trim(),
        status: document.getElementById('officialStatus').value,
        password: password
    };
    
    // Removed committee_position from required validation
    if (!formData.first_name || !formData.last_name || !formData.position || !formData.email || !formData.role) {
        showAlert('Please fill in all required fields', 'error');
        return;
    }
    
    if (!isValidEmail(formData.email)) {
        showAlert('Please enter a valid email address', 'error');
        return;
    }
    
    if (formData.contact_number && !isValidPhoneNumber(formData.contact_number)) {
        showAlert('Contact number must be 11 digits', 'error');
        return;
    }
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    fetch('../backend/barangay-officials-backend.php?action=add_official', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert('Official added successfully!' + (data.username ? ` Username: ${data.username}` : ''), 'success');
            document.getElementById('addOfficialForm').reset();
            bootstrap.Modal.getInstance(document.getElementById('addOfficialModal')).hide();
            loadOfficials();
        } else {
            throw new Error(data.message || 'Failed to add official');
        }
    })
    .catch(error => {
        console.error('Error adding official:', error);
        showAlert('Error: ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function updateOfficial(e) {
    e.preventDefault();
    
    const formData = {
        id: document.getElementById('editOfficialId').value,
        first_name: document.getElementById('editOfficialFirstName').value.trim(),
        middle_name: document.getElementById('editOfficialMiddleName').value.trim(),
        last_name: document.getElementById('editOfficialLastName').value.trim(),
        position: document.getElementById('editOfficialPosition').value,
        committee_position: document.getElementById('editOfficialCommitteePosition').value, // Now optional
        role: document.getElementById('editOfficialRole').value,
        contact_number: document.getElementById('editOfficialContact').value.trim(),
        status: document.getElementById('editOfficialStatus').value
    };
    
    // Removed committee_position from required validation
    if (!formData.first_name || !formData.last_name || !formData.position || !formData.role) {
        showAlert('Please fill in all required fields', 'error');
        return;
    }
    
    if (formData.contact_number && !isValidPhoneNumber(formData.contact_number)) {
        showAlert('Contact number must be 11 digits', 'error');
        return;
    }
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    
    fetch('../backend/barangay-officials-backend.php?action=update_official', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert('Official updated successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('editOfficialModal')).hide();
            loadOfficials();
        } else {
            throw new Error(data.message || 'Failed to update official');
        }
    })
    .catch(error => {
        console.error('Error updating official:', error);
        showAlert('Error: ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function deleteOfficial() {
    const id = document.getElementById('deleteOfficialId').value;
    
    if (!id) {
        showAlert('Invalid official ID', 'error');
        return;
    }
    
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    
    fetch(`../backend/barangay-officials-backend.php?action=delete_official&id=${encodeURIComponent(id)}`, {
        method: 'GET' 
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert('Official deleted successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('deleteOfficialModal')).hide();
            loadOfficials();
        } else {
            throw new Error(data.message || 'Failed to delete official');
        }
    })
    .catch(error => {
        console.error('Error deleting official:', error);
        showAlert('Error: ' + error.message, 'error');
    })
    .finally(() => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = originalText;
    });
}

function showAlert(message, type) {
    const icon = type === 'success' ? 'success' : type === 'error' ? 'error' : 'info';
    
    Swal.fire({
        icon: icon,
        title: type === 'success' ? 'Success!' : type === 'error' ? 'Error!' : 'Info',
        text: message,
        showConfirmButton: false,
        timer: 3000,
        position: 'top-end',
        toast: true,
        timerProgressBar: true
    });
}

function showLoadingSpinner(show) {
    const tableBody = document.querySelector('#officialsTable tbody');
    if (show) {
        const colspan = window.USER_CAN_MODIFY ? '6' : '5';
        tableBody.innerHTML = `<tr><td colspan="${colspan}" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>`;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhoneNumber(phone) {
    const phoneRegex = /^[0-9]{11}$/;
    return phoneRegex.test(phone);
}