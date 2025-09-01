<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';

require_once __DIR__ . '/../../config/mail.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Booking.php';

class Notification {

    private static function getMailer() {
        $mail = new PHPMailer(true);
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = MAIL_SMTPAUTH;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_SMTPSECURE;
        $mail->Port       = MAIL_PORT;
        // Sender
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        return $mail;
    }

    public static function sendBookingConfirmation($bookingId) {
        $booking = Booking::findById($bookingId);
        if (!$booking) return false;

        $customer = User::findById($booking->customerId);
        if (!$customer) return false;

        $mail = self::getMailer();
        try {
            //Recipients
            $mail->addAddress($customer['Email'], $customer['FirstName']);
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Booking Confirmation';
            $mail->Body    = "
                <p>Dear {$customer['FirstName']},</p>
                <p>Your booking has been successfully created.</p>
                <p><strong>Booking Details:</strong></p>
                <ul>
                    <li>Booking ID: {$booking->bookingId}</li>
                    <li>Date: {$booking->bookingDate}</li>
                    <li>Time: {$booking->startTime} - {$booking->endTime}</li>
                    <li>Guests: {$booking->numberOfGuests}</li>
                </ul>
                <p>Thank you for choosing our resort!</p>";
            $mail->AltBody = 'Your booking has been successfully created. Booking ID: ' . $booking->bookingId;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log error: "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
            return false;
        }
    }

    public static function sendBookingCancellation($booking) {
        if (!$booking) return false;

        $customer = User::findById($booking->customerId);
        if (!$customer) return false;

        $mail = self::getMailer();
        try {
            //Recipients
            $mail->addAddress($customer['Email'], $customer['FirstName']);
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Booking Cancellation';
            $mail->Body    = "
                <p>Dear {$customer['FirstName']},</p>
                <p>Your booking for {$booking->bookingDate} from {$booking->startTime} to {$booking->endTime} has been cancelled.</p>
                <p>If you did not request this cancellation, please contact us immediately.</p>
                <p>We hope to see you again soon.</p>";
            $mail->AltBody = 'Your booking for ' . $booking->bookingDate . ' has been cancelled.';

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log error: "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
            return false;
        }
    }

    public static function sendWelcomeEmail($userId) {
        $user = User::findById($userId);
        if (!$user) return false;

        $mail = self::getMailer();
        try {
            //Recipients
            $mail->addAddress($user['Email'], $user['FirstName']);
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Our Resort System!';
            $mail->Body    = "
                <p>Dear {$user['FirstName']},</p>
                <p>Thank you for registering an account with us. We're excited to have you!</p>
                <p>You can now browse facilities and make bookings at your convenience.</p>
                <p>Best regards,<br>The Resort Team</p>";
            $mail->AltBody = 'Welcome to Our Resort System! Thank you for registering.';

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log error: "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
            return false;
        }
    }
}