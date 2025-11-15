<?php
$pageTitle = "My Feedback";
require_once __DIR__ . '/../partials/header.php';
?>

<div class="row">
    <div class="col-12 mb-4">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
    </div>

    <!-- Section 1: Bookings Awaiting Feedback -->
    <div class="col-12 mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-star"></i> Bookings Awaiting Your Feedback (<?= count($pendingFeedbacks) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendingFeedbacks)): ?>
                    <div class="alert alert-info m-3">
                        You currently have no completed bookings awaiting feedback. Thank you for using our resorts!
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Booking Date</th>
                                    <th>Resort</th>
                                    <th>Facilities</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingFeedbacks as $booking): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(date('F j, Y', strtotime($booking->BookingDate))) ?></td>
                                        <td><strong><?= htmlspecialchars($booking->ResortName ?? 'Unknown Resort') ?></strong></td>
                                        <td>
                                            <?php if (!empty($booking->FacilityNames)): ?>
                                                <span class="badge bg-info text-dark"><?= htmlspecialchars($booking->FacilityNames) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Resort access only</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm feedback-btn" data-bs-toggle="modal" data-bs-target="#feedbackModal" data-booking-id="<?= htmlspecialchars($booking->BookingID) ?>" data-booking-date="<?= htmlspecialchars(date('F j, Y', strtotime($booking->BookingDate))) ?>" data-resort-name="<?= htmlspecialchars($booking->ResortName ?? 'Unknown Resort') ?>">
                                                <i class="fas fa-star"></i> Leave Feedback
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="small text-muted p-3 mb-0">
                        <i class="fas fa-info-circle"></i> Click 'Leave Feedback' to submit your review for a completed booking.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Section 2: Feedback History -->
    <div class="col-12 mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-history"></i> My Feedback History (<?= count($feedbackHistory) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($feedbackHistory)): ?>
                    <div class="alert alert-secondary">
                        You have not submitted any feedback yet. We look forward to hearing about your experience!
                    </div>
                <?php else: ?>
                    <?php foreach ($feedbackHistory as $feedback): ?>
                        <div class="card mb-3 border-secondary">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-primary">Booking at <?= htmlspecialchars($feedback['ResortName']) ?> (<?= htmlspecialchars(date('F j, Y', strtotime($feedback['BookingDate']))) ?>)</h6>
                                    <span class="badge bg-secondary">Submitted: <?= htmlspecialchars(date('M j, Y', strtotime($feedback['CreatedAt']))) ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                
                                <!-- General Resort Feedback -->
                                <h6 class="text-success"><i class="fas fa-hotel"></i> Resort Rating</h6>
                                <p class="mb-1">
                                    <strong>Rating:</strong>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= ($i <= $feedback['ResortRating']) ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                    (<?= htmlspecialchars($feedback['ResortRating']) ?>/5)
                                </p>
                                <p class="small text-muted"><strong>Comment:</strong> <?= !empty($feedback['ResortComment']) ? htmlspecialchars($feedback['ResortComment']) : 'No comment provided.' ?></p>

                                <!-- Display Media -->
                                <?php if (!empty($feedback['Media'])): ?>
                                    <div class="feedback-media mt-2">
                                        <?php foreach ($feedback['Media'] as $media): ?>
                                            <?php if ($media['MediaType'] === 'Image'): ?>
                                                <img src="<?= BASE_URL . '/' . htmlspecialchars($media['MediaURL']) ?>" alt="Feedback Image" class="img-thumbnail" style="max-width: 150px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#mediaModal" data-media-url="<?= BASE_URL . '/' . htmlspecialchars($media['MediaURL']) ?>" data-media-type="Image">
                                            <?php elseif ($media['MediaType'] === 'Video'): ?>
                                                <video controls class="img-thumbnail" style="max-width: 150px;">
                                                    <source src="<?= BASE_URL . '/' . htmlspecialchars($media['MediaURL']) ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($feedback['FacilityFeedbacks']) && count($feedback['FacilityFeedbacks']) > 0): ?>
                                    <hr>
                                    <h6 class="text-info"><i class="fas fa-box-open"></i> Facility Feedback</h6>
                                    <ul class="list-group list-group-flush mt-2">
                                        <?php foreach ($feedback['FacilityFeedbacks'] as $facilityFb): ?>
                                            <?php if (isset($facilityFb['FacilityName'])): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                                    <div class="ms-2 me-auto">
                                                        <div class="fw-bold"><?= htmlspecialchars($facilityFb['FacilityName']) ?></div>
                                                        <p class="mb-1 small">
                                                            <strong>Rating:</strong>
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star <?= ($i <= $facilityFb['Rating']) ? 'text-warning' : 'text-muted' ?>"></i>
                                                            <?php endfor; ?>
                                                            (<?= htmlspecialchars($facilityFb['Rating']) ?>/5)
                                                        </p>
                                                        <p class="mb-0 small text-muted">Comment: <?= !empty($facilityFb['Comment']) ? htmlspecialchars($facilityFb['Comment']) : 'No comment provided.' ?></p>
                                                    </div>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Modal (Copied from my_bookings.php) -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Leave Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Your feedback is important to us. Please rate your experience for your booking at <strong id="modalResortName"></strong> on <strong id="modalBookingDate"></strong>.</p>
                <form id="feedbackForm" action="?controller=feedback&action=submitFeedback" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="bookingId" id="modalBookingId">
                    <input type="hidden" name="redirect_url" value="?controller=feedback&action=showMyFeedback">
                    
                    <!-- Resort Feedback -->
                    <div class="feedback-section mb-4 p-3 border rounded bg-light">
                        <h5>Resort Feedback</h5>
                        <div class="mb-3">
                            <label for="resort_rating" class="form-label"><strong>Rating (1 to 5)</strong></label>
                            <div class="rating-stars">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="resort_star<?= $i ?>" name="resort_rating" value="<?= $i ?>" required>
                                    <label for="resort_star<?= $i ?>">&starf;</label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="resort_comment" class="form-label"><strong>Comments (Optional)</strong></label>
                            <textarea class="form-control" id="resort_comment" name="resort_comment" rows="3" placeholder="Tell us more about your experience at the resort..."></textarea>
                        </div>
                    </div>

                    <!-- Media Upload -->
                    <div class="mb-4">
                        <label for="media" class="form-label"><strong>Upload Images or Videos (Optional)</strong></label>
                        <input type="file" class="form-control" id="media" name="media[]" multiple accept="image/*,video/*">
                    </div>

                    <!-- Facility Feedback Section (Dynamically Populated) -->
                    <div id="facilityFeedbackSection">
                        <!-- Facility feedback forms will be inserted here -->
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Media Modal -->
<div class="modal fade" id="mediaModal" tabindex="-1" aria-labelledby="mediaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaModalLabel">View Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="mediaModalBody">
                <!-- Media will be loaded here -->
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
    // Media Modal Handler
    var mediaModal = document.getElementById('mediaModal');
    if (mediaModal) {
        mediaModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var mediaUrl = button.getAttribute('data-media-url');
            var mediaType = button.getAttribute('data-media-type');
            var modalBody = mediaModal.querySelector('#mediaModalBody');

            if (mediaType === 'Image') {
                modalBody.innerHTML = `<img src="${mediaUrl}" class="img-fluid" alt="Feedback Media">`;
            } else if (mediaType === 'Video') {
                modalBody.innerHTML = `<video src="${mediaUrl}" class="img-fluid" controls autoplay></video>`;
            }
        });
    }

    // Feedback Modal Handler
    var feedbackModal = document.getElementById('feedbackModal');
    if (feedbackModal) {
        feedbackModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var bookingId = button.getAttribute('data-booking-id');
            var bookingDate = button.getAttribute('data-booking-date');
            var resortName = button.getAttribute('data-resort-name');

            var modalBookingDate = feedbackModal.querySelector('#modalBookingDate');
            var modalBookingIdInput = feedbackModal.querySelector('#modalBookingId');
            var modalResortName = feedbackModal.querySelector('#modalResortName');
            var facilityFeedbackSection = feedbackModal.querySelector('#facilityFeedbackSection');

            modalBookingDate.textContent = bookingDate;
            modalBookingIdInput.value = bookingId;
            modalResortName.textContent = resortName;

            facilityFeedbackSection.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

            fetch('?controller=booking&action=getFacilitiesForBooking&booking_id=' + bookingId)
                .then(response => response.json())
                .then(facilities => {
                    facilityFeedbackSection.innerHTML = '';
                    if (facilities.length > 0) {
                        var facilityHtml = '<h5>Optional Facilities Feedback</h5>';
                        facilities.forEach(facility => {
                            facilityHtml += `
                                <div class="feedback-section mb-3 p-3 border rounded">
                                    <h6>${facility.Name}</h6>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Rating (1 to 5)</strong></label>
                                        <div class="rating-stars">
                                            <input type="radio" id="facility_star_${facility.FacilityID}_5" name="facilities[${facility.FacilityID}][rating]" value="5"><label for="facility_star_${facility.FacilityID}_5">&starf;</label>
                                            <input type="radio" id="facility_star_${facility.FacilityID}_4" name="facilities[${facility.FacilityID}][rating]" value="4"><label for="facility_star_${facility.FacilityID}_4">&starf;</label>
                                            <input type="radio" id="facility_star_${facility.FacilityID}_3" name="facilities[${facility.FacilityID}][rating]" value="3"><label for="facility_star_${facility.FacilityID}_3">&starf;</label>
                                            <input type="radio" id="facility_star_${facility.FacilityID}_2" name="facilities[${facility.FacilityID}][rating]" value="2"><label for="facility_star_${facility.FacilityID}_2">&starf;</label>
                                            <input type="radio" id="facility_star_${facility.FacilityID}_1" name="facilities[${facility.FacilityID}][rating]" value="1"><label for="facility_star_${facility.FacilityID}_1">&starf;</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Comments (Optional)</strong></label>
                                        <textarea class="form-control" name="facilities[${facility.FacilityID}][comment]" rows="2" placeholder="Feedback for ${facility.Name}..."></textarea>
                                    </div>
                                </div>
                            `;
                        });
                        facilityFeedbackSection.innerHTML = facilityHtml;
                    }
                })
                .catch(error => {
                    console.error('Error fetching facilities:', error);
                    facilityFeedbackSection.innerHTML = '<div class="alert alert-warning">Could not load facilities for feedback.</div>';
                });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>