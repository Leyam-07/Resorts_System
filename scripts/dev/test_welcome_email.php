<?php
// A dedicated script to test the PHPMailer welcome email function.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../app/Helpers/Notification.php';
require_once __DIR__ . '/../../app/Models/User.php';
require_once __DIR__ . '/../../config/database.php';

echo "<pre>";
echo "Attempting to send welcome email...\n";

// --- IMPORTANT ---
// Please change '1' to the UserID of a valid user in your database.
$testUserID = 1; 

$result = Notification::sendWelcomeEmail($testUserID);

if ($result) {
    echo "\nEmail sent successfully (according to the function).\n";
} else {
    echo "\nEmail sending failed (according to the function).\n";
}

echo "\nScript finished.</pre>";
?>