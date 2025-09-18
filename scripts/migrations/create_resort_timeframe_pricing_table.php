<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `ResortTimeframePricing` (
              `PricingID` INT PRIMARY KEY AUTO_INCREMENT,
              `ResortID` INT NOT NULL,
              `TimeframeType` ENUM('12_hours', '24_hours', 'overnight') NOT NULL,
              `BasePrice` DECIMAL(10,2) NOT NULL,
              `WeekendSurcharge` DECIMAL(10,2) DEFAULT 0,
              `HolidaySurcharge` DECIMAL(10,2) DEFAULT 0,
              `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`ResortID`) REFERENCES `Resorts`(`ResortID`) ON DELETE CASCADE,
              UNIQUE KEY `unique_resort_timeframe` (`ResortID`, `TimeframeType`)
            );";

    $db->exec($sql);
    echo "Table 'ResortTimeframePricing' created successfully." . PHP_EOL;

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . PHP_EOL);
}