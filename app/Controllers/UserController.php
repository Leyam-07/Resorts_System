<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Helpers/Notification.php';
require_once __DIR__ . '/../Models/Facility.php';
require_once __DIR__ . '/../Models/Feedback.php';
require_once __DIR__ . '/../Models/Resort.php';
require_once __DIR__ . '/../Helpers/ValidationHelper.php';

require_once __DIR__ . '/../Helpers/AsyncHelper.php';

class UserController {

   public function dashboard() {
       $resorts = Resort::findAll();
       include __DIR__ . '/../Views/dashboard.php';
   }

   public function getFacilityDetails() {
       if (!isset($_GET['id'])) {
           http_response_code(400);
           echo json_encode(['error' => 'Facility ID not specified.']);
           exit();
       }
       $facilityId = $_GET['id'];
       $facility = Facility::findById($facilityId);

       if ($facility) {
           header('Content-Type: application/json');
           // Format descriptions for display
           $facility->shortDescription = nl2br(htmlspecialchars($facility->shortDescription));
           $facility->fullDescription = nl2br(htmlspecialchars($facility->fullDescription));
           echo json_encode($facility);
       } else {
           http_response_code(404);
           echo json_encode(['error' => 'Facility not found.']);
       }
       exit();
   }

   public function getFacilityFeedback() {
       if (!isset($_GET['id'])) {
           http_response_code(400);
           echo json_encode(['error' => 'Facility ID not specified.']);
           exit();
       }
       $facilityId = $_GET['id'];
       $feedback = Feedback::findByFacilityId($facilityId);

       if ($feedback) {
           header('Content-Type: application/json');
           echo json_encode($feedback);
       } else {
           http_response_code(404);
           echo json_encode([]); // Return empty array if no feedback
       }
       exit();
   }

   public function getResortDetails() {
       if (!isset($_GET['id'])) {
           http_response_code(400);
           echo json_encode(['error' => 'Resort ID not specified.']);
           exit();
       }
       $resortId = $_GET['id'];
       $resort = Resort::findById($resortId);

       if ($resort) {
           header('Content-Type: application/json');
           // Format descriptions for display
           $resort->shortDescription = nl2br(htmlspecialchars($resort->shortDescription));
           $resort->fullDescription = nl2br(htmlspecialchars($resort->fullDescription));
           echo json_encode($resort);
       } else {
           http_response_code(404);
           echo json_encode(['error' => 'Resort not found.']);
       }
       exit();
   }

   public function getResortFacilities() {
       if (!isset($_GET['id'])) {
           http_response_code(400);
           echo json_encode(['error' => 'Resort ID not specified.']);
           exit();
       }
       $resortId = $_GET['id'];
       $facilities = Facility::findByResortId($resortId);

       if ($facilities) {
           header('Content-Type: application/json');
           // Format descriptions for display
           foreach ($facilities as $facility) {
               $facility->shortDescription = nl2br(htmlspecialchars($facility->shortDescription));
               $facility->fullDescription = nl2br(htmlspecialchars($facility->fullDescription));
           }
           echo json_encode($facilities);
       } else {
           http_response_code(404);
           echo json_encode([]); // Return empty array if no facilities
       }
       exit();
   }

   public function getResortFeedback() {
       if (!isset($_GET['id'])) {
           http_response_code(400);
           echo json_encode(['error' => 'Resort ID not specified.']);
           exit();
       }
       $resortId = $_GET['id'];
       $feedback = Feedback::findByResortId($resortId);

       if ($feedback) {
           header('Content-Type: application/json');
           echo json_encode($feedback);
       } else {
           http_response_code(404);
           echo json_encode([]); // Return empty array if no feedback
       }
       exit();
   }
 
     public function register() {
         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             // Phase 6: Enhanced validation
            $validation = ValidationHelper::validateUserRegistration($_POST);

            if (!$validation['valid']) {
                $_SESSION['error_message'] = implode('<br>', array_merge(...array_values($validation['errors'])));
                $_SESSION['old_input'] = $_POST;
                header('Location: index.php?action=showRegisterForm');
                exit;
            }

            $validatedData = $validation['data'];

            // Attempt to create the user
            $result = User::create(
                $validatedData['username'],
                $validatedData['password'],
                $validatedData['email'],
                'Customer',
                $_POST['firstName'] ?? '',
                $_POST['lastName'] ?? '',
                $_POST['phoneNumber'] ?? ''
            );

            if ($result === true) {
                // Find the new user to get their details
                $newUser = User::findByUsername($validatedData['username']);
                if ($newUser) {
                    AsyncHelper::triggerEmailWorker('welcome_email', $newUser['UserID']);
                }
                // Redirect to login page on success
                header('Location: index.php?action=login&registration=success');
                exit();
            } else {
                // Handle registration failure
                header('Location: index.php?action=showRegisterForm&error=' . $result);
                exit();
            }
        }
    }

    public function showRegisterForm() {
        include __DIR__ . '/../Views/register.php';
    }

    public function registerAdmin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Phase 6: Enhanced validation
            $validation = ValidationHelper::validateUserRegistration($_POST);

            if (!$validation['valid']) {
                $_SESSION['error_message'] = implode('<br>', array_merge(...array_values($validation['errors'])));
                $_SESSION['old_input'] = $_POST;
                header('Location: index.php?action=showAdminRegisterForm');
                exit;
            }

            $validatedData = $validation['data'];

            // Attempt to create the user with Admin role
            $result = User::create(
                $validatedData['username'],
                $validatedData['password'],
                $validatedData['email'],
                'Admin',
                $_POST['firstName'] ?? '',
                $_POST['lastName'] ?? '',
                $_POST['phoneNumber'] ?? ''
            );

            if ($result === true) {
                // Redirect to login page on success
                header('Location: index.php?action=login&registration=success');
                exit();
            } else {
                // Handle registration failure
                header('Location: index.php?action=showAdminRegisterForm&error=' . $result);
                exit();
            }
        }
    }

    public function showAdminRegisterForm() {
        include __DIR__ . '/../Views/register-admin.php';
    }
 
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
            $password = $_POST['password'];

            $user = User::findByUsername($username);

            if ($user && password_verify($password, $user['Password'])) {
                // Password is correct, destroy old session and start new one
                session_destroy();
                session_start();
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['role'] = $user['Role'];

                if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Staff') {
                   header('Location: ?controller=admin&action=dashboard');
                } else {
                   header('Location: ?controller=user&action=dashboard');
                }
                exit();
            } else {
                // Invalid credentials, reload the login page with an error
                header('Location: index.php?action=login&error=invalid_credentials');
                exit();
            }
        } else {
            // Display the login form
            include __DIR__ . '/../Views/login.php';
        }
    }

    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            // Show guest-friendly profile page
            include __DIR__ . '/../Views/profile_guest.php';
            return;
        }

        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle profile update
            $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $firstName = filter_input(INPUT_POST, 'firstName', FILTER_UNSAFE_RAW);
            $lastName = filter_input(INPUT_POST, 'lastName', FILTER_UNSAFE_RAW);
            $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_UNSAFE_RAW);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            // Update user details
            $result = User::update($userId, $username, $email, $firstName, $lastName, $phoneNumber);

            if ($result) {
                // Update the session variables
                $_SESSION['username'] = $username;
            }

            // Update password if provided
            if (!empty($password)) {
                if ($password !== $confirmPassword) {
                    header('Location: ?controller=user&action=profile&error=password_mismatch');
                    exit();
                }
                User::updatePassword($userId, $password);
            }
            
            header('Location: ?controller=user&action=profile&status=updated');
            exit();

        } else {
            // Display profile form
            $user = User::findById($userId);
            include __DIR__ . '/../Views/profile.php';
        }
    }

    public function logout() {
       
        // Unset all session variables
        $_SESSION = array();

        // Destroy the session
        session_destroy();

        // Redirect to the login page
        header('Location: index.php?action=login&logout=success');
        exit();
    }
}

// This router is now handled by public/index.php
