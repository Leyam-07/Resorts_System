<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Booking.php';

class AdminController {
    private $db;
    private $userModel;

    public function __construct() {
        // Ensure user is an admin
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
            // Redirect to login page if not an admin
            header('Location: /?controller=user&action=login&error=unauthorized');
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

    public function users() {
        $users = $this->userModel->findAll();
        include __DIR__ . '/../Views/admin/users.php';
    }

    public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
            $firstName = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_STRING);
            $lastName = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_STRING);
            $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_STRING);
            $notes = $_POST['notes'];

            if ($password !== $confirmPassword) {
                header('Location: ?controller=admin&action=addUser&error=password_mismatch');
                exit();
            }

            $result = $this->userModel->create($username, $password, $email, $role, $firstName, $lastName, $phoneNumber, $notes);
            if ($result === true) {
                header('Location: ?controller=admin&action=users&status=user_added');
                exit();
            } else {
                header('Location: ?controller=admin&action=addUser&error=' . $result);
                exit();
            }
        } else {
            include __DIR__ . '/../Views/admin/add_user.php';
        }
    }

    public function editUser() {
        if (!isset($_GET['id'])) {
            die('User ID not specified.');
        }
        $userId = $_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle the form submission
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $firstName = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_STRING);
            $lastName = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_STRING);
            $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_STRING);
            $notes = $_POST['notes'];

            $result = $this->userModel->update($userId, $username, $email, $firstName, $lastName, $phoneNumber, $notes);

            if ($result === true) {
                // If admin edits their own profile, update session
                if ($userId == $_SESSION['user_id']) {
                    $_SESSION['username'] = $username;
                }
                header('Location: ?controller=admin&action=users&status=user_updated');
                exit();
            } else {
                header('Location: ?controller=admin&action=editUser&id=' . $userId . '&error=' . $result);
                exit();
            }
        } else {
            // Display the edit form
            $user = $this->userModel->findById($userId);
            if (!$user) {
                die('User not found.');
            }
            include __DIR__ . '/../Views/admin/edit_user.php';
        }
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

        include __DIR__ . '/../Views/admin/view_user_bookings.php';
    }
}