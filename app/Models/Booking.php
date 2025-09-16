<?php

class Booking {
    public $bookingId;
    public $customerId;
    public $facilityId;
    public $bookingDate;
    public $timeSlotType;
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
            "INSERT INTO Bookings (CustomerID, FacilityID, BookingDate, TimeSlotType, NumberOfGuests, Status)
             VALUES (:customerId, :facilityId, :bookingDate, :timeSlotType, :numberOfGuests, :status)"
        );
        $stmt->bindValue(':customerId', $booking->customerId, PDO::PARAM_INT);
        $stmt->bindValue(':facilityId', $booking->facilityId, PDO::PARAM_INT);
        $stmt->bindValue(':bookingDate', $booking->bookingDate, PDO::PARAM_STR);
        $stmt->bindValue(':timeSlotType', $booking->timeSlotType, PDO::PARAM_STR);
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
            $booking->timeSlotType = $data['TimeSlotType'];
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
             ORDER BY b.BookingDate DESC"
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
             ORDER BY b.BookingDate ASC"
        );
        $stmt->bindValue(':today', $today, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function findUpcomingBookings() {
        $db = self::getDB();
        $today = date('Y-m-d');
        $stmt = $db->prepare(
            "SELECT b.*, f.Name as FacilityName, u.Username as CustomerName
             FROM Bookings b
             JOIN Facilities f ON b.FacilityID = f.FacilityID
             JOIN Users u ON b.CustomerID = u.UserID
             WHERE b.BookingDate > :today AND b.Status IN ('Pending', 'Confirmed')
             ORDER BY b.BookingDate ASC"
        );
        $stmt->bindValue(':today', $today, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public static function getMonthlyIncome($year, $month, $resortId = null) {
        $db = self::getDB();
        
        $sql = "SELECT SUM(p.Amount) as TotalIncome
                FROM Payments p
                JOIN Bookings b ON p.BookingID = b.BookingID";
        
        if ($resortId) {
            $sql .= " JOIN Facilities f ON b.FacilityID = f.FacilityID";
        }
        
        $sql .= " WHERE YEAR(b.BookingDate) = :year AND MONTH(b.BookingDate) = :month
                  AND p.Status IN ('Paid', 'Partial')";

        if ($resortId) {
            $sql .= " AND f.ResortID = :resortId";
        }

        $stmt = $db->prepare($sql);
        
        $stmt->bindValue(':year', $year, PDO::PARAM_INT);
        $stmt->bindValue(':month', $month, PDO::PARAM_INT);
        if ($resortId) {
            $stmt->bindValue(':resortId', $resortId, PDO::PARAM_INT);
        }
        
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
             ORDER BY b.BookingDate DESC
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
                 TimeSlotType = :timeSlotType, NumberOfGuests = :numberOfGuests, Status = :status
             WHERE BookingID = :bookingId"
        );
        $stmt->bindValue(':customerId', $booking->customerId, PDO::PARAM_INT);
        $stmt->bindValue(':facilityId', $booking->facilityId, PDO::PARAM_INT);
        $stmt->bindValue(':bookingDate', $booking->bookingDate, PDO::PARAM_STR);
        $stmt->bindValue(':timeSlotType', $booking->timeSlotType, PDO::PARAM_STR);
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
    public static function getTimeSlotDisplay($timeSlotType) {
        switch ($timeSlotType) {
            case '12_hours':
                return '7:00 AM - 5:00 PM (12 hrs)';
            case 'overnight':
                return '7:00 PM - 5:00 AM (Overnight)';
            case '24_hours':
                return '7:00 AM - 5:00 AM (24 hrs)';
            default:
                return 'N/A';
        }
    }

    public static function isTimeSlotAvailable($facilityId, $bookingDate, $timeSlotType, $excludeBookingId = null) {
        $db = self::getDB();

        // Define which time slots conflict with each other
        $conflicts = [
            '12_hours'  => ['12_hours', '24_hours'],
            'overnight' => ['overnight', '24_hours'],
            '24_hours'  => ['12_hours', 'overnight', '24_hours']
        ];

        // If the requested time slot type is invalid, it's not available.
        if (!isset($conflicts[$timeSlotType])) {
            return false;
        }

        $conflictingSlots = $conflicts[$timeSlotType];
        $placeholders = rtrim(str_repeat('?,', count($conflictingSlots)), ',');

        $sql = "SELECT COUNT(*) FROM Bookings
                WHERE FacilityID = ?
                AND BookingDate = ?
                AND Status IN ('Pending', 'Confirmed')
                AND TimeSlotType IN ($placeholders)";
        
        $params = [$facilityId, $bookingDate, ...$conflictingSlots];

        if ($excludeBookingId) {
            $sql .= " AND BookingID != ?";
            $params[] = $excludeBookingId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() == 0;
    }
}