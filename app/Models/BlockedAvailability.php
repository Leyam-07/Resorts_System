<?php

class BlockedAvailability {
    public $blockedAvailabilityId;
    public $facilityId;
    public $blockDate;
    public $startTime;
    public $endTime;
    public $reason;

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

    public static function create(BlockedAvailability $blocked) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO BlockedAvailabilities (FacilityID, BlockDate, StartTime, EndTime, Reason)
             VALUES (:facilityId, :blockDate, :startTime, :endTime, :reason)"
        );
        $stmt->bindValue(':facilityId', $blocked->facilityId, PDO::PARAM_INT);
        $stmt->bindValue(':blockDate', $blocked->blockDate, PDO::PARAM_STR);
        $stmt->bindValue(':startTime', $blocked->startTime, PDO::PARAM_STR);
        $stmt->bindValue(':endTime', $blocked->endTime, PDO::PARAM_STR);
        $stmt->bindValue(':reason', $blocked->reason, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public static function findByFacilityId($facilityId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM BlockedAvailabilities WHERE FacilityID = :facilityId ORDER BY BlockDate, StartTime");
        $stmt->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
        $stmt->execute();
        $blockedTimes = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $blocked = new BlockedAvailability();
            $blocked->blockedAvailabilityId = $data['BlockedAvailabilityID'];
            $blocked->facilityId = $data['FacilityID'];
            $blocked->blockDate = $data['BlockDate'];
            $blocked->startTime = $data['StartTime'];
            $blocked->endTime = $data['EndTime'];
            $blocked->reason = $data['Reason'];
            $blockedTimes[] = $blocked;
        }
        return $blockedTimes;
    }

    public static function delete($blockedAvailabilityId) {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM BlockedAvailabilities WHERE BlockedAvailabilityID = :id");
        $stmt->bindValue(':id', $blockedAvailabilityId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}