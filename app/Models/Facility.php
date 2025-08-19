<?php

class Facility {
    public $facilityId;
    public $resortId;
    public $name;
    public $capacity;
    public $rate;

    private static $db;

    public function __construct() {
        // The constructor can be used to set default values if needed.
    }

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

    public static function create(Facility $facility) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO Facilities (ResortID, Name, Capacity, Rate)
             VALUES (:resortId, :name, :capacity, :rate)"
        );
        $stmt->bindValue(':resortId', $facility->resortId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $facility->name, PDO::PARAM_STR);
        $stmt->bindValue(':capacity', $facility->capacity, PDO::PARAM_INT);
        $stmt->bindValue(':rate', $facility->rate, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return false;
    }

    public static function findById($facilityId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM Facilities WHERE FacilityID = :facilityId");
        $stmt->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $facility = new Facility();
            $facility->facilityId = $data['FacilityID'];
            $facility->resortId = $data['ResortID'];
            $facility->name = $data['Name'];
            $facility->capacity = $data['Capacity'];
            $facility->rate = $data['Rate'];
            return $facility;
        }
        return null;
    }

    public static function findAll() {
        $db = self::getDB();
        $stmt = $db->query("SELECT * FROM Facilities ORDER BY FacilityID ASC");
        $facilities = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $facility = new Facility();
            $facility->facilityId = $data['FacilityID'];
            $facility->resortId = $data['ResortID'];
            $facility->name = $data['Name'];
            $facility->capacity = $data['Capacity'];
            $facility->rate = $data['Rate'];
            $facilities[] = $facility;
        }
        return $facilities;
    }

    public static function update(Facility $facility) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "UPDATE Facilities
             SET ResortID = :resortId, Name = :name, Capacity = :capacity, Rate = :rate
             WHERE FacilityID = :facilityId"
        );
        $stmt->bindValue(':resortId', $facility->resortId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $facility->name, PDO::PARAM_STR);
        $stmt->bindValue(':capacity', $facility->capacity, PDO::PARAM_INT);
        $stmt->bindValue(':rate', $facility->rate, PDO::PARAM_STR);
        $stmt->bindValue(':facilityId', $facility->facilityId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public static function delete($facilityId) {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM Facilities WHERE FacilityID = :facilityId");
        $stmt->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}