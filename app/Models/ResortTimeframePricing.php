<?php

class ResortTimeframePricing {
    public $pricingId;
    public $resortId;
    public $timeframeType;
    public $basePrice;
    public $weekendSurcharge;
    public $holidaySurcharge;
    public $createdAt;

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

    public static function create(ResortTimeframePricing $pricing) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO ResortTimeframePricing (ResortID, TimeframeType, BasePrice, WeekendSurcharge, HolidaySurcharge)
             VALUES (:resortId, :timeframeType, :basePrice, :weekendSurcharge, :holidaySurcharge)"
        );
        $stmt->bindValue(':resortId', $pricing->resortId, PDO::PARAM_INT);
        $stmt->bindValue(':timeframeType', $pricing->timeframeType, PDO::PARAM_STR);
        $stmt->bindValue(':basePrice', $pricing->basePrice, PDO::PARAM_STR);
        $stmt->bindValue(':weekendSurcharge', $pricing->weekendSurcharge, PDO::PARAM_STR);
        $stmt->bindValue(':holidaySurcharge', $pricing->holidaySurcharge, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return false;
    }

    public static function findByResortId($resortId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM ResortTimeframePricing WHERE ResortID = :resortId ORDER BY TimeframeType");
        $stmt->bindValue(':resortId', $resortId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function findByResortAndTimeframe($resortId, $timeframeType) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM ResortTimeframePricing WHERE ResortID = :resortId AND TimeframeType = :timeframeType");
        $stmt->bindValue(':resortId', $resortId, PDO::PARAM_INT);
        $stmt->bindValue(':timeframeType', $timeframeType, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $pricing = new ResortTimeframePricing();
            $pricing->pricingId = $data['PricingID'];
            $pricing->resortId = $data['ResortID'];
            $pricing->timeframeType = $data['TimeframeType'];
            $pricing->basePrice = $data['BasePrice'];
            $pricing->weekendSurcharge = $data['WeekendSurcharge'];
            $pricing->holidaySurcharge = $data['HolidaySurcharge'];
            $pricing->createdAt = $data['CreatedAt'];
            return $pricing;
        }
        return null;
    }

    public static function update(ResortTimeframePricing $pricing) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "UPDATE ResortTimeframePricing
             SET BasePrice = :basePrice, WeekendSurcharge = :weekendSurcharge, HolidaySurcharge = :holidaySurcharge
             WHERE PricingID = :pricingId"
        );
        $stmt->bindValue(':basePrice', $pricing->basePrice, PDO::PARAM_STR);
        $stmt->bindValue(':weekendSurcharge', $pricing->weekendSurcharge, PDO::PARAM_STR);
        $stmt->bindValue(':holidaySurcharge', $pricing->holidaySurcharge, PDO::PARAM_STR);
        $stmt->bindValue(':pricingId', $pricing->pricingId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function delete($pricingId) {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM ResortTimeframePricing WHERE PricingID = :pricingId");
        $stmt->bindValue(':pricingId', $pricingId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Calculate the total price for a specific date, accounting for weekends and holidays
     */
    public static function calculatePrice($resortId, $timeframeType, $bookingDate) {
        $pricing = self::findByResortAndTimeframe($resortId, $timeframeType);
        if (!$pricing) {
            return 0; // No pricing found
        }

        $totalPrice = $pricing->basePrice;

        // Check if it's a weekend (Saturday = 6, Sunday = 0)
        $dayOfWeek = date('w', strtotime($bookingDate));
        if ($dayOfWeek == 0 || $dayOfWeek == 6) {
            $totalPrice += $pricing->weekendSurcharge;
        }

        // TODO: Implement holiday checking logic
        // This would require a holidays table or external API
        // For now, weekends cover most premium pricing scenarios

        return $totalPrice;
    }

    /**
     * Get all timeframe types available in the system
     */
    public static function getTimeframeTypes() {
        return ['12_hours', '24_hours', 'overnight'];
    }

    /**
     * Get display name for timeframe type
     */
    public static function getTimeframeDisplay($timeframeType) {
        switch ($timeframeType) {
            case '12_hours':
                return '12 Hours (7:00 AM - 5:00 PM)';
            case '24_hours':
                return '24 Hours (7:00 AM - 5:00 AM next day)';
            case 'overnight':
                return 'Overnight (7:00 PM - 5:00 AM)';
            default:
                return 'Unknown';
        }
    }
}