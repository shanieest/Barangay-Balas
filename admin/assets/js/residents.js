// Global variables
let currentResidentId = null;
let currentRequestId = null;
let currentResidentPage = 1;
let currentRequestPage = 1;
let currentRequestFilter = 'all';
let currentResidentSearch = '';
const perPage = 10;

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
    
    const url = `../backend/residents-backend.php?action=list&page=${page}&per_page=${perPage}&search=${encodeURIComponent(search)}`;
    
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
        const colspan = window.USER_CAN_MODIFY ? 8 : 7;
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="${colspan}" class="text-center">No residents found</td>`;
        tableBody.appendChild(row);
        updatePagination('resident', pagination);
        return;
    }
    
    residents.forEach((resident, index) => {
        const row = createResidentRow(resident, index);
        tableBody.appendChild(row);
        
        // Load history for this resident immediately
        loadResidentHistory(resident.id);
    });
    
    updatePagination('resident', pagination);
    
    // Only attach button event listeners if user can modify
    if (window.USER_CAN_MODIFY) {
        attachButtonEventListeners();
    }
}

// Function to create a resident table row
function createResidentRow(resident, index) {
    const row = document.createElement('tr');
    
    // Account status badge
    const accountStatusBadge = createAccountStatusBadge(resident.account_status);
    
    // Calculate row number based on current page
    const rowNumber = (currentResidentPage - 1) * perPage + index + 1;
    
    // Build row HTML with history column
    let rowHTML = `
        <td>${rowNumber}</td>
        <td>${resident.last_name}, ${resident.first_name} ${resident.middle_name || ''} ${resident.suffix || ''}</td>
        <td>${resident.email || 'N/A'}</td>
        <td>${resident.contact_number}</td>
        <td>${resident.birthdate}</td>
        <td>${accountStatusBadge}</td>
        <td class="resident-history" data-resident-id="${resident.id}">
            <div class="history-placeholder">
                <span class="text-muted">Loading history...</span>
            </div>
        </td>
    `;
    
    // Only add actions column if user can modify
    if (window.USER_CAN_MODIFY) {
        rowHTML += `
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
    }
    
    row.innerHTML = rowHTML;
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

// Function to load and display resident history
function loadResidentHistory(residentId) {
    const historyCell = document.querySelector(`.resident-history[data-resident-id="${residentId}"]`);
    if (!historyCell) return;

    // Show loading state
    historyCell.innerHTML = '<span class="text-muted">Loading...</span>';

    getResidentHistory(residentId)
        .then(history => {
            const historyContent = formatResidentHistory(history);
            historyCell.innerHTML = historyContent;
        })
        .catch(error => {
            console.error('Error loading history for resident', residentId, error);
            historyCell.innerHTML = '<span class="text-danger">Error loading history</span>';
        });
}

// Function to fetch resident history
function getResidentHistory(residentId) {
    return fetch(`../backend/residents-backend.php?action=get_resident_history&id=${residentId}`)
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to load resident history');
            }
        })
        .catch(error => {
            console.error('Error loading resident history:', error);
            return [];
        });
}

// Function to format resident history for display
function formatResidentHistory(history) {
    if (!history || history.length === 0) {
        return '<span class="text-muted">No requests yet</span>';
    }

    const items = [];
    
    // Group by type and count
    const documentRequests = history.filter(item => item.type === 'document');
    const serviceReservations = history.filter(item => item.type === 'service');

    if (documentRequests.length > 0) {
        const docTypes = {};
        documentRequests.forEach(doc => {
            const docType = doc.document_type || 'Document';
            docTypes[docType] = (docTypes[docType] || 0) + 1;
        });

        Object.keys(docTypes).forEach(docType => {
            const count = docTypes[docType];
            items.push(`<span class="badge bg-primary me-1 mb-1">${docType}: ${count}</span>`);
        });
    }

    if (serviceReservations.length > 0) {
        const serviceTypes = {};
        serviceReservations.forEach(service => {
            const serviceName = service.service_name || 'Service';
            serviceTypes[serviceName] = (serviceTypes[serviceName] || 0) + 1;
        });

        Object.keys(serviceTypes).forEach(serviceName => {
            const count = serviceTypes[serviceName];
            items.push(`<span class="badge bg-success me-1 mb-1">${serviceName}: ${count}</span>`);
        });
    }

    return items.join('');
}

