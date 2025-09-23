
// Initialize when document loads
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
});

// Initialize page functionality
function initializePage() {
    setupEventListeners();
    loadHouseholdData();
}

// Setup event listeners
function setupEventListeners() {
    // Export button
    const exportBtn = document.querySelector('.export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', exportHouseholdData);
    }
    
    // Mobile sidebar functionality
    setupMobileSidebar();
}

// Load household data (for dynamic updates if needed)
function loadHouseholdData() {
    fetch('census-backend.php?action=get_household_data')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error loading household data:', data.error);
                return;
            }
            
            // Update any dynamic elements if needed
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
    
    // Update stat cards if they exist
    updateStatCard('total-members', totalMembers);
    updateStatCard('adults-count', adults);
    updateStatCard('children-count', children);
    updateStatCard('working-members', workingMembers);
}

// Update individual stat card
function updateStatCard(className, value) {
    const elements = document.getElementsByClassName(className);
    for (let element of elements) {
        if (element.classList.contains('stat-number')) {
            animateNumber(element, parseInt(element.textContent) || 0, value);
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
    
    // Show loading overlay
    if (overlay) {
        overlay.style.display = 'flex';
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../../census-backend.php';
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = 'export_household';
    
    form.appendChild(actionInput);
    document.body.appendChild(form);
    
    // Submit form
    form.submit();
    
    // Hide loading overlay after a delay
    setTimeout(() => {
        if (overlay) {
            overlay.style.display = 'none';
        }
        document.body.removeChild(form);
        
        // Show success message
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
        } else {
            // Fallback alert
            alert('Your household report has been downloaded successfully!');
        }
    }, 2000);
}

// Setup mobile sidebar functionality
function setupMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const sidebarItems = document.querySelectorAll('.sidebar-menu li');
    
    if (!sidebar || !mainContent) return;
    
    // Toggle sidebar on small screens
    function toggleSidebar() {
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
    }
    
    // Add click event to sidebar items on mobile
    sidebarItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                toggleSidebar();
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('expanded');
        }
    });
}

// Refresh household data
function refreshHouseholdData() {
    loadHouseholdData();
}

// Print household report
function printHouseholdReport() {
    const printContent = generatePrintableContent();
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Household Census Report</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 20px; 
                    color: #333; 
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 30px; 
                    border-bottom: 2px solid #0033cc; 
                    padding-bottom: 15px; 
                }
                .member-card { 
                    border: 1px solid #ddd; 
                    margin: 10px 0; 
                    padding: 15px; 
                    border-radius: 8px; 
                }
                .member-header { 
                    font-weight: bold; 
                    font-size: 1.2em; 
                    margin-bottom: 10px; 
                    color: #0033cc; 
                }
                .member-details { 
                    display: grid; 
                    grid-template-columns: 1fr 1fr; 
                    gap: 10px; 
                }
                .detail-item { 
                    margin: 5px 0; 
                }
                .head-member { 
                    background-color: #f0f8ff; 
                    border-left: 4px solid #0033cc; 
                }
                @media print {
                    .no-print { display: none; }
                    body { margin: 0; }
                }
            </style>
        </head>
        <body>
            ${printContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

// Generate printable content
function generatePrintableContent() {
    const headerCard = document.querySelector('.header-card');
    const memberCards = document.querySelectorAll('.member-card');
    const statsGrid = document.querySelector('.stats-grid');
    
    let content = '';
    
    // Header
    if (headerCard) {
        const title = headerCard.querySelector('h1')?.textContent || 'My Household';
        const address = headerCard.querySelector('p')?.textContent || '';
        const householdId = headerCard.querySelector('small')?.textContent || '';
        
        content += `
            <div class="header">
                <h1>${title}</h1>
                <p>${address}</p>
                <p><strong>${householdId}</strong></p>
                <p>Generated on: ${new Date().toLocaleDateString()}</p>
            </div>
        `;
    }
    
    // Statistics
    if (statsGrid) {
        const statCards = statsGrid.querySelectorAll('.stat-card');
        content += '<div class="stats-section"><h3>Household Statistics</h3><div class="member-details">';
        
        statCards.forEach(card => {
            const number = card.querySelector('.stat-number')?.textContent || '0';
            const label = card.querySelector('.stat-label')?.textContent || '';
            content += `<div class="detail-item"><strong>${label}:</strong> ${number}</div>`;
        });
        
        content += '</div></div>';
    }
    
    // Members
    content += '<h3>Family Members</h3>';
    
    memberCards.forEach(card => {
        const isHead = card.querySelector('.head-member') !== null;
        const name = card.querySelector('h5')?.textContent || 'Unknown';
        const relationship = card.querySelector('.relationship-badge')?.textContent || 'MEMBER';
        const details = card.querySelectorAll('.detail-item');
        
        content += `
            <div class="member-card ${isHead ? 'head-member' : ''}">
                <div class="member-header">${name} - ${relationship}</div>
                <div class="member-details">
        `;
        
        details.forEach(detail => {
            const label = detail.querySelector('.detail-label')?.textContent || '';
            const value = detail.querySelector('.detail-value')?.textContent || '';
            content += `<div class="detail-item"><strong>${label}:</strong> ${value}</div>`;
        });
        
        content += '</div></div>';
    });
    
    return content;
}

// Share household data (if needed for future features)
function shareHouseholdData() {
    if (navigator.share) {
        navigator.share({
            title: 'My Household Census',
            text: 'View my household census information',
            url: window.location.href
        }).then(() => {
            console.log('Shared successfully');
        }).catch((error) => {
            console.log('Error sharing:', error);
        });
    } else {
        // Fallback - copy URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Link Copied!',
                    text: 'The page URL has been copied to your clipboard.',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                alert('Page URL copied to clipboard!');
            }
        });
    }
}

// Validate household data (for form submissions if needed)
function validateHouseholdData() {
    let isValid = true;
    const errors = [];
    
    // Check if household has at least one member
    const memberCards = document.querySelectorAll('.member-card');
    if (memberCards.length === 0) {
        errors.push('Household must have at least one member');
        isValid = false;
    }
    
    // Check if household has a head
    const headMember = document.querySelector('.head-member');
    if (!headMember) {
        errors.push('Household must have a head of family');
        isValid = false;
    }
    
    if (!isValid && errors.length > 0) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                html: errors.join('<br>'),
                confirmButtonText: 'OK'
            });
        } else {
            alert(errors.join('\n'));
        }
    }
    
    return isValid;
}

// Handle connection errors
function handleConnectionError(error) {
    console.error('Connection error:', error);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Unable to connect to the server. Please check your internet connection.',
            confirmButtonText: 'Retry',
            showCancelButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                location.reload();
            }
        });
    } else {
        if (confirm('Connection error. Would you like to retry?')) {
            location.reload();
        }
    }
}

// Auto-refresh functionality (optional)
let autoRefreshInterval;

function enableAutoRefresh(intervalMinutes = 5) {
    disableAutoRefresh(); // Clear any existing interval
    
    autoRefreshInterval = setInterval(() => {
        refreshHouseholdData();
    }, intervalMinutes * 60 * 1000);
}

function disableAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// Cleanup when page unloads
window.addEventListener('beforeunload', function() {
    disableAutoRefresh();
});

// Utility functions
const CensusUtils = {
    // Format phone number
    formatPhoneNumber: function(phone) {
        if (!phone) return 'N/A';
        const cleaned = phone.replace(/\D/g, '');
        if (cleaned.length === 11) {
            return cleaned.replace(/(\d{4})(\d{3})(\d{4})/, '$1-$2-$3');
        }
        return phone;
    },
    
    // Calculate age from birthdate
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
    
    // Format date
    formatDate: function(date) {
        return new Date(date).toLocaleDateString('en-PH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
};

// Export utility functions for global use
window.CensusUtils = CensusUtils;