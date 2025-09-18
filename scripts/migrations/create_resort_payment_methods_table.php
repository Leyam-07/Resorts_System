<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `ResortPaymentMethods` (
              `PaymentMethodID` INT PRIMARY KEY AUTO_INCREMENT,
              `ResortID` INT NOT NULL,
              `MethodName` VARCHAR(100) NOT NULL,
              `MethodDetails` TEXT,
              `IsActive` BOOLEAN DEFAULT TRUE,
              `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`ResortID`) REFERENCES `Resorts`(`ResortID`) ON DELETE CASCADE
            );";

    $db->exec($sql);
    echo "Table 'ResortPaymentMethods' created successfully." . PHP_EOL;

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . PHP_EOL);
}