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

    public static function findAll() {
        $db = self::getDB();
        $stmt = $db->prepare(
            "SELECT f.*, b.BookingDate, u.Username as CustomerName, fac.Name as FacilityName
             FROM Feedback f
             JOIN Bookings b ON f.BookingID = b.BookingID
             JOIN Users u ON b.CustomerID = u.UserID
             JOIN Facilities fac ON b.FacilityID = fac.FacilityID
             ORDER BY f.CreatedAt DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

   public static function findByFacilityId($facilityId) {
       $db = self::getDB();
       $stmt = $db->prepare(
           "SELECT f.Rating, f.Comment, f.CreatedAt, u.Username as CustomerName
            FROM Feedback f
            JOIN Bookings b ON f.BookingID = b.BookingID
            JOIN Users u ON b.CustomerID = u.UserID
            WHERE b.FacilityID = :facilityId
            ORDER BY f.CreatedAt DESC"
       );
       $stmt->bindValue(':facilityId', $facilityId, PDO::PARAM_INT);
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_OBJ);
   }

   public static function findByResortId($resortId) {
       $db = self::getDB();
       $stmt = $db->prepare(
           "SELECT f.Rating, f.Comment, f.CreatedAt, u.Username as CustomerName, fac.Name as FacilityName
            FROM Feedback f
            JOIN Bookings b ON f.BookingID = b.BookingID
            JOIN Users u ON b.CustomerID = u.UserID
            JOIN Facilities fac ON b.FacilityID = fac.FacilityID
            WHERE fac.ResortID = :resortId
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
}
