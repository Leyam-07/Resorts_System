<?php

require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Facility.php';
require_once __DIR__ . '/../app/Models/Booking.php';

echo "--- Starting Model Relationship Test ---\n\n";

// --- PREREQUISITES ---
// This test assumes you have:
// --- DYNAMIC PREREQUISITES ---
echo "--- Fetching prerequisites for test ---\n";

// 1. Find the Customer user to get their ID
$testUsername = 'Customer2';
$customerUser = User::findByUsername($testUsername);
if (!$customerUser) {
    echo "   Failure: Could not find the test user '$testUsername'. Please ensure the user exists.\n";
    exit;
}
$testCustomerId = $customerUser['UserID'];
echo "   Success: Found test user '$testUsername' with ID: $testCustomerId\n";

// 2. Find the Facility to get its ID
$testFacilityName = 'Main Pool'; // As defined in the seeder
$allFacilities = Facility::findAll();
$testFacilityId = null;
foreach ($allFacilities as $facility) {
    if ($facility->name === $testFacilityName) {
        $testFacilityId = $facility->facilityId;
        break;
    }
}

if (!$testFacilityId) {
    echo "   Failure: Could not find the test facility '$testFacilityName'. Please ensure the seeder has been run.\n";
    exit;
}
echo "   Success: Found test facility '$testFacilityName' with ID: $testFacilityId\n\n";

// --- 1. CREATE a new booking ---
echo "1. Creating a test booking...\n";
$newBooking = new Booking();
$newBooking->customerId = $testCustomerId;
$newBooking->facilityId = $testFacilityId;
$newBooking->bookingDate = '2026-01-15';
$newBooking->startTime = '09:00:00';
$newBooking->endTime = '12:00:00';
$newBooking->numberOfGuests = 8;
$newBooking->status = 'Confirmed';

$newBookingId = Booking::create($newBooking);

if (!$newBookingId) {
    echo "   Failure: Could not create the initial booking for the test.\n";
    exit;
}

echo "   Success: Created booking with ID: $newBookingId\n";

// --- 2. RETRIEVE the booking and check relationships ---
echo "\n2. Retrieving booking and verifying relationships...\n";
$retrievedBooking = Booking::findById($newBookingId);

if (!$retrievedBooking) {
    echo "   Failure: Could not retrieve the booking with ID $newBookingId.\n";
    Booking::delete($newBookingId); // Clean up
    exit;
}

// --- 3. VERIFY User (Customer) Relationship ---
echo "   - Verifying User relationship...\n";
$relatedUser = User::findById($retrievedBooking->customerId);

if ($relatedUser && $relatedUser['Username'] === $testUsername) {
    echo "     Success: Booking correctly linked to User: " . $relatedUser['Username'] . "\n";
} else {
    echo "     Failure: Booking is NOT correctly linked to the User.\n";
    echo "       Expected Username: $testUsername\n";
    echo "       Found User: " . ($relatedUser ? $relatedUser['Username'] : 'Not Found') . "\n";
    Booking::delete($newBookingId); // Clean up
    exit;
}

// --- 4. VERIFY Facility Relationship ---
echo "   - Verifying Facility relationship...\n";
$relatedFacility = Facility::findById($retrievedBooking->facilityId);

if ($relatedFacility && $relatedFacility->name === $testFacilityName) {
    echo "     Success: Booking correctly linked to Facility: " . $relatedFacility->name . "\n";
} else {
    echo "     Failure: Booking is NOT correctly linked to the Facility.\n";
    echo "       Expected Facility: $testFacilityName\n";
    echo "       Found Facility: " . ($relatedFacility ? $relatedFacility->name : 'Not Found') . "\n";
    Booking::delete($newBookingId); // Clean up
    exit;
}

// --- 5. CLEAN UP the test booking ---
echo "\n3. Cleaning up test data...\n";
if (Booking::delete($newBookingId)) {
    echo "   Success: Test booking with ID $newBookingId deleted.\n";
} else {
    echo "   Failure: Could not clean up the test booking.\n";
}

echo "\n--- Model Relationship Test Completed Successfully! ---\n";

?>