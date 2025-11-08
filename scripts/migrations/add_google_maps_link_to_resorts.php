<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "ALTER TABLE `Resorts` ADD `GoogleMapsLink` TEXT NULL AFTER `MainPhotoURL`";

    $db->exec($sql);

    echo "Migration successful: Added 'GoogleMapsLink' column to 'Resorts' table.\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}