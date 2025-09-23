// assets/js/census-enhanced.js - Enhanced Census Management JavaScript

let currentPage = 1;
let currentLimit = 25;
let currentPurok = '';
let currentSearch = '';

// Initialize when document loads
document.addEventListener('DOMContentLoaded', function() {
    loadHouseholds();
    loadStatistics();
});

// Load households data with complete information
function loadHouseholds(page = 1) {
    currentPage = page;
    const purok = document.getElementById('purokFilter').value;
    const search = document.getElementById('searchInput').value;
    const limit = document.getElementById('entriesPerPage').value;
    
    currentPurok = purok;
    currentSearch = search;
    currentLimit = parseInt(limit);
    
    const container = document.getElementById('householdContainer');
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3">Loading households...</p>
        </div>
    `;
    
    const params = new URLSearchParams({
        action: 'get_households',
        page: page,
        limit: limit,
        purok: purok,
        search: search
    });
    
    fetch(`census-backend.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            displayHouseholdsEnhanced(data.households);
            displayPagination(data);
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error loading households: ${error.message}
                </div>
            `;
        });
}

// Display households with complete census information
function displayHouseholdsEnhanced(households) {
    const container = document.getElementById('householdContainer');
    
    if (households.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                No households found matching your criteria.
            </div>
        `;
        return;
    }
    
    let householdsHTML = '';
    
    households.forEach(household => {
        householdsHTML += `
            <div class="household-card-enhanced">
                <div class="household-header-enhanced">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">House #${household.house_number}, ${household.purok}</h4>
                            <p class="mb-0 opacity-75">Household ID: ${household.household_id}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="household-stats">
                                <span class="badge bg-light text-dark fs-6 me-2">${household.member_count} Member${household.member_count > 1 ? 's' : ''}</span>
                                <div class="mt-2">
                                    ${getHouseholdBadges(household.members)}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    ${household.members.map(member => `
                        <div class="member-row-enhanced ${member.relationship === 'HEAD' ? 'head-member-row' : ''}">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <div class="member-basic-info">
                                        <div class="d-flex align-items-center">
                                            <div class="member-avatar-sm">
                                                ${getInitials(member.name)}
                                            </div>
                                            <div class="ms-3">
                                                <h6 class="mb-1 fw-bold">${member.name}</h6>
                                                <span class="badge ${getBadgeClass(member.relationship)}">${member.relationship}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="member-details-grid">
                                        <div class="detail-group">
                                            <small class="detail-label">Personal Info</small>
                                            <div class="detail-content">
                                                <span class="detail-item">Age: ${member.age}</span>
                                                <span class="detail-item">Sex: ${member.sex}</span>
                                                <span class="detail-item">Civil Status: ${member.civil_status}</span>
                                                <span class="detail-item">Birthday: ${formatBirthdate(member.birthdate)}</span>
                                            </div>
                                        </div>
                                        <div class="detail-group">
                                            <small class="detail-label">Background</small>
                                            <div class="detail-content">
                                                <span class="detail-item">Education: ${member.education}</span>
                                                <span class="detail-item">Religion: ${member.religion}</span>
                                                <span class="detail-item">Occupation: ${member.occupation}</span>
                                            </div>
                                        </div>
                                        <div class="detail-group">
                                            <small class="detail-label">Contact & Health</small>
                                            <div class="detail-content">
                                                <span class="detail-item">Contact: ${member.contact}</span>
                                                <span class="detail-item">Email: ${member.email}</span>
                                                <span class="detail-item">PhilHealth: ${member.philhealth}</span>
                                            </div>
                                        </div>
                                        <div class="detail-group">
                                            <small class="detail-label">Social Programs</small>
                                            <div class="detail-content">
                                                <span class="badge ${member.is_indigent ? 'bg-warning' : 'bg-light text-dark'} me-1">
                                                    ${member.is_indigent ? 'Indigent' : 'Non-Indigent'}
                                                </span>
                                                <span class="badge ${member.is_4ps ? 'bg-success' : 'bg-light text-dark'}">
                                                    ${member.is_4ps ? '4Ps Member' : 'Non-4Ps'}
                                                </span>
                                            </div>
                                        </div>
                                        ${member.medical_history !== 'None' ? `
                                        <div class="detail-group">
                                            <small class="detail-label">Medical History</small>
                                            <div class="detail-content">
                                                <span class="detail-item text-muted">${member.medical_history}</span>
                                            </div>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="household-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-users me-1"></i>
                            ${getHouseholdComposition(household.members)}
                        </small>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-outline-primary" onclick="editHousehold('${household.household_id}')">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="viewHouseholdDetails('${household.household_id}')">
                                <i class="fas fa-eye me-1"></i>Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = householdsHTML;
}

// Helper functions for enhanced display
function getInitials(fullName) {
    return fullName.split(' ')
        .map(name => name.charAt(0))
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

function getBadgeClass(relationship) {
    switch(relationship) {
        case 'HEAD': return 'head-badge bg-primary';
        case 'SPOUSE': return 'spouse-badge bg-success';
        case 'SON': case 'DAUGHTER': return 'child-badge bg-info';
        case 'FATHER': case 'MOTHER': return 'parent-badge bg-warning';
        case 'BROTHER': case 'SISTER': return 'sibling-badge bg-secondary';
        default: return 'member-badge bg-light text-dark';
    }
}

function formatBirthdate(birthdate) {
    if (!birthdate || birthdate === '0000-00-00') return 'Not provided';
    const date = new Date(birthdate);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function getHouseholdBadges(members) {
    const indigent = members.some(m => m.is_indigent);
    const fourps = members.some(m => m.is_4ps);
    const seniors = members.some(m => parseInt(m.age) >= 60);
    const children = members.some(m => parseInt(m.age) < 18);
    
    let badges = '';
    if (indigent) badges += '<span class="badge bg-warning text-dark me-1">Indigent</span>';
    if (fourps) badges += '<span class="badge bg-success me-1">4Ps</span>';
    if (seniors) badges += '<span class="badge bg-info me-1">Senior</span>';
    if (children) badges += '<span class="badge bg-light text-dark me-1">w/ Children</span>';
    
    return badges;
}

function getHouseholdComposition(members) {
    const adults = members.filter(m => parseInt(m.age) >= 18).length;
    const children = members.filter(m => parseInt(m.age) < 18).length;
    const seniors = members.filter(m => parseInt(m.age) >= 60).length;
    
    let composition = [];
    if (adults > 0) composition.push(`${adults} Adult${adults > 1 ? 's' : ''}`);
    if (children > 0) composition.push(`${children} Child${children > 1 ? 'ren' : ''}`);
    if (seniors > 0) composition.push(`${seniors} Senior${seniors > 1 ? 's' : ''}`);
    
    return composition.join(', ');
}

// Load and update enhanced statistics
function loadStatistics() {
    fetch('census-backend.php?action=get_statistics')
        .then(response => response.json())
        .then(data => {
            updateStatistic('totalHouseholds', parseInt(data.total_households));
            updateStatistic('totalResidents', parseInt(data.total_residents));
            updateStatistic('malePopulation', parseInt(data.male_population));
            updateStatistic('femalePopulation', parseInt(data.female_population));
            updateStatistic('childrenCount', parseInt(data.children));
            updateStatistic('adultsCount', parseInt(data.adults));
            updateStatistic('seniorsCount', parseInt(data.seniors));
            updateStatistic('indigentCount', parseInt(data.indigent_families));
            updateStatistic('fourpsCount', parseInt(data.fourps_members));
            updateStatistic('philhealthCount', parseInt(data.philhealth_members));
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
}

// Edit household function
function editHousehold(householdId) {
    // Implementation for editing household
    Swal.fire({
        title: 'Edit Household',
        text: `Edit household ${householdId}`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Edit Members',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to edit page or open modal
            window.location.href = `edit-household.php?id=${householdId}`;
        }
    });
}

// View household details function
function viewHouseholdDetails(householdId) {
    // Implementation for viewing household details
    Swal.fire({
        title: 'Household Details',
        html: `<p>Loading detailed information for ${householdId}...</p>`,
        icon: 'info',
        confirmButtonText: 'Close',
        width: '800px',
        didOpen: () => {
            // Fetch detailed household data
            fetch(`census-backend.php?action=get_household_details&id=${householdId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    // Update modal content with detailed data
                    Swal.update({
                        html: generateHouseholdDetailsHTML(data)
                    });
                })
                .catch(error => {
                    Swal.update({
                        html: `<p class="text-danger">Error loading details: ${error.message}</p>`
                    });
                });
        }
    });
}

