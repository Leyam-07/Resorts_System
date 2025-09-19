<?php
/**
 * Debug BookingAuditTrail Issue - Phase 6
 * Comprehensive analysis of the audit trail problem
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/BookingAuditTrail.php';
require_once __DIR__ . '/../../app/Models/Booking.php';

echo "=== DEBUGGING BOOKINGAUDITTRAIL ISSUE ===\n\n";

try {
    // 1. Test database connection
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n";
    
    // 2. Check if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'BookingAuditTrail'");
    if ($stmt->rowCount() > 0) {
        echo "✅ BookingAuditTrail table exists\n";
    } else {
        echo "❌ BookingAuditTrail table missing\n";
        exit(1);
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'Bookings'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Bookings table exists\n";
    } else {
        echo "❌ Bookings table missing\n";
        exit(1);
    }
    
    // 3. Check existing bookings
    $stmt = $pdo->query("SELECT BookingID, CustomerID, Status FROM Bookings ORDER BY BookingID DESC LIMIT 5");
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n📋 Recent Bookings:\n";
    foreach ($bookings as $booking) {
        echo "  BookingID: {$booking['BookingID']}, CustomerID: {$booking['CustomerID']}, Status: {$booking['Status']}\n";
    }
    
    if (empty($bookings)) {
        echo "❌ No bookings found in database\n";
        exit(1);
    }
    
    // Use the most recent booking for testing
    $testBookingId = $bookings[0]['BookingID'];
    $testUserId = $bookings[0]['CustomerID'];
    
    echo "\n🎯 Using BookingID: $testBookingId, UserID: $testUserId for testing\n";
    
    // 4. Test simple logChange method step by step
    echo "\n=== Testing Individual logChange Method ===\n";
    
    // Test 1: Simple manual insert
    echo "Test 1: Manual SQL insert to verify table structure...\n";
    try {
        $stmt = $pdo->prepare("
            INSERT INTO BookingAuditTrail (BookingID, UserID, Action, FieldName, OldValue, NewValue, ChangeReason, IPAddress, UserAgent)
            VALUES (?, ?, 'TEST', 'TestField', 'OldVal', 'NewVal', 'Manual test', '127.0.0.1', 'Test Script')
        ");
        $stmt->execute([$testBookingId, $testUserId]);
        echo "✅ Manual SQL insert successful\n";
        
        // Clean up
        $pdo->prepare("DELETE FROM BookingAuditTrail WHERE Action = 'TEST'")->execute();
    } catch (Exception $e) {
        echo "❌ Manual SQL insert failed: " . $e->getMessage() . "\n";
    }
    
    // Test 2: BookingAuditTrail::logChange method
    echo "\nTest 2: Using BookingAuditTrail::logChange method...\n";
    try {
        $result = BookingAuditTrail::logChange($testBookingId, $testUserId, 'CREATE', 'TestField', 'OldValue', 'NewValue', 'Method test');
        if ($result) {
            echo "✅ BookingAuditTrail::logChange successful\n";
        } else {
            echo "❌ BookingAuditTrail::logChange returned false\n";
        }
        
        // Check if record was created
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM BookingAuditTrail WHERE BookingID = ? AND Action = 'CREATE'");
        $stmt->execute([$testBookingId]);
        $count = $stmt->fetchColumn();
        echo "Records found: $count\n";
        
    } catch (Exception $e) {
        echo "❌ BookingAuditTrail::logChange failed: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    // Test 3: BookingAuditTrail::logBookingCreation method
    echo "\n=== Testing logBookingCreation Method ===\n";
    
    $bookingData = [
        'resortId' => 1,
        'bookingDate' => '2024-12-25',
        'timeSlotType' => '24_hours',
        'numberOfGuests' => 6,
        'totalAmount' => 1500.00
    ];
    
    try {
        echo "Calling logBookingCreation with BookingID: $testBookingId, UserID: $testUserId\n";
        $result = BookingAuditTrail::logBookingCreation($testBookingId, $testUserId, $bookingData);
        
        if ($result) {
            echo "✅ BookingAuditTrail::logBookingCreation successful\n";
        } else {
            echo "❌ BookingAuditTrail::logBookingCreation returned false\n";
        }
        
        // Check how many records were created
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM BookingAuditTrail WHERE BookingID = ?");
        $stmt->execute([$testBookingId]);
        $totalCount = $stmt->fetchColumn();
        echo "Total audit records for BookingID $testBookingId: $totalCount\n";
        
        // Show recent audit records
        $stmt = $pdo->prepare("
            SELECT Action, FieldName, OldValue, NewValue, CreatedAt 
            FROM BookingAuditTrail 
            WHERE BookingID = ? 
            ORDER BY CreatedAt DESC 
            LIMIT 10
        ");
        $stmt->execute([$testBookingId]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($records)) {
            echo "\n📝 Recent Audit Records:\n";
            foreach ($records as $record) {
                echo "  {$record['Action']}: {$record['FieldName']} = '{$record['NewValue']}' at {$record['CreatedAt']}\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ BookingAuditTrail::logBookingCreation failed: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    // 5. Debug the logBookingCreation method step by step
    echo "\n=== Debugging logBookingCreation Step by Step ===\n";
    
    // Manually execute what logBookingCreation does
    $changes = [
        'ResortID' => $bookingData['resortId'] ?? null,
        'BookingDate' => $bookingData['bookingDate'] ?? null,
        'TimeSlotType' => $bookingData['timeSlotType'] ?? null,
        'NumberOfGuests' => $bookingData['numberOfGuests'] ?? null,
        'TotalAmount' => $bookingData['totalAmount'] ?? null,
        'Status' => 'Pending'
    ];
    
    echo "Changes to log: " . count($changes) . "\n";
    
    foreach ($changes as $field => $value) {
        echo "Logging change: $field = $value\n";
        try {
            $individualResult = BookingAuditTrail::logChange($testBookingId, $testUserId, 'CREATE', $field, null, $value, 'Step-by-step test');
            if ($individualResult) {
                echo "  ✅ Success for field: $field\n";
            } else {
                echo "  ❌ Failed for field: $field\n";
                break;
            }
        } catch (Exception $e) {
            echo "  ❌ Exception for field $field: " . $e->getMessage() . "\n";
            break;
        }
    }
    
    // Final count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM BookingAuditTrail WHERE BookingID = ?");
    $stmt->execute([$testBookingId]);
    $finalCount = $stmt->fetchColumn();
    echo "\n📊 Final audit record count for BookingID $testBookingId: $finalCount\n";
    
    // 6. Clean up test records
    echo "\n🧹 Cleaning up test records...\n";
    $stmt = $pdo->prepare("DELETE FROM BookingAuditTrail WHERE BookingID = ? AND ChangeReason LIKE '%test%'");
    $stmt->execute([$testBookingId]);
    $deleted = $stmt->rowCount();
    echo "Cleaned up $deleted test records\n";
    
    echo "\n=== DEBUGGING COMPLETE ===\n";
    
    if ($finalCount > 0) {
        echo "✅ CONCLUSION: BookingAuditTrail system is working correctly\n";
        echo "   The issue in the main test might be elsewhere\n";
    } else {
        echo "❌ CONCLUSION: BookingAuditTrail system has issues\n";
        echo "   Need to investigate the logChange method further\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fatal error during debugging: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>