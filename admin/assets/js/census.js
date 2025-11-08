
let currentPage = 1;
let currentLimit = 25;
let currentSearch = '';

document.addEventListener('DOMContentLoaded', function() {
    loadHouseholds();
    loadStatistics();
});

function loadHouseholds(page = 1) {
    currentPage = page;
    const search = document.getElementById('searchInput').value;
    const limit = document.getElementById('entriesPerPage').value;
    
    currentSearch = search;
    currentLimit = parseInt(limit);
    
    const container = document.getElementById('householdContainer');
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p class="mt-3 text-muted">Loading households...</p>
        </div>
    `;
    
    const params = new URLSearchParams({
        action: 'get_households',
        page: page,
        limit: limit,
        search: search
    });
    
    fetch(`../backend/census-backend.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            displayHouseholds(data.households);
            displayPagination(data);
        })
        .catch(error => {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Error: ${error.message}
                </div>
            `;
        });
}

function displayHouseholds(households) {
    const container = document.getElementById('householdContainer');
    
    if (households.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info text-center py-4">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <h5>No households found</h5>
            </div>
        `;
        return;
    }

    let html = '';

    households.forEach(household => {
        const householdId = `household_${household.house_number}`;
        const headMember = household.members.find(member => member.relationship === 'HEAD') || household.members[0];
        
        html += `
            <div class="card shadow-sm mb-4 household-card">
                <!-- Household Header (Always Visible) -->
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center">
                            <button class="btn btn-light btn-sm me-3" onclick="toggleHousehold('${householdId}')">
                                <i class="fas fa-chevron-right collapse-icon"></i>
                            </button>
                            <div>
                                <h4 class="mb-1">
                                    <i class="fas fa-home me-2"></i>House #${household.house_number}
                                </h4>
                                <p class="mb-0"><small>${household.address || 'No address specified'}</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-light text-dark fs-6 px-3 py-2 me-2">
                            <i class="fas fa-users me-1"></i>${household.member_count} Member${household.member_count > 1 ? 's' : ''}
                        </span>
                        <button class="btn btn-outline-light btn-sm" onclick="viewHouseholdDetails(${household.house_number})">
                            <i class="fas fa-external-link-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Household Head Summary (Always Visible) -->
                <div class="household-head-summary p-3 bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="member-avatar me-3">
                                    ${getInitials(headMember.name)}
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">
                                        ${headMember.name}
                                        <i class="fas fa-crown text-warning ms-2" title="Household Head"></i>
                                    </h5>
                                    <p class="mb-0 text-muted">
                                        <strong>Age:</strong> ${headMember.age} • 
                                        <strong>Sex:</strong> ${headMember.sex} • 
                                        <strong>Contact:</strong> ${headMember.contact || 'Not provided'}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="badge bg-info me-2">${headMember.occupation || 'Not specified'}</span>
                            <span class="badge ${headMember.philhealth === 'Member' ? 'bg-success' : 'bg-secondary'}">
                                ${headMember.philhealth === 'Member' ? 'PhilHealth Member' : 'No PhilHealth'}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Household Members Details (Collapsible) -->
                <div id="${householdId}" class="collapse">
                    <div class="card-body p-0">
                        ${household.members.map((member, idx) => {
                            const isHead = member.relationship === 'HEAD';
                            return `
                                <div class="member-row ${isHead ? 'member-head' : ''} ${idx % 2 === 0 ? 'bg-light' : 'bg-darker'}">
                                    <div class="row g-0">
                                        <!-- Left Column -->
                                        <div class="col-lg-4 border-end p-4">
                                            <div class="d-flex align-items-start">
                                                <div class="member-avatar me-3">
                                                    ${getInitials(member.name)}
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-2 fw-bold">
                                                        ${member.name}
                                                    </h5>
                                                    <span class="badge badge-relationship-${member.relationship.toLowerCase()} mb-2">
                                                        ${member.relationship}
                                                    </span>
                                                    <div class="mt-3">
                                                        <p class="mb-2"><strong>Age:</strong> ${member.age} years old</p>
                                                        <p class="mb-2"><strong>Sex:</strong> ${member.sex}</p>
                                                        <p class="mb-0"><strong>Birthday:</strong> ${formatDate(member.birthdate)}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Middle Column -->
                                        <div class="col-lg-4 border-end p-4">
                                            <h6 class="text-muted text-uppercase mb-3" style="font-size: 0.75rem;">Personal Information</h6>
                                            <p class="mb-2"><strong>Civil Status:</strong> ${member.civil_status}</p>
                                            <p class="mb-2"><strong>Education:</strong> ${member.education}</p>
                                            <p class="mb-2"><strong>Religion:</strong> ${member.religion}</p>
                                            <p class="mb-2"><strong>Occupation:</strong> ${member.occupation}</p>
                                            <p class="mb-2"><strong>Contact:</strong> ${member.contact}</p>
                                            <p class="mb-0"><strong>Email:</strong> ${member.email}</p>
                                        </div>

                                        <!-- Right Column -->
                                        <div class="col-lg-4 p-4">
                                            <h6 class="text-muted text-uppercase mb-3" style="font-size: 0.75rem;">Status & Programs</h6>
                                            <div class="mb-3">
                                                <strong class="d-block mb-2">Health Coverage:</strong>
                                                ${member.philhealth === 'Member' 
                                                    ? '<span class="badge bg-info text-white px-3 py-2"><i class="fas fa-notes-medical me-1"></i>PhilHealth Member</span>' 
                                                    : '<span class="badge bg-secondary px-3 py-2">No PhilHealth</span>'}
                                            </div>
                                            <div class="mb-3">
                                                <strong class="d-block mb-2">Social Programs:</strong>
                                                <div class="d-flex flex-wrap gap-2">
                                                    ${member.is_indigent ? '<span class="badge bg-warning text-dark px-3 py-2"><i class="fas fa-hand-holding-heart me-1"></i>Indigent</span>' : ''}
                                                    ${member.is_4ps ? '<span class="badge bg-success px-3 py-2"><i class="fas fa-hands-helping me-1"></i>4Ps Member</span>' : ''}
                                                    ${parseInt(member.age) >= 60 ? '<span class="badge bg-purple text-white px-3 py-2"><i class="fas fa-walking me-1"></i>Senior Citizen</span>' : ''}
                                                    ${parseInt(member.age) < 18 ? '<span class="badge bg-pink text-white px-3 py-2"><i class="fas fa-child me-1"></i>Minor</span>' : ''}
                                                    ${!member.is_indigent && !member.is_4ps && parseInt(member.age) < 60 && parseInt(member.age) >= 18
                                                        ? '<span class="badge bg-light text-dark px-3 py-2">None</span>'
                                                        : ''}
                                                </div>
                                            </div>
                                            ${member.medical_history !== 'None' ? `
                                            <div>
                                                <strong class="d-block mb-2">Medical History:</strong>
                                                <p class="text-muted small mb-0">${member.medical_history}</p>
                                            </div>` : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>

                    <!-- Card Footer -->
                    <div class="card-footer bg-light py-3">
                        <span class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Household Composition: ${getHouseholdComposition(household.members)}
                        </span>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function toggleHousehold(householdId) {
    const element = document.getElementById(householdId);
    const card = element.closest('.household-card');
    const icon = card.querySelector('.collapse-icon');
    
    // Check current state before toggling
    const isCurrentlyOpen = element.classList.contains('show');
    
    // Toggle the collapse
    if (isCurrentlyOpen) {
        element.classList.remove('show');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
    } else {
        element.classList.add('show');
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
    }
}

function getInitials(name) {
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
}

function formatDate(dateString) {
    if (!dateString || dateString === '0000-00-00') return 'Not provided';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function getHouseholdComposition(members) {
    const adults = members.filter(m => parseInt(m.age) >= 18 && parseInt(m.age) < 60).length;
    const children = members.filter(m => parseInt(m.age) < 18).length;
    const seniors = members.filter(m => parseInt(m.age) >= 60).length;
    
    let parts = [];
    if (adults > 0) parts.push(`${adults} Adult${adults > 1 ? 's' : ''}`);
    if (children > 0) parts.push(`${children} Child${children > 1 ? 'ren' : ''}`);
    if (seniors > 0) parts.push(`${seniors} Senior${seniors > 1 ? 's' : ''}`);
    
    return parts.join(', ') || 'No members';
}

function viewHouseholdDetails(householdId) {
    Swal.fire({
        title: 'Loading...',
        html: '<div class="spinner-border text-primary"></div>',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    fetch(`../backend/census-backend.php?action=get_household_details&id=${householdId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            
            Swal.fire({
                title: `Household #${data.house_number} - Complete Details`,
                html: `
                    <div class="text-start">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Total Members:</strong> ${data.total_members}
                            </div>
                            <div class="col-6">
                                <strong>Household Type:</strong> ${data.household_type}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4">
                                <strong>Indigent:</strong> ${data.has_indigent ? 'Yes' : 'No'}
                            </div>
                            <div class="col-4">
                                <strong>4Ps:</strong> ${data.has_4ps ? 'Yes' : 'No'}
                            </div>
                            <div class="col-4">
                                <strong>Seniors:</strong> ${data.senior_count}
                            </div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Relationship</th>
                                    <th>Age</th>
                                    <th>Occupation</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.members.map(m => `
                                    <tr>
                                        <td>${m.name}</td>
                                        <td><span class="badge badge-relationship-${m.relationship.toLowerCase()}">${m.relationship}</span></td>
                                        <td>${m.age}</td>
                                        <td>${m.occupation}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `,
                width: '800px',
                confirmButtonText: 'Close'
            });
        })
        .catch(error => {
            Swal.fire('Error', error.message, 'error');
        });
}

function loadStatistics() {
    fetch('../backend/census-backend.php?action=get_statistics')
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
        });
}

function updateStatistic(elementId, value) {
    const element = document.getElementById(elementId);
    if (!element) return;
    element.textContent = value.toLocaleString();
}

function displayPagination(data) {
    const container = document.getElementById('paginationContainer');
    const { total, page, limit, total_pages } = data;
    
    const startEntry = ((page - 1) * limit) + 1;
    const endEntry = Math.min(page * limit, total);
    
    let html = `
        <div><span class="text-muted">Showing ${startEntry} to ${endEntry} of ${total} households</span></div>
        <nav><ul class="pagination mb-0">
    `;
    
    if (page > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadHouseholds(${page - 1}); return false;">Previous</a></li>`;
    }
    
    for (let i = Math.max(1, page - 2); i <= Math.min(total_pages, page + 2); i++) {
        html += `<li class="page-item ${i === page ? 'active' : ''}">
            <a class="page-link" href="#" onclick="loadHouseholds(${i}); return false;">${i}</a>
        </li>`;
    }
    
    if (page < total_pages) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadHouseholds(${page + 1}); return false;">Next</a></li>`;
    }
    
    html += '</ul></nav>';
    container.innerHTML = html;
}

function filterHouseholds() {
    loadHouseholds(1);
}

function changeEntriesPerPage() {
    loadHouseholds(1);
}

function exportToExcel(type) {
    const loadingOverlay = document.getElementById('loadingOverlay');
    loadingOverlay.style.display = 'flex';
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../backend/census-backend.php';
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = 'export_excel';
    
    const typeInput = document.createElement('input');
    typeInput.name = 'type';
    typeInput.value = type;
    
    form.appendChild(actionInput);
    form.appendChild(typeInput);
    document.body.appendChild(form);
    form.submit();
    
    setTimeout(() => {
        loadingOverlay.style.display = 'none';
        document.body.removeChild(form);
        Swal.fire({
            icon: 'success',
            title: 'Export Successful!',
            text: 'Your Excel file has been downloaded.',
            timer: 2000,
            showConfirmButton: false
        });
    }, 2000);
}

function printPurokPDF(purok) {
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingText = loadingOverlay.querySelector('h5');
    const loadingSubtext = loadingOverlay.querySelector('p');
    
    loadingText.textContent = 'Preparing PDF...';
    loadingSubtext.textContent = `Formatting ${purok === 'all' ? 'All Puroks' : purok} census data for long bond paper`;
    loadingOverlay.style.display = 'flex';
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../backend/census-backend.php';
    form.target = '_blank'; // Open in new window for printing
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = 'export_pdf';
    
    const purokInput = document.createElement('input');
    purokInput.name = 'purok';
    purokInput.value = purok;
    
    form.appendChild(actionInput);
    form.appendChild(purokInput);
    document.body.appendChild(form);
    
    form.submit();
    
    // Hide loading overlay after a short delay
    setTimeout(() => {
        loadingOverlay.style.display = 'none';
        document.body.removeChild(form);
        
        Swal.fire({
            icon: 'success',
            title: ' PDF Ready!',
            html: `
                <div class="text-start">
                    <p>The <strong>${purok === 'all' ? 'All Puroks' : purok}</strong> census report is opening in landscape format.</p>
                </div>
            `,
            timer: 5000,
            showConfirmButton: true,
            confirmButtonText: 'OK'
        });
    }, 2000);
}

setInterval(loadStatistics, 60000);