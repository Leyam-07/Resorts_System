<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "ALTER TABLE `Users` ADD `ProfileImageURL` VARCHAR(255) NULL AFTER `PhoneNumber`";

    $db->exec($query);

    echo "Migration successful: Added `ProfileImageURL` to `Users` table.\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}