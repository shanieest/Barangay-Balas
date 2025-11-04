        $(document).ready(function() {
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();
                updateProfile();
            });
            
            $('#passwordForm').on('submit', function(e) {
                e.preventDefault();
                updatePassword();
            });
            
            $('#photoInput').on('change', function() {
                uploadPhoto();
            });
            
            $('#deleteConfirmation').on('input', function() {
                $('#confirmDeleteBtn').prop('disabled', $(this).val() !== 'DELETE MY ACCOUNT');
            });
            
            $('#confirmDeleteBtn').on('click', function() {
                deleteAccount();
            });
        });
        
        function updateProfile() {
            const formData = $('#profileForm').serialize();
            
            $.ajax({
                url: '../backend/profile-backend.php?action=update_profile',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'An error occurred while updating profile');
                }
            });
        }
        
        function updatePassword() {
            const formData = $('#passwordForm').serialize();
            
            $.ajax({
                url: '../backend/profile-backend.php?action=update_password',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        $('#passwordForm')[0].reset();
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'An error occurred while updating password');
                }
            });
        }
        
        function uploadPhoto() {
            const formData = new FormData($('#photoUploadForm')[0]);
            
            $.ajax({
                url: '../backend/profile-backend.php?action=upload_photo',
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        $('#profilePhoto').attr('src', response.photo_path);
                        showAlert('success', response.message);
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'An error occurred while uploading photo');
                }
            });
        }
        
        function deleteAccount() {
            const confirmation = $('#deleteConfirmation').val();
            
            $.ajax({
                url: '../backend/profile-backend.php?action=delete_account',
                type: 'POST',
                data: { confirmation: confirmation },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        showAlert('danger', response.message);
                        $('#deleteAccountModal').modal('hide');
                    }
                },
                error: function() {
                    showAlert('danger', 'An error occurred while deleting account');
                    $('#deleteAccountModal').modal('hide');
                }
            });
        }
        
        function showAlert(type, message) {
            const alert = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            
            $('.container-fluid').prepend(alert);
            
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
