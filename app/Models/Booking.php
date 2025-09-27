<?php

require_once __DIR__ . '/BlockedFacilityAvailability.php';
require_once __DIR__ . '/ResortTimeframePricing.php';
require_once __DIR__ . '/BookingFacilities.php';

class Booking {
    public $bookingId;
    public $customerId;
    public $resortId;
    public $facilityId; // This will be deprecated in favor of multiple facilities
    public $bookingDate;
    public $timeSlotType;
    public $numberOfGuests;
    public $status; // e.g., 'Pending', 'Confirmed', 'Cancelled', 'Completed'
    public $totalAmount;
    public $paymentProofURL;
    public $paymentReference;
    public $remainingBalance;
    public $createdAt;

    private static $db;

    public function __construct() {
        // Constructor can be used for setting default values.
    }

    private static function getDB() {
        if (!self::$db) {
            require_once __DIR__ . '/../Helpers/Database.php';
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    public static function create(Booking $booking) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO Bookings (CustomerID, ResortID, FacilityID, BookingDate, TimeSlotType, NumberOfGuests, Status, TotalAmount, PaymentProofURL, PaymentReference, RemainingBalance)
             VALUES (:customerId, :resortId, :facilityId, :bookingDate, :timeSlotType, :numberOfGuests, :status, :totalAmount, :paymentProofURL, :paymentReference, :remainingBalance)"
        );
        $stmt->bindValue(':customerId', $booking->customerId, PDO::PARAM_INT);
        $stmt->bindValue(':resortId', $booking->resortId, PDO::PARAM_INT);
        $stmt->bindValue(':facilityId', $booking->facilityId, PDO::PARAM_INT);
        $stmt->bindValue(':bookingDate', $booking->bookingDate, PDO::PARAM_STR);
        $stmt->bindValue(':timeSlotType', $booking->timeSlotType, PDO::PARAM_STR);
        $stmt->bindValue(':numberOfGuests', $booking->numberOfGuests, PDO::PARAM_INT);
        $stmt->bindValue(':status', $booking->status, PDO::PARAM_STR);
        $stmt->bindValue(':totalAmount', $booking->totalAmount, PDO::PARAM_STR);
        $stmt->bindValue(':paymentProofURL', $booking->paymentProofURL, PDO::PARAM_STR);
        $stmt->bindValue(':paymentReference', $booking->paymentReference, PDO::PARAM_STR);
        $stmt->bindValue(':remainingBalance', $booking->remainingBalance, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return false;
    }

    /**
     * Create a new resort-centric booking with multiple facilities
     */
    public static function createResortBooking($customerId, $resortId, $bookingDate, $timeSlotType, $numberOfGuests, $facilityIds = []) {
        $db = self::getDB();
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Calculate total price
            $basePrice = ResortTimeframePricing::calculatePrice($resortId, $timeSlotType, $bookingDate);
            $facilityPrice = 0;
            
            if (!empty($facilityIds)) {
                require_once __DIR__ . '/Facility.php';
                foreach ($facilityIds as $facilityId) {
                    $facility = Facility::findById($facilityId);
                    if ($facility) {
                        $facilityPrice += $facility->rate;
                    }
                }
            }
            
            $totalAmount = $basePrice + $facilityPrice;
            
            // Create booking record
            $booking = new Booking();
            $booking->customerId = $customerId;
            $booking->resortId = $resortId;
            $booking->facilityId = null; // Will be deprecated
            $booking->bookingDate = $bookingDate;
            $booking->timeSlotType = $timeSlotType;
            $booking->numberOfGuests = $numberOfGuests;
            $booking->status = 'Pending';
            $booking->totalAmount = $totalAmount;
            $booking->paymentProofURL = null;
            $booking->paymentReference = null;
            $booking->remainingBalance = $totalAmount;
            
            $bookingId = self::create($booking);
            
            if (!$bookingId) {
                throw new Exception("Failed to create booking");
            }
            
            // Add facilities to booking
            if (!empty($facilityIds)) {
                if (!BookingFacilities::addFacilitiesToBooking($bookingId, $facilityIds)) {
                    throw new Exception("Failed to add facilities to booking");
                }
            }
            
            // Commit transaction
            $db->commit();
            return $bookingId;
            
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            return false;
        }
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
            $booking->resortId = $data['ResortID'];
            $booking->facilityId = $data['FacilityID'];
            $booking->bookingDate = $data['BookingDate'];
            $booking->timeSlotType = $data['TimeSlotType'];
            $booking->numberOfGuests = $data['NumberOfGuests'];
            $booking->status = $data['Status'];
            $booking->totalAmount = $data['TotalAmount'];
            $booking->paymentProofURL = $data['PaymentProofURL'];
            $booking->paymentReference = $data['PaymentReference'];
            $booking->remainingBalance = $data['RemainingBalance'];
            $booking->createdAt = $data['CreatedAt'];
            return $booking;
        }
        return null;
    }

    public static function findByCustomerId($customerId) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT b.*, r.Name as ResortName,
                    GROUP_CONCAT(f.Name SEPARATOR ', ') as FacilityNames
             FROM Bookings b
             LEFT JOIN Resorts r ON b.ResortID = r.ResortID
             LEFT JOIN BookingFacilities bf ON b.BookingID = bf.BookingID
             LEFT JOIN Facilities f ON bf.FacilityID = f.FacilityID
             WHERE b.CustomerID = :customerId
             GROUP BY b.BookingID
             ORDER BY b.BookingDate DESC, b.CreatedAt DESC"
        );
        $stmt->bindValue(':customerId', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function findTodaysBookings($resortId = null) {
        $db = self::getDB();
        $today = date('Y-m-d');
        
        $sql = "SELECT b.*, r.Name as ResortName, u.Username as CustomerName,
                       GROUP_CONCAT(f.Name SEPARATOR ', ') as FacilityNames
                FROM Bookings b
                LEFT JOIN Resorts r ON b.ResortID = r.ResortID
                LEFT JOIN Users u ON b.CustomerID = u.UserID
                LEFT JOIN BookingFacilities bf ON b.BookingID = bf.BookingID
                LEFT JOIN Facilities f ON bf.FacilityID = f.FacilityID
                WHERE b.BookingDate = :today";

        if ($resortId) {
            $sql .= " AND b.ResortID = :resortId";
        }

        $sql .= " GROUP BY b.BookingID ORDER BY b.BookingDate ASC, b.CreatedAt ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':today', $today, PDO::PARAM_STR);
        if ($resortId) {
            $stmt->bindValue(':resortId', $resortId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function findUpcomingBookings($resortId = null) {
        $db = self::getDB();
        $today = date('Y-m-d');

        $sql = "SELECT b.*, r.Name as ResortName, u.Username as CustomerName,
                       GROUP_CONCAT(f.Name SEPARATOR ', ') as FacilityNames
                FROM Bookings b
                LEFT JOIN Resorts r ON b.ResortID = r.ResortID
                LEFT JOIN Users u ON b.CustomerID = u.UserID
                LEFT JOIN BookingFacilities bf ON b.BookingID = bf.BookingID
                LEFT JOIN Facilities f ON bf.FacilityID = f.FacilityID
                WHERE b.BookingDate > :today AND b.Status IN ('Pending', 'Confirmed')";

        if ($resortId) {
            $sql .= " AND b.ResortID = :resortId";
        }

        $sql .= " GROUP BY b.BookingID ORDER BY b.BookingDate ASC, b.CreatedAt ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':today', $today, PDO::PARAM_STR);
        if ($resortId) {
            $stmt->bindValue(':resortId', $resortId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public static function getMonthlyIncome($year, $month, $resortId = null) {
        $db = self::getDB();
        
        $sql = "SELECT SUM(p.Amount) as TotalIncome
                FROM Payments p
                JOIN Bookings b ON p.BookingID = b.BookingID
                WHERE YEAR(b.BookingDate) = :year AND MONTH(b.BookingDate) = :month
                AND p.Status IN ('Paid', 'Partial')";

        if ($resortId) {
            $sql .= " AND b.ResortID = :resortId";
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
            "SELECT b.*, r.Name as ResortName, u.Username as CustomerName,
                    GROUP_CONCAT(f.Name SEPARATOR ', ') as FacilityNames
             FROM Bookings b
             LEFT JOIN Resorts r ON b.ResortID = r.ResortID
             LEFT JOIN Users u ON b.CustomerID = u.UserID
             LEFT JOIN BookingFacilities bf ON b.BookingID = bf.BookingID
             LEFT JOIN Facilities f ON bf.FacilityID = f.FacilityID
             WHERE b.BookingDate < CURDATE()
             GROUP BY b.BookingID
             ORDER BY b.BookingDate DESC, b.CreatedAt DESC
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
             SET CustomerID = :customerId, ResortID = :resortId, FacilityID = :facilityId, BookingDate = :bookingDate,
                 TimeSlotType = :timeSlotType, NumberOfGuests = :numberOfGuests, Status = :status,
                 TotalAmount = :totalAmount, PaymentProofURL = :paymentProofURL, PaymentReference = :paymentReference,
                 RemainingBalance = :remainingBalance
             WHERE BookingID = :bookingId"
        );
        $stmt->bindValue(':customerId', $booking->customerId, PDO::PARAM_INT);
        $stmt->bindValue(':resortId', $booking->resortId, PDO::PARAM_INT);
        $stmt->bindValue(':facilityId', $booking->facilityId, PDO::PARAM_INT);
        $stmt->bindValue(':bookingDate', $booking->bookingDate, PDO::PARAM_STR);
        $stmt->bindValue(':timeSlotType', $booking->timeSlotType, PDO::PARAM_STR);
        $stmt->bindValue(':numberOfGuests', $booking->numberOfGuests, PDO::PARAM_INT);
        $stmt->bindValue(':status', $booking->status, PDO::PARAM_STR);
        $stmt->bindValue(':totalAmount', $booking->totalAmount, PDO::PARAM_STR);
        $stmt->bindValue(':paymentProofURL', $booking->paymentProofURL, PDO::PARAM_STR);
        $stmt->bindValue(':paymentReference', $booking->paymentReference, PDO::PARAM_STR);
        $stmt->bindValue(':remainingBalance', $booking->remainingBalance, PDO::PARAM_STR);
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
        
        $bookingConflict = $stmt->fetchColumn() > 0;
        if ($bookingConflict) {
            return false; // Found a conflicting booking
        }

        // Now, check for resort-level blocks for the entire day
        $facilityStmt = $db->prepare("SELECT ResortID FROM Facilities WHERE FacilityID = ?");
        $facilityStmt->execute([$facilityId]);
        $resortId = $facilityStmt->fetchColumn();

        if ($resortId) {
            $blockSql = "SELECT COUNT(*) FROM BlockedResortAvailability
                         WHERE ResortID = ?
                         AND BlockDate = ?";
            $blockStmt = $db->prepare($blockSql);
            $blockStmt->execute([$resortId, $bookingDate]);
            $isBlocked = $blockStmt->fetchColumn() > 0;
            if ($isBlocked) {
                return false; // The entire resort is blocked for this day
            }
        }

        // Finally, check for facility-level blocks for the entire day
        $facilityBlockSql = "SELECT COUNT(*) FROM BlockedFacilityAvailability
                             WHERE FacilityID = ?
                             AND BlockDate = ?";
        $facilityBlockStmt = $db->prepare($facilityBlockSql);
        $facilityBlockStmt->execute([$facilityId, $bookingDate]);
        $isFacilityBlocked = $facilityBlockStmt->fetchColumn() > 0;
        if ($isFacilityBlocked) {
            return false; // The specific facility is blocked for this day
        }

        return true; // No conflicts found
    }

    /**
     * Check if a resort + timeframe combination is available (for resort-centric booking)
     */
    public static function isResortTimeframeAvailable($resortId, $bookingDate, $timeSlotType, $facilityIds = [], $excludeBookingId = null) {
        // Check resort-level blocks
        $db = self::getDB();
        $blockSql = "SELECT COUNT(*) FROM BlockedResortAvailability WHERE ResortID = ? AND BlockDate = ?";
        $blockStmt = $db->prepare($blockSql);
        $blockStmt->execute([$resortId, $bookingDate]);
        if ($blockStmt->fetchColumn() > 0) {
            return false; // Resort is blocked for this date
        }

        // If facilities are selected, check their availability
        if (!empty($facilityIds)) {
            foreach ($facilityIds as $facilityId) {
                if (!self::isTimeSlotAvailable($facilityId, $bookingDate, $timeSlotType, $excludeBookingId)) {
                    return false;
                }
            }
        }

        return true; // Available
    }

    /**
     * Update payment information for a booking
     */
    public static function updatePaymentInfo($bookingId, $paymentProofURL, $paymentReference, $amountPaid = null) {
        $db = self::getDB();
        
        $booking = self::findById($bookingId);
        if (!$booking) {
            return false;
        }

        // Calculate remaining balance
        $remainingBalance = $booking->remainingBalance;
        if ($amountPaid !== null) {
            $remainingBalance = max(0, $booking->remainingBalance - $amountPaid);
        }

        // Update status based on payment
        $newStatus = $booking->status;
        if ($remainingBalance <= 0) {
            $newStatus = 'Confirmed'; // Fully paid
        } elseif ($remainingBalance < $booking->totalAmount) {
            $newStatus = 'Pending'; // Partially paid, but still pending confirmation
        }

        $stmt = $db->prepare(
            "UPDATE Bookings
             SET PaymentProofURL = :paymentProofURL, PaymentReference = :paymentReference,
                 RemainingBalance = :remainingBalance, Status = :status
             WHERE BookingID = :bookingId"
        );
        $stmt->bindValue(':paymentProofURL', $paymentProofURL, PDO::PARAM_STR);
        $stmt->bindValue(':paymentReference', $paymentReference, PDO::PARAM_STR);
        $stmt->bindValue(':remainingBalance', $remainingBalance, PDO::PARAM_STR);
        $stmt->bindValue(':status', $newStatus, PDO::PARAM_STR);
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Get bookings with facilities and resort information
     */
    public static function getBookingsWithDetails($resortId = null, $status = null, $limit = null) {
        $db = self::getDB();
        
        $sql = "SELECT b.*, r.Name as ResortName, u.Username as CustomerName,
                       GROUP_CONCAT(f.Name SEPARATOR ', ') as FacilityNames
                FROM Bookings b
                LEFT JOIN Resorts r ON b.ResortID = r.ResortID
                LEFT JOIN Users u ON b.CustomerID = u.UserID
                LEFT JOIN BookingFacilities bf ON b.BookingID = bf.BookingID
                LEFT JOIN Facilities f ON bf.FacilityID = f.FacilityID
                WHERE 1=1";
        
        $params = [];
        
        if ($resortId) {
            $sql .= " AND b.ResortID = ?";
            $params[] = $resortId;
        }
        
        if ($status) {
            $sql .= " AND b.Status = ?";
            $params[] = $status;
        }
        
        $sql .= " GROUP BY b.BookingID ORDER BY b.BookingDate DESC, b.CreatedAt DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Calculate total booking value with facilities
     */
    public static function calculateBookingTotal($resortId, $timeSlotType, $bookingDate, $facilityIds = []) {
        // Get base price from resort timeframe pricing
        $basePrice = ResortTimeframePricing::calculatePrice($resortId, $timeSlotType, $bookingDate);
        
        // Add facility costs
        $facilityTotal = 0;
        if (!empty($facilityIds)) {
            require_once __DIR__ . '/Facility.php';
            foreach ($facilityIds as $facilityId) {
                $facility = Facility::findById($facilityId);
                if ($facility) {
                    $facilityTotal += $facility->rate;
                }
            }
        }
        
        return $basePrice + $facilityTotal;
    }

    /**
     * PHASE 5: Get bookings with payment details for unified management
     */
    public static function getBookingsWithPaymentDetails($resortId = null, $status = null) {
        $db = self::getDB();
        
        $sql = "SELECT
                    b.*,
                    u.Username as CustomerName,
                    u.Email as CustomerEmail,
                    r.Name as ResortName,
                    GROUP_CONCAT(f.Name SEPARATOR ', ') as FacilityNames,
                    COALESCE(p.PaymentStatus, 'Unpaid') as PaymentStatus,
                    COALESCE(p.TotalPaid, 0) as TotalPaid,
                    -- Phase 6 Data
                    COALESCE(at.AuditTrailCount, 0) as AuditTrailCount,
                    COALESCE(ps.PaymentScheduleSummary, 'Not Scheduled') as PaymentScheduleSummary
                FROM Bookings b
                LEFT JOIN Users u ON b.CustomerID = u.UserID
                LEFT JOIN Resorts r ON b.ResortID = r.ResortID
                LEFT JOIN BookingFacilities bf ON b.BookingID = bf.BookingID
                LEFT JOIN Facilities f ON bf.FacilityID = f.FacilityID
                LEFT JOIN (
                    SELECT
                        p2.BookingID,
                        SUM(p2.Amount) as TotalPaid,
                        CASE
                            WHEN SUM(CASE WHEN p2.Status = 'Verified' THEN p2.Amount ELSE 0 END) >= MAX(b2.TotalAmount) THEN 'Paid'
                            WHEN SUM(CASE WHEN p2.Status = 'Verified' THEN p2.Amount ELSE 0 END) > 0 THEN 'Partial'
                            ELSE 'Unpaid'
                        END as PaymentStatus
                    FROM Payments p2
                    LEFT JOIN Bookings b2 ON p2.BookingID = b2.BookingID
                    GROUP BY p2.BookingID
                ) p ON b.BookingID = p.BookingID
                -- Phase 6: Join for Audit Trail Count
                LEFT JOIN (
                    SELECT BookingID, COUNT(*) as AuditTrailCount
                    FROM BookingAuditTrail
                    GROUP BY BookingID
                ) at ON b.BookingID = at.BookingID
                -- Phase 6: Join for Payment Schedule Summary
                LEFT JOIN (
                    SELECT
                        BookingID,
                        CONCAT(
                            COUNT(*), ' installments (',
                            SUM(CASE WHEN Status = 'Paid' THEN 1 ELSE 0 END), ' Paid, ',
                            SUM(CASE WHEN Status IN ('Pending', 'Overdue') THEN 1 ELSE 0 END), ' Due)'
                        ) as PaymentScheduleSummary
                    FROM PaymentSchedules
                    GROUP BY BookingID
                ) ps ON b.BookingID = ps.BookingID
                WHERE 1=1";

        $params = [];
        
        if ($resortId) {
            $sql .= " AND b.ResortID = :resortId";
            $params[':resortId'] = $resortId;
        }
        
        if ($status && $status !== 'all') {
            $sql .= " AND b.Status = :status";
            $params[':status'] = $status;
        }
        
        $sql .= " GROUP BY b.BookingID ORDER BY b.BookingDate DESC, b.CreatedAt DESC";
        
        $stmt = $db->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Update remaining balance for a booking
     */
    public static function updateRemainingBalance($bookingId, $remainingBalance) {
        $db = self::getDB();
        $stmt = $db->prepare("UPDATE Bookings SET RemainingBalance = :remainingBalance WHERE BookingID = :bookingId");
        $stmt->bindValue(':remainingBalance', $remainingBalance, PDO::PARAM_STR);
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}