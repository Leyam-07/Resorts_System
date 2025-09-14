<?php

require_once __DIR__ . '/../../app/Models/Booking.php';
require_once __DIR__ . '/../../app/Models/Facility.php';

echo "--- Starting Time-Slot Booking Logic Test ---\n\n";

// --- PREREQUISITES ---
// This test assumes:
// 1. A user with CustomerID = 26 exists (from the list_users script)
// 2. A facility with FacilityID = 1 exists
$testCustomerId = 26;
$testFacilityId = 1;
$testDate = date('Y-m-d', strtotime('+1 month')); // A date in the future to avoid conflicts

// --- Pre-flight Cleanup: Ensure a clean slate for the test date ---
echo "--- Running Pre-flight Cleanup for Date: $testDate ---\n";
try {
    require_once __DIR__ . '/../../config/database.php';
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->prepare("DELETE FROM Bookings WHERE FacilityID = :facilityId AND BookingDate = :bookingDate");
    $stmt->bindValue(':facilityId', $testFacilityId, PDO::PARAM_INT);
    $stmt->bindValue(':bookingDate', $testDate, PDO::PARAM_STR);
    $deletedRows = $stmt->execute() ? $stmt->rowCount() : 0;
    
    echo "   - Deleted $deletedRows orphaned booking(s).\n\n";
    
} catch (PDOException $e) {
    die("   - Cleanup failed: " . e->getMessage());
}

$createdBookingIds = [];
// Register a shutdown function to ensure cleanup happens even if the script fails.
// This uses a closure to capture the $createdBookingIds array by reference, ensuring
// it has the correct values when the script exits.
register_shutdown_function(function() use (&$createdBookingIds) {
    if (empty($createdBookingIds)) {
        return;
    }
    
    echo "\n--- Cleaning up created test bookings ---\n";
    foreach ($createdBookingIds as $id) {
        if (Booking::delete($id)) {
            echo "   - Deleted booking ID: $id\n";
        } else {
            echo "   - FAILED to delete booking ID: $id\n";
        }
    }
});

// --- 1. Test isTimeSlotAvailable (for a completely free date) ---
echo "1. Testing isTimeSlotAvailable (for a free slot '12_hours')...\n";
$isAvailable = Booking::isTimeSlotAvailable($testFacilityId, $testDate, '12_hours');
if ($isAvailable) {
    echo "   Success: Correctly identified the time slot as available.\n";
} else {
    echo "   Failure: Incorrectly identified the time slot as booked. The test date might already have bookings.\n";
    exit;
}

// --- 2. Test CREATE (First Booking: 12 Hours) ---
echo "\n2. Testing CREATE for '12_hours' slot...\n";
$booking1 = new Booking();
$booking1->customerId = $testCustomerId;
$booking1->facilityId = $testFacilityId;
$booking1->bookingDate = $testDate;
$booking1->timeSlotType = '12_hours';
$booking1->numberOfGuests = 5;
$booking1->status = 'Confirmed';

$booking1Id = Booking::create($booking1);
if ($booking1Id) {
    $createdBookingIds[] = $booking1Id;
    echo "   Success: Booking created with ID: $booking1Id\n";
} else {
    echo "   Failure: Could not create the '12_hours' booking.\n";
    exit;
}

// --- 3. Test isTimeSlotAvailable (Direct Conflict) ---
echo "\n3. Testing isTimeSlotAvailable (for a directly conflicting slot)...\n";
$isAvailableNow = Booking::isTimeSlotAvailable($testFacilityId, $testDate, '12_hours');
if (!$isAvailableNow) {
    echo "   Success: Correctly identified the '12_hours' slot as NOT available.\n";
} else {
    echo "   Failure: Failed to detect a direct booking conflict.\n";
    exit;
}

// --- 4. CRITICAL TEST: Test isTimeSlotAvailable (Non-Conflicting Slot on Same Day) ---
echo "\n4. CRITICAL TEST: Testing for a non-conflicting 'overnight' slot on the same day...\n";
$isOvernightAvailable = Booking::isTimeSlotAvailable($testFacilityId, $testDate, 'overnight');
if ($isOvernightAvailable) {
    echo "   Success: Correctly identified the 'overnight' slot as available.\n";
} else {
    echo "   CRITICAL FAILURE: The current logic incorrectly blocks non-overlapping time slots.\n";
    echo "   This test will fail until the isTimeSlotAvailable() method is fixed to check TimeSlotType, not just the date.\n";
    exit;
}

// --- 5. Test CREATE (Second Booking: Overnight) ---
echo "\n5. Testing CREATE for 'overnight' slot on the same date...\n";
$booking2 = new Booking();
$booking2->customerId = $testCustomerId;
$booking2->facilityId = $testFacilityId;
$booking2->bookingDate = $testDate;
$booking2->timeSlotType = 'overnight';
$booking2->numberOfGuests = 2;
$booking2->status = 'Confirmed';

$booking2Id = Booking::create($booking2);
if ($booking2Id) {
    $createdBookingIds[] = $booking2Id;
    echo "   Success: Second booking created with ID: $booking2Id\n";
} else {
    echo "   Failure: Could not create the 'overnight' booking.\n";
    exit;
}

// --- 6. Final Verification ---
echo "\n6. Verifying both bookings exist before cleanup...\n";
$foundBooking1 = Booking::findById($booking1Id);
$foundBooking2 = Booking::findById($booking2Id);

if ($foundBooking1 && $foundBooking2) {
    echo "   Success: Both bookings (ID: $booking1Id, ID: $booking2Id) are confirmed in the database.\n";
} else {
    echo "   Failure: One or both bookings were not found after creation.\n";
    exit;
}

echo "\n--- Time-Slot Booking Logic Test Completed Successfully! ---\n";

?>