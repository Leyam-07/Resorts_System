<?php

require_once __DIR__ . '/../../app/Helpers/Database.php';

echo "Starting cleanup of ResortPaymentMethods table...\n";

try {
    $db = Database::getInstance();

    // Check if table exists and what state it's in
    $stmt = $db->query("SHOW TABLES LIKE 'ResortPaymentMethods'");
    if ($stmt->rowCount() == 0) {
        echo "Table doesn't exist. Creating from scratch.\n";

        // Create the table properly
        $sql = "
            CREATE TABLE IF NOT EXISTS ResortPaymentMethods (
              PaymentMethodID INT PRIMARY KEY AUTO_INCREMENT,
              ResortID INT NOT NULL,
              MethodType ENUM('Gcash', 'Bank Transfer', 'Cash') NOT NULL,
              AccountDetails TEXT,
              IsDefault BOOLEAN DEFAULT FALSE,
              IsActive BOOLEAN DEFAULT TRUE,
              CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              FOREIGN KEY (ResortID) REFERENCES Resorts(ResortID) ON DELETE CASCADE,
              UNIQUE KEY unique_resort_method (ResortID, MethodType)
            );
        ";

        $db->exec($sql);
        echo "Created table with correct structure.\n";
        exit;
    }

    // Table exists, check its structure
    echo "Table exists. Analyzing current structure...\n";
    $stmt = $db->query("DESCRIBE ResortPaymentMethods");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hasMethodType = false;
    $hasAccountDetails = false;
    $hasIsDefault = false;
    $hasMethodName = false;
    $hasMethodDetails = false;
    $hasUpdatedAt = false;

    foreach ($columns as $column) {
        if ($column['Field'] === 'MethodType') $hasMethodType = true;
        if ($column['Field'] === 'AccountDetails') $hasAccountDetails = true;
        if ($column['Field'] === 'IsDefault') $hasIsDefault = true;
        if ($column['Field'] === 'MethodName') $hasMethodName = true;
        if ($column['Field'] === 'MethodDetails') $hasMethodDetails = true;
        if ($column['Field'] === 'UpdatedAt') $hasUpdatedAt = true;
    }

    $db->beginTransaction();

    // Step 1: Remove any existing constraints
    echo "Removing existing constraints...\n";
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

    // Step 2: Add missing new columns if needed
    if (!$hasMethodType) {
        echo "Adding MethodType column...\n";
        $db->exec("ALTER TABLE ResortPaymentMethods ADD COLUMN MethodType ENUM('Gcash', 'Bank Transfer', 'Cash') AFTER ResortID");
    }

    if (!$hasAccountDetails) {
        echo "Adding AccountDetails column...\n";
        $db->exec("ALTER TABLE ResortPaymentMethods ADD COLUMN AccountDetails TEXT AFTER MethodType");
    }

    if (!$hasIsDefault) {
        echo "Adding IsDefault column...\n";
        $db->exec("ALTER TABLE ResortPaymentMethods ADD COLUMN IsDefault BOOLEAN DEFAULT FALSE AFTER AccountDetails");
    }

    if (!$hasUpdatedAt) {
        echo "Adding UpdatedAt column...\n";
        $db->exec("ALTER TABLE ResortPaymentMethods ADD COLUMN UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER CreatedAt");
    }

    // Step 3: Migrate data if old columns exist
    if ($hasMethodName) {
        echo "Migrating old data...\n";

        // Map old values to new ones
        $db->exec("UPDATE ResortPaymentMethods SET MethodType = CASE
            WHEN MethodName = 'GCash' THEN 'Gcash'
            WHEN MethodName = 'BPI' THEN 'Bank Transfer'
            WHEN MethodName = 'Cash' THEN 'Cash'
            ELSE 'Bank Transfer'
        END WHERE MethodName IS NOT NULL");

        if ($hasMethodDetails) {
            $db->exec("UPDATE ResortPaymentMethods SET AccountDetails = MethodDetails WHERE MethodDetails IS NOT NULL");
        }
    }

    // Step 4: Set default values
    echo "Setting default payment methods...\n";
    $db->exec("UPDATE ResortPaymentMethods SET IsDefault = TRUE WHERE PaymentMethodID IN (
        SELECT MIN(PaymentMethodID) FROM ResortPaymentMethods GROUP BY ResortID
    )");

    // Step 5: Remove old columns
    if ($hasMethodName) {
        echo "Removing MethodName column...\n";
        $db->exec("ALTER TABLE ResortPaymentMethods DROP COLUMN MethodName");
    }

    if ($hasMethodDetails) {
        echo "Removing MethodDetails column...\n";
        $db->exec("ALTER TABLE ResortPaymentMethods DROP COLUMN MethodDetails");
    }

    // Step 6: Add the unique constraint
    echo "Adding unique constraint...\n";
    $db->exec("ALTER TABLE ResortPaymentMethods ADD CONSTRAINT unique_resort_method UNIQUE (ResortID, MethodType)");

    $db->commit();

    echo "\nCleanup completed successfully!\n";

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
    echo "\nSample data:\n";
    $stmt = $db->query("SELECT * FROM ResortPaymentMethods LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "- " . json_encode($row) . "\n";
    }

} catch (Exception $e) {
    echo "Cleanup failed: " . $e->getMessage() . "\n";

    try {
        $db->rollBack();
        echo "Transaction rolled back.\n";
    } catch (Exception $rollbackEx) {
        echo "Rollback failed: " . $rollbackEx->getMessage() . "\n";
    }
}
