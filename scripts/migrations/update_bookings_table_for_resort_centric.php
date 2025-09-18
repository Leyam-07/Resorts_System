<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add new columns to Bookings table for resort-centric booking
    $sql = "ALTER TABLE `Bookings`
            ADD `ResortID` INT NULL AFTER `CustomerID`,
            ADD `TotalAmount` DECIMAL(10,2) NULL AFTER `Status`,
            ADD `PaymentProofURL` VARCHAR(255) NULL AFTER `TotalAmount`,
            ADD `PaymentReference` VARCHAR(100) NULL AFTER `PaymentProofURL`,
            ADD `RemainingBalance` DECIMAL(10,2) DEFAULT 0 AFTER `PaymentReference`";

    $db->exec($sql);

    // Add foreign key constraint for ResortID
    $sql2 = "ALTER TABLE `Bookings`
             ADD FOREIGN KEY (`ResortID`) REFERENCES `Resorts`(`ResortID`)";

    $db->exec($sql2);

    echo "Bookings table updated successfully for resort-centric booking." . PHP_EOL;

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . PHP_EOL);
}