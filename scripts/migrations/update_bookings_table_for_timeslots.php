<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to the database successfully.\n";

    // SQL to alter the Bookings table
    $sql = "ALTER TABLE `Bookings`
            DROP COLUMN `StartTime`,
            DROP COLUMN `EndTime`,
            ADD COLUMN `TimeSlotType` ENUM('12_hours', '24_hours', 'overnight') NOT NULL AFTER `BookingDate`;";

    $db->exec($sql);

    echo "Table 'Bookings' updated successfully.\n";

    // Note: You might want to handle existing data here.
    // For this migration, we are assuming the table can be cleared or existing data is not critical.
    // A more complex migration would involve mapping old StartTime/EndTime to the new TimeSlotType.

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}