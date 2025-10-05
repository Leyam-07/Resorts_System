<?php

require_once __DIR__ . '/../../app/Helpers/Database.php';

try {
    $db = Database::getInstance();

    // Check if table exists
    $stmt = $db->query("SHOW TABLES LIKE 'ResortPaymentMethods'");
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        echo "Table ResortPaymentMethods does not exist.\n";
        exit;
    }

    echo "Table ResortPaymentMethods exists.\n";
    echo "\nTable structure:\n";

    // Show table structure
    $stmt = $db->query("DESCRIBE ResortPaymentMethods");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}";
        if ($column['Null'] === 'NO') echo " NOT NULL";
        if ($column['Default'] !== null) echo " DEFAULT '{$column['Default']}'";
        if ($column['Key']) echo " [{$column['Key']}]";
        echo "\n";
    }

    echo "\nTable indexes:\n";
    $stmt = $db->query("SHOW INDEX FROM ResortPaymentMethods");
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($indexes as $index) {
        if ($index['Key_name'] !== 'PRIMARY') {
            echo "- Index: {$index['Key_name']} on {$index['Column_name']}\n";
        }
    }

    echo "\nSample data (first 5 rows):\n";
    $stmt = $db->query("SELECT * FROM ResortPaymentMethods LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "No data in the table.\n";
    } else {
        foreach ($rows as $row) {
            echo "- " . json_encode($row) . "\n";
        }
    }

    echo "\nEnum values for MethodType:\n";
    $stmt = $db->query("SHOW COLUMNS FROM ResortPaymentMethods LIKE 'MethodType'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['Type'])) {
        if (preg_match("/^enum\('(.*)'\)$/", $row['Type'], $matches)) {
            $enumValues = explode("','", $matches[1]);
            echo "- " . implode(', ', $enumValues) . "\n";
        } else {
            echo "- Not an ENUM type: {$row['Type']}\n";
        }
    } else {
        echo "- MethodType column not found.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
