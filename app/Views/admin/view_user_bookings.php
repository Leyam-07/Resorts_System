<?php
// Prevent direct access
if (!defined('APP_LOADED')) {
    http_response_code(403);
    include __DIR__ . '/../errors/403.php';
    exit();
}

$pageTitle = "Bookings for " . htmlspecialchars($user['Username']);
require_once __DIR__ . '/../partials/header.php';
?>

<h1><?= htmlspecialchars($pageTitle) ?></h1>

<?php if (empty($bookings)): ?>
    <div class="alert alert-info" role="alert">
        This user has no bookings.
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
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="?controller=admin&action=users" class="btn btn-secondary mt-3">Back to User List</a>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>