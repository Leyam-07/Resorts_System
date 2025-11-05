<?php
/**
 * Migration: Add AdminType column to Users table for sub-admin roles
 * 
 * This migration adds support for modular admin roles:
 * - Admin: Main Admin (System Admin) with full access
 * - BookingAdmin: Manages bookings and payments
 * - OperationsAdmin: Manages pricing, blocking, and facilities
 * - ReportsAdmin: Read-only access to reports and analytics
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Adding AdminType column to Users table...\n";

    // Add AdminType column with default NULL for non-admin users
    $sql = "ALTER TABLE Users 
            ADD COLUMN AdminType ENUM('Admin', 'BookingAdmin', 'OperationsAdmin', 'ReportsAdmin') NULL DEFAULT NULL 
            AFTER Role";
    
    $pdo->exec($sql);
    echo "✓ AdminType column added successfully.\n";

    // Set existing Admin users to 'Admin' (Main Admin)
    $updateSql = "UPDATE Users SET AdminType = 'Admin' WHERE Role = 'Admin'";
    $pdo->exec($updateSql);
    echo "✓ Existing Admin users updated to Main Admin (AdminType='Admin').\n";

    echo "\nMigration completed successfully!\n";
    echo "\nAdmin Role Structure:\n";
    echo "- Role='Admin' + AdminType='Admin' = Main Admin (System Admin)\n";
    echo "- Role='Admin' + AdminType='BookingAdmin' = Booking Admin\n";
    echo "- Role='Admin' + AdminType='OperationsAdmin' = Operations Admin\n";
    echo "- Role='Admin' + AdminType='ReportsAdmin' = Reports Admin\n";
    echo "- Role='Staff' = Staff (no AdminType)\n";
    echo "- Role='Customer' = Customer (no AdminType)\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}