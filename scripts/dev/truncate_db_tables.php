<?php

// This script truncates all tables in the database, effectively clearing all data
// while keeping the table schema intact.

require_once __DIR__ . '/../../config/database.php';

try {
    // Connect to the specific database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all table names in the database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "No tables found in database '" . DB_NAME . "'.\n";
    } else {
        // Disable foreign key checks temporarily to allow truncation of tables
        // with foreign key constraints.
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

        foreach ($tables as $table) {
            $pdo->exec("TRUNCATE TABLE `" . $table . "`");
            echo "Table '" . $table . "' truncated successfully.\n";
        }

        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        echo "All tables in database '" . DB_NAME . "' truncated successfully.\n";
    }

} catch (PDOException $e) {
    die("Database truncation failed: " . $e->getMessage() . "\n");
}

?>