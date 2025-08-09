<?php
// This script initializes the database by creating the database and tables.

// Include the database configuration file
require_once __DIR__ . '/../config/database.php';

try {
    // 1. Connect to MySQL server (without selecting a database)
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create the database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    echo "Database '" . DB_NAME . "' created or already exists.\n";

    // 3. Re-connect to the specific database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 4. Read the SQL schema from the markdown file
    $schema_content = file_get_contents(__DIR__ . '/../docs/Database-Schema.md');
    
    // Extract SQL blocks from the markdown
    preg_match_all('/```sql\s*([\s\S]+?)\s*```/', $schema_content, $matches);
    $sql_commands = $matches[1];

    // 5. Execute each CREATE TABLE statement
    foreach ($sql_commands as $command) {
        // Clean up the command
        $command = trim($command);
        if (!empty($command)) {
            $pdo->exec($command);
            // Extract table name for logging
            preg_match('/CREATE TABLE `(.*?)`/', $command, $tableNameMatch);
            $tableName = $tableNameMatch[1] ?? 'unknown table';
            echo "Table '" . $tableName . "' created successfully.\n";
        }
    }

    echo "Database initialization completed successfully!\n";

} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage() . "\n");
}