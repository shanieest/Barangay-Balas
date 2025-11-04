

// Report generation functionality
document.addEventListener('DOMContentLoaded', function() {
    const reportType = document.getElementById('report-type');
    const monthSelection = document.getElementById('month-selection');
    const generateReportBtn = document.getElementById('generate-report');
    const exportExcelBtn = document.getElementById('export-excel');
    const exportCsvBtn = document.getElementById('export-csv');
    const reportResults = document.getElementById('report-results');
    
    let currentReportData = null;

    // Toggle month selection based on report type
    reportType.addEventListener('change', function() {
        monthSelection.style.display = this.value === 'monthly' ? 'block' : 'none';
    });

    // Generate report
    generateReportBtn.addEventListener('click', function() {
        generateReport();
    });

    // Export buttons
    exportExcelBtn.addEventListener('click', function() {
        exportReport('excel');
    });

    exportCsvBtn.addEventListener('click', function() {
        exportReport('csv');
    });

    function generateReport() {
        const reportTypeVal = document.getElementById('report-type').value;
        const month = document.getElementById('report-month').value;
        const year = document.getElementById('report-year').value;
        
        if (!year) {
            alert('Please select a year');
            return;
        }

        if (reportTypeVal === 'monthly' && !month) {
            alert('Please select a month');
            return;
        }

        const button = generateReportBtn;
        const originalText = button.innerHTML;
        
        // Add loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
        button.disabled = true;

        const formData = new FormData();
        formData.append('action', 'get_report_data');
        formData.append('report_type', reportTypeVal);
        formData.append('month', month);
        formData.append('year', year);

        fetch('../backend/process_request.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentReportData = data.data;
                displayReport(data.data);
                reportResults.style.display = 'block';
            } else {
                alert('Error generating report: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Report generation error:', err);
            alert('Something went wrong while generating the report.');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }

    function displayReport(data) {
        const reportTitle = document.getElementById('report-title');
        if (data.report_type === 'monthly') {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            reportTitle.textContent = `Document Requests Report - ${monthNames[parseInt(data.month) - 1]} ${data.year}`;
        } else {
            reportTitle.textContent = `Document Requests Report - Year ${data.year}`;
        }

        updateSummaryCards(data.summary);
        updateStatusChart(data.summary.status_counts);
        updateDocumentTypeTable(data.summary.document_type_counts);
        updateBreakdownTable(data);
    }

    function updateSummaryCards(summary) {
        const summaryCards = document.getElementById('summary-cards');
        const statusColors = {
            'Pending': 'warning',
            'Approved': 'success',
            'Disapproved': 'danger'
        };

        let cardsHtml = `
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-value fs-3 fw-bold">${summary.total}</div>
                                <div>Total Requests</div>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        Object.entries(summary.status_counts).forEach(([status, count]) => {
            const color = statusColors[status] || 'secondary';
            const icon = status === 'Pending' ? 'clock' : 
                        status === 'Approved' ? 'check-circle' : 'times-circle';
            
            cardsHtml += `
                <div class="col-md-3">
                    <div class="card text-white bg-${color} mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-value fs-3 fw-bold">${count}</div>
                                    <div>${status}</div>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-${icon} fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        summaryCards.innerHTML = cardsHtml;
    }

    function updateStatusChart(statusCounts) {
        const ctx = document.getElementById('statusChart').getContext('2d');
        
        if (window.statusChartInstance) {
            window.statusChartInstance.destroy();
        }

        const colors = {
            'Pending': '#ffc107',
            'Approved': '#198754',
            'Disapproved': '#dc3545'
        };

        window.statusChartInstance = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: Object.keys(statusCounts),
                datasets: [{
                    data: Object.values(statusCounts),
                    backgroundColor: Object.keys(statusCounts).map(status => colors[status]),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
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

    function updateDocumentTypeTable(documentTypeCounts) {
        const tableContainer = document.getElementById('document-breakdown-table');
        
        let tableHtml = `
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Document Type</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        const total = Object.values(documentTypeCounts).reduce((sum, count) => sum + count, 0);
        
        Object.entries(documentTypeCounts)
            .sort((a, b) => b[1] - a[1])
            .forEach(([docType, count]) => {
                const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
                tableHtml += `
                    <tr>
                        <td>${docType}</td>
                        <td>${count}</td>
                        <td>${percentage}%</td>
                    </tr>
                `;
            });

        tableHtml += `
                    </tbody>
                </table>
            </div>
        `;

        tableContainer.innerHTML = tableHtml;
    }

    function updateBreakdownTable(data) {
        const tableBody = document.getElementById('breakdown-table-body');
        
        if (data.report_type === 'monthly') {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const monthName = monthNames[parseInt(data.month) - 1];
            
            const summary = data.summary;
            tableBody.innerHTML = `
                <tr>
                    <td>${monthName} ${data.year}</td>
                    <td>${summary.total}</td>
                    <td>${summary.status_counts.Pending || 0}</td>
                    <td>${summary.status_counts.Approved || 0}</td>
                    <td>${summary.status_counts.Disapproved || 0}</td>
                </tr>
            `;
        } else {
            let tableHtml = '';
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            data.monthly_breakdown.forEach(monthData => {
                tableHtml += `
                    <tr>
                        <td>${monthNames[monthData.month - 1]} ${data.year}</td>
                        <td>${monthData.total}</td>
                        <td>${monthData.pending}</td>
                        <td>${monthData.approved}</td>
                        <td>${monthData.disapproved}</td>
                    </tr>
                `;
            });

            tableHtml += `
                <tr class="table-primary fw-bold">
                    <td>Total ${data.year}</td>
                    <td>${data.summary.total}</td>
                    <td>${data.summary.status_counts.Pending || 0}</td>
                    <td>${data.summary.status_counts.Approved || 0}</td>
                    <td>${data.summary.status_counts.Disapproved || 0}</td>
                </tr>
            `;

            tableBody.innerHTML = tableHtml;
        }
    }

    function exportReport(exportType) {
        if (!currentReportData) {
            alert('Please generate a report first');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'generate_report');
        formData.append('report_type', currentReportData.report_type);
        formData.append('month', currentReportData.month);
        formData.append('year', currentReportData.year);
        formData.append('export_type', exportType);

        fetch('../backend/process_request.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.filepath;
            } else {
                alert('Error exporting report: ' + data.message);
            }
        })
        .catch(err => {
            console.error('Export error:', err);
            alert('Something went wrong while exporting the report.');
        });
    }
});
