<?php

// Automated Test Script for Feedback System (Backend)

// Setup: Bootstrap the application environment
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Models/User.php';
require_once __DIR__ . '/../../app/Models/Facility.php';
require_once __DIR__ . '/../../app/Models/Booking.php';
require_once __DIR__ . '/../../app/Models/Feedback.php';
require_once __DIR__ . '/../../app/Controllers/FeedbackController.php';

class FeedbackSystemTestRunner {
    private $pdo;
    private $testUserId;
    private $testFacilityId;
    private $testBookingId;
    private $testFeedbackId;

    public function __construct() {
        try {
            $this->pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->fail("Database connection failed: " . $e->getMessage());
            exit;
        }
    }

    private function pass($message) {
        echo "[PASS] " . $message . PHP_EOL;
    }

    private function fail($message) {
        echo "[FAIL] " . $message . PHP_EOL;
    }

    private function setupTestData() {
        echo "\nSetting up test data..." . PHP_EOL;
        // 1. Create Test User
        $username = 'testuser_' . uniqid();
        $email = 'testuser_' . uniqid() . '@example.com';
        $password = 'password';
        $role = 'Customer';

        if (User::create($username, $password, $email, $role)) {
            $createdUser = User::findByUsername($username);
            $this->testUserId = $createdUser['UserID'];
        } else {
            $this->fail("Failed to create test user.");
            exit;
        }

        // 2. Create Test Facility
        $facility = new Facility();
        $facility->name = 'Test Pool';
        $facility->description = 'A test pool';
        $facility->capacity = 10;
        $facility->price = 100.00;
        $this->testFacilityId = Facility::create($facility);

        // 3. Create a 'Completed' Booking for the test user
        $booking = new Booking();
        $booking->customerId = $this->testUserId;
        $booking->facilityId = $this->testFacilityId;
        $booking->bookingDate = date('Y-m-d');
        $booking->startTime = '10:00:00';
        $booking->endTime = '12:00:00';
        $booking->guestCount = 5;
        $booking->totalPrice = 200.00;
        $booking->status = 'Completed'; // Critical for feedback
        $this->testBookingId = Booking::create($booking);
        
        echo "Test data created (User ID: {$this->testUserId}, Booking ID: {$this->testBookingId})." . PHP_EOL;
    }

    private function cleanupTestData() {
        echo "\nCleaning up test data..." . PHP_EOL;
        if ($this->testFeedbackId) {
            $this->pdo->exec("DELETE FROM Feedback WHERE FeedbackID = {$this->testFeedbackId}");
        }
        if ($this->testBookingId) {
            $this->pdo->exec("DELETE FROM Bookings WHERE BookingID = {$this->testBookingId}");
        }
        if ($this->testFacilityId) {
            $this->pdo->exec("DELETE FROM Facilities WHERE FacilityID = {$this->testFacilityId}");
        }
        if ($this->testUserId) {
            $this->pdo->exec("DELETE FROM Users WHERE UserID = {$this->testUserId}");
        }
        echo "Cleanup complete." . PHP_EOL;
    }

    public function run() {
        $this->setupTestData();

        echo "\n--- Running Model Tests ---" . PHP_EOL;
        $this->testBE01_CreateModel();
        $this->testBE02_FindByBookingId();
        $this->testBE03_FindAll();

        echo "\n--- Running Controller Tests ---" . PHP_EOL;
        $this->testBE04_ShowForm();
        $this->testBE05_SubmitValidFeedback();
        $this->testBE06_SubmitInvalidFeedback();
        $this->testBE07_ListAllFeedbackAsAdmin();

        $this->cleanupTestData();
    }

    // --- Model Tests ---

    public function testBE01_CreateModel() {
        $feedback = new Feedback();
        $feedback->bookingId = $this->testBookingId;
        $feedback->rating = 5;
        $feedback->comment = 'Excellent!';
        
        $this->testFeedbackId = Feedback::create($feedback);
        
        if ($this->testFeedbackId) {
            $this->pass("BE-01: Feedback::create() successfully inserted a record.");
        } else {
            $this->fail("BE-01: Feedback::create() failed to insert a record.");
        }
    }

