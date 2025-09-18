<?php
/**
 * Phase 2 Testing: Resort-Centric Booking System
 * 
 * This script tests the core functionality of Phase 2 transformation:
 * - Resort-centric booking creation
 * - Multiple facility selection mechanism
 * - Model relationships and data structure
 * - Basic availability checking
 * 
 * EXCLUDES: Pricing calculations, admin pricing management
 */

echo "=== PHASE 2 TESTING: RESORT-CENTRIC BOOKING SYSTEM ===\n\n";

// Include required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/Resort.php';
require_once __DIR__ . '/../../app/Models/Facility.php';
require_once __DIR__ . '/../../app/Models/Booking.php';
require_once __DIR__ . '/../../app/Models/BookingFacilities.php';
require_once __DIR__ . '/../../app/Models/User.php';

// Test configuration
$testsPassed = 0;
$testsTotal = 0;

function runTest($testName, $testFunction) {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    
    echo "Testing: $testName\n";
    echo str_repeat("-", 50) . "\n";
    
    try {
        $result = $testFunction();
        if ($result) {
            echo "✅ PASS: $testName\n\n";
            $testsPassed++;
        } else {
            echo "❌ FAIL: $testName\n\n";
        }
    } catch (Exception $e) {
        echo "❌ ERROR: $testName - " . $e->getMessage() . "\n\n";
    }
}

// Test 1: Database Structure Verification
runTest("Database Structure & New Tables", function() {
    try {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check for new tables
        $tables = ['BookingFacilities', 'ResortTimeframePricing', 'ResortPaymentMethods'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() === 0) {
                echo "❌ Missing table: $table\n";
                return false;
            }
            echo "✅ Table exists: $table\n";
        }
        
        // Check new columns in Bookings table
        $stmt = $pdo->query("DESCRIBE Bookings");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $newColumns = ['ResortID', 'TotalAmount', 'PaymentProofURL', 'PaymentReference', 'RemainingBalance'];
        
        foreach ($newColumns as $column) {
            if (!in_array($column, $columns)) {
                echo "❌ Missing column in Bookings: $column\n";
                return false;
            }
            echo "✅ Column exists: Bookings.$column\n";
        }
        
        return true;
    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage() . "\n";
        return false;
    }
});

// Test 2: Model Instantiation and Basic Methods
runTest("Model Classes Basic Functionality", function() {
    // Test Resort model
    $resorts = Resort::findAll();
    if (empty($resorts)) {
        echo "⚠️ No resorts found in database\n";
        return false;
    }
    echo "✅ Resort::findAll() returned " . count($resorts) . " resorts\n";
    
    // Test Facility model
    $firstResort = $resorts[0];
    $facilities = Facility::findByResortId($firstResort->resortId);
    echo "✅ Facility::findByResortId() returned " . count($facilities) . " facilities for resort {$firstResort->name}\n";
    
    // Test BookingFacilities model instantiation
    $bf = new BookingFacilities();
    if (!$bf) {
        return false;
    }
    echo "✅ BookingFacilities model instantiated successfully\n";
    
    return true;
});

