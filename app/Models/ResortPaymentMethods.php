<?php

class ResortPaymentMethods {
    public $paymentMethodId;
    public $resortId;
    public $methodType;
    public $accountDetails;
    public $isDefault;
    public $isActive;
    public $createdAt;
    public $updatedAt;

    private static $db;

    private static function getDB() {
        if (!self::$db) {
            require_once __DIR__ . '/../Helpers/Database.php';
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    public static function create(ResortPaymentMethods $paymentMethod) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO ResortPaymentMethods (ResortID, MethodType, AccountDetails, IsDefault, IsActive)
             VALUES (:resortId, :methodType, :accountDetails, :isDefault, :isActive)"
        );
        $stmt->bindValue(':resortId', $paymentMethod->resortId, PDO::PARAM_INT);
        $stmt->bindValue(':methodType', $paymentMethod->methodType, PDO::PARAM_STR);
        $stmt->bindValue(':accountDetails', $paymentMethod->accountDetails, PDO::PARAM_STR);
        $stmt->bindValue(':isDefault', $paymentMethod->isDefault, PDO::PARAM_BOOL);
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
            $paymentMethod->methodType = $data['MethodType'];
            $paymentMethod->accountDetails = $data['AccountDetails'];
            $paymentMethod->isDefault = $data['IsDefault'];
            $paymentMethod->isActive = $data['IsActive'];
            $paymentMethod->createdAt = $data['CreatedAt'];
            $paymentMethod->updatedAt = $data['UpdatedAt'];
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
        
        $sql .= " ORDER BY MethodType ASC";
        
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
             ORDER BY r.Name ASC, rpm.MethodType ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function update(ResortPaymentMethods $paymentMethod) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "UPDATE ResortPaymentMethods
             SET MethodType = :methodType, AccountDetails = :accountDetails, IsDefault = :isDefault, IsActive = :isActive
             WHERE PaymentMethodID = :paymentMethodId"
        );
        $stmt->bindValue(':methodType', $paymentMethod->methodType, PDO::PARAM_STR);
        $stmt->bindValue(':accountDetails', $paymentMethod->accountDetails, PDO::PARAM_STR);
        $stmt->bindValue(':isDefault', $paymentMethod->isDefault, PDO::PARAM_BOOL);
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
                'type' => 'Gcash',
                'details' => 'Send payment to GCash number: [Add GCash number here]'
            ],
            [
                'type' => 'Bank Transfer',
                'details' => 'Transfer to: [Add bank account details here]'
            ],
            [
                'type' => 'Cash',
                'details' => 'Pay cash upon arrival at the resort'
            ]
        ];

        foreach ($defaultMethods as $method) {
            $paymentMethod = new ResortPaymentMethods();
            $paymentMethod->resortId = $resortId;
            $paymentMethod->methodType = $method['type'];
            $paymentMethod->accountDetails = $method['details'];
            $paymentMethod->isDefault = ($method['type'] === 'Gcash'); // Example default
            $paymentMethod->isActive = true;

            self::create($paymentMethod);
        }
    }

    /**
     * Get suggested payment method examples for admins
     */
    public static function getAvailableMethodTypes() {
        // Return common payment method examples that admins can use or modify
        // These are no longer restricted - admins can enter any custom method name
        return ['GCash', 'Maya', 'PayPal', 'BPI', 'BDO', 'UnionBank', 'Cash'];
    }

    /**
     * Get formatted payment methods for display
     */
    public static function getFormattedPaymentMethods($resortId) {
        $methods = self::findByResortId($resortId, true);
        $formatted = [];

        foreach ($methods as $method) {
            $formatted[] = [
                'name' => $method->MethodType,
                'details' => $method->AccountDetails
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
