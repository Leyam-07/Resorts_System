<?php

require_once __DIR__ . '/../Models/Feedback.php';
require_once __DIR__ . '/../Models/Booking.php';

class FeedbackController {

    public function showFeedbackForm() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?controller=user&action=login');
            exit;
        }

        $bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
        if (!$bookingId) {
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        $booking = Booking::findById($bookingId);

        // Security check: Ensure the booking belongs to the current user and is completed
        if (!$booking || $booking->customerId != $_SESSION['user_id']) {
            $_SESSION['error_message'] = "You are not authorized to leave feedback for this booking.";
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }
        
        if ($booking->status !== 'Completed') {
            $_SESSION['error_message'] = "You can only leave feedback for completed bookings.";
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        // Check if feedback already exists
        if (Feedback::findByBookingId($bookingId)) {
            $_SESSION['error_message'] = "You have already submitted feedback for this booking.";
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        require_once __DIR__ . '/../Views/feedback/create.php';
    }

    public function submitFeedback() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }

        $bookingId = filter_input(INPUT_POST, 'bookingId', FILTER_VALIDATE_INT);
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$bookingId || !$rating) {
            $_SESSION['error_message'] = "Rating is required.";
            header('Location: ?controller=feedback&action=showFeedbackForm&booking_id=' . $bookingId);
            exit;
        }

        $booking = Booking::findById($bookingId);
        if (!$booking || $booking->customerId != $_SESSION['user_id']) {
            $_SESSION['error_message'] = "Invalid booking.";
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        $feedback = new Feedback();
        $feedback->bookingId = $bookingId;
        $feedback->rating = $rating;
        $feedback->comment = $comment;

        if (Feedback::create($feedback)) {
            $_SESSION['success_message'] = "Thank you for your feedback!";
            header('Location: ?controller=booking&action=showMyBookings');
        } else {
            $_SESSION['error_message'] = "Failed to submit feedback. Please try again.";
            header('Location: ?controller=feedback&action=showFeedbackForm&booking_id=' . $bookingId);
        }
        exit;
    }
    
    public function listAllFeedback() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
            $_SESSION['error_message'] = "You are not authorized to view this page.";
            header('Location: index.php');
            exit;
        }

        $feedbacks = Feedback::findAll();

        require_once __DIR__ . '/../Views/admin/feedback/index.php';
    }
}