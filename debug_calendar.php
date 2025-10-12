<?php
// Test calendar API directly
require_once 'config/database.php';
require_once 'app/Controllers/BookingController.php';

$_GET['resort_id'] = '1';
$_GET['timeframe'] = '24_hours';
$_GET['month'] = date('Y-m');

$controller = new BookingController();
$controller->getCalendarAvailability();
?>
