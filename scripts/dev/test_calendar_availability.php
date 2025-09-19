<?php

// Test the getCalendarAvailability functionality directly
require_once '../../config/database.php';
require_once '../../app/Models/Booking.php';
require_once '../../app/Models/BlockedResortAvailability.php';

echo "=== Testing Calendar Availability Functionality ===" . PHP_EOL;

try {
    // Test parameters
    $resortId = 1;
    $timeframe = '24_hours';
    $month = date('Y-m', strtotime('+1 month')); // Next month to see future dates
    
    echo "Parameters:" . PHP_EOL;
    echo "  Resort ID: $resortId" . PHP_EOL;
    echo "  Timeframe: $timeframe" . PHP_EOL;
    echo "  Month: $month" . PHP_EOL;
    echo PHP_EOL;
    
    // Test database connection first
    echo "Testing database connection..." . PHP_EOL;
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection successful" . PHP_EOL;
    
    // Test if resort exists
    echo "Checking if resort exists..." . PHP_EOL;
    $stmt = $db->prepare("SELECT COUNT(*) FROM Resorts WHERE ResortID = ?");
    $stmt->execute([$resortId]);
    $resortExists = $stmt->fetchColumn() > 0;
    echo $resortExists ? "✓ Resort exists" : "✗ Resort does not exist" . PHP_EOL;
    
    if (!$resortExists) {
        echo "Available resorts:" . PHP_EOL;
        $stmt = $db->prepare("SELECT ResortID, Name FROM Resorts");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  Resort {$row['ResortID']}: {$row['Name']}" . PHP_EOL;
        }
    }
    
    // Test the calendar availability logic manually
    echo PHP_EOL . "Testing calendar availability logic..." . PHP_EOL;
    
    // Get start and end dates for the month
    $startDate = $month . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));
    echo "Date range: $startDate to $endDate" . PHP_EOL;
    
    $availability = [];
    
    // Generate calendar data for each day of the month
    $currentDate = new DateTime($startDate);
    $endDateTime = new DateTime($endDate);
    
    $dayCount = 0;
    while ($currentDate <= $endDateTime && $dayCount < 7) { // Test first 7 days only
        $dateStr = $currentDate->format('Y-m-d');
        $dayOfWeek = $currentDate->format('w');
        $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);
        $isToday = ($dateStr === date('Y-m-d'));
        $isPast = ($currentDate < new DateTime('today'));
        
        echo "Testing date: $dateStr" . PHP_EOL;
        
        // Check if resort is available for this date/timeframe
        $isAvailable = Booking::isResortTimeframeAvailable($resortId, $dateStr, $timeframe, []);
        echo "  Available: " . ($isAvailable ? "Yes" : "No") . PHP_EOL;
        
        // Check blocked resort dates
        $stmt = $db->prepare("SELECT COUNT(*) FROM BlockedResortAvailability WHERE ResortID = ? AND BlockDate = ?");
        $stmt->execute([$resortId, $dateStr]);
        $resortBlocked = $stmt->fetchColumn() > 0;
        echo "  Blocked: " . ($resortBlocked ? "Yes" : "No") . PHP_EOL;
        
        // Get existing bookings count
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM Bookings
            WHERE ResortID = ? AND BookingDate = ? AND TimeSlotType = ? AND Status IN ('Confirmed', 'Pending')
        ");
        $stmt->execute([$resortId, $dateStr, $timeframe]);
        $bookingCount = $stmt->fetchColumn();
        echo "  Booking count: $bookingCount" . PHP_EOL;
        
        // Determine status
        $status = 'available';
        if ($isPast) $status = 'past';
        elseif ($resortBlocked) $status = 'blocked';
        elseif (!$isAvailable) $status = 'unavailable';
        elseif ($bookingCount > 0) $status = 'booked';
        elseif ($isWeekend) $status = 'weekend';
        
        $availability[$dateStr] = [
            'available' => $isAvailable && !$resortBlocked && !$isPast,
            'isWeekend' => $isWeekend,
            'isToday' => $isToday,
            'isPast' => $isPast,
            'isBlocked' => $resortBlocked,
            'bookingCount' => $bookingCount,
            'status' => $status
        ];
        
        echo "  Status: $status" . PHP_EOL;
        echo "  Final availability: " . ($availability[$dateStr]['available'] ? "Available" : "Not available") . PHP_EOL;
        echo PHP_EOL;
        
        $currentDate->modify('+1 day');
        $dayCount++;
    }
    
    echo "Calendar generation successful!" . PHP_EOL;
    echo "Generated availability data for " . count($availability) . " days" . PHP_EOL;
    
    // Create the expected JSON response
    $response = [
        'month' => $month,
        'availability' => $availability,
        'resortId' => $resortId,
        'timeframe' => $timeframe
    ];
    
    echo PHP_EOL . "Sample JSON response:" . PHP_EOL;
    echo json_encode($response, JSON_PRETTY_PRINT) . PHP_EOL;
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== Test completed ===" . PHP_EOL;