// event listener attachment
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
    const totalItems = pagination.total;
    
    // Update pagination info
    const paginationInfo = getElement(`#${prefix}-pagination .pagination-info`);
    if (paginationInfo) {
        if (totalItems === 0) {
            paginationInfo.textContent = `Showing 0 to 0 of 0 entries`;
        } else {
            const startItem = (currentPage - 1) * perPage + 1;
            const endItem = Math.min(currentPage * perPage, totalItems);
            paginationInfo.textContent = `Showing ${startItem} to ${endItem} of ${totalItems} entries`;
        }
    }
    
    // Update pagination buttons
    const prevBtn = document.getElementById(`prev${prefix.charAt(0).toUpperCase() + prefix.slice(1)}Page`);
    const nextBtn = document.getElementById(`next${prefix.charAt(0).toUpperCase() + prefix.slice(1)}Page`);
    
    if (prevBtn) {
        prevBtn.classList.toggle('disabled', currentPage === 1);
        prevBtn.querySelector('a').tabIndex = currentPage === 1 ? -1 : 0;
    }
    
    if (nextBtn) {
        nextBtn.classList.toggle('disabled', currentPage >= totalPages);
        nextBtn.querySelector('a').tabIndex = currentPage >= totalPages ? -1 : 0;
    }
    
    // Update page numbers
    const paginationContainer = getElement(`#${prefix}-pagination .pagination`);
    if (paginationContainer) {
        // Remove existing page numbers (keep Previous and Next)
        const pageItems = paginationContainer.querySelectorAll('.page-item:not(.page-prev):not(.page-next)');
        pageItems.forEach(item => item.remove());
        
        // Add page numbers
        const prevItem = paginationContainer.querySelector('.page-prev');
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
            
            if (prevItem && prevItem.nextSibling) {
                paginationContainer.insertBefore(pageItem, prevItem.nextSibling);
            }
        }
    }
}

