<?php

require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Booking.php';
require_once __DIR__ . '/../Models/User.php';

class PaymentController {

    public function __construct() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
            header('Location: index.php?controller=user&action=login&error=unauthorized');
            exit();
        }
    }

    public function index() {
        // This will be used to list all bookings with payment info
        // For now, let's redirect to the admin dashboard
        header('Location: index.php?controller=admin&action=dashboard');
    }

    public function manage() {
        if (!isset($_GET['booking_id'])) {
            die('Booking ID not specified.');
        }
        $bookingId = $_GET['booking_id'];
        $booking = Booking::findById($bookingId);
        if (!$booking) {
            die('Booking not found.');
        }

        $customer = User::findById($booking->customerId);
        $payments = Payment::findByBookingId($bookingId);

        include __DIR__ . '/../Views/admin/payments/manage.php';
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=admin&action=dashboard');
            exit();
        }

        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $paymentMethod = filter_input(INPUT_POST, 'payment_method', FILTER_UNSAFE_RAW);
        $status = filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW);

        if (!$bookingId || !$amount || !$paymentMethod || !$status) {
            header('Location: index.php?controller=payment&action=manage&booking_id=' . $bookingId . '&error=invalid_input');
            exit();
        }

        $payment = new Payment();
        $payment->bookingId = $bookingId;
        $payment->amount = $amount;
        $payment->paymentMethod = $paymentMethod;
        $payment->status = $status;
        
        // For now, we'll leave ProofOfPaymentURL as null
        $payment->proofOfPaymentURL = null;

        if (Payment::create($payment)) {
            // If payment was successful and status is 'Paid', auto-confirm the booking
            if ($status === 'Paid') {
                Booking::updateStatus($bookingId, 'Confirmed');
            }
            header('Location: index.php?controller=payment&action=manage&booking_id=' . $bookingId . '&status=payment_added');
        } else {
            header('Location: index.php?controller=payment&action=manage&booking_id=' . $bookingId . '&error=add_failed');
        }
        exit();
    }

    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=admin&action=dashboard');
            exit();
        }

        $paymentId = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW);

        if (!$paymentId || !$bookingId || !$status) {
            header('Location: index.php?controller=payment&action=manage&booking_id=' . $bookingId . '&error=invalid_input');
            exit();
        }

        if (Payment::updateStatus($paymentId, $status)) {
            // If status is updated to 'Paid', auto-confirm the booking
            if ($status === 'Paid') {
                Booking::updateStatus($bookingId, 'Confirmed');
            }
            header('Location: index.php?controller=payment&action=manage&booking_id=' . $bookingId . '&status=status_updated');
        } else {
            header('Location: index.php?controller=payment&action=manage&booking_id=' . $bookingId . '&error=update_failed');
        }
        exit();
    }

    public function updateBookingStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=admin&action=dashboard');
            exit();
        }

        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW);

        if (!$bookingId || !$status) {
            header('Location: index.php?controller=payment&action=manage&booking_id=' . $bookingId . '&error=invalid_input');
            exit();
        }

        if (Booking::updateStatus($bookingId, $status)) {
            header('Location: index.php?controller=payment&action=manage&booking_id=' . $bookingId . '&status=booking_status_updated');
        } else {
            header('Location: index.php?controller=payment&action=manage&booking_id=' . $bookingId . '&error=booking_update_failed');
        }
        exit();
    }

    /**
     * Show pending payments for admin review
     */
    public function showPendingPayments() {
        require_once __DIR__ . '/../Models/Payment.php';
        
        $pendingPayments = Payment::getPendingPayments();
        
        require_once __DIR__ . '/../Views/admin/payments/pending.php';
    }

    /**
     * Verify a payment
     */
    public function verifyPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=payment&action=showPendingPayments');
            exit();
        }

        $paymentId = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
        if (!$paymentId) {
            $_SESSION['error_message'] = "Invalid payment ID.";
            header('Location: ?controller=payment&action=showPendingPayments');
            exit();
        }

        require_once __DIR__ . '/../Models/Payment.php';
        require_once __DIR__ . '/../Helpers/Notification.php';
        
        if (Payment::verifyPayment($paymentId, $_SESSION['user_id'])) {
            // Payment verification successful - try to send confirmation email
            $payment = Payment::findById($paymentId);
            if ($payment) {
                try {
                    Notification::sendPaymentVerificationConfirmation($payment->bookingId, true);
                } catch (Exception $e) {
                    // Log email failure but don't fail the payment verification
                    error_log("Payment verification email failed: " . $e->getMessage());
                }
            }

            $_SESSION['success_message'] = "Payment verified successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to verify payment.";
        }

        header('Location: ?controller=payment&action=showPendingPayments');
        exit();
    }

    /**
     * Reject a payment
     */
    public function rejectPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=payment&action=showPendingPayments');
            exit();
        }

        $paymentId = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
        $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
        
        if (!$paymentId) {
            $_SESSION['error_message'] = "Invalid payment ID.";
            header('Location: ?controller=payment&action=showPendingPayments');
            exit();
        }

        require_once __DIR__ . '/../Models/Payment.php';
        require_once __DIR__ . '/../Helpers/Notification.php';
        
        if (Payment::rejectPayment($paymentId, $reason)) {
            // Get payment info to send rejection notification
            $payment = Payment::findById($paymentId);
            if ($payment) {
                try {
                    Notification::sendPaymentVerificationConfirmation($payment->bookingId, false);
                } catch (Exception $e) {
                    // Log email failure but don't fail the payment rejection
                    error_log("Payment rejection email failed: " . $e->getMessage());
                }
            }

            $_SESSION['success_message'] = "Payment rejected.";
        } else {
            $_SESSION['error_message'] = "Failed to reject payment.";
        }

        header('Location: ?controller=payment&action=showPendingPayments');
        exit();
    }
}
