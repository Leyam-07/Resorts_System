// Test script for getResortDetails API endpoint
// Direct test of the data access

require_once __DIR__ . '/../app/Models/Resort.php';
require_once __DIR__ . '/../config/database.php';

echo "Testing resort data access...\n";

// Test with resort ID 1
$resortId = 1;

echo "Fetching resort with ID: $resortId\n";

$resort = Resort::findById($resortId);

if ($resort) {
    echo "Resort found:\n";
    echo "Name: " . $resort->name . "\n";
    echo "Capacity: " . $resort->capacity . "\n";

    // Simulate the API response
    $apiResponse = [
        'resort_id' => $resort->resortId,
        'name' => $resort->name,
        'capacity' => $resort->capacity
    ];

    echo "\nAPI Response would be:\n";
    echo json_encode($apiResponse) . "\n";

    if ($resort->capacity > 0) {
        echo "\nData looks correct! Capacity is " . $resort->capacity . "\n";
    } else {
        echo "\nERROR: Capacity is 0! This is the problem.\n";
    }
} else {
    echo "ERROR: Resort not found!\n";
}
