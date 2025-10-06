<?php
/**
 * Test script to verify payment audit trail consolidation
 * This script simulates a payment submission and checks that only one audit trail entry is created
 */
require_once __DIR__ . '/../../app/Helpers/Database.php';
require_once __DIR__ . '/../../app/Models/BookingAuditTrail.php';
require_once __DIR__ . '/../../app/Models/Payment.php';
require_once __DIR__ . '/../../config/database.php';

echo "Testing Payment Audit Trail Consolidation\n";
echo "=========================================\n\n";

// Simulate customer session
$_SESSION['user_id'] = 2; // Assuming customer ID 2 exists

// Create a test booking ID (replace with actual existing booking in your DB)
$testBookingId = 1; // Replace with actual booking ID from your database

// Get audit trail count before payment submission
$auditTrailBefore = BookingAuditTrail::getBookingAuditTrail($testBookingId);
echo "Audit trail entries before payment submission: " . count($auditTrailBefore) . "\n\n";

// Simulate payment submission data
$paymentData = [
    'bookingId' => $testBookingId,
    'amount' => 8500.00,
    'paymentMethod' => 'GCash',
    'paymentReference' => 'TEST4525454774',
    'paymentProofURL' => 'test_payment_proof.jpg'
];

echo "Simulating payment submission...\n";
echo "- Amount: ₱" . number_format($paymentData['amount'], 2) . "\n";
echo "- Method: " . $paymentData['paymentMethod'] . "\n";
echo "- Reference: " . $paymentData['paymentReference'] . "\n\n";

// Create payment record (this would normally be done through the controller)
$result = Payment::createFromBookingPayment(
    $paymentData['bookingId'],
    $paymentData['amount'],
    $paymentData['paymentMethod'],
    $paymentData['paymentReference'],
    $paymentData['paymentProofURL']
);

if ($result['success']) {
    echo "✅ Payment record created successfully (Payment ID: {$result['paymentId']})\n\n";

    // Get audit trail count after payment submission
    $auditTrailAfter = BookingAuditTrail::getBookingAuditTrail($testBookingId);
    $newEntriesCount = count($auditTrailAfter) - count($auditTrailBefore);

    echo "Audit trail entries after payment submission: " . count($auditTrailAfter) . "\n";
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

    // Verify consolidation
    $paymentRelatedEntries = array_filter($auditTrailAfter, function($entry) {
        return $entry->Action === 'PAYMENT_UPDATE';
    });

    $consolidatedEntries = array_filter($paymentRelatedEntries, function($entry) {
        return $entry->FieldName === 'Payment' || $entry->FieldName === 'PaymentSubmitted' || $entry->FieldName === 'PaymentSubmission';
    });

    echo "PAYMENT_UPDATE entries found: " . count($consolidatedEntries) . "\n";

    if (count($consolidatedEntries) === 1 && $newEntriesCount === 1) {
        echo "✅ SUCCESS: Payment submission consolidated into single audit trail entry!\n";
    } elseif (count($consolidatedEntries) > 1) {
        echo "❌ FAILURE: Multiple PAYMENT_UPDATE entries still being created\n";
    } else {
        echo "ℹ️  INFO: Testing may need adjustment - payment submission may have other audit entries\n";
    }

} else {
    echo "❌ Payment creation failed: " . implode(', ', $result['errors']) . "\n";
}

echo "\nTest completed.\n";
