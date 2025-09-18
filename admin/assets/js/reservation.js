document.addEventListener('DOMContentLoaded', function() {
    $('#viewServiceModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var reservationId = button.data('id');
        
        $('#scheduled-datetime-section').hide();
        $('#processed-by-section').hide();
        $('#rejection-reason-section').hide();
        
        $('#view-reservation-id, #view-resident-name, #view-service-type, #view-reservation-date, #view-duration, #view-status, #view-purpose, #view-contact, #view-email, #view-date-requested, #view-notes').text('Loading...');
        
        $.ajax({
            url: 'reservation-backend.php?action=get&id=' + reservationId,
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
                    $('#view-notes').text(reservation.notes || 'No additional notes');
                    
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
        
        var now = new Date();
        var localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
        $('#approve-schedule').attr('min', localDateTime.toISOString().slice(0, 16));
        $('#approve-schedule').val('');
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
            url: 'reservation-backend.php',
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

    $('#approveServiceModal, #rejectServiceModal, #updateServiceModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
});