<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Accounts | Barangay Balas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .archived-badge {
            background: #6c757d;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .last-login-warning {
            color: #dc3545;
            font-weight: 500;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <?php include '../includes/navbar.php'; ?>
<div id="layoutSidenav">
<?php include '../includes/sidebar.php'; ?>
<div id="layoutSidenav_content">
<main class="container-fluid px-4">
    <h1 class="mt-4">Archived Accounts</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Archived Accounts</li>
    </ol>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Accounts are automatically archived after 1 year of inactivity. Residents can request reactivation through the barangay office.
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-archive me-1"></i>
                            Archived Resident Accounts
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="searchArchived" placeholder="Search archived accounts...">
                                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="archivedTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Contact</th>
                                            <th>Last Login</th>
                                            <th>Archived Date</th>
                                            <th>Reason</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <div class="pagination-info"></div>
                                </div>
                                <div class="col-md-6">
                                    <nav>
                                        <ul class="pagination justify-content-end">
                                            <li class="page-item disabled" id="prevPage">
                                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                                            </li>
                                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                            <li class="page-item" id="nextPage">
                                                <a class="page-link" href="#">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Restore Account Modal -->
    <div class="modal fade" id="restoreModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Restore Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to restore this account?</p>
                    <p><strong>Resident:</strong> <span id="restoreResidentName"></span></p>
                    <p><strong>Email:</strong> <span id="restoreResidentEmail"></span></p>
                    
                    <div class="mb-3">
                        <label for="restoreNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="restoreNotes" rows="3" 
                                  placeholder="Reason for reactivation..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-envelope me-2"></i>
                        An email notification will be sent to the resident.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmRestoreBtn">
                        <i class="fas fa-undo me-1"></i> Restore Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Archive History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="historyContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        let currentPage = 1;
        let currentSearch = '';
        let currentResidentId = null;

        // Load archived accounts
        function loadArchivedAccounts(page = 1, search = '') {
            currentPage = page;
            currentSearch = search;

            fetch(`../backend/archived_accounts_backend.php?action=list_archived&page=${page}&per_page=10&search=${encodeURIComponent(search)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderTable(data.data);
                        updatePagination(data.pagination);
                    } else {
                        showToast(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to load archived accounts', 'danger');
                });
        }

        // Render table
        function renderTable(accounts) {
            const tbody = document.querySelector('#archivedTable tbody');
            tbody.innerHTML = '';

            if (accounts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No archived accounts found</td></tr>';
                return;
            }

            accounts.forEach((account, index) => {
                const row = document.createElement('tr');
                
                const lastLogin = account.last_login 
                    ? new Date(account.last_login).toLocaleDateString()
                    : '<span class="last-login-warning">Never logged in</span>';
                
                const archivedDate = new Date(account.archived_at).toLocaleDateString();
                const fullName = `${account.first_name} ${account.last_name}`;

                row.innerHTML = `
                    <td>${(currentPage - 1) * 10 + index + 1}</td>
                    <td>${fullName}</td>
                    <td>${account.email || 'N/A'}</td>
                    <td>${account.contact_number}</td>
                    <td>${lastLogin}</td>
                    <td>${archivedDate}</td>
                    <td><span class="archived-badge">${account.archived_reason || 'N/A'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-success restore-btn" 
                                data-id="${account.id}"
                                data-name="${fullName}"
                                data-email="${account.email || 'N/A'}"
                                title="Restore Account">
                            <i class="fas fa-undo"></i>
                        </button>
                        <button class="btn btn-sm btn-info history-btn" 
                                data-id="${account.id}"
                                title="View History">
                            <i class="fas fa-history"></i>
                        </button>
                    </td>
                `;
                
                tbody.appendChild(row);
            });

            // Attach event listeners
            attachButtonListeners();
        }

        // Attach event listeners to buttons
        function attachButtonListeners() {
            document.querySelectorAll('.restore-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    showRestoreModal(
                        this.dataset.id,
                        this.dataset.name,
                        this.dataset.email
                    );
                });
            });

            document.querySelectorAll('.history-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    viewHistory(this.dataset.id);
                });
            });
        }

        // Show restore modal
        function showRestoreModal(residentId, name, email) {
            currentResidentId = residentId;
            document.getElementById('restoreResidentName').textContent = name;
            document.getElementById('restoreResidentEmail').textContent = email;
            document.getElementById('restoreNotes').value = '';

            const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
            modal.show();
        }

        // Restore account
        function restoreAccount() {
            if (!currentResidentId) return;

            const notes = document.getElementById('restoreNotes').value || 'Account restored by admin';
            const btn = document.getElementById('confirmRestoreBtn');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Restoring...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('resident_id', currentResidentId);
            formData.append('notes', notes);

            fetch('../backend/archived_accounts_backend.php?action=restore', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Account restored successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('restoreModal')).hide();
                    loadArchivedAccounts(currentPage, currentSearch);
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to restore account', 'danger');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        // View history
        function viewHistory(residentId) {
            const modal = new bootstrap.Modal(document.getElementById('historyModal'));
            modal.show();

            const content = document.getElementById('historyContent');
            content.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';

            fetch(`../backend/archived_accounts_backend.php?action=view_history&resident_id=${residentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderHistory(data.data);
                    } else {
                        content.innerHTML = '<div class="alert alert-danger">Failed to load history</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = '<div class="alert alert-danger">Error loading history</div>';
                });
        }

        // Render history
        function renderHistory(history) {
            const content = document.getElementById('historyContent');

            if (history.length === 0) {
                content.innerHTML = '<p class="text-muted">No history available</p>';
                return;
            }

            let html = '<div class="timeline">';
            history.forEach(item => {
                const date = new Date(item.performed_at).toLocaleString();
                const actionClass = item.action === 'archived' ? 'danger' : 'success';
                const icon = item.action === 'archived' ? 'fa-archive' : 'fa-undo';

                html += `
                    <div class="timeline-item mb-3">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <span class="badge bg-${actionClass}">
                                    <i class="fas ${icon}"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">${item.action.charAt(0).toUpperCase() + item.action.slice(1)}</h6>
                                <p class="text-muted mb-1">${item.reason}</p>
                                <small class="text-muted">
                                    ${item.performed_by_name ? 'By: ' + item.performed_by_name + ' - ' : ''}
                                    ${date}
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';

            content.innerHTML = html;
        }

        // Update pagination
        function updatePagination(pagination) {
            const info = document.querySelector('.pagination-info');
            const prevBtn = document.getElementById('prevPage');
            const nextBtn = document.getElementById('nextPage');

            const start = (pagination.page - 1) * pagination.per_page + 1;
            const end = Math.min(pagination.page * pagination.per_page, pagination.total);

            info.textContent = `Showing ${start} to ${end} of ${pagination.total} entries`;

            prevBtn.classList.toggle('disabled', pagination.page === 1);
            nextBtn.classList.toggle('disabled', pagination.page >= pagination.total_pages);
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }

            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;

            toastContainer.appendChild(toastEl);
            const toast = new bootstrap.Toast(toastEl);
            toast.show();

            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            loadArchivedAccounts();

            // Search
            document.getElementById('searchBtn').addEventListener('click', function() {
                const search = document.getElementById('searchArchived').value;
                loadArchivedAccounts(1, search);
            });

            document.getElementById('searchArchived').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    loadArchivedAccounts(1, this.value);
                }
            });

            // Pagination
            document.getElementById('prevPage').addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPage > 1) {
                    loadArchivedAccounts(currentPage - 1, currentSearch);
                }
            });

            document.getElementById('nextPage').addEventListener('click', function(e) {
                e.preventDefault();
                loadArchivedAccounts(currentPage + 1, currentSearch);
            });

            // Restore confirmation
            document.getElementById('confirmRestoreBtn').addEventListener('click', restoreAccount);
        });
    </script>
</body>
</html>