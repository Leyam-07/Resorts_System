<?php
// Define a constant to be used as a security check in other files
define('APP_LOADED', true);

// Load application configuration
require_once __DIR__ . '/../config/app.php';
// Phase 6: Initialize enhanced error handler
require_once __DIR__ . '/../app/Helpers/ErrorHandler.php';
ErrorHandler::initialize();

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
if (in_array($actionName, ['login', 'showRegisterForm', 'register', 'showAdminRegisterForm', 'registerAdmin']) && !isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../app/Controllers/UserController.php';
    $userController = new UserController();

    // Specific actions for logged-out users
    switch ($actionName) {
        case 'login':
            $userController->login();
            break;
        case 'showRegisterForm':
            $userController->showRegisterForm();
            break;
        case 'register':
            $userController->register();
            break;
        case 'showAdminRegisterForm':
            $userController->showAdminRegisterForm();
            break;
        case 'registerAdmin':
            $userController->registerAdmin();
            break;
    }
    exit();
}


if ($controllerName === 'dashboard' && $actionName === 'index') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit();
    }

    // Route based on role
    if (in_array($_SESSION['role'], ['Admin', 'Staff'])) {
        require_once __DIR__ . '/../app/Controllers/AdminController.php';
        $adminController = new AdminController();
        $adminController->dashboard();
        exit();
    } else { // Default to customer dashboard
        require_once __DIR__ . '/../app/Controllers/UserController.php';
        $userController = new UserController();
        $userController->dashboard();
        exit();
    }
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

    // Define allowed actions for the booking controller
    $allowedActions = ['showBookingForm', 'createBooking', 'bookingSuccess', 'showMyBookings', 'cancelBooking', 'getFacilitiesByResort'];

    if (in_array($actionName, $allowedActions) && method_exists($bookingController, $actionName)) {
        $bookingController->$actionName();
        exit();
    } else {
        die('Action not found or not allowed.');
    }
} elseif ($controllerName === 'payment') {
   require_once __DIR__ . '/../app/Controllers/PaymentController.php';
   $paymentController = new PaymentController();
   if (method_exists($paymentController, $actionName)) {
       $paymentController->$actionName();
       exit();
   } else {
       die('Action not found.');
   }
} elseif ($controllerName === 'feedback') {
    require_once __DIR__ . '/../app/Controllers/FeedbackController.php';
    $feedbackController = new FeedbackController();
    if (method_exists($feedbackController, $actionName)) {
        $feedbackController->$actionName();
        exit();
    } else {
        die('Action not found.');
    }
}

// All dashboard logic is now handled by controllers.
// The default HTML block below is no longer needed.
?>