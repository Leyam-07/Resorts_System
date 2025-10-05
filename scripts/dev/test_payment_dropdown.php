<?php

require_once __DIR__ . '/../../app/Models/ResortPaymentMethods.php';

echo "Testing payment method dropdown data...\n\n";

// Test directly with resort ID 1 (from our database knowledge)
$resortId = 1;
echo "Testing with resort ID: $resortId\n\n";

// Test finding payment methods for this resort
$methods = ResortPaymentMethods::findByResortId($resortId, true);
echo "Active payment methods found: " . count($methods) . "\n\n";

foreach ($methods as $method) {
    echo "Method: {$method->MethodType}\n";
    echo "Details: {$method->AccountDetails}\n";
    echo "Active: {$method->IsActive}\n\n";
}

// Test with activeOnly = false as well
$methodsAll = ResortPaymentMethods::findByResortId($resortId, false);
echo "All methods (including inactive): " . count($methodsAll) . "\n";

foreach ($methodsAll as $method) {
    echo "  - {$method->MethodType} (Active: {$method->IsActive})\n";
}
echo "\n";

echo "Test complete.\n";
?>
