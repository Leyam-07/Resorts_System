<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../../config/app.php'; // Ensure BASE_URL is defined
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
        $mail->Timeout    = 5; // 5 second timeout to prevent hanging
        $mail->CharSet = 'UTF-8';
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

            // Expiration logic
            $expirationWarning = '';
            if (!empty($booking->expiresAt) && new DateTime($booking->expiresAt) > new DateTime()) {
                try {
                    $expiresAtUTC = new DateTime($booking->expiresAt, new DateTimeZone('UTC'));
                    $expiresAtUTC->setTimezone(new DateTimeZone('Asia/Shanghai')); // Convert to local timezone
                    $expirationTime = htmlspecialchars($expiresAtUTC->format('F j, Y, g:i A'));
                    
                    // Construct payment URL
                    $paymentUrl = rtrim(BASE_URL, '/') . '/?controller=booking&action=showPaymentForm&id=' . $booking->bookingId;

                    $expirationWarning = "
                        <div style='border: 1px solid #ffc107; background-color: #fff3cd; padding: 15px; margin-top: 20px; border-radius: 5px;'>
                            <h4 style='color: #664d03; margin-top: 0;'>Action Required: Secure Your Booking</h4>
                            <p>To secure your reservation, a payment must be submitted. This booking will automatically expire if no payment is received within <strong>3 hours</strong>.</p>
                            <p><strong>Your reservation will expire on:</strong> {$expirationTime}</p>
                        </div>";
                } catch (Exception $e) {
                    // Fallback if date conversion fails
                    $expirationWarning = "<p><strong>Note:</strong> Your booking will expire soon if payment is not made.</p>";
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Booking Confirmation - Action Required';
            $mail->Body    = "
                <p>Dear {$customer['FirstName']},</p>
                <p>Your booking has been successfully created and is pending payment.</p>
                
                {$expirationWarning}

                <p><strong>Customer Information:</strong></p>
                <ul>
                    <li><strong>Name:</strong> {$customer['FirstName']} {$customer['LastName']}</li>
                    <li><strong>Contact Number:</strong> {$customer['PhoneNumber']}</li>
                </ul>
                <p><strong>Booking Details:</strong></p>
                <ul>
                    <li>Booking ID: {$booking->bookingId}</li>
                    <li>Date: {$booking->bookingDate}</li>
                    <li>Time: " . htmlspecialchars(Booking::getTimeSlotDisplay($booking->timeSlotType)) . "</li>
                </ul>
                <p>Thank you for choosing our resort!</p>";
            $mail->AltBody = 'Your booking has been created and requires payment. Booking ID: ' . $booking->bookingId;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log error instead of halting execution
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false; // Indicate failure but don't stop the booking process
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
                <p>Your booking for {$booking->bookingDate} (" . htmlspecialchars(Booking::getTimeSlotDisplay($booking->timeSlotType)) . ") has been cancelled.</p>
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

    /**
     * Send payment submission notification to admin
     */
    public static function sendPaymentSubmissionNotification($bookingId, $customer) {
        $booking = Booking::findById($bookingId);
        if (!$booking) return false;

        // Get resort and admin information
        require_once __DIR__ . '/../Models/Resort.php';
        require_once __DIR__ . '/../Models/BookingFacilities.php';

        $resort = Resort::findById($booking->resortId);
        $facilities = BookingFacilities::findByBookingId($bookingId);

        // Get admin users for notifications
        $admins = User::getAdminUsers();

        $facilityList = '';
        if (!empty($facilities)) {
            $facilityNames = array_map(function($f) { return $f->FacilityName; }, $facilities);
            $facilityList = 'Facilities: ' . implode(', ', $facilityNames) . '<br>';
        }

        $paidAmount = $booking->totalAmount - $booking->remainingBalance;
        $paymentStatus = ($booking->remainingBalance <= 0) ? 'Full Payment' : 'Partial Payment';

        $mail = self::getMailer();
        
        foreach ($admins as $admin) {
            try {
                $recipientEmail = $admin['Email'];
                $recipientName = $admin['FirstName'];

                if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                    error_log("Skipping invalid admin email address: " . ($recipientEmail ?? 'not set'));
                    continue;
                }

                // Special handling for the placeholder admin email for testing purposes
                if ($recipientEmail === 'admin@gmail.com') {
                    $recipientEmail = MAIL_FROM; // Redirect to the system's sender address
                    error_log("Redirecting placeholder admin email to system sender: " . MAIL_FROM);
                }
                
                $mail->clearAddresses(); // Clear previous addresses
                $mail->addAddress($recipientEmail, $recipientName);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Payment Submitted - Booking #' . $booking->bookingId;
                $mail->Body = "
                    <h2>Payment Proof Submitted</h2>
                    <p>Dear Admin,</p>
                    <p>A customer has submitted payment proof for review.</p>
                    
                    <h3>Customer Information:</h3>
                    <ul>
                        <li><strong>Name:</strong> {$customer['FirstName']} {$customer['LastName']}</li>
                        <li><strong>Email:</strong> {$customer['Email']}</li>
                        <li><strong>Phone:</strong> " . ($customer['PhoneNumber'] ?? 'N/A') . "</li>
                    </ul>
                    
                    <h3>Booking Details:</h3>
                    <ul>
                        <li><strong>Booking ID:</strong> #{$booking->bookingId}</li>
                        <li><strong>Resort:</strong> " . htmlspecialchars($resort->name ?? 'N/A') . "</li>
                        <li><strong>Date:</strong> " . date('F j, Y', strtotime($booking->bookingDate)) . "</li>
                        <li><strong>Timeframe:</strong> " . htmlspecialchars(Booking::getTimeSlotDisplay($booking->timeSlotType)) . "</li>

                        <li>{$facilityList}</li>
                    </ul>
                    
                    <h3>Payment Information:</h3>
                    <ul>
                        <li><strong>Total Booking Amount:</strong> ₱" . number_format($booking->totalAmount, 2) . "</li>
                        <li><strong>Amount Paid:</strong> ₱" . number_format($paidAmount, 2) . "</li>
                        <li><strong>Remaining Balance:</strong> ₱" . number_format($booking->remainingBalance, 2) . "</li>
                        <li><strong>Payment Status:</strong> {$paymentStatus}</li>
                        <li><strong>Reference Number:</strong> " . htmlspecialchars($booking->paymentReference ?? 'N/A') . "</li>
                    </ul>
                    
                    <p><strong>⚠️ Action Required:</strong> Please review and verify the payment proof through the admin dashboard.</p>
                    <p>The customer is waiting for confirmation to finalize their booking.</p>
                    
                    <p><em>Resort Management System</em></p>";
                
                $mail->AltBody = "Payment submitted for Booking #{$booking->bookingId} by {$customer['FirstName']} {$customer['LastName']}. Amount: ₱" . number_format($paidAmount, 2) . ". Ref: " . ($booking->paymentReference ?? 'N/A');

                $mail->send();
            } catch (Exception $e) {
                // Log error for this admin but continue with others
                continue;
            }
        }

        return true;
    }

    /**
     * Send payment submission confirmation to customer
     */
    public static function sendPaymentSubmissionConfirmation($bookingId) {
        $booking = Booking::findById($bookingId);
        if (!$booking) return false;

        $customer = User::findById($booking->customerId);
        if (!$customer) return false;

        // Get recent payment for this booking
        require_once __DIR__ . '/../Models/Payment.php';
        $payments = Payment::findByBookingId($bookingId);
        $latestPayment = end($payments); // Get the most recent payment

        // Get resort information
        require_once __DIR__ . '/../Models/Resort.php';
        $resort = Resort::findById($booking->resortId);

        // Get facilities information
        require_once __DIR__ . '/../Models/BookingFacilities.php';
        $facilities = BookingFacilities::findByBookingId($bookingId);

        $facilityList = '';
        if (!empty($facilities)) {
            $facilityNames = array_map(function($f) { return $f->FacilityName; }, $facilities);
            $facilityList = '<br><strong>Additional Facilities:</strong> ' . implode(', ', $facilityNames);
        }

        $mail = self::getMailer();
        try {
            //Recipients
            $mail->addAddress($customer['Email'], $customer['FirstName']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Payment Submitted - Booking #' . $booking->bookingId . ' (Pending Review)';
            $mail->Body = "
                <h2>Payment Submitted Successfully</h2>
                <p>Dear {$customer['FirstName']},</p>
                <p>Your payment has been successfully submitted and is now pending administrative review.</p>

                <h3>Customer Information:</h3>
                <ul>
                    <li><strong>Name:</strong> {$customer['FirstName']} {$customer['LastName']}</li>
                    <li><strong>Phone:</strong> {$customer['PhoneNumber']}</li>
                </ul>

                <h3>Booking Details:</h3>
                <ul>
                    <li><strong>Booking ID:</strong> #{$booking->bookingId}</li>
                    <li><strong>Resort:</strong> " . htmlspecialchars($resort->name ?? 'N/A') . "</li>
                    <li><strong>Date:</strong> " . date('F j, Y', strtotime($booking->bookingDate)) . "</li>
                    <li><strong>Timeframe:</strong> " . htmlspecialchars(Booking::getTimeSlotDisplay($booking->timeSlotType)) . "</li>

                    {$facilityList}
                </ul>

                <h3>Payment Details:</h3>
                <ul>
                    <li><strong>Total Booking Amount:</strong> ₱" . number_format($booking->totalAmount, 2) . "</li>
                    <li><strong>Amount Paid This Time:</strong> ₱" . number_format($latestPayment->Amount ?? 0, 2) . "</li>
                    <li><strong>Payment Method:</strong> " . htmlspecialchars($latestPayment->PaymentMethod ?? 'N/A') . "</li>
                    <li><strong>Reference Number:</strong> " . htmlspecialchars($booking->paymentReference ?? $latestPayment->Reference ?? 'N/A') . "</li>
                    <li><strong>Status:</strong> <span style='color: orange;'>Pending Review</span></li>
                </ul>

                <p><strong>What happens next?</strong></p>
                <ul>
                    <li>Our admin team will review your payment proof</li>
                    <li>You will receive another email once your payment is verified or if more information is needed</li>
                    <li>Verification typically takes 24-48 hours</li>
                </ul>

                <p>Thank you for your patience. You can check the status of your booking anytime through your account dashboard.</p>

                <p><em>The Resort Management Team</em></p>";

            $mail->AltBody = "Your payment for Booking #{$booking->bookingId} has been submitted and is pending review. You will be notified once it has been verified.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log error instead of halting execution
            error_log("Payment submission confirmation email failed: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Send payment verification confirmation to customer
     */
    public static function sendPaymentVerificationConfirmation($bookingId, $isVerified = true) {
        $booking = Booking::findById($bookingId);
        if (!$booking) return false;

        $customer = User::findById($booking->customerId);
        if (!$customer) return false;

        // Get resort information
        require_once __DIR__ . '/../Models/Resort.php';
        $resort = Resort::findById($booking->resortId);

        $mail = self::getMailer();
        try {
            //Recipients
            $mail->addAddress($customer['Email'], $customer['FirstName']);
            
            if ($isVerified) {
                // Payment verified - booking confirmed
                $mail->Subject = 'Payment Verified - Booking Confirmed #' . $booking->bookingId;
                $statusMessage = ($booking->remainingBalance <= 0) ? 'fully confirmed' : 'partially confirmed';
                $nextSteps = ($booking->remainingBalance > 0) ?
                    "<p><strong>Remaining Balance:</strong> ₱" . number_format($booking->remainingBalance, 2) . " - Please complete payment before your visit.</p>" :
                    "<p>Your booking is fully paid and confirmed!</p>";
                
                $mail->Body = "
                    <h2>Payment Verified!</h2>
                    <p>Dear {$customer['FirstName']},</p>
                    <p>Great news! Your payment has been verified and your booking is now {$statusMessage}.</p>
                    
                    <h3>Booking Details:</h3>
                    <ul>
                        <li><strong>Booking ID:</strong> #{$booking->bookingId}</li>
                        <li><strong>Resort:</strong> " . htmlspecialchars($resort->name ?? 'N/A') . "</li>
                        <li><strong>Date:</strong> " . date('F j, Y', strtotime($booking->bookingDate)) . "</li>
                        <li><strong>Status:</strong> <span style='color: green;'>Confirmed</span></li>
                    </ul>
                    
                    {$nextSteps}
                    
                    <p>We look forward to welcoming you to our resort!</p>
                    <p><em>The Resort Management Team</em></p>";
            } else {
                // Payment rejected
                $mail->Subject = 'Payment Issue - Booking #' . $booking->bookingId;
                $mail->Body = "
                    <h2>Payment Verification Issue</h2>
                    <p>Dear {$customer['FirstName']},</p>
                    <p>We were unable to verify your recent payment submission for booking #{$booking->bookingId}.</p>
                    
                    <p><strong>Please:</strong></p>
                    <ul>
                        <li>Check your payment proof image is clear and readable</li>
                        <li>Ensure the reference number is correct</li>
                        <li>Resubmit your payment proof through your account</li>
                    </ul>
                    
                    <p>If you need assistance, please contact us directly.</p>
                    <p><em>The Resort Management Team</em></p>";
            }

            $mail->AltBody = $isVerified ?
                "Payment verified! Booking #{$booking->bookingId} is confirmed." :
                "Payment verification failed for booking #{$booking->bookingId}. Please resubmit.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    public static function sendBookingExpiredNotification($bookingId) {
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
            $mail->Subject = 'Booking Expired Due to Non-Payment';
            $mail->Body    = "
                <p>Dear {$customer['FirstName']},</p>
                <p>We're writing to inform you that your booking (#{$booking->bookingId}) for {$booking->bookingDate} has expired.</p>
                <p>The reservation was automatically cancelled because payment was not received within the 3-hour window.</p>
                <p>If you are still interested, you will need to create a new booking. Please note that the same time slot may no longer be available.</p>
                <p>We hope to see you again soon.</p>";
            $mail->AltBody = 'Your booking #' . $booking->bookingId . ' has expired due to non-payment.';

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Expired booking notification could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

}
