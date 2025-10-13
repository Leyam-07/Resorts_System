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
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="<?= BASE_URL ?>/assets/css/fontawesome.min.css" rel="stylesheet">
    <!-- Bootstrap JS Bundle - Load in head for navbar dropdowns -->
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
</head>
<body>

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
                        <i class="fas fa-clock"></i> My Reservations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?controller=booking&action=showMyBookings">
                        <i class="fas fa-calendar-check"></i> My Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?controller=user&action=profile">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?action=login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?action=showRegisterForm">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">