<?php

class FacilityFeedback {
    public $facilityFeedbackId;
    public $feedbackId;
    public $facilityId;
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

    public static function create(FacilityFeedback $feedback) {
        $db = self::getDB();
        $stmt = $db->prepare(
            "INSERT INTO FacilityFeedback (FeedbackID, FacilityID, Rating, Comment)
             VALUES (:feedbackId, :facilityId, :rating, :comment)"
        );
        $stmt->bindValue(':feedbackId', $feedback->feedbackId, PDO::PARAM_INT);
        $stmt->bindValue(':facilityId', $feedback->facilityId, PDO::PARAM_INT);
        $stmt->bindValue(':rating', $feedback->rating, PDO::PARAM_INT);
        $stmt->bindValue(':comment', $feedback->comment, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return false;
    }
}