<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Models/ResortPaymentMethods.php';

// Start session for testing
session_start();

// Simulate a form submission
echo "=== Testing Payment Method Form Submission ===\n\n";

// Test 1: Simulate POST data exactly as the form sends
echo "Test 1: Simulating form submission...\n";
$_POST = [
    'resort_id' => '1',
    'method_name' => 'Test Payment Method',
    'method_details' => 'Test details for debugging'
];
$_SERVER['REQUEST_METHOD'] = 'POST';

echo "POST data: " . print_r($_POST, true) . "\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n\n";

// Test 2: Check direct access to $_POST vs filter_input
echo "Test 2: Testing filter_input vs direct access...\n";
echo "Direct \$__POST access:\n";
echo "- resort_id: " . ($_POST['resort_id'] ?? 'missing') . "\n";
echo "- method_name: " . ($_POST['method_name'] ?? 'missing') . "\n";
echo "- method_details: " . ($_POST['method_details'] ?? 'missing') . "\n\n";

echo "filter_input access:\n";
try {
    // Simulate the controller logic
    $resortId = filter_input(INPUT_POST, 'resort_id', FILTER_VALIDATE_INT);
    $methodName = filter_input(INPUT_POST, 'method_name', FILTER_UNSAFE_RAW);
    $methodDetails = filter_input(INPUT_POST, 'method_details', FILTER_UNSAFE_RAW);

    echo "Filtered data:\n";
    echo "- resort_id: $resortId\n";
    echo "- method_name: $methodName\n";
    echo "- method_details: $methodDetails\n\n";

    if (!$resortId || !$methodName || !$methodDetails) {
        echo "❌ Validation failed: All fields are required.\n";
        echo "Setting session error message...\n";
        $_SESSION['error_message'] = "All fields are required.";
    } else {
        echo "✅ Validation passed.\n";
        echo "Creating payment method...\n";

        $paymentMethod = new ResortPaymentMethods();
        $paymentMethod->resortId = $resortId;
        $paymentMethod->methodName = $methodName;
        $paymentMethod->methodDetails = $methodDetails;
        $paymentMethod->isActive = true;

        $result = ResortPaymentMethods::create($paymentMethod);
        if ($result) {
            echo "✅ Payment method created successfully!\n";
            $_SESSION['success_message'] = "Payment method added successfully.";
            echo "Session success message set.\n";
        } else {
            echo "❌ Failed to create payment method.\n";
            $_SESSION['error_message'] = "Failed to add payment method.";
            echo "Session error message set.\n";
        }
    }

    echo "Current session messages:\n";
    echo "- success_message: " . ($_SESSION['success_message'] ?? 'none') . "\n";
    echo "- error_message: " . ($_SESSION['error_message'] ?? 'none') . "\n\n";

    // Simulate redirect behavior
    echo "Test 3: Simulating redirect logic...\n";
    $redirectLocation = '?controller=admin&action=management';
    echo "Redirect location: $redirectLocation\n";
    echo "Headers that would be sent: Location: $redirectLocation\n";

} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getStackTraceAsString() . "\n";
}

// Clean up session for next test
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

echo "\n=== Test Complete ===\n";
