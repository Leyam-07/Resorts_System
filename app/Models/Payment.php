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
}