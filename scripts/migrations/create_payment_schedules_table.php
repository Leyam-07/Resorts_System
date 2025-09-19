<?php
/**
 * Phase 6: Create PaymentSchedules Table Migration
 * Supports installment payments and payment scheduling
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    CREATE TABLE IF NOT EXISTS `PaymentSchedules` (
        `ScheduleID` INT PRIMARY KEY AUTO_INCREMENT,
        `BookingID` INT NOT NULL,
        `InstallmentNumber` INT NOT NULL,
        `DueDate` DATE NOT NULL,
        `Amount` DECIMAL(10, 2) NOT NULL,
        `Status` ENUM('Pending', 'Paid', 'Overdue', 'Cancelled') DEFAULT 'Pending',
        `PaymentID` INT NULL,
        `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `UpdatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`BookingID`) REFERENCES `Bookings`(`BookingID`) ON DELETE CASCADE,
        FOREIGN KEY (`PaymentID`) REFERENCES `Payments`(`PaymentID`) ON DELETE SET NULL,
        UNIQUE KEY `unique_booking_installment` (`BookingID`, `InstallmentNumber`),
        INDEX `idx_due_date` (`DueDate`),
        INDEX `idx_status` (`Status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "✅ PaymentSchedules table created successfully.\n";

    // Add some sample data for testing
    $sampleData = "
    -- Sample payment schedules for existing bookings (if any)
    -- This is just for testing purposes
    ";
    
    echo "✅ PaymentSchedules table migration completed successfully.\n";
    echo "📋 Table supports: installment tracking, due dates, payment linking, overdue management\n";

} catch (PDOException $e) {
    echo "❌ Error creating PaymentSchedules table: " . $e->getMessage() . "\n";
    exit(1);
}
?>