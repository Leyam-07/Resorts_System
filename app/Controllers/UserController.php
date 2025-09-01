<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Helpers/Notification.php';

class UserController {
    private $db;
    private $userModel;

    public function __construct() {
        try {
            $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
        $this->userModel = new User($this->db);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize POST data
            $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password']; // No sanitization needed before hashing
            $confirmPassword = $_POST['confirm_password'];
            $firstName = filter_input(INPUT_POST, 'firstName', FILTER_UNSAFE_RAW);
            $lastName = filter_input(INPUT_POST, 'lastName', FILTER_UNSAFE_RAW);
            $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_UNSAFE_RAW);

            // Validate that passwords match
            if ($password !== $confirmPassword) {
                header('Location: index.php?action=showRegisterForm&error=password_mismatch');
                exit();
            }

            // Attempt to create the user
            $result = $this->userModel->create($username, $password, $email, 'Customer', $firstName, $lastName, $phoneNumber);

            if ($result === true) {
                // Find the new user to get their details
                $newUser = $this->userModel->findByUsername($username);
                if ($newUser) {
                    // Send welcome email
                    Notification::sendWelcomeEmail($newUser['UserID']);
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
            // Sanitize POST data
            $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password']; // No sanitization needed before hashing
            $confirmPassword = $_POST['confirm_password'];
            $firstName = filter_input(INPUT_POST, 'firstName', FILTER_UNSAFE_RAW);
            $lastName = filter_input(INPUT_POST, 'lastName', FILTER_UNSAFE_RAW);
            $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_UNSAFE_RAW);

            // Validate that passwords match
            if ($password !== $confirmPassword) {
                header('Location: index.php?action=showAdminRegisterForm&error=password_mismatch');
                exit();
            }

            // Attempt to create the user with Admin role
            $result = $this->userModel->create($username, $password, $email, 'Admin', $firstName, $lastName, $phoneNumber);

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

            $user = $this->userModel->findByUsername($username);

            if ($user && password_verify($password, $user['Password'])) {
                // Password is correct, destroy old session and start new one
                session_destroy();
                session_start();
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['role'] = $user['Role'];

                header('Location: index.php'); // Redirect to the main dashboard
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
            header('Location: index.php?action=login');
            exit();
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
            $result = $this->userModel->update($userId, $username, $email, $firstName, $lastName, $phoneNumber);

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
                $this->userModel->updatePassword($userId, $password);
            }
            
            header('Location: ?controller=user&action=profile&status=updated');
            exit();

        } else {
            // Display profile form
            $user = $this->userModel->findById($userId);
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