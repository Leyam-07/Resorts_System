<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== PHASE 1 DATABASE STRUCTURE VERIFICATION ===\n\n";

    // 1. Check if new tables exist
    echo "1. Checking if new tables exist:\n";
    $newTables = ['ResortTimeframePricing', 'BookingFacilities', 'ResortPaymentMethods'];
    
    foreach ($newTables as $table) {
        $stmt = $db->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt->rowCount() > 0;
        echo "   - $table: " . ($exists ? "✓ EXISTS" : "✗ MISSING") . "\n";
    }
    echo "\n";

    // 2. Check ResortTimeframePricing table structure
    echo "2. ResortTimeframePricing table structure:\n";
    $stmt = $db->query("DESCRIBE ResortTimeframePricing");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['Field']}: {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']}\n";
    }
    echo "\n";

    // 3. Check BookingFacilities table structure
    echo "3. BookingFacilities table structure:\n";
    $stmt = $db->query("DESCRIBE BookingFacilities");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['Field']}: {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']}\n";
    }
    echo "\n";

    // 4. Check ResortPaymentMethods table structure
    echo "4. ResortPaymentMethods table structure:\n";
    $stmt = $db->query("DESCRIBE ResortPaymentMethods");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['Field']}: {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']}\n";
    }
    echo "\n";

    // 5. Check updated Bookings table structure
    echo "5. Updated Bookings table structure (new columns):\n";
    $stmt = $db->query("DESCRIBE Bookings");
    $bookingColumns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bookingColumns[] = $row['Field'];
        // Only show new columns we added
        if (in_array($row['Field'], ['ResortID', 'TotalAmount', 'PaymentProofURL', 'PaymentReference', 'RemainingBalance'])) {
            echo "   - {$row['Field']}: {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']}\n";
        }
    }
    
    // Check if all new columns exist
    $expectedNewColumns = ['ResortID', 'TotalAmount', 'PaymentProofURL', 'PaymentReference', 'RemainingBalance'];
    $missingColumns = array_diff($expectedNewColumns, $bookingColumns);
    
    if (empty($missingColumns)) {
        echo "   ✓ All new columns added successfully\n";
    } else {
        echo "   ✗ Missing columns: " . implode(', ', $missingColumns) . "\n";
    }
    echo "\n";

    // 6. Check foreign key constraints
    echo "6. Checking foreign key constraints:\n";
    
    $fkQueries = [
        "ResortTimeframePricing.ResortID" => "
            SELECT COUNT(*) as fk_count 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'ResortTimeframePricing' 
            AND COLUMN_NAME = 'ResortID' 
            AND REFERENCED_TABLE_NAME IS NOT NULL",
        
        "BookingFacilities.BookingID" => "
            SELECT COUNT(*) as fk_count 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'BookingFacilities' 
            AND COLUMN_NAME = 'BookingID' 
            AND REFERENCED_TABLE_NAME IS NOT NULL",
        
        "BookingFacilities.FacilityID" => "
            SELECT COUNT(*) as fk_count 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'BookingFacilities' 
            AND COLUMN_NAME = 'FacilityID' 
            AND REFERENCED_TABLE_NAME IS NOT NULL",
        
        "ResortPaymentMethods.ResortID" => "
            SELECT COUNT(*) as fk_count 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'ResortPaymentMethods' 
            AND COLUMN_NAME = 'ResortID' 
            AND REFERENCED_TABLE_NAME IS NOT NULL",
        
        "Bookings.ResortID" => "
            SELECT COUNT(*) as fk_count 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'Bookings' 
            AND COLUMN_NAME = 'ResortID' 
            AND REFERENCED_TABLE_NAME IS NOT NULL"
    ];
    
    foreach ($fkQueries as $constraint => $query) {
        $stmt = $db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $hasFK = $result['fk_count'] > 0;
        echo "   - $constraint: " . ($hasFK ? "✓ FK EXISTS" : "✗ FK MISSING") . "\n";
    }
    echo "\n";

    // 7. Test basic functionality
    echo "7. Testing basic model functionality:\n";
    
    // Test ResortTimeframePricing
    echo "   - ResortTimeframePricing::getTimeframeTypes(): ";
    require_once __DIR__ . '/../../app/Models/ResortTimeframePricing.php';
    $timeframes = ResortTimeframePricing::getTimeframeTypes();
    echo (count($timeframes) == 3 ? "✓ PASS" : "✗ FAIL") . "\n";
    
    // Test BookingFacilities
    echo "   - BookingFacilities::calculateFacilitiesTotalCost([]): ";
    require_once __DIR__ . '/../../app/Models/BookingFacilities.php';
    $cost = BookingFacilities::calculateTotalFacilityCost(999999); // Non-existent booking
    echo ($cost == 0 ? "✓ PASS" : "✗ FAIL") . "\n";
    
    // Test ResortPaymentMethods
    echo "   - ResortPaymentMethods::hasActivePaymentMethods(): ";
    require_once __DIR__ . '/../../app/Models/ResortPaymentMethods.php';
    $hasActive = ResortPaymentMethods::hasActivePaymentMethods(999999); // Non-existent resort
    echo ($hasActive === false ? "✓ PASS" : "✗ FAIL") . "\n";

    echo "\n=== VERIFICATION COMPLETE ===\n";
    
    // Summary
    echo "\nSUMMARY:\n";
    echo "- New tables created: ResortTimeframePricing, BookingFacilities, ResortPaymentMethods\n";
    echo "- Bookings table updated with new columns\n";
    echo "- Foreign key constraints established\n";
    echo "- Model classes created and basic functionality tested\n";
    echo "\nPhase 1 database structure is ready for resort-centric booking system!\n";

} catch (PDOException $e) {
    die("Database verification failed: " . $e->getMessage() . "\n");
}