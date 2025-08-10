<?php
$pageTitle = "My Bookings";
require_once __DIR__ . '/../partials/header.php';
?>

<h1><?= htmlspecialchars($pageTitle) ?></h1>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success" role="alert">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
        <?php unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($_SESSION['error_message']) ?>
        <?php unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<?php if (empty($bookings)): ?>
    <div class="alert alert-info" role="alert">
        You have no bookings yet. <a href="?controller=booking&action=showBookingForm">Make a booking now!</a>
    </div>
<?php else: ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Facility</th>
                <th>Date</th>
                <th>Time</th>
                <th>Guests</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking->FacilityName) ?></td>
                    <td><?= htmlspecialchars(date('F j, Y', strtotime($booking->BookingDate))) ?></td>
                    <td><?= htmlspecialchars(date('g:i A', strtotime($booking->StartTime))) ?> - <?= htmlspecialchars(date('g:i A', strtotime($booking->EndTime))) ?></td>
                    <td><?= htmlspecialchars($booking->NumberOfGuests) ?></td>
                    <td><span class="badge bg-primary"><?= htmlspecialchars($booking->Status) ?></span></td>
                    <td>
                        <a href="?controller=booking&action=cancelBooking&id=<?= htmlspecialchars($booking->BookingID) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>