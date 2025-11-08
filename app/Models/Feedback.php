<?php

class Feedback {
    public $feedbackId;
    public $bookingId;
    public $rating;
    public $comment;
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

    public static function create(Feedback $feedback) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO Feedback (BookingID, Rating, Comment)
             VALUES (:bookingId, :rating, :comment)"
        );
        $stmt->bindValue(':bookingId', $feedback->bookingId, PDO::PARAM_INT);
        $stmt->bindValue(':rating', $feedback->rating, PDO::PARAM_INT);
        $stmt->bindValue(':comment', $feedback->comment, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return false;
    }

    public static function findByBookingId($bookingId) {
        $db = self::getDB();
        $stmt = $db->prepare("SELECT * FROM Feedback WHERE BookingID = :bookingId");
        $stmt->bindValue(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchObject('Feedback');
    }

    public static function findAll($resortId = null) {
        $db = self::getDB();
        $query = "SELECT f.FeedbackID, f.BookingID, f.Rating, f.Comment, f.CreatedAt, b.BookingDate, u.UserID as CustomerID, u.Username as CustomerName,
                         COALESCE(fac.Name, 'Overall Resort Experience') as FacilityName, r.Name as ResortName, b.ResortID
                  FROM Feedback f
                  JOIN Bookings b ON f.BookingID = b.BookingID
                  JOIN Users u ON b.CustomerID = u.UserID
                  LEFT JOIN Facilities fac ON b.FacilityID = fac.FacilityID
                  JOIN Resorts r ON b.ResortID = r.ResortID";

        $conditions = [];
        $params = [];

        if ($resortId !== null) {
            $conditions[] = "b.ResortID = :resortId";
            $params['resortId'] = $resortId;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY f.CreatedAt DESC";

        $stmt = $db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function findAllFacilityFeedbacks($resortId = null) {
        $db = self::getDB();
        $query = "SELECT ff.FacilityFeedbackID, ff.FeedbackID, ff.FacilityID, ff.Rating, ff.Comment, ff.CreatedAt,
                         u.UserID as CustomerID, u.Username as CustomerName, fac.Name as FacilityName, r.Name as ResortName, b.BookingDate, r.ResortID
                  FROM FacilityFeedback ff
                  JOIN Feedback f ON ff.FeedbackID = f.FeedbackID
                  JOIN Bookings b ON f.BookingID = b.BookingID
                  JOIN Users u ON b.CustomerID = u.UserID
                  JOIN Facilities fac ON ff.FacilityID = fac.FacilityID
                  JOIN Resorts r ON b.ResortID = r.ResortID";

        $conditions = [];
        $params = [];

        if ($resortId !== null) {
            $conditions[] = "b.ResortID = :resortId";
            $params['resortId'] = $resortId;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY ff.CreatedAt DESC";

        $stmt = $db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

   public static function findByFacilityId($facilityId) {
       $db = self::getDB();
       $stmt = $db->prepare(
           "SELECT ff.Rating, ff.Comment, ff.CreatedAt, u.Username as CustomerName, CONCAT('Feedback for ', fac.Name) as FeedbackContext
            FROM FacilityFeedback ff
            JOIN Feedback f ON ff.FeedbackID = f.FeedbackID
            JOIN Bookings b ON f.BookingID = b.BookingID
            JOIN Users u ON b.CustomerID = u.UserID
            JOIN Facilities fac ON ff.FacilityID = fac.FacilityID
            WHERE ff.FacilityID = :facilityId
            ORDER BY ff.CreatedAt DESC"
       );
       $stmt->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_OBJ);
   }

   public static function findByResortId($resortId) {
       $db = self::getDB();
       $stmt = $db->prepare(
           "SELECT f.Rating, f.Comment, f.CreatedAt, u.UserID as CustomerID, u.Username as CustomerName, COALESCE(fac.Name, 'General Resort Experience') as FacilityName
            FROM Feedback f
            JOIN Bookings b ON f.BookingID = b.BookingID
            JOIN Users u ON b.CustomerID = u.UserID
            LEFT JOIN Facilities fac ON b.FacilityID = fac.FacilityID
            WHERE b.ResortID = :resortId
            ORDER BY f.CreatedAt DESC"
       );
       $stmt->bindValue(':resortId', $resortId, PDO::PARAM_INT);
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_OBJ);
   }
    public static function createWithFacilities(Feedback $feedback, $facilityFeedbacks = []) {
        \ErrorHandler::log("Starting createWithFacilities with feedback: " . json_encode($feedback) . ", facilities: " . json_encode($facilityFeedbacks), 'DEBUG');

        if (empty($facilityFeedbacks)) {
            // No facilities, simple create
            return self::create($feedback);
        }

        $db = self::getDB();

        try {
            // Create main feedback first in its own transaction
            $db->beginTransaction();
            $stmt = $db->prepare(
                "INSERT INTO Feedback (BookingID, Rating, Comment)
                 VALUES (:bookingId, :rating, :comment)"
            );
            $stmt->bindValue(':bookingId', $feedback->bookingId, PDO::PARAM_INT);
            $stmt->bindValue(':rating', $feedback->rating, PDO::PARAM_INT);
            $stmt->bindValue(':comment', $feedback->comment, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                $db->rollBack();
                throw new Exception("Failed to create main feedback entry.");
            }
            $feedbackId = $db->lastInsertId();
            \ErrorHandler::log("Created main feedback with ID: $feedbackId", 'DEBUG');
            $db->commit();

            // Now create facility feedbacks individually (no transaction, as they are less critical)
            require_once __DIR__ . '/FacilityFeedback.php';
            $facilityErrors = [];
            foreach ($facilityFeedbacks as $facilityData) {
                \ErrorHandler::log("Processing facility feedback: " . json_encode($facilityData), 'DEBUG');
                $facilityFeedback = new FacilityFeedback();
                $facilityFeedback->feedbackId = $feedbackId;
                $facilityFeedback->facilityId = $facilityData['id'];
                $facilityFeedback->rating = $facilityData['rating'];
                $facilityFeedback->comment = $facilityData['comment'];

                if (!FacilityFeedback::create($facilityFeedback)) {
                    $facilityErrors[] = "Failed to save feedback for facility ID " . $facilityData['id'];
                    \ErrorHandler::log("Failed to create facility feedback for ID: " . $facilityData['id'], 'ERROR');
                } else {
                    \ErrorHandler::log("Created facility feedback for ID: " . $facilityData['id'], 'DEBUG');
                }
            }

            if (!empty($facilityErrors)) {
                // Some facility feedbacks failed, but main feedback succeeded
                \ErrorHandler::log("Main feedback created but some facility feedbacks failed: " . implode('; ', $facilityErrors), 'WARNING');
            }

            \ErrorHandler::log("Feed back submission completed", 'INFO');
            return $feedbackId;
        } catch (Exception $e) {
            // If rollback happened, main feedback failed
            \ErrorHandler::log("Feedback submission failed: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }

    /**
     * Get all feedback entries for a specific customer, including facility feedback details.
     */
    public static function findCustomerFeedbackHistory($customerId) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT
                f.FeedbackID,
                f.BookingID,
                f.Rating AS ResortRating,
                f.Comment AS ResortComment,
                f.CreatedAt,
                r.Name AS ResortName,
                b.BookingDate,
                GROUP_CONCAT(
                    CONCAT(
                        '{\"FacilityName\": \"', fac.Name, '\", ',
                        '\"Rating\": ', ff.Rating, ', ',
                        '\"Comment\": \"', REPLACE(ff.Comment, '\"', '\\\"'), '\"}'
                    ) SEPARATOR '|||'
                ) AS FacilityFeedbackJson
             FROM Feedback f
             JOIN Bookings b ON f.BookingID = b.BookingID
             JOIN Resorts r ON b.ResortID = r.ResortID
             LEFT JOIN FacilityFeedback ff ON f.FeedbackID = ff.FeedbackID
             LEFT JOIN Facilities fac ON ff.FacilityID = fac.FacilityID
             WHERE b.CustomerID = :customerId
             GROUP BY f.FeedbackID
             ORDER BY f.CreatedAt DESC"
        );
        $stmt->bindValue(':customerId', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        // Post-process the FacilityFeedbackJson column
        foreach ($results as $result) {
            $result->FacilityFeedbacks = [];
            if (!empty($result->FacilityFeedbackJson)) {
                $rawFeedbacks = explode('|||', $result->FacilityFeedbackJson);
                foreach ($rawFeedbacks as $rawFeedback) {
                    $result->FacilityFeedbacks[] = json_decode($rawFeedback);
                }
            }
            unset($result->FacilityFeedbackJson);
        }

        return $results;
    }

    /**
     * Get all feedback entries for a specific facility with customer details.
     */
    public static function findFacilityFeedbackDetails($facilityId) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT ff.Rating, ff.Comment, ff.CreatedAt, u.UserID AS CustomerID, u.Username AS CustomerName
             FROM FacilityFeedback ff
             JOIN Feedback f ON ff.FeedbackID = f.FeedbackID
             JOIN Bookings b ON f.BookingID = b.BookingID
             JOIN Users u ON b.CustomerID = u.UserID
             WHERE ff.FacilityID = :facilityId
             ORDER BY ff.CreatedAt DESC"
        );
        $stmt->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
