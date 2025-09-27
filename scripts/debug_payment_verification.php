<?php
/**
 * Debug script for payment verification hanging issue
 */

session_start(); // Required for User context
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Models/Payment.php';
require_once __DIR__ . '/../app/Models/BookingAuditTrail.php';

echo "=== Payment Verification Debug Script ===\n\n";

// Mock admin session
$_SESSION['user_id'] = 1; // Assume admin user ID = 1
$_SESSION['role'] = 'Admin';

// Get a pending payment for testing
echo "Getting pending payments...\n";
$pendingPayments = Payment::getPendingPayments();

if (empty($pendingPayments)) {
    echo "❌ No pending payments found. Please create a payment first.\n";
    exit(1);
}

echo "Found " . count($pendingPayments) . " pending payments\n";
$payment = $pendingPayments[0]; // Use first pending payment

echo "\nTesting payment verification...\n";
$paymentId = $payment->PaymentID;

echo "Payment ID: $paymentId\n";
echo "Booking ID: {$payment->BookingID}\n";
echo "Amount: ₱{$payment->Amount}\n";
echo "Start Time: " . date('H:i:s') . "\n";

set_time_limit(300); // 5 minutes for testing
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test the payment verification
try {
    echo "Starting payment verification...\n";

    $startTime = microtime(true);
    $result = Payment::verifyPayment($paymentId, $_SESSION['user_id']);
    $endTime = microtime(true);

    echo "End Time: " . date('H:i:s') . "\n";
    echo "Execution time: " . round(($endTime - $startTime), 2) . " seconds\n";
    echo "Result: " . json_encode($result) . "\n";

    if ($result['success']) {
        echo "✅ Payment verified successfully!\n";
        echo "New status: {$result['newStatus']}\n";
        echo "New balance: ₱{$result['newBalance']}\n";
    } else {
        echo "❌ Payment verification failed: {$result['error']}\n";
    }

} catch (Exception $e) {
    echo "❌ Exception thrown: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";

    // Print stack trace
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== Verification Complete ===\n";
?>
