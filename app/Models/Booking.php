<?php

class Booking {
    public $bookingId;
    public $customerId;
    public $facilityId;
    public $bookingDate;
    public $startTime;
    public $endTime;
    public $numberOfGuests;
    public $status; // e.g., 'Pending', 'Confirmed', 'Cancelled', 'Completed'
    public $createdAt;

    private static $db;

    public function __construct() {
        // Constructor can be used for setting default values.
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

    public static function create(Booking $booking) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO Bookings (CustomerID, FacilityID, BookingDate, StartTime, EndTime, NumberOfGuests, Status)
             VALUES (:customerId, :facilityId, :bookingDate, :startTime, :endTime, :numberOfGuests, :status)"
        );
        $stmt->bindValue(':customerId', $booking->customerId, PDO::PARAM_INT);
        $stmt->bindValue(':facilityId', $booking->facilityId, PDO::PARAM_INT);
        $stmt->bindValue(':bookingDate', $booking->bookingDate, PDO::PARAM_STR);
        $stmt->bindValue(':startTime', $booking->startTime, PDO::PARAM_STR);
        $stmt->bindValue(':endTime', $booking->endTime, PDO::PARAM_STR);
        $stmt->bindValue(':numberOfGuests', $booking->numberOfGuests, PDO::PARAM_INT);
        $stmt->bindValue(':status', $booking->status, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return false;
    }

    public static function findById($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM Bookings WHERE BookingID = :bookingId");
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $booking = new Booking();
            $booking->bookingId = $data['BookingID'];
            $booking->customerId = $data['CustomerID'];
            $booking->facilityId = $data['FacilityID'];
            $booking->bookingDate = $data['BookingDate'];
            $booking->startTime = $data['StartTime'];
            $booking->endTime = $data['EndTime'];
            $booking->numberOfGuests = $data['NumberOfGuests'];
            $booking->status = $data['Status'];
            $booking->createdAt = $data['CreatedAt'];
            return $booking;
        }
        return null;
    }

    public static function update(Booking $booking) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "UPDATE Bookings
             SET CustomerID = :customerId, FacilityID = :facilityId, BookingDate = :bookingDate,
                 StartTime = :startTime, EndTime = :endTime, NumberOfGuests = :numberOfGuests, Status = :status
             WHERE BookingID = :bookingId"
        );
        $stmt->bindValue(':customerId', $booking->customerId, PDO::PARAM_INT);
        $stmt->bindValue(':facilityId', $booking->facilityId, PDO::PARAM_INT);
        $stmt->bindValue(':bookingDate', $booking->bookingDate, PDO::PARAM_STR);
        $stmt->bindValue(':startTime', $booking->startTime, PDO::PARAM_STR);
        $stmt->bindValue(':endTime', $booking->endTime, PDO::PARAM_STR);
        $stmt->bindValue(':numberOfGuests', $booking->numberOfGuests, PDO::PARAM_INT);
        $stmt->bindValue(':status', $booking->status, PDO::PARAM_STR);
        $stmt->bindValue(':bookingId', $booking->bookingId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public static function delete($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM Bookings WHERE BookingID = :bookingId");
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function isTimeSlotAvailable($facilityId, $bookingDate, $startTime, $endTime, $excludeBookingId = null) {
        $db = self::getDB();
        // Check for any bookings that overlap with the requested time slot
        $sql = "SELECT COUNT(*) FROM Bookings
                WHERE FacilityID = :facilityId
                AND BookingDate = :bookingDate
                AND Status IN ('Pending', 'Confirmed')
                AND :startTime < EndTime
                AND :endTime > StartTime";
        
        if ($excludeBookingId) {
            $sql .= " AND BookingID != :excludeBookingId";
        }

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
        $stmt->bindValue(':bookingDate', $bookingDate, PDO::PARAM_STR);
        $stmt->bindValue(':startTime', $startTime, PDO::PARAM_STR);
        $stmt->bindValue(':endTime', $endTime, PDO::PARAM_STR);
        
        if ($excludeBookingId) {
            $stmt->bindValue(':excludeBookingId', $excludeBookingId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchColumn() == 0;
    }
}