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

    public static function findByCustomerId($customerId) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT b.*, f.Name as FacilityName
             FROM Bookings b
             JOIN Facilities f ON b.FacilityID = f.FacilityID
             WHERE b.CustomerID = :customerId
             ORDER BY b.BookingDate DESC, b.StartTime DESC"
        );
        $stmt->bindValue(':customerId', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function findTodaysBookings() {
        $db = self::getDB();
        $today = date('Y-m-d');
        $stmt = $db->prepare(
            "SELECT b.*, f.Name as FacilityName, u.Username as CustomerName
             FROM Bookings b
             JOIN Facilities f ON b.FacilityID = f.FacilityID
             JOIN Users u ON b.CustomerID = u.UserID
             WHERE b.BookingDate = :today
             ORDER BY b.StartTime ASC"
        );
        $stmt->bindValue(':today', $today, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public static function getMonthlyIncome($year, $month) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT SUM(p.Amount) as TotalIncome
             FROM Payments p
             JOIN Bookings b ON p.BookingID = b.BookingID
             WHERE YEAR(b.BookingDate) = :year AND MONTH(b.BookingDate) = :month
             AND p.Status IN ('Paid', 'Partial')"
        );
        $stmt->bindValue(':year', $year, PDO::PARAM_INT);
        $stmt->bindValue(':month', $month, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['TotalIncome'] ?? 0;
    }

    public static function getBookingHistory($limit = 10) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT b.*, f.Name as FacilityName, u.Username as CustomerName
             FROM Bookings b
             JOIN Facilities f ON b.FacilityID = f.FacilityID
             JOIN Users u ON b.CustomerID = u.UserID
             WHERE b.BookingDate < CURDATE()
             ORDER BY b.BookingDate DESC, b.StartTime DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
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

    public static function updateStatus($bookingId, $status) {
        $db = self::getDB();
        $stmt = $db->prepare("UPDATE Bookings SET Status = :status WHERE BookingID = :bookingId");
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
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

        // 1. Check for overlapping bookings
        $sqlBookings = "SELECT COUNT(*) FROM Bookings
                        WHERE FacilityID = :facilityId
                        AND BookingDate = :bookingDate
                        AND Status IN ('Pending', 'Confirmed')
                        AND :startTime < EndTime
                        AND :endTime > StartTime";
        if ($excludeBookingId) {
            $sqlBookings .= " AND BookingID != :excludeBookingId";
        }
        $stmtBookings = $db->prepare($sqlBookings);
        $stmtBookings->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
        $stmtBookings->bindValue(':bookingDate', $bookingDate, PDO::PARAM_STR);
        $stmtBookings->bindValue(':startTime', $startTime, PDO::PARAM_STR);
        $stmtBookings->bindValue(':endTime', $endTime, PDO::PARAM_STR);
        if ($excludeBookingId) {
            $stmtBookings->bindValue(':excludeBookingId', $excludeBookingId, PDO::PARAM_INT);
        }
        $stmtBookings->execute();
        if ($stmtBookings->fetchColumn() > 0) {
            return false; // Conflict with an existing booking
        }

        // 2. Check for overlapping blocked slots
        $sqlBlocked = "SELECT COUNT(*) FROM BlockedAvailabilities
                       WHERE FacilityID = :facilityId
                       AND BlockDate = :bookingDate
                       AND :startTime < EndTime
                       AND :endTime > StartTime";
        $stmtBlocked = $db->prepare($sqlBlocked);
        $stmtBlocked->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
        $stmtBlocked->bindValue(':bookingDate', $bookingDate, PDO::PARAM_STR);
        $stmtBlocked->bindValue(':startTime', $startTime, PDO::PARAM_STR);
        $stmtBlocked->bindValue(':endTime', $endTime, PDO::PARAM_STR);
        $stmtBlocked->execute();
        if ($stmtBlocked->fetchColumn() > 0) {
            return false; // Conflict with a blocked time slot
        }

        return true; // The time slot is available
    }
}