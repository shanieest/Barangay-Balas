<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['registration_success'])) {
    echo '<div class="alert alert-success">'.$_SESSION['registration_success'].'</div>';
    unset($_SESSION['registration_success']);
}

if (isset($_SESSION['login_error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['login_error'].'</div>';
    unset($_SESSION['login_error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Balas Portal</title>
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/signup.css">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-header">
                <img src="\barangay-balas\assets\img\balas-logo.png" alt="Barangay Balas Logo" class="auth-logo">
                <h2>Barangay Balas Portal</h2>
                <p class="text-muted">Secure online services for residents</p>
            </div>

            <ul class="nav nav-tabs" id="authTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="login-tab" data-toggle="tab" href="#login" role="tab" aria-controls="login" aria-selected="true">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="register-tab" data-toggle="tab" href="#register" role="tab" aria-controls="register" aria-selected="false">Register</a>
                </li>
            </ul>

            <div class="tab-content" id="authTabContent">
                <!-- Login Form -->
                <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                    <div class="alert alert-primary">
                        <i class="fas fa-info-circle"></i> Please login with your registered email address.
                    </div>
                    <form id="loginForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="form-group">
                            <label for="loginEmail">Email</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email" class="form-control" id="loginEmail" name="email" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="loginPassword">Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Enter your password" required>
                            </div>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                            <a href="forgot-password.php" class="float-right" style="color: var(--primary-red);">Forgot password?</a>
                        </div>
                        <button type="submit" class="btn btn-auth" id="loginBtn">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                </div>

                <!-- Registration Form -->
                <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                    <form id="registrationForm" action="signup-backend.php" method="POST" enctype="multipart/form-data">
                        <div class="form-section">
                            <h5 class="form-section-title"><i class="fas fa-user"></i> Personal Information</h5>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="firstName">First Name*</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First name" required>
                                </div>
                                 <div class="form-group col-md-6">
                                    <label for="middleName">Middle Name*</label>
                                    <input type="text" class="form-control" id="middleName" name="middleName" placeholder="Middle name" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="lastName">Last Name*</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last name" required>
                                </div>
                                 <div class="form-group col-md-6">
                                    <label for="suffix">Suffix</label>
                                    <input type="text" class="form-control" id="suffix" name="suffix" placeholder="Suffix">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="birthdate">Birthdate</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="age">Age</label>
                                    <input type="text" class="form-control" id="age" name="age" placeholder="Age" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="sex">Sex</label>
                                    <select class="form-control" id="sex" name="sex" required>
                                        <option value="" selected disabled>Select sex</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h5 class="form-section-title"><i class="fas fa-address-card"></i> Contact Information</h5>
                            <div class="form-group">
                                <label for="registerEmail">Email</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" class="form-control" id="registerEmail" name="email" placeholder="Enter your email" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    </div>
                                    <input type="tel" class="form-control" id="phone" name="contactNumber" placeholder="Enter your phone number" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="house_no">House No.*</label>
                                <input type="text" class="form-control" id="house_no" name="houseNumber" placeholder="Enter House No." required>
                            </div>

                            <div class="form-group">
                                <label for="purok">Purok*</label>
                                <select class="form-control" id="purok" name="purok" required>
                                    <option value="">-- Select Purok --</option>
                                    <option value="Purok 1">1</option>
                                    <option value="Purok 2">2</option>
                                    <option value="Purok 3">3</option>
                                    <option value="Purok 4">4</option>
                                    <option value="Purok 5">5</option>
                                    <option value="Purok 6">6</option>
                                    <option value="Purok 7">7</option>
                                </select>
                             </div>

                            <div class="form-group">
                                <label for="full_address">Full Address</label>
                                <textarea class="form-control" id="full_address" name="full_address" rows="2" readonly></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <h5 class="form-section-title"><i class="fas fa-lock"></i> Account Security</h5>
                            <div class="form-group">
                                <label for="registerPassword">Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" class="form-control" id="registerPassword" name="password" placeholder="Create a password" required>
                                </div>
                                <small class="form-text text-muted">Must be at least 8 characters long</small>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h5 class="form-section-title"><i class="fas fa-id-card"></i> ID Verification</h5>
                            <div class="alert alert-primary">
                                <i class="fas fa-info-circle"></i> Please upload 2 clear photos of your valid government-issued ID (front and back).
                            </div>
                           <div class="form-group">
                                <label for="idType">ID Type</label>
                                <select class="form-control" id="idType" name="idType" required>
                                    <option value="" selected disabled>Select ID type</option>
                                    <option value="prc">PRC ID</option>
                                    <option value="driver">Driver's License ID</option>
                                    <option value="passport">Passport ID</option>
                                    <option value="company">Company ID</option>
                                    <option value="other">Others</option>
                                </select>
                            </div>

                            <div class="form-group" id="otherIdTypeGroup" style="display: none;">
                                <label for="otherIdType">Specify Other ID Type</label>
                                <input type="text" class="form-control" id="otherIdType" name="otherIdType" placeholder="Enter ID Type">
                            </div>

                            <!-- First ID Photo -->
                            <div class="id-upload-container">
                                <label class="id-upload-label">Upload ID Photo 1 (Front)*</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="idUpload1" name="validId1" accept="image/*,.pdf" required>
                                    <label class="custom-file-label" for="idUpload1">Choose file</label>
                                </div>
                                <img id="idPreview1" class="id-preview" alt="ID Preview 1">
                                <small class="form-text text-muted">Max file size: 5MB (JPG, PNG, PDF)</small>
                            </div>

                            <!-- Second ID Photo -->
                            <div class="id-upload-container">
                                <label class="id-upload-label">Upload ID Photo 2 (Back)*</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="idUpload2" name="validId2" accept="image/*,.pdf" required>
                                    <label class="custom-file-label" for="idUpload2">Choose file</label>
                                </div>
                                <img id="idPreview2" class="id-preview" alt="ID Preview 2">
                                <small class="form-text text-muted">Max file size: 5MB (JPG, PNG, PDF)</small>
                            </div>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">I certify that all information provided is accurate and I agree to the <a href="#" style="color: var(--primary-blue);">terms and conditions</a></label>
                        </div>
                        <button type="submit" class="btn btn-auth" id="submitBtn">
                            <i class="fas fa-user-plus"></i> Register Account
                        </button>
                    </form>
                </div>
            </div>

            <div class="auth-footer">
                <p>Need help? <a href="#">Contact Barangay Support</a> or visit our office</p>
      &copy; <?php echo date("Y"); ?> Barangay Balas, Mexico, Pampanga. All Rights Reserved. 
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <!-- Custom JS -->
   <script>
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

            // Handle ID Upload 1
            $('#idUpload1').change(function(){
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e){
                        $('#idPreview1').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                    $(this).next('.custom-file-label').text(file.name);
                }
            });

            // Handle ID Upload 2
            $('#idUpload2').change(function(){
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e){
                        $('#idPreview2').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                    $(this).next('.custom-file-label').text(file.name);
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
            if (purok) addressParts.push(purok);

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
   </script>
</body>
</html>