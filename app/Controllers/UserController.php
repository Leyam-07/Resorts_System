<?php

session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/User.php';

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
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password']; // No sanitization needed before hashing
            $firstName = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_STRING);
            $lastName = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_STRING);
            $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_STRING);

            // Attempt to create the user
            if ($this->userModel->create($username, $password, $email, 'Customer', $firstName, $lastName, $phoneNumber)) {
                // Redirect to login page on success
                header('Location: ../Views/login.php?registration=success');
                exit();
            } else {
                // Handle registration failure
                header('Location: ../Views/register.php?error=registration_failed');
                exit();
            }
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $password = $_POST['password'];

            $user = $this->userModel->findByUsername($username);

            if ($user && password_verify($password, $user['Password'])) {
                // Password is correct, start a new session
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['role'] = $user['Role'];

                // Redirect to a new dashboard page (to be created)
                header('Location: ../../public/index.php'); // Redirect to a main dashboard
                exit();
            } else {
                // Invalid credentials
                header('Location: ../Views/login.php?error=invalid_credentials');
                exit();
            }
        }
    }

    public function logout() {
        // Unset all session variables
        $_SESSION = array();

        // Destroy the session
        session_destroy();

        // Redirect to the login page
        header('Location: ../Views/login.php?logout=success');
        exit();
    }
}

// Basic router to handle actions
if (isset($_GET['action'])) {
    $controller = new UserController();
    $action = $_GET['action'];

    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        die("Action not found.");
    }
}