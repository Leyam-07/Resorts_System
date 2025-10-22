<?php

require_once __DIR__ . '/../../app/Helpers/Database.php';

// Get the database connection
try {
    $db = Database::getInstance();
    echo "Successfully connected to the database.\n";

    // SQL statement to add the new column
    $sql = "ALTER TABLE `Bookings` ADD `ExpiresAt` DATETIME NULL DEFAULT NULL AFTER `Status`";

    // Execute the SQL statement
    $db->exec($sql);

    echo "Successfully added 'ExpiresAt' column to the 'Bookings' table.\n";

} catch (PDOException $e) {
    // Handle potential errors, such as connection failure or SQL errors
    die("Error applying migration: " . $e->getMessage() . "\n");
}

?>