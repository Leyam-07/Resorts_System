<?php
/**
 * Test script to verify payment verification audit trail consolidation
 */
require_once __DIR__ . '/../../app/Helpers/Database.php';
require_once __DIR__ . '/../../app/Models/BookingAuditTrail.php';
require_once __DIR__ . '/../../app/Models/Payment.php';
require_once __DIR__ . '/../../config/database.php';

echo "Testing Payment Verification Audit Trail Consolidation\n";
echo "====================================================\n\n";

// Simulate admin session
$_SESSION['user_id'] = 1; // Assuming admin ID is 1

// Use the booking_id that has the test payment from the previous script
$testBookingId = 1; // Same booking ID as before
$testPaymentId = 10; // Payment ID from the previous test

// Get audit trail count before verification
$auditTrailBefore = BookingAuditTrail::getBookingAuditTrail($testBookingId);
echo "Audit trail entries before payment verification: " . count($auditTrailBefore) . "\n\n";

// Get payment info for verification
$payment = Payment::findById($testPaymentId);

if ($payment && $payment->status === 'Pending') {
    echo "Verifying payment ID: $testPaymentId for booking ID: $testBookingId\n";
    echo "Payment amount: ₱" . number_format($payment->amount, 2) . "\n";
    echo "Current payment status: {$payment->status}\n\n";

    // Verify the payment
    $result = Payment::verifyPayment($testPaymentId, 1); // Admin ID = 1

    if ($result['success']) {
        echo "✅ Payment verification successful!\n";
        echo "New booking status: {$result['newStatus']}\n";
        echo "New balance: ₱" . number_format($result['newBalance'], 2) . "\n\n";

        // Get audit trail count after verification
        $auditTrailAfter = BookingAuditTrail::getBookingAuditTrail($testBookingId);
        $newEntriesCount = count($auditTrailAfter) - count($auditTrailBefore);

        echo "Audit trail entries after payment verification: " . count($auditTrailAfter) . "\n";
        echo "New audit trail entries created: $newEntriesCount\n\n";

        // Show the latest entries to verify consolidation
        $latestEntries = array_slice($auditTrailAfter, -5); // Get last 5 entries
        echo "Latest audit trail entries:\n";

        foreach ($latestEntries as $entry) {
            $changeDescription = BookingAuditTrail::getChangeDescription($entry);
            echo "----------------------------------------\n";
            echo "Action: {$entry->Action}\n";
            echo "Field: {$entry->FieldName}\n";
            echo "Old Value: {$entry->OldValue}\n";
            echo "New Value: {$entry->NewValue}\n";
            echo "Description: $changeDescription\n";
            echo "Time: {$entry->CreatedAt} by {$entry->Username} ({$entry->Role})\n";
            if ($entry->ChangeReason) {
                echo "Reason: {$entry->ChangeReason}\n";
            }
            echo "\n";
        }

        // Verify consolidation - should be only 1 PAYMENT_UPDATE for verification
        $verificationEntries = array_filter($auditTrailAfter, function($entry) {
            return $entry->Action === 'PAYMENT_UPDATE' &&
                   strpos($entry->ChangeReason, 'Admin verified payment') !== false;
        });

        echo "Payment verification PAYMENT_UPDATE entries found: " . count($verificationEntries) . "\n";

        if (count($verificationEntries) === 1 && $newEntriesCount === 1) {
            echo "✅ SUCCESS: Payment verification audit trail consolidated into single entry!\n";
            echo "The entry includes all verification details, status changes, and balance updates.\n";
        } elseif (count($verificationEntries) > 1) {
            echo "❌ FAILURE: Multiple verification entries still being created\n";
        } else {
            echo "ℹ️  INFO: Testing may need adjustment - verification may have other audit entries\n";
        }

    } else {
        echo "❌ Payment verification failed: " . ($result['error'] ?? 'Unknown error') . "\n";
    }

} else {
    echo "❌ Payment not found or not pending verification\n";
    if ($payment) {
        echo "Current status: {$payment->status}\n";
        echo "Expected status: Pending\n";
    }
}

echo "\nTest completed.\n";
