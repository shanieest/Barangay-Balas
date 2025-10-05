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

    if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
        document.body.classList.add('sb-sidenav-toggled');
        if (brandWrapper) brandWrapper.classList.add('d-none');
    }

    // Close sidebar when a nav link is clicked (mobile only)
    document.querySelectorAll('.sb-sidenav .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                document.body.classList.remove('sb-sidenav-toggled');
                localStorage.setItem('sb|sidebar-toggle', false);
                if (brandWrapper) brandWrapper.classList.remove('d-none');
            }
        });
    });

    // Bootstrap components
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl).show();
    });

    // DataTables
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.table').DataTable({
            responsive: true
        });
    }
});
