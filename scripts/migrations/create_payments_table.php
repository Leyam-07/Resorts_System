<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `Payments` (
              `PaymentID` INT PRIMARY KEY AUTO_INCREMENT,
              `BookingID` INT,
              `Amount` DECIMAL(10, 2) NOT NULL,
              `PaymentMethod` ENUM('Gcash', 'Bank Transfer', 'Cash') NOT NULL,
              `PaymentDate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `Status` ENUM('Paid', 'Unpaid', 'Partial') NOT NULL,
              `ProofOfPaymentURL` VARCHAR(255),
              FOREIGN KEY (`BookingID`) REFERENCES `Bookings`(`BookingID`)
            );";

    $db->exec($sql);
    echo "Payments table created successfully." . PHP_EOL;

} catch (PDOException $e) {
    die("Error creating Payments table: " . $e->getMessage());
}