// Generate detailed HTML for household
function generateHouseholdDetailsHTML(data) {
    let html = `
        <div class="household-details">
            <h5>Household Information</h5>
            <div class="row mb-3">
                <div class="col-6">
                    <strong>Address:</strong> House #${data.house_number}, ${data.purok}<br>
                    <strong>Total Members:</strong> ${data.total_members}<br>
                    <strong>Household Type:</strong> ${data.household_type || 'Nuclear Family'}
                </div>
                <div class="col-6">
                    <strong>Indigent Status:</strong> ${data.has_indigent ? 'Yes' : 'No'}<br>
                    <strong>4Ps Member:</strong> ${data.has_4ps ? 'Yes' : 'No'}<br>
                    <strong>Senior Citizens:</strong> ${data.senior_count}
                </div>
            </div>
            <h6>Family Members</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Relationship</th>
                            <th>Age</th>
                            <th>Occupation</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
    `;
    
    data.members.forEach(member => {
        html += `
            <tr>
                <td>${member.name}</td>
                <td><span class="badge ${getBadgeClass(member.relationship)}">${member.relationship}</span></td>
                <td>${member.age}</td>
                <td>${member.occupation}</td>
                <td>
                    ${member.is_indigent ? '<span class="badge bg-warning">Indigent</span>' : ''}
                    ${member.is_4ps ? '<span class="badge bg-success">4Ps</span>' : ''}
                    ${member.philhealth !== 'Not Registered' ? '<span class="badge bg-info">PhilHealth</span>' : ''}
                </td>
            </tr>
        `;
    });
    
    html += `
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    return html;
}

// Update relationship function
function updateRelationship(residentId, currentRelationship) {
    const relationships = [
        'HEAD', 'SPOUSE', 'SON', 'DAUGHTER', 'FATHER', 'MOTHER', 
        'BROTHER', 'SISTER', 'GRANDSON', 'GRANDDAUGHTER', 
        'GRANDFATHER', 'GRANDMOTHER', 'SON-IN-LAW', 'DAUGHTER-IN-LAW',
        'BROTHER-IN-LAW', 'SISTER-IN-LAW', 'NEPHEW', 'NIECE',
        'UNCLE', 'AUNT', 'COUSIN', 'BOARDER', 'DOMESTIC HELPER',
        'OTHER RELATIVE', 'NON-RELATIVE'
    ];
    
    let selectOptions = relationships.map(rel => 
        `<option value="${rel}" ${rel === currentRelationship ? 'selected' : ''}>${rel}</option>`
    ).join('');
    
    Swal.fire({
        title: 'Update Relationship',
        html: `
            <div class="mb-3">
                <label class="form-label">Relationship to Head of Family:</label>
                <select class="form-select" id="relationshipSelect">
                    ${selectOptions}
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Update',
        preConfirm: () => {
            const newRelationship = document.getElementById('relationshipSelect').value;
            if (!newRelationship) {
                Swal.showValidationMessage('Please select a relationship');
                return false;
            }
            return newRelationship;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Send update request
            const formData = new FormData();
            formData.append('action', 'update_relationship');
            formData.append('resident_id', residentId);
            formData.append('relationship', result.value);
            
            fetch('census-backend.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', 'Relationship updated successfully', 'success');
                    loadHouseholds(currentPage); // Refresh the display
                } else {
                    throw new Error(data.error || 'Update failed');
                }
            })
            .catch(error => {
                Swal.fire('Error!', error.message, 'error');
            });
        }
    });
}

