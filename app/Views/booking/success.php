<?php
$pageTitle = "Booking Successful";
require_once __DIR__ . '/../partials/header.php';
?>

<div class="alert alert-success" role="alert">
    <h4 class="alert-heading">Booking Successful!</h4>
    <p>Your booking has been confirmed. Your Booking ID is: <strong><?= htmlspecialchars($_GET['id']) ?></strong></p>
    <hr>
    <p class="mb-0">You will receive a confirmation email shortly. Thank you for booking with us!</p>
</div>
<a href="?" class="btn btn-primary">Back to Dashboard</a>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>