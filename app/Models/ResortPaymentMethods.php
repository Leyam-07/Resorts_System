<?php

class ResortPaymentMethods {
    public $paymentMethodId;
    public $resortId;
    public $methodName;
    public $methodDetails;
    public $isActive;
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

    public static function create(ResortPaymentMethods $paymentMethod) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO ResortPaymentMethods (ResortID, MethodName, MethodDetails, IsActive)
             VALUES (:resortId, :methodName, :methodDetails, :isActive)"
        );
        $stmt->bindValue(':resortId', $paymentMethod->resortId, PDO::PARAM_INT);
        $stmt->bindValue(':methodName', $paymentMethod->methodName, PDO::PARAM_STR);
        $stmt->bindValue(':methodDetails', $paymentMethod->methodDetails, PDO::PARAM_STR);
        $stmt->bindValue(':isActive', $paymentMethod->isActive, PDO::PARAM_BOOL);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return false;
    }

    public static function findById($paymentMethodId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM ResortPaymentMethods WHERE PaymentMethodID = :paymentMethodId");
        $stmt->bindValue(':paymentMethodId', $paymentMethodId, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $paymentMethod = new ResortPaymentMethods();
            $paymentMethod->paymentMethodId = $data['PaymentMethodID'];
            $paymentMethod->resortId = $data['ResortID'];
            $paymentMethod->methodName = $data['MethodName'];
            $paymentMethod->methodDetails = $data['MethodDetails'];
            $paymentMethod->isActive = $data['IsActive'];
            $paymentMethod->createdAt = $data['CreatedAt'];
            return $paymentMethod;
        }
        return null;
    }

    public static function findByResortId($resortId, $activeOnly = true) {
        $db = self::getDB();
        $sql = "SELECT * FROM ResortPaymentMethods WHERE ResortID = :resortId";
        
        if ($activeOnly) {
            $sql .= " AND IsActive = 1";
        }
        
        $sql .= " ORDER BY MethodName ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':resortId', $resortId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function findAll() {
        $db = self::getDB();
        $stmt = $db->query(
            "SELECT rpm.*, r.Name as ResortName
             FROM ResortPaymentMethods rpm
             JOIN Resorts r ON rpm.ResortID = r.ResortID
             ORDER BY r.Name ASC, rpm.MethodName ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function update(ResortPaymentMethods $paymentMethod) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "UPDATE ResortPaymentMethods
             SET MethodName = :methodName, MethodDetails = :methodDetails, IsActive = :isActive
             WHERE PaymentMethodID = :paymentMethodId"
        );
        $stmt->bindValue(':methodName', $paymentMethod->methodName, PDO::PARAM_STR);
        $stmt->bindValue(':methodDetails', $paymentMethod->methodDetails, PDO::PARAM_STR);
        $stmt->bindValue(':isActive', $paymentMethod->isActive, PDO::PARAM_BOOL);
        $stmt->bindValue(':paymentMethodId', $paymentMethod->paymentMethodId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public static function delete($paymentMethodId) {
        $db = self::getDB();
        $stmt = $db->prepare("DELETE FROM ResortPaymentMethods WHERE PaymentMethodID = :paymentMethodId");
        $stmt->bindValue(':paymentMethodId', $paymentMethodId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function toggleActive($paymentMethodId) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "UPDATE ResortPaymentMethods 
             SET IsActive = NOT IsActive 
             WHERE PaymentMethodID = :paymentMethodId"
        );
        $stmt->bindValue(':paymentMethodId', $paymentMethodId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Create default payment methods for a new resort
     */
    public static function createDefaultPaymentMethods($resortId) {
        $defaultMethods = [
            [
                'name' => 'GCash',
                'details' => 'Send payment to GCash number: [Add GCash number here]'
            ],
            [
                'name' => 'Bank Transfer',
                'details' => 'Transfer to: [Add bank account details here]'
            ],
            [
                'name' => 'Cash on Arrival',
                'details' => 'Pay cash upon arrival at the resort'
            ]
        ];

        foreach ($defaultMethods as $method) {
            $paymentMethod = new ResortPaymentMethods();
            $paymentMethod->resortId = $resortId;
            $paymentMethod->methodName = $method['name'];
            $paymentMethod->methodDetails = $method['details'];
            $paymentMethod->isActive = true;

            self::create($paymentMethod);
        }
    }

    /**
     * Get formatted payment methods for display
     */
    public static function getFormattedPaymentMethods($resortId) {
        $methods = self::findByResortId($resortId, true);
        $formatted = [];

        foreach ($methods as $method) {
            $formatted[] = [
                'id' => $method->PaymentMethodID,
                'name' => $method->MethodName,
                'details' => $method->MethodDetails,
                'display' => $method->MethodName . ($method->MethodDetails ? ' - ' . $method->MethodDetails : '')
            ];
        }

        return $formatted;
    }

    /**
     * Check if a resort has any active payment methods
     */
    public static function hasActivePaymentMethods($resortId) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM ResortPaymentMethods 
             WHERE ResortID = :resortId AND IsActive = 1"
        );
        $stmt->bindValue(':resortId', $resortId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}