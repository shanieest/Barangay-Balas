<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Balas</title>
  <!-- Bootstrap 4 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    .top-navbar {
      background-color: #003366;
    }
    .top-navbar .navbar-brand {
      color: white;
    }
    .main-navbar {
      background-color: white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .dropdown-menu {
      border: none;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    /* Desktop styles */
    @media (min-width: 992px) {
      .dropdown-submenu {
        position: relative;
      }
      .dropdown-submenu .dropdown-menu {
        top: 0;
        left: 100%;
        margin-top: -6px;
        margin-left: 0;
      }
      .dropdown-submenu:hover > .dropdown-menu {
        display: block;
      }
    }
    
    /* Mobile styles */
    @media (max-width: 991.98px) {
      .dropdown-submenu .dropdown-menu {
        margin-left: 15px;
      }
    }
    
    /* Custom burger icon color */
    .navbar-toggler {
      border-color: #003366;
    }
    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='30' height='30' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(0, 51, 102, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
  </style>
</head>
<body>
  <!-- Top Blue Navbar -->
  <nav class="navbar top-navbar py">
  <div class="container d-flex justify-content-between align-items-center">
    <span class="navbar-brand mb-0 font-weight-bold"></span>
    <div class="d-flex align-items-center">
      <span class="mr-2 navbar-brand mb">Don't have an account?</span>
      <a class="btn btn-primary btn-sm" href="\balas-2.0\signup.php">Sign up</a>
    </div>
  </div>
</nav>


  <!-- Main White Navbar -->
  <nav class="navbar navbar-expand-lg main-navbar sticky-top py-2">
  <div class="container d-flex justify-content-between align-items-center">

    <!-- Left Side: Barangay Balas Logo + Text -->
    <a class="navbar-brand d-flex align-items-center" href="/balas 2.0/index.php">
      <img src="\balas-2.0\assets\img\pampangalogo.png" alt="Pampanga Logo" width="80" height="80" class="d-inline-block align-top mr-2">
      <img src="\balas-2.0\assets\img\Mexico_Pampanga.png" alt="Mexico Logo" width="80" height="80" class="d-inline-block align-top mr-2">
      <img src="\balas-2.0\assets\img\balas-logo.png" alt="Balas Logo" width="80" height="80" class="d-inline-block align-top mr-2">
      <span class="font-weight-bold">Barangay Balas<br><small>Mexico, Pampanga</small></span>
    </a>

    <!-- Navbar Toggler (Mobile View) -->
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNavbar"
      aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Center Menu Items -->
    <div class="collapse navbar-collapse justify-content-center" id="mainNavbar">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="/balas 2.0/index.php">Home</a>
        </li>
        <!-- Services Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-toggle="dropdown" aria-expanded="false">
            Services
          </a>
          <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
            <li class="dropdown-submenu">
              <a class="dropdown-item dropdown-toggle" href="#" role="button">Barangay Certifications</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="\balas-2.0\services\barangay-certifications\residency.php">Certificate of Residency</a></li>
                <li><a class="dropdown-item" href="\balas-2.0\services\barangay-certifications\indigency.php">Certificate of Indigency</a></li>
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="dropdown-item dropdown-toggle" href="#" role="button">Barangay Clearance</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="\balas-2.0\services\barangay-clearance\businessClearance.php">Business Clearance</a></li>
                <li><a class="dropdown-item" href="\balas-2.0\services\barangay-clearance\employmentClearance.php">Employment Clearance</a></li>
              </ul>
            </li>
            <li class="dropdown-submenu">
              <a class="dropdown-item dropdown-toggle" href="#" role="button">Other Services</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="\barangay-balas\services\other-services\reservation.php">Reservation Services</a></li>
              </ul>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/barangay-balas/officials.php">Barangay Officials</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#jobs">Jobs</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/barangay-balas/announcements.php">News & Events</a>
        </li>
      </ul>
    </div>

  </div>
  </nav>


  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
  
  <!-- Mobile Submenu Handling -->
  <script>
    $(document).ready(function() {
      // Handle submenu toggle on mobile
      $('.dropdown-submenu > a').on('click', function(e) {
        if ($(window).width() < 992) { // Mobile only
          e.preventDefault();
          e.stopPropagation();
          
          var submenu = $(this).next('.dropdown-menu');
          var isOpen = submenu.hasClass('show');
          
          // Close all other submenus
          $('.dropdown-submenu .dropdown-menu').not(submenu).removeClass('show');
          
          // Toggle current submenu
          submenu.toggleClass('show');
        }
      });
      
      // Close all menus when clicking outside
      $(document).on('click', function(e) {
        if (!$(e.target).closest('.navbar-nav').length) {
          $('.dropdown-menu').removeClass('show');
        }
      });
    });
  </script>
</body>
</html>