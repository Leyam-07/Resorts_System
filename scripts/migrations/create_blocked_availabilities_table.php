<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `BlockedAvailabilities` (
        `BlockedAvailabilityID` INT PRIMARY KEY AUTO_INCREMENT,
        `FacilityID` INT,
        `BlockDate` DATE NOT NULL,
        `StartTime` TIME NOT NULL,
        `EndTime` TIME NOT NULL,
        `Reason` VARCHAR(255),
        `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`)
    );";

    $db->exec($sql);
    echo "Table `BlockedAvailabilities` created successfully.\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}