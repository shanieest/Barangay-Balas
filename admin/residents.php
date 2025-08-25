<?php 

require_once __DIR__ . '/includes/auth.php';
requireAuth();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents | Barangay Balas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
        }
        .img-preview {
            max-height: 200px;
            max-width: 100%;
        }
        .note-textarea {
            min-height: 100px;
        }
        .note-textarea.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .account-notes {
            margin-top: 1rem;
        }
        .account-notes .notes-content {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            white-space: pre-wrap;
        }
        .account-status-badge {
            font-size: 0.75rem;
        }
        .account-approved { background-color: #28a745; }
        .account-pending { background-color: #ffc107; color: #212529; }
        .account-disapproved { background-color: #dc3545; }
        .account-details {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            margin-top: 1rem;
        }
        .pagination-info {
            margin-top: 0.5rem;
        }
        .search-box {
            max-width: 300px;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <?php include 'includes/navbar.php'; ?>
    
    <div id="layoutSidenav">
        <?php include 'includes/sidebar.php'; ?>
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Residents Management</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Residents</li>
                    </ol>
                    
                    <!-- Tabs for Residents and Account Requests -->
                    <ul class="nav nav-tabs mb-4" id="residentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="verified-tab" data-bs-toggle="tab" data-bs-target="#verified-residents" type="button" role="tab">Verified Residents</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="requests-tab" data-bs-toggle="tab" data-bs-target="#account-requests" type="button" role="tab">Account Requests <span class="badge bg-danger" id="pending-count">0</span></button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="residentTabsContent">
                        <!-- Verified Residents Tab -->
                        <div class="tab-pane fade show active" id="verified-residents" role="tabpanel">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-users me-1"></i>
                                            Verified Residents
                                        </div>
                                        <div>
                                            <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addResidentModal">
                                                <i class="fas fa-plus me-1"></i> Add Resident
                                            </button>
                                            <button class="btn btn-success btn-sm" onclick="exportResidents()">
                                                <i class="fas fa-file-excel me-1"></i> Export
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="input-group search-box">
                                                <input type="text" class="form-control" id="residentSearch" placeholder="Search residents...">
                                                <button class="btn btn-outline-secondary" type="button" id="searchResidentBtn">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="residentsTable" class="table table-striped table-bordered" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Contact</th>
                                                    <th>Birthdate</th>
                                                    <th>Account Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div class="pagination-info"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <nav aria-label="Residents pagination">
                                                <ul class="pagination justify-content-end">
                                                    <li class="page-item disabled" id="prevResidentPage">
                                                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                                                    </li>
                                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                                    <li class="page-item" id="nextResidentPage">
                                                        <a class="page-link" href="#">Next</a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Requests Tab -->
                        <div class="tab-pane fade" id="account-requests" role="tabpanel">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-user-clock me-1"></i>
                                            Resident Account Requests
                                        </div>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                Filter: <span id="currentFilter">All</span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item filter-requests" href="#" data-status="all">All Requests</a></li>
                                                <li><a class="dropdown-item filter-requests" href="#" data-status="Pending">Pending</a></li>
                                                <li><a class="dropdown-item filter-requests" href="#" data-status="Approved">Approved</a></li>
                                                <li><a class="dropdown-item filter-requests" href="#" data-status="Disapproved">Disapproved</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="requestsTable" class="table table-striped table-bordered" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Contact</th>
                                                    <th>Date Requested</th>
                                                    <th>Status</th>
                                                    <th>Processed By</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <div class="pagination-info"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <nav aria-label="Requests pagination">
                                                <ul class="pagination justify-content-end">
                                                    <li class="page-item disabled" id="prevRequestPage">
                                                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                                                    </li>
                                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                                    <li class="page-item" id="nextRequestPage">
                                                        <a class="page-link" href="#">Next</a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

 <?php include 'modals/residentsModal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/script.js"></script>
 <script>
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
        return;
    }
    
    residents.forEach((resident, index) => {
        const row = createResidentRow(resident, index);
        tableBody.appendChild(row);
    });
    
    updatePagination('resident', pagination);
    addButtonEventListeners();
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
            <button class="btn btn-sm btn-info view-btn" data-id="${resident.id}">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-warning edit-btn" data-id="${resident.id}">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger delete-btn" data-id="${resident.id}">
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

// Function to add event listeners to all action buttons
function addButtonEventListeners() {
    // View buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            viewResident(this.getAttribute('data-id'));
        });
    });
    
    // Edit buttons
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            editResident(this.getAttribute('data-id'));
        });
    });
    
    // Delete buttons
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            showDeleteModal(this.getAttribute('data-id'));
        });
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

