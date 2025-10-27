<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "ALTER TABLE `Users` ADD `Socials` TEXT NULL AFTER `Notes`";

    $pdo->exec($sql);

    echo "Migration successful: 'Socials' column added to 'Users' table.\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}