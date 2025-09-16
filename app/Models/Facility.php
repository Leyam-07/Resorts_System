<?php

class Facility {
    public $facilityId;
    public $resortId;
    public $name;
    public $capacity;
    public $rate;
   public $shortDescription;
   public $fullDescription;
   public $mainPhotoURL;
   public $photos = [];

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
            "INSERT INTO Facilities (ResortID, Name, Capacity, Rate, ShortDescription, FullDescription, MainPhotoURL)
             VALUES (:resortId, :name, :capacity, :rate, :shortDescription, :fullDescription, :mainPhotoURL)"
        );
        $stmt->bindValue(':resortId', $facility->resortId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $facility->name, PDO::PARAM_STR);
        $stmt->bindValue(':capacity', $facility->capacity, PDO::PARAM_INT);
        $stmt->bindValue(':rate', $facility->rate, PDO::PARAM_STR);
       $stmt->bindValue(':shortDescription', $facility->shortDescription, PDO::PARAM_STR);
       $stmt->bindValue(':fullDescription', $facility->fullDescription, PDO::PARAM_STR);
       $stmt->bindValue(':mainPhotoURL', $facility->mainPhotoURL, PDO::PARAM_STR);
        
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
           $facility->shortDescription = $data['ShortDescription'];
           $facility->fullDescription = $data['FullDescription'];
           $facility->mainPhotoURL = $data['MainPhotoURL'];
           $facility->photos = self::getPhotos($facilityId);
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
           $facility->shortDescription = $data['ShortDescription'];
           $facility->fullDescription = $data['FullDescription'];
           $facility->mainPhotoURL = $data['MainPhotoURL'];
            $facilities[] = $facility;
        }
        return $facilities;
    }

    public static function findAllWithResort() {
        $db = self::getDB();
        $stmt = $db->query(
            "SELECT f.*, r.Name as ResortName
             FROM Facilities f
             JOIN Resorts r ON f.ResortID = r.ResortID
             ORDER BY f.FacilityID ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function findByResortId($resortId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM Facilities WHERE ResortID = :resortId");
        $stmt->bindValue(':resortId', $resortId, PDO::PARAM_INT);
        $stmt->execute();
        $facilities = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $facility = new Facility();
            $facility->facilityId = $data['FacilityID'];
            $facility->resortId = $data['ResortID'];
            $facility->name = $data['Name'];
            $facility->capacity = $data['Capacity'];
            $facility->rate = $data['Rate'];
           $facility->shortDescription = $data['ShortDescription'];
           $facility->fullDescription = $data['FullDescription'];
           $facility->mainPhotoURL = $data['MainPhotoURL'];
            $facilities[] = $facility;
        }
        return $facilities;
    }

    public static function update(Facility $facility) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "UPDATE Facilities
             SET ResortID = :resortId, Name = :name, Capacity = :capacity, Rate = :rate,
                 ShortDescription = :shortDescription, FullDescription = :fullDescription, MainPhotoURL = :mainPhotoURL
             WHERE FacilityID = :facilityId"
        );
        $stmt->bindValue(':resortId', $facility->resortId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $facility->name, PDO::PARAM_STR);
        $stmt->bindValue(':capacity', $facility->capacity, PDO::PARAM_INT);
        $stmt->bindValue(':rate', $facility->rate, PDO::PARAM_STR);
       $stmt->bindValue(':shortDescription', $facility->shortDescription, PDO::PARAM_STR);
       $stmt->bindValue(':fullDescription', $facility->fullDescription, PDO::PARAM_STR);
       $stmt->bindValue(':mainPhotoURL', $facility->mainPhotoURL, PDO::PARAM_STR);
        $stmt->bindValue(':facilityId', $facility->facilityId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public static function delete($facilityId) {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM Facilities WHERE FacilityID = :facilityId");
        $stmt->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function getPhotos($facilityId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM FacilityPhotos WHERE FacilityID = :facilityId ORDER BY CreatedAt DESC");
        $stmt->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addPhoto($facilityId, $photoURL) {
        $db = self::getDB();
        $stmt = $db->prepare("INSERT INTO FacilityPhotos (FacilityID, PhotoURL) VALUES (:facilityId, :photoURL)");
        $stmt->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
        $stmt->bindValue(':photoURL', $photoURL, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public static function deletePhoto($photoId) {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM FacilityPhotos WHERE PhotoID = :photoId");
        $stmt->bindValue(':photoId', $photoId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function findPhotoById($photoId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM FacilityPhotos WHERE PhotoID = :photoId");
        $stmt->bindValue(':photoId', $photoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}