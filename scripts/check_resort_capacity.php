<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/Database.php';

$pdo = Database::getInstance();

echo "Checking resort capacities:\n";

$stmt = $pdo->query('SELECT ResortID, Name, Capacity FROM Resorts ORDER BY ResortID');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['ResortID'] . ': ' . $row['Name'] . ' - Capacity: ' . $row['Capacity'] . "\n";
}

echo "\nDone.\n";
