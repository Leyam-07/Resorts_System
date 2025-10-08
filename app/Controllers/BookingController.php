<?php

require_once __DIR__ . '/../Models/Booking.php';
require_once __DIR__ . '/../Models/Facility.php';
require_once __DIR__ . '/../Helpers/Notification.php';
require_once __DIR__ . '/../Models/Feedback.php';
require_once __DIR__ . '/../Models/Resort.php';
require_once __DIR__ . '/../Models/BookingAuditTrail.php';
require_once __DIR__ . '/../Models/PaymentSchedule.php';
require_once __DIR__ . '/../Models/BookingLifecycleManager.php';
require_once __DIR__ . '/../Helpers/ValidationHelper.php';
require_once __DIR__ . '/../Helpers/Database.php';

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

        // Phase 6: Enhanced validation
        $validation = ValidationHelper::validateBookingData($_POST);

        if (!$validation['valid']) {
            $errorMessages = [];
            foreach ($validation['errors'] as $field => $errors) {
                $errorMessages = array_merge($errorMessages, $errors);
            }
            $_SESSION['error_message'] = implode('<br>', $errorMessages);
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        $validatedData = $validation['data'];
        $resortId = $validatedData['resort_id'];
        $bookingDate = $validatedData['booking_date'];
        $timeSlotType = $validatedData['timeframe'];
        $numberOfGuests = $validatedData['number_of_guests'];
        $facilityIds = $validatedData['facility_ids'] ?? [];
        $customerId = $_SESSION['user_id'];

        // 4. Check for availability (resort + timeframe + optional facilities)
        if (!Booking::isResortTimeframeAvailable($resortId, $bookingDate, $timeSlotType, $facilityIds)) {
            $_SESSION['error_message'] = "The selected date and timeframe is not available. Please choose a different date or time.";
            $_SESSION['old_input'] = $_POST;
            header('Location: ?controller=booking&action=showBookingForm');
            exit;
        }

        // 5. Create resort-centric booking with Phase 6 enhancements
        $totalAmount = Booking::calculateBookingTotal($resortId, $timeSlotType, $bookingDate, $facilityIds);
        $bookingId = Booking::createResortBooking($customerId, $resortId, $bookingDate, $timeSlotType, $numberOfGuests, $facilityIds);

        if ($bookingId) {
            // Phase 6: Log booking creation in audit trail
            $bookingData = [
                'resortId' => $resortId,
                'bookingDate' => $bookingDate,
                'timeSlotType' => $timeSlotType,
                'numberOfGuests' => $numberOfGuests,
                'totalAmount' => $totalAmount
            ];
            BookingAuditTrail::logBookingCreation($bookingId, $customerId, $bookingData);
            
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

        // Check for admin contact information (needed for pricing incomplete notice)
        require_once __DIR__ . '/../Models/User.php';
        $adminUsers = User::getAdminUsers();
        $adminContact = !empty($adminUsers) ? $adminUsers[0] : null; // Use first admin

        // Add icon, full photo URL, and pricing completeness to each resort for display
        require_once __DIR__ . '/../Models/ResortTimeframePricing.php';
        foreach ($resorts as $resort) {
            $resort->icon = $this->getIconForResort($resort->name);
            // Prepend BASE_URL to the mainPhotoURL for correct display
            $resort->mainPhotoURL = BASE_URL . '/' . $resort->mainPhotoURL;
            // Check if resort has complete pricing
            $resort->hasCompletePricing = ResortTimeframePricing::hasCompletePricing($resort->resortId);
        }

        // Check for error messages and old input from session
        $errorMessage = $_SESSION['error_message'] ?? null;
        $oldInput = $_SESSION['old_input'] ?? [];

        // Check for a pre-selected facility ID from the URL
        $selectedFacilityId = filter_input(INPUT_GET, 'facility_id', FILTER_VALIDATE_INT);

        // Unset them so they don't persist on refresh
        unset($_SESSION['error_message']);
        unset($_SESSION['old_input']);

        // Pass admin contact information for pricing incomplete notice
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
        $date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING);
        if (!$resortId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid Resort ID']);
            exit;
        }

        $facilities = Facility::findByResortId($resortId);

        // Add pricing information and icon to facilities for display
        // Add pricing information, icon, and full photo URL to facilities for display
        require_once __DIR__ . '/../Models/BlockedFacilityAvailability.php';
        foreach ($facilities as &$facility) {
            $facility->priceDisplay = '₱' . number_format($facility->rate, 2);
            $facility->icon = $this->getIconForFacility($facility->name);
            // Prepend BASE_URL to the mainPhotoURL for correct display
            $facility->mainPhotoURL = BASE_URL . '/' . $facility->mainPhotoURL;

            // Check if facility is blocked on the provided date
            if ($date) {
                $facility->isBlocked = BlockedFacilityAvailability::isFacilityBlockedOnDate($facility->facilityId, $date);
            } else {
                $facility->isBlocked = false;
            }
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

            // Check if current date is a holiday
            require_once __DIR__ . '/../Helpers/HolidayHelper.php';
            $isHoliday = HolidayHelper::isHoliday($date);
            $response['isHoliday'] = $isHoliday;
        }
        
        echo json_encode($response);
        exit;
    }
    public function getResortDetails() {
        header('Content-Type: application/json');
        
        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        
        if (!$resortId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid Resort ID']);
            exit;
        }

        $resort = Resort::findById($resortId);

        if ($resort) {
            echo json_encode([
                'resort_id' => $resort->resortId,
                'name' => $resort->name,
                'capacity' => $resort->capacity
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Resort not found']);
        }
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
     * Check availability for resort + timeframe + facilities (Enhanced Phase 6)
     */
    public function checkAvailability() {
        header('Content-Type: application/json');
        
        try {
            $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
            $date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING);
            $timeframe = filter_input(INPUT_GET, 'timeframe', FILTER_SANITIZE_STRING);
            $numberOfGuests = filter_input(INPUT_GET, 'number_of_guests', FILTER_VALIDATE_INT) ?? 1;
            $facilityIds = filter_input(INPUT_GET, 'facility_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];
            
            // Enhanced validation
            if (!$resortId || $resortId <= 0) {
                throw new InvalidArgumentException('Valid resort ID is required');
            }
            
            if (!$date || !$this->isValidDate($date)) {
                throw new InvalidArgumentException('Valid date is required');
            }
            
            if (!$timeframe || !in_array($timeframe, ['12_hours', 'overnight', '24_hours'])) {
                throw new InvalidArgumentException('Valid timeframe is required');
            }
            
            if ($numberOfGuests <= 0 || $numberOfGuests > 100) {
                throw new InvalidArgumentException('Number of guests must be between 1 and 100');
            }
            
            // Sanitize facility IDs
            $sanitizedFacilityIds = [];
            if (is_array($facilityIds)) {
                foreach ($facilityIds as $id) {
                    $cleanId = filter_var($id, FILTER_VALIDATE_INT);
                    if ($cleanId && $cleanId > 0) {
                        $sanitizedFacilityIds[] = $cleanId;
                    }
                }
            }
            
            // Use advanced availability checker for detailed analysis
            $availabilityResult = AdvancedAvailabilityChecker::checkAvailabilityDetailed(
                $resortId,
                $date,
                $timeframe,
                $numberOfGuests,
                $sanitizedFacilityIds
            );
            
            echo json_encode([
                'success' => true,
                'available' => $availabilityResult['available'],
                'detailed_result' => $availabilityResult,
                'message' => $availabilityResult['available'] ?
                    'Time slot is available' :
                    'Time slot has conflicts or issues'
            ]);
            
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'validation_error',
                'message' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            error_log("Availability check error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'system_error',
                'message' => 'Unable to check availability. Please try again.'
            ]);
        }
        exit;
    }

    /**
     * Get calendar availability data for enhanced calendar modal
     */
    public function getCalendarAvailability() {
        header('Content-Type: application/json');
        
        // Use $_GET fallback for compatibility with testing and manual calls
        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT)
                   ?? filter_var($_GET['resort_id'] ?? null, FILTER_VALIDATE_INT);
        $timeframe = filter_input(INPUT_GET, 'timeframe', FILTER_SANITIZE_STRING)
                    ?? filter_var($_GET['timeframe'] ?? null, FILTER_SANITIZE_STRING);
        $month = filter_input(INPUT_GET, 'month', FILTER_SANITIZE_STRING)
                ?? filter_var($_GET['month'] ?? null, FILTER_SANITIZE_STRING);
        
        if (!$resortId || !$timeframe) {
            http_response_code(400);
            echo json_encode(['error' => 'Resort ID and timeframe are required']);
            exit;
        }

        // Default to current month if not provided
        if (!$month) {
            $month = date('Y-m');
        }

        require_once __DIR__ . '/../Models/BlockedResortAvailability.php';
        require_once __DIR__ . '/../Models/BlockedFacilityAvailability.php';
        
        // Get start and end dates for the month
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $availability = [];
        
        // Generate calendar data for each day of the month
        $currentDate = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
        
        while ($currentDate <= $endDateTime) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayOfWeek = intval($currentDate->format('w')); // Ensure integer
            $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6); // Sunday=0, Saturday=6
            
            // Check if it's a holiday
            require_once __DIR__ . '/../Helpers/HolidayHelper.php';
            $isHoliday = HolidayHelper::isHoliday($dateStr);
            
            $isToday = ($dateStr === date('Y-m-d'));
            $isPast = ($currentDate < new DateTime('today'));
            
            // Check if resort is available for this date/timeframe
            $isAvailable = Booking::isResortTimeframeAvailable($resortId, $dateStr, $timeframe, []);
            
            // Get blocked resort dates
            $resortBlocked = $this->isDateBlocked($resortId, $dateStr, 'resort');
            
            // Get existing bookings count
            $bookingCount = $this->getBookingCountForDate($resortId, $dateStr, $timeframe);
            
            $status = $this->getDayStatus($isAvailable, $resortBlocked, $isPast, $isWeekend, $isHoliday, $bookingCount);
            
            $statusText = '';
            switch ($status) {
                case 'available': $statusText = 'Available'; break;
                case 'weekend': $statusText = 'Weekend'; break;
                case 'holiday': $statusText = 'Holiday'; break;
                case 'booked': $statusText = 'Booked'; break;
                case 'blocked': $statusText = 'Blocked'; break;
                case 'unavailable': $statusText = 'Taken'; break;
                case 'past': $statusText = 'Past'; break;
            }

            $availability[$dateStr] = [
                'available' => ($status === 'available' || $status === 'weekend' || $status === 'holiday'),
                'isWeekend' => $isWeekend,
                'isHoliday' => $isHoliday,
                'isToday' => $isToday,
                'isPast' => $isPast,
                'isBlocked' => $resortBlocked,
                'bookingCount' => $bookingCount,
                'status' => $status,
                'statusText' => $statusText
            ];
            
            $currentDate->modify('+1 day');
        }
        
        echo json_encode([
            'month' => $month,
            'availability' => $availability,
            'resortId' => $resortId,
            'timeframe' => $timeframe
        ]);
        exit;
    }

    /**
     * Helper method to check if a date is blocked
     */
    private function isDateBlocked($resortId, $date, $type = 'resort') {
        if ($type === 'resort') {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) FROM BlockedResortAvailability WHERE ResortID = :resortId AND BlockDate = :date");
            $stmt->bindValue(':resortId', $resortId, PDO::PARAM_INT);
            $stmt->bindValue(':date', $date, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        }
        return false;
    }

    /**
     * Helper method to get booking count for a specific date
     */
    private function getBookingCountForDate($resortId, $date, $timeframe) {
        $db = Database::getInstance();
        // Updated logic: Count all bookings for the date, regardless of timeframe.
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM Bookings
            WHERE ResortID = :resortId
            AND BookingDate = :date
            AND Status IN ('Confirmed', 'Pending')
        ");
        $stmt->bindValue(':resortId', $resortId, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Helper method to determine day status for calendar
     */
    private function getDayStatus($isAvailable, $isBlocked, $isPast, $isWeekend, $isHoliday, $bookingCount) {
        if ($isPast) return 'past';
        if ($isBlocked) return 'blocked';
        if ($bookingCount > 0) return 'booked';
        if (!$isAvailable) return 'unavailable';
        if ($isHoliday) return 'holiday'; // Prioritize holiday over weekend
        if ($isWeekend) return 'weekend';
        return 'available';
    }


    public function showMyBookings() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?controller=user&action=login');
            exit;
        }

        $bookings = Booking::findByCustomerId($_SESSION['user_id']);

        // Keep all bookings for transparency including completed and cancelled ones
        $activeBookings = [];
        if ($bookings) {
            foreach ($bookings as $booking) {
                // Set hasFeedback flag for all bookings
                $booking->hasFeedback = (Feedback::findByBookingId($booking->BookingID) !== false);

                $activeBookings[] = $booking;
            }
        }

        // Get admin contact information for fallback contact buttons
        $adminUsers = User::getAdminUsers();
        $adminContact = !empty($adminUsers) ? $adminUsers[0] : null; // Use first admin

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

        // Check if the booking can be cancelled
        if ($booking->status !== 'Pending' || $booking->remainingBalance < $booking->totalAmount) {
            $_SESSION['error_message'] = "This booking cannot be cancelled as it has been paid or is no longer pending.";
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
        require_once __DIR__ . '/../Models/PaymentSchedule.php';
        require_once __DIR__ . '/../Models/User.php';

        $resort = Resort::findById($booking->resortId);
        $paymentMethods = ResortPaymentMethods::findByResortId($booking->resortId, true);
        $facilities = BookingFacilities::findByBookingId($bookingId);
        $customer = User::findById($booking->customerId);
        
        // Phase 6: Get payment schedule information for display
        $paymentSchedule = PaymentSchedule::findByBookingId($bookingId);
        $scheduleSummary = PaymentSchedule::getScheduleSummary($bookingId);
        $nextPayment = PaymentSchedule::getNextPaymentDue($bookingId);

        // Check if resort has payment methods configured
        $hasPaymentMethods = !empty($paymentMethods);

        // Check for error messages
        $errorMessage = $_SESSION['error_message'] ?? null;
        $successMessage = $_SESSION['success_message'] ?? null;
        unset($_SESSION['error_message'], $_SESSION['success_message']);

        require_once __DIR__ . '/../Views/booking/confirmation.php';
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
        $paymentMethod = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);

        if (!$bookingId || !$amountPaid || !$paymentReference || !$paymentMethod) {
            $_SESSION['error_message'] = "All fields are required, including payment method.";
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

        // Update booking with payment information AND create payment record with Phase 6 enhancements
        require_once __DIR__ . '/../Models/Payment.php';

        // Create payment record
        $paymentResult = Payment::createFromBookingPayment($bookingId, $amountPaid, $paymentMethod, $paymentReference, $paymentProofURL);

        if ($paymentResult['success'] && Booking::updatePaymentInfo($bookingId, $paymentProofURL, $paymentReference, $amountPaid)) {
            $paymentId = $paymentResult['paymentId'];
            // Audit trail logging is now handled comprehensively in Payment::createFromBookingPayment
            
            // Phase 6: Create or update payment schedule
            $existingSchedules = PaymentSchedule::findByBookingId($bookingId);
            if (empty($existingSchedules)) {
                // This is the first payment, so create the schedule now
                PaymentSchedule::createScheduleForBooking($bookingId, $booking->totalAmount, $amountPaid);
                
                // Now mark the first installment as paid
                $newSchedule = PaymentSchedule::findByBookingId($bookingId);
                if (!empty($newSchedule)) {
                    PaymentSchedule::markAsPaid($newSchedule[0]->ScheduleID, $paymentId);
                }
            } else {
                // Schedule exists, find the next due installment and mark it as paid
                $nextPayment = PaymentSchedule::getNextPaymentDue($bookingId);
                if ($nextPayment && $amountPaid >= $nextPayment->Amount) {
                    PaymentSchedule::markAsPaid($nextPayment->ScheduleID, $paymentId);
                }
            }
            
            // Phase 6: Trigger lifecycle management check
            BookingLifecycleManager::processBookingAfterPayment($bookingId);
            
            // Send notifications
            $this->notifyAdminPaymentSubmission($bookingId);
            Notification::sendPaymentSubmissionConfirmation($bookingId);

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
        require_once __DIR__ . '/../Models/Payment.php';
        require_once __DIR__ . '/../Models/User.php';

        $resort = Resort::findById($booking->resortId);
        $facilities = BookingFacilities::findByBookingId($bookingId);
        $latestPayment = current(Payment::findByBookingId($bookingId)); // Get the most recent payment
        $customer = User::findById($booking->customerId);

        require_once __DIR__ . '/../Views/booking/payment_success.php';
    }

    /**
     * Generate and download invoice as PDF
     */
    public function generateInvoice() {
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
        if (!$booking || $booking->customerId != $_SESSION['user_id']) {
            $_SESSION['error_message'] = "You are not authorized to access this booking.";
            header('Location: ?controller=booking&action=showMyBookings');
            exit;
        }

        require_once __DIR__ . '/../Models/Resort.php';
        require_once __DIR__ . '/../Models/BookingFacilities.php';
        require_once __DIR__ . '/../Models/Payment.php';
        require_once __DIR__ . '/../Models/User.php';

        $resort = Resort::findById($booking->resortId);
        $facilities = BookingFacilities::findByBookingId($bookingId);
        $payments = Payment::findByBookingId($bookingId);
        $customer = User::findById($booking->customerId);

        // Generate PDF invoice
        $this->generateInvoicePDF($booking, $resort, $facilities, $payments, $customer);
    }

    /**
     * Generate PDF invoice using DomPDF
     */
    private function generateInvoicePDF($booking, $resort, $facilities, $payments, $customer) {
        require_once __DIR__ . '/../../vendor/autoload.php';

        // Calculate paid amount
        $paidAmount = $booking->totalAmount - $booking->remainingBalance;

        // Build HTML content for invoice
        $html = $this->getInvoiceHTML($booking, $resort, $facilities, $payments, $paidAmount, $customer);

        // Instantiate and use the dompdf class
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);

        $dompdf->loadHtml($html);

        // Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('invoice_' . $booking->bookingId . '.pdf', array('Attachment' => 0));
        exit;
    }

    /**
     * Get invoice HTML template
     */
    private function getInvoiceHTML($booking, $resort, $facilities, $payments, $paidAmount, $customer) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Invoice #<?= $booking->bookingId ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; font-size: 13px; line-height: 1.4; }
                .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 15px; margin-bottom: 25px; }
                .header h1 { font-size: 18px; margin: 0 0 8px 0; }
                .header h2 { font-size: 15px; margin: 0 0 8px 0; }
                .header p { font-size: 11px; margin: 0; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 12px; }
                th { background-color: #f5f5f5; font-weight: bold; font-size: 11px; }
                .section { margin-bottom: 20px; }
                .section h3 { font-size: 13px; margin: 0 0 8px 0; padding: 0; }
                .customer-info { display: inline-block; width: 48%; vertical-align: top; margin-bottom: 10px; }
                .invoice-info { display: inline-block; width: 48%; vertical-align: top; margin-left: 4%; margin-bottom: 10px; }
                .customer-info > div, .invoice-info > div { margin-bottom: 5px; font-size: 12px; }
                .total { text-align: right; margin-top: 15px; padding-top: 15px; border-top: 1px solid #000; font-size: 12px; }
                .total strong { font-size: 13px; }
                .footer { margin-top: 25px; text-align: center; font-size: 11px; color: #666; }
                .details { font-size: 12px; margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>INVOICE</h1>
                <h2><?= htmlspecialchars($resort->name ?? 'Resort') ?></h2>
                <p>Integrated Digital Management System</p>
            </div>

            <div style="display: table;">
                <div style="display: table-row;">
                    <div style="display: table-cell; padding-right: 20px; vertical-align: top;">
                        <div><strong>Customer:</strong> <?= htmlspecialchars($customer['FirstName'] . ' ' . $customer['LastName']) ?></div>
                        <div><strong>Phone:</strong> <?= htmlspecialchars($customer['PhoneNumber'] ?? 'N/A') ?></div>
                    </div>
                    <div style="display: table-cell; padding-left: 20px; vertical-align: top;">
                        <div><strong>Invoice #:</strong> <?= $booking->bookingId ?></div>
                        <div><strong>Date:</strong> <?= date('M j, Y') ?></div>
                        <div><strong>Booking:</strong> <?= date('M j, Y', strtotime($booking->bookingDate)) ?></div>
                        <div><strong>Timeframe:</strong> <?= htmlspecialchars(Booking::getTimeSlotDisplay($booking->timeSlotType)) ?></div>
                        <div><strong>Guests:</strong> <?= $booking->numberOfGuests ?> person<?= $booking->numberOfGuests > 1 ? 's' : '' ?></div>
                    </div>
                </div>
            </div>

            <div class="details">
                <h3>Service Details</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Base Resort Fee (<?= htmlspecialchars(Booking::getTimeSlotDisplay($booking->timeSlotType)) ?>)</td>
                            <td>PHP <?= number_format($booking->totalAmount - ($facilities ? array_sum(array_column($facilities, 'facilityPrice')) : 0), 2) ?></td>
                        </tr>
                        <?php if ($facilities): ?>
                            <?php foreach ($facilities as $facility): ?>
                            <tr>
                                <td>Additional Facility: <?= htmlspecialchars($facility->FacilityName) ?></td>
                                <td>PHP <?= number_format($facility->FacilityRate, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total Amount</strong></td>
                            <td><strong>PHP <?= number_format($booking->totalAmount, 2) ?></strong></td>
                        </tr>
                        <?php if ($paidAmount > 0): ?>
                        <tr>
                            <td><strong>Amount Paid</strong></td>
                            <td><strong>PHP <?= number_format($paidAmount, 2) ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong>Remaining Balance</strong></td>
                            <td><strong>PHP <?= number_format($booking->remainingBalance, 2) ?></strong></td>
                        </tr>
                        <?php endif; ?>
                    </tfoot>
                </table>
            </div>

            <?php if ($payments): ?>
            <div class="details">
                <h3>Payment History</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($payment->PaymentDate)) ?></td>
                            <td><?= htmlspecialchars($payment->PaymentMethod) ?></td>
                            <td><?= htmlspecialchars($payment->Reference) ?></td>
                            <td>PHP <?= number_format($payment->Amount, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <div class="footer">
                <p>Thank you for your business!</p>
                <p>This invoice was generated on <?= date('F j, Y \a\t H:i:s') ?> by the Integrated Digital Management System.</p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Get resort payment methods (API endpoint)
     */
    public function getPaymentMethods() {
        header('Content-Type: application/json');

        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        $bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);

        if (!$resortId && !$bookingId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid Resort ID or Booking ID']);
            exit;
        }

        require_once __DIR__ . '/../Models/ResortPaymentMethods.php';

        // If we have booking_id, get resort_id from booking
        if ($bookingId && !$resortId) {
            $booking = Booking::findById($bookingId);
            if ($booking) {
                $resortId = $booking->resortId;
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Booking not found']);
                exit;
            }
        }

        $paymentMethods = ResortPaymentMethods::getFormattedPaymentMethods($resortId);

        echo json_encode($paymentMethods);
        exit;
    }

    /**
     * Phase 6: Advanced availability report for admin
     */
    public function getAdvancedAvailabilityReport() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        header('Content-Type: application/json');
        
        try {
            $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
            $startDate = filter_input(INPUT_GET, 'start_date', FILTER_UNSAFE_RAW);
            $endDate = filter_input(INPUT_GET, 'end_date', FILTER_UNSAFE_RAW);
            
            // Default to current month if no dates provided
            if (!$startDate) {
                $startDate = date('Y-m-01');
            }
            if (!$endDate) {
                $endDate = date('Y-m-t');
            }
            
            // Validate dates
            if (!$this->isValidDate($startDate) || !$this->isValidDate($endDate)) {
                throw new InvalidArgumentException('Invalid date format');
            }
            
            if (strtotime($endDate) < strtotime($startDate)) {
                throw new InvalidArgumentException('End date must be after start date');
            }
            
            $report = AdvancedAvailabilityChecker::getAvailabilityReport($resortId, $startDate, $endDate);
            
            echo json_encode([
                'success' => true,
                'report' => $report
            ]);
            
        } catch (Exception $e) {
            error_log("Advanced availability report error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Phase 6: Get availability suggestions for conflict resolution
     */
    public function getAvailabilitySuggestions() {
        header('Content-Type: application/json');
        
        try {
            $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
            $date = filter_input(INPUT_GET, 'date', FILTER_UNSAFE_RAW);
            $timeframe = filter_input(INPUT_GET, 'timeframe', FILTER_UNSAFE_RAW);
            $numberOfGuests = filter_input(INPUT_GET, 'number_of_guests', FILTER_VALIDATE_INT) ?? 1;
            $facilityIds = $_GET['facility_ids'] ?? [];
            
            if (!$resortId || !$date || !$timeframe) {
                throw new InvalidArgumentException('Missing required parameters');
            }
            
            // Get detailed availability analysis
            $result = AdvancedAvailabilityChecker::checkAvailabilityDetailed(
                $resortId,
                $date,
                $timeframe,
                $numberOfGuests,
                is_array($facilityIds) ? $facilityIds : []
            );
            
            echo json_encode([
                'success' => true,
                'suggestions' => $result['suggestions'],
                'alternative_dates' => $result['alternative_dates'],
                'alternative_facilities' => $result['alternative_facilities'],
                'optimization_suggestions' => $result['optimization_suggestions']
            ]);
            
        } catch (Exception $e) {
            error_log("Availability suggestions error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * Enhanced date validation
     */
    private function isValidDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Helper to assign an icon based on facility name
     */
    private function getIconForFacility($facilityName) {
        $name = strtolower($facilityName);
        if (strpos($name, 'pool') !== false) return 'fas fa-swimming-pool';
        if (strpos($name, 'videoke') !== false || strpos($name, 'karaoke') !== false) return 'fas fa-microphone-alt';
        if (strpos($name, 'room') !== false) return 'fas fa-bed';
        if (strpos($name, 'cottage') !== false) return 'fas fa-campground';
        if (strpos($name, 'grill') !== false) return 'fas fa-fire-alt';
        if (strpos($name, 'kitchen') !== false) return 'fas fa-utensils';
        return 'fas fa-swimming-pool'; // Default icon
    }

    /**
     * Helper to assign an icon based on resort name
     */
    private function getIconForResort($resortName) {
        $name = strtolower($resortName);
        if (strpos($name, 'villa') !== false) return 'fas fa-house-user';
        if (strpos($name, 'season') !== false) return 'fas fa-cloud-sun';
        if (strpos($name, 'classic') !== false) return 'fas fa-gopuram';
        return 'fas fa-hotel'; // Default icon
    }

    /**
     * Enhanced input sanitization
     */
    private function sanitizeInput($input, $type = 'string') {
        switch ($type) {
            case 'int':
                return filter_var($input, FILTER_VALIDATE_INT);
            case 'float':
                return filter_var($input, FILTER_VALIDATE_FLOAT);
            case 'email':
                return filter_var($input, FILTER_VALIDATE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_VALIDATE_URL);
            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Enhanced error handling with logging
     */
    private function handleError($message, $code = 500, $logLevel = 'error') {
        // Log the error
        error_log("[BookingController] $logLevel: $message");
        
        // Return appropriate response
        http_response_code($code);
        
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $message,
                'code' => $code
            ]);
        } else {
            // For non-AJAX requests, redirect with error message
            $_SESSION['error_message'] = $message;
            header('Location: ?controller=booking&action=create');
        }
        exit();
    }
    public function getFacilitiesForBooking() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['booking_id']) || !isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
        $booking = Booking::findById($bookingId);

        if (!$booking || $booking->customerId != $_SESSION['user_id']) {
            echo json_encode(['error' => 'Invalid booking']);
            return;
        }

        require_once __DIR__ . '/../Models/BookingFacilities.php';
        $facilities = BookingFacilities::getFacilitiesForBooking($bookingId);

        echo json_encode($facilities);
    }
}
