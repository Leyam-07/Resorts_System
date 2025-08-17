<?php

// This script is for testing the Payment model.
// It's intended to be run from the command line.

// Adjust the path to the bootstrap file as needed
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/Payment.php';
require_once __DIR__ . '/../../app/Models/Booking.php';
require_once __DIR__ . '/../../app/Models/User.php';
require_once __DIR__ . '/../../app/Models/Facility.php';

// Create a dummy booking to associate the payment with
$userModel = new User();
$testUser = $userModel->findByUsername('testcustomer');
if (!$testUser) {
    echo "Creating a dummy user 'testcustomer'...\n";
    $userId = $userModel->create([
        'Username' => 'testcustomer',
        'Password' => password_hash('password', PASSWORD_DEFAULT),
        'Email' => 'customer@test.com',
        'FirstName' => 'Test',
        'LastName' => 'Customer',
        'PhoneNumber' => '1234567890',
        'Role' => 'Customer'
    ]);
} else {
    $userId = $testUser['UserID'];
}

$facilityModel = new Facility();
$testFacility = $facilityModel->findById(1); // Assuming facility with ID 1 exists
if (!$testFacility) {
    die("Please seed the database with at least one facility.\n");
}


$bookingModel = new Booking();
echo "Creating a dummy booking...\n";

$newBooking = new Booking();
$newBooking->customerId = $userId;
$newBooking->facilityId = 1;
$newBooking->bookingDate = date('Y-m-d');
$newBooking->startTime = '10:00:00';
$newBooking->endTime = '12:00:00';
$newBooking->numberOfGuests = 5;
$newBooking->status = 'Confirmed';

$bookingId = $bookingModel->create($newBooking);

if (!$bookingId) {
    die("Failed to create a dummy booking. Exiting.\n");
}

echo "Dummy booking created with ID: $bookingId\n";

$paymentModel = new Payment();

// 1. Test create()
echo "Testing Payment::create()...\n";
$newPayment = new Payment();
$newPayment->bookingId = $bookingId;
$newPayment->amount = 500.00;
$newPayment->paymentMethod = 'Gcash';
$newPayment->status = 'Paid';
$newPayment->proofOfPaymentURL = null; // or a dummy URL

$paymentId = $paymentModel->create($newPayment);
if ($paymentId) {
    echo "SUCCESS: Payment created with ID: $paymentId\n";
} else {
    echo "FAILURE: Could not create payment.\n";
    exit;
}

// 2. Test findByBookingId()
echo "\nTesting Payment::findByBookingId()...\n";
$payments = $paymentModel->findByBookingId($bookingId);
if (!empty($payments)) {
    echo "SUCCESS: Found payments for Booking ID $bookingId.\n";
    print_r($payments);
} else {
    echo "FAILURE: Could not find payments for Booking ID $bookingId.\n";
}

// 3. Test updateStatus()
echo "\nTesting Payment::updateStatus()...\n";
$newStatus = 'Partial';
$updated = $paymentModel->updateStatus($paymentId, $newStatus);
if ($updated) {
    echo "SUCCESS: Payment status updated to '$newStatus'.\n";
    // Verify the update
    $updatedPayment = $paymentModel->findById($paymentId);
    if ($updatedPayment && $updatedPayment->status === $newStatus) {
        echo "VERIFIED: Status is now '$newStatus'.\n";
    } else {
        echo "VERIFICATION FAILED: Status did not update correctly.\n";
    }
} else {
    echo "FAILURE: Could not update payment status.\n";
}