// Enhanced export function
function exportToExcelEnhanced() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.style.display = 'flex';
    
    // Update loading text for enhanced export
    const loadingText = loadingOverlay.querySelector('h5');
    if (loadingText) {
        loadingText.textContent = 'Generating Complete Census Report...';
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'census-backend.php';
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = 'export_excel';
    
    const typeInput = document.createElement('input');
    typeInput.name = 'type';
    typeInput.value = 'enhanced';
    
    form.appendChild(actionInput);
    form.appendChild(typeInput);
    document.body.appendChild(form);
    
    form.submit();
    
    setTimeout(() => {
        loadingOverlay.style.display = 'none';
        document.body.removeChild(form);
        
        Swal.fire({
            icon: 'success',
            title: 'Export Complete!',
            text: 'Your comprehensive census report has been downloaded successfully.',
            timer: 3000,
            showConfirmButton: false
        });
    }, 3000);
}

// Enhanced filter function with more options
function filterHouseholdsEnhanced() {
    const filters = {
        purok: document.getElementById('purokFilter').value,
        search: document.getElementById('searchInput').value,
        indigent: document.getElementById('indigentFilter')?.value || '',
        fourps: document.getElementById('fourpsFilter')?.value || '',
        seniors: document.getElementById('seniorsFilter')?.value || ''
    };
    
    currentPage = 1;
    loadHouseholds(1);
}

