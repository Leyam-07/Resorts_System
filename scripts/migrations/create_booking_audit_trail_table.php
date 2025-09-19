<?php
/**
 * Phase 6: Create BookingAuditTrail Table Migration
 * Comprehensive audit trail system for tracking all booking modifications
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    CREATE TABLE IF NOT EXISTS `BookingAuditTrail` (
        `AuditID` INT PRIMARY KEY AUTO_INCREMENT,
        `BookingID` INT NOT NULL,
        `UserID` INT NOT NULL,
        `Action` ENUM('CREATE', 'UPDATE', 'DELETE', 'STATUS_CHANGE', 'PAYMENT_UPDATE', 'FACILITY_CHANGE') NOT NULL,
        `FieldName` VARCHAR(100) NOT NULL,
        `OldValue` TEXT NULL,
        `NewValue` TEXT NULL,
        `ChangeReason` TEXT NULL,
        `IPAddress` VARCHAR(45) NULL,
        `UserAgent` TEXT NULL,
        `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`BookingID`) REFERENCES `Bookings`(`BookingID`) ON DELETE CASCADE,
        FOREIGN KEY (`UserID`) REFERENCES `Users`(`UserID`) ON DELETE CASCADE,
        INDEX `idx_booking_id` (`BookingID`),
        INDEX `idx_user_id` (`UserID`),
        INDEX `idx_action` (`Action`),
        INDEX `idx_created_at` (`CreatedAt`),
        INDEX `idx_field_name` (`FieldName`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "✅ BookingAuditTrail table created successfully.\n";

    echo "✅ BookingAuditTrail migration completed successfully.\n";
    echo "📋 Table supports: comprehensive change tracking, user attribution, IP logging, search capabilities\n";

} catch (PDOException $e) {
    echo "❌ Error creating BookingAuditTrail table: " . $e->getMessage() . "\n";
    exit(1);
}
?>