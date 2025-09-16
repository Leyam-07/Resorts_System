<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Booking.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Facility.php';
require_once __DIR__ . '/../Models/BlockedAvailability.php';
require_once __DIR__ . '/../Models/Resort.php';

class AdminController {
    private $db;
    private $userModel;

    public function __construct() {
        // Ensure user is an admin
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            // If user is not logged in, deny access
            http_response_code(403);
            require_once __DIR__ . '/../Views/errors/403.php';
            exit();
        }

        // Allow Staff to access their own dashboard, but ensure they are logged in
        // The specific view logic will be handled in the dashboard method.
        // For other admin functions, enforce Admin-only access later in specific methods if needed.
        // This constructor's primary role is now general access control and DB setup.
        if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Staff') {
            http_response_code(403);
            require_once __DIR__ . '/../Views/errors/403.php';
            exit();
        }

        try {
            $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
        $this->userModel = new User($this->db);
    }

    public function dashboard() {
        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        $resorts = Resort::findAll(); // For the filter dropdown

        // Role-based dashboard logic
        if ($_SESSION['role'] === 'Admin') {
            $todaysBookings = Booking::findTodaysBookings($resortId);
            $upcomingBookings = Booking::findUpcomingBookings($resortId);

            // Augment bookings with payment status
            foreach ($todaysBookings as $booking) {
                $payments = Payment::findByBookingId($booking->BookingID);
                if (empty($payments)) {
                    $booking->PaymentStatus = 'Unpaid';
                } else {
                    // Use the status of the most recent payment
                    $booking->PaymentStatus = $payments[0]->Status;
                }
            }

            foreach ($upcomingBookings as $booking) {
                $payments = Payment::findByBookingId($booking->BookingID);
                if (empty($payments)) {
                    $booking->PaymentStatus = 'Unpaid';
                } else {
                    $booking->PaymentStatus = $payments[0]->Status;
                }
            }

            // Get financial and history data
            $currentMonth = date('m');
            $currentYear = date('Y');
            $monthlyIncome = Booking::getMonthlyIncome($currentYear, $currentMonth, $resortId);
            $bookingHistory = Booking::getBookingHistory(10); // This could also be filtered if needed

            include __DIR__ . '/../Views/admin/dashboard.php';
        } elseif ($_SESSION['role'] === 'Staff') {
            $this->staffDashboard($resortId, $resorts);
        } else {
            // Fallback for any other roles or errors
            http_response_code(403);
            require_once __DIR__ . '/../Views/errors/403.php';
            exit();
        }
    }

    public function staffDashboard($resortId = null, $resorts = null) {
        // If called directly, fetch the necessary data
        if ($resortId === null) {
            $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        }
        if ($resorts === null) {
            $resorts = Resort::findAll();
        }

        $todaysBookings = Booking::findTodaysBookings($resortId);
        $upcomingBookings = Booking::findUpcomingBookings($resortId);

        foreach ($todaysBookings as $booking) {
            $payments = Payment::findByBookingId($booking->BookingID);
            if (empty($payments)) {
                $booking->PaymentStatus = 'Unpaid';
            } else {
                $booking->PaymentStatus = $payments[0]->Status;
            }
        }

        foreach ($upcomingBookings as $booking) {
            $payments = Payment::findByBookingId($booking->BookingID);
            if (empty($payments)) {
                $booking->PaymentStatus = 'Unpaid';
            } else {
                $booking->PaymentStatus = $payments[0]->Status;
            }
        }
        include __DIR__ . '/../Views/admin/staff_dashboard.php';
    }

    public function users() {
        $users = $this->userModel->findAll();
        include __DIR__ . '/../Views/admin/users.php';
    }

    public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $role = filter_input(INPUT_POST, 'role', FILTER_UNSAFE_RAW);
            $firstName = filter_input(INPUT_POST, 'firstName', FILTER_UNSAFE_RAW);
            $lastName = filter_input(INPUT_POST, 'lastName', FILTER_UNSAFE_RAW);
            $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_UNSAFE_RAW);
            $notes = filter_input(INPUT_POST, 'notes', FILTER_UNSAFE_RAW) ?? '';

            if ($password !== $confirmPassword) {
                header('Location: ?controller=admin&action=addUser&error=password_mismatch');
                exit();
            }

            $result = $this->userModel->create($username, $password, $email, $role, $firstName, $lastName, $phoneNumber, $notes);
            if ($result === true) {
                header('Location: ?controller=admin&action=users&status=user_added');
                exit();
            } else {
                // To handle errors in modal, you might want to return a JSON response
                // For now, redirecting back with an error. A more advanced implementation
                // would handle this via AJAX on the client-side.
                header('Location: ?controller=admin&action=users&error=' . $result);
                exit();
            }
        }
        // This part is now handled by getAddUserForm
    }

    // The getAddUserForm() method is no longer needed as the form is now static in user_modals.php
    // public function getAddUserForm() { ... }

    public function editUser() {
        if (!isset($_GET['id'])) {
            die('User ID not specified.');
        }
        $userId = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle the form submission
            $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $firstName = filter_input(INPUT_POST, 'firstName', FILTER_UNSAFE_RAW);
            $lastName = filter_input(INPUT_POST, 'lastName', FILTER_UNSAFE_RAW);
            $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_UNSAFE_RAW);
            $notes = filter_input(INPUT_POST, 'notes', FILTER_UNSAFE_RAW) ?? '';

            $result = $this->userModel->update($userId, $username, $email, $firstName, $lastName, $phoneNumber, $notes);

            if ($result === true) {
                // If admin edits their own profile, update session
                if ($userId == $_SESSION['user_id']) {
                    $_SESSION['username'] = $username;
                }
                header('Location: ?controller=admin&action=users&status=user_updated');
                exit();
            } else {
                header('Location: ?controller=admin&action=users&error=' . $result);
                exit();
            }
        }
        // This part is now handled by getEditUserForm
    }

    public function getUserJson() {
        header('Content-Type: application/json');
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID not specified.']);
            exit();
        }
        $userId = $_GET['id'];
        $user = $this->userModel->findById($userId);

        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
        }
        exit();
    }

    public function deleteUser() {
        if (!isset($_GET['id'])) {
            die('User ID not specified.');
        }
        $userId = $_GET['id'];

        if ($this->userModel->delete($userId)) {
            header('Location: ?controller=admin&action=users&status=user_deleted');
            exit();
        } else {
            die('Failed to delete user.');
        }
    }
    public function viewUserBookings() {
        if (!isset($_GET['id'])) {
            die('User ID not specified.');
        }
        $userId = $_GET['id'];

        $user = $this->userModel->findById($userId);
        if (!$user) {
            die('User not found.');
        }

        $bookings = Booking::findByCustomerId($userId);
        // This part is now handled by getUserBookings
        include __DIR__ . '/../Views/admin/view_user_bookings.php';
    }

    public function getUserBookings() {
        if (!isset($_GET['id'])) {
            die('User ID not specified.');
        }
        $userId = $_GET['id'];
        $user = $this->userModel->findById($userId);
        if (!$user) {
            die('User not found.');
        }
        $bookings = Booking::findByCustomerId($userId);

        // This view will be loaded into the modal
        include __DIR__ . '/../Views/admin/view_user_bookings.php';
    }

    public function facilities() {
        $facilities = Facility::findAllWithResort();
        $resorts = Resort::findAll(); // Fetch all resorts for the dropdown
        include __DIR__ . '/../Views/admin/facilities/index.php';
    }

    public function addFacility() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $facility = new Facility();
            $facility->name = filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW);
            $facility->capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
            $facility->rate = filter_input(INPUT_POST, 'rate', FILTER_VALIDATE_FLOAT);
           $facility->shortDescription = filter_input(INPUT_POST, 'shortDescription', FILTER_UNSAFE_RAW);
           $facility->fullDescription = filter_input(INPUT_POST, 'fullDescription', FILTER_UNSAFE_RAW);
            $facility->resortId = filter_input(INPUT_POST, 'resortId', FILTER_VALIDATE_INT);

            if (Facility::create($facility)) {
                header('Location: ?controller=admin&action=facilities&status=facility_added');
                exit();
            } else {
                header('Location: ?controller=admin&action=addFacility&error=add_failed');
                exit();
            }
        }
    }

    public function editFacility() {
        if (!isset($_GET['id'])) {
            die('Facility ID not specified.');
        }
        $facilityId = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $facility = new Facility();
            $facility->facilityId = $facilityId;
            $facility->name = filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW);
            $facility->capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
            $facility->rate = filter_input(INPUT_POST, 'rate', FILTER_VALIDATE_FLOAT);
           $facility->shortDescription = filter_input(INPUT_POST, 'shortDescription', FILTER_UNSAFE_RAW);
           $facility->fullDescription = filter_input(INPUT_POST, 'fullDescription', FILTER_UNSAFE_RAW);
           $facility->mainPhotoURL = Facility::findById($facilityId)->mainPhotoURL; // Preserve existing main photo
            $facility->resortId = filter_input(INPUT_POST, 'resortId', FILTER_VALIDATE_INT);

            if (Facility::update($facility)) {
                header('Location: ?controller=admin&action=facilities&status=facility_updated');
                exit();
            } else {
                header('Location: ?controller=admin&action=editFacility&id=' . $facilityId . '&error=update_failed');
                exit();
            }
        }
    }
    
    public function getFacilityEditForm() {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo "Facility ID not specified.";
            exit();
        }
        $facilityId = $_GET['id'];
        $facility = Facility::findById($facilityId);
        if (!$facility) {
            http_response_code(404);
            echo "Facility not found.";
            exit();
        }
        $resorts = Resort::findAll(); // Fetch all resorts for the dropdown
        // This view will be loaded into the modal
        include __DIR__ . '/../Views/admin/facilities/edit.php';
    }
    
    public function getScheduleView() {
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo "Facility ID not specified.";
            exit();
        }
        $facilityId = $_GET['id'];
        $facility = Facility::findById($facilityId);
        if (!$facility) {
            http_response_code(404);
            echo "Facility not found.";
            exit();
        }
        $blockedSlots = BlockedAvailability::findByFacilityId($facilityId);
        
        // This view will be loaded into the modal
        include __DIR__ . '/../Views/admin/facilities/schedule.php';
    }

    public function deleteFacility() {
        if (!isset($_GET['id'])) {
            die('Facility ID not specified.');
        }
        $facilityId = $_GET['id'];

        if (Facility::delete($facilityId)) {
            header('Location: ?controller=admin&action=facilities&status=facility_deleted');
            exit();
        } else {
            die('Failed to delete facility.');
        }
    }

    public function schedule() {
        if (!isset($_GET['id'])) {
            die('Facility ID not specified.');
        }
        $facilityId = $_GET['id'];

        $facility = Facility::findById($facilityId);
        if (!$facility) {
            die('Facility not found.');
        }

        $blockedSlots = BlockedAvailability::findByFacilityId($facilityId);

        include __DIR__ . '/../Views/admin/facilities/schedule.php';
    }

    public function blockTime() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $facilityId = filter_input(INPUT_POST, 'facilityId', FILTER_VALIDATE_INT);
            $startDate = filter_input(INPUT_POST, 'blockDate', FILTER_UNSAFE_RAW);
            $endDate = filter_input(INPUT_POST, 'blockEndDate', FILTER_UNSAFE_RAW);
            $startTime = filter_input(INPUT_POST, 'startTime', FILTER_UNSAFE_RAW);
            $endTime = filter_input(INPUT_POST, 'endTime', FILTER_UNSAFE_RAW);
            $reason = filter_input(INPUT_POST, 'reason', FILTER_UNSAFE_RAW);

            // If no end date is provided, it's a single-day block
            if (empty($endDate)) {
                $endDate = $startDate;
            }

            $currentDate = new DateTime($startDate);
            $lastDate = new DateTime($endDate);
            $success = true;

            while ($currentDate <= $lastDate) {
                $blocked = new BlockedAvailability();
                $blocked->facilityId = $facilityId;
                $blocked->blockDate = $currentDate->format('Y-m-d');
                $blocked->startTime = $startTime;
                $blocked->endTime = $endTime;
                $blocked->reason = $reason;

                if (!BlockedAvailability::create($blocked)) {
                    $success = false;
                    // Stop on first failure
                    break;
                }
                $currentDate->modify('+1 day');
            }

            if ($success) {
                header('Location: ?controller=admin&action=schedule&id=' . $facilityId . '&status=time_blocked');
            } else {
                header('Location: ?controller=admin&action=schedule&id=' . $facilityId . '&error=block_failed');
            }
            exit();
        }
    }

    public function unblockTime() {
        if (!isset($_GET['id']) || !isset($_GET['facilityId'])) {
            die('Required parameters not specified.');
        }
        $blockedId = $_GET['id'];
        $facilityId = $_GET['facilityId'];

        if (BlockedAvailability::delete($blockedId)) {
            header('Location: ?controller=admin&action=schedule&id=' . $facilityId . '&status=time_unblocked');
        } else {
            header('Location: ?controller=admin&action=schedule&id=' . $facilityId . '&error=unblock_failed');
        }
        exit();
    }
   public function uploadPhoto() {
       if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['id'])) {
           die('Invalid request.');
       }
       $facilityId = $_GET['id'];

       if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
           $uploadDir = __DIR__ . '/../../public/uploads/facilities/';
           if (!is_dir($uploadDir)) {
               mkdir($uploadDir, 0777, true);
           }
           $fileName = uniqid() . '-' . basename($_FILES['photo']['name']);
           $targetPath = $uploadDir . $fileName;

           if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
               $photoURL = '/public/uploads/facilities/' . $fileName;
               Facility::addPhoto($facilityId, $photoURL);
               header('Location: ?controller=admin&action=editFacility&id=' . $facilityId . '&status=photo_uploaded');
           } else {
               header('Location: ?controller=admin&action=editFacility&id=' . $facilityId . '&error=upload_failed');
           }
       } else {
           header('Location: ?controller=admin&action=editFacility&id=' . $facilityId . '&error=no_file');
       }
       exit();
   }

   public function deletePhoto() {
       if (!isset($_GET['id']) || !isset($_GET['photoId'])) {
           die('Required parameters not specified.');
       }
       $facilityId = $_GET['id'];
       $photoId = $_GET['photoId'];

       $photo = Facility::findPhotoById($photoId);
       if ($photo) {
           $filePath = __DIR__ . '/../../' . ltrim($photo['PhotoURL'], '/');
           if (file_exists($filePath)) {
               unlink($filePath);
           }
           Facility::deletePhoto($photoId);
           header('Location: ?controller=admin&action=editFacility&id=' . $facilityId . '&status=photo_deleted');
       } else {
           header('Location: ?controller=admin&action=editFacility&id=' . $facilityId . '&error=photo_not_found');
       }
       exit();
   }

   public function setMainPhoto() {
       if (!isset($_GET['id']) || !isset($_GET['photoId'])) {
           die('Required parameters not specified.');
       }
       $facilityId = $_GET['id'];
       $photoId = $_GET['photoId'];

       $photo = Facility::findPhotoById($photoId);
       $facility = Facility::findById($facilityId);

       if ($photo && $facility) {
           $facility->mainPhotoURL = $photo['PhotoURL'];
           Facility::update($facility);
           header('Location: ?controller=admin&action=editFacility&id=' . $facilityId . '&status=main_photo_set');
       } else {
           header('Location: ?controller=admin&action=editFacility&id=' . $facilityId . '&error=set_main_failed');
       }
       exit();
   }

   public function previewFacilities() {
       // This action is for admins/staff to see the customer view
       // We can reuse the logic from the UserController's dashboard
       $resorts = Resort::findAll();
       include __DIR__ . '/../Views/admin/facilities/preview.php';
   }
    public function management() {
        if ($_SESSION['role'] !== 'Admin') {
            http_response_code(403);
            require_once __DIR__ . '/../Views/errors/403.php';
            exit();
        }

        $resorts = Resort::findAll();
        $resortsWithFacilities = [];
        foreach ($resorts as $resort) {
            $facilities = Facility::findByResortId($resort->resortId);
            $resortsWithFacilities[] = [
                'resort' => $resort,
                'facilities' => $facilities
            ];
        }

        include __DIR__ . '/../Views/admin/management/index.php';
    }

    public function blockResortAvailability() {
        if ($_SESSION['role'] !== 'Admin') {
            http_response_code(403);
            exit('Forbidden');
        }
        $resortId = filter_input(INPUT_POST, 'resortId', FILTER_VALIDATE_INT);
        $blockDate = filter_input(INPUT_POST, 'blockDate', FILTER_UNSAFE_RAW);
        $reason = filter_input(INPUT_POST, 'reason', FILTER_UNSAFE_RAW);

        if ($resortId && $blockDate) {
            BlockedResortAvailability::create($resortId, $blockDate, $reason);
        }
        header('Location: ?controller=admin&action=management');
        exit;
    }

    public function deleteResortAvailabilityBlock() {
        if ($_SESSION['role'] !== 'Admin') {
            http_response_code(403);
            exit('Forbidden');
        }
        $blockId = filter_input(INPUT_GET, 'block_id', FILTER_VALIDATE_INT);
        if ($blockId) {
            BlockedResortAvailability::delete($blockId);
        }
        header('Location: ?controller=admin&action=management');
        exit;
    }
}