// Function to refresh account requests with pagination and filtering
function refreshAccountRequests(page = 1, status = 'all') {
    currentRequestPage = page;
    currentRequestFilter = status;
    
    const url = `../backend/residents-backend.php?action=account_requests&page=${page}&per_page=${perPage}&status=${status}`;
    
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
        const colspan = window.USER_CAN_MODIFY ? 8 : 7;
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="${colspan}" class="text-center">No requests found</td>`;
        tableBody.appendChild(row);
        return;
    }
    
    requests.forEach((request, index) => {
        const row = createRequestRow(request, index);
        tableBody.appendChild(row);
    });
    
    updatePagination('request', pagination);
    
    // Only add request button event listeners if user can modify
    if (window.USER_CAN_MODIFY) {
        addRequestButtonEventListeners();
    }
}

// Function to create a request table row
function createRequestRow(request, index) {
    const row = document.createElement('tr');
    
    // Status badge
    const statusBadge = createAccountStatusBadge(request.account_status);
    
    // Processed by info
    const processedBy = request.processed_by ? 
        `${request.processed_by} (${request.date_processed})` : 'N/A';
    
    // Calculate row number based on current page
    const rowNumber = (currentRequestPage - 1) * perPage + index + 1;
    
    // ✅ FIX: Always use account_id (ra.id) for actions
    const accountId = request.account_id || request.id;
    
    console.log('Creating row for request:', {
        resident_id: request.id,
        account_id: request.account_id,
        using_id: accountId
    });
    
    // Build row HTML
    let rowHTML = `
        <td>${rowNumber}</td>
        <td>${request.last_name}, ${request.first_name}</td>
        <td>${request.email}</td>
        <td>${request.contact_number}</td>
        <td>${request.date_requested}</td>
        <td>${statusBadge}</td>
        <td>${processedBy}</td>
    `;
    
    // Only add actions column if user can modify
    if (window.USER_CAN_MODIFY) {
        // ✅ Use accountId for all buttons
        const actionButtons = request.account_status === 'Pending' ? `
            <button class="btn btn-sm btn-success approve-request-btn" data-id="${accountId}" data-account-id="${accountId}">
                <i class="fas fa-check"></i>
            </button>
            <button class="btn btn-sm btn-danger reject-request-btn" data-id="${accountId}" data-account-id="${accountId}">
                <i class="fas fa-times"></i>
            </button>
        ` : '';
        
        rowHTML += `
            <td>
                <button class="btn btn-sm btn-info view-request-btn" data-id="${accountId}" data-account-id="${accountId}">
                    <i class="fas fa-eye"></i>
                </button>
                ${actionButtons}
            </td>
        `;
    }
    
    row.innerHTML = rowHTML;
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

// Function View resident
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
    
    fetch(`../backend/residents-backend.php?action=list&id=${id}`)
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

//Edit resident function
function editResident(id) {
    if (!id) {
        showToast('Invalid resident ID', 'danger');
        return;
    }

    console.log('Editing resident with ID:', id);
    
    fetch(`../backend/residents-backend.php?action=list&id=${id}`)
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
        photoPath = `../../auth/uploads/photos/${resident.photo_path}`;
    }
    updateModalImage(viewModal, '.resident-photo', photoPath);

    // Handle BOTH valid ID paths
    displayIdPhotos(
        viewModal, 
        resident.valid_id_path, 
        resident.valid_id_path_2,
        '.resident-valid-id',
        '.resident-valid-id-2'
    );

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
    
    // Reset onerror handler first
    element.onerror = null;
    
    // Set the source
    element.src = src;

    // Add comprehensive error handling
    element.onerror = function() {
        console.error(`Failed to load image: ${this.src}`);
        console.log('Trying fallback image...');
        
        // Try multiple fallback strategies
        const fallbackSrc = selector.includes('id') ? 'img/default-id.jpg' : 'img/default-profile.jpg';
        this.onerror = null; // Prevent infinite loop
        this.src = fallbackSrc;
    };
    
    element.onload = function() {
        console.log(`Successfully loaded image: ${this.src}`);
    };
    
    // Add a timeout to check if image loaded
    setTimeout(() => {
        if (element.complete && element.naturalHeight === 0) {
            console.warn(`Image might not have loaded properly: ${src}`);
        }
    }, 1000);
}

function displayIdPhotos(modal, idPath1, idPath2, idSelector1 = '.resident-valid-id', idSelector2 = '.resident-valid-id-2') {
    console.log('Displaying ID photos:', { idPath1, idPath2 });
    
    // Process and display first ID photo
    let cleanIdPath1 = 'img/default-id.jpg';
    if (idPath1 && idPath1 !== 'null' && idPath1 !== '') {
        let cleanPath = idPath1;
        // Remove any prefix paths and get just the filename
        cleanPath = cleanPath.replace(/^uploads\/valid_ids\//, '');
        cleanPath = cleanPath.replace(/^auth\/uploads\/valid_ids\//, '');
        cleanPath = cleanPath.replace(/^\.\.\/\.\.\/auth\/uploads\/valid_ids\//, '');
        
        // Construct the correct path
        cleanIdPath1 = `../../auth/uploads/valid_ids/${cleanPath}`;
        console.log('First ID path:', cleanIdPath1);
    }
    updateModalImage(modal, idSelector1, cleanIdPath1);

    // Process and display second ID photo
    let cleanIdPath2 = 'img/default-id.jpg';
    if (idPath2 && idPath2 !== 'null' && idPath2 !== '') {
        let cleanPath = idPath2;
        // Remove any prefix paths and get just the filename
        cleanPath = cleanPath.replace(/^uploads\/valid_ids\//, '');
        cleanPath = cleanPath.replace(/^auth\/uploads\/valid_ids\//, '');
        cleanPath = cleanPath.replace(/^\.\.\/\.\.\/auth\/uploads\/valid_ids\//, '');
        
        // Construct the correct path
        cleanIdPath2 = `../../auth/uploads/valid_ids/${cleanPath}`;
        console.log('Second ID path:', cleanIdPath2);
    }
    updateModalImage(modal, idSelector2, cleanIdPath2);

    // Show/hide second ID container based on availability
    const idContainer2 = modal.querySelector('.id-photo-2-container');
    if (idContainer2) {
        if (idPath2 && idPath2 !== 'null' && idPath2 !== '') {
            idContainer2.style.display = 'block';
        } else {
            idContainer2.style.display = 'none';
        }
    }
}

// View request function
function viewRequest(id) {
    if (!id) {
        showToast('Invalid request ID', 'danger');
        return;
    }

    console.log('=== VIEW REQUEST DEBUG ===');
    console.log('Viewing request with ID:', id);

    fetch(`../backend/residents-backend.php?action=account_requests&id=${id}`)
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

            if (!data.success) {
                // ✅ Show debug info if available
                if (data.debug) {
                    console.error('Debug info:', data.debug);
                }
                throw new Error(data.message || 'Failed to load request');
            }

            let request = null;

            // ✅ Handle array or object responses
            if (Array.isArray(data.data)) {
                request = data.data.length > 0 ? data.data[0] : null;
            } else if (data.data) {
                request = data.data;
            }

            if (request) {
                console.log('Request data loaded:', request);
                displayRequestModal(request);
            } else {
                throw new Error('No request data received');
            }
        })
        .catch(error => {
            console.error('Error loading request:', error);
            showToast('Failed to load request details: ' + error.message, 'danger');
        });
}

// Display request data in modal 
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

    // ✅ FIX: Properly identify account_id
    const accountId = request.account_id; // This should always exist in the query result
    const formattedRequestId = accountId ? `${accountId.toString().padStart(6, '0')}` : 'N/A';
    
    console.log('Account ID for display:', {
        account_id: request.account_id,
        resident_id: request.id,
        using: accountId
    });

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
    updateModalImage(viewModal, '.request-photo', photoPath);

    displayIdPhotos(
        viewModal, 
        request.valid_id_path, 
        request.valid_id_path_2,
        '.request-valid-id',
        '.request-valid-id-2'
    );

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
        
        // ✅ FIX: Use account_id for button data attributes
        approveBtn.dataset.id = accountId;
        rejectBtn.dataset.id = accountId;
        
        approveBtn.style.display = isPending ? 'inline-block' : 'none';
        rejectBtn.style.display = isPending ? 'inline-block' : 'none';
        
        console.log('Button IDs set to:', accountId);
    }

    // ✅ Store account_id globally
    currentRequestId = accountId;
    
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

    fetch('../backend/residents-backend.php?action=edit', {
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
    fetch(`../backend/residents-backend.php?action=list&id=${id}`)
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

    fetch('../backend/residents-backend.php?action=delete', {
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

function processAccountRequest(id, action, note) {
    console.log('Processing request:', {id, action, note});
    
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

    // Use FormData instead of JSON for file upload compatibility
    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', action); // Changed from 'action' to avoid conflicts
    if (note) formData.append('note', note);

    fetch('../backend/residents-backend.php?action=process_request', {
        method: 'POST',
        body: formData
    })
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
            throw new Error('Invalid JSON response from server');
        }
    })
    .then(data => {
        console.log('Parsed data:', data);
        
        if (data.success) {
            showToast(`Account request ${action === 'approve' ? 'approved' : 'rejected'} successfully`);
            refreshAccountRequests(currentRequestPage, currentRequestFilter);
            refreshResidentList(currentResidentPage, currentResidentSearch);
            
            // Close both modals
            const processModalEl = getElement('#processRequestModal');
            if (processModalEl) {
                const processModal = bootstrap.Modal.getInstance(processModalEl);
                if (processModal) processModal.hide();
            }
            
            const viewModalEl = getElement('#viewRequestModal');
            if (viewModalEl) {
                const viewModal = bootstrap.Modal.getInstance(viewModalEl);
                if (viewModal) viewModal.hide();
            }
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
    window.location.href = '../backend/residents-backend.php?action=export';
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing residents management...');
    console.log('User can modify:', window.USER_CAN_MODIFY);
    
    // Load initial resident list and account requests
    refreshResidentList(1, '');
    refreshAccountRequests(1, 'all');
    
    // Only attach form handlers if user can modify
    if (window.USER_CAN_MODIFY) {
        // Auto-generate address when house number or purok changes (add resident)
        const houseNumberInput = getElement('#houseNumber');
        const purokInput = getElement('#purok');
        const addressInput = getElement('#address');
        
        if (houseNumberInput && purokInput && addressInput) {
            const updateAddress = () => {
                const houseNumber = houseNumberInput.value.trim();
                const purok = purokInput.value.trim();
                
                if (houseNumber && purok) {
                    addressInput.value = `House ${houseNumber}, ${purok}, Balas, Mexico, Pampanga, Philippines`;
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
                    editAddressInput.value = `House ${houseNumber}, ${purok}, Balas, Mexico, Pampanga, Philippines`;
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
       const saveResidentBtn = getElement('#saveResidentBtn');
if (saveResidentBtn) {
    console.log('Save Resident Button found and attaching listener...');
    
    saveResidentBtn.addEventListener('click', async function (e) {
        e.preventDefault(); // Prevent any default behavior
        console.log('Save Resident Button clicked!');
        
        const form = getElement('#addResidentForm');
        if (!form) {
            console.error('Form not found!');
            showToast('Form not found', 'danger');
            return;
        }

        // Validate form
        if (!form.checkValidity()) {
            console.log('Form validation failed');
            form.classList.add('was-validated');
            showToast('Please fill in all required fields', 'danger');
            return;
        }

        console.log('Form is valid, proceeding...');

        /* ---------- Birthdate Validation ---------- */
        const birthdateInput = getElement('#birthdate');
        const ageInput = getElement('#age');
        
        if (birthdateInput && birthdateInput.value) {
            const dateValue = birthdateInput.value.trim();
            console.log('Processing birthdate:', dateValue);

            // Year only → default to Jan 1
            if (/^\d{4}$/.test(dateValue)) {
                birthdateInput.value = `${dateValue}-01-01`;
                console.log('Converted year to full date:', birthdateInput.value);
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
                    console.log('Parsed date to:', birthdateInput.value);
                } else {
                    showToast('Invalid birthdate format. Use YYYY-MM-DD or year only.', 'danger');
                    return;
                }
            }

            /* ---------- Calculate Age ---------- */
            if (ageInput) {
                const birthdate = new Date(birthdateInput.value);
                const today = new Date();
                let age = today.getFullYear() - birthdate.getFullYear();
                const monthDiff = today.getMonth() - birthdate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }
                
                age = Math.max(0, age);
                ageInput.value = age;
                console.log('Calculated age:', age);
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
                addressInput.value = `House ${houseNumber}, ${purok}, Balas, Mexico, Pampanga, Philippines`;
                console.log('Generated address:', addressInput.value);
            }
        }

        /* ---------- Check account creation ---------- */
        const createAccountCheck = getElement('#createAccountCheck');
        const passwordInput = getElement('#password');
        const emailInput = getElement('#email');
        
        if (createAccountCheck && createAccountCheck.checked) {
            console.log('Account creation requested');
            
            if (!passwordInput || !passwordInput.value.trim()) {
                passwordInput?.classList.add('is-invalid');
                showToast('Password is required when creating an account', 'danger');
                return;
            }
            
            if (!emailInput || !emailInput.value.trim()) {
                emailInput?.classList.add('is-invalid');
                showToast('Email is required when creating an account', 'danger');
                return;
            }
        }

        /* ---------- Prepare FormData ---------- */
        const formData = new FormData(form);
        
        // Ensure age and birthdate are in FormData
        if (ageInput && ageInput.value) {
            formData.set('age', ageInput.value);
        }
        if (birthdateInput && birthdateInput.value) {
            formData.set('birthdate', birthdateInput.value);
        }
        
        // Debug: Log all form data
        console.log('=== FormData Contents ===');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        console.log('========================');

        const originalText = this.innerHTML;
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
        this.disabled = true;

        try {
            console.log('Sending request to server...');
            
            const response = await fetch('../backend/residents-backend.php?action=add', {
                method: 'POST',
                body: formData
            });

            console.log('Response status:', response.status);

            // Check content type
            const contentType = response.headers.get('content-type') || '';
            console.log('Response content-type:', contentType);

            if (!contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response received:', text.substring(0, 500));
                throw new Error('Server returned invalid response (expected JSON)');
            }

            const data = await response.json();
            console.log('Response data:', data);

            if (!data.success) {
                throw new Error(data.message || 'Failed to add resident');
            }

            // Success!
            console.log('Resident added successfully!');
            showToast(data.message || 'Resident added successfully!', 'success');

            // Close modal
            const modal = bootstrap.Modal.getInstance(getElement('#addResidentModal'));
            if (modal) {
                console.log('Closing modal...');
                modal.hide();
            }

            // Reset form
            form.reset();
            form.classList.remove('was-validated');

            // Reset account fields
            const accountFields = getElement('#accountFields');
            if (accountFields) accountFields.style.display = 'none';
            if (createAccountCheck) createAccountCheck.checked = false;
            const createAccountInput = getElement('#createAccount');
            if (createAccountInput) createAccountInput.value = 'false';

            // Refresh resident list
            console.log('Refreshing resident list...');
            refreshResidentList(currentResidentPage, currentResidentSearch);
        }
        catch (error) {
            console.error('=== ERROR ===');
            console.error('Error type:', error.name);
            console.error('Error message:', error.message);
            console.error('Error stack:', error.stack);
            console.error('=============');
            
            showToast(error.message || 'An unexpected error occurred', 'danger');
        }
        finally {
            // Restore button
            this.innerHTML = originalText;
            this.disabled = false;
            console.log('Button restored');
        }
    });
    
    console.log('Save Resident Button listener attached successfully');
} else {
    console.error('Save Resident Button (#saveResidentBtn) not found in DOM!');
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
    }
    
    // Search and pagination handlers (available to all users)
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
    
    // Pagination controls (available to all users)
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
    
    // Reset forms when modal is closed (only if user can modify)
    if (window.USER_CAN_MODIFY) {
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
    }
    
    console.log('Residents management initialized successfully');

    // Create image viewer modal HTML
    const imageViewerHTML = `
        <div class="modal fade" id="imageViewerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content bg-dark">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-white" id="imageViewerTitle">Image Viewer</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-0">
                        <img src="" id="imageViewerImg" class="img-fluid" alt="Full size image" style="max-height: 80vh;">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Append modal to body if it doesn't exist
    if (!document.getElementById('imageViewerModal')) {
        document.body.insertAdjacentHTML('beforeend', imageViewerHTML);
    }
    
    // Add click event to all ID images (delegated event)
    document.addEventListener('click', function(e) {
        const target = e.target;
        
        // Check if clicked element is an ID image
        if (target.classList.contains('resident-valid-id') || 
            target.classList.contains('resident-valid-id-2') ||
            target.classList.contains('request-valid-id') ||
            target.classList.contains('request-valid-id-2')) {
            
            // Don't open viewer for default images
            if (target.src.includes('default-id.jpg')) {
                return;
            }
            
            openImageViewer(target.src, target.alt);
        }
    });
});


// Function to open image in full screen
function openImageViewer(imageSrc, imageTitle) {
    const modal = document.getElementById('imageViewerModal');
    const img = document.getElementById('imageViewerImg');
    const title = document.getElementById('imageViewerTitle');
    
    if (modal && img && title) {
        img.src = imageSrc;
        title.textContent = imageTitle || 'ID Photo';
        
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
}

document.addEventListener('keydown', function(e) {
    const imageViewerModal = document.getElementById('imageViewerModal');
    if (imageViewerModal && imageViewerModal.classList.contains('show')) {
        if (e.key === 'Escape') {
            const modal = bootstrap.Modal.getInstance(imageViewerModal);
            if (modal) modal.hide();
        }
    }
});