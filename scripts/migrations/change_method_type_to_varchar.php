<?php

require_once __DIR__ . '/../../app/Helpers/Database.php';

echo "Changing MethodType column from ENUM to VARCHAR in ResortPaymentMethods table...\n";

try {
    $db = Database::getInstance();

    // Check current structure
    echo "Current table structure:\n";
    $stmt = $db->query("DESCRIBE ResortPaymentMethods");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        if ($column['Field'] === 'MethodType') {
            echo "- MethodType: {$column['Type']}\n";
            break;
        }
    }

    $db->beginTransaction();

    // Change MethodType from ENUM to VARCHAR
    echo "Converting MethodType column to VARCHAR(100)...\n";
    $db->exec("ALTER TABLE ResortPaymentMethods MODIFY COLUMN MethodType VARCHAR(100) NOT NULL");

    // Since we're making it free-form, we can remove the unique constraint on ResortID+MethodType
    // and replace it with a constraint that allows duplicate method types per resort
    echo "Updating constraints since payment method names can now be duplicated...\n";
    try {
        $db->exec("ALTER TABLE ResortPaymentMethods DROP CONSTRAINT unique_resort_method");
    } catch (Exception $e) {
        // Constraint might not exist, continue
    }

    try {
        $db->exec("ALTER TABLE ResortPaymentMethods DROP INDEX unique_resort_method");
    } catch (Exception $e) {
        // Index might not exist, continue
    }

    // Add back without uniqueness (admins might want to have multiple entries for same method type)
    // Actually, let's not add any constraint for now since data integrity can be handled in the application

    $db->commit();

    echo "\nColumn converted successfully!\n";

    // Verify final structure
    echo "\nFinal table structure:\n";
    $stmt = $db->query("DESCRIBE ResortPaymentMethods");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        if ($column['Field'] === 'MethodType') {
            echo "- MethodType: {$column['Type']}\n";
            break;
        }
    }

    // Show sample data
    echo "\nSample data:\n";
    $stmt = $db->query("SELECT MethodType, COUNT(*) as count FROM ResortPaymentMethods GROUP BY MethodType");
    $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($methods as $method) {
        echo "- {$method['MethodType']}: {$method['count']} records\n";
    }

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";

    try {
        $db->rollBack();
        echo "Transaction rolled back.\n";
    } catch (Exception $rollbackEx) {
        echo "Rollback failed: " . $rollbackEx->getMessage() . "\n";
    }
}
