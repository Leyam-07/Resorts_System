<?php

require_once __DIR__ . '/../Models/Booking.php';
require_once __DIR__ . '/../Models/Facility.php';

class BookingController {

    public function __construct() {
        // Initialize database connection
    }

    public function createBooking() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Only allow POST requests
            header('Location: ?action=showBookingForm');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            // Redirect to login if not logged in
            header('Location: ?controller=user&action=login');
            exit;
        }

        // 1. Sanitize and retrieve form data
        $facilityId = filter_input(INPUT_POST, 'facilityId', FILTER_VALIDATE_INT);
        $bookingDate = filter_input(INPUT_POST, 'bookingDate', FILTER_SANITIZE_STRING);
        $startTime = filter_input(INPUT_POST, 'startTime', FILTER_SANITIZE_STRING);
        $endTime = filter_input(INPUT_POST, 'endTime', FILTER_SANITIZE_STRING);
        $numberOfGuests = filter_input(INPUT_POST, 'numberOfGuests', FILTER_VALIDATE_INT);
        $customerId = $_SESSION['user_id'];

        // 2. Basic Validation
        if (!$facilityId || !$bookingDate || !$startTime || !$endTime || !$numberOfGuests) {
            $_SESSION['error_message'] = "All fields are required.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // 3. Advanced Validation
        // Check if the facility exists
        if (!Facility::findById($facilityId)) {
            $_SESSION['error_message'] = "The selected facility does not exist.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // Check if the booking date is in the past
        $today = new DateTime();
        $bookingDateTime = new DateTime($bookingDate);
        if ($bookingDateTime < $today) {
            $_SESSION['error_message'] = "You cannot book a date in the past.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // 4. Check for booking conflicts
        if (!Booking::isTimeSlotAvailable($facilityId, $bookingDate, $startTime, $endTime)) {
            $_SESSION['error_message'] = "The selected time slot is no longer available. Please choose a different time.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // 4. Create Booking object
        $booking = new Booking();
        $booking->customerId = $customerId;
        $booking->facilityId = $facilityId;
        $booking->bookingDate = $bookingDate;
        $booking->startTime = $startTime;
        $booking->endTime = $endTime;
        $booking->numberOfGuests = $numberOfGuests;
        $booking->status = 'Pending'; // Default status

        // 5. Save to database
        $bookingId = Booking::create($booking);

        if ($bookingId) {
            // Success: Redirect to a confirmation page
            header('Location: ?controller=booking&action=bookingSuccess&id=' . $bookingId);
        } else {
            // Failure: Redirect back with an error
            $_SESSION['error_message'] = "Could not save the booking. Please try again.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }
    }

    public function showBookingForm() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?controller=user&action=login');
            exit;
        }

        // Fetch all available facilities to pass to the view
        $facilities = Facility::findAll();

        // Check for error messages and old input from session
        $errorMessage = $_SESSION['error_message'] ?? null;
        $oldInput = $_SESSION['old_input'] ?? [];

        // Unset them so they don't persist on refresh
        unset($_SESSION['error_message']);
        unset($_SESSION['old_input']);

        require_once __DIR__ . '/../Views/booking/create.php';
    }

    public function bookingSuccess() {
        require_once __DIR__ . '/../Views/booking/success.php';
    }

    public function getAvailableSlots() {
        // Logic to get available time slots for a facility
    }

    public function cancelBooking() {
        // Handle booking cancellation
    }
}