<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "ALTER TABLE Users ADD COLUMN Notes TEXT";

    $db->exec($sql);

    echo "Migration successful: 'Notes' column added to 'Users' table.";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}