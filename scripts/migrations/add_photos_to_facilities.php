<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add new columns to Facilities table
    $db->exec("
        ALTER TABLE `Facilities`
        ADD COLUMN `ShortDescription` TEXT,
        ADD COLUMN `FullDescription` TEXT,
        ADD COLUMN `MainPhotoURL` VARCHAR(255);
    ");

    // Create the new FacilityPhotos table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `FacilityPhotos` (
          `PhotoID` INT PRIMARY KEY AUTO_INCREMENT,
          `FacilityID` INT,
          `PhotoURL` VARCHAR(255) NOT NULL,
          `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`) ON DELETE CASCADE
        );
    ");

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}