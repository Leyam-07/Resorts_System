<?php

require_once __DIR__ . '/../../app/Helpers/Database.php';

echo "Running migration to add UseCustom to EmailTemplates table...\n";

try {
    $db = Database::getInstance();
    $conn = $db;

    $sql = "ALTER TABLE `EmailTemplates` ADD `UseCustom` BOOLEAN NOT NULL DEFAULT FALSE AFTER `Body`;";

    $conn->exec($sql);

    echo "Column UseCustom added successfully.\n";

} catch (PDOException $e) {
    // Check if the error is "duplicate column"
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column UseCustom already exists.\n";
    } else {
        echo "Error adding column UseCustom: " . $e->getMessage() . "\n";
    }
}

?>