// reservation.js (updated with reports functionality)
document.addEventListener('DOMContentLoaded', function() {
    // Modal event handlers
    $('#viewServiceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var reservationId = button.data('id');
        
        $('#scheduled-datetime-section').hide();
        $('#processed-by-section').hide();
        $('#rejection-reason-section').hide();
        $('#setup-time-section').hide();
        $('#duration-type-section').hide();
        $('#event-location-section').hide();
        
        $('#view-reservation-id, #view-resident-name, #view-service-type, #view-reservation-date, #view-duration, #view-status, #view-purpose, #view-contact, #view-email, #view-date-requested, #view-notes, #view-setup-time, #view-duration-type, #view-event-location').text('Loading...');
        
        $.ajax({
            url: '../backend/reservation-backend.php?action=get&id=' + reservationId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    var reservation = data.reservation;
                    
                    $('#view-reservation-id').text('SR-' + String(reservation.id).padStart(3, '0'));
                    $('#view-resident-name').text(reservation.resident_name || 'N/A');
                    $('#view-service-type').html(reservation.service_types || 'N/A');
                    $('#view-reservation-date').text(reservation.reservation_date || 'N/A');
                    $('#view-duration').text(reservation.duration || 'N/A');
                    $('#view-status').html(reservation.status_badge || 'N/A');
                    $('#view-purpose').text(reservation.purpose || 'N/A');
                    $('#view-contact').text(reservation.contact_number || 'N/A');
                    $('#view-email').text(reservation.email || 'N/A');
                    $('#view-date-requested').text(reservation.date_requested || 'N/A');
                    
                    // Extract and display additional fields from notes
                    var notes = reservation.notes || '';
                    
                    // Extract Setup Time
                    var setupTimeMatch = notes.match(/Setup Time:\s*(\d+:\d+)/);
                    if (setupTimeMatch && setupTimeMatch[1]) {
                        $('#view-setup-time').text(setupTimeMatch[1]);
                        $('#setup-time-section').show();
                    }
                    
                    // Extract Duration Type
                    var durationTypeMatch = notes.match(/Duration Type:\s*(\w+)/);
                    if (durationTypeMatch && durationTypeMatch[1]) {
                        $('#view-duration-type').text(durationTypeMatch[1].replace('_', ' ').toUpperCase());
                        $('#duration-type-section').show();
                    }
                    
                    // Extract Event Location
                    var eventLocationMatch = notes.match(/Event Location:\s*([^]*)/);
                    if (eventLocationMatch && eventLocationMatch[1]) {
                        $('#view-event-location').text(eventLocationMatch[1].trim());
                        $('#event-location-section').show();
                    }
                    
                    // Display remaining notes (without the extracted fields)
                    var cleanNotes = notes
                        .replace(/Setup Time:\s*\d+:\d+\s*/, '')
                        .replace(/Duration Type:\s*\w+\s*/, '')
                        .replace(/Event Location:\s*[^]*/, '')
                        .trim();
                    
                    $('#view-notes').text(cleanNotes || 'No additional notes');
                    
                    if (reservation.scheduled_datetime) {
                        $('#view-scheduled-datetime').text(reservation.scheduled_datetime);
                        $('#scheduled-datetime-section').show();
                    }
                    
                    if (reservation.processed_by) {
                        $('#view-processed-by').text(reservation.processed_by);
                        $('#processed-by-section').show();
                    }
                    
                    if (reservation.rejection_reason) {
                        $('#view-rejection-reason').text(reservation.rejection_reason);
                        $('#rejection-reason-section').show();
                    }
                } else {
                    console.error('Server error:', data.message);
                    alert('Error: ' + (data.message || 'Unknown error occurred'));
                    $('#viewServiceModal').modal('hide');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', xhr.responseText);
                alert('Error fetching reservation details. Please try again.');
                $('#viewServiceModal').modal('hide');
            }
        });
    });

    $('#approveServiceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var reservationId = button.data('id');
        
        $('#approve-service-id').val(reservationId);
        $('#approve-notes').val('');
    });

    $('#rejectServiceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var reservationId = button.data('id');
        
        $('#reject-service-id').val(reservationId);
        $('#reject-reason').val('');
    });

    $('#updateServiceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var reservationId = button.data('id');
        
        $('#update-service-id').val(reservationId);
        $('#update-status').val('');
        $('#update-notes').val('');
    });

    $('#approveServiceForm').on('submit', function(e) {
        e.preventDefault();
        
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        var formData = $(this).serialize() + '&action=approve';
        
        $.ajax({
            url: '../backend/reservation-backend.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 10000, 
            success: function(response) {
                if (response.success) {
                    alert('Service reservation approved successfully!');
                    $('#approveServiceModal').modal('hide');
                    location.reload();
                } else {
                    console.error('Server error:', response.message);
                    alert('Error: ' + (response.message || 'Unknown error occurred'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', xhr.responseText);
                if (status === 'timeout') {
                    alert('Request timed out. Please try again.');
                } else {
                    alert('An error occurred while processing the request. Please try again.');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#rejectServiceForm').on('submit', function(e) {
        e.preventDefault();
        
        var rejectionReason = $('#reject-reason').val().trim();
        if (!rejectionReason) {
            alert('Please provide a reason for rejection.');
            return;
        }
        
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        var formData = $(this).serialize() + '&action=reject';
        
        $.ajax({
            url: '../backend/reservation-backend.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                if (response.success) {
                    alert('Service reservation rejected successfully!');
                    $('#rejectServiceModal').modal('hide');
                    location.reload();
                } else {
                    console.error('Server error:', response.message);
                    alert('Error: ' + (response.message || 'Unknown error occurred'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', xhr.responseText);
                if (status === 'timeout') {
                    alert('Request timed out. Please try again.');
                } else {
                    alert('An error occurred while processing the request. Please try again.');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#updateServiceForm').on('submit', function(e) {
        e.preventDefault();
        
        var status = $('#update-status').val();
        if (!status) {
            alert('Please select a status.');
            return;
        }
        
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        var formData = $(this).serialize() + '&action=update_status';
        
        $.ajax({
            url: '../backend/reservation-backend.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                if (response.success) {
                    alert('Service status updated successfully!');
                    $('#updateServiceModal').modal('hide');
                    location.reload();
                } else {
                    console.error('Server error:', response.message);
                    alert('Error: ' + (response.message || 'Unknown error occurred'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', xhr.responseText);
                if (status === 'timeout') {
                    alert('Request timed out. Please try again.');
                } else {
                    alert('An error occurred while processing the request. Please try again.');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#approveServiceModal, #rejectServiceModal, #updateServiceModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });

    // Initialize reports functionality
    initializeReports();
});

function initializeReports() {
    const reportType = document.getElementById('report-type');
    const monthSelection = document.getElementById('month-selection');
    const generateReportBtn = document.getElementById('generate-report');
    const exportCsvBtn = document.getElementById('export-csv');
    const exportJsonBtn = document.getElementById('export-json');
    const reportResults = document.getElementById('report-results');
    
    // If report elements don't exist, return (for pages without reports)
    if (!reportType || !generateReportBtn) return;
    
    let currentChart = null;
    
    // Toggle month selection based on report type
    reportType.addEventListener('change', function() {
        if (monthSelection) {
            monthSelection.style.display = this.value === 'monthly' ? 'block' : 'none';
        }
    });
    
    // Generate report
    generateReportBtn.addEventListener('click', function() {
        const reportTypeVal = reportType.value;
        const month = document.getElementById('report-month').value;
        const year = document.getElementById('report-year').value;
        
        generateReportBtn.disabled = true;
        generateReportBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
        
        $.ajax({
            url: '../backend/reservation-backend.php?action=generate_service_report',
            type: 'GET',
            data: {
                report_type: reportTypeVal,
                month: month,
                year: year
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayReport(response);
                    if (reportResults) {
                        reportResults.style.display = 'block';
                    }
                } else {
                    alert('Error generating report: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                alert('Error generating report. Please try again.');
            },
            complete: function() {
                generateReportBtn.disabled = false;
                generateReportBtn.innerHTML = '<i class="fas fa-chart-bar me-1"></i> Generate Report';
            }
        });
    });
    
    // Export functions
    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', function() {
            exportReport('csv');
        });
    }
    
    if (exportJsonBtn) {
        exportJsonBtn.addEventListener('click', function() {
            exportReport('json');
        });
    }
    
    function exportReport(format) {
        const reportTypeVal = reportType.value;
        const month = document.getElementById('report-month').value;
        const year = document.getElementById('report-year').value;
        
        const url = `../backend/reservation-backend.php?action=export_service_report&report_type=${reportTypeVal}&month=${month}&year=${year}&format=${format}`;
        window.open(url, '_blank');
    }
    
    function displayReport(data) {
        // Update report title
        const reportTitle = document.getElementById('report-title');
        if (reportTitle) {
            reportTitle.textContent = 
                `${data.report_type === 'yearly' ? 'Yearly' : 'Monthly'} Service Reservation Report - ${data.period}`;
        }
        
        // Display summary cards
        displaySummaryCards(data.summary);
        
        // Display breakdown table
        displayBreakdownTable(data.breakdown, data.report_type);
        
        // Display service breakdown
        displayServiceBreakdown(data.service_breakdown);
        
        // Create chart
        createStatusChart(data.summary);
    }
    
    function displaySummaryCards(summary) {
        const cardsContainer = document.getElementById('summary-cards');
        if (!cardsContainer) return;
        
        const cards = [
            {
                title: 'Total Reservations',
                value: summary.total_reservations,
                color: 'primary',
                icon: 'fas fa-list'
            },
            {
                title: 'Approved',
                value: summary.approved_reservations,
                color: 'success',
                icon: 'fas fa-check-circle'
            },
            {
                title: 'Pending',
                value: summary.pending_reservations,
                color: 'warning',
                icon: 'fas fa-clock'
            },
            {
                title: 'Completed',
                value: summary.completed_reservations,
                color: 'info',
                icon: 'fas fa-flag-checkered'
            },
            {
                title: 'Cancelled',
                value: summary.cancelled_reservations,
                color: 'danger',
                icon: 'fas fa-times-circle'
            }
        ];
        
        cardsContainer.innerHTML = cards.map(card => `
            <div class="col-md-2 col-6 mb-3">
                <div class="card bg-${card.color} text-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-white-50 small">${card.title}</div>
                                <div class="fs-5 fw-bold">${card.value}</div>
                            </div>
                            <div class="align-self-center">
                                <i class="${card.icon} fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    function displayBreakdownTable(breakdown, reportType) {
        const tableBody = document.getElementById('breakdown-table-body');
        if (!tableBody) return;
        
        tableBody.innerHTML = breakdown.map(row => `
            <tr>
                <td>${formatPeriod(row.period, reportType)}</td>
                <td>${row.total}</td>
                <td>${row.approved}</td>
                <td>${row.pending}</td>
                <td>${row.cancelled}</td>
                <td>${row.completed}</td>
            </tr>
        `).join('');
    }
    
    function displayServiceBreakdown(serviceBreakdown) {
        const container = document.getElementById('service-breakdown-table');
        if (!container) return;
        
        if (serviceBreakdown.length === 0) {
            container.innerHTML = '<p class="text-muted">No service data available</p>';
            return;
        }
        
        container.innerHTML = `
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Service Type</th>
                            <th>Total Requests</th>
                            <th>Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${serviceBreakdown.map(service => `
                            <tr>
                                <td>${service.service_name}</td>
                                <td>${service.total_requests}</td>
                                <td>${service.total_quantity || service.total_requests}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    function createStatusChart(summary) {
        const ctx = document.getElementById('statusChart');
        if (!ctx) return;
        
        const chartCtx = ctx.getContext('2d');
        
        // Destroy existing chart if it exists
        if (currentChart) {
            currentChart.destroy();
        }
        
        const data = {
            labels: ['Approved', 'Pending', 'Completed', 'Cancelled'],
            datasets: [{
                data: [
                    summary.approved_reservations,
                    summary.pending_reservations,
                    summary.completed_reservations,
                    summary.cancelled_reservations
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#17a2b8',
                    '#dc3545'
                ],
                borderWidth: 1
            }]
        };
        
        currentChart = new Chart(chartCtx, {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    function formatPeriod(period, reportType) {
        if (reportType === 'yearly') {
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            const [year, month] = period.split('-');
            return `${months[parseInt(month) - 1]} ${year}`;
        } else {
            return new Date(period).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }
    }
}


// Add this to your reservation.js file

function initializeReports() {
    const reportType = document.getElementById('report-type');
    const monthSelection = document.getElementById('month-selection');
    const generateReportBtn = document.getElementById('generate-report');
    const exportExcelBtn = document.getElementById('export-excel');
    const exportCsvBtn = document.getElementById('export-csv');
    const reportResults = document.getElementById('report-results');
    
    // If report elements don't exist, return (for pages without reports)
    if (!reportType || !generateReportBtn) return;
    
    let currentChart = null;
    
    // Toggle month selection based on report type
    reportType.addEventListener('change', function() {
        if (monthSelection) {
            monthSelection.style.display = this.value === 'monthly' ? 'block' : 'none';
        }
    });
    
    // Generate report
    generateReportBtn.addEventListener('click', function() {
        const reportTypeVal = reportType.value;
        const month = document.getElementById('report-month').value;
        const year = document.getElementById('report-year').value;
        
        generateReportBtn.disabled = true;
        generateReportBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
        
        $.ajax({
            url: '../backend/reservation-backend.php?action=generate_service_report',
            type: 'GET',
            data: {
                report_type: reportTypeVal,
                month: month,
                year: year
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayReport(response);
                    if (reportResults) {
                        reportResults.style.display = 'block';
                    }
                } else {
                    alert('Error generating report: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                alert('Error generating report. Please try again.');
            },
            complete: function() {
                generateReportBtn.disabled = false;
                generateReportBtn.innerHTML = '<i class="fas fa-chart-bar me-1"></i> Generate Report';
            }
        });
    });
    
    // Export functions
    if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', function() {
            exportReport('excel');
        });
    }
    
    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', function() {
            exportReport('csv');
        });
    }
    
    function exportReport(format) {
        const reportTypeVal = reportType.value;
        const month = document.getElementById('report-month').value;
        const year = document.getElementById('report-year').value;
        
        const url = `../backend/reservation-backend.php?action=export_service_report&report_type=${reportTypeVal}&month=${month}&year=${year}&format=${format}`;
        window.open(url, '_blank');
    }
    
    function displayReport(data) {
        // Update report title
        const reportTitle = document.getElementById('report-title');
        if (reportTitle) {
            reportTitle.textContent = 
                `${data.report_type === 'yearly' ? 'Yearly' : 'Monthly'} Service Reservation Report - ${data.period}`;
        }
        
        // Display summary cards
        displaySummaryCards(data.summary);
        
        // Display breakdown table
        displayBreakdownTable(data.breakdown, data.report_type);
        
        // Display service breakdown
        displayServiceBreakdown(data.service_breakdown);
        
        // Create chart
        createStatusChart(data.summary);
    }
    
    function displaySummaryCards(summary) {
        const cardsContainer = document.getElementById('summary-cards');
        if (!cardsContainer) return;
        
        const cards = [
            {
                title: 'Total Reservations',
                value: summary.total_reservations,
                color: 'primary',
                icon: 'fas fa-list'
            },
            {
                title: 'Approved',
                value: summary.approved_reservations,
                color: 'success',
                icon: 'fas fa-check-circle'
            },
            {
                title: 'Pending',
                value: summary.pending_reservations,
                color: 'warning',
                icon: 'fas fa-clock'
            },
            {
                title: 'In Progress',
                value: summary.in_progress_reservations,
                color: 'info',
                icon: 'fas fa-spinner'
            },
            {
                title: 'Completed',
                value: summary.completed_reservations,
                color: 'secondary',
                icon: 'fas fa-flag-checkered'
            },
            {
                title: 'Cancelled',
                value: summary.cancelled_reservations,
                color: 'danger',
                icon: 'fas fa-times-circle'
            }
        ];
        
        cardsContainer.innerHTML = cards.map(card => `
            <div class="col-md-2 col-6 mb-3">
                <div class="card bg-${card.color} text-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-white-50 small">${card.title}</div>
                                <div class="fs-5 fw-bold">${card.value}</div>
                            </div>
                            <div class="align-self-center">
                                <i class="${card.icon} fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    function displayBreakdownTable(breakdown, reportType) {
        const tableBody = document.getElementById('breakdown-table-body');
        if (!tableBody) return;
        
        tableBody.innerHTML = breakdown.map(row => `
            <tr>
                <td>${formatPeriod(row.period, reportType)}</td>
                <td>${row.total}</td>
                <td>${row.approved}</td>
                <td>${row.pending}</td>
                <td>${row.in_progress}</td>
                <td>${row.completed}</td>
                <td>${row.cancelled}</td>
            </tr>
        `).join('');
    }
    
    function displayServiceBreakdown(serviceBreakdown) {
        const container = document.getElementById('service-breakdown-table');
        if (!container) return;
        
        if (serviceBreakdown.length === 0) {
            container.innerHTML = '<p class="text-muted">No service data available</p>';
            return;
        }
        
        container.innerHTML = `
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Service Type</th>
                            <th>Total Requests</th>
                            <th>Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${serviceBreakdown.map(service => `
                            <tr>
                                <td>${service.service_name}</td>
                                <td>${service.total_requests}</td>
                                <td>${service.total_quantity || service.total_requests}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }
    
    function createStatusChart(summary) {
        const ctx = document.getElementById('statusChart');
        if (!ctx) return;
        
        const chartCtx = ctx.getContext('2d');
        
        // Destroy existing chart if it exists
        if (currentChart) {
            currentChart.destroy();
        }
        
        const data = {
            labels: ['Approved', 'Pending', 'In Progress', 'Completed', 'Cancelled'],
            datasets: [{
                data: [
                    summary.approved_reservations,
                    summary.pending_reservations,
                    summary.in_progress_reservations,
                    summary.completed_reservations,
                    summary.cancelled_reservations
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#17a2b8',
                    '#6c757d',
                    '#dc3545'
                ],
                borderWidth: 1
            }]
        };
        
        currentChart = new Chart(chartCtx, {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    function formatPeriod(period, reportType) {
        if (reportType === 'yearly') {
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            const [year, month] = period.split('-');
            return `${months[parseInt(month) - 1]} ${year}`;
        } else {
            return new Date(period).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }
    }
}

// Initialize reports when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeReports();
});