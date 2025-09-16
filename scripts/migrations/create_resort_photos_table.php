<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `ResortPhotos` (
              `PhotoID` INT PRIMARY KEY AUTO_INCREMENT,
              `ResortID` INT,
              `PhotoURL` VARCHAR(255) NOT NULL,
              `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`ResortID`) REFERENCES `Resorts`(`ResortID`) ON DELETE CASCADE
            )";

    $pdo->exec($sql);

    echo "Migration successful: Created ResortPhotos table." . PHP_EOL;

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}