<?php

require_once __DIR__ . '/../../app/Helpers/Database.php';

echo "Starting migration of ResortPaymentMethods table...\n";

try {
    $db = Database::getInstance();

    // Check current structure
    echo "Current table structure:\n";
    $stmt = $db->query("DESCRIBE ResortPaymentMethods");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}";
        if ($column['Null'] === 'NO') echo " NOT NULL";
        if ($column['Default'] !== null) echo " DEFAULT '{$column['Default']}'";
        echo "\n";
    }

    $db->beginTransaction();

    // Step 1: Eliminate duplicates first by deleting conflicting records (keep the first one)
    echo "Eliminating potential duplicates...\n";
    $db->exec("
        CREATE TEMPORARY TABLE temp_methods AS
        SELECT PaymentMethodID, ResortID, MethodName,
               CASE
                   WHEN MethodName = 'GCash' THEN 'Gcash'
                   WHEN MethodName = 'BPI' THEN 'Bank Transfer'
                   WHEN MethodName = 'Cash' THEN 'Cash'
                   ELSE 'Bank Transfer'
               END as new_method_type
        FROM ResortPaymentMethods
    ");

    // Delete duplicates keeping only the first one per resort+methodtype
    $db->exec("
        DELETE pm1 FROM ResortPaymentMethods pm1
        INNER JOIN ResortPaymentMethods pm2
        WHERE pm1.PaymentMethodID > pm2.PaymentMethodID
          AND pm1.ResortID = pm2.ResortID
          AND (
              CASE
                  WHEN pm1.MethodName = 'GCash' THEN 'Gcash'
                  WHEN pm1.MethodName = 'BPI' THEN 'Bank Transfer'
                  WHEN pm1.MethodName = 'Cash' THEN 'Cash'
                  ELSE 'Bank Transfer'
              END
          ) = (
              CASE
                  WHEN pm2.MethodName = 'GCash' THEN 'Gcash'
                  WHEN pm2.MethodName = 'BPI' THEN 'Bank Transfer'
                  WHEN pm2.MethodName = 'Cash' THEN 'Cash'
                  ELSE 'Bank Transfer'
              END
          )
    ");

    // Step 2: Add new columns
    echo "\nAdding new columns...\n";

    // Add MethodType column (ENUM)
    $db->exec("ALTER TABLE ResortPaymentMethods ADD COLUMN MethodType ENUM('Gcash', 'Bank Transfer', 'Cash') AFTER ResortID");

    // Add AccountDetails column
    $db->exec("ALTER TABLE ResortPaymentMethods ADD COLUMN AccountDetails TEXT AFTER MethodType");

    // Add IsDefault column
    $db->exec("ALTER TABLE ResortPaymentMethods ADD COLUMN IsDefault BOOLEAN DEFAULT FALSE AFTER AccountDetails");

    // Add UpdatedAt column
    $db->exec("ALTER TABLE ResortPaymentMethods ADD COLUMN UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER CreatedAt");

    // Step 3: Migrate data from old columns to new columns
    echo "Migrating data...\n";

    // Migrate MethodName to MethodType
    $db->exec("UPDATE ResortPaymentMethods SET MethodType = CASE
        WHEN MethodName = 'GCash' THEN 'Gcash'
        WHEN MethodName = 'BPI' THEN 'Bank Transfer'
        WHEN MethodName = 'Cash' THEN 'Cash'
        ELSE 'Bank Transfer'
    END");

    // Migrate MethodDetails to AccountDetails
    $db->exec("UPDATE ResortPaymentMethods SET AccountDetails = MethodDetails");

    // Step 4: Set default values for IsDefault (first method as default per resort)
    echo "Setting default payment methods...\n";
    $db->exec("UPDATE ResortPaymentMethods SET IsDefault = TRUE WHERE PaymentMethodID IN (
        SELECT MIN(PaymentMethodID) FROM ResortPaymentMethods GROUP BY ResortID
    )");

    // Step 5: Drop old columns
    echo "Removing old columns...\n";
    $db->exec("ALTER TABLE ResortPaymentMethods DROP COLUMN MethodName");
    $db->exec("ALTER TABLE ResortPaymentMethods DROP COLUMN MethodDetails");

    // Step 6: Add unique constraint on ResortID + MethodType (after data is clean)
    echo "Adding constraints...\n";
    $db->exec("ALTER TABLE ResortPaymentMethods ADD CONSTRAINT unique_resort_method UNIQUE (ResortID, MethodType)");

    $db->commit();

    echo "\nMigration completed successfully!\n";

    // Verify final structure
    echo "\nFinal table structure:\n";
    $stmt = $db->query("DESCRIBE ResortPaymentMethods");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}";
        if ($column['Null'] === 'NO') echo " NOT NULL";
        if ($column['Default'] !== null) echo " DEFAULT '{$column['Default']}'";
        if ($column['Key']) echo " [{$column['Key']}]";
        echo "\n";
    }

    // Check sample data
    echo "\nSample migrated data:\n";
    $stmt = $db->query("SELECT * FROM ResortPaymentMethods LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "- " . json_encode($row) . "\n";
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
