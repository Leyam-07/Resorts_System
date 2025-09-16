<?php

require_once __DIR__ . '/../Models/Booking.php';
require_once __DIR__ . '/../Models/Facility.php';
require_once __DIR__ . '/../Helpers/Notification.php';
require_once __DIR__ . '/../Models/Feedback.php';
require_once __DIR__ . '/../Models/Resort.php';

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

        // Role-based access control: Only Customers can create bookings
        if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Customer') {
            $_SESSION['error_message'] = "You do not have permission to create a booking.";
            header('Location: index.php'); // Redirect to a safe page like the dashboard
            exit;
        }

        // 1. Sanitize and retrieve form data
        $facilityId = filter_input(INPUT_POST, 'facilityId', FILTER_VALIDATE_INT);
        $bookingDate = filter_input(INPUT_POST, 'bookingDate', FILTER_UNSAFE_RAW);
        $timeSlotType = filter_input(INPUT_POST, 'timeSlotType', FILTER_SANITIZE_STRING);
        $numberOfGuests = filter_input(INPUT_POST, 'numberOfGuests', FILTER_VALIDATE_INT);
        $customerId = $_SESSION['user_id'];

        // 2. Basic Validation
        if (!$facilityId || !$bookingDate || !$timeSlotType || !$numberOfGuests) {
            $_SESSION['error_message'] = "All fields are required.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // 3. Advanced Validation
        // Check if the facility exists
        $facility = Facility::findById($facilityId);
        if (!$facility) {
            $_SESSION['error_message'] = "The selected facility does not exist.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // Check if the number of guests exceeds the facility's capacity
        if ($numberOfGuests > $facility->capacity) {
            $_SESSION['error_message'] = "The number of guests (" . $numberOfGuests . ") exceeds the capacity (" . $facility->capacity . ") for this facility.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // Check if the booking date is in the past. Compare date part only.
        $today = new DateTime();
        $today->setTime(0, 0, 0); // Reset time to midnight for accurate date comparison

        $bookingDateTime = new DateTime($bookingDate);
        $bookingDateTime->setTime(0, 0, 0); // Also reset time for the booking date just in case

        if ($bookingDateTime < $today) {
            $_SESSION['error_message'] = "You cannot book a date in the past.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // 4. Check for booking conflicts
        if (!Booking::isTimeSlotAvailable($facilityId, $bookingDate, $timeSlotType)) {
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
        $booking->timeSlotType = $timeSlotType;
        $booking->numberOfGuests = $numberOfGuests;
        $booking->status = 'Pending'; // Default status

        // 5. Save to database
        $bookingId = Booking::create($booking);

        if ($bookingId) {
            // Send confirmation email
            Notification::sendBookingConfirmation($bookingId);

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

        // Role-based access control: Only allow Customers to see this form
        if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Customer') {
            $_SESSION['error_message'] = "You do not have permission to access this page.";
            header('Location: index.php'); // Redirect non-customers away
            exit;
        }

        // Fetch all resorts and their facilities
        $resorts = Resort::findAll();
        $resortsWithFacilities = [];
        foreach ($resorts as $resort) {
            $facilities = Facility::findByResortId($resort->resortId);
            $resortsWithFacilities[] = [
                'resort' => $resort,
                'facilities' => $facilities
            ];
        }

        // Check for error messages and old input from session
        $errorMessage = $_SESSION['error_message'] ?? null;
        $oldInput = $_SESSION['old_input'] ?? [];

        // Check for a pre-selected facility ID from the URL
        $selectedFacilityId = filter_input(INPUT_GET, 'facility_id', FILTER_VALIDATE_INT);

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

    public function showMyBookings() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?controller=user&action=login');
            exit;
        }

        $bookings = Booking::findByCustomerId($_SESSION['user_id']);

        // Check if feedback has been submitted for each completed booking
        if ($bookings) {
            foreach ($bookings as $booking) {
                if ($booking->Status === 'Completed') {
                    $booking->hasFeedback = (Feedback::findByBookingId($booking->BookingID) !== false);
                } else {
                    $booking->hasFeedback = false;
                }
            }
        }

        require_once __DIR__ . '/../Views/booking/my_bookings.php';
    }

    public function cancelBooking() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?controller=user&action=login');
            exit;
        }

        $bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$bookingId) {
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        $booking = Booking::findById($bookingId);

        // First, check if the booking exists at all
        if (!$booking) {
            $_SESSION['error_message'] = "Booking not found.";
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        // Second, check if the booking belongs to the logged-in user
        if ($booking->customerId != $_SESSION['user_id']) {
            $_SESSION['error_message'] = "You are not authorized to cancel this booking.";
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        if (Booking::delete($bookingId)) {
            // Send cancellation email
            Notification::sendBookingCancellation($booking);
            
            $_SESSION['success_message'] = "Booking successfully cancelled.";
        } else {
            $_SESSION['error_message'] = "Failed to cancel the booking. Please try again.";
        }

        header('Location: ?controller=booking&action=showMyBookings');
        exit;
    }
}