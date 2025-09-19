<?php
/**
 * Debug BookingAuditTrail functionality
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/BookingAuditTrail.php';

echo "Testing BookingAuditTrail functionality...\n";

try {
    // Test database connection first
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n";
    
    // Check if BookingAuditTrail table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'BookingAuditTrail'");
    if ($stmt->rowCount() > 0) {
        echo "✅ BookingAuditTrail table exists\n";
    } else {
        echo "❌ BookingAuditTrail table does not exist\n";
        exit(1);
    }
    
    // Test simple logChange method
    echo "Testing BookingAuditTrail::logChange()...\n";
    $result = BookingAuditTrail::logChange(999, 3, 'TEST', 'TestField', 'OldValue', 'NewValue', 'Test reason');
    
    if ($result) {
        echo "✅ BookingAuditTrail::logChange() successful\n";
    } else {
        echo "❌ BookingAuditTrail::logChange() failed\n";
    }
    
    // Test logBookingCreation
    echo "Testing BookingAuditTrail::logBookingCreation()...\n";
    $bookingData = [
        'resortId' => 1,
        'bookingDate' => '2024-12-25',
        'timeSlotType' => '24_hours',
        'numberOfGuests' => 4,
        'totalAmount' => 1500.00
    ];
    
    $result2 = BookingAuditTrail::logBookingCreation(999, 3, $bookingData);
    
    if ($result2) {
        echo "✅ BookingAuditTrail::logBookingCreation() successful\n";
    } else {
        echo "❌ BookingAuditTrail::logBookingCreation() failed\n";
    }
    
    // Check if records were created
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM BookingAuditTrail WHERE BookingID = 999");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    echo "Records created for BookingID 999: $count\n";
    
    // Clean up test records
    $stmt = $pdo->prepare("DELETE FROM BookingAuditTrail WHERE BookingID = 999");
    $stmt->execute();
    echo "✅ Test records cleaned up\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>