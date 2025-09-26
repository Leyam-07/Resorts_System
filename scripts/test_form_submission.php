<?php

// Test script to simulate form submission for payment method creation
session_start();

// Simulate logged in admin
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'Admin';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Controllers/AdminController.php';

echo 'Testing form submission...' . PHP_EOL;

// Simulate POST data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['resort_id'] = '1';
$_POST['method_name'] = 'GCash';
$_POST['method_details'] = 'Send to 09123456789';

// Create controller and call the method
$adminController = new AdminController();
$adminController->addPaymentMethod();

echo 'Form submission test completed.' . PHP_EOL;
?>
