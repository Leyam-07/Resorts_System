<?php

class Payment {
    public $paymentId;
    public $bookingId;
    public $amount;
    public $paymentMethod;
    public $paymentDate;
    public $status;
    public $proofOfPaymentURL;

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

    public static function create(Payment $payment) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO Payments (BookingID, Amount, PaymentMethod, Status, ProofOfPaymentURL)
             VALUES (:bookingId, :amount, :paymentMethod, :status, :proofOfPaymentURL)"
        );
        $stmt->bindValue(':bookingId', $payment->bookingId, PDO::PARAM_INT);
        $stmt->bindValue(':amount', $payment->amount, PDO::PARAM_STR);
        $stmt->bindValue(':paymentMethod', $payment->paymentMethod, PDO::PARAM_STR);
        $stmt->bindValue(':status', $payment->status, PDO::PARAM_STR);
        $stmt->bindValue(':proofOfPaymentURL', $payment->proofOfPaymentURL, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return false;
    }

    public static function findByBookingId($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM Payments WHERE BookingID = :bookingId ORDER BY PaymentDate DESC");
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public static function findById($paymentId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM Payments WHERE PaymentID = :paymentId");
        $stmt->bindValue(':paymentId', $paymentId, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $payment = new Payment();
            $payment->paymentId = $data['PaymentID'];
            $payment->bookingId = $data['BookingID'];
            $payment->amount = $data['Amount'];
            $payment->paymentMethod = $data['PaymentMethod'];
            $payment->paymentDate = $data['PaymentDate'];
            $payment->status = $data['Status'];
            $payment->proofOfPaymentURL = $data['ProofOfPaymentURL'];
            return $payment;
        }
        return null;
    }

    public static function updateStatus($paymentId, $status) {
        $db = self::getDB();
        $stmt = $db->prepare("UPDATE Payments SET Status = :status WHERE PaymentID = :paymentId");
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':paymentId', $paymentId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Create payment record from booking payment submission
     */
    public static function createFromBookingPayment($bookingId, $amount, $paymentReference, $paymentProofURL) {
        $payment = new Payment();
        $payment->bookingId = $bookingId;
        $payment->amount = $amount;
        $payment->paymentMethod = 'Online Payment'; // Default for online submissions
        $payment->status = 'Pending'; // Pending admin verification
        $payment->proofOfPaymentURL = $paymentProofURL;
        
        // Add payment reference to the payment method details
        $payment->paymentMethod = 'Online Payment (Ref: ' . $paymentReference . ')';
        
        return self::create($payment);
    }

    /**
     * Get total amount paid for a booking
     */
    public static function getTotalPaidAmount($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT SUM(Amount) FROM Payments WHERE BookingID = :bookingId AND Status IN ('Paid', 'Verified')");
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result ?: 0;
    }

    /**
     * Get pending payments for admin review
     */
    public static function getPendingPayments($resortId = null) {
        $db = self::getDB();
        $sql = "SELECT p.*, b.BookingID, b.BookingDate, b.TotalAmount, b.RemainingBalance,
                       u.Username as CustomerName, u.Email as CustomerEmail,
                       r.Name as ResortName
                FROM Payments p
                JOIN Bookings b ON p.BookingID = b.BookingID
                JOIN Users u ON b.CustomerID = u.UserID
                JOIN Resorts r ON b.ResortID = r.ResortID
                WHERE p.Status = 'Pending'";
        
        $params = [];
        if ($resortId) {
            $sql .= " AND b.ResortID = ?";
            $params[] = $resortId;
        }
        
        $sql .= " ORDER BY p.PaymentDate DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Verify payment and update booking status
     */
    public static function verifyPayment($paymentId, $adminId) {
        $db = self::getDB();
        
        try {
            $db->beginTransaction();
            
            // Update payment status
            $stmt = $db->prepare("UPDATE Payments SET Status = 'Verified' WHERE PaymentID = ?");
            $stmt->execute([$paymentId]);
            
            // Get payment info to update booking
            $payment = self::findById($paymentId);
            if (!$payment) {
                throw new Exception("Payment not found");
            }
            
            // Update booking balance and status
            require_once __DIR__ . '/Booking.php';
            $booking = Booking::findById($payment->bookingId);
            if (!$booking) {
                throw new Exception("Booking not found");
            }
            
            $newBalance = max(0, $booking->remainingBalance - $payment->amount);
            $newStatus = ($newBalance <= 0) ? 'Confirmed' : 'Pending';
            
            $updateStmt = $db->prepare("UPDATE Bookings SET RemainingBalance = ?, Status = ? WHERE BookingID = ?");
            $updateStmt->execute([$newBalance, $newStatus, $booking->bookingId]);
            
            $db->commit();
            return true;
            
        } catch (Exception $e) {
            $db->rollback();
            return false;
        }
    }

    /**
     * Reject payment with reason
     */
    public static function rejectPayment($paymentId, $reason = null) {
        $db = self::getDB();
        $stmt = $db->prepare("UPDATE Payments SET Status = 'Rejected' WHERE PaymentID = ?");
        return $stmt->execute([$paymentId]);
    }

    /**
     * Get payment history for a booking
     */
    public static function getPaymentHistory($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM Payments WHERE BookingID = ? ORDER BY PaymentDate DESC");
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get count of pending payments for notification
     */
    public static function getPendingPaymentCount() {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM Payments WHERE Status = 'Pending'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}