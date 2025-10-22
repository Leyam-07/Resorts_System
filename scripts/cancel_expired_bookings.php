<?php

require_once __DIR__ . '/../app/Helpers/Database.php';
require_once __DIR__ . '/../app/Models/Booking.php';
require_once __DIR__ . '/../app/Models/Payment.php';
require_once __DIR__ . '/../app/Models/BookingAuditTrail.php';
require_once __DIR__ . '/../app/Helpers/Notification.php';

function cancelExpiredBookings() {
    $db = Database::getInstance();
    $now = date('Y-m-d H:i:s');

    // Find pending bookings that have expired
    $stmt = $db->prepare(
        "SELECT * FROM Bookings 
         WHERE Status = 'Pending' 
         AND ExpiresAt IS NOT NULL 
         AND ExpiresAt < :now"
    );
    $stmt->bindValue(':now', $now, PDO::PARAM_STR);
    $stmt->execute();
    $expiredBookings = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($expiredBookings)) {
        echo "No expired bookings to cancel.\n";
        return;
    }

    foreach ($expiredBookings as $booking) {
        // Check if any payment has been made
        $totalPaid = Payment::getTotalPaidAmount($booking->BookingID);

        if ($totalPaid > 0) {
            // If a payment was made, just clear the expiration
            Booking::clearExpiration($booking->BookingID);
            echo "Booking #{$booking->BookingID} has payments, expiration cleared.\n";
            continue;
        }

        // No payment made, so cancel the booking
        // No payment made, so cancel the booking
        if (Booking::updateStatus($booking->BookingID, 'Cancelled')) {
            echo "Booking #{$booking->BookingID} has been automatically cancelled due to expiration.\n";

            // Send expiration email
            Notification::sendBookingExpiredNotification($booking->BookingID);
            
            // Log the cancellation in the audit trail
            BookingAuditTrail::logStatusChange(
                $booking->BookingID,
                0, // System user
                'Pending',
                'Cancelled',
"Booking automatically cancelled after 3-hour payment window expired."
            );
        } else {
            error_log("Failed to automatically cancel expired booking #{$booking->BookingID}");
        }
    }
}

echo "Running cancellation script...\n";
cancelExpiredBookings();
echo "Cancellation script finished.\n";

?>