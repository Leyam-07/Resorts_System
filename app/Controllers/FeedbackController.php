<?php

require_once __DIR__ . '/../Models/Feedback.php';
require_once __DIR__ . '/../Models/Booking.php';

class FeedbackController {


    public function submitFeedback() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: index.php');
            exit;
        }

        \ErrorHandler::log("SubmitFeedback received: " . json_encode($_POST), 'DEBUG');

        $bookingId = filter_input(INPUT_POST, 'bookingId', FILTER_VALIDATE_INT);
        $resortRating = filter_input(INPUT_POST, 'resort_rating', FILTER_VALIDATE_INT);
        $resortComment = filter_input(INPUT_POST, 'resort_comment', FILTER_SANITIZE_SPECIAL_CHARS);
        $facilitiesData = $_POST['facilities'] ?? [];

        \ErrorHandler::log("Filtered data - bookingId: $bookingId, resortRating: $resortRating, facilitiesData: " . json_encode($facilitiesData), 'DEBUG');

        if (!$bookingId || !$resortRating) {
            $_SESSION['error_message'] = "A rating for the resort is required.";
            header('Location: ?controller=booking&action=showMyBookings');
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
        $feedback->rating = $resortRating;
        $feedback->comment = $resortComment;

        // Sanitize and prepare facility feedback data
        $facilityFeedbacks = [];
        if (!empty($facilitiesData)) {
            foreach ($facilitiesData as $facilityId => $data) {
                $facilityFeedbacks[] = [
                    'id' => filter_var($facilityId, FILTER_VALIDATE_INT),
                    'rating' => filter_var($data['rating'], FILTER_VALIDATE_INT),
                    'comment' => filter_var($data['comment'], FILTER_SANITIZE_SPECIAL_CHARS)
                ];
            }
        }

        if (Feedback::createWithFacilities($feedback, $facilityFeedbacks)) {
            $_SESSION['success_message'] = "Thank you for your feedback!";
            header('Location: ?controller=booking&action=showMyBookings');
        } else {
            $_SESSION['error_message'] = "Failed to submit feedback. Please try again.";
            header('Location: ?controller=booking&action=showMyBookings');
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