    public function testBE02_FindByBookingId() {
        $feedback = Feedback::findByBookingId($this->testBookingId);
        if ($feedback && $feedback->BookingID == $this->testBookingId) {
            $this->pass("BE-02: Feedback::findByBookingId() found the correct record.");
        } else {
            $this->fail("BE-02: Feedback::findByBookingId() did not find the record.");
        }
    }

    public function testBE03_FindAll() {
        $feedbacks = Feedback::findAll();
        if (is_array($feedbacks) && !empty($feedbacks)) {
            $this->pass("BE-03: Feedback::findAll() returned an array of feedback objects.");
        } else {
            $this->fail("BE-03: Feedback::findAll() did not return expected results.");
        }
    }

    // --- Controller Tests ---

    // Note: Controller tests simulate the environment. We can't test headers directly,
    // but we can check the state of the database or session variables after execution.
    
    public function testBE04_ShowForm() {
        $controller = new FeedbackController();
        
        // Simulate logged-in user and GET request
        $_SESSION['user_id'] = $this->testUserId;
        $_GET['booking_id'] = $this->testBookingId;

        // We can't test for output buffering, but we can check if it runs without fatal errors or unauthorized redirects
        try {
            // Suppress view output
            ob_start();
            $controller->showFeedbackForm();
            ob_end_clean();
            $this->pass("BE-04: FeedbackController::showFeedbackForm() executed without fatal errors for an authorized user.");
        } catch (Exception $e) {
            $this->fail("BE-04: FeedbackController::showFeedbackForm() threw an exception: " . $e->getMessage());
        }
        unset($_GET['booking_id']);
    }

    public function testBE05_SubmitValidFeedback() {
        // First, clean any existing feedback for this booking to test submission
        $this->pdo->exec("DELETE FROM Feedback WHERE BookingID = {$this->testBookingId}");
        
        $controller = new FeedbackController();

        // Simulate logged-in user and POST request
        $_SESSION['user_id'] = $this->testUserId;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['bookingId'] = $this->testBookingId;
        $_POST['rating'] = 4;
        $_POST['comment'] = 'Very good service.';
        
        // Suppress header output
        ob_start();
        $controller->submitFeedback();
        ob_end_clean();

        $feedback = Feedback::findByBookingId($this->testBookingId);
        if ($feedback && $feedback->Rating == 4) {
            $this->pass("BE-05: FeedbackController::submitFeedback() with valid data was successful.");
        } else {
            $this->fail("BE-05: FeedbackController::submitFeedback() with valid data failed.");
        }
    }

    public function testBE06_SubmitInvalidFeedback() {
        $this->pdo->exec("DELETE FROM Feedback WHERE BookingID = {$this->testBookingId}");
        
        $controller = new FeedbackController();

        $_SESSION['user_id'] = $this->testUserId;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['bookingId'] = $this->testBookingId;
        $_POST['rating'] = null; // Invalid data
        $_POST['comment'] = 'This should fail.';

        ob_start();
        $controller->submitFeedback();
        ob_end_clean();

        $feedback = Feedback::findByBookingId($this->testBookingId);
        if (!$feedback) {
            $this->pass("BE-06: FeedbackController::submitFeedback() correctly rejected invalid data.");
        } else {
            $this->fail("BE-06: FeedbackController::submitFeedback() incorrectly accepted invalid data.");
        }
    }

    public function testBE07_ListAllFeedbackAsAdmin() {
        $controller = new FeedbackController();

        // Simulate Admin login
        $_SESSION['user_id'] = $this->testUserId; // In a real scenario, this would be a separate admin user
        $_SESSION['role'] = 'Admin';
        
        try {
            ob_start();
            $controller->listAllFeedback();
            ob_end_clean();
            $this->pass("BE-07: FeedbackController::listAllFeedback() executed for Admin without fatal errors.");
        } catch (Exception $e) {
            $this->fail("BE-07: FeedbackController::listAllFeedback() threw an exception: " . $e->getMessage());
        }
        
        // Clean up session
        unset($_SESSION['user_id']);
        unset($_SESSION['role']);
    }
}

// Start a session to test session-dependent controller logic
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$testRunner = new FeedbackSystemTestRunner();
$testRunner->run();

// Close the session
session_write_close();

?>