// View resident function
function viewResident(id) {
    fetch(`residents-backend.php?action=list&id=${id}`)
        .then(handleResponse)
        .then(data => {
            if (data.success && data.data) {
                displayResidentModal(data.data);
            } else {
                showToast('Resident not found', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load resident details: ' + error.message, 'danger');
        });
}

// Display resident data in modal
function displayResidentModal(resident) {
    const viewModal = getElement('#viewResidentModal');
    if (!viewModal) return;
    
    // Format birthdate
    const birthdate = new Date(resident.birthdate);
    const formattedBirthdate = birthdate.toLocaleDateString('en-US', { 
        year: 'numeric', month: 'long', day: 'numeric' 
    }) + ` (${resident.age} years old)`;
    
    // Set verification badge class
    const verificationClass = resident.verification_status === 'Verified' ? 'bg-success' : 
                           resident.verification_status === 'Pending' ? 'bg-warning' : 'bg-secondary';
    
    // Set resident status badge class
    const statusClass = resident.resident_status === 'Active' ? 'bg-primary' : 
                       resident.resident_status === 'Inactive' ? 'bg-secondary' :
                       resident.resident_status === 'Deceased' ? 'bg-dark' : 'bg-info';
    
    // Update modal content
    updateModalField(viewModal, '.verification-badge', verificationClass, resident.verification_status);
    updateModalField(viewModal, '.resident-status-badge', statusClass, resident.resident_status);
    
    // Update other fields
    updateModalText(viewModal, '.resident-name', `${resident.first_name} ${resident.last_name}`);
    updateModalText(viewModal, '.resident-birthdate', formattedBirthdate);
    updateModalText(viewModal, '.resident-sex', resident.sex === 'male' ? 'Male' : 'Female');
    updateModalText(viewModal, '.resident-contact', resident.contact_number);
    updateModalText(viewModal, '.resident-email', resident.email);
    updateModalText(viewModal, '.resident-address', resident.address || 'N/A');
    updateModalImage(viewModal, '.resident-photo', resident.photo_path || 'img/default-profile.jpg');
    updateModalImage(viewModal, '.resident-valid-id', resident.valid_id_path || 'img/default-id.jpg');

    // Account information
    const accountSection = viewModal.querySelector('.account-details');
    if (accountSection) {
        if (resident.account_status) {
            accountSection.style.display = 'block';
            
            // Set account status badge
            const accountStatusBadge = accountSection.querySelector('.account-status-badge');
            if (accountStatusBadge) {
                accountStatusBadge.className = 'badge account-status-badge';
                if (resident.account_status === 'Approved') {
                    accountStatusBadge.classList.add('account-approved');
                } else if (resident.account_status === 'Pending') {
                    accountStatusBadge.classList.add('account-pending');
                } else if (resident.account_status === 'Disapproved') {
                    accountStatusBadge.classList.add('account-disapproved');
                }
                accountStatusBadge.textContent = resident.account_status;
            }
            
            updateModalText(viewModal, '.resident-processed-by', resident.account_processed_by || 'N/A');
            updateModalText(viewModal, '.resident-date-processed', resident.account_date_processed || 'N/A');
            updateModalText(viewModal, '.resident-account-notes', resident.account_notes || 'N/A');
        } else {
            accountSection.style.display = 'none';
        }
    }

    // Set verify button state
    const verifyBtn = getElement('#verifyResidentBtn');
    if (verifyBtn) {
        verifyBtn.dataset.id = resident.id;
        verifyBtn.disabled = resident.verification_status === 'Verified';
        verifyBtn.textContent = resident.verification_status === 'Verified' ? 'Verified' : 'Verify';
    }

    // Store current resident ID
    currentResidentId = resident.id;
    
    // Show the modal
    const modal = new bootstrap.Modal(viewModal);
    modal.show();
}

// Helper functions for updating modal fields
function updateModalText(modal, selector, value) {
    const element = modal.querySelector(selector);
    if (element) element.textContent = value || 'N/A';
}

function updateModalField(modal, selector, className, value) {
    const element = modal.querySelector(selector);
    if (element) {
        element.className = className;
        element.textContent = value || 'N/A';
    }
}

function updateModalImage(modal, selector, src) {
    const element = modal.querySelector(selector);
    if (element) element.src = src;
}

// View request function
function viewRequest(id) {
    fetch(`residents-backend.php?action=account_requests&id=${id}`)
        .then(handleResponse)
        .then(data => {
            if (data.success && data.data) {
                displayRequestModal(data.data);
            } else {
                showToast(data.message || 'Request not found', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load request details: ' + error.message, 'danger');
        });
}

// Display request data in modal
function displayRequestModal(request) {
    const viewModal = getElement('#viewRequestModal');
    if (!viewModal) return;
    
    // Format birthdate safely
    let formattedBirthdate = 'N/A';
    if (request.birthdate) {
        try {
            const birthdate = new Date(request.birthdate);
            if (!isNaN(birthdate.getTime())) {
                formattedBirthdate = birthdate.toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'long', day: 'numeric' 
                }) + (request.age ? ` (${request.age} years old)` : '');
            }
        } catch (e) {
            console.error('Error parsing birthdate:', e);
        }
    }
    
    // Set status badge class
    let statusClass = '';
    if (request.account_status === 'Approved') {
        statusClass = 'account-approved';
    } else if (request.account_status === 'Pending') {
        statusClass = 'account-pending';
    } else if (request.account_status === 'Disapproved') {
        statusClass = 'account-disapproved';
    }
    
    // Safely generate request ID - handle cases where request.id might be undefined
    const requestId = request.id ? `BRGY-REQ-${request.id.toString().padStart(4, '0')}` : 'N/A';
    
    // Safely get name
    const requestName = request.first_name && request.last_name ? 
        `${request.first_name} ${request.last_name}` : 'N/A';
    
    // Update modal content
    updateModalField(viewModal, '.request-status-badge', `badge ${statusClass}`, request.account_status || 'N/A');
    updateModalText(viewModal, '.request-name', requestName);
    updateModalText(viewModal, '.request-id', `Request ID: ${requestId}`);
    updateModalText(viewModal, '.request-birthdate', formattedBirthdate);
    updateModalText(viewModal, '.request-sex', request.sex ? 
        (request.sex === 'male' ? 'Male' : 'Female') : 'N/A');
    updateModalText(viewModal, '.request-contact', request.contact_number || 'N/A');
    updateModalText(viewModal, '.request-email', request.email || 'N/A');
    updateModalImage(viewModal, '.request-photo', request.photo_path || 'img/default-profile.jpg');
    updateModalImage(viewModal, '.request-valid-id', request.valid_id_path || 'img/default-id.jpg');
    updateModalText(viewModal, '.request-date-requested', request.date_requested || 'N/A');
    updateModalText(viewModal, '.request-processed-by', request.processed_by || 'N/A');
    updateModalText(viewModal, '.request-date-processed', request.date_processed || 'N/A');
    updateModalText(viewModal, '.request-notes', request.notes || 'N/A');

    // Show/hide processed info section
    const processedInfo = viewModal.querySelector('#requestProcessedInfo');
    if (processedInfo) {
        processedInfo.style.display = request.account_status !== 'Pending' ? 'block' : 'none';
    }

    // Set buttons state
    const approveBtn = getElement('#approveRequestBtn');
    const rejectBtn = getElement('#rejectRequestBtn');
    if (approveBtn && rejectBtn) {
        approveBtn.dataset.id = request.id || '';
        rejectBtn.dataset.id = request.id || '';
        
        if (request.account_status !== 'Pending') {
            approveBtn.style.display = 'none';
            rejectBtn.style.display = 'none';
        } else {
            approveBtn.style.display = 'inline-block';
            rejectBtn.style.display = 'inline-block';
        }
    }

    // Store current request ID
    currentRequestId = request.id;
    
    // Show the modal
    const modal = new bootstrap.Modal(viewModal);
    modal.show();
}

function displayRequestModal(request) {
    const viewModal = getElement('#viewRequestModal');
    if (!viewModal) return;
    
    // Format birthdate
    const birthdate = request.birthdate ? new Date(request.birthdate) : null;
    const formattedBirthdate = birthdate ? 
        birthdate.toLocaleDateString('en-US', { 
            year: 'numeric', month: 'long', day: 'numeric' 
        }) + (request.age ? ` (${request.age} years old)` : '') : 
        'N/A';
    
    // Set status badge class
    let statusClass = '';
    if (request.account_status === 'Approved') {
        statusClass = 'account-approved';
    } else if (request.account_status === 'Pending') {
        statusClass = 'account-pending';
    } else if (request.account_status === 'Disapproved') {
        statusClass = 'account-disapproved';
    }
    
    // Safely generate request ID
    const requestId = request.id ? `BRGY-REQ-${request.id.toString().padStart(4, '0')}` : 'N/A';
    
    // Update modal content
    updateModalField(viewModal, '.request-status-badge', `badge ${statusClass}`, request.account_status || 'N/A');
    updateModalText(viewModal, '.request-name', request.first_name && request.last_name ? 
        `${request.first_name} ${request.last_name}` : 'N/A');
    updateModalText(viewModal, '.request-id', `Request ID: ${requestId}`);
    updateModalText(viewModal, '.request-birthdate', formattedBirthdate);
    updateModalText(viewModal, '.request-sex', request.sex ? 
        (request.sex === 'male' ? 'Male' : 'Female') : 'N/A');
    updateModalText(viewModal, '.request-contact', request.contact_number || 'N/A');
    updateModalText(viewModal, '.request-email', request.email || 'N/A');
    updateModalImage(viewModal, '.request-photo', request.photo_path || 'img/default-profile.jpg');
    updateModalImage(viewModal, '.request-valid-id', request.valid_id_path || 'img/default-id.jpg');
    updateModalText(viewModal, '.request-date-requested', request.date_requested || 'N/A');
    updateModalText(viewModal, '.request-processed-by', request.processed_by || 'N/A');
    updateModalText(viewModal, '.request-date-processed', request.date_processed || 'N/A');
    updateModalText(viewModal, '.request-notes', request.notes || 'N/A');

    // Show/hide processed info section
    const processedInfo = viewModal.querySelector('#requestProcessedInfo');
    if (processedInfo) {
        processedInfo.style.display = request.account_status !== 'Pending' ? 'block' : 'none';
    }

    // Set buttons state
    const approveBtn = getElement('#approveRequestBtn');
    const rejectBtn = getElement('#rejectRequestBtn');
    if (approveBtn && rejectBtn) {
        approveBtn.dataset.id = request.id || '';
        rejectBtn.dataset.id = request.id || '';
        
        if (request.account_status !== 'Pending') {
            approveBtn.style.display = 'none';
            rejectBtn.style.display = 'none';
        } else {
            approveBtn.style.display = 'inline-block';
            rejectBtn.style.display = 'inline-block';
        }
    }

    // Store current request ID
    currentRequestId = request.id;
    
    // Show the modal
    const modal = new bootstrap.Modal(viewModal);
    modal.show();
}

// Edit resident function
function editResident(id) {
    fetch(`residents-backend.php?action=list&id=${id}`)
        .then(handleResponse)
        .then(data => {
            if (data.success && data.data) {
                populateEditForm(data.data);
            } else {
                showToast('Resident not found', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load resident details: ' + error.message, 'danger');
        });
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
    
    // Parse address to get house number and purok if it follows the expected format
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
    if (editFirstName) editFirstName.value = resident.first_name;
    if (editMiddleName) editMiddleName.value = resident.middle_name || '';
    if (editLastName) editLastName.value = resident.last_name;
    if (editSuffix) editSuffix.value = resident.suffix || '';
    if (editSex) editSex.value = resident.sex;
    if (editContactNumber) editContactNumber.value = resident.contact_number;
    if (editEmail) editEmail.value = resident.email || '';
    if (editBirthdate) editBirthdate.value = resident.birthdate;
    if (editAge) editAge.value = resident.age;
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

    fetch('residents-backend.php?action=edit', {
        method: 'POST',
        body: formData
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

// Verify resident function
function verifyResident(id) {
    if (confirm('Are you sure you want to verify this resident?')) {
        fetch('residents-backend.php?action=verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                showToast('Resident verified successfully');
                refreshResidentList(currentResidentPage, currentResidentSearch);
                const modal = bootstrap.Modal.getInstance(getElement('#viewResidentModal'));
                if (modal) modal.hide();
            } else {
                throw new Error(data.message || 'Error verifying resident');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to verify resident: ' + error.message, 'danger');
        });
    }
}

// Show delete confirmation modal
function showDeleteModal(id) {
    fetch(`residents-backend.php?action=list&id=${id}`)
        .then(handleResponse)
        .then(data => {
            if (data.success && data.data) {
                const deleteResidentName = getElement('#deleteResidentName');
                const deleteResidentId = getElement('#deleteResidentId');
                const confirmDeleteBtn = getElement('#confirmDeleteBtn');
                
                if (deleteResidentName) deleteResidentName.textContent = `${data.data.first_name} ${data.data.last_name}`;
                if (confirmDeleteBtn) confirmDeleteBtn.dataset.id = data.data.id;
                
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
    fetch('residents-backend.php?action=delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}`
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
    });
}

// Show process request modal (approve/reject)
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
    // Validate that rejection has a note
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

    // Create form data to properly send the request
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
            
            // Close modals
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
            
            // Toggle required attribute on account fields
            const password = getElement('#password');
            if (password) {
                password.required = this.checked;
            }
        });
    }
    
// Add resident form submission
const saveResidentBtn = getElement('#saveResidentBtn');
if (saveResidentBtn) {
    saveResidentBtn.addEventListener('click', function() {
        const form = getElement('#addResidentForm');
        if (!form) return;
        
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // Format the birthdate properly
        const birthdateInput = getElement('#birthdate');
        if (birthdateInput) {
            const dateValue = birthdateInput.value;
            
            // If only year is provided (e.g., "2003"), convert to full date
            if (/^\d{4}$/.test(dateValue)) {
                birthdateInput.value = dateValue + '-01-01'; // Default to January 1st
            }
            // If it's already in YYYY-MM-DD format, leave it as is
            else if (/^\d{4}-\d{2}-\d{2}$/.test(dateValue)) {
                // Valid format, no change needed
            }
            // If it's in another format, try to parse it
            else {
                const parsedDate = new Date(dateValue);
                if (!isNaN(parsedDate.getTime())) {
                    birthdateInput.value = parsedDate.toISOString().split('T')[0];
                } else {
                    showToast('Invalid birthdate format. Please use YYYY-MM-DD or year only.', 'danger');
                    return;
                }
            }
        }

        const formData = new FormData(form);
        const originalText = saveResidentBtn.innerHTML;
        
        saveResidentBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
        saveResidentBtn.disabled = true;

        fetch('residents-backend.php?action=add', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // First check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    throw new Error(text || 'Invalid response from server');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('Resident added successfully!', 'success');
                const modal = bootstrap.Modal.getInstance(getElement('#addResidentModal'));
                if (modal) modal.hide();
                form.reset();
                form.classList.remove('was-validated');
                refreshResidentList();
            } else {
                throw new Error(data.message || 'Failed to save resident');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message || 'An error occurred', 'danger');
        })
        .finally(() => {
            saveResidentBtn.innerHTML = originalText;
            saveResidentBtn.disabled = false;
        });
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
    
    // Verify button in view modal
    const verifyResidentBtn = getElement('#verifyResidentBtn');
    if (verifyResidentBtn) {
        verifyResidentBtn.addEventListener('click', function() {
            verifyResident(this.dataset.id);
        });
    }
    
    // Approve/Reject request buttons in view modal
const approveRequestBtn = getElement('#approveRequestBtn');
const rejectRequestBtn = getElement('#rejectRequestBtn');
if (approveRequestBtn && rejectRequestBtn) {
    approveRequestBtn.addEventListener('click', function() {
        currentRequestId = this.dataset.id; // Set the global variable
        showProcessRequestModal(this.dataset.id, 'approve');
    });
    
    rejectRequestBtn.addEventListener('click', function() {
        currentRequestId = this.dataset.id; // Set the global variable
        showProcessRequestModal(this.dataset.id, 'reject');
    });
}
    // Confirm process request button

const confirmProcessRequestBtn = getElement('#confirmProcessRequestBtn');
if (confirmProcessRequestBtn) {
    confirmProcessRequestBtn.addEventListener('click', function() {
        const requestId = currentRequestId; // Use the global variable
        const action = this.textContent.trim().toLowerCase(); // Get action from button text
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
});
</script>
</body>
</html>