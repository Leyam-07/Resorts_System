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

        // 1. Sanitize and retrieve form data (Resort-centric approach)
        $resortId = filter_input(INPUT_POST, 'resortId', FILTER_VALIDATE_INT);
        $bookingDate = filter_input(INPUT_POST, 'bookingDate', FILTER_UNSAFE_RAW);
        $timeSlotType = filter_input(INPUT_POST, 'timeSlotType', FILTER_SANITIZE_STRING);
        $numberOfGuests = filter_input(INPUT_POST, 'numberOfGuests', FILTER_VALIDATE_INT);
        $facilityIds = filter_input(INPUT_POST, 'facilityIds', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];
        $customerId = $_SESSION['user_id'];

        // 2. Basic Validation - Resort and timeframe are required, facilities are optional
        if (!$resortId || !$bookingDate || !$timeSlotType || !$numberOfGuests) {
            $_SESSION['error_message'] = "Resort, date, timeframe, and number of guests are required.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // 3. Advanced Validation
        // Check if the resort exists
        require_once __DIR__ . '/../Models/Resort.php';
        $resort = Resort::findById($resortId);
        if (!$resort) {
            $_SESSION['error_message'] = "The selected resort does not exist.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // Validate selected facilities belong to the resort
        if (!empty($facilityIds)) {
            foreach ($facilityIds as $facilityId) {
                $facility = Facility::findById($facilityId);
                if (!$facility || $facility->resortId != $resortId) {
                    $_SESSION['error_message'] = "One or more selected facilities are invalid for this resort.";
                    $_SESSION['old_input'] = $_POST;
                    header('Location: ?controller=booking&action=showBookingForm');
                    exit;
                }

                // Check guest capacity for each facility
                if ($numberOfGuests > $facility->capacity) {
                    $_SESSION['error_message'] = "The number of guests (" . $numberOfGuests . ") exceeds the capacity (" . $facility->capacity . ") for facility: " . htmlspecialchars($facility->name);
                    $_SESSION['old_input'] = $_POST;
                    header('Location: ?controller=booking&action=showBookingForm');
                    exit;
                }
            }
        }

        // Check if the booking date is in the past
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $bookingDateTime = new DateTime($bookingDate);
        $bookingDateTime->setTime(0, 0, 0);

        if ($bookingDateTime < $today) {
            $_SESSION['error_message'] = "You cannot book a date in the past.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // 4. Check for availability (resort + timeframe + optional facilities)
        if (!Booking::isResortTimeframeAvailable($resortId, $bookingDate, $timeSlotType, $facilityIds)) {
            $_SESSION['error_message'] = "The selected date and timeframe is not available. Please choose a different date or time.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // 5. Create resort-centric booking
        $bookingId = Booking::createResortBooking($customerId, $resortId, $bookingDate, $timeSlotType, $numberOfGuests, $facilityIds);

        if ($bookingId) {
            // Send booking confirmation email (before payment)
            Notification::sendBookingConfirmation($bookingId);

            // Redirect to payment submission instead of success page
            header('Location: ?controller=booking&action=showPaymentForm&id=' . $bookingId);
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

    public function getFacilitiesByResort() {
        header('Content-Type: application/json');
        
        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        if (!$resortId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid Resort ID']);
            exit;
        }

        $facilities = Facility::findByResortId($resortId);
        
        // Add pricing information to facilities for display
        foreach ($facilities as &$facility) {
            $facility->priceDisplay = '₱' . number_format($facility->rate, 2);
        }
        
        echo json_encode($facilities);
        exit;
    }

    /**
     * Get pricing information for a resort and timeframe
     */
    public function getResortPricing() {
        header('Content-Type: application/json');
        
        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        $timeframe = filter_input(INPUT_GET, 'timeframe', FILTER_SANITIZE_STRING);
        $date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING);
        
        if (!$resortId || !$timeframe || !$date) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            exit;
        }

        require_once __DIR__ . '/../Models/ResortTimeframePricing.php';
        
        $basePrice = ResortTimeframePricing::calculatePrice($resortId, $timeframe, $date);
        $pricing = ResortTimeframePricing::findByResortAndTimeframe($resortId, $timeframe);
        
        $response = [
            'basePrice' => $basePrice,
            'basePriceDisplay' => '₱' . number_format($basePrice, 2),
            'timeframeDisplay' => ResortTimeframePricing::getTimeframeDisplay($timeframe)
        ];

        if ($pricing) {
            $response['weekendSurcharge'] = $pricing->weekendSurcharge;
            $response['holidaySurcharge'] = $pricing->holidaySurcharge;
            
            // Check if current date is weekend
            $dayOfWeek = date('w', strtotime($date));
            $isWeekend = ($dayOfWeek == 0 || $dayOfWeek == 6);
            $response['isWeekend'] = $isWeekend;
        }
        
        echo json_encode($response);
        exit;
    }

    /**
     * Calculate total booking price dynamically
     */
    public function calculateBookingPrice() {
        header('Content-Type: application/json');
        
        $resortId = filter_input(INPUT_POST, 'resort_id', FILTER_VALIDATE_INT);
        $timeframe = filter_input(INPUT_POST, 'timeframe', FILTER_SANITIZE_STRING);
        $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
        $facilityIds = filter_input(INPUT_POST, 'facility_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];
        
        if (!$resortId || !$timeframe || !$date) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            exit;
        }

        $totalPrice = Booking::calculateBookingTotal($resortId, $timeframe, $date, $facilityIds);
        
        echo json_encode([
            'totalPrice' => $totalPrice,
            'totalPriceDisplay' => '₱' . number_format($totalPrice, 2)
        ]);
        exit;
    }

    /**
     * Check availability for resort + timeframe + facilities
     */
    public function checkAvailability() {
        header('Content-Type: application/json');
        
        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        $date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING);
        $timeframe = filter_input(INPUT_GET, 'timeframe', FILTER_SANITIZE_STRING);
        $facilityIds = filter_input(INPUT_GET, 'facility_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];
        
        if (!$resortId || !$date || !$timeframe) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            exit;
        }

        $isAvailable = Booking::isResortTimeframeAvailable($resortId, $date, $timeframe, $facilityIds);
        
        echo json_encode([
            'available' => $isAvailable,
            'message' => $isAvailable ? 'Available' : 'Not available for selected date and time'
        ]);
        exit;
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

    /**
     * Show payment submission form for a booking
     */
    public function showPaymentForm() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?controller=user&action=login');
            exit;
        }

        $bookingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$bookingId) {
            $_SESSION['error_message'] = "Invalid booking ID.";
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        $booking = Booking::findById($bookingId);
        if (!$booking) {
            $_SESSION['error_message'] = "Booking not found.";
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        // Ensure this booking belongs to the current user
        if ($booking->customerId != $_SESSION['user_id']) {
            $_SESSION['error_message'] = "You are not authorized to access this booking.";
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        // Get resort information and payment methods
        require_once __DIR__ . '/../Models/Resort.php';
        require_once __DIR__ . '/../Models/ResortPaymentMethods.php';
        require_once __DIR__ . '/../Models/BookingFacilities.php';
        
        $resort = Resort::findById($booking->resortId);
        $paymentMethods = ResortPaymentMethods::findByResortId($booking->resortId, true);
        $facilities = BookingFacilities::getFacilitiesForBooking($bookingId);

        // Check for error messages
        $errorMessage = $_SESSION['error_message'] ?? null;
        $successMessage = $_SESSION['success_message'] ?? null;
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        require_once __DIR__ . '/../Views/booking/payment.php';
    }

    /**
     * Process payment submission with proof upload
     */
    public function submitPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ?controller=user&action=login');
            exit;
        }

        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        $amountPaid = filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT);
        $paymentReference = filter_input(INPUT_POST, 'payment_reference', FILTER_SANITIZE_STRING);

        if (!$bookingId || !$amountPaid || !$paymentReference) {
            $_SESSION['error_message'] = "All fields are required.";
            header('Location: ?controller=booking&action=showPaymentForm&id=' . $bookingId);
            exit;
        }

        $booking = Booking::findById($bookingId);
        if (!$booking || $booking->customerId != $_SESSION['user_id']) {
            $_SESSION['error_message'] = "Invalid booking.";
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        // Validate payment amount
        if ($amountPaid <= 0 || $amountPaid > $booking->remainingBalance) {
            $_SESSION['error_message'] = "Invalid payment amount. Please enter an amount between ₱1 and ₱" . number_format($booking->remainingBalance, 2);
            header('Location: ?controller=booking&action=showPaymentForm&id=' . $bookingId);
            exit;
        }

        // Handle file upload
        $paymentProofURL = null;
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $paymentProofURL = $this->handlePaymentProofUpload($_FILES['payment_proof'], $bookingId);
            if (!$paymentProofURL) {
                $_SESSION['error_message'] = "Failed to upload payment proof. Please try again.";
                header('Location: ?controller=booking&action=showPaymentForm&id=' . $bookingId);
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Payment proof is required.";
            header('Location: ?controller=booking&action=showPaymentForm&id=' . $bookingId);
            exit;
        }

        // Update booking with payment information AND create payment record
        require_once __DIR__ . '/../Models/Payment.php';
        
        // Create payment record
        $paymentId = Payment::createFromBookingPayment($bookingId, $amountPaid, $paymentReference, $paymentProofURL);
        
        if ($paymentId && Booking::updatePaymentInfo($bookingId, $paymentProofURL, $paymentReference, $amountPaid)) {
            // Send notification to admin
            $this->notifyAdminPaymentSubmission($bookingId);

            $_SESSION['success_message'] = "Payment submitted successfully! Your payment is being reviewed.";
            header('Location: ?controller=booking&action=paymentSuccess&id=' . $bookingId);
        } else {
            $_SESSION['error_message'] = "Failed to submit payment. Please try again.";
            header('Location: ?controller=booking&action=showPaymentForm&id=' . $bookingId);
        }
        exit;
    }

    /**
     * Handle payment proof file upload
     */
    private function handlePaymentProofUpload($file, $bookingId) {
        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../public/uploads/payment_proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }

        // Generate unique filename
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'payment_' . $bookingId . '_' . uniqid() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return 'public/uploads/payment_proofs/' . $fileName;
        }

        return false;
    }

    /**
     * Notify admin of payment submission
     */
    private function notifyAdminPaymentSubmission($bookingId) {
        // Get booking and customer information
        $booking = Booking::findById($bookingId);
        require_once __DIR__ . '/../Models/User.php';
        $customer = User::findById($booking->customerId);
        
        // Send email notification to admin
        Notification::sendPaymentSubmissionNotification($bookingId, $customer);
    }

    /**
     * Show payment success page
     */
    public function paymentSuccess() {
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
        if (!$booking || $booking->customerId != $_SESSION['user_id']) {
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        require_once __DIR__ . '/../Models/Resort.php';
        require_once __DIR__ . '/../Models/BookingFacilities.php';
        
        $resort = Resort::findById($booking->resortId);
        $facilities = BookingFacilities::getFacilitiesForBooking($bookingId);

        require_once __DIR__ . '/../Views/booking/payment_success.php';
    }

    /**
     * Get resort payment methods (API endpoint)
     */
    public function getPaymentMethods() {
        header('Content-Type: application/json');
        
        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        if (!$resortId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid Resort ID']);
            exit;
        }

        require_once __DIR__ . '/../Models/ResortPaymentMethods.php';
        $paymentMethods = ResortPaymentMethods::getFormattedPaymentMethods($resortId);
        
        echo json_encode($paymentMethods);
        exit;
    }
}