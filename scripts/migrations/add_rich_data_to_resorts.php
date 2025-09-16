<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "ALTER TABLE `Resorts`
            ADD `ShortDescription` TEXT NULL AFTER `ContactPerson`,
            ADD `FullDescription` TEXT NULL AFTER `ShortDescription`,
            ADD `MainPhotoURL` VARCHAR(255) NULL AFTER `FullDescription`";

    $pdo->exec($sql);

    echo "Migration successful: Added rich data columns to Resorts table." . PHP_EOL;

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}