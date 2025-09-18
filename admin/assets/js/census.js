    //modaal admin census
    
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function() {
                const householdData = JSON.parse(this.getAttribute('data-household'));
                
                document.getElementById('modal-household-number').textContent = 'HH-' + householdData.purok + '-' + String(householdData.id).padStart(4, '0');
                document.getElementById('modal-purok').textContent = 'Purok ' + householdData.purok;
                document.getElementById('modal-address').textContent = householdData.address;
                document.getElementById('modal-water-source').textContent = householdData.type_of_water_source || 'Not specified';
                document.getElementById('modal-toilet-facility').textContent = householdData.type_of_toilet_facility || 'Not specified';
                
                fetch('census-backend.php?house_number=' + householdData.house_number + '&purok=' + householdData.purok)
                    .then(response => response.json())
                    .then(data => {
                        const membersList = document.getElementById('modal-members-list');
                        membersList.innerHTML = '';
                        
                        if (data.success && data.members.length > 0) {
                            data.members.forEach(member => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${member.first_name} ${member.middle_name || ''} ${member.last_name}</td>
                                    <td>${member.relationship_to_head || 'Not specified'}</td>
                                    <td>${member.age}</td>
                                    <td>${member.sex}</td>
                                    <td>${member.civil_status || 'Not specified'}</td>
                                    <td>${member.occupation || 'Not specified'}</td>
                                    <td>${member.educational_attainment || 'Not specified'}</td>
                                    <td>${member.philhealth_number ? 'Yes' : 'No'}</td>
                                `;
                                membersList.appendChild(row);
                            });
                        } else {
                            membersList.innerHTML = '<tr><td colspan="8" class="text-center">No members found</td></tr>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching household members:', error);
                        document.getElementById('modal-members-list').innerHTML = '<tr><td colspan="8" class="text-center">Error loading members</td></tr>';
                    });
                
                var modal = new bootstrap.Modal(document.getElementById('householdModal'));
                modal.show();
            });
        });
        
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                var householdId = this.getAttribute('data-id');
                window.location.href = 'edit_household.php?id=' + householdId;
            });
        });
        
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                var householdId = this.getAttribute('data-id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // AJAX request to delete household using the backend
                        fetch('census-backend.php?id=' + householdId, {
                            method: 'DELETE'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'Household record has been deleted.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'Failed to delete household record: ' + data.message,
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                'An error occurred while deleting the record.',
                                'error'
                            );
                        });
                    }
                });
            });
        });
        
        document.getElementById('exportExcelBtn').addEventListener('click', function() {
            const params = new URLSearchParams({
                export: 'excel',
                purok: '<?php echo $purok_filter; ?>',
                water_source: '<?php echo $water_filter; ?>',
                toilet_facility: '<?php echo $toilet_filter; ?>',
                status: '<?php echo $status_filter; ?>',
                search: '<?php echo $search; ?>'
            });
            
            Swal.fire({
                title: 'Export to Excel',
                text: 'Preparing data for export...',
                icon: 'info',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'export_census.php?' + params.toString();
            });
        });
    });
    
    function updateLimit(limit) {
        const url = new URL(window.location.href);
        url.searchParams.set('limit', limit);
        url.searchParams.set('page', 1); 
        window.location.href = url.toString();
    }
