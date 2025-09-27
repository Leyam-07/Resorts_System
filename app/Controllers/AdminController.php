<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Booking.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Facility.php';
require_once __DIR__ . '/../Models/BlockedAvailability.php';
require_once __DIR__ . '/../Models/Resort.php';
require_once __DIR__ . '/../Models/BlockedFacilityAvailability.php';
require_once __DIR__ . '/../Models/BlockedResortAvailability.php';
require_once __DIR__ . '/../Models/ResortTimeframePricing.php';
require_once __DIR__ . '/../Models/ResortPaymentMethods.php';
require_once __DIR__ . '/../Models/BookingFacilities.php';
// Phase 6: Advanced lifecycle management models
require_once __DIR__ . '/../Models/BookingLifecycleManager.php';
require_once __DIR__ . '/../Models/BookingAuditTrail.php';
require_once __DIR__ . '/../Models/PaymentSchedule.php';
require_once __DIR__ . '/../Helpers/ValidationHelper.php';

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
            // Phase 6: Enhanced validation
            $validation = ValidationHelper::validateFacilityData($_POST);

            if (!$validation['valid']) {
                $_SESSION['error_message'] = implode('<br>', array_merge(...array_values($validation['errors'])));
                header('Location: ?controller=admin&action=management&error=validation_failed');
                exit;
            }

            $validatedData = $validation['data'];
            $facility = new Facility();
            $facility->name = $validatedData['name'];
            $facility->capacity = $validatedData['capacity'];
            $facility->rate = $validatedData['rate'];
            $facility->shortDescription = $validatedData['short_description'];
            $facility->fullDescription = $validatedData['description'];
            $facility->resortId = $validatedData['resort_id'];

            $facilityId = Facility::create($facility);

            if ($facilityId) {
                $photoURLs = $this->handlePhotoUpload('photos', 'facilities');
                if (!empty($photoURLs)) {
                    Facility::setMainPhoto($facilityId, $photoURLs[0]);
                    foreach ($photoURLs as $url) {
                        Facility::addPhoto($facilityId, $url);
                    }
                }
                header('Location: ?controller=admin&action=management&status=facility_added');
            } else {
                header('Location: ?controller=admin&action=management&error=add_failed');
            }
            exit();
        }
    }

    public function editFacility() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Phase 6: Enhanced validation
            $validation = ValidationHelper::validateFacilityData($_POST);

            if (!$validation['valid']) {
                $_SESSION['error_message'] = implode('<br>', array_merge(...array_values($validation['errors'])));
                header('Location: ?controller=admin&action=management&error=validation_failed');
                exit;
            }

            $validatedData = $validation['data'];
            $facilityId = filter_input(INPUT_POST, 'facilityId', FILTER_VALIDATE_INT);
            if (!$facilityId) { die('Invalid Facility ID.'); }

            $facility = new Facility();
            $facility->facilityId = $facilityId;
            $facility->name = $validatedData['name'];
            $facility->capacity = $validatedData['capacity'];
            $facility->rate = $validatedData['rate'];
            $facility->shortDescription = $validatedData['short_description'];
            $facility->fullDescription = $validatedData['description'];

            // Handle new photo uploads
            $newPhotoURLs = $this->handlePhotoUpload('photos', 'facilities');
            if (!empty($newPhotoURLs)) {
                foreach ($newPhotoURLs as $url) {
                    Facility::addPhoto($facilityId, $url);
                }
            }

            if (Facility::update($facility)) {
                header('Location: ?controller=admin&action=management&status=facility_updated');
            } else {
                header('Location: ?controller=admin&action=management&error=update_failed');
            }
            exit();
        }
    }
    
    public function getFacilityJson() {
        header('Content-Type: application/json');
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Facility ID not specified.']);
            exit();
        }
        $facilityId = $_GET['id'];
        $facility = Facility::findById($facilityId);

        if ($facility) {
            echo json_encode($facility);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Facility not found.']);
        }
        exit();
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

        $resortsWithFacilities = Resort::findAllWithFacilities();
        include __DIR__ . '/../Views/admin/management/index.php';
    }

    // Resort Management Logic (Moved from ResortController)
    public function storeResort() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Phase 6: Enhanced validation for resort data
            // Note: A 'validateResortData' method should be added to ValidationHelper.php
            // For now, we assume it exists and mirrors the facility validation structure.
            // Let's proceed with a placeholder validation.
            $resort = new Resort();
            $resort->name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $resort->address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
            $resort->contactPerson = filter_input(INPUT_POST, 'contactPerson', FILTER_SANITIZE_STRING);
            $resort->shortDescription = filter_input(INPUT_POST, 'shortDescription', FILTER_SANITIZE_STRING);
            $resort->fullDescription = filter_input(INPUT_POST, 'fullDescription', FILTER_SANITIZE_STRING);
            
            $resortId = Resort::create($resort);

            if ($resortId) {
                $photoURLs = $this->handlePhotoUpload('photos');
                if (!empty($photoURLs)) {
                    // Set the first photo as the main photo
                    Resort::setMainPhoto($resortId, $photoURLs[0]);
                    // Add all photos to the gallery
                    foreach ($photoURLs as $url) {
                        Resort::addPhoto($resortId, $url);
                    }
                }
                header('Location: ?controller=admin&action=management&status=resort_added');
            } else {
                header('Location: ?controller=admin&action=management&error=add_failed');
            }
            exit();
        }
    }

    public function updateResort() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resortId = filter_input(INPUT_POST, 'resortId', FILTER_VALIDATE_INT);
            if (!$resortId) { die('Invalid Resort ID.'); }

            $resort = Resort::findById($resortId);
            if (!$resort) { die('Resort not found.'); }

            // Phase 6: Enhanced validation for resort data
            $resort->name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $resort->address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
            $resort->contactPerson = filter_input(INPUT_POST, 'contactPerson', FILTER_SANITIZE_STRING);
            $resort->shortDescription = filter_input(INPUT_POST, 'shortDescription', FILTER_SANITIZE_STRING);
            $resort->fullDescription = filter_input(INPUT_POST, 'fullDescription', FILTER_SANITIZE_STRING);
            
            // Handle new photo uploads
            $newPhotoURLs = $this->handlePhotoUpload('photos', 'resorts');
            if (!empty($newPhotoURLs)) {
                foreach ($newPhotoURLs as $url) {
                    Resort::addPhoto($resortId, $url);
                }
            }
            
            if (Resort::update($resort)) {
                header('Location: ?controller=admin&action=management&status=resort_updated');
            } else {
                header('Location: ?controller=admin&action=management&error=update_failed');
            }
            exit();
        }
    }

    public function destroyResort() {
        $resortId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$resortId) {
            die('Invalid Resort ID.');
        }

        // Check for dependent facilities before deleting
        $facilities = Facility::findByResortId($resortId);
        if (!empty($facilities)) {
            header('Location: ?controller=admin&action=management&error=delete_has_facilities');
            exit();
        }

        if (Resort::delete($resortId)) {
            header('Location: ?controller=admin&action=management&status=resort_deleted');
        } else {
            header('Location: ?controller=admin&action=management&error=delete_failed');
        }
        exit();
    }

    public function getResortJson() {
        header('Content-Type: application/json');
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Resort ID not specified.']);
            exit();
        }
        $resortId = $_GET['id'];
        $resort = Resort::findById($resortId);

        if ($resort) {
            // The findById method already populates the photos property
            echo json_encode($resort);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Resort not found.']);
        }
        exit();
    }


    public function setResortMainPhoto() {
        $resortId = filter_input(INPUT_GET, 'resortId', FILTER_VALIDATE_INT);
        $photoId = filter_input(INPUT_GET, 'photoId', FILTER_VALIDATE_INT);

        if (!$resortId || !$photoId) { die('Invalid parameters.'); }
        
        $photo = Resort::findPhotoById($photoId);
        if ($photo && $photo['ResortID'] == $resortId) {
            Resort::setMainPhoto($resortId, $photo['PhotoURL']);
            header('Location: ?controller=admin&action=management&status=main_photo_set');
        } else {
            header('Location: ?controller=admin&action=management&error=set_main_failed');
        }
        exit();
    }

    public function deleteResortPhoto() {
        $resortId = filter_input(INPUT_GET, 'resortId', FILTER_VALIDATE_INT);
        $photoId = filter_input(INPUT_GET, 'photoId', FILTER_VALIDATE_INT);

        if (!$resortId || !$photoId) { die('Invalid parameters.'); }

        $photo = Resort::findPhotoById($photoId);
        if ($photo && $photo['ResortID'] == $resortId) {
            // Physical file deletion
            $filePath = __DIR__ . '/../../' . ltrim($photo['PhotoURL'], '/');
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            // DB record deletion
            Resort::deletePhoto($photoId);

            // If the deleted photo was the main photo, pick a new main one
            $resort = Resort::findById($resortId);
            if ($resort->mainPhotoURL === $photo['PhotoURL']) {
                $remainingPhotos = Resort::getPhotos($resortId);
                $newMainPhoto = !empty($remainingPhotos) ? $remainingPhotos[0]['PhotoURL'] : null;
                Resort::setMainPhoto($resortId, $newMainPhoto);
            }

            header('Location: ?controller=admin&action=management&status=photo_deleted');
        } else {
            header('Location: ?controller=admin&action=management&error=delete_failed');
        }
        exit();
    }


    public function setFacilityMainPhoto() {
        $facilityId = filter_input(INPUT_GET, 'facilityId', FILTER_VALIDATE_INT);
        $photoId = filter_input(INPUT_GET, 'photoId', FILTER_VALIDATE_INT);

        if (!$facilityId || !$photoId) { die('Invalid parameters.'); }
        
        $photo = Facility::findPhotoById($photoId);
        if ($photo && $photo['FacilityID'] == $facilityId) {
            Facility::setMainPhoto($facilityId, $photo['PhotoURL']);
            header('Location: ?controller=admin&action=management&status=main_photo_set');
        } else {
            header('Location: ?controller=admin&action=management&error=set_main_failed');
        }
        exit();
    }

    public function deleteFacilityPhoto() {
        $facilityId = filter_input(INPUT_GET, 'facilityId', FILTER_VALIDATE_INT);
        $photoId = filter_input(INPUT_GET, 'photoId', FILTER_VALIDATE_INT);

        if (!$facilityId || !$photoId) { die('Invalid parameters.'); }

        $photo = Facility::findPhotoById($photoId);
        if ($photo && $photo['FacilityID'] == $facilityId) {
            $filePath = __DIR__ . '/../../' . ltrim($photo['PhotoURL'], '/');
            if (file_exists($filePath)) { unlink($filePath); }
            
            Facility::deletePhoto($photoId);

            $facility = Facility::findById($facilityId);
            if ($facility->mainPhotoURL === $photo['PhotoURL']) {
                $remainingPhotos = Facility::getPhotos($facilityId);
                $newMainPhoto = !empty($remainingPhotos) ? $remainingPhotos[0]['PhotoURL'] : null;
                Facility::setMainPhoto($facilityId, $newMainPhoto);
            }
            header('Location: ?controller=admin&action=management&status=photo_deleted');
        } else {
            header('Location: ?controller=admin&action=management&error=delete_failed');
        }
        exit();
    }

    private function handlePhotoUpload($fileInputName, $type = 'resorts') {
        $uploadedPaths = [];
        if (isset($_FILES[$fileInputName])) {
            $files = $_FILES[$fileInputName];
            $uploadDir = __DIR__ . '/../../public/uploads/' . $type . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($files['tmp_name'] as $key => $tmpName) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = uniqid() . '-' . basename($files['name'][$key]);
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $uploadedPaths[] = '/public/uploads/' . $type . '/' . $fileName;
                    }
                }
            }
        }
        return $uploadedPaths;
    }


    public function getPaymentMethodsJson() {
        header('Content-Type: application/json');
        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        if (!$resortId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid Resort ID']);
            exit();
        }

        $methods = ResortPaymentMethods::findByResortId($resortId, false);
        echo json_encode($methods);
        exit();
    }

    public function addPaymentMethod() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resortId = filter_input(INPUT_POST, 'resort_id', FILTER_VALIDATE_INT);
            $methodName = filter_input(INPUT_POST, 'method_name', FILTER_UNSAFE_RAW);
            $methodDetails = filter_input(INPUT_POST, 'method_details', FILTER_UNSAFE_RAW);

            // Check if this is an AJAX request
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            $validationErrors = [];
            if (!$resortId) $validationErrors[] = "Invalid resort ID.";
            if (!$methodName) $validationErrors[] = "Payment method name is required.";
            if (!$methodDetails) $validationErrors[] = "Payment method details are required.";

            if (!empty($validationErrors)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => implode(' ', $validationErrors)
                    ]);
                    exit();
                } else {
                    $_SESSION['error_message'] = implode('<br>', $validationErrors);
                    header('Location: ?controller=admin&action=management');
                    exit();
                }
            }

            $paymentMethod = new ResortPaymentMethods();
            $paymentMethod->resortId = $resortId;
            $paymentMethod->methodName = $methodName;
            $paymentMethod->methodDetails = $methodDetails;
            $paymentMethod->isActive = true;

            if (ResortPaymentMethods::create($paymentMethod)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Payment method added successfully!'
                    ]);
                    exit();
                } else {
                    $_SESSION['success_message'] = "Payment method added successfully.";
                    header('Location: ?controller=admin&action=management');
                    exit();
                }
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to add payment method.'
                    ]);
                    exit();
                } else {
                    $_SESSION['error_message'] = "Failed to add payment method.";
                    header('Location: ?controller=admin&action=management');
                    exit();
                }
            }
        }
    }

    public function deletePaymentMethod() {
        $methodId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($methodId && ResortPaymentMethods::delete($methodId)) {
            $_SESSION['success_message'] = "Payment method deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete payment method.";
        }
        header('Location: ?controller=admin&action=management');
        exit();
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
        // Return a JSON response for AJAX request
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function getResortScheduleJson() {
        header('Content-Type: application/json');
        try {
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Resort ID not specified.']);
                exit();
            }
            $resortId = $_GET['id'];
            $blocks = BlockedResortAvailability::findByResortId($resortId);
            echo json_encode($blocks);
        } catch (Exception $e) {
            http_response_code(500); // Internal Server Error
            // Log the actual error to the server's error log for debugging
            error_log('Error in getResortScheduleJson: ' . $e->getMessage());
            // Send a generic error message to the client
            echo json_encode(['error' => 'A server error occurred while fetching the schedule.']);
        }
        exit();
    }

    public function blockFacilityAvailability() {
        if ($_SESSION['role'] !== 'Admin') {
            http_response_code(403);
            exit('Forbidden');
        }
        $facilityId = filter_input(INPUT_POST, 'facilityId', FILTER_VALIDATE_INT);
        $blockDate = filter_input(INPUT_POST, 'blockDate', FILTER_UNSAFE_RAW);
        $reason = filter_input(INPUT_POST, 'reason', FILTER_UNSAFE_RAW);

        if ($facilityId && $blockDate) {
            BlockedFacilityAvailability::create($facilityId, $blockDate, $reason);
        }
        header('Location: ?controller=admin&action=management');
        exit;
    }

    public function deleteFacilityAvailabilityBlock() {
        if ($_SESSION['role'] !== 'Admin') {
            http_response_code(403);
            exit('Forbidden');
        }
        $blockId = filter_input(INPUT_GET, 'block_id', FILTER_VALIDATE_INT);
        if ($blockId) {
            BlockedFacilityAvailability::delete($blockId);
        }
        // Return a JSON response for AJAX request
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function getFacilityScheduleJson() {
        header('Content-Type: application/json');
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Facility ID not specified.']);
            exit();
        }
        $facilityId = $_GET['id'];
        $blocks = BlockedFacilityAvailability::findByFacilityId($facilityId);
        echo json_encode($blocks);
        exit();
    }

    /**
     * PHASE 5: UNIFIED BOOKING/PAYMENT MANAGEMENT
     */

    /**
     * Show unified booking and payment management interface
     */
    public function unifiedBookingManagement() {
        if ($_SESSION['role'] !== 'Admin') {
            http_response_code(403);
            require_once __DIR__ . '/../Views/errors/403.php';
            exit();
        }

        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        $status = filter_input(INPUT_GET, 'status', FILTER_UNSAFE_RAW);
        
        $resorts = Resort::findAll();
        
        // Get bookings with payment information
        $bookings = Booking::getBookingsWithPaymentDetails($resortId, $status);
        
        // Get pending payment count for notification
        $pendingPaymentCount = Payment::getPendingPaymentCount();
        
        require_once __DIR__ . '/../Views/admin/unified_booking_management.php';
    }

    /**
     * Update booking with payment information
     */
    public function updateBookingPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=admin&action=unifiedBookingManagement');
            exit();
        }

        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        $bookingStatus = filter_input(INPUT_POST, 'booking_status', FILTER_UNSAFE_RAW);
        $paymentAmount = filter_input(INPUT_POST, 'payment_amount', FILTER_VALIDATE_FLOAT);
        $paymentMethod = filter_input(INPUT_POST, 'payment_method', FILTER_UNSAFE_RAW);
        $paymentStatus = filter_input(INPUT_POST, 'payment_status', FILTER_UNSAFE_RAW);

        if (!$bookingId) {
            $_SESSION['error_message'] = "Invalid booking ID.";
            header('Location: ?controller=admin&action=unifiedBookingManagement');
            exit();
        }

        // Update booking status if provided
        if ($bookingStatus) {
            Booking::updateStatus($bookingId, $bookingStatus);
        }

        // Add payment if provided
        if ($paymentAmount && $paymentMethod && $paymentStatus) {
            $payment = new Payment();
            $payment->bookingId = $bookingId;
            $payment->amount = $paymentAmount;
            $payment->paymentMethod = $paymentMethod;
            $payment->status = $paymentStatus;
            
            if (Payment::create($payment)) {
                // Update booking remaining balance
                $booking = Booking::findById($bookingId);
                if ($booking && $booking->TotalAmount > 0) {
                    $totalPaid = Payment::getTotalPaidAmount($bookingId);
                    $remainingBalance = max(0, $booking->TotalAmount - $totalPaid);
                    Booking::updateRemainingBalance($bookingId, $remainingBalance);
                }
                $_SESSION['success_message'] = "Payment added successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to add payment.";
            }
        }

        header('Location: ?controller=admin&action=unifiedBookingManagement');
        exit();
    }

    /**
     * PRICING MANAGEMENT SYSTEM
     */

    /**
     * Show pricing management interface
     */
    public function pricingManagement() {
        if ($_SESSION['role'] !== 'Admin') {
            http_response_code(403);
            require_once __DIR__ . '/../Views/errors/403.php';
            exit();
        }

        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        $resorts = Resort::findAll();
        
        $resortPricing = [];
        $facilityPricing = [];
        
        if ($resortId) {
            $resortPricing = ResortTimeframePricing::findByResortId($resortId);
            $facilityPricing = Facility::findByResortId($resortId);
        }
        
        require_once __DIR__ . '/../Views/admin/pricing_management.php';
    }

    /**
     * Update resort timeframe pricing
     */
    public function updateResortPricing() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=admin&action=pricingManagement');
            exit();
        }

        $resortId = filter_input(INPUT_POST, 'resort_id', FILTER_VALIDATE_INT);
        $timeframeType = filter_input(INPUT_POST, 'timeframe_type', FILTER_UNSAFE_RAW);
        $basePrice = filter_input(INPUT_POST, 'base_price', FILTER_VALIDATE_FLOAT);
        $weekendSurcharge = filter_input(INPUT_POST, 'weekend_surcharge', FILTER_VALIDATE_FLOAT);
        $holidaySurcharge = filter_input(INPUT_POST, 'holiday_surcharge', FILTER_VALIDATE_FLOAT);

        if (!$resortId || !$timeframeType || !$basePrice) {
            $_SESSION['error_message'] = "Invalid pricing data provided.";
            header('Location: ?controller=admin&action=pricingManagement&resort_id=' . $resortId);
            exit();
        }

        // Check if pricing already exists
        $existingPricing = ResortTimeframePricing::findByResortAndTimeframe($resortId, $timeframeType);
        
        if ($existingPricing) {
            // Update existing pricing
            $existingPricing->basePrice = $basePrice;
            $existingPricing->weekendSurcharge = $weekendSurcharge ?? 0;
            $existingPricing->holidaySurcharge = $holidaySurcharge ?? 0;
            
            if (ResortTimeframePricing::update($existingPricing)) {
                $_SESSION['success_message'] = "Pricing updated successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to update pricing.";
            }
        } else {
            // Create new pricing
            $pricing = new ResortTimeframePricing();
            $pricing->resortId = $resortId;
            $pricing->timeframeType = $timeframeType;
            $pricing->basePrice = $basePrice;
            $pricing->weekendSurcharge = $weekendSurcharge ?? 0;
            $pricing->holidaySurcharge = $holidaySurcharge ?? 0;
            
            if (ResortTimeframePricing::create($pricing)) {
                $_SESSION['success_message'] = "Pricing created successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to create pricing.";
            }
        }

        header('Location: ?controller=admin&action=pricingManagement&resort_id=' . $resortId);
        exit();
    }

    /**
     * Update facility fixed pricing
     */
    public function updateFacilityPricing() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=admin&action=pricingManagement');
            exit();
        }

        $facilityId = filter_input(INPUT_POST, 'facility_id', FILTER_VALIDATE_INT);
        $rate = filter_input(INPUT_POST, 'rate', FILTER_VALIDATE_FLOAT);

        if (!$facilityId || !$rate) {
            $_SESSION['error_message'] = "Invalid facility pricing data.";
            header('Location: ?controller=admin&action=pricingManagement');
            exit();
        }

        if (Facility::updateRate($facilityId, $rate)) {
            $_SESSION['success_message'] = "Facility pricing updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update facility pricing.";
        }

        // Get resort ID for redirect
        $facility = Facility::findById($facilityId);
        $resortId = $facility ? $facility->resortId : null;

        header('Location: ?controller=admin&action=pricingManagement&resort_id=' . $resortId);
        exit();
    }

    /**
     * ADVANCED BLOCKING SYSTEM
     */

    /**
     * Show advanced blocking management
     */
    public function advancedBlocking() {
        if ($_SESSION['role'] !== 'Admin') {
            http_response_code(403);
            require_once __DIR__ . '/../Views/errors/403.php';
            exit();
        }

        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        $resorts = Resort::findAll();
        
        require_once __DIR__ . '/../Views/admin/advanced_blocking.php';
    }

    /**
     * Apply preset blocking (weekends, holidays)
     */
    public function applyPresetBlocking() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=admin&action=advancedBlocking');
            exit();
        }

        $resortId = filter_input(INPUT_POST, 'resort_id', FILTER_VALIDATE_INT);
        $presetType = filter_input(INPUT_POST, 'preset_type', FILTER_UNSAFE_RAW);
        $startDate = filter_input(INPUT_POST, 'start_date', FILTER_UNSAFE_RAW);
        $endDate = filter_input(INPUT_POST, 'end_date', FILTER_UNSAFE_RAW);
        $reason = filter_input(INPUT_POST, 'reason', FILTER_UNSAFE_RAW);

        if (!$resortId || !$presetType || !$startDate || !$endDate) {
            $_SESSION['error_message'] = "Invalid blocking parameters.";
            header('Location: ?controller=admin&action=advancedBlocking&resort_id=' . $resortId);
            exit();
        }

        $blockedCount = 0;
        $currentDate = new DateTime($startDate);
        $lastDate = new DateTime($endDate);

        while ($currentDate <= $lastDate) {
            $shouldBlock = false;
            $dateStr = $currentDate->format('Y-m-d');
            $dayOfWeek = $currentDate->format('w'); // 0 = Sunday, 6 = Saturday

            switch ($presetType) {
                case 'weekends':
                    $shouldBlock = ($dayOfWeek == 0 || $dayOfWeek == 6); // Sunday or Saturday
                    break;
                case 'philippine_holidays':
                    $shouldBlock = $this->isPhilippineHoliday($currentDate);
                    break;
                case 'all_dates':
                    $shouldBlock = true;
                    break;
            }

            if ($shouldBlock) {
                if (BlockedResortAvailability::create($resortId, $dateStr, $reason ?: $presetType)) {
                    $blockedCount++;
                }
            }

            $currentDate->modify('+1 day');
        }

        $_SESSION['success_message'] = "Blocked $blockedCount dates successfully!";
        header('Location: ?controller=admin&action=advancedBlocking&resort_id=' . $resortId);
        exit();
    }

    /**
     * Check if a date is a Philippine holiday
     */
    private function isPhilippineHoliday($date) {
        $holidays = $this->getPhilippineHolidays($date->format('Y'));
        $dateStr = $date->format('m-d');
        return in_array($dateStr, $holidays);
    }

    /**
     * Get Philippine holidays for a given year
     */
    private function getPhilippineHolidays($year) {
        return [
            '01-01', // New Year's Day
            '04-09', // Araw ng Kagitingan (Day of Valor)
            '05-01', // Labor Day
            '06-12', // Independence Day
            '08-29', // National Heroes Day (last Monday of August, approximate)
            '11-30', // Bonifacio Day
            '12-25', // Christmas Day
            '12-30', // Rizal Day
            '12-31'  // New Year's Eve
        ];
    }

    /**
     * Get pricing summary for dashboard
     */
    public function getPricingSummary() {
        header('Content-Type: application/json');
        
        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        
        if (!$resortId) {
            echo json_encode(['error' => 'Resort ID required']);
            exit();
        }

        $pricing = ResortTimeframePricing::findByResortId($resortId);
        $facilities = Facility::findByResortId($resortId);
        
        $summary = [
            'timeframe_pricing' => $pricing,
            'facility_pricing' => $facilities
        ];
        
        echo json_encode($summary);
        exit();
    }

    /**
     * PHASE 6: BOOKING LIFECYCLE MANAGEMENT
     */

    /**
     * Process automated lifecycle recommendations
     */
    public function processLifecycleRecommendations() {
        if ($_SESSION['role'] !== 'Admin') {
            http_response_code(403);
            require_once __DIR__ . '/../Views/errors/403.php';
            exit();
        }

        $results = BookingLifecycleManager::processAllBookings();
        
        $_SESSION['success_message'] = "Processed {$results['processed']} bookings: " .
            "{$results['confirmed']} confirmed, {$results['cancelled']} cancelled, " .
            "{$results['completed']} completed.";
            
        if (!empty($results['errors'])) {
            $_SESSION['error_message'] = "Some errors occurred: " . implode(', ', $results['errors']);
        }

        header('Location: ?controller=admin&action=unifiedBookingManagement');
        exit();
    }

    /**
     * Apply individual lifecycle recommendation
     */
    public function applyLifecycleRecommendation() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }

        if ($_SESSION['role'] !== 'Admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        $newStatus = filter_input(INPUT_POST, 'new_status', FILTER_UNSAFE_RAW);
        $reason = filter_input(INPUT_POST, 'reason', FILTER_UNSAFE_RAW);

        if (!$bookingId || !$newStatus) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit();
        }

        $result = BookingLifecycleManager::changeBookingStatus(
            $bookingId,
            $newStatus,
            $_SESSION['user_id'],
            $reason ?: 'Lifecycle recommendation applied'
        );

        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

    /**
     * Get booking audit trail data for modal
     */
    public function getBookingAuditTrail() {
        if ($_SESSION['role'] !== 'Admin') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
        if (!$bookingId) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid booking ID']);
            exit();
        }

        $auditTrail = BookingAuditTrail::getBookingAuditTrail($bookingId, 50);
        
        $formattedTrail = [];
        foreach ($auditTrail as $entry) {
            $formattedTrail[] = [
                'id' => $entry->AuditID,
                'action' => $entry->Action,
                'fieldName' => $entry->FieldName,
                'oldValue' => $entry->OldValue,
                'newValue' => $entry->NewValue,
                'reason' => $entry->ChangeReason,
                'username' => $entry->Username ?? 'System',
                'role' => $entry->Role ?? 'Unknown',
                'createdAt' => $entry->CreatedAt,
                'description' => BookingAuditTrail::getChangeDescription($entry)
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'bookingId' => $bookingId,
            'auditTrail' => $formattedTrail,
            'totalEntries' => count($formattedTrail)
        ]);
        exit();
    }

    /**
     * Get payment schedule data for modal
     */
    public function getPaymentScheduleData() {
        if ($_SESSION['role'] !== 'Admin') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
        if (!$bookingId) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid booking ID']);
            exit();
        }

        $schedule = PaymentSchedule::findByBookingId($bookingId);
        $summary = PaymentSchedule::getScheduleSummary($bookingId);
        $nextPayment = PaymentSchedule::getNextPaymentDue($bookingId);

        $formattedSchedule = [];
        foreach ($schedule as $item) {
            $formattedSchedule[] = [
                'scheduleId' => $item->ScheduleID,
                'installmentNumber' => $item->InstallmentNumber,
                'dueDate' => $item->DueDate,
                'amount' => number_format($item->Amount, 2),
                'status' => $item->Status,
                'paymentId' => $item->PaymentID,
                'isOverdue' => ($item->Status === 'Pending' && strtotime($item->DueDate) < time())
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'bookingId' => $bookingId,
            'schedule' => $formattedSchedule,
            'summary' => [
                'totalInstallments' => $summary->TotalInstallments ?? 0,
                'totalAmount' => number_format($summary->TotalAmount ?? 0, 2),
                'paidAmount' => number_format($summary->PaidAmount ?? 0, 2),
                'remainingAmount' => number_format($summary->RemainingAmount ?? 0, 2),
                'overdueCount' => $summary->OverdueCount ?? 0
            ],
            'nextPayment' => $nextPayment ? [
                'installmentNumber' => $nextPayment->InstallmentNumber,
                'dueDate' => $nextPayment->DueDate,
                'amount' => number_format($nextPayment->Amount, 2),
                'status' => $nextPayment->Status
            ] : null
        ]);
        exit();
    }

    /**
     * Manual trigger for automated processing
     */
    public function runAutomatedProcessing() {
        if ($_SESSION['role'] !== 'Admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $results = BookingLifecycleManager::processAllBookings();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Automated processing completed",
            'results' => $results
        ]);
        exit();
    }

    /**
     * Get lifecycle dashboard data
     */
    public function getLifecycleDashboard() {
        if ($_SESSION['role'] !== 'Admin') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $resortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
        
        $summary = BookingLifecycleManager::getBookingLifecycleSummary($resortId);
        $requiresAttention = BookingLifecycleManager::getBookingsRequiringAttention($resortId);
        $overduePayments = PaymentSchedule::getOverdueSchedules($resortId);
        $auditStats = BookingAuditTrail::getAuditStatistics($resortId, 30);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'summary' => $summary,
            'requiresAttention' => $requiresAttention,
            'overduePayments' => $overduePayments,
            'auditStatistics' => $auditStats,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }

    /**
     * Update payment schedule manually
     */
    public function updatePaymentSchedule() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit();
        }

        if ($_SESSION['role'] !== 'Admin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $scheduleId = filter_input(INPUT_POST, 'schedule_id', FILTER_VALIDATE_INT);
        $paymentId = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
        $action = filter_input(INPUT_POST, 'action', FILTER_UNSAFE_RAW);

        if (!$scheduleId || !$action) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit();
        }

        $result = false;
        switch ($action) {
            case 'mark_paid':
                if ($paymentId) {
                    $result = PaymentSchedule::markAsPaid($scheduleId, $paymentId);
                }
                break;
            case 'cancel_schedule':
                // This would require a method to cancel individual schedule items
                $result = true; // Placeholder for now
                break;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Payment schedule updated successfully' : 'Failed to update payment schedule'
        ]);
        exit();
    }
    /**
     * Get comprehensive booking details for the on-site management modal
     */
    public function getBookingDetailsForManagement() {
        header('Content-Type: application/json');
        if ($_SESSION['role'] !== 'Admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit();
        }

        $bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
        if (!$bookingId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid Booking ID.']);
            exit();
        }

        try {
            $booking = Booking::findById($bookingId);
            if (!$booking) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Booking not found.']);
                exit();
            }

            $bookedFacilities = BookingFacilities::findByBookingId($bookingId);
            
            // We only need the IDs for the initial check in the UI
            $booking->BookedFacilities = $bookedFacilities;

            echo json_encode(['success' => true, 'booking' => $booking]);
        } catch (Exception $e) {
            http_response_code(500);
            error_log("Error in getBookingDetailsForManagement: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'A server error occurred.']);
        }
        exit();
    }

    /**
     * Handle comprehensive booking updates from the on-site management modal
     */
    public function adminUpdateBooking() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=admin&action=unifiedBookingManagement');
            exit();
        }

        if ($_SESSION['role'] !== 'Admin') {
            $_SESSION['error_message'] = "You are not authorized to perform this action.";
            header('Location: ?controller=admin&action=unifiedBookingManagement');
            exit();
        }

        // --- Data Collection ---
        $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
        $resortId = filter_input(INPUT_POST, 'resort_id', FILTER_VALIDATE_INT);
        $bookingStatus = filter_input(INPUT_POST, 'booking_status', FILTER_UNSAFE_RAW);
        
        // Facility IDs will be an array
        $facilityIds = $_POST['facilities'] ?? [];
        
        $paymentAmount = filter_input(INPUT_POST, 'payment_amount', FILTER_VALIDATE_FLOAT);
        $paymentMethod = filter_input(INPUT_POST, 'payment_method', FILTER_UNSAFE_RAW);
        
        // --- Validation ---
        if (!$bookingId || !$resortId || !$bookingStatus) {
            $_SESSION['error_message'] = "Missing required booking information.";
            header('Location: ?controller=admin&action=unifiedBookingManagement');
            exit();
        }

        // --- Prepare data for the model ---
        $updateData = [
            'booking_id' => $bookingId,
            'status' => $bookingStatus,
            'facility_ids' => $facilityIds
        ];

        $paymentData = null;
        if ($paymentAmount > 0 && !empty($paymentMethod)) {
            $paymentData = [
                'amount' => $paymentAmount,
                'method' => $paymentMethod,
                'status' => 'Verified' // Admin-added payments are auto-verified
            ];
        }
        
        $adminUserId = $_SESSION['user_id'];

        // --- Call the Model to process the update ---
        $result = Booking::adminUpdateBooking($updateData, $paymentData, $adminUserId);

        if ($result['success']) {
            $_SESSION['success_message'] = "Booking #{$bookingId} has been updated successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to update booking #{$bookingId}: " . ($result['error'] ?? 'An unknown error occurred.');
        }

        header('Location: ?controller=admin&action=unifiedBookingManagement');
        exit();
    }
}
