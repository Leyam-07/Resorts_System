<?php
session_start();

// If not logged in and no specific action, show login page
if (!isset($_SESSION['user_id']) && !isset($_GET['action'])) {
    require_once __DIR__ . '/../app/Controllers/UserController.php';
    $userController = new UserController();
    $userController->login();
    exit();
}

// If logged in, get user data from session
if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];
    $role = $_SESSION['role'];
}

// Basic router to handle different pages based on role
$controllerName = isset($_GET['controller']) ? $_GET['controller'] : 'dashboard';
$actionName = isset($_GET['action']) ? $_GET['action'] : 'index';

// Allow login action even if not logged in
if ($actionName === 'login' && !isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../app/Controllers/UserController.php';
    $userController = new UserController();
    $userController->login();
    exit();
}


if ($controllerName === 'dashboard' && $actionName === 'index') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit();
    }
   // The rest of the file will act as the default dashboard view
} elseif ($controllerName === 'admin') {
   require_once __DIR__ . '/../app/Controllers/AdminController.php';
   $adminController = new AdminController();
   if (method_exists($adminController, $actionName)) {
       $adminController->$actionName();
       exit(); // Stop further execution
   } else {
       die('Action not found.');
   }
} elseif ($controllerName === 'user') {
    require_once __DIR__ . '/../app/Controllers/UserController.php';
    $userController = new UserController();
    if (method_exists($userController, $actionName)) {
        $userController->$actionName();
        exit();
    } else {
        die('Action not found.');
    }
} elseif ($controllerName === 'booking') {
    require_once __DIR__ . '/../app/Controllers/BookingController.php';
    $bookingController = new BookingController();
    if (method_exists($bookingController, $actionName)) {
        $bookingController->$actionName();
        exit();
    } else {
        die('Action not found.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Resort Management</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="?controller=user&action=profile">My Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?controller=user&action=logout">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Welcome, <?php echo htmlspecialchars($username); ?>!</h3>
                </div>
                <div class="card-body">
                    <?php if ($role === 'Admin'): ?>
                        <h5 class="card-title">Admin Dashboard</h5>
                        <p class="card-text">You have full access to the system.</p>
                        <a href="?controller=admin&action=users" class="btn btn-primary">Manage Users</a>
                    <?php elseif ($role === 'Staff'): ?>
                        <h5 class="card-title">Staff Dashboard</h5>
                        <p class="card-text">You can view daily tasks and schedules.</p>
                        <!-- Staff-specific content goes here -->
                    <?php else: ?>
                        <h5 class="card-title">Customer Dashboard</h5>
                        <p class="card-text">Welcome to your personal dashboard.</p>
                        <a href="?controller=booking&action=showBookingForm" class="btn btn-primary">Book a Facility</a>
                        <!-- Customer-specific content goes here -->
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>