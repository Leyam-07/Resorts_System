<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../app/Models/Booking.php';
require_once __DIR__ . '/../../../app/Models/Payment.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Resort Management' ?></title>
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="<?= BASE_URL ?>/assets/css/fontawesome.min.css" rel="stylesheet">
    <!-- Bootstrap JS Bundle - Load in head for navbar dropdowns -->
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js - Added for dashboard graphs -->
    <script src="<?= BASE_URL ?>/assets/js/chart.min.js"></script>


    <style>
        .booking-count-badge {
            font-size: 12px !important;
            line-height: 1;
            padding: 0.3em 0.6em;
        }
    </style>
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
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
                        <?php
                        // Calculate total counts across all resorts for navigation badges
                        $totalPendingPayments = Payment::getPendingPaymentCount(); // No resort filter = all resorts
                        $totalActiveBookings = Booking::getActiveBookingsCountForAdmin(); // No resort filter = all resorts
                        ?>
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
                                <li><a class="dropdown-item" href="?controller=admin&action=unifiedBookingManagement"><i class="fas fa-calendar-check"></i> Unified Management<?php if ($totalActiveBookings > 0): ?><span class="badge booking-count-badge bg-info text-white fw-semibold ms-1"><?php echo $totalActiveBookings; ?></span><?php endif; ?></a></li>
                                <li><a class="dropdown-item" href="?controller=payment&action=showPendingPayments"><i class="fas fa-credit-card"></i> Payment Verification<?php if ($totalPendingPayments > 0): ?><span class="badge booking-count-badge bg-danger text-white fw-semibold ms-1"><?php echo $totalPendingPayments; ?></span><?php endif; ?></a></li>
                                <li><a class="dropdown-item" href="?controller=admin&action=showOnSiteBookingForm"><i class="fas fa-store"></i> On-Site Booking</a></li>
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
                                <li><a class="dropdown-item" href="?controller=admin&action=emailTemplates"><i class="fas fa-envelope"></i> Email Templates</a></li>
                                <li><a class="dropdown-item" href="?controller=feedback&action=listAllFeedback"><i class="fas fa-comments"></i> View Feedback</a></li>
                                <li><a class="dropdown-item" href="?controller=admin&action=incomeAnalytics"><i class="fas fa-chart-line"></i> Income Analytics</a></li>
                                <li><a class="dropdown-item" href="?controller=admin&action=operationalReports"><i class="fas fa-file-alt"></i> Operational Reports</a></li>
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
                           <a class="nav-link" href="?controller=feedback&action=listAllFeedback">
                               <i class="fas fa-comments"></i> View Feedback
                           </a>
                       </li>
                    <?php elseif ($_SESSION['role'] === 'Customer'): ?>
                        <?php 
                            $confirmedBookingCount = Booking::getConfirmedBookingsCount($_SESSION['user_id']);
                            $pendingBookingCount = Booking::getPendingBookingsCount($_SESSION['user_id']);
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?controller=booking&action=showBookingForm">
                                <i class="fas fa-plus-circle"></i> New Reservation
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?controller=booking&action=showMyReservations">
                                <i class="fas fa-clock"></i> My Reservations<?php if ($pendingBookingCount > 0): ?><span class="badge booking-count-badge bg-warning text-dark fw-semibold ms-1"><?php echo $pendingBookingCount; ?></span><?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?controller=booking&action=showMyBookings">
                                <i class="fas fa-calendar-check"></i> My Bookings<?php if ($confirmedBookingCount > 0): ?><span class="badge booking-count-badge bg-success text-white fw-semibold ms-1"><?php echo $confirmedBookingCount; ?></span><?php endif; ?>
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
