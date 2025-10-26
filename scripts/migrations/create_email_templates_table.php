<?php

require_once __DIR__ . '/../../app/Helpers/Database.php';

// This script creates the EmailTemplates table
echo "Running migration to create EmailTemplates table...\n";

try {
    $db = Database::getInstance();

    $sql = "
    CREATE TABLE IF NOT EXISTS `EmailTemplates` (
      `TemplateID` INT PRIMARY KEY AUTO_INCREMENT,
      `TemplateType` VARCHAR(255) NOT NULL UNIQUE,
      `Subject` VARCHAR(255) NOT NULL,
      `Body` TEXT NOT NULL,
      `IsEnabled` BOOLEAN DEFAULT TRUE,
      `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `UpdatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );
    ";

    $conn->exec($sql);

    echo "EmailTemplates table created successfully.\n";

} catch (PDOException $e) {
    echo "Error creating EmailTemplates table: " . $e->getMessage() . "\n";
}

?>