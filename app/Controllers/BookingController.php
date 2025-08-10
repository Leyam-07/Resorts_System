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

        session_start();
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
            // Handle validation error, e.g., redirect back with an error message
            echo "Error: All fields are required.";
            // In a real app, you'd use a more robust notification system
            exit;
        }

        // 3. Advanced Validation
        // Check if the facility exists
        if (!Facility::findById($facilityId)) {
            echo "Error: The selected facility does not exist.";
            exit;
        }

        // Check if the booking date is in the past
        $today = new DateTime();
        $bookingDateTime = new DateTime($bookingDate);
        if ($bookingDateTime < $today) {
            echo "Error: You cannot book a date in the past.";
            exit;
        }

        // 4. Check for booking conflicts
        if (!Booking::isTimeSlotAvailable($facilityId, $bookingDate, $startTime, $endTime)) {
            echo "Error: The selected time slot is no longer available. Please choose a different time.";
            // Redirect back with an error
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
            echo "Booking successful! Your booking ID is " . $bookingId;
            // header('Location: ?action=bookingSuccess&id=' . $bookingId);
        } else {
            // Failure: Redirect back with an error
            echo "Error: Could not save the booking. Please try again.";
        }
    }

    public function showBookingForm() {
        // Fetch all available facilities to pass to the view
        $facilities = Facility::findAll();
        require_once __DIR__ . '/../Views/booking/create.php';
    }

    public function getAvailableSlots() {
        // Logic to get available time slots for a facility
    }

    public function cancelBooking() {
        // Handle booking cancellation
    }
}