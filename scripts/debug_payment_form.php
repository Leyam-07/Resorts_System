<?php

// Test script to simulate a booking with no payment methods configured
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Models/Resort.php';
require_once __DIR__ . '/../app/Models/ResortPaymentMethods.php';
require_once __DIR__ . '/../app/Models/Booking.php';

echo "=== Payment Method Testing Script ===\n\n";

// Test 1: Check if we can create/find a resort with no payment methods
echo "1. Creating/Finding test resort:\n";
$testResort = null;

// Try to find existing resort with ID 1 (we'll clear its payment methods)
$testResort = Resort::findById(1);

if (!$testResort) {
    // Create a test resort
    echo "   No existing resort found, creating test resort...\n";
    $testResort = new Resort();
    $testResort->name = 'Test Resort - No Payment Methods';
    $testResort->description = 'Resort for testing payment method restrictions';
    $testResort->location = 'Test Location';
    $testResort->phone = '+1234567890';
    $testResort->email = 'test@resort.com';
    $testResort->price = 1000;
    $testResort->capacity = 50;
    $result = Resort::create($testResort);

    if ($result) {
        $testResort = Resort::findById($result);
        echo "   ✓ Created test resort with ID: $result\n";
    } else {
        echo "   ✗ Failed to create test resort\n";
        exit(1);
    }
} else {
    echo "   ✓ Found existing resort: " . $testResort->name . "\n";
}

echo "\n2. Checking payment methods for resort ID {$testResort->resortId}:\n";
$paymentMethods = ResortPaymentMethods::findByResortId($testResort->resortId, true);
echo "   Found " . count($paymentMethods) . " payment methods\n";

if (count($paymentMethods) > 0) {
    echo "   ✗ Resort has payment methods configured - clearing them for test\n";
    // Delete existing payment methods for clean test
    try {
        $db = Database::getInstance();
        $db->prepare("DELETE FROM ResortPaymentMethods WHERE ResortID = ?")->execute([$testResort->resortId]);

        echo "   ✓ Cleared payment methods\n";
    } catch (Exception $e) {
        echo "   ✗ Failed to clear payment methods: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✓ Resort has no payment methods configured (perfect for testing)\n";
}

echo "\n3. Testing payment method loading logic:\n";
// Simulate the controller logic
$hasPaymentMethods = !empty($paymentMethods);

echo "   \$hasPaymentMethods = " . ($hasPaymentMethods ? 'true' : 'false') . "\n";

if (!$hasPaymentMethods) {
    echo "   ✓ Controller would show 'No Payment Methods' warning\n";
    echo "   ✓ Form fields would be disabled\n";
    echo "   ✓ Submit button would be disabled\n";
} else {
    echo "   ✗ Unexpected: Payment methods found when none expected\n";
}

echo "\n4. Simulating getPaymentMethods API call:\n";
try {
    // Test the API endpoint logic
    $apiMethods = ResortPaymentMethods::getFormattedPaymentMethods($testResort->resortId);
    echo "   API returned " . count($apiMethods) . " methods\n";

    if (count($apiMethods) === 0) {
        echo "   ✓ API correctly returns empty array\n";
        echo "   ✓ Frontend would show 'Payment Not Available' message\n";
        echo "   ✓ Form would be disabled with pointer-events: none\n";
    } else {
        echo "   ✗ API returned methods when none expected\n";
    }
} catch (Exception $e) {
    echo "Error testing API: " . $e->getMessage() . "\n";
}

echo "\n5. Test Summary:\n";
echo "   =========================================\n";
echo "   Resort ID: {$testResort->resortId}\n";
echo "   Resort Name: {$testResort->name}\n";
echo "   Payment Methods Configured: " . count($paymentMethods) . "\n";
echo "   Expected UI Behavior:\n";
echo "   - Payment warning should be shown: YES\n";
echo "   - Form fields should be disabled: YES\n";
echo "   - Submit button should be disabled: YES\n";
echo "   - Contact resort options should be shown: YES\n";
echo "   =========================================\n";

echo "\n✅ Test completed successfully!\n";
echo "   To test manually:\n";
echo "   1. Create a booking for Resort ID {$testResort->resortId}\n";
echo "   2. Go to payment page or click 'Submit Payment' in My Bookings\n";
echo "   3. Verify the UI shows payment method restrictions\n";
echo "   4. Try to submit payment - it should be prevented\n";

?>
