<?php
session_start();

require_once __DIR__ . '/../app/Helpers/Database.php';
require_once __DIR__ . '/../app/Models/Facility.php';
require_once __DIR__ . '/../app/Models/Resort.php';

// Simulate login - set admin user
$_SESSION['user_id'] = 1; // Assuming admin user exists
$_SESSION['role'] = 'Admin';

echo "=== Testing getFacilitiesJson functionality ===\n";

// Test with a known resort that should have facilities
$testResorts = [1, 2, 3]; // Try different resort IDs

foreach ($testResorts as $resortId) {
    echo "\n--- Testing Resort ID: {$resortId} ---\n";

    // Check if resort exists
    $resort = Resort::findById($resortId);
    if (!$resort) {
        echo "Resort not found\n";
        continue;
    }
    echo "Resort found: {$resort->name}\n";

    // Test the method directly
    $facilities = Facility::findByResortId($resortId);
    echo "Facilities found: " . count($facilities) . "\n";

    if (!empty($facilities)) {
        echo "Sample facility:\n";
        $first = reset($facilities);
        echo "  ID: {$first->facilityId}, Name: {$first->name}, Rate: {$first->rate}\n";
    }

    // Test JSON encoding
    $response = ['success' => true, 'facilities' => $facilities];
    $json = json_encode($response);
    echo "JSON length: " . strlen($json) . " characters\n";
    echo "JSON sample: " . substr($json, 0, 200) . "...\n";
}

echo "\n--- Testing Controller Method ---\n";

// Simulate request
$_GET['resort_id'] = 1; // Use the first resort

// Try to create controller instance (if it doesn't have constructor issues)
try {
    require_once __DIR__ . '/../app/Controllers/ResortController.php';
    $controller = new ResortController();

    echo "Controller instantiated successfully\n";

    // Call the method directly
    $_GET['resort_id'] = 1;
    $controller->getFacilitiesJson();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== End Test ===\n";
