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
                    <td>
                        <?php
                            $timeSlotDisplay = [
                                '12_hours' => '12 Hours (7 AM to 5 PM)',
                                '24_hours' => '24 Hours (7 AM to 5 AM)',
                                'overnight' => 'Overnight (7 PM to 5 AM)'
                            ];
                            echo htmlspecialchars($timeSlotDisplay[$booking->TimeSlotType] ?? 'N/A');
                        ?>
                    </td>
                    <td><?= htmlspecialchars($booking->NumberOfGuests) ?></td>
                    <td><span class="badge bg-primary"><?= htmlspecialchars($booking->Status) ?></span></td>
                    <td>
                        <?php if ($booking->Status === 'Completed'): ?>
                            <?php if ($booking->hasFeedback): ?>
                                <span class="badge bg-secondary">Feedback Submitted</span>
                            <?php else: ?>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#feedbackModal" data-booking-id="<?= htmlspecialchars($booking->BookingID) ?>" data-booking-date="<?= htmlspecialchars(date('F j, Y', strtotime($booking->BookingDate))) ?>">
                                    Leave Feedback
                                </button>
                            <?php endif; ?>
                        <?php elseif ($booking->Status !== 'Cancelled'): ?>
                            <a href="?controller=booking&action=cancelBooking&id=<?= htmlspecialchars($booking->BookingID) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="?" class="btn btn-secondary mt-3">Back to Dashboard</a>
<?php endif; ?>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Leave Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Your feedback is important to us. Please rate your experience for your booking on <strong id="modalBookingDate"></strong>.</p>
                <form id="feedbackForm" action="?controller=feedback&action=submitFeedback" method="POST">
                    <input type="hidden" name="bookingId" id="modalBookingId">
                    <div class="mb-3">
                        <label for="rating" class="form-label"><strong>Rating (1 to 5)</strong></label>
                        <div class="rating-stars">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                                <label for="star<?= $i ?>">&starf;</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="comment" class="form-label"><strong>Comments (Optional)</strong></label>
                        <textarea class="form-control" id="comment" name="comment" rows="5" placeholder="Tell us more about your experience..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .rating-stars {
        display: inline-block;
        direction: rtl;
        font-size: 2.5rem;
    }
    .rating-stars input[type="radio"] {
        display: none;
    }
    .rating-stars label {
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s;
    }
    .rating-stars input[type="radio"]:checked ~ label,
    .rating-stars label:hover,
    .rating-stars label:hover ~ label {
        color: #ffc107;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var feedbackModal = document.getElementById('feedbackModal');
    feedbackModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var bookingId = button.getAttribute('data-booking-id');
        var bookingDate = button.getAttribute('data-booking-date');

        var modalTitle = feedbackModal.querySelector('.modal-title');
        var modalBookingDate = feedbackModal.querySelector('#modalBookingDate');
        var modalBookingIdInput = feedbackModal.querySelector('#modalBookingId');

        modalBookingDate.textContent = bookingDate;
        modalBookingIdInput.value = bookingId;
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>