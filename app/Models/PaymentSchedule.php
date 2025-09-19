<?php

/**
 * PaymentSchedule Model - Phase 6 Enhancement
 * Manages payment schedules and installments for bookings
 */
class PaymentSchedule {
    public $scheduleId;
    public $bookingId;
    public $installmentNumber;
    public $dueDate;
    public $amount;
    public $status; // 'Pending', 'Paid', 'Overdue', 'Cancelled'
    public $paymentId; // Link to actual payment when paid
    public $createdAt;
    public $updatedAt;

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

    /**
     * Create a payment schedule for a booking
     */
    public static function createScheduleForBooking($bookingId, $totalAmount, $downPayment = null, $installments = 2) {
        $db = self::getDB();
        
        // Default: 50% down payment if not specified
        if ($downPayment === null) {
            $downPayment = $totalAmount * 0.5;
        }
        
        // Validate inputs
        if ($downPayment >= $totalAmount) {
            $downPayment = $totalAmount; // Full payment
            $installments = 1;
        }
        
        $remainingAmount = $totalAmount - $downPayment;
        $installmentAmount = $remainingAmount / ($installments - 1);
        
        $db->beginTransaction();
        
        try {
            // Create down payment schedule
            $schedule = new PaymentSchedule();
            $schedule->bookingId = $bookingId;
            $schedule->installmentNumber = 1;
            $schedule->dueDate = date('Y-m-d'); // Due immediately
            $schedule->amount = $downPayment;
            $schedule->status = 'Pending';
            
            self::createScheduleItem($schedule);
            
            // Create remaining installment schedules
            for ($i = 2; $i <= $installments; $i++) {
                $schedule = new PaymentSchedule();
                $schedule->bookingId = $bookingId;
                $schedule->installmentNumber = $i;
                $schedule->dueDate = date('Y-m-d', strtotime('+' . (($i - 1) * 7) . ' days')); // Weekly installments
                $schedule->amount = ($i == $installments) ? 
                    ($remainingAmount - ($installmentAmount * ($installments - 2))) : // Last installment gets remainder
                    $installmentAmount;
                $schedule->status = 'Pending';
                
                self::createScheduleItem($schedule);
            }
            
            $db->commit();
            return true;
            
        } catch (Exception $e) {
            $db->rollback();
            return false;
        }
    }

    /**
     * Create individual schedule item
     */
    private static function createScheduleItem(PaymentSchedule $schedule) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO PaymentSchedules (BookingID, InstallmentNumber, DueDate, Amount, Status)
             VALUES (:bookingId, :installmentNumber, :dueDate, :amount, :status)"
        );
        
        $stmt->bindValue(':bookingId', $schedule->bookingId, PDO::PARAM_INT);
        $stmt->bindValue(':installmentNumber', $schedule->installmentNumber, PDO::PARAM_INT);
        $stmt->bindValue(':dueDate', $schedule->dueDate, PDO::PARAM_STR);
        $stmt->bindValue(':amount', $schedule->amount, PDO::PARAM_STR);
        $stmt->bindValue(':status', $schedule->status, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Get payment schedule for a booking
     */
    public static function findByBookingId($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM PaymentSchedules WHERE BookingID = :bookingId ORDER BY InstallmentNumber ASC");
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Mark a schedule item as paid
     */
    public static function markAsPaid($scheduleId, $paymentId) {
        $db = self::getDB();
        $stmt = $db->prepare("UPDATE PaymentSchedules SET Status = 'Paid', PaymentID = :paymentId WHERE ScheduleID = :scheduleId");
        $stmt->bindValue(':paymentId', $paymentId, PDO::PARAM_INT);
        $stmt->bindValue(':scheduleId', $scheduleId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Get overdue payment schedules
     */
    public static function getOverdueSchedules($resortId = null) {
        $db = self::getDB();
        
        $sql = "SELECT ps.*, b.BookingID, b.BookingDate, u.Username as CustomerName, r.Name as ResortName
                FROM PaymentSchedules ps
                JOIN Bookings b ON ps.BookingID = b.BookingID
                JOIN Users u ON b.CustomerID = u.UserID
                JOIN Resorts r ON b.ResortID = r.ResortID
                WHERE ps.Status = 'Pending' AND ps.DueDate < CURDATE()";
        
        $params = [];
        if ($resortId) {
            $sql .= " AND b.ResortID = :resortId";
            $params[':resortId'] = $resortId;
        }
        
        $sql .= " ORDER BY ps.DueDate ASC";
        
        $stmt = $db->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Update overdue schedules
     */
    public static function updateOverdueStatus() {
        $db = self::getDB();
        $stmt = $db->prepare("UPDATE PaymentSchedules SET Status = 'Overdue' WHERE Status = 'Pending' AND DueDate < CURDATE()");
        return $stmt->execute();
    }

    /**
     * Calculate next payment due for a booking
     */
    public static function getNextPaymentDue($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT * FROM PaymentSchedules 
             WHERE BookingID = :bookingId AND Status IN ('Pending', 'Overdue') 
             ORDER BY InstallmentNumber ASC 
             LIMIT 1"
        );
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get payment schedule summary for a booking
     */
    public static function getScheduleSummary($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT 
                COUNT(*) as TotalInstallments,
                SUM(Amount) as TotalAmount,
                SUM(CASE WHEN Status = 'Paid' THEN Amount ELSE 0 END) as PaidAmount,
                SUM(CASE WHEN Status IN ('Pending', 'Overdue') THEN Amount ELSE 0 END) as RemainingAmount,
                COUNT(CASE WHEN Status = 'Overdue' THEN 1 END) as OverdueCount
             FROM PaymentSchedules 
             WHERE BookingID = :bookingId"
        );
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Cancel remaining payment schedules for a booking
     */
    public static function cancelRemainingSchedules($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare("UPDATE PaymentSchedules SET Status = 'Cancelled' WHERE BookingID = :bookingId AND Status IN ('Pending', 'Overdue')");
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Flexible payment schedule creation with custom dates
     */
    public static function createCustomSchedule($bookingId, $scheduleItems) {
        $db = self::getDB();
        $db->beginTransaction();
        
        try {
            foreach ($scheduleItems as $index => $item) {
                $schedule = new PaymentSchedule();
                $schedule->bookingId = $bookingId;
                $schedule->installmentNumber = $index + 1;
                $schedule->dueDate = $item['dueDate'];
                $schedule->amount = $item['amount'];
                $schedule->status = 'Pending';
                
                self::createScheduleItem($schedule);
            }
            
            $db->commit();
            return true;
            
        } catch (Exception $e) {
            $db->rollback();
            return false;
        }
    }
}