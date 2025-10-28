<?php

require_once __DIR__ . '/../../app/Helpers/Database.php';

function run_migration() {
    $db = Database::getInstance();
    $conn = $db;

    try {
        echo "Starting payment methods refactor migration...\n";

        // 1. Add new columns to ResortPaymentMethods table
        echo "Adding AccountName, AccountNumber, QrCodeURL to ResortPaymentMethods...\n";
        $conn->exec("
            ALTER TABLE `ResortPaymentMethods`
            ADD COLUMN `AccountName` VARCHAR(255) NULL AFTER `MethodType`,
            ADD COLUMN `AccountNumber` VARCHAR(50) NULL AFTER `AccountName`,
            ADD COLUMN `QrCodeURL` VARCHAR(255) NULL AFTER `AccountNumber`;
        ");
        echo "Columns added successfully.\n";

        // 2. Attempt to parse existing AccountDetails into new columns
        echo "Parsing existing AccountDetails...\n";
        $stmt = $conn->query("SELECT PaymentMethodID, AccountDetails FROM ResortPaymentMethods WHERE AccountDetails IS NOT NULL AND AccountDetails != ''");
        $methodsToUpdate = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($methodsToUpdate as $method) {
            // Simple parsing logic: "09123456789 - Juan Dela Cruz"
            $details = $method['AccountDetails'];
            $parts = explode('-', $details, 2);
            
            $accountNumber = trim($parts[0]);
            $accountName = isset($parts[1]) ? trim($parts[1]) : 'N/A';

            $updateStmt = $conn->prepare(
                "UPDATE `ResortPaymentMethods` 
                 SET AccountNumber = :accountNumber, AccountName = :accountName 
                 WHERE PaymentMethodID = :id"
            );
            $updateStmt->execute([
                ':accountNumber' => $accountNumber,
                ':accountName' => $accountName,
                ':id' => $method['PaymentMethodID']
            ]);
        }
        echo "Finished parsing " . count($methodsToUpdate) . " records.\n";

        // 3. Drop the old AccountDetails column
        echo "Dropping AccountDetails column...\n";
        $conn->exec("ALTER TABLE `ResortPaymentMethods` DROP COLUMN `AccountDetails`;");
        echo "Column dropped successfully.\n";

        // 4. Standardize MethodType values before changing to ENUM
        echo "Standardizing MethodType values (e.g., 'Gcash' to 'GCash')...\n";
        $conn->exec("UPDATE `ResortPaymentMethods` SET `MethodType` = 'GCash' WHERE LOWER(`MethodType`) = 'gcash';");
        $conn->exec("UPDATE `ResortPaymentMethods` SET `MethodType` = 'Maya' WHERE LOWER(`MethodType`) = 'maya';");
        echo "MethodType values standardized.\n";
        
        // 5. Delete unsupported payment methods
        echo "Deleting unsupported payment methods...\n";
        $deleteStmt = $conn->prepare("DELETE FROM `ResortPaymentMethods` WHERE `MethodType` NOT IN ('GCash', 'Maya', 'Cash')");
        $deleteStmt->execute();
        echo $deleteStmt->rowCount() . " unsupported methods deleted.\n";

        // 6. Modify MethodType column to ENUM
        echo "Changing MethodType column to ENUM('GCash', 'Maya', 'Cash')...\n";
        $conn->exec("
            ALTER TABLE `ResortPaymentMethods`
            MODIFY COLUMN `MethodType` ENUM('GCash', 'Maya', 'Cash') NOT NULL;
        ");
        echo "MethodType column changed successfully.\n";

        // 7. Update the Payments table ENUM
        echo "Updating Payments.PaymentMethod ENUM...\n";
        // First, standardize existing data
        $conn->exec("UPDATE `Payments` SET `PaymentMethod` = 'GCash' WHERE `PaymentMethod` = 'Gcash';");
        // Then, alter the table
        $conn->exec("
            ALTER TABLE `Payments`
            MODIFY COLUMN `PaymentMethod` ENUM('GCash', 'Maya', 'Cash', 'On-Site Payment') NOT NULL;
        ");
        echo "Payments.PaymentMethod ENUM updated successfully.\n";

        echo "Migration completed successfully!\n";

    } catch (Exception $e) {
        echo "Migration failed: " . $e->getMessage() . "\n";
        // It's often useful to see the state before the error
        // var_dump($conn->errorInfo());
        return false;
    }

    return true;
}

// This allows the script to be run from the command line
if (php_sapi_name() === 'cli') {
    run_migration();
}