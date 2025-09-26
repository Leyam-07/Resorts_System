<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Models/Booking.php';
require_once __DIR__ . '/../app/Models/ResortPaymentMethods.php';
require_once __DIR__ . '/../app/Helpers/Database.php';

// Test script to debug payment form visibility issue

echo "=== Payment Form Visibility Debug ===\n\n";

// Get a booking from the database
$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM Bookings LIMIT 1");
$stmt->execute();
$bookingData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bookingData) {
    echo "❌ No bookings found in database\n";
    exit;
}

echo "Test Booking IDs in database:\n";
$bookingIds = $db->query("SELECT BookingID, TotalAmount, RemainingBalance, Status FROM Bookings LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($bookingIds as $bid) {
    echo "- ID: {$bid['BookingID']}, Total: {$bid['TotalAmount']}, Remaining: {$bid['RemainingBalance']}, Status: {$bid['Status']}\n";
}

echo "\n--- Testing payment form visibility logic ---\n";

// Test a booking with remaining balance > 0
$testBooking = Booking::findById($bookingIds[0]['BookingID']);
if ($testBooking) {
    echo "Test Booking 1: ID {$testBooking->bookingId}\n";
    echo "- Total Amount: {$testBooking->totalAmount}\n";
    echo "- Remaining Balance: {$testBooking->remainingBalance}\n";
    echo "- Status: {$testBooking->status}\n";
    echo "- Form should be visible: " . ($testBooking->remainingBalance > 0 ? "✅ YES" : "❌ NO (remaining balance <= 0)") . "\n";

    // Check payment methods for this resort
    $paymentMethods = ResortPaymentMethods::findByResortId($testBooking->resortId, true);
    echo "- Payment methods found: " . (empty($paymentMethods) ? "❌ NONE" : "✅ " . count($paymentMethods) . " methods") . "\n";

    if (!empty($paymentMethods)) {
        foreach ($paymentMethods as $method) {
            echo "  - {$method->MethodName}: {$method->MethodDetails}\n";
        }
    }
}

// Test edge case: check if there are any paid bookings (remaining balance = 0)
$stmt = $db->prepare("SELECT * FROM Bookings WHERE RemainingBalance <= 0 OR RemainingBalance IS NULL LIMIT 5");
$stmt->execute();
$paidBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($paidBookings)) {
    echo "\n--- Paid/Missing Balance Bookings ---\n";
    foreach ($paidBookings as $paid) {
        echo "Booking ID: {$paid['BookingID']}, Total: {$paid['TotalAmount']}, Remaining: {$paid['RemainingBalance']}, Status: {$paid['Status']}\n";
    }
    echo "\n❌ These bookings would NOT show the payment form (remainingBalance <= 0)\n";
}

echo "\n=== Potential Issues Identified ===\n";

$issues = [];

// Check if there are bookings with remaining balance but payment methods missing
$stmt = $db->prepare("
    SELECT b.BookingID, b.RemainingBalance, r.Name as ResortName
    FROM Bookings b
    JOIN Resorts r ON b.ResortID = r.ResortID
    WHERE b.RemainingBalance > 0
    AND NOT EXISTS (SELECT 1 FROM ResortPaymentMethods WHERE ResortID = b.ResortID AND IsActive = 1)
");
$stmt->execute();
$bookingsWithoutMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($bookingsWithoutMethods)) {
    $issues[] = "Found " . count($bookingsWithoutMethods) . " bookings with remaining balance but no active payment methods";
    foreach ($bookingsWithoutMethods as $booking) {
        $issues[] = "  - Booking {$booking['BookingID']} at {$booking['ResortName']} (Balance: {$booking['RemainingBalance']})";
    }
}

if (empty($issues)) {
    $issues[] = "No obvious issues found with remaining balance > 0 and payment methods";
}

foreach ($issues as $issue) {
    echo "- $issue\n";
}

echo "\n=== Testing URL Access Logic ===\n";

// Simulate the My Bookings page logic
echo "In my_bookings.php, 'Submit Payment' button appears when:\n";
echo "- Booking status is NOT 'Completed'\n";
echo "- Remaining balance > 0\n";
echo "- User is logged in and owns the booking\n";

echo "\nIn payment.php, form displays when:\n";
echo "- remainingBalance > 0\n";

echo "\n=== Recommendations ===\n";
echo "1. Check if customers are trying to submit payment for fully paid bookings (remainingBalance = 0)\n";
echo "2. Verify that payment methods are properly configured for resorts\n";
echo "3. Test the exact booking IDs customers are trying to pay for\n";
echo "4. Check if RemainingBalance field is correctly updated after payments\n";

echo "\nDebug complete.\n";
?>
