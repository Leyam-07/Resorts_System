<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `FeedbackMedia` (
              `MediaID` INT PRIMARY KEY AUTO_INCREMENT,
              `FeedbackID` INT,
              `MediaType` ENUM('Image', 'Video') NOT NULL,
              `MediaURL` VARCHAR(255) NOT NULL,
              `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`FeedbackID`) REFERENCES `Feedback`(`FeedbackID`) ON DELETE CASCADE
            );";

    $db->exec($sql);
    echo "Table `FeedbackMedia` created successfully." . PHP_EOL;

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . PHP_EOL);
}