// Pagination (reuse existing function)
function displayPagination(data) {
    const container = document.getElementById('paginationContainer');
    const { total, page, limit, total_pages } = data;
    
    const startEntry = ((page - 1) * limit) + 1;
    const endEntry = Math.min(page * limit, total);
    
    let paginationHTML = `
        <div>
            <p class="text-muted mb-0">Showing ${startEntry} to ${endEntry} of ${total} households</p>
        </div>
        <nav>
            <ul class="pagination mb-0">
    `;
    
    // Previous button
    if (page > 1) {
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadHouseholds(${page - 1})">Previous</a>
            </li>
        `;
    } else {
        paginationHTML += `
            <li class="page-item disabled">
                <span class="page-link">Previous</span>
            </li>
        `;
    }
    
    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, page - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(total_pages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        if (i === page) {
            paginationHTML += `
                <li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>
            `;
        } else {
            paginationHTML += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="loadHouseholds(${i})">${i}</a>
                </li>
            `;
        }
    }
    
    // Next button
    if (page < total_pages) {
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadHouseholds(${page + 1})">Next</a>
            </li>
        `;
    } else {
        paginationHTML += `
            <li class="page-item disabled">
                <span class="page-link">Next</span>
            </li>
        `;
    }
    
    paginationHTML += `
            </ul>
        </nav>
    `;
    
    container.innerHTML = paginationHTML;
}

// Filter and search functions
function filterHouseholds() {
    currentPage = 1;
    loadHouseholds(1);
}

function changeEntriesPerPage() {
    currentPage = 1;
    loadHouseholds(1);
}

// Update statistic with animation (reuse existing function)
function updateStatistic(elementId, value) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    let current = 0;
    const increment = value / 50;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= value) {
            element.textContent = value.toLocaleString();
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current).toLocaleString();
        }
    }, 20);
}

// Auto-refresh functionality
setInterval(() => {
    loadStatistics();
}, 30000); // Refresh every 30 seconds