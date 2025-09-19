<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Resort Management' ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap JS - Load in head to ensure dropdowns work -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<?php if (isset($_SESSION['user_id'])): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="?">
            <i class="fas fa-swimming-pool"></i> Resort Management
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] === 'Admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?controller=admin&action=dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="bookingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-calendar-check"></i> Booking & Payments
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="bookingDropdown">
                                <li><a class="dropdown-item" href="?controller=admin&action=unifiedBookingManagement"><i class="fas fa-calendar-check"></i> Unified Management</a></li>
                                <li><a class="dropdown-item" href="?controller=payment&action=showPendingPayments"><i class="fas fa-credit-card"></i> Payment Verification</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="pricingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-tags"></i> Pricing & Blocking
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="pricingDropdown">
                                <li><a class="dropdown-item" href="?controller=admin&action=pricingManagement"><i class="fas fa-tags"></i> Pricing Management</a></li>
                                <li><a class="dropdown-item" href="?controller=admin&action=advancedBlocking"><i class="fas fa-ban"></i> Advanced Blocking</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="systemDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog"></i> System
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="systemDropdown">
                                <li><a class="dropdown-item" href="?controller=admin&action=users"><i class="fas fa-users"></i> Manage Users</a></li>
                                <li><a class="dropdown-item" href="?controller=admin&action=management"><i class="fas fa-building"></i> Resort Management</a></li>
                                <li><a class="dropdown-item" href="?controller=feedback&action=listAllFeedback"><i class="fas fa-comments"></i> View Feedback</a></li>
                                <li><a class="dropdown-item" href="?controller=admin&action=previewFacilities"><i class="fas fa-eye"></i> Preview Customer View</a></li>
                            </ul>
                        </li>
                   <?php elseif ($_SESSION['role'] === 'Staff'): ?>
                       <li class="nav-item">
                           <a class="nav-link" href="?controller=admin&action=dashboard">
                               <i class="fas fa-tachometer-alt"></i> Dashboard
                           </a>
                       </li>
                       <li class="nav-item">
                           <a class="nav-link" href="?controller=admin&action=previewFacilities">
                               <i class="fas fa-eye"></i> Preview Customer View
                           </a>
                       </li>
                    <?php elseif ($_SESSION['role'] === 'Customer'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?controller=booking&action=showBookingForm">
                                <i class="fas fa-plus-circle"></i> New Booking
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?controller=booking&action=showMyBookings">
                                <i class="fas fa-calendar-check"></i> My Bookings
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="?controller=user&action=profile">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?controller=user&action=logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- Initialize Bootstrap dropdowns -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dropdown toggles
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    // Add click event listeners to dropdown toggles as backup
    document.querySelectorAll('.dropdown-toggle').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            var dropdown = bootstrap.Dropdown.getInstance(element) || new bootstrap.Dropdown(element);
            dropdown.toggle();
        });
    });
});
</script>

<div class="container mt-4">