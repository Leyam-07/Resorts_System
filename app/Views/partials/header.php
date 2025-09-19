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
    <!-- Bootstrap JS -->
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
                        <li class="nav-item">
                            <a class="nav-link" href="?controller=admin&action=users">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?controller=admin&action=management">
                                <i class="fas fa-building"></i> Manage Resorts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?controller=feedback&action=listAllFeedback">
                                <i class="fas fa-comments"></i> View Feedback
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?controller=admin&action=previewFacilities">
                                <i class="fas fa-eye"></i> Preview Customer View
                            </a>
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

<div class="container mt-4">