<?php
// This script seeds the database with initial data for testing.

require_once __DIR__ . '/../config/database.php';

try {
    // 1. Connect to the specific database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Seed Resorts
    $stmt = $pdo->prepare("INSERT INTO `Resorts` (`Name`, `Address`, `ContactPerson`) VALUES (?, ?, ?)");
    $stmt->execute(['Test Resort', '123 Test Lane', 'John Doe']);
    $resortId = $pdo->lastInsertId();
    echo "Seeded Resort: Test Resort (ID: $resortId)\n";

    // 3. Seed Facilities
    $facilities = [
        ['resortId' => $resortId, 'name' => 'Main Pool', 'capacity' => 50, 'rate' => 500.00],
        ['resortId' => $resortId, 'name' => 'Villa A', 'capacity' => 10, 'rate' => 150.00],
        ['resortId' => $resortId, 'name' => 'Karaoke Room', 'capacity' => 15, 'rate' => 75.00]
    ];

    $stmt = $pdo->prepare("INSERT INTO `Facilities` (`ResortID`, `Name`, `Capacity`, `Rate`) VALUES (?, ?, ?, ?)");

    foreach ($facilities as $facility) {
        $stmt->execute([$facility['resortId'], $facility['name'], $facility['capacity'], $facility['rate']]);
        echo "Seeded Facility: " . $facility['name'] . "\n";
    }

    echo "Database seeding completed successfully!\n";

} catch (PDOException $e) {
    die("Database seeding failed: " . $e->getMessage() . "\n");
}