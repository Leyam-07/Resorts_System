<?php
// A simple script to test the server's mail() function.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$to = 'systemresorts@gmail.com'; // Replace with a recipient email you can check
$subject = 'Direct Mail Test';
$message = 'This is a test email sent directly from the mail() function.';
$headers = 'From: webmaster@example.com' . "\r\n" .
    'Reply-To: webmaster@example.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

if (mail($to, $subject, $message, $headers)) {
    echo 'Test email sent successfully.';
} else {
    echo 'Test email could not be sent.';
}
?>