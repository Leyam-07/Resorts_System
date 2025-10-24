<?php
// This script is designed to be called from the command line to send various emails asynchronously.

// Prevent direct web access
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script can only be run from the command line.');
}

// Bootstrap the application environment
require_once __DIR__ . '/../app/Helpers/Notification.php';
require_once __DIR__ . '/../app/Models/Booking.php';
require_once __DIR__ . '/../app/Models/User.php';

// Configure error logging for this script
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/email_worker.log');

if (!isset($argv[1]) || !isset($argv[2])) {
    error_log("Email worker called with missing arguments. Usage: php send_email_worker.php [email_type] [id]");
    exit(1);
}

$emailType = $argv[1];
$id = filter_var($argv[2], FILTER_VALIDATE_INT);

if (!$id) {
    error_log("Invalid ID provided to email worker: " . $argv[2]);
    exit(1);
}

error_log("Processing email job: Type='{$emailType}', ID='{$id}'");

try {
    switch ($emailType) {
        case 'payment_submission_admin':
            $booking = Booking::findById($id);
            if (!$booking) throw new Exception("Booking not found for ID: {$id}");
            $customer = User::findById($booking->customerId);
            if (!$customer) throw new Exception("Customer not found for Booking ID: {$id}");
            
            Notification::sendPaymentSubmissionNotification($id, $customer);
            break;

        case 'payment_submission_customer':
            Notification::sendPaymentSubmissionConfirmation($id);
            break;

        case 'booking_confirmation':
            Notification::sendBookingConfirmation($id);
            break;

        case 'booking_cancellation':
            $booking = Booking::findById($id);
            if (!$booking) throw new Exception("Booking not found for ID: {$id}");
            Notification::sendBookingCancellation($booking);
            break;

        case 'payment_verified':
            Notification::sendPaymentVerificationConfirmation($id, true);
            break;

        case 'payment_rejected':
            Notification::sendPaymentVerificationConfirmation($id, false);
            break;

        case 'welcome_email':
            Notification::sendWelcomeEmail($id);
            break;

        case 'booking_expired':
            Notification::sendBookingExpiredNotification($id);
            break;

        default:
            throw new Exception("Unknown email type: {$emailType}");
    }

    error_log("Successfully processed email job: Type='{$emailType}', ID='{$id}'");
    exit(0);

} catch (Exception $e) {
    error_log("Error in email worker (Type='{$emailType}', ID='{$id}'): " . $e->getMessage());
    exit(1);
}