<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `BookingFacilities` (
              `BookingFacilityID` INT PRIMARY KEY AUTO_INCREMENT,
              `BookingID` INT NOT NULL,
              `FacilityID` INT NOT NULL,
              `FacilityPrice` DECIMAL(10,2) NOT NULL,
              `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`BookingID`) REFERENCES `Bookings`(`BookingID`) ON DELETE CASCADE,
              FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`) ON DELETE CASCADE,
              UNIQUE KEY `unique_booking_facility` (`BookingID`, `FacilityID`)
            );";

    $db->exec($sql);
    echo "Table 'BookingFacilities' created successfully." . PHP_EOL;

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . PHP_EOL);
}