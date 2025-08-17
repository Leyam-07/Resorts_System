<?php

require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Booking.php';
require_once __DIR__ . '/../Models/User.php';

class PaymentController {

    public function __construct() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
            header('Location: /?controller=user&action=login&error=unauthorized');
            exit();
        }
    }

    public function index() {
        // This will be used to list all bookings with payment info
        // For now, let's redirect to the admin dashboard
        header('Location: /?controller=admin&action=dashboard');
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
            header('Location: /?controller=admin&action=dashboard');
            exit();
        }

        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $paymentMethod = filter_input(INPUT_POST, 'payment_method', FILTER_UNSAFE_RAW);
        $status = filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW);

        if (!$bookingId || !$amount || !$paymentMethod || !$status) {
            header('Location: /?controller=payment&action=manage&booking_id=' . $bookingId . '&error=invalid_input');
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
            header('Location: /?controller=payment&action=manage&booking_id=' . $bookingId . '&status=payment_added');
        } else {
            header('Location: /?controller=payment&action=manage&booking_id=' . $bookingId . '&error=add_failed');
        }
        exit();
    }

    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?controller=admin&action=dashboard');
            exit();
        }

        $paymentId = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW);

        if (!$paymentId || !$bookingId || !$status) {
            header('Location: /?controller=payment&action=manage&booking_id=' . $bookingId . '&error=invalid_input');
            exit();
        }

        if (Payment::updateStatus($paymentId, $status)) {
            header('Location: /?controller=payment&action=manage&booking_id=' . $bookingId . '&status=status_updated');
        } else {
            header('Location: /?controller=payment&action=manage&booking_id=' . $bookingId . '&error=update_failed');
        }
        exit();
    }
}