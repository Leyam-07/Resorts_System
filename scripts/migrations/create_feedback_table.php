<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `Feedback` (
              `FeedbackID` INT PRIMARY KEY AUTO_INCREMENT,
              `BookingID` INT,
              `Rating` INT CHECK (`Rating` >= 1 AND `Rating` <= 5),
              `Comment` TEXT,
              `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`BookingID`) REFERENCES `Bookings`(`BookingID`)
            );";

    $db->exec($sql);
    echo "Table 'Feedback' created successfully." . PHP_EOL;

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . PHP_EOL);
}