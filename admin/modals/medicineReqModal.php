<!-- admin/modals/medicineReqModal.php -->

<!-- Add Medicine Modal -->
<div class="modal fade" id="addMedicineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="../backend/medicineReq-backend.php">
                <input type="hidden" name="action" value="add_medicine">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Medicine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Medicine Name *</label>
                        <input type="text" class="form-control" name="medicine_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" name="category" placeholder="e.g., Antibiotic, Pain Relief">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Medicine description..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stock Quantity *</label>
                                <input type="number" class="form-control" name="stock_quantity" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Minimum Stock *</label>
                                <input type="number" class="form-control" name="minimum_stock" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit *</label>
                        <select class="form-select" name="unit" required>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="tablets">Tablets</option>
                            <option value="capsules">Capsules</option>
                            <option value="bottles">Bottles</option>
                            <option value="boxes">Boxes</option>
                            <option value="packs">Packs</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Medicine Modal -->
<div class="modal fade" id="editMedicineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="../backend/medicineReq-backend.php">
                <input type="hidden" name="action" value="update_medicine">
                <input type="hidden" name="id" id="edit_medicine_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Medicine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Medicine Name *</label>
                        <input type="text" class="form-control" name="medicine_name" id="edit_medicine_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" name="category" id="edit_category">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stock Quantity *</label>
                                <input type="number" class="form-control" name="stock_quantity" id="edit_stock_quantity" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Minimum Stock *</label>
                                <input type="number" class="form-control" name="minimum_stock" id="edit_minimum_stock" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit *</label>
                        <select class="form-select" name="unit" id="edit_unit" required>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="tablets">Tablets</option>
                            <option value="capsules">Capsules</option>
                            <option value="bottles">Bottles</option>
                            <option value="boxes">Boxes</option>
                            <option value="packs">Packs</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="edit_is_active" value="1">
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Medicine Confirmation Modal -->
<div class="modal fade" id="deleteMedicineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="../backend/medicineReq-backend.php">
                <input type="hidden" name="action" value="delete_medicine">
                <input type="hidden" name="id" id="delete_medicine_id">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="delete_medicine_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Medicine Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestDetails">
                <!-- Details will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="../backend/medicineReq-backend.php">
                <input type="hidden" name="action" value="update_request_status">
                <input type="hidden" name="request_id" id="status_request_id">
                <input type="hidden" name="status" id="status_value">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalTitle">Update Request Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea class="form-control" name="admin_notes" rows="3" placeholder="Add notes for the resident..."></textarea>
                    </div>
                    <div class="mb-3" id="disapproval_reason_field" style="display: none;">
                        <label class="form-label">Reason for Disapproval *</label>
                        <textarea class="form-control" name="disapproval_reason" rows="3" placeholder="Please provide reason for disapproval..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Generate Report Modal -->
<div class="modal fade" id="generateReportModal" tabindex="-1" aria-labelledby="generateReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generateReportModalLabel"><i class="fas fa-chart-bar me-2"></i>Generate Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="reportType" class="form-label">Report Type</label>
                    <select class="form-select" id="reportType">
                        <option value="inventory">Inventory Report</option>
                        <option value="requests">Requests Report</option>
                        <option value="low_stock">Low Stock Alert</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="dateRange" class="form-label">Date Range</label>
                    <select class="form-select" id="dateRange">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month" selected>This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="row mb-3" id="customDateRange" style="display: none;">
                    <div class="col-md-6">
                        <label for="startDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startDate">
                    </div>
                    <div class="col-md-6">
                        <label for="endDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="endDate">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="reportFormat" class="form-label">Format</label>
                    <select class="form-select" id="reportFormat">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="generateReportBtn">Generate Report</button>
            </div>
        </div>
    </div>
</div>