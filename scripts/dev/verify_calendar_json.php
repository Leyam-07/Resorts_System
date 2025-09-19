<?php

// Extract and verify the JSON structure from the API response
$jsonResponse = '{"month":"2025-10","availability":{"2025-10-01":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-02":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-03":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-04":{"available":true,"isWeekend":true,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"weekend"},"2025-10-05":{"available":true,"isWeekend":true,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"weekend"},"2025-10-06":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-07":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-08":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-09":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-10":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-11":{"available":true,"isWeekend":true,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"weekend"},"2025-10-12":{"available":true,"isWeekend":true,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"weekend"},"2025-10-13":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-14":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-15":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-16":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-17":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-18":{"available":true,"isWeekend":true,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"weekend"},"2025-10-19":{"available":true,"isWeekend":true,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"weekend"},"2025-10-20":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-21":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-22":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-23":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-24":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-25":{"available":true,"isWeekend":true,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"weekend"},"2025-10-26":{"available":true,"isWeekend":true,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"weekend"},"2025-10-27":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-28":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-29":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-30":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"},"2025-10-31":{"available":true,"isWeekend":false,"isToday":false,"isPast":false,"isBlocked":false,"bookingCount":0,"status":"available"}},"resortId":1,"timeframe":"24_hours"}';

echo "=== Calendar API JSON Response Verification ===" . PHP_EOL;

$data = json_decode($jsonResponse, true);

if ($data) {
    echo "✓ JSON is valid!" . PHP_EOL;
    echo "✓ Response structure:" . PHP_EOL;
    echo "  Month: " . $data['month'] . PHP_EOL;
    echo "  Resort ID: " . $data['resortId'] . PHP_EOL;
    echo "  Timeframe: " . $data['timeframe'] . PHP_EOL;
    echo "  Total availability data: " . count($data['availability']) . " days" . PHP_EOL;
    
    // Analyze the availability data
    $availableCount = 0;
    $weekendCount = 0;
    $blockedCount = 0;
    
    foreach ($data['availability'] as $date => $info) {
        if ($info['status'] === 'available') $availableCount++;
        elseif ($info['status'] === 'weekend') $weekendCount++;
        elseif ($info['status'] === 'blocked') $blockedCount++;
    }
    
    echo PHP_EOL . "✓ Availability breakdown:" . PHP_EOL;
    echo "  Available days: $availableCount" . PHP_EOL;
    echo "  Weekend days: $weekendCount" . PHP_EOL;
    echo "  Blocked days: $blockedCount" . PHP_EOL;
    
    echo PHP_EOL . "✓ Sample data structure:" . PHP_EOL;
    $sampleDate = array_keys($data['availability'])[0];
    $sampleData = $data['availability'][$sampleDate];
    
    foreach ($sampleData as $key => $value) {
        $valueStr = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        echo "  $key: $valueStr" . PHP_EOL;
    }
    
    echo PHP_EOL . "✓ Weekend detection verification:" . PHP_EOL;
    $weekendDates = [];
    foreach ($data['availability'] as $date => $info) {
        if ($info['isWeekend']) {
            $weekendDates[] = $date . ' (' . date('l', strtotime($date)) . ')';
        }
    }
    echo "  Weekend dates: " . implode(', ', array_slice($weekendDates, 0, 4)) . "..." . PHP_EOL;
    
} else {
    echo "✗ JSON is invalid: " . json_last_error_msg() . PHP_EOL;
}

echo PHP_EOL . "=== Verification completed ===" . PHP_EOL;