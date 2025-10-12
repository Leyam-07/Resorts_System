<?php
/**
 * Migration: Remove NumberOfGuests column from Bookings table
 * This migration drops the NumberOfGuests column as it's no longer needed.
 * Resort capacity is maintained for informational purposes only.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/Database.php';

try {
    $pdo = Database::getInstance();

    // Check if column exists before attempting to drop it
    $stmt = $pdo->query("DESCRIBE `Bookings`");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (in_array('NumberOfGuests', $columns)) {
        // Drop the NumberOfGuests column
        $pdo->exec("ALTER TABLE `Bookings` DROP COLUMN `NumberOfGuests`");

        echo "Successfully removed NumberOfGuests column from Bookings table.\n";
    } else {
        echo "NumberOfGuests column does not exist in Bookings table. Skipping...\n";
    }

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
?>
