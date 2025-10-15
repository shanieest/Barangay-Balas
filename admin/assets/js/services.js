//modal events and fetch calls for document requests
document.addEventListener('DOMContentLoaded', function() {
 
    const viewRequestModal = document.getElementById('viewRequestModal');
    if (viewRequestModal) {
        viewRequestModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-id');

            fetch(`get-request-details.php?id=${requestId}`)
                .then(res => res.json())
                .then(data => {
                  
                    console.log('Full API Response:', data);
                    console.log('Processed By Value:', data.processed_by);
                    
                    document.getElementById('viewRequestId').textContent = data.id;
                    document.getElementById('viewDocumentType').textContent = data.document_type;
                    document.getElementById('viewDateRequested').textContent = data.date_requested;
                    document.getElementById('viewResidentName').textContent = data.full_name;
                    document.getElementById('viewResidentAddress').textContent = `${data.houseno ? 'House #' + data.houseno + ', ' : ''}${data.purok ? 'Purok ' + data.purok : ''}`;   
                    document.getElementById('viewResidentContact').textContent = data.contact_number || 'N/A';
                    document.getElementById('viewResidentEmail').textContent = data.resident_email || 'N/A';
                    
                    const accountStatus = document.getElementById('viewAccountStatus');
                    accountStatus.textContent = data.account_status || 'N/A';
                    accountStatus.className = 'badge ms-2 bg-' + 
                        (data.account_status == 'Approved' ? 'success' : 
                         (data.account_status == 'Pending' ? 'warning' : 'danger'));
                    
                    const processedByElement = document.getElementById('viewProcessedBy');
                    console.log('Processed By Element Found:', processedByElement);
                    if (processedByElement) {
                        processedByElement.textContent = data.processed_by || 'Not processed yet';
                        console.log('Set processed_by to:', data.processed_by || 'Not processed yet');
                    } else {
                        console.error('viewProcessedBy element not found!');
                    }
                    
                    document.getElementById('viewPurpose').textContent = data.purpose;
                    document.getElementById('viewNotes').textContent = data.notes || 'No notes provided';

                    const statusBadge = document.getElementById('viewStatusBadge');
                    const downloadBtn = document.getElementById('downloadDocumentBtn');
                    statusBadge.textContent = data.status;
                    if (data.status === 'Approved') {
                        statusBadge.className = 'badge bg-success';
                        if (data.document_path) {
                            downloadBtn.style.display = 'inline-block';
                            downloadBtn.href = `download-document.php?id=${requestId}`;
                        } else downloadBtn.style.display = 'none';
                    } else if (data.status === 'Disapproved') {
                        statusBadge.className = 'badge bg-danger';
                        downloadBtn.style.display = 'none';
                    } else {
                        statusBadge.className = 'badge bg-warning';
                        downloadBtn.style.display = 'none';
                    }
                })
                .catch(err => {
                    console.error('Error fetching request details:', err);
                    console.error('Full error:', err);
                });
        });
    }

    const approveModal = document.getElementById('approveRequestModal');
    const approveForm = document.getElementById('approveForm');

    if (approveModal) {
        approveModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('approveRequestId').value = button.getAttribute('data-id');
        });
    }

    if (approveForm) {
        approveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(approveForm);

            fetch('process_request.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(" " + data.message);

                    if (data.auto_download && data.file_path) {
                        const downloadUrl = `download-document.php?id=${document.getElementById('approveRequestId').value}`;
                        const link = document.createElement('a');
                        link.href = downloadUrl;
                        link.download = '';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }

                    const downloadBtn = document.getElementById('downloadDocumentBtn');
                    if (data.file_path) {
                        downloadBtn.href = `download-document.php?id=${document.getElementById('approveRequestId').value}`;
                        downloadBtn.style.display = 'inline-block';
                    }

                    bootstrap.Modal.getInstance(approveModal).hide();
                    location.reload();
                } else alert("❌ Error: " + data.message);
            })
            .catch(err => {
                console.error('Approve error:', err);
                alert("Something went wrong while approving. Check console.");
            });
        });
    }

    const disapproveModal = document.getElementById('disapproveRequestModal');
    const disapproveForm = document.getElementById('disapproveForm');

    if (disapproveModal) {
        disapproveModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('disapproveRequestId').value = button.getAttribute('data-id');
        });
    }

    if (disapproveForm) {
        disapproveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(disapproveForm);

            fetch('process_request.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Request disapproved!");
                    location.reload();
                } else alert("Error: " + data.message);
            })
            .catch(err => {
                console.error('Disapprove error:', err);
                alert("Something went wrong while disapproving. Check console.");
            });
        });
    }
});

// Report generation functionality
document.addEventListener('DOMContentLoaded', function() {
    const monthlyReportForm = document.getElementById('monthlyReportForm');
    const yearlyReportForm = document.getElementById('yearlyReportForm');
    
    // Set current year and month as default
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
    
    document.getElementById('year_monthly').value = currentYear;
    document.getElementById('month').value = currentMonth;
    document.getElementById('year_yearly').value = currentYear;
    
    if (monthlyReportForm) {
        monthlyReportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            generateReport('monthly');
        });
    }
    
    if (yearlyReportForm) {
        yearlyReportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            generateReport('yearly');
        });
    }
    
    function generateReport(reportType) {
        const form = reportType === 'monthly' ? monthlyReportForm : yearlyReportForm;
        const formData = new FormData(form);
        const button = form.querySelector('button[type="submit"]');
        const originalText = button.innerHTML;
        
        // Add loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating Report...';
        button.disabled = true;
        
        fetch('process_request.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Download the generated file
                window.location.href = data.filepath;
                
                // Show success message
                showAlert('Report generated successfully! Your download should start shortly.', 'success');
            } else {
                showAlert('Error generating report: ' + data.message, 'danger');
            }
        })
        .catch(err => {
            console.error('Report generation error:', err);
            showAlert('Something went wrong while generating the report.', 'danger');
        })
        .finally(() => {
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
    
    function showAlert(message, type) {
        // Remove existing alerts
        const existingAlert = document.querySelector('.alert-dismissible');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert after the report forms
        const reportCard = document.querySelector('.card .card-header');
        const cardBody = reportCard.closest('.card').querySelector('.card-body');
        cardBody.appendChild(alertDiv);
    }
});