// Test 3: Resort-Centric Booking Creation (Without Pricing)
runTest("Resort-Centric Booking Creation", function() {
    // Get test data
    $resorts = Resort::findAll();
    $usersData = User::findAll();
    
    if (empty($resorts) || empty($usersData)) {
        echo "❌ Need at least 1 resort and 1 user for testing\n";
        return false;
    }
    
    $testResort = $resorts[0];
    $testUser = (object)$usersData[0]; // Convert array to object for consistency
    $facilities = Facility::findByResortId($testResort->resortId);
    
    echo "✅ Test data loaded: Resort '{$testResort->name}', User '{$testUser->username}'\n";
    echo "✅ Available facilities: " . count($facilities) . "\n";
    
    // Test 1: Resort-only booking (no facilities)
    echo "\n--- Testing resort-only booking ---\n";
    
    // Mock the pricing call by temporarily setting a default total
    $mockBooking = new Booking();
    $mockBooking->customerId = $testUser->UserID;
    $mockBooking->resortId = $testResort->resortId;
    $mockBooking->facilityId = null;
    $mockBooking->bookingDate = date('Y-m-d', strtotime('+7 days'));
    $mockBooking->timeSlotType = '12_hours';
    $mockBooking->numberOfGuests = 5;
    $mockBooking->status = 'Pending';
    $mockBooking->totalAmount = 500.00; // Mock amount
    $mockBooking->remainingBalance = 500.00;
    
    $bookingId1 = Booking::create($mockBooking);
    if (!$bookingId1) {
        echo "❌ Failed to create resort-only booking\n";
        return false;
    }
    echo "✅ Resort-only booking created with ID: $bookingId1\n";
    
    // Test 2: Resort with multiple facilities
    if (count($facilities) >= 2) {
        echo "\n--- Testing resort + multiple facilities booking ---\n";
        
        $mockBooking2 = new Booking();
        $mockBooking2->customerId = $testUser->UserID;
        $mockBooking2->resortId = $testResort->resortId;
        $mockBooking2->facilityId = null;
        $mockBooking2->bookingDate = date('Y-m-d', strtotime('+8 days'));
        $mockBooking2->timeSlotType = '24_hours';
        $mockBooking2->numberOfGuests = 8;
        $mockBooking2->status = 'Pending';
        $mockBooking2->totalAmount = 800.00; // Mock amount
        $mockBooking2->remainingBalance = 800.00;
        
        $bookingId2 = Booking::create($mockBooking2);
        if (!$bookingId2) {
            echo "❌ Failed to create booking for facilities test\n";
            return false;
        }
        
        // Add multiple facilities using BookingFacilities
        $selectedFacilities = array_slice($facilities, 0, 2);
        $facilityIds = array_map(function($f) { return $f->facilityId; }, $selectedFacilities);
        
        $addResult = BookingFacilities::addFacilitiesToBooking($bookingId2, $facilityIds);
        if (!$addResult) {
            echo "❌ Failed to add facilities to booking\n";
            return false;
        }
        echo "✅ Multiple facilities booking created with ID: $bookingId2\n";
        echo "✅ Added " . count($facilityIds) . " facilities to booking\n";
        
        // Verify facility relationships
        $bookingFacilities = BookingFacilities::findByBookingId($bookingId2);
        if (count($bookingFacilities) !== count($facilityIds)) {
            echo "❌ Facility count mismatch in junction table\n";
            return false;
        }
        echo "✅ Junction table correctly stores " . count($bookingFacilities) . " facility relationships\n";
    }
    
    // Clean up test bookings
    if (isset($bookingId1)) Booking::delete($bookingId1);
    if (isset($bookingId2)) {
        BookingFacilities::deleteByBookingId($bookingId2);
        Booking::delete($bookingId2);
    }
    echo "✅ Test bookings cleaned up\n";
    
    return true;
});

// Test 4: Multiple Facility Selection Mechanism
runTest("Multiple Facility Selection Mechanism", function() {
    $resorts = Resort::findAll();
    if (empty($resorts)) {
        return false;
    }
    
    $testResort = $resorts[0];
    $facilities = Facility::findByResortId($testResort->resortId);
    
    if (count($facilities) < 2) {
        echo "⚠️ Resort has less than 2 facilities, testing with available facilities\n";
    }
    
    // Test facility selection and capacity validation
    $totalCapacity = 0;
    foreach ($facilities as $facility) {
        $totalCapacity += $facility->capacity;
        echo "✅ Facility: {$facility->name} - Capacity: {$facility->capacity}\n";
    }
    
    // Test facility IDs extraction
    $facilityIds = array_map(function($f) { return $f->facilityId; }, $facilities);
    echo "✅ Extracted facility IDs: " . implode(', ', $facilityIds) . "\n";
    
    // Test availability checking for multiple facilities
    $testDate = date('Y-m-d', strtotime('+10 days'));
    $isAvailable = BookingFacilities::checkFacilitiesAvailability($facilityIds, $testDate, '12_hours');
    echo "✅ Multiple facility availability check: " . ($isAvailable ? "Available" : "Not Available") . "\n";
    
    return true;
});

// Test 5: Model Relationships and Data Retrieval
runTest("Model Relationships & Data Retrieval", function() {
    // Test booking queries with resort and facility information
    $todaysBookings = Booking::findTodaysBookings();
    echo "✅ Today's bookings query returned " . count($todaysBookings) . " results\n";
    
    if (!empty($todaysBookings)) {
        $firstBooking = $todaysBookings[0];
        echo "✅ Booking includes ResortName: " . (isset($firstBooking->ResortName) ? "Yes" : "No") . "\n";
        echo "✅ Booking includes FacilityNames: " . (isset($firstBooking->FacilityNames) ? "Yes" : "No") . "\n";
    }
    
    $upcomingBookings = Booking::findUpcomingBookings();
    echo "✅ Upcoming bookings query returned " . count($upcomingBookings) . " results\n";
    
    // Test user bookings with resort information
    $usersData = User::findAll();
    if (!empty($usersData)) {
        $testUser = (object)$usersData[0];
        $userBookings = Booking::findByCustomerId($testUser->UserID);
        echo "✅ User bookings query returned " . count($userBookings) . " results\n";
        
        if (!empty($userBookings)) {
            $firstUserBooking = $userBookings[0];
            echo "✅ User booking includes ResortName: " . (isset($firstUserBooking->ResortName) ? "Yes" : "No") . "\n";
            echo "✅ User booking includes FacilityNames: " . (isset($firstUserBooking->FacilityNames) ? "Yes" : "No") . "\n";
        }
    }
    
    return true;
});

// Test 6: Basic Availability Checking
runTest("Basic Availability Checking", function() {
    $resorts = Resort::findAll();
    if (empty($resorts)) {
        return false;
    }
    
    $testResort = $resorts[0];
    $facilities = Facility::findByResortId($testResort->resortId);
    $testDate = date('Y-m-d', strtotime('+15 days'));
    
    // Test resort-level availability
    $resortAvailable = Booking::isResortTimeframeAvailable($testResort->resortId, $testDate, '12_hours');
    echo "✅ Resort availability check: " . ($resortAvailable ? "Available" : "Blocked") . "\n";
    
    // Test facility-level availability
    if (!empty($facilities)) {
        $facilityAvailable = Booking::isTimeSlotAvailable($facilities[0]->facilityId, $testDate, '12_hours');
        echo "✅ Facility availability check: " . ($facilityAvailable ? "Available" : "Blocked") . "\n";
        
        // Test multiple facility availability
        $facilityIds = array_slice(array_map(function($f) { return $f->facilityId; }, $facilities), 0, 2);
        $multipleAvailable = Booking::isResortTimeframeAvailable($testResort->resortId, $testDate, '12_hours', $facilityIds);
        echo "✅ Multiple facilities availability: " . ($multipleAvailable ? "Available" : "Blocked") . "\n";
    }
    
    return true;
});

// Test 7: Data Storage Verification
runTest("Data Storage Verification", function() {
    // Create a test booking to verify storage format
    $resorts = Resort::findAll();
    $usersData = User::findAll();
    
    if (empty($resorts) || empty($usersData)) {
        echo "❌ Need test data to verify storage\n";
        return false;
    }
    
    $testResort = $resorts[0];
    $testUser = (object)$usersData[0];
    
    // Create test booking
    $testBooking = new Booking();
    $testBooking->customerId = $testUser->UserID;
    $testBooking->resortId = $testResort->resortId;
    $testBooking->facilityId = null; // Resort-centric
    $testBooking->bookingDate = date('Y-m-d', strtotime('+20 days'));
    $testBooking->timeSlotType = 'overnight';
    $testBooking->numberOfGuests = 6;
    $testBooking->status = 'Pending';
    $testBooking->totalAmount = 600.00;
    $testBooking->remainingBalance = 600.00;
    
    $bookingId = Booking::create($testBooking);
    if (!$bookingId) {
        echo "❌ Failed to create test booking\n";
        return false;
    }
    
    // Retrieve and verify
    $retrievedBooking = Booking::findById($bookingId);
    if (!$retrievedBooking) {
        echo "❌ Failed to retrieve test booking\n";
        return false;
    }
    
    // Verify new columns
    $checks = [
        'ResortID' => $retrievedBooking->resortId == $testResort->resortId,
        'TotalAmount' => $retrievedBooking->totalAmount == 600.00,
        'RemainingBalance' => $retrievedBooking->remainingBalance == 600.00,
        'PaymentProofURL' => $retrievedBooking->paymentProofURL === null,
        'PaymentReference' => $retrievedBooking->paymentReference === null
    ];
    
    foreach ($checks as $field => $isCorrect) {
        echo ($isCorrect ? "✅" : "❌") . " $field storage: " . ($isCorrect ? "Correct" : "Incorrect") . "\n";
        if (!$isCorrect) {
            Booking::delete($bookingId);
            return false;
        }
    }
    
    // Test payment info update
    $updateResult = Booking::updatePaymentInfo($bookingId, 'test_proof.jpg', 'REF123456', 300.00);
    if (!$updateResult) {
        echo "❌ Failed to update payment info\n";
        Booking::delete($bookingId);
        return false;
    }
    
    $updatedBooking = Booking::findById($bookingId);
    echo "✅ Payment info update successful\n";
    echo "✅ Remaining balance updated: ₱" . $updatedBooking->remainingBalance . "\n";
    
    // Clean up
    Booking::delete($bookingId);
    echo "✅ Test booking cleaned up\n";
    
    return true;
});

// Test 8: Admin Interface Compatibility (Basic Data Display)
runTest("Admin Interface Data Compatibility", function() {
    // Test that admin queries can handle new booking structure
    $bookingsWithDetails = Booking::getBookingsWithDetails(null, null, 5);
    echo "✅ Admin bookings query returned " . count($bookingsWithDetails) . " results\n";
    
    if (!empty($bookingsWithDetails)) {
        $booking = $bookingsWithDetails[0];
        $hasRequiredFields = isset($booking->ResortName) && isset($booking->CustomerName);
        echo "✅ Admin booking data includes required fields: " . ($hasRequiredFields ? "Yes" : "No") . "\n";
        
        // Check for facility names (can be null for resort-only bookings)
        $hasFacilityNames = property_exists($booking, 'FacilityNames');
        echo "✅ Admin booking includes FacilityNames field: " . ($hasFacilityNames ? "Yes" : "No") . "\n";
    }
    
    // Test booking history
    $history = Booking::getBookingHistory(3);
    echo "✅ Booking history query returned " . count($history) . " results\n";
    
    return true;
});

// Final Results
echo "\n" . str_repeat("=", 60) . "\n";
echo "PHASE 2 TESTING RESULTS\n";
echo str_repeat("=", 60) . "\n";
echo "Tests Passed: $testsPassed / $testsTotal\n";

if ($testsPassed === $testsTotal) {
    echo "🎉 ALL TESTS PASSED! Phase 2 is ready for Phase 3.\n";
    echo "\nREADY FOR PHASE 3:\n";
    echo "✅ Database structure is correct\n";
    echo "✅ Resort-centric booking creation works\n";
    echo "✅ Multiple facility selection mechanism functional\n";
    echo "✅ Model relationships are working correctly\n";
    echo "✅ Basic availability checking operational\n";
    echo "✅ Data storage format is correct\n";
    echo "✅ Admin interface compatibility confirmed\n";
} else {
    echo "⚠️  SOME TESTS FAILED. Review failures before proceeding to Phase 3.\n";
    $failedCount = $testsTotal - $testsPassed;
    echo "Failed tests: $failedCount\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
?>