<?php

require_once __DIR__ . '/../../app/Helpers/Database.php';

echo "Recreating ResortPaymentMethods table...\n";

try {
    $db = Database::getInstance();

    // First, backup any existing data
    echo "Backing up existing data...\n";
    $backupData = [];
    try {
        $stmt = $db->query("SELECT * FROM ResortPaymentMethods");
        $backupData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "No existing data to backup.\n";
    }

    $db->beginTransaction();

    // Drop existing table
    echo "Dropping existing table...\n";
    $db->exec("DROP TABLE IF EXISTS ResortPaymentMethods");

    // Create table with correct schema
    echo "Creating new table...\n";
    $sql = "
        CREATE TABLE ResortPaymentMethods (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $db->exec($sql);

    // Restore data with proper column names
    echo "Restoring data...\n";
    foreach ($backupData as $row) {
        // Map old data to new schema
        $methodType = 'Bank Transfer'; // Default
        $accountDetails = '';

        if (isset($row['MethodName']) || isset($row['MethodType'])) {
            $methodName = $row['MethodName'] ?? $row['MethodType'] ?? '';
            $accountDetails = $row['MethodDetails'] ?? $row['AccountDetails'] ?? '';

            switch ($methodName) {
                case 'GCash':
                case 'Gcash':
                    $methodType = 'Gcash';
                    break;
                case 'Cash':
                    $methodType = 'Cash';
                    break;
                default:
                    $methodType = 'Bank Transfer';
            }
        }

        $stmt = $db->prepare("
            INSERT INTO ResortPaymentMethods
            (ResortID, MethodType, AccountDetails, IsDefault, IsActive, CreatedAt, UpdatedAt)
            VALUES (:resortId, :methodType, :accountDetails, :isDefault, :isActive, :createdAt, :updatedAt)
        ");

        $stmt->bindValue(':resortId', $row['ResortID']);
        $stmt->bindValue(':methodType', $methodType);
        $stmt->bindValue(':accountDetails', $accountDetails);
        $stmt->bindValue(':isDefault', $row['IsDefault'] ?? false);
        $stmt->bindValue(':isActive', $row['IsActive'] ?? true);
        $stmt->bindValue(':createdAt', $row['CreatedAt']);
        $stmt->bindValue(':updatedAt', $row['UpdatedAt'] ?? $row['CreatedAt']);

        $stmt->execute();
    }

    // If no data was restored, create default payment methods for each resort
    $stmt = $db->query("SELECT COUNT(*) FROM ResortPaymentMethods");
    if ($stmt->fetchColumn() == 0) {
        echo "No data restored. Creating default payment methods for all resorts...\n";

        // Get all resorts
        $stmt = $db->query("SELECT ResortID FROM Resorts");
        $resorts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $defaultMethods = [
            ['type' => 'Gcash', 'details' => 'Send payment to GCash number: [Add GCash number here]'],
            ['type' => 'Bank Transfer', 'details' => 'Transfer to: [Add bank account details here]'],
            ['type' => 'Cash', 'details' => 'Pay cash upon arrival at the resort']
        ];

        foreach ($resorts as $resort) {
            foreach ($defaultMethods as $index => $method) {
                $stmt = $db->prepare("
                    INSERT INTO ResortPaymentMethods
                    (ResortID, MethodType, AccountDetails, IsDefault, IsActive)
                    VALUES (:resortId, :methodType, :accountDetails, :isDefault, :isActive)
                ");

                $stmt->bindValue(':resortId', $resort['ResortID']);
                $stmt->bindValue(':methodType', $method['type']);
                $stmt->bindValue(':accountDetails', $method['details']);
                $stmt->bindValue(':isDefault', $index === 0); // First method is default
                $stmt->bindValue(':isActive', true);

                $stmt->execute();
            }
        }
    } else {
        // Ensure at least one payment method is default per resort
        echo "Ensuring default payment methods are set...\n";
        $db->exec("
            UPDATE ResortPaymentMethods SET IsDefault = TRUE
            WHERE (ResortID, PaymentMethodID) IN (
                SELECT ResortID, MIN(PaymentMethodID)
                FROM ResortPaymentMethods
                GROUP BY ResortID
            )
        ");
    }

    $db->commit();

    echo "\nTable recreated successfully!\n";

    // Verify final structure and data
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

    echo "\nSample data:\n";
    $stmt = $db->query("SELECT * FROM ResortPaymentMethods LIMIT 10");
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
