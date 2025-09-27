<?php
echo "Testing Database Connection Fix...\n";

// Test singleton connection
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/Database.php';

try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT COUNT(*) as count FROM Payments WHERE Status = 'Pending'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Database connection works!\n";
    echo "Pending payments: {$result['count']}\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\nTesting Payment model...\n";

// Mock session without starting a new one
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'Admin';

require_once __DIR__ . '/../app/Models/Payment.php';

try {
    $pending = Payment::getPendingPayments();
    echo "✅ Payment::getPendingPayments() works!\n";
    echo "Found " . count($pending) . " pending payments\n";

    if (count($pending) > 0) {
        echo "First payment: ID=" . $pending[0]->PaymentID . ", Booking=" . $pending[0]->BookingID . ", Amount=₱" . $pending[0]->Amount . "\n";

        echo "\nTesting Payment::verifyPayment()...\n";
        try {
            $startTime = microtime(true);
            $result = Payment::verifyPayment($pending[0]->PaymentID, $_SESSION['user_id']);
            $endTime = microtime(true);

            echo "Execution time: " . round(($endTime - $startTime), 2) . " seconds\n";

            if ($result['success']) {
                echo "✅ Payment verification successful!\n";
                echo "New status: {$result['newStatus']}\n";
                echo "New balance: ₱{$result['newBalance']}\n";
            } else {
                echo "❌ Payment verification failed: {$result['error']}\n";
            }
        } catch (Exception $e) {
            echo "❌ verifyPayment exception: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
        }
    } else {
        echo "No pending payments to test with.\n";
    }
} catch (Exception $e) {
    echo "❌ Payment model error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\nTest complete.\n";
?>
