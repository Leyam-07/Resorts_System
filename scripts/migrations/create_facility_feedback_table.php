<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `FacilityFeedback` (
              `FacilityFeedbackID` INT PRIMARY KEY AUTO_INCREMENT,
              `FeedbackID` INT,
              `FacilityID` INT,
              `Rating` INT NOT NULL CHECK (`Rating` >= 1 AND `Rating` <= 5),
              `Comment` TEXT,
              `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`FeedbackID`) REFERENCES `Feedback`(`FeedbackID`) ON DELETE CASCADE,
              FOREIGN KEY (`FacilityID`) REFERENCES `Facilities`(`FacilityID`) ON DELETE CASCADE
            );";

    $db->exec($sql);
    echo "Table 'FacilityFeedback' created successfully." . PHP_EOL;

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . PHP_EOL);
}