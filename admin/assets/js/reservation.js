// Service Modal Event Handlers
document.addEventListener('DOMContentLoaded', function() {
    // View Service Modal
    $('#viewServiceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var reservationId = button.data('id');
        
        // Reset all conditional sections
        $('#scheduled-datetime-section').hide();
        $('#processed-by-section').hide();
        $('#rejection-reason-section').hide();
        
        // Show loading state
        $('#view-reservation-id, #view-resident-name, #view-service-type, #view-reservation-date, #view-duration, #view-status, #view-purpose, #view-contact, #view-email, #view-date-requested, #view-notes').text('Loading...');
        
        // Fetch reservation details via AJAX
        $.ajax({
            url: 'reservation-backend.php?action=get&id=' + reservationId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    var reservation = data.reservation;
                    
                    // Basic details
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
                    $('#view-notes').text(reservation.notes || 'No additional notes');
                    
                    // Show/hide conditional fields based on data availability
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

    // Approve Service Modal
    $('#approveServiceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var reservationId = button.data('id');
        
        // Reset form
        $('#approve-service-id').val(reservationId);
        $('#approve-notes').val('');
        
        // Set minimum datetime to current time
        var now = new Date();
        // Adjust for timezone offset to get local time
        var localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
        $('#approve-schedule').attr('min', localDateTime.toISOString().slice(0, 16));
        $('#approve-schedule').val('');
    });

    // Reject Service Modal  
    $('#rejectServiceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var reservationId = button.data('id');
        
        // Reset form
        $('#reject-service-id').val(reservationId);
        $('#reject-reason').val('');
    });

    // Update Service Modal
    $('#updateServiceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var reservationId = button.data('id');
        
        // Reset form
        $('#update-service-id').val(reservationId);
        $('#update-status').val('');
        $('#update-notes').val('');
    });

    // Form Submissions with improved error handling
    $('#approveServiceForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate required fields
        var scheduledDateTime = $('#approve-schedule').val();
        if (!scheduledDateTime) {
            alert('Please select a scheduled date and time.');
            return;
        }
        
        // Disable submit button to prevent double submission
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        var formData = $(this).serialize() + '&action=approve';
        
        $.ajax({
            url: 'reservation-backend.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 10000, // 10 second timeout
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
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#rejectServiceForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate required fields
        var rejectionReason = $('#reject-reason').val().trim();
        if (!rejectionReason) {
            alert('Please provide a reason for rejection.');
            return;
        }
        
        // Disable submit button
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        var formData = $(this).serialize() + '&action=reject';
        
        $.ajax({
            url: 'reservation-backend.php',
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
        
        // Validate required fields
        var status = $('#update-status').val();
        if (!status) {
            alert('Please select a status.');
            return;
        }
        
        // Disable submit button
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        var formData = $(this).serialize() + '&action=update_status';
        
        $.ajax({
            url: 'reservation-backend.php',
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

    // Clear form data when modals are hidden
    $('#approveServiceModal, #rejectServiceModal, #updateServiceModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
});