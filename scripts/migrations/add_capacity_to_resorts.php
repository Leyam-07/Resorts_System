<?php

require_once __DIR__ . '/../../app/Helpers/Database.php';

$pdo = Database::getInstance();

$sql = <<<SQL
ALTER TABLE Resorts ADD COLUMN Capacity INT NOT NULL DEFAULT 25 AFTER FullDescription;
SQL;

try {
    $pdo->exec($sql);
    echo "Migration successful: Added 'Capacity' column to 'Resorts' table.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}