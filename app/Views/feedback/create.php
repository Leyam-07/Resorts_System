<?php
require_once __DIR__ . '/../partials/header.php';

// Assuming $booking is passed to this view from the controller
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Leave Feedback</h2>
                    <p>Your feedback is important to us. Please rate your experience for your booking on
                        <strong><?= htmlspecialchars($booking->bookingDate) ?></strong>.
                    </p>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <form action="?controller=feedback&action=submitFeedback" method="POST">
                        <input type="hidden" name="bookingId" value="<?= htmlspecialchars($booking->bookingId) ?>">

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
                        <a href="?controller=booking&action=showMyBookings" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
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

<?php
require_once __DIR__ . '/../partials/footer.php';
?>