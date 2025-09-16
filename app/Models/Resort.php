<?php

class Resort {
    public $resortId;
    public $name;
    public $address;
    public $contactPerson;

    private static $db;

    private static function getDB() {
        if (!self::$db) {
            require_once __DIR__ . '/../../config/database.php';
            try {
                self::$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$db;
    }

    public static function create(Resort $resort) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO Resorts (Name, Address, ContactPerson)
             VALUES (:name, :address, :contactPerson)"
        );
        $stmt->bindValue(':name', $resort->name, PDO::PARAM_STR);
        $stmt->bindValue(':address', $resort->address, PDO::PARAM_STR);
        $stmt->bindValue(':contactPerson', $resort->contactPerson, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return false;
    }

    public static function findById($id) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM Resorts WHERE ResortID = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $resort = new Resort();
            $resort->resortId = $data['ResortID'];
            $resort->name = $data['Name'];
            $resort->address = $data['Address'];
            $resort->contactPerson = $data['ContactPerson'];
            return $resort;
        }
        return null;
    }

    public static function findAll() {
        $db = self::getDB();
        $stmt = $db->query("SELECT * FROM Resorts ORDER BY Name ASC");
        $resorts = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resort = new Resort();
            $resort->resortId = $data['ResortID'];
            $resort->name = $data['Name'];
            $resort->address = $data['Address'];
            $resort->contactPerson = $data['ContactPerson'];
            $resorts[] = $resort;
        }
        return $resorts;
    }

    public static function update(Resort $resort) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "UPDATE Resorts
             SET Name = :name, Address = :address, ContactPerson = :contactPerson
             WHERE ResortID = :id"
        );
        $stmt->bindValue(':name', $resort->name, PDO::PARAM_STR);
        $stmt->bindValue(':address', $resort->address, PDO::PARAM_STR);
        $stmt->bindValue(':contactPerson', $resort->contactPerson, PDO::PARAM_STR);
        $stmt->bindValue(':id', $resort->resortId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public static function delete($id) {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM Resorts WHERE ResortID = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}