<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Update the Status ENUM to include Phase 3 values
    $sql = "ALTER TABLE `Payments` 
            MODIFY COLUMN `Status` ENUM('Paid', 'Unpaid', 'Partial', 'Pending', 'Verified', 'Rejected') NOT NULL DEFAULT 'Pending'";

    $db->exec($sql);
    echo "Payments table Status column updated successfully for Phase 3." . PHP_EOL;
    
    // Also update PaymentMethod to allow for Phase 3 online payment strings
    $sql2 = "ALTER TABLE `Payments` 
             MODIFY COLUMN `PaymentMethod` VARCHAR(255) NOT NULL";

    $db->exec($sql2);
    echo "Payments table PaymentMethod column updated to VARCHAR for Phase 3." . PHP_EOL;

} catch (PDOException $e) {
    die("Error updating Payments table: " . $e->getMessage());
}
?>