// Initialize when document loads
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
});

// Initialize page functionality
function initializePage() {
    setupEventListeners();
    loadHouseholdData();
    setupMobileSidebar();
}

// Setup event listeners
function setupEventListeners() {
    const exportBtn = document.querySelector('.export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportHouseholdData);
    }
}

// Load household data
function loadHouseholdData() {
    fetch('census-backend.php?action=get_household_data')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error loading household data:', data.error);
                return;
            }
            updateHouseholdStats(data);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Update household statistics
function updateHouseholdStats(data) {
    const totalMembers = data.total_members;
    let adults = 0;
    let children = 0;
    let workingMembers = 0;
    
    data.members.forEach(member => {
        if (member.age >= 18) {
            adults++;
            if (member.occupation !== 'N/A' && 
                member.occupation.toLowerCase() !== 'student') {
                workingMembers++;
            }
        } else {
            children++;
        }
    });
    
    updateStatCard('stat-number', totalMembers, 0);
    updateStatCard('stat-number', adults, 1);
    updateStatCard('stat-number', children, 2);
    updateStatCard('stat-number', workingMembers, 3);
}

// Update individual stat card
function updateStatCard(className, value, index) {
    const elements = document.getElementsByClassName(className);
    if (elements[index]) {
        const currentValue = parseInt(elements[index].textContent) || 0;
        if (currentValue !== value) {
            animateNumber(elements[index], currentValue, value);
        }
    }
}

// Animate number changes
function animateNumber(element, start, end) {
    const duration = 1000;
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            element.textContent = end;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// Export household data
function exportHouseholdData() {
    const overlay = document.getElementById('loadingOverlay');
    
    if (overlay) {
        overlay.style.display = 'flex';
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'census-backend.php';
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = 'export_household';
    
    form.appendChild(actionInput);
    document.body.appendChild(form);
    form.submit();
    
    setTimeout(() => {
        if (overlay) {
            overlay.style.display = 'none';
        }
        document.body.removeChild(form);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Export Successful!',
                text: 'Your household report has been downloaded successfully.',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    }, 2000);
}

// Setup mobile sidebar functionality
function setupMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const sidebarItems = document.querySelectorAll('.sidebar-menu li');
    
    if (!sidebar || !mainContent) return;
    
    function toggleSidebar() {
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
    }
    
    sidebarItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                toggleSidebar();
            }
        });
    });
    
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('expanded');
        }
    });
}

// Utility functions
const CensusUtils = {
    formatPhoneNumber: function(phone) {
        if (!phone) return 'N/A';
        const cleaned = phone.replace(/\D/g, '');
        if (cleaned.length === 11) {
            return cleaned.replace(/(\d{4})(\d{3})(\d{4})/, '$1-$2-$3');
        }
        return phone;
    },
    
    calculateAge: function(birthdate) {
        const today = new Date();
        const birth = new Date(birthdate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        
        return age;
    },
    
    formatDate: function(date) {
        return new Date(date).toLocaleDateString('en-PH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
};

window.CensusUtils = CensusUtils;


        function saveRelationship(memberId, button) {
            const select = document.querySelector(`select[data-member-id="${memberId}"]`);
            const relationship = select.value;
            
            if (!relationship) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select a relationship first.'
                });
                return;
            }
            
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
            button.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'update_relationship');
            formData.append('member_id', memberId);
            formData.append('relationship', relationship);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.innerHTML = '<i class="fas fa-check me-1"></i>Saved';
                    button.className = 'btn btn-success btn-sm';
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: 'Relationship updated successfully.',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.className = 'btn save-btn btn-sm';
                        button.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(data.error || 'Failed to save');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error saving relationship: ' + error.message
                });
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        function saveAllRelationships() {
            const selects = document.querySelectorAll('.relationship-select:not([disabled])');
            const updates = [];
            
            selects.forEach(select => {
                if (select.value) {
                    updates.push({
                        member_id: select.getAttribute('data-member-id'),
                        relationship: select.value
                    });
                }
            });
            
            if (updates.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Changes',
                    text: 'No changes to save.'
                });
                return;
            }
            
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            const formData = new FormData();
            formData.append('action', 'batch_update_relationships');
            formData.append('updates', JSON.stringify(updates));
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingOverlay').style.display = 'none';
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'All relationships saved successfully!',
                        showConfirmButton: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.error || 'Failed to save');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loadingOverlay').style.display = 'none';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error saving relationships: ' + error.message
                });
            });
        }
