
function editSocialWorker(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_first_name').value = data.first_name;
    document.getElementById('edit_middle_name').value = data.middle_name || '';
    document.getElementById('edit_last_name').value = data.last_name;
    document.getElementById('edit_email').value = data.email;
    document.getElementById('edit_contact_number').value = data.contact_number;
    document.getElementById('edit_position').value = data.position;
    document.getElementById('edit_department').value = data.department || '';
    document.getElementById('edit_specialization').value = data.specialization || '';
    document.getElementById('edit_status').value = data.status;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteSocialWorker(id, name) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_name').textContent = name;
    
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.parentElement.querySelector('.bi');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

function validatePassword() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return false;
    }
    
    if (password.length < 6) {
        alert('Password must be at least 6 characters long!');
        return false;
    }
    
    return true;
}