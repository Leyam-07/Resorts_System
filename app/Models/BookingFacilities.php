<?php

class BookingFacilities {
    public $bookingFacilityId;
    public $bookingId;
    public $facilityId;
    public $facilityPrice;
    public $createdAt;

    private static $db;

    private static function getDB() {
        if (!self::$db) {
            require_once __DIR__ . '/../Helpers/Database.php';
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    public static function create(BookingFacilities $bookingFacility) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO BookingFacilities (BookingID, FacilityID)
             VALUES (:bookingId, :facilityId)"
        );
        $stmt->bindValue(':bookingId', $bookingFacility->bookingId, PDO::PARAM_INT);
        $stmt->bindValue(':facilityId', $bookingFacility->facilityId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return false;
    }

    public static function findByBookingId($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT bf.*, f.Name as FacilityName, f.Rate as FacilityRate
             FROM BookingFacilities bf
             JOIN Facilities f ON bf.FacilityID = f.FacilityID
             WHERE bf.BookingID = :bookingId"
        );
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function deleteByBookingId($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM BookingFacilities WHERE BookingID = :bookingId");
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function delete($bookingFacilityId) {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM BookingFacilities WHERE BookingFacilityID = :bookingFacilityId");
        $stmt->bindValue(':bookingFacilityId', $bookingFacilityId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Add multiple facilities to a booking
     */
    public static function addFacilitiesToBooking($bookingId, $facilityIds) {
        if (empty($facilityIds)) {
            return true; // No facilities to add
        }

        $db = self::getDB();
        
        // Insert each facility
        foreach ($facilityIds as $facilityId) {
            $bookingFacility = new BookingFacilities();
            $bookingFacility->bookingId = $bookingId;
            $bookingFacility->facilityId = $facilityId;
            
            if (!self::create($bookingFacility)) {
                return false; // Failed to add facility
            }
        }

        return true;
    }

    /**
     * Calculate total facility costs for a booking
     */
    public static function calculateTotalFacilityCost($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT SUM(f.Rate) as TotalCost FROM BookingFacilities bf JOIN Facilities f ON bf.FacilityID = f.FacilityID WHERE bf.BookingID = :bookingId");
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['TotalCost'] ?? 0;
    }

    /**
     * Check if any of the selected facilities are available for the booking date/time
     */
    public static function checkFacilitiesAvailability($facilityIds, $bookingDate, $timeSlotType, $excludeBookingId = null) {
        if (empty($facilityIds)) {
            return true; // No facilities to check
        }

        require_once __DIR__ . '/Booking.php';
        
        foreach ($facilityIds as $facilityId) {
            if (!Booking::isTimeSlotAvailable($facilityId, $bookingDate, $timeSlotType, $excludeBookingId)) {
                return false; // At least one facility is not available
            }
        }
        return true; // All facilities are available
    }

    /**
     * Get facility IDs for a booking
     */
    public static function getFacilityIds($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT FacilityID FROM BookingFacilities WHERE BookingID = :bookingId");
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Update facilities for an existing booking
     */
    public static function updateBookingFacilities($bookingId, $facilityIds) {
        $db = self::getDB();
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Remove all existing facilities for this booking
            self::deleteByBookingId($bookingId);
            
            // Add new facilities
            if (!empty($facilityIds)) {
                if (!self::addFacilitiesToBooking($bookingId, $facilityIds)) {
                    throw new Exception("Failed to add facilities to booking");
                }
            }
            
            // Commit transaction
            $db->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction
            $db->rollback();
            return false;
        }
    }
    public static function getFacilitiesForBooking($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT f.FacilityID, f.Name
             FROM BookingFacilities bf
             JOIN Facilities f ON bf.FacilityID = f.FacilityID
             WHERE bf.BookingID = :bookingId"
        );
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
