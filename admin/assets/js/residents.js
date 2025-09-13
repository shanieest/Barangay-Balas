// Global variables
let currentResidentId = null;
let currentRequestId = null;
let currentResidentPage = 1;
let currentRequestPage = 1;
let currentRequestFilter = 'all';
let currentResidentSearch = '';
const perPage = 10;

// Function to safely get DOM elements
function getElement(selector) {
    const el = document.querySelector(selector);
    if (!el) {
        console.warn(`Element not found: ${selector}`);
    }
    return el;
}

// Function to show toast notifications
function showToast(message, type = 'success') {
    let toastContainer = getElement('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    const toastElement = document.createElement('div');
    toastElement.className = `toast align-items-center text-white bg-${type} border-0`;
    toastElement.setAttribute('role', 'alert');
    toastElement.setAttribute('aria-live', 'assertive');
    toastElement.setAttribute('aria-atomic', 'true');
    
    toastElement.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    toastContainer.appendChild(toastElement);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Function to refresh resident list with pagination and search
function refreshResidentList(page = 1, search = '') {
    currentResidentPage = page;
    currentResidentSearch = search;
    
    const url = `residents-backend.php?action=list&page=${page}&per_page=${perPage}&search=${encodeURIComponent(search)}`;
    
    fetch(url)
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                renderResidentsTable(data.data, data.pagination);
            } else {
                showToast(data.message || 'Failed to load residents', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load residents: ' + error.message, 'danger');
        });
}

// Function to render residents table with data
function renderResidentsTable(residents, pagination) {
    const tableBody = getElement('#residentsTable tbody');
    if (!tableBody) 
        return;
    
    tableBody.innerHTML = '';
    
    if (!residents || residents.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="7" class="text-center">No residents found</td>`;
        tableBody.appendChild(row);
        updatePagination('resident', pagination);
        return;
    }
    
    residents.forEach((resident, index) => {
        const row = createResidentRow(resident, index);
        tableBody.appendChild(row);
    });
    
    updatePagination('resident', pagination);
    // FIXED: Move event listener attachment after DOM is updated
    attachButtonEventListeners();
}

// Function to create a resident table row
function createResidentRow(resident, index) {
    const row = document.createElement('tr');
    
    // Account status badge
    const accountStatusBadge = createAccountStatusBadge(resident.account_status);
    
    row.innerHTML = `
        <td>${index + 1}</td>
        <td>${resident.last_name}, ${resident.first_name} ${resident.middle_name || ''} ${resident.suffix || ''}</td>
        <td>${resident.email || 'N/A'}</td>
        <td>${resident.contact_number}</td>
        <td>${resident.birthdate}</td>
        <td>${accountStatusBadge}</td>
        <td>
            <button class="btn btn-sm btn-info view-btn" data-id="${resident.id}" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-warning edit-btn" data-id="${resident.id}" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger delete-btn" data-id="${resident.id}" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    return row;
}

// Function to create account status badge
function createAccountStatusBadge(status) {
    if (!status) return '<span class="badge bg-secondary">No Account</span>';
    
    const badgeClasses = {
        'Approved': 'account-approved',
        'Pending': 'account-pending',
        'Disapproved': 'account-disapproved'
    };
    
    const badgeClass = badgeClasses[status] || 'bg-secondary';
    return `<span class="badge ${badgeClass}">${status}</span>`;
}

// FIXED: Renamed and improved event listener attachment
function attachButtonEventListeners() {
    console.log('Attaching button event listeners...');
    
    // Remove existing listeners to prevent duplicates
    document.querySelectorAll('.view-btn, .edit-btn, .delete-btn').forEach(btn => {
        btn.replaceWith(btn.cloneNode(true));
    });
    
    // View buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            console.log('View button clicked for ID:', id);
            viewResident(id);
        });
    });
    
    // Edit buttons
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            console.log('Edit button clicked for ID:', id);
            editResident(id);
        });
    });
    
    // Delete buttons
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            console.log('Delete button clicked for ID:', id);
            showDeleteModal(id);
        });
    });
    
    console.log('Event listeners attached to', document.querySelectorAll('.view-btn, .edit-btn, .delete-btn').length, 'buttons');
}

// Function to update pagination controls
function updatePagination(type, pagination) {
    const prefix = type === 'resident' ? 'resident' : 'request';
    const totalPages = pagination.total_pages;
    const currentPage = pagination.page;
    
    // Update pagination info
    const paginationInfo = getElement(`#${prefix}-pagination .pagination-info`);
    if (paginationInfo) {
        const startItem = (currentPage - 1) * perPage + 1;
        const endItem = Math.min(currentPage * perPage, pagination.total);
        paginationInfo.textContent = `Showing ${startItem} to ${endItem} of ${pagination.total} entries`;
    }
    
    // Update pagination buttons
    const prevBtn = document.getElementById(`prev${prefix.charAt(0).toUpperCase() + prefix.slice(1)}Page`);
    const nextBtn = document.getElementById(`next${prefix.charAt(0).toUpperCase() + prefix.slice(1)}Page`);
    
    if (prevBtn) prevBtn.classList.toggle('disabled', currentPage === 1);
    if (nextBtn) nextBtn.classList.toggle('disabled', currentPage >= totalPages);
    
    // Update page numbers (simple implementation)
    const paginationContainer = getElement(`#${prefix}-pagination .pagination`);
    if (paginationContainer) {
        const pageItems = paginationContainer.querySelectorAll('.page-item:not(:first-child):not(:last-child)');
        pageItems.forEach(item => item.remove());
        
        // Add page numbers
        for (let i = 1; i <= totalPages; i++) {
            const pageItem = document.createElement('li');
            pageItem.className = `page-item ${i === currentPage ? 'active' : ''}`;
            pageItem.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            
            pageItem.addEventListener('click', (e) => {
                e.preventDefault();
                if (type === 'resident') {
                    refreshResidentList(i, currentResidentSearch);
                } else {
                    refreshAccountRequests(i, currentRequestFilter);
                }
            });
            
            if (nextBtn) {
                nextBtn.parentNode.insertBefore(pageItem, nextBtn);
            }
        }
    }
}

// Function to refresh account requests with pagination and filtering
function refreshAccountRequests(page = 1, status = 'all') {
    currentRequestPage = page;
    currentRequestFilter = status;
    
    const url = `residents-backend.php?action=account_requests&page=${page}&per_page=${perPage}&status=${status}`;
    
    fetch(url)
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                renderRequestsTable(data.data, data.pagination);
                updatePendingCount(data.pending_count || 0);
            } else {
                showToast(data.message || 'Failed to load account requests', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load account requests: ' + error.message, 'danger');
        });
}

// Function to render requests table with data
function renderRequestsTable(requests, pagination) {
    const tableBody = getElement('#requestsTable tbody');
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    if (!requests || requests.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="8" class="text-center">No requests found</td>`;
        tableBody.appendChild(row);
        return;
    }
    
    requests.forEach((request, index) => {
        const row = createRequestRow(request, index);
        tableBody.appendChild(row);
    });
    
    updatePagination('request', pagination);
    addRequestButtonEventListeners();
}

// Function to create a request table row
function createRequestRow(request, index) {
    const row = document.createElement('tr');
    
    // Status badge
    const statusBadge = createAccountStatusBadge(request.account_status);
    
    // Processed by info
    const processedBy = request.processed_by ? 
        `${request.processed_by} (${request.date_processed})` : 'N/A';
    
    // Action buttons (only show for pending requests)
    const actionButtons = request.account_status === 'Pending' ? `
        <button class="btn btn-sm btn-success approve-request-btn" data-id="${request.id}">
            <i class="fas fa-check"></i>
        </button>
        <button class="btn btn-sm btn-danger reject-request-btn" data-id="${request.id}">
            <i class="fas fa-times"></i>
        </button>
    ` : '';
    
    row.innerHTML = `
        <td>${index + 1}</td>
        <td>${request.last_name}, ${request.first_name}</td>
        <td>${request.email}</td>
        <td>${request.contact_number}</td>
        <td>${request.date_requested}</td>
        <td>${statusBadge}</td>
        <td>${processedBy}</td>
        <td>
            <button class="btn btn-sm btn-info view-request-btn" data-id="${request.id}">
                <i class="fas fa-eye"></i>
            </button>
            ${actionButtons}
        </td>
    `;
    
    return row;
}

// Function to update pending count badge
function updatePendingCount(pendingCount) {
    const pendingBadge = getElement('#pending-count');
    if (pendingBadge) {
        pendingBadge.textContent = pendingCount;
        pendingBadge.style.display = pendingCount > 0 ? 'inline-block' : 'none';
    }
}

// Function to handle API responses
function handleResponse(response) {
    if (!response.ok) {
        return response.text().then(text => {
            throw new Error(text || 'Network response was not ok');
        });
    }
    
    return response.text().then(text => {
        try {
            return JSON.parse(text);
        } catch (e) {
            // Log the actual response for debugging
            console.error('Invalid JSON response:', text.substring(0, 100));
            throw new Error('Invalid JSON response from server');
        }
    });
}

// Function to add event listeners to request action buttons
function addRequestButtonEventListeners() {
    // View request buttons
    document.querySelectorAll('.view-request-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            viewRequest(this.getAttribute('data-id'));
        });
    });
    
    // Approve request buttons
    document.querySelectorAll('.approve-request-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            showProcessRequestModal(this.getAttribute('data-id'), 'approve');
        });
    });
    
    // Reject request buttons
    document.querySelectorAll('.reject-request-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            showProcessRequestModal(this.getAttribute('data-id'), 'reject');
        });
    });
}

// FIXED: Improved View resident function with better error handling
function viewResident(id) {
    if (!id) {
        showToast('Invalid resident ID', 'danger');
        return;
    }

    console.log('Viewing resident with ID:', id);
    currentResidentId = id;
    
    const viewModal = getElement('#viewResidentModal');
    if (!viewModal) {
        showToast('View modal not found', 'danger');
        return;
    }

    // Show loading state
    viewModal.querySelectorAll('.resident-data').forEach(el => {
        el.textContent = 'Loading...';
    });
    
    // Show the modal first
    const modal = new bootstrap.Modal(viewModal);
    modal.show();
    
    fetch(`residents-backend.php?action=list&id=${id}`)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text.substring(0, 200) + '...');
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('Invalid JSON response from server');
            }
        })
        .then(data => {
            console.log('Parsed data:', data);
            
            if (data.success && data.data) {
                displayResidentModal(data.data);
            } else {
                throw new Error(data.message || 'No resident data received');
            }
        })
        .catch(error => {
            console.error('Error loading resident:', error);
            showToast('Failed to load resident details: ' + error.message, 'danger');
            modal.hide();
        });
}

// FIXED: Improved Edit resident function
function editResident(id) {
    if (!id) {
        showToast('Invalid resident ID', 'danger');
        return;
    }

    console.log('Editing resident with ID:', id);
    
    fetch(`residents-backend.php?action=list&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data) {
                populateEditForm(data.data);
            } else {
                throw new Error(data.message || 'No resident data received');
            }
        })
        .catch(error => {
            console.error('Error loading resident for edit:', error);
            showToast('Failed to load resident details: ' + error.message, 'danger');
        });
}

// Display resident data in modal
function displayResidentModal(resident) {
    const viewModal = getElement('#viewResidentModal');
    if (!viewModal) return;

    console.log('Displaying resident data:', resident);

    // Helper function to safely get value or return 'N/A'
    const safeValue = (value) => {
        if (value === null || value === undefined || value === '' || value === 'null') {
            return 'N/A';
        }
        return value;
    };

    // Format full name
    const fullName = [
        safeValue(resident.first_name),
        safeValue(resident.middle_name),
        safeValue(resident.last_name),
        safeValue(resident.suffix)
    ].filter(part => part !== 'N/A' && part.trim()).join(' ') || 'N/A';

    // Format birthdate and calculate age
    let formattedBirthdate = 'N/A';
    let calculatedAge = 'N/A';
    
    if (resident.birthdate && resident.birthdate !== '0000-00-00') {
        const birthdate = new Date(resident.birthdate);
        if (!isNaN(birthdate.getTime())) {
            formattedBirthdate = birthdate.toLocaleDateString('en-US', {
                year: 'numeric', 
                month: 'long', 
                day: 'numeric'
            });
            
            // Calculate age if not provided
            if (resident.age && resident.age > 0) {
                calculatedAge = resident.age + ' years old';
            } else {
                const today = new Date();
                let age = today.getFullYear() - birthdate.getFullYear();
                const monthDiff = today.getMonth() - birthdate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }
                calculatedAge = age + ' years old';
            }
        }
    }

    // Update basic information
    updateModalText(viewModal, '.resident-name', fullName);
    updateModalText(viewModal, '.resident-id', resident.id ? `Resident ID: ${resident.id}` : 'N/A');
    updateModalText(viewModal, '.resident-birthdate', formattedBirthdate);
    
    const sex = resident.sex ? 
        (resident.sex.toLowerCase() === 'male' ? 'Male' : 
         resident.sex.toLowerCase() === 'female' ? 'Female' : 
         resident.sex) : 'N/A';
    updateModalText(viewModal, '.resident-sex', sex);
    
    updateModalText(viewModal, '.resident-civil-status', safeValue(resident.civil_status));
    updateModalText(viewModal, '.resident-contact', safeValue(resident.contact_number));
    updateModalText(viewModal, '.resident-email', safeValue(resident.email));
    updateModalText(viewModal, '.resident-age', calculatedAge);
    updateModalText(viewModal, '.resident-address', safeValue(resident.address));

    // Handle photo path
    let photoPath = 'img/default-profile.jpg';
    if (resident.photo_path && resident.photo_path !== 'null' && resident.photo_path !== '') {
        photoPath = `../auth/uploads/photos/${resident.photo_path}`;
    }

    // Handle valid ID path
    let idPath = 'img/default-id.jpg';
    if (resident.valid_id_path && resident.valid_id_path !== 'null' && resident.valid_id_path !== '') {
        let cleanPath = resident.valid_id_path;
        cleanPath = cleanPath.replace(/^uploads\/valid_ids\//, '');
        cleanPath = cleanPath.replace(/^auth\/uploads\/valid_ids\//, '');
        idPath = `../auth/uploads/valid_ids/${cleanPath}`;
    }

    updateModalImage(viewModal, '.resident-photo', photoPath);
    updateModalImage(viewModal, '.resident-valid-id', idPath);

    // Resident status badge
    const residentStatus = resident.resident_status || 'Active';
    const statusClass = residentStatus === 'Active' ? 'bg-primary' :
                       residentStatus === 'Inactive' ? 'bg-secondary' :
                       residentStatus === 'Deceased' ? 'bg-dark' : 'bg-info';
    updateModalField(viewModal, '.resident-status-badge', `badge ${statusClass}`, residentStatus);

    // Account Information Section
    const accountSection = viewModal.querySelector('.account-details');
    if (accountSection) {
        const accountStatus = resident.account_status;
        if (accountStatus && accountStatus !== 'null') {
            accountSection.style.display = 'block';
            const accountStatusBadge = accountSection.querySelector('.account-status-badge');
            if (accountStatusBadge) {
                accountStatusBadge.className = 'badge account-status-badge';
                if (accountStatus === 'Approved') {
                    accountStatusBadge.classList.add('account-approved');
                } else if (accountStatus === 'Pending') {
                    accountStatusBadge.classList.add('account-pending');
                } else if (accountStatus === 'Disapproved') {
                    accountStatusBadge.classList.add('account-disapproved');
                } else {
                    accountStatusBadge.classList.add('bg-secondary');
                }
                accountStatusBadge.textContent = accountStatus;
            }
            updateModalText(viewModal, '.resident-processed-by', resident.account_processed_by || 'N/A');
            updateModalText(viewModal, '.resident-date-processed', resident.account_date_processed || 'N/A');
            updateModalText(viewModal, '.resident-account-notes', resident.account_notes || 'N/A');
        } else {
            accountSection.style.display = 'none';
        }
    }
}

// Helper functions for updating modal fields
function safeValue(value) {
    if (
        value === null || value === undefined ||
        value === '' ||
        value === 'null' || value === 'undefined' ||
        (typeof value === 'string' && value.trim() === '')
    ) {
        return 'N/A';
    }
    return value;
}

function updateModalText(modal, selector, value) {
    const element = modal.querySelector(selector);
    if (element) {
        element.textContent = safeValue(value);
    } else {
        console.warn(`Modal element not found: ${selector}`);
    }
}

function updateModalField(modal, selector, className, value) {
    const element = modal.querySelector(selector);
    if (element) {
        if (className) {
            element.className = className;
        }
        element.textContent = safeValue(value);
    } else {
        console.warn(`Modal element not found: ${selector}`);
    }
}

function updateModalImage(modal, selector, src) {
    const element = modal.querySelector(selector);
    if (!element) {
        console.warn(`Modal image element not found: ${selector}`);
        return;
    }

    console.log(`Setting image source for ${selector}:`, src);
    
    if (src && src !== 'img/default-profile.jpg' && src !== 'img/default-id.jpg') {
        element.src = src;
    } else {
        element.src = selector.includes('id') ? 'img/default-id.jpg' : 'img/default-profile.jpg';
    }

    element.onerror = function () {
        console.error(`Failed to load image: ${this.src}`);
        this.onerror = null;
        this.src = selector.includes('id') ? 'img/default-id.jpg' : 'img/default-profile.jpg';
    };
    
    element.onload = function() {
        console.log(`Successfully loaded image: ${this.src}`);
    };
}

// View request function
function viewRequest(id) {
    if (!id) {
        showToast('Invalid request ID', 'danger');
        return;
    }
    
    fetch(`residents-backend.php?action=account_requests&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('Invalid JSON response from server');
            }
        })
        .then(data => {
            console.log('Parsed data:', data);
            
            if (data.success && data.data && data.data.length > 0) {
                const request = data.data[0];
                displayRequestModal(request);
            } else {
                throw new Error('No request data received or empty data array');
            }
        })
        .catch(error => {
            console.error('Error loading request:', error);
            showToast('Failed to load request details: ' + error.message, 'danger');
        });
}

// Display request data in modal (keeping existing implementation)
function displayRequestModal(request) {
    const viewModal = getElement('#viewRequestModal');
    if (!viewModal) return;

    console.log('Displaying request data:', request);

    const safeValue = (value) => {
        if (value === null || value === undefined || value === '' || value === 'null') {
            return 'N/A';
        }
        return value;
    };

    const fullName = [
        safeValue(request.first_name),
        safeValue(request.middle_name),
        safeValue(request.last_name),
        safeValue(request.suffix)
    ].filter(part => part !== 'N/A' && part.trim()).join(' ') || 'N/A';

    let formattedBirthdate = 'N/A';
    if (request.birthdate && request.birthdate !== '0000-00-00') {
        const birthdate = new Date(request.birthdate);
        if (!isNaN(birthdate.getTime())) {
            formattedBirthdate = birthdate.toLocaleDateString('en-US', {
                year: 'numeric', 
                month: 'long', 
                day: 'numeric'
            });
            
            if (request.age && request.age > 0) {
                formattedBirthdate += ` (${request.age} years old)`;
            }
        }
    }

    const requestId = request.account_id || request.id;
    const formattedRequestId = requestId ? `${requestId.toString().padStart( '0')}` : 'N/A';

    updateModalText(viewModal, '.request-name', fullName);
    updateModalText(viewModal, '.request-id', `Request ID: ${formattedRequestId}`);
    updateModalText(viewModal, '.request-birthdate', formattedBirthdate);
    
    const sex = request.sex ? 
        (request.sex.toLowerCase() === 'male' ? 'Male' : 
         request.sex.toLowerCase() === 'female' ? 'Female' : 
         request.sex) : 'N/A';
    updateModalText(viewModal, '.request-sex', sex);
    
    updateModalText(viewModal, '.request-contact', safeValue(request.contact_number));
    updateModalText(viewModal, '.request-email', safeValue(request.email));
    updateModalText(viewModal, '.request-address', safeValue(request.address));

    const accountStatus = request.account_status || 'Pending';
    let statusClass = 'bg-secondary';
    if (accountStatus === 'Approved') {
        statusClass = 'account-approved';
    } else if (accountStatus === 'Pending') {
        statusClass = 'account-pending';
    } else if (accountStatus === 'Disapproved') {
        statusClass = 'account-disapproved';
    }
    updateModalField(viewModal, '.request-status-badge', `badge ${statusClass}`, accountStatus);

    let photoPath = 'img/default-profile.jpg';
    if (request.photo_path && request.photo_path !== 'null' && request.photo_path !== '') {
        photoPath = `../auth/uploads/photos/${request.photo_path}`;
    }

    let idPath = 'img/default-id.jpg';
    if (request.valid_id_path && request.valid_id_path !== 'null' && request.valid_id_path !== '') {
        let cleanPath = request.valid_id_path;
        cleanPath = cleanPath.replace(/^uploads\/valid_ids\//, '');
        cleanPath = cleanPath.replace(/^auth\/uploads\/valid_ids\//, '');
        idPath = `../auth/uploads/valid_ids/${cleanPath}`;
    }
    
    updateModalImage(viewModal, '.request-photo', photoPath);
    updateModalImage(viewModal, '.request-valid-id', idPath);

    const dateRequested = request.date_requested;
    updateModalText(viewModal, '.request-date-requested', 
        dateRequested && dateRequested !== '0000-00-00 00:00:00' ? 
        new Date(dateRequested).toLocaleDateString() : 'N/A');

    updateModalText(viewModal, '.request-processed-by', safeValue(request.processed_by));
    updateModalText(viewModal, '.request-date-processed', 
        request.date_processed && request.date_processed !== '0000-00-00 00:00:00' ? 
        new Date(request.date_processed).toLocaleDateString() : 'N/A');
    updateModalText(viewModal, '.request-notes', safeValue(request.notes));

    const processedInfo = viewModal.querySelector('#requestProcessedInfo');
    if (processedInfo) {
        const isPending = accountStatus === 'Pending';
        processedInfo.style.display = isPending ? 'none' : 'block';
    }

    const approveBtn = getElement('#approveRequestBtn');
    const rejectBtn = getElement('#rejectRequestBtn');
    
    if (approveBtn && rejectBtn) {
        const isPending = accountStatus === 'Pending';
        const buttonRequestId = request.account_id || request.id;
        
        approveBtn.dataset.id = buttonRequestId;
        rejectBtn.dataset.id = buttonRequestId;
        
        approveBtn.style.display = isPending ? 'inline-block' : 'none';
        rejectBtn.style.display = isPending ? 'inline-block' : 'none';
    }

    currentRequestId = request.account_id || request.id;
    const modal = new bootstrap.Modal(viewModal);
    modal.show();
}

// Populate edit form with resident data
function populateEditForm(resident) {
    const editModal = getElement('#editResidentModal');
    if (!editModal) return;
    
    const editResidentId = getElement('#editResidentId');
    const editFirstName = getElement('#editFirstName');
    const editMiddleName = getElement('#editMiddleName');
    const editLastName = getElement('#editLastName');
    const editSuffix = getElement('#editSuffix');
    const editSex = getElement('#editSex');
    const editContactNumber = getElement('#editContactNumber');
    const editEmail = getElement('#editEmail');
    const editBirthdate = getElement('#editBirthdate');
    const editAge = getElement('#editAge');
    
    // Parse address to get house number and purok
    let houseNumber = '';
    let purok = '';
    
    if (resident.address) {
        const addressRegex = /House\s(\w+),\sPurok\s(\w+),/i;
        const matches = resident.address.match(addressRegex);
        
        if (matches && matches.length >= 3) {
            houseNumber = matches[1];
            purok = matches[2];
        }
    }
    
    const editHouseNumber = getElement('#editHouseNumber');
    const editPurok = getElement('#editPurok');
    const editAddress = getElement('#editAddress');
    
    if (editResidentId) editResidentId.value = resident.id;
    if (editFirstName) editFirstName.value = resident.first_name || '';
    if (editMiddleName) editMiddleName.value = resident.middle_name || '';
    if (editLastName) editLastName.value = resident.last_name || '';
    if (editSuffix) editSuffix.value = resident.suffix || '';
    if (editSex) editSex.value = resident.sex || '';
    if (editContactNumber) editContactNumber.value = resident.contact_number || '';
    if (editEmail) editEmail.value = resident.email || '';
    if (editBirthdate) editBirthdate.value = resident.birthdate || '';
    if (editAge) editAge.value = resident.age || '';
    if (editHouseNumber) editHouseNumber.value = houseNumber;
    if (editPurok) editPurok.value = purok;
    if (editAddress) editAddress.value = resident.address || '';
    
    const modal = new bootstrap.Modal(editModal);
    modal.show();
}

// Update resident function
function updateResident() {
    const form = getElement('#editResidentForm');
    if (!form) return;
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    const formData = new FormData(form);
    const updateBtn = getElement('#updateResidentBtn');
    if (!updateBtn) return;
    
    const originalText = updateBtn.innerHTML;
    
    updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
    updateBtn.disabled = true;

    // Convert FormData to JSON for the backend
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }

    fetch('residents-backend.php?action=edit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(handleResponse)
    .then(data => {
        if (data.success) {
            showToast('Resident updated successfully');
            refreshResidentList(currentResidentPage, currentResidentSearch);
            const modal = bootstrap.Modal.getInstance(getElement('#editResidentModal'));
            if (modal) modal.hide();
        } else {
            throw new Error(data.message || 'Failed to update resident');
        }
    })
    .catch(error => {
        showToast(error.message, 'danger');
        console.error('Error:', error);
    })
    .finally(() => {
        updateBtn.innerHTML = originalText;
        updateBtn.disabled = false;
    });
}

// Show delete confirmation modal
function showDeleteModal(id) {
    fetch(`residents-backend.php?action=list&id=${id}`)
        .then(handleResponse)
        .then(data => {
            if (data.success && data.data) {
                const resident = data.data;
                const deleteResidentName = getElement('#deleteResidentName');
                const deleteResidentIdSpan = getElement('#deleteResidentId');
                const confirmDeleteBtn = getElement('#confirmDeleteBtn');
                
                if (deleteResidentName) deleteResidentName.textContent = `${resident.first_name} ${resident.last_name}`;
                if (deleteResidentIdSpan) deleteResidentIdSpan.textContent = resident.id;
                if (confirmDeleteBtn) confirmDeleteBtn.dataset.id = resident.id;
                
                const modal = new bootstrap.Modal(getElement('#deleteResidentModal'));
                modal.show();
            } else {
                showToast('Resident not found', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load resident details: ' + error.message, 'danger');
        });
}

// Delete resident function
function deleteResident(id) {
    const confirmDeleteBtn = getElement('#confirmDeleteBtn');
    const originalText = confirmDeleteBtn ? confirmDeleteBtn.innerHTML : '';
    
    if (confirmDeleteBtn) {
        confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
        confirmDeleteBtn.disabled = true;
    }

    fetch('residents-backend.php?action=delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${encodeURIComponent(id)}`
    })
    .then(handleResponse)
    .then(data => {
        if (data.success) {
            showToast('Resident deleted successfully');
            refreshResidentList(currentResidentPage, currentResidentSearch);
            const modal = bootstrap.Modal.getInstance(getElement('#deleteResidentModal'));
            if (modal) modal.hide();
        } else {
            throw new Error(data.message || 'Error deleting resident');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to delete resident: ' + error.message, 'danger');
    })
    .finally(() => {
        if (confirmDeleteBtn) {
            confirmDeleteBtn.innerHTML = originalText;
            confirmDeleteBtn.disabled = false;
        }
    });
}

function showProcessRequestModal(id, action) {
    currentRequestId = id;
    
    const modal = getElement('#processRequestModal');
    if (!modal) return;
    
    const header = modal.querySelector('#processRequestModalHeader');
    const title = modal.querySelector('#processRequestModalLabel');
    const message = modal.querySelector('#processRequestMessage');
    const submitBtn = modal.querySelector('#confirmProcessRequestBtn');
    const noteTextarea = modal.querySelector('#requestNote');
    
    if (action === 'approve') {
        if (header) header.className = 'modal-header bg-success text-white';
        if (title) title.textContent = 'Approve Account Request';
        if (message) message.textContent = 'You may provide optional notes for this approval:';
        if (submitBtn) {
            submitBtn.className = 'btn btn-success';
            submitBtn.textContent = 'Approve';
        }
        if (noteTextarea) {
            noteTextarea.required = false;
            noteTextarea.classList.remove('is-invalid');
        }
    } else {
        if (header) header.className = 'modal-header bg-danger text-white';
        if (title) title.textContent = 'Reject Account Request';
        if (message) message.textContent = 'Please provide the reason for rejection (required):';
        if (submitBtn) {
            submitBtn.className = 'btn btn-danger';
            submitBtn.textContent = 'Reject';
        }
        if (noteTextarea) noteTextarea.required = true;
    }
    
    const requestIdForProcess = getElement('#requestIdForProcess');
    const requestActionType = getElement('#requestActionType');
    
    if (requestIdForProcess) requestIdForProcess.value = id;
    if (requestActionType) requestActionType.value = action;
    if (noteTextarea) noteTextarea.value = '';
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

// Process account request (approve/reject)
function processAccountRequest(id, action, note) {
    if (action === 'reject' && !note.trim()) {
        const noteInput = getElement('#requestNote');
        if (noteInput) noteInput.classList.add('is-invalid');
        showToast('Please provide a reason for rejection', 'danger');
        return;
    }

    const submitBtn = getElement('#confirmProcessRequestBtn');
    const originalText = submitBtn ? submitBtn.innerHTML : '';
    
    if (submitBtn) {
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        submitBtn.disabled = true;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', action);
    if (note) formData.append('note', note);

    fetch('residents-backend.php?action=process_request', {
        method: 'POST',
        body: formData
    })
    .then(handleResponse)
    .then(data => {
        if (data.success) {
            showToast(`Account request ${action}d successfully`);
            refreshAccountRequests(currentRequestPage, currentRequestFilter);
            refreshResidentList(currentResidentPage, currentResidentSearch);
            
            const processModal = bootstrap.Modal.getInstance(getElement('#processRequestModal'));
            if (processModal) processModal.hide();
            
            const viewModal = bootstrap.Modal.getInstance(getElement('#viewRequestModal'));
            if (viewModal) viewModal.hide();
        } else {
            throw new Error(data.message || `Error ${action}ing request`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast(`Failed to ${action} request: ` + error.message, 'danger');
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Export to Excel function
function exportResidents() {
    window.location.href = 'residents-backend.php?action=export';
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing residents management...');
    
    // Load initial resident list and account requests
    refreshResidentList();
    refreshAccountRequests();
    
    // Auto-generate address when house number or purok changes (add resident)
    const houseNumberInput = getElement('#houseNumber');
    const purokInput = getElement('#purok');
    const addressInput = getElement('#address');
    
    if (houseNumberInput && purokInput && addressInput) {
        const updateAddress = () => {
            const houseNumber = houseNumberInput.value.trim();
            const purok = purokInput.value.trim();
            
            if (houseNumber && purok) {
                addressInput.value = `House ${houseNumber}, Purok ${purok}, Balas, Mexico, Pampanga, Philippines`;
            } else {
                addressInput.value = '';
            }
        };
        
        houseNumberInput.addEventListener('input', updateAddress);
        purokInput.addEventListener('input', updateAddress);
    }

    // Auto-generate address when house number or purok changes (edit resident)
    const editHouseNumberInput = getElement('#editHouseNumber');
    const editPurokInput = getElement('#editPurok');
    const editAddressInput = getElement('#editAddress');
    
    if (editHouseNumberInput && editPurokInput && editAddressInput) {
        const updateEditAddress = () => {
            const houseNumber = editHouseNumberInput.value.trim();
            const purok = editPurokInput.value.trim();
            
            if (houseNumber && purok) {
                editAddressInput.value = `House ${houseNumber}, Purok ${purok}, Balas, Mexico, Pampanga, Philippines`;
            } else {
                editAddressInput.value = '';
            }
        };
        
        editHouseNumberInput.addEventListener('input', updateEditAddress);
        editPurokInput.addEventListener('input', updateEditAddress);
    }

    // Toggle account creation fields
    const createAccountCheck = getElement('#createAccountCheck');
    const accountFields = getElement('#accountFields');
    if (createAccountCheck && accountFields) {
        createAccountCheck.addEventListener('change', function() {
            accountFields.style.display = this.checked ? 'block' : 'none';
            const createAccount = getElement('#createAccount');
            if (createAccount) createAccount.value = this.checked ? 'true' : 'false';
            
            const password = getElement('#password');
            if (password) {
                password.required = this.checked;
            }
        });
    }
    
    // Add resident form submission
    // Add resident form submission - FIXED VERSION
const saveResidentBtn = getElement('#saveResidentBtn');
if (saveResidentBtn) {
    saveResidentBtn.addEventListener('click', async function () {
        const form = getElement('#addResidentForm');
        if (!form) return;

        // Stop native submit if user presses Enter
        form.addEventListener('submit', e => e.preventDefault(), { once: true });

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        /* ---------- Birthdate Validation ---------- */
        const birthdateInput = getElement('#birthdate');
        const ageInput = getElement('#age');
        
        if (birthdateInput) {
            const dateValue = birthdateInput.value.trim();

            // Year only → default to Jan 1
            if (/^\d{4}$/.test(dateValue)) {
                birthdateInput.value = `${dateValue}-01-01`;
            }
            // Full date → strict check
            else if (/^\d{4}-\d{2}-\d{2}$/.test(dateValue)) {
                const [y, m, d] = dateValue.split('-').map(Number);
                const dt = new Date(dateValue);
                if (dt.getFullYear() !== y || dt.getMonth() + 1 !== m || dt.getDate() !== d) {
                    showToast('Invalid birthdate. Use a real YYYY-MM-DD date.', 'danger');
                    return;
                }
            }
            // Browser-parsable fallback
            else {
                const parsedDate = new Date(dateValue);
                if (!isNaN(parsedDate.getTime())) {
                    birthdateInput.value = parsedDate.toISOString().split('T')[0];
                } else {
                    showToast('Invalid birthdate format. Use YYYY-MM-DD or year only.', 'danger');
                    return;
                }
            }

            /* ---------- Calculate Age AFTER birthdate validation ---------- */
            if (ageInput) {
                const birthdate = new Date(birthdateInput.value);
                const today = new Date();
                let age = today.getFullYear() - birthdate.getFullYear();
                const monthDiff = today.getMonth() - birthdate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }
                
                // Ensure age is not negative
                age = Math.max(0, age);
                ageInput.value = age;
                
                console.log('Calculated age:', age, 'for birthdate:', birthdateInput.value);
            }
        }

        /* ---------- Auto-generate address ---------- */
        const houseNumberInput = getElement('#houseNumber');
        const purokInput = getElement('#purok');
        const addressInput = getElement('#address');
        if (houseNumberInput && purokInput && addressInput) {
            const houseNumber = houseNumberInput.value.trim();
            const purok = purokInput.value.trim();
            if (houseNumber && purok) {
                addressInput.value = `House ${houseNumber}, Purok ${purok}, Balas, Mexico, Pampanga, Philippines`;
            }
        }

        /* ---------- Prepare and send ---------- */
        const formData = new FormData(form);
        
        // CRITICAL: Ensure age is explicitly added to FormData
        if (ageInput && ageInput.value) {
            formData.set('age', ageInput.value);
            console.log('Age added to FormData:', ageInput.value);
        }
        
        // CRITICAL: Ensure birthdate is explicitly added to FormData
        if (birthdateInput && birthdateInput.value) {
            formData.set('birthdate', birthdateInput.value);
            console.log('Birthdate added to FormData:', birthdateInput.value);
        }
        
        // Debug: Log all form data
        console.log('FormData contents:');
        for (let [key, value] of formData.entries()) {
            console.log(key, ':', value);
        }

        const originalText = saveResidentBtn.innerHTML;
        saveResidentBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving…';
        saveResidentBtn.disabled = true;

        try {
            const response = await fetch('residents-backend.php?action=add', {
                method: 'POST',
                body: formData
            });

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Invalid response from server.');
            }

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Failed to save resident');
            }

            showToast('Resident added successfully!', 'success');

            // Hide modal
            const modal = bootstrap.Modal.getInstance(getElement('#addResidentModal'));
            if (modal) modal.hide();

            // Reset form
            form.reset();
            form.classList.remove('was-validated');

            // Reset account toggle fields
            const accountFields = getElement('#accountFields');
            if (accountFields) accountFields.style.display = 'none';
            const createAccountCheck = getElement('#createAccountCheck');
            if (createAccountCheck) createAccountCheck.checked = false;
            const createAccount = getElement('#createAccount');
            if (createAccount) createAccount.value = 'false';

            // Refresh the resident list
            refreshResidentList();
        }
        catch (error) {
            console.error('Save Resident Error:', error);
            showToast(error.message || 'An unexpected error occurred.', 'danger');
        }
        finally {
            saveResidentBtn.innerHTML = originalText;
            saveResidentBtn.disabled = false;
        }
    });
}
        
        // Update resident form submission
        const updateResidentBtn = getElement('#updateResidentBtn');
        if (updateResidentBtn) {
            updateResidentBtn.addEventListener('click', updateResident);
        }
        
        // Delete resident confirmation
        const confirmDeleteBtn = getElement('#confirmDeleteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                deleteResident(this.dataset.id);
            });
        }
        
        // Approve/Reject request buttons in view modal
        const approveRequestBtn = getElement('#approveRequestBtn');
        const rejectRequestBtn = getElement('#rejectRequestBtn');
        if (approveRequestBtn && rejectRequestBtn) {
            approveRequestBtn.addEventListener('click', function() {
                currentRequestId = this.dataset.id;
                showProcessRequestModal(this.dataset.id, 'approve');
            });
            
            rejectRequestBtn.addEventListener('click', function() {
                currentRequestId = this.dataset.id;
                showProcessRequestModal(this.dataset.id, 'reject');
            });
        }

        // Confirm process request button
        const confirmProcessRequestBtn = getElement('#confirmProcessRequestBtn');
        if (confirmProcessRequestBtn) {
            confirmProcessRequestBtn.addEventListener('click', function() {
                const requestId = currentRequestId;
                const action = this.textContent.trim().toLowerCase();
                const note = getElement('#requestNote')?.value || '';
                
                if (!requestId) {
                    showToast('Request ID is required', 'danger');
                    return;
                }

                processAccountRequest(requestId, action, note);
            });
        }
        
        // Calculate age when birthdate changes
        const birthdateInput = getElement('#birthdate');
        if (birthdateInput) {
            birthdateInput.addEventListener('change', function() {
                if (this.value) {
                    const birthdate = new Date(this.value);
                    const today = new Date();
                    let age = today.getFullYear() - birthdate.getFullYear();
                    const monthDiff = today.getMonth() - birthdate.getMonth();
                    
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                        age--;
                    }
                    
                    const ageInput = getElement('#age');
                    if (ageInput) ageInput.value = age;
                }
            });
        }
    
    // Edit form birthdate change handler
    const editBirthdateInput = getElement('#editBirthdate');
    if (editBirthdateInput) {
        editBirthdateInput.addEventListener('change', function() {
            if (this.value) {
                const birthdate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthdate.getFullYear();
                const monthDiff = today.getMonth() - birthdate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }
                
                const ageInput = getElement('#editAge');
                if (ageInput) ageInput.value = age;
            }
        });
    }
    
    // Search residents
    const searchResidentBtn = getElement('#searchResidentBtn');
    const residentSearchInput = getElement('#residentSearch');
    if (searchResidentBtn && residentSearchInput) {
        searchResidentBtn.addEventListener('click', function() {
            refreshResidentList(1, residentSearchInput.value);
        });
        
        residentSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                refreshResidentList(1, this.value);
            }
        });
    }
    
    // Filter account requests
    document.querySelectorAll('.filter-requests').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const status = this.getAttribute('data-status');
            const currentFilter = getElement('#currentFilter');
            if (currentFilter) currentFilter.textContent = status === 'all' ? 'All' : status;
            refreshAccountRequests(1, status);
        });
    });
    
    // Pagination controls
    const prevResidentPage = getElement('#prevResidentPage');
    const nextResidentPage = getElement('#nextResidentPage');
    const prevRequestPage = getElement('#prevRequestPage');
    const nextRequestPage = getElement('#nextRequestPage');
    
    if (prevResidentPage) {
        prevResidentPage.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentResidentPage > 1) {
                refreshResidentList(currentResidentPage - 1, currentResidentSearch);
            }
        });
    }
    
    if (nextResidentPage) {
        nextResidentPage.addEventListener('click', function(e) {
            e.preventDefault();
            refreshResidentList(currentResidentPage + 1, currentResidentSearch);
        });
    }
    
    if (prevRequestPage) {
        prevRequestPage.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentRequestPage > 1) {
                refreshAccountRequests(currentRequestPage - 1, currentRequestFilter);
            }
        });
    }
    
    if (nextRequestPage) {
        nextRequestPage.addEventListener('click', function(e) {
            e.preventDefault();
            refreshAccountRequests(currentRequestPage + 1, currentRequestFilter);
        });
    }
    
    // Reset forms when modal is closed
    const addResidentModal = getElement('#addResidentModal');
    if (addResidentModal) {
        addResidentModal.addEventListener('hidden.bs.modal', function() {
            const form = getElement('#addResidentForm');
            if (form) {
                form.reset();
                form.classList.remove('was-validated');
                const accountFields = getElement('#accountFields');
                if (accountFields) accountFields.style.display = 'none';
                const createAccountCheck = getElement('#createAccountCheck');
                if (createAccountCheck) createAccountCheck.checked = false;
                const createAccount = getElement('#createAccount');
                if (createAccount) createAccount.value = 'false';
            }
        });
    }
    
    const editResidentModal = getElement('#editResidentModal');
    if (editResidentModal) {
        editResidentModal.addEventListener('hidden.bs.modal', function() {
            const form = getElement('#editResidentForm');
            if (form) {
                form.classList.remove('was-validated');
            }
        });
    }
    
    const processRequestModal = getElement('#processRequestModal');
    if (processRequestModal) {
        processRequestModal.addEventListener('hidden.bs.modal', function() {
            const noteInput = getElement('#requestNote');
            if (noteInput) {
                noteInput.value = '';
                noteInput.classList.remove('is-invalid');
            }
        });
    }
    
    console.log('Residents management initialized successfully');
});