<?php

// Test the getCalendarAvailability API endpoint as it would be called from the frontend
require_once '../../config/database.php';
require_once '../../app/Controllers/BookingController.php';

echo "=== Testing Calendar Availability API (Fixed Version) ===" . PHP_EOL;

try {
    // Simulate the API request parameters (as they would come from JavaScript fetch)
    $_GET['resort_id'] = '1';
    $_GET['timeframe'] = '24_hours';
    $_GET['month'] = date('Y-m', strtotime('+1 month')); // Next month to see future dates
    
    echo "Testing with parameters:" . PHP_EOL;
    echo "  Resort ID: {$_GET['resort_id']}" . PHP_EOL;
    echo "  Timeframe: {$_GET['timeframe']}" . PHP_EOL;
    echo "  Month: {$_GET['month']}" . PHP_EOL;
    echo PHP_EOL;
    
    // Create controller instance and test the fixed method
    $controller = new BookingController();
    
    echo "Calling getCalendarAvailability method..." . PHP_EOL;
    
    // Capture output (this is what would be sent as JSON response)
    ob_start();
    
    // Disable exit() for testing - we'll catch any potential exits
    try {
        $controller->getCalendarAvailability();
    } catch (Exception $e) {
        echo "Exception caught: " . $e->getMessage() . PHP_EOL;
    }
    
    $output = ob_get_clean();
    
    echo "API Response:" . PHP_EOL;
    echo "Raw output length: " . strlen($output) . " characters" . PHP_EOL;
    echo "First 200 characters: " . substr($output, 0, 200) . "..." . PHP_EOL;
    echo PHP_EOL;
    
    // Try to decode JSON
    $decoded = json_decode($output, true);
    if ($decoded) {
        echo "✓ JSON Response Successfully Decoded:" . PHP_EOL;
        
        if (isset($decoded['error'])) {
            echo "✗ API returned error: " . $decoded['error'] . PHP_EOL;
        } else {
            echo "✓ API Response Structure:" . PHP_EOL;
            echo "  Month: " . ($decoded['month'] ?? 'N/A') . PHP_EOL;
            echo "  Resort ID: " . ($decoded['resortId'] ?? 'N/A') . PHP_EOL;
            echo "  Timeframe: " . ($decoded['timeframe'] ?? 'N/A') . PHP_EOL;
            echo "  Availability data count: " . count($decoded['availability'] ?? []) . " days" . PHP_EOL;
            
            if (!empty($decoded['availability'])) {
                echo "✓ Sample availability data:" . PHP_EOL;
                $count = 0;
                foreach ($decoded['availability'] as $date => $data) {
                    if ($count < 3) {
                        echo "    $date: available=" . ($data['available'] ? 'true' : 'false') 
                             . ", status=" . $data['status'] . PHP_EOL;
                        $count++;
                    } else {
                        break;
                    }
                }
            }
        }
    } else {
        echo "✗ Failed to decode JSON. JSON Error: " . json_last_error_msg() . PHP_EOL;
        echo "Raw output:" . PHP_EOL;
        echo $output . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== Test completed ===" . PHP_EOL;