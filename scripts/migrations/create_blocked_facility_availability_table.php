<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `BlockedFacilityAvailability` (
              `BlockedAvailabilityID` INT PRIMARY KEY AUTO_INCREMENT,
              `FacilityID` INT,
              `BlockDate` DATE NOT NULL,
              `Reason` VARCHAR(255),
              `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`) ON DELETE CASCADE
            );";

    $db->exec($sql);
    echo "Table 'BlockedFacilityAvailability' created successfully." . PHP_EOL;

} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}