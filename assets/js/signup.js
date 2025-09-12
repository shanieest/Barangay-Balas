        $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                $('#loginBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Logging in...');
                
                $.ajax({
                    url: 'login-backend.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect || 'dashboard.php';
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred during login');
                    },
                    complete: function() {
                        $('#loginBtn').prop('disabled', false).html('<i class="fas fa-sign-in-alt"></i> Login');
                    }
                });
            });
            $('#registrationForm').on('submit', function(e) {
                e.preventDefault();
                
                $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'signup-success.php';
                        } else {
                            alert(response.message);
                            if (response.errors) {
                                $.each(response.errors, function(field, message) {
                                    $('#' + field).addClass('is-invalid');
                                    $('#' + field).after('<div class="invalid-feedback">' + message + '</div>');
                                });
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + error);
                    },
                    complete: function() {
                        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-user-plus"></i> Register Account');
                    }
                });
            });
        });
        $(document).ready(function(){
            $('.datepicker').datepicker({
                format: 'mm/dd/yyyy',
                autoclose: true,
                todayHighlight: true,
                endDate: '0d'
            });

            $('#birthdate').on('change', function() {
                var birthdate = new Date($(this).val());
                var today = new Date();
                var age = today.getFullYear() - birthdate.getFullYear();
                var m = today.getMonth() - birthdate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }
                $('#age').val(age);
            });

            $('#idUpload').change(function(){
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e){
                        $('#idPreview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                    $('.custom-file-label').text(file.name);
                }
            });

            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });

        const houseNoInput = document.getElementById('house_no');
        const purokInput = document.getElementById('purok');
        const fullAddressField = document.getElementById('full_address');

        function updateFullAddress() {
            const houseNo = houseNoInput.value.trim();
            const purok = purokInput.value.trim();

            let addressParts = [];

            if (houseNo) addressParts.push("House " + houseNo);
            if (purok) addressParts.push("Purok " + purok);

            addressParts.push("Balas, Mexico, Pampanga, Philippines");

            fullAddressField.value = addressParts.join(', ');
        }

        houseNoInput.addEventListener('input', updateFullAddress);
        purokInput.addEventListener('input', updateFullAddress);

        const idTypeSelect = document.getElementById('idType');
        const otherIdTypeGroup = document.getElementById('otherIdTypeGroup');

        idTypeSelect.addEventListener('change', function() {
            if (this.value === 'other') {
                otherIdTypeGroup.style.display = 'block';
                document.getElementById('otherIdType').setAttribute('required', 'required');
            } else {
                otherIdTypeGroup.style.display = 'none';
                document.getElementById('otherIdType').removeAttribute('required');
            }
        });
