<?php

require_once __DIR__ . '/../app/Models/Booking.php';

echo "--- Starting Booking Model Test ---\n\n";

// --- PREREQUISITES ---
// This test assumes you have:
// 1. A user with CustomerID = 1 (or you can change the ID below)
// 2. A facility with FacilityID = 1 (or you can change the ID below)
$testCustomerId = 2; // Assuming a customer with ID 2 exists
$testFacilityId = 1; // Assuming facility with ID 1 exists

// --- 1. Test isTimeSlotAvailable (when slot IS free) ---
echo "1. Testing isTimeSlotAvailable (for a free slot)...\n";
$isAvailable = Booking::isTimeSlotAvailable($testFacilityId, '2025-12-25', '10:00:00', '14:00:00');
if ($isAvailable) {
    echo "   Success: Correctly identified the time slot as available.\n";
} else {
    echo "   Failure: Incorrectly identified the time slot as booked.\n";
    exit;
}

// --- 2. Test CREATE ---
echo "\n2. Testing CREATE...\n";
$newBooking = new Booking();
$newBooking->customerId = $testCustomerId;
$newBooking->facilityId = $testFacilityId;
$newBooking->bookingDate = '2025-12-25';
$newBooking->startTime = '10:00:00';
$newBooking->endTime = '14:00:00';
$newBooking->numberOfGuests = 10;
$newBooking->status = 'Confirmed';

$newId = Booking::create($newBooking);

if ($newId) {
    echo "   Success: Booking created with ID: $newId\n";
} else {
    echo "   Failure: Could not create booking.\n";
    exit;
}

// --- 3. Test isTimeSlotAvailable (when slot IS BOOKED) ---
echo "\n3. Testing isTimeSlotAvailable (for a booked slot)...\n";
$isAvailableNow = Booking::isTimeSlotAvailable($testFacilityId, '2025-12-25', '11:00:00', '13:00:00');
if (!$isAvailableNow) {
    echo "   Success: Correctly identified the overlapping time slot as NOT available.\n";
} else {
    echo "   Failure: Failed to detect booking conflict.\n";
    // Clean up
    Booking::delete($newId);
    exit;
}

// --- 4. Test READ ---
echo "\n4. Testing READ (findById)...\n";
$foundBooking = Booking::findById($newId);

if ($foundBooking && $foundBooking->status === 'Confirmed') {
    echo "   Success: Found booking with ID $newId. Status: " . $foundBooking->status . "\n";
} else {
    echo "   Failure: Could not find booking with ID $newId or data mismatch.\n";
    // Clean up
    Booking::delete($newId);
    exit;
}

// --- 5. Test UPDATE ---
echo "\n5. Testing UPDATE...\n";
$foundBooking->status = 'Completed';
$foundBooking->numberOfGuests = 12;

if (Booking::update($foundBooking)) {
    echo "   Success: Booking with ID $newId updated.\n";
} else {
    echo "   Failure: Could not update booking with ID $newId.\n";
    // Clean up
    Booking::delete($newId);
    exit;
}

// --- 6. Test READ AGAIN (after update) ---
echo "\n6. Testing READ again (to verify update)...\n";
$updatedBooking = Booking::findById($newId);

if ($updatedBooking && $updatedBooking->status === 'Completed' && $updatedBooking->numberOfGuests == 12) {
    echo "   Success: Verified updated data. Status: " . $updatedBooking->status . ", Guests: " . $updatedBooking->numberOfGuests . "\n";
} else {
    echo "   Failure: Booking data was not updated correctly.\n";
    // Clean up
    Booking::delete($newId);
    exit;
}

// --- 7. Test DELETE ---
echo "\n7. Testing DELETE...\n";
if (Booking::delete($newId)) {
    echo "   Success: Booking with ID $newId deleted.\n";
} else {
    echo "   Failure: Could not delete booking with ID $newId.\n";
    exit;
}

// --- 8. Verifying DELETION ---
echo "\n8. Verifying DELETION...\n";
$deletedBooking = Booking::findById($newId);

if ($deletedBooking === null) {
    echo "   Success: Booking with ID $newId is confirmed deleted.\n";
} else {
    echo "   Failure: Booking with ID $newId was not properly deleted.\n";
    exit;
}

echo "\n--- Booking Model Test Completed Successfully! ---\n";

?>