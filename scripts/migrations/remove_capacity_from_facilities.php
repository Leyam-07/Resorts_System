<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/Database.php';

$pdo = Database::getInstance();

$sql = "ALTER TABLE Facilities DROP COLUMN Capacity";

try {
    $pdo->exec($sql);
    echo "Migration successful: 'Capacity' column removed from 'Facilities' table." . PHP_EOL;
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . PHP_EOL;
}