
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
        
        // Auto-generate address when house number or purok changes
        function updateAddress() {
            const houseNumber = document.querySelector('input[name="house_number"]').value;
            const purok = document.getElementById('purokSelect').value;
            const barangay = "Barangay Balas, Mabalacat City, Pampanga";
            
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
        
        document.getElementById('purokSelect').addEventListener('change', updateAddress);
        document.querySelector('input[name="house_number"]').addEventListener('input', updateAddress);
        
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
