document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.querySelector('#sidebarToggle');
    const brandWrapper = document.querySelector('#brandWrapper a'); // whole brand link

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();

            // Toggle sidebar
            document.body.classList.toggle('sb-sidenav-toggled');
            const isToggled = document.body.classList.contains('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', isToggled);

            // Hide/show brand (logo + text)
            if (brandWrapper) {
                brandWrapper.classList.toggle('d-none', isToggled);
            }
        });
    }

    // Restore sidebar state from localStorage
    if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
        document.body.classList.add('sb-sidenav-toggled');
        if (brandWrapper) brandWrapper.classList.add('d-none');
    }

    // Close sidebar when clicking on actual links
    document.querySelectorAll('.sb-sidenav .nav-link').forEach(link => {
        link.addEventListener('click', (e) => {

            const isDropdownToggle = link.hasAttribute('data-bs-toggle');
            const hasRealHref = link.getAttribute('href') && link.getAttribute('href') !== '#';
            
            if (window.innerWidth < 992 && !isDropdownToggle && hasRealHref) {
                document.body.classList.remove('sb-sidenav-toggled');
                localStorage.setItem('sb|sidebar-toggle', 'false');
                if (brandWrapper) brandWrapper.classList.remove('d-none');
            }
        });
    });

    // Bootstrap Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Bootstrap Popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Bootstrap Toasts
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl).show();
    });

    // DataTables initialization
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.table').DataTable({
            responsive: true,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }
});