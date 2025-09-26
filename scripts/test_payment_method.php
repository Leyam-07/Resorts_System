<?php

// Test script for payment method creation issue
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Models/ResortPaymentMethods.php';

echo 'Testing database connection and model...' . PHP_EOL;

try {
    // Test direct database query
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo 'DB connection successful' . PHP_EOL;

    // Check table structure
    $result = $pdo->query('DESCRIBE ResortPaymentMethods');
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);

    echo 'Table structure:' . PHP_EOL;
    foreach ($columns as $col) {
        echo '  ' . $col['Field'] . ' - ' . $col['Type'] . ' - ' . $col['Null'] . ' - ' . $col['Key'] . ' - ' . $col['Default'] . PHP_EOL;
    }

    // Test insert
    echo PHP_EOL . 'Testing insert...' . PHP_EOL;
    $pm = new ResortPaymentMethods();
    $pm->resortId = 1;
    $pm->methodName = 'Test Method';
    $pm->methodDetails = 'Test details';
    $pm->isActive = true;

    $result = ResortPaymentMethods::create($pm);
    echo 'Insert result: ' . ($result !== false ? $result : 'FAILED') . PHP_EOL;

    // Check existing records
    echo PHP_EOL . 'Existing payment methods for resort 1:' . PHP_EOL;
    $methods = ResortPaymentMethods::findByResortId(1, false);
    foreach ($methods as $method) {
        echo '  ID: ' . $method->PaymentMethodID . ', Name: ' . $method->MethodName . ', Details: ' . $method->MethodDetails . ', Active: ' . ($method->IsActive ? 'Yes' : 'No') . PHP_EOL;
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo 'Stack trace:' . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
?>
