<?php

echo "=== Testing Live Calendar API Response ===" . PHP_EOL;

// Test the actual API endpoint with real HTTP request
$baseUrl = "http://localhost/ResortsSystem/";
$testUrl = $baseUrl . "?controller=booking&action=getCalendarAvailability&resort_id=1&timeframe=24_hours&month=" . date('Y-m');

echo "Testing URL: $testUrl" . PHP_EOL;
echo PHP_EOL;

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "❌ cURL Error: $curlError" . PHP_EOL;
    echo "(This is expected if XAMPP is not running or URL is incorrect)" . PHP_EOL;
} else {
    echo "✅ HTTP Response Code: $httpCode" . PHP_EOL;
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        
        if ($data && isset($data['availability'])) {
            echo "✅ API Response received successfully" . PHP_EOL;
            echo "Month: " . $data['month'] . PHP_EOL;
            echo "Resort ID: " . $data['resortId'] . PHP_EOL;
            echo "Timeframe: " . $data['timeframe'] . PHP_EOL;
            echo PHP_EOL;
            
            // Check specific dates that user reported as wrong
            $today = date('Y-m-d');
            $testDatesForMonth = [];
            
            // Get some dates from this month to test
            $currentDate = new DateTime($data['month'] . '-01');
            $endDate = new DateTime(date('Y-m-t', strtotime($data['month'] . '-01')));
            
            echo "Weekend Analysis for " . $data['month'] . ":" . PHP_EOL;
            $dayCount = 0;
            while ($currentDate <= $endDate && $dayCount < 14) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayName = $currentDate->format('l');
                $dayData = $data['availability'][$dateStr] ?? null;
                
                if ($dayData) {
                    $isWeekend = $dayData['isWeekend'];
                    $status = $dayData['status'];
                    
                    echo "$dateStr ($dayName): isWeekend=" . ($isWeekend ? 'true' : 'false') . ", status=$status" . PHP_EOL;
                    
                    // Check for inconsistencies
                    if (in_array($dayName, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']) && $isWeekend) {
                        echo "  ⚠️  FOUND THE BUG: Weekday marked as weekend!" . PHP_EOL;
                    }
                    
                    if (in_array($dayName, ['Saturday', 'Sunday']) && !$isWeekend) {
                        echo "  ⚠️  FOUND THE BUG: Weekend day marked as weekday!" . PHP_EOL;
                    }
                }
                
                $currentDate->modify('+1 day');
                $dayCount++;
            }
            
        } else {
            echo "❌ Invalid JSON response or missing availability data" . PHP_EOL;
            echo "Raw response: " . substr($response, 0, 300) . "..." . PHP_EOL;
        }
    } else {
        echo "❌ HTTP Error $httpCode" . PHP_EOL;
        echo "Response: " . substr($response, 0, 300) . PHP_EOL;
    }
}

echo PHP_EOL . "=== Alternative: Direct Method Test ===" . PHP_EOL;

// If cURL fails, test the method directly
try {
    $_GET['resort_id'] = '1';
    $_GET['timeframe'] = '24_hours';
    $_GET['month'] = date('Y-m');
    
    require_once '../../app/Controllers/BookingController.php';
    $controller = new BookingController();
    
    ob_start();
    $controller->getCalendarAvailability();
    $directResponse = ob_get_clean();
    
    $data = json_decode($directResponse, true);
    if ($data && isset($data['availability'])) {
        echo "✅ Direct method works" . PHP_EOL;
        
        // Quick weekend check
        $foundIssue = false;
        foreach ($data['availability'] as $dateStr => $dayData) {
            $dayName = date('l', strtotime($dateStr));
            $isWeekendInData = $dayData['isWeekend'];
            $shouldBeWeekend = in_array($dayName, ['Saturday', 'Sunday']);
            
            if ($isWeekendInData !== $shouldBeWeekend) {
                echo "❌ INCONSISTENCY: $dateStr ($dayName) - Data says weekend: " . ($isWeekendInData ? 'true' : 'false') . ", Should be: " . ($shouldBeWeekend ? 'true' : 'false') . PHP_EOL;
                $foundIssue = true;
            }
        }
        
        if (!$foundIssue) {
            echo "✅ Weekend detection is working correctly in backend" . PHP_EOL;
            echo "The issue might be in frontend rendering or browser caching" . PHP_EOL;
        }
    }
    
} catch (Exception $e) {
    echo "❌ Direct method failed: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== Test completed ===" . PHP_EOL;