<?php
/**
 * Migration: Add Reference column to Payments table
 *
 * This migration adds the Reference VARCHAR field to the Payments table
 * which was added to support payment references in invoice generation.
 */

require_once __DIR__ . '/../../config/database.php';

// Create database connection
try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting migration: Add Reference column to Payments table\n";

    // Check if Reference column already exists
    $stmt = $db->prepare("SHOW COLUMNS FROM Payments LIKE 'Reference'");
    $stmt->execute();
    $columnExists = $stmt->rowCount() > 0;

    if (!$columnExists) {
        echo "Adding Reference column to Payments table...\n";

        // Add the Reference column
        $alterStmt = $db->prepare("
            ALTER TABLE Payments
            ADD COLUMN Reference VARCHAR(100) AFTER ProofOfPaymentURL
        ");

        $alterStmt->execute();

        echo "✓ Successfully added Reference column to Payments table\n";

        // Optional: Update existing payment records with a default reference
        // This will help identify payments that were made without explicit references
        $updateStmt = $db->prepare("
            UPDATE Payments
            SET Reference = CONCAT('AUTO-', PaymentID)
            WHERE Reference IS NULL OR Reference = ''
        ");

        $updateStmt->execute();
        $updatedRecords = $updateStmt->rowCount();

        if ($updatedRecords > 0) {
            echo "✓ Updated {$updatedRecords} existing payment records with auto-generated references\n";
        }

    } else {
        echo "Reference column already exists in Payments table. Skipping migration.\n";
    }

    echo "Migration completed successfully!\n\n";

    echo "Summary:\n";
    echo "- Payments table now has Reference VARCHAR(100) field\n";
    echo "- This supports storing payment references for invoice generation\n";
    echo "- Existing payments updated with auto-generated references\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
