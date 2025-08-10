<?php

require_once __DIR__ . '/../app/Models/Facility.php';

echo "--- Starting Facility Model CRUD Test ---\n\n";

// --- CREATE ---
echo "1. Testing CREATE...\n";
$newFacility = new Facility();
$newFacility->resortId = 1; // Assuming a resort with ID 1 exists from seeding
$newFacility->name = 'Test Pool';
$newFacility->capacity = 50;
$newFacility->rate = 1500.00;

$newId = Facility::create($newFacility);

if ($newId) {
    echo "   Success: Facility created with ID: $newId\n";
} else {
    echo "   Failure: Could not create facility.\n";
    exit;
}

// --- READ ---
echo "\n2. Testing READ (findById)...\n";
$foundFacility = Facility::findById($newId);

if ($foundFacility && $foundFacility->name === 'Test Pool') {
    echo "   Success: Found facility with ID $newId. Name: " . $foundFacility->name . "\n";
} else {
    echo "   Failure: Could not find facility with ID $newId or data mismatch.\n";
    // Clean up before exiting
    Facility::delete($newId);
    exit;
}

// --- UPDATE ---
echo "\n3. Testing UPDATE...\n";
$foundFacility->name = 'Updated Test Pool';
$foundFacility->capacity = 75;

if (Facility::update($foundFacility)) {
    echo "   Success: Facility with ID $newId updated.\n";
} else {
    echo "   Failure: Could not update facility with ID $newId.\n";
    // Clean up before exiting
    Facility::delete($newId);
    exit;
}

// --- READ AGAIN (after update) ---
echo "\n4. Testing READ again (to verify update)...\n";
$updatedFacility = Facility::findById($newId);

if ($updatedFacility && $updatedFacility->name === 'Updated Test Pool' && $updatedFacility->capacity == 75) {
    echo "   Success: Verified updated data. Name: " . $updatedFacility->name . ", Capacity: " . $updatedFacility->capacity . "\n";
} else {
    echo "   Failure: Facility data was not updated correctly.\n";
    // Clean up before exiting
    Facility::delete($newId);
    exit;
}

// --- DELETE ---
echo "\n5. Testing DELETE...\n";
if (Facility::delete($newId)) {
    echo "   Success: Facility with ID $newId deleted.\n";
} else {
    echo "   Failure: Could not delete facility with ID $newId.\n";
    exit;
}

// --- VERIFY DELETION ---
echo "\n6. Verifying DELETION...\n";
$deletedFacility = Facility::findById($newId);

if ($deletedFacility === null) {
    echo "   Success: Facility with ID $newId is confirmed deleted.\n";
} else {
    echo "   Failure: Facility with ID $newId was not properly deleted.\n";
    exit;
}

echo "\n--- Facility Model CRUD Test Completed Successfully! ---\n";

?>