<?php
// Prevent direct access
if (!defined('APP_LOADED')) {
    http_response_code(403);
    include __DIR__ . '/../errors/403.php';
    exit();
}

if (empty($bookings)): ?>
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
                    <td><?= htmlspecialchars(Booking::getTimeSlotDisplay($booking->TimeSlotType)) ?></td>
                    <td><?= htmlspecialchars($booking->NumberOfGuests) ?></td>
                    <td><span class="badge bg-primary"><?= htmlspecialchars($booking->Status) ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>