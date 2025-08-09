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
        // Check for existing user
        if ($this->findByUsername($username)) {
            return 'username_exists';
        }
        if ($this->findByEmail($email)) {
            return 'email_exists';
        }

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
        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            // Return a generic error to avoid leaking implementation details
            return 'registration_failed';
        }

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

    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE Email = :email LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll() {
        $stmt = $this->db->prepare("SELECT UserID, Username, Email, Role, FirstName, LastName, PhoneNumber, CreatedAt FROM " . $this->table . " ORDER BY CreatedAt DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE UserID = :id LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $username, $email, $firstName, $lastName, $phoneNumber) {
        // Check if the new username or email already exists for another user
        $query = "SELECT UserID FROM " . $this->table . " WHERE (Username = :username OR Email = :email) AND UserID != :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        if ($stmt->fetch()) {
            return 'username_or_email_exists';
        }

        $query = "UPDATE " . $this->table . " SET
                    Username = :username,
                    Email = :email,
                    FirstName = :firstName,
                    LastName = :lastName,
                    PhoneNumber = :phoneNumber
                  WHERE UserID = :id";
        
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':firstName', $firstName);
        $stmt->bindParam(':lastName', $lastName);
        $stmt->bindParam(':phoneNumber', $phoneNumber);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE UserID = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function updatePassword($id, $newPassword) {
        $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

        $query = "UPDATE " . $this->table . " SET Password = :password WHERE UserID = :id";
        
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', $hashed_password);

        return $stmt->execute();
    }
}