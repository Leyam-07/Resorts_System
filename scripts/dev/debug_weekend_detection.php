<?php

echo "=== Debug Weekend Detection Logic ===" . PHP_EOL;

// Test specific dates to see what's happening
$testDates = [
    '2025-01-05', // Sunday
    '2025-01-06', // Monday 
    '2025-01-07', // Tuesday
    '2025-01-08', // Wednesday
    '2025-01-09', // Thursday
    '2025-01-10', // Friday
    '2025-01-11', // Saturday
];

echo "Testing weekend detection for specific dates:" . PHP_EOL;
echo PHP_EOL;

foreach ($testDates as $dateStr) {
    $currentDate = new DateTime($dateStr);
    $dayOfWeek = intval($currentDate->format('w'));
    $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
    $dayName = $currentDate->format('l');
    
    echo "Date: $dateStr ($dayName)" . PHP_EOL;
    echo "  Day of week (w format): $dayOfWeek" . PHP_EOL;
    echo "  Is weekend: " . ($isWeekend ? 'YES' : 'NO') . PHP_EOL;
    echo "  Expected: " . (in_array($dayName, ['Saturday', 'Sunday']) ? 'Weekend' : 'Weekday') . PHP_EOL;
    echo "  ✓ " . ($isWeekend === in_array($dayName, ['Saturday', 'Sunday']) ? 'CORRECT' : 'INCORRECT') . PHP_EOL;
    echo PHP_EOL;
}

echo "=== Testing Current Calendar Month ===" . PHP_EOL;

// Test current month logic similar to the API
$month = date('Y-m', strtotime('+1 month')); // Next month
$startDate = $month . '-01';
$endDate = date('Y-m-t', strtotime($startDate));

echo "Testing month: $month" . PHP_EOL;
echo "Date range: $startDate to $endDate" . PHP_EOL;
echo PHP_EOL;

$currentDate = new DateTime($startDate);
$endDateTime = new DateTime($endDate);

$dayCount = 0;
while ($currentDate <= $endDateTime && $dayCount < 14) { // First 2 weeks
    $dateStr = $currentDate->format('Y-m-d');
    $dayOfWeek = intval($currentDate->format('w'));
    $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
    $dayName = $currentDate->format('l');
    
    // Status logic from the controller
    $isPast = ($currentDate < new DateTime('today'));
    $status = 'available';
    if ($isPast) $status = 'past';
    elseif ($isWeekend) $status = 'weekend';
    
    echo "$dateStr ($dayName): dayOfWeek=$dayOfWeek, isWeekend=" . ($isWeekend ? 'true' : 'false') . ", status=$status" . PHP_EOL;
    
    // Flag any incorrect weekend detection
    if (($dayName === 'Monday' || $dayName === 'Tuesday' || $dayName === 'Wednesday' || 
         $dayName === 'Thursday' || $dayName === 'Friday') && $isWeekend) {
        echo "  ⚠️  ERROR: Weekday incorrectly marked as weekend!" . PHP_EOL;
    }
    
    if (($dayName === 'Saturday' || $dayName === 'Sunday') && !$isWeekend) {
        echo "  ⚠️  ERROR: Weekend day incorrectly marked as weekday!" . PHP_EOL;
    }
    
    $currentDate->modify('+1 day');
    $dayCount++;
}

echo PHP_EOL . "=== Debug completed ===" . PHP_EOL;