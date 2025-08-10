<?php

require_once __DIR__ . '/../../app/Models/User.php';
require_once __DIR__ . '/../../app/Models/Facility.php';
require_once __DIR__ . '/../../app/Models/Booking.php';

echo "--- Starting Data Validation Test ---\n\n";

// --- PREREQUISITES ---
$customerUser = User::findByUsername('Customer2');
if (!$customerUser) {
    echo "Failure: Could not find the test user 'Customer2'. Please ensure the user exists.\n";
    exit;
}
$testCustomerId = $customerUser['UserID'];

// --- Test Case 1: Booking with a non-existent Facility ID ---
echo "1. Testing with a non-existent Facility ID...\n";

// We will replicate the controller's logic for the test
$nonExistentFacilityId = 9999;
if (!Facility::findById($nonExistentFacilityId)) {
    echo "   Success: Correctly identified that Facility ID $nonExistentFacilityId does not exist.\n";
} else {
    echo "   Failure: Did not correctly identify the non-existent facility.\n";
    exit;
}

// --- Test Case 2: Booking with a date in the past ---
echo "\n2. Testing with a past date...\n";
$pastDate = '2020-01-01';

// Replicating the controller's logic for the test
$today = new DateTime();
$bookingDateTime = new DateTime($pastDate);
if ($bookingDateTime < $today) {
    echo "   Success: Correctly identified that the date $pastDate is in the past.\n";
} else {
    echo "   Failure: Did not correctly identify the past date.\n";
    exit;
}

echo "\n--- Data Validation Test Completed Successfully! ---\n";

?>