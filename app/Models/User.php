<?php

class User {
    private $db;
    private $table = 'Users';

    public function __construct($db_connection) {
        $this->db = $db_connection;
    }

    /**
     * Creates a new user in the database.
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $role
     * @param string $firstName
     * @param string $lastName
     * @param string $phoneNumber
     * @return bool
     */
    public function create($username, $password, $email, $role = 'Customer', $firstName = null, $lastName = null, $phoneNumber = null) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO " . $this->table . " (Username, Password, Email, Role, FirstName, LastName, PhoneNumber)
                  VALUES (:username, :password, :email, :role, :firstName, :lastName, :phoneNumber)";

        $stmt = $this->db->prepare($query);

        // Bind parameters
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':firstName', $firstName);
        $stmt->bindParam(':lastName', $lastName);
        $stmt->bindParam(':phoneNumber', $phoneNumber);

        // Execute the query
        if ($stmt->execute()) {
            return true;
        }

        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    /**
     * Finds a user by their username.
     *
     * @param string $username
     * @return mixed Returns the user data as an associative array or false if not found.
     */
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table . " WHERE Username = :username LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll() {
        $stmt = $this->db->prepare("SELECT UserID, Username, Email, Role, FirstName, LastName, PhoneNumber, CreatedAt FROM " . $this->table . " ORDER BY CreatedAt DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}