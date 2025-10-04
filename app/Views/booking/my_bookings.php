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
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Resort</th>
                    <th>Date & Time</th>
                    <th>Facilities</th>
                    <th>Guests</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($booking->ResortName ?? 'Unknown Resort') ?></strong>
                        </td>
                        <td>
                            <div><strong><?= htmlspecialchars(date('F j, Y', strtotime($booking->BookingDate))) ?></strong></div>
                            <small class="text-muted">
                                <?php
                                    $timeSlotDisplay = [
                                        '12_hours' => '12 Hours (7:00 AM - 5:00 PM)',
                                        '24_hours' => '24 Hours (7:00 AM - 5:00 AM next day)',
                                        'overnight' => 'Overnight (7:00 PM - 5:00 AM)'
                                    ];
                                    echo htmlspecialchars($timeSlotDisplay[$booking->TimeSlotType] ?? 'N/A');
                                ?>
                            </small>
                        </td>
                        <td>
                            <?php if (!empty($booking->FacilityNames)): ?>
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($booking->FacilityNames) ?></span>
                            <?php else: ?>
                                <span class="text-muted small">Resort access only</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= htmlspecialchars($booking->NumberOfGuests) ?> guest(s)</span>
                        </td>
                        <td>
                            <?php if (!empty($booking->TotalAmount)): ?>
                                <strong class="text-success">₱<?= number_format($booking->TotalAmount, 2) ?></strong>
                                <?php if (!empty($booking->RemainingBalance) && $booking->RemainingBalance > 0): ?>
                                    <br><small class="text-warning">Balance: ₱<?= number_format($booking->RemainingBalance, 2) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                                $statusColors = [
                                    'Pending' => 'bg-warning text-dark',
                                    'Confirmed' => 'bg-success',
                                    'Cancelled' => 'bg-danger',
                                    'Completed' => 'bg-primary'
                                ];
                                $statusClass = $statusColors[$booking->Status] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($booking->Status) ?></span>
                        </td>
                        <td>
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                <?php if ($booking->Status === 'Completed'): ?>
                                    <?php if ($booking->hasFeedback): ?>
                                        <span class="badge bg-secondary">Feedback Submitted</span>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-success btn-sm feedback-btn" data-bs-toggle="modal" data-bs-target="#feedbackModal" data-booking-id="<?= htmlspecialchars($booking->BookingID) ?>" data-booking-date="<?= htmlspecialchars(date('F j, Y', strtotime($booking->BookingDate))) ?>" data-resort-name="<?= htmlspecialchars($booking->ResortName ?? 'Unknown Resort') ?>">
                                            <i class="fas fa-star"></i> Leave Feedback
                                        </button>
                                    <?php endif; ?>
                                <?php elseif ($booking->Status === 'Cancelled'): ?>
                                    <span class="text-muted small">No actions available</span>
                                <?php else: ?>
                                    <!-- Payment Actions -->
                                    <?php if (!empty($booking->RemainingBalance) && $booking->RemainingBalance > 0): ?>
                                        <button type="button" class="btn btn-primary btn-sm mb-1 payment-modal-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#paymentModal"
                                                data-booking-id="<?= htmlspecialchars($booking->BookingID) ?>"
                                                data-booking-date="<?= htmlspecialchars(date('F j, Y', strtotime($booking->BookingDate))) ?>"
                                                data-total-amount="<?= htmlspecialchars($booking->TotalAmount) ?>"
                                                data-remaining-balance="<?= htmlspecialchars($booking->RemainingBalance) ?>"
                                                data-resort-name="<?= htmlspecialchars($booking->ResortName ?? 'Unknown Resort') ?>">
                                            <i class="fas fa-credit-card"></i>
                                            <?php if ($booking->RemainingBalance < $booking->TotalAmount): ?>
                                                Complete Payment
                                            <?php else: ?>
                                                Submit Payment
                                            <?php endif; ?>
                                        </button>
                                    <?php elseif ($booking->Status === 'Pending'): ?>
                                        <span class="badge bg-warning text-dark">Payment Under Review</span>
                                    <?php endif; ?>
                                    
                                    <!-- Cancel Action -->
                                    <?php if ($booking->Status === 'Pending' && $booking->RemainingBalance >= $booking->TotalAmount): ?>
                                    <a href="?controller=booking&action=cancelBooking&id=<?= htmlspecialchars($booking->BookingID) ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this booking?');">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
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
                <p>Your feedback is important to us. Please rate your experience for your booking at <strong id="modalResortName"></strong> on <strong id="modalBookingDate"></strong>.</p>
                <form id="feedbackForm" action="?controller=feedback&action=submitFeedback" method="POST">
                    <input type="hidden" name="bookingId" id="modalBookingId">
                    
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

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-credit-card"></i> Submit Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Booking Summary -->
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary-subtle">
                        <h6 class="mb-0 text-primary">
                            <i class="fas fa-receipt"></i> Booking Summary - <span id="paymentResortName"></span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Booking Details</h6>
                                <p><strong>Date:</strong> <span id="paymentBookingDate"></span></p>
                                <p><strong>Status:</strong> <span class="badge bg-warning text-dark">Pending</span></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Payment Information</h6>
                                <p><strong>Total Amount:</strong> <span id="paymentTotalAmount" class="fw-bold text-success"></span></p>
                                <p><strong>Remaining Balance:</strong> <span id="paymentRemainingBalance" class="fw-bold text-danger"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div id="paymentMethodsSection">
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success-subtle">
                            <h6 class="mb-0 text-success">
                                <i class="fas fa-credit-card"></i> Available Payment Methods
                            </h6>
                        </div>
                        <div class="card-body" id="paymentMethodsList">
                            <!-- Payment methods will be loaded here -->
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading payment methods...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <form id="paymentForm" action="?controller=booking&action=submitPayment" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="booking_id" id="paymentBookingId">

                    <!-- Amount Being Paid -->
                    <div class="mb-3">
                        <label for="modalAmountPaid" class="form-label fw-bold">
                            <i class="fas fa-peso-sign text-primary"></i> Amount Being Paid <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-peso-sign"></i></span>
                            <input type="number" class="form-control" id="modalAmountPaid" name="amount_paid" min="1" step="0.01" required>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> You can pay the full amount or make a partial payment.
                        </div>
                        <div class="mt-2 d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-success btn-sm" id="payFullBtn">
                                <i class="fas fa-money-bill"></i> Pay Full Amount
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="payHalfBtn">
                                <i class="fas fa-percentage"></i> Pay 50%
                            </button>
                        </div>
                    </div>

                    <!-- Payment Reference -->
                    <div class="mb-3">
                        <label for="modalPaymentReference" class="form-label fw-bold">
                            <i class="fas fa-hashtag text-primary"></i> Payment Reference Number <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                            <input type="text" class="form-control" id="modalPaymentReference" name="payment_reference" required>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> Enter the transaction ID, reference number, or confirmation code from your payment.
                        </div>
                    </div>

                    <!-- Payment Proof Upload -->
                    <div class="mb-3">
                        <label for="modalPaymentProof" class="form-label fw-bold">
                            <i class="fas fa-camera text-primary"></i> Payment Proof <span class="text-danger">*</span>
                        </label>

                        <div class="upload-area border-2 border-dashed rounded-3 p-3 text-center" id="modalUploadArea">
                            <div class="upload-content">
                                <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                                <p class="mb-2"><strong>Drop your payment proof here</strong></p>
                                <p class="text-muted small mb-2">or click to browse files</p>
                                <input type="file" class="form-control d-none" id="modalPaymentProof" name="payment_proof" accept="image/*" required>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('modalPaymentProof').click()">
                                    <i class="fas fa-folder-open"></i> Browse Files
                                </button>
                            </div>
                        </div>

                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle"></i> Upload a screenshot or photo of your payment confirmation (JPG, PNG, GIF | Max 5MB)
                        </div>

                        <div id="modalImagePreview" class="mt-2" style="display: none;">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <img id="modalPreviewImg" src="" alt="Payment proof preview" class="img-fluid rounded" style="max-height: 200px;">
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeModalImage()">
                                            <i class="fas fa-trash"></i> Remove Image
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Important Notice -->
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Important Notice</h6>
                        <ul class="mb-0 small">
                            <li>Your booking will be confirmed only after payment verification</li>
                            <li>Please ensure the payment proof clearly shows the transaction amount and reference number</li>
                            <li>Verification may take up to 24 hours</li>
                        </ul>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="modalSubmitBtn" disabled>
                            <span id="modalSubmitText">
                                <i class="fas fa-paper-plane"></i> Submit Payment Proof
                            </span>
                            <span id="modalLoadingText" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Submitting...
                            </span>
                        </button>
                    </div>
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
// Admin contact information
const adminPhone = '<?= htmlspecialchars($adminContact['PhoneNumber'] ?? '') ?>';
const adminEmail = '<?= htmlspecialchars($adminContact['Email'] ?? '') ?>';

document.addEventListener('DOMContentLoaded', function () {
    // Feedback Modal Handler
    var feedbackModal = document.getElementById('feedbackModal');
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

        // Clear previous facility feedback forms
        facilityFeedbackSection.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

        // Fetch and display facility feedback forms
        fetch('?controller=booking&action=getFacilitiesForBooking&booking_id=' + bookingId)
            .then(response => response.json())
            .then(facilities => {
                facilityFeedbackSection.innerHTML = ''; // Clear spinner
                if (facilities.length > 0) {
                    var facilityHtml = '<h5>Optional Facilities Feedback</h5>';
                    facilities.forEach(facility => {
                        facilityHtml += `
                            <div class="feedback-section mb-3 p-3 border rounded">
                                <h6>${facility.Name}</h6>
                                <input type="hidden" name="facilities[${facility.FacilityID}][id]" value="${facility.FacilityID}">
                                <div class="mb-3">
                                    <label for="facility_rating_${facility.FacilityID}" class="form-label"><strong>Rating (1 to 5)</strong></label>
                                    <div class="rating-stars">
                                        <input type="radio" id="facility_star_${facility.FacilityID}_5" name="facilities[${facility.FacilityID}][rating]" value="5" required><label for="facility_star_${facility.FacilityID}_5">&starf;</label>
                                        <input type="radio" id="facility_star_${facility.FacilityID}_4" name="facilities[${facility.FacilityID}][rating]" value="4" required><label for="facility_star_${facility.FacilityID}_4">&starf;</label>
                                        <input type="radio" id="facility_star_${facility.FacilityID}_3" name="facilities[${facility.FacilityID}][rating]" value="3" required><label for="facility_star_${facility.FacilityID}_3">&starf;</label>
                                        <input type="radio" id="facility_star_${facility.FacilityID}_2" name="facilities[${facility.FacilityID}][rating]" value="2" required><label for="facility_star_${facility.FacilityID}_2">&starf;</label>
                                        <input type="radio" id="facility_star_${facility.FacilityID}_1" name="facilities[${facility.FacilityID}][rating]" value="1" required><label for="facility_star_${facility.FacilityID}_1">&starf;</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="facility_comment_${facility.FacilityID}" class="form-label"><strong>Comments (Optional)</strong></label>
                                    <textarea class="form-control" id="facility_comment_${facility.FacilityID}" name="facilities[${facility.FacilityID}][comment]" rows="2" placeholder="Feedback for ${facility.Name}..."></textarea>
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

    // Payment Modal Handler
    var paymentModal = document.getElementById('paymentModal');
    paymentModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var bookingId = button.getAttribute('data-booking-id');
        var bookingDate = button.getAttribute('data-booking-date');
        var totalAmount = parseFloat(button.getAttribute('data-total-amount'));
        var remainingBalance = parseFloat(button.getAttribute('data-remaining-balance'));
        var resortName = button.getAttribute('data-resort-name');

        // Populate modal data
        document.getElementById('paymentBookingId').value = bookingId;
        document.getElementById('paymentBookingDate').textContent = bookingDate;
        document.getElementById('paymentTotalAmount').textContent = '₱' + totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('paymentRemainingBalance').textContent = '₱' + remainingBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('paymentResortName').textContent = resortName;

        // Set input constraints
        var amountInput = document.getElementById('modalAmountPaid');
        amountInput.max = remainingBalance;
        amountInput.value = remainingBalance; // Default to remaining balance

        // Load payment methods
        loadPaymentMethods(bookingId, resortName);

        // Reset form validation
        validateModalForm();
    });

    // Reset modal when hidden
    paymentModal.addEventListener('hidden.bs.modal', function () {
        // Reset form
        document.getElementById('paymentForm').reset();
        document.getElementById('modalImagePreview').style.display = 'none';
        document.getElementById('modalUploadArea').style.display = 'block';

        // Reset validation
        document.getElementById('modalSubmitBtn').disabled = true;
    });

    // Load payment methods via AJAX
    function loadPaymentMethods(bookingId, resortName) {
        // First get resort ID from booking (we need to make a request to get resort info)
        fetch('?controller=booking&action=getPaymentMethods&booking_id=' + bookingId, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            displayPaymentMethods(data);
        })
        .catch(error => {
            console.error('Error loading payment methods:', error);
            document.getElementById('paymentMethodsList').innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Unable to load payment methods. Please contact the resort directly at <strong>' + resortName + '</strong> for payment instructions.</div>';
        });
    }

    // Display payment methods in modal
    function displayPaymentMethods(methods) {
        var container = document.getElementById('paymentMethodsList');
        var paymentForm = document.getElementById('paymentForm');
        var modalSubmitBtn = document.getElementById('modalSubmitBtn');

        if (!methods || methods.length === 0) {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h6 class="fw-bold"><i class="fas fa-times-circle"></i> Payment Not Available</h6>
                    <p class="mb-2">This resort has not configured any payment methods yet.</p>
                    <p class="mb-3"><strong>Please contact the resort directly to arrange payment outside of this system.</strong></p>
                    <div class="d-flex gap-2">
                        <a href="tel:${adminPhone}" class="btn btn-outline-primary btn-sm" id="callResortBtn">
                            <i class="fas fa-phone"></i> Phone
                        </a>
                        <a href="mailto:${adminEmail}" class="btn btn-outline-primary btn-sm" id="emailResortBtn">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                    </div>
                </div>
            `;

            // Disable all form fields and submit button
            var formElements = paymentForm.querySelectorAll('input, button[type="submit"]');
            formElements.forEach(function(element) {
                element.disabled = true;
            });
            modalSubmitBtn.disabled = true;

            // Add disabled styling
            paymentForm.classList.add('opacity-50');
            paymentForm.style.pointerEvents = 'none';

            return;
        }

        var html = '<div class="row">';
        methods.forEach(function(method) {
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-success shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                <i class="fas fa-mobile-alt fa-2x text-success"></i>
                            </div>
                            <h6 class="card-title text-success fw-bold">${method.name}</h6>
                            <p class="card-text small text-muted">${method.details}</p>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    // Quick pay buttons
    document.getElementById('payFullBtn').addEventListener('click', function() {
        var remainingBalance = parseFloat(document.getElementById('modalAmountPaid').max);
        document.getElementById('modalAmountPaid').value = remainingBalance;
        document.getElementById('modalAmountPaid').classList.add('pulse');
        setTimeout(() => document.getElementById('modalAmountPaid').classList.remove('pulse'), 1000);
        validateModalForm();
    });

    document.getElementById('payHalfBtn').addEventListener('click', function() {
        var remainingBalance = parseFloat(document.getElementById('modalAmountPaid').max);
        var halfAmount = remainingBalance / 2;
        document.getElementById('modalAmountPaid').value = halfAmount.toFixed(2);
        document.getElementById('modalAmountPaid').classList.add('pulse');
        setTimeout(() => document.getElementById('modalAmountPaid').classList.remove('pulse'), 1000);
        validateModalForm();
    });

    // File upload handling
    var modalUploadArea = document.getElementById('modalUploadArea');
    var modalFileInput = document.getElementById('modalPaymentProof');
    var modalPreview = document.getElementById('modalImagePreview');
    var modalPreviewImg = document.getElementById('modalPreviewImg');

    // Click to upload
    modalUploadArea.addEventListener('click', function(e) {
        if (e.target !== modalFileInput) {
            modalFileInput.click();
        }
    });

    // Drag and drop
    modalUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        modalUploadArea.classList.add('dragover');
    });

    modalUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        modalUploadArea.classList.remove('dragover');
    });

    modalUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        modalUploadArea.classList.remove('dragover');

        var files = e.dataTransfer.files;
        if (files.length > 0) {
            modalFileInput.files = files;
            handleModalFileSelection(files[0]);
        }
    });

    // File input change
    modalFileInput.addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            handleModalFileSelection(file);
        } else {
            modalPreview.style.display = 'none';
            modalUploadArea.style.display = 'block';
        }
    });

    function handleModalFileSelection(file, retryCount = 0) {
        const maxRetries = 3;
        const retryDelay = 100; // milliseconds

        // Validate file name extension as fallback (immediate check)
        const fileName = file.name.toLowerCase();
        const validExtensions = /\.(jpg|jpeg|png|gif|webp)$/;
        const hasValidExtension = validExtensions.test(fileName);

        if (!hasValidExtension) {
            showModalAlert('Please select an image file (JPG, PNG, GIF, WebP)', 'danger');
            modalFileInput.value = '';
            return;
        }

        // Check if file metadata is available
        if (!file.type || file.size === 0) {
            // File metadata not ready yet, retry after delay
            if (retryCount < maxRetries) {
                setTimeout(() => {
                    handleModalFileSelection(file, retryCount + 1);
                }, retryDelay);
                return;
            } else {
                // Max retries reached, use extension-based validation
                if (hasValidExtension) {
                    // Assume valid since extension is valid, size might be 0 due to browser limitations
                    proceedWithFileProcessing(file);
                } else {
                    showModalAlert('Unable to validate file. Please try selecting the file again.', 'danger');
                    modalFileInput.value = '';
                }
                return;
            }
        }

        // Validate file type once metadata is available
        if (!file.type.startsWith('image/')) {
            showModalAlert('Please select an image file (JPG, PNG, GIF, WebP)', 'danger');
            modalFileInput.value = '';
            return;
        }

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            showModalAlert('File size must be less than 5MB', 'danger');
            modalFileInput.value = '';
            return;
        }

        // All validations passed, proceed
        proceedWithFileProcessing(file);
    }

    function proceedWithFileProcessing(file) {
        // Show preview
        var reader = new FileReader();
        reader.onload = function(e) {
            modalPreviewImg.src = e.target.result;
            modalPreview.style.display = 'block';
            modalUploadArea.style.display = 'none';
            validateModalForm();
            showModalAlert('Image uploaded successfully!', 'success');
        };
        reader.onerror = function() {
            showModalAlert('Error reading the selected file. Please try again.', 'danger');
            modalFileInput.value = '';
        };
        reader.readAsDataURL(file);
    }

    // Remove image function
    window.removeModalImage = function() {
        modalFileInput.value = '';
        modalPreview.style.display = 'none';
        modalUploadArea.style.display = 'block';
        validateModalForm();
    };

    // Form validation
    function validateModalForm() {
        var amount = parseFloat(document.getElementById('modalAmountPaid').value) || 0;
        var reference = document.getElementById('modalPaymentReference').value.trim();
        var file = modalFileInput.files.length > 0;
        var maxAmount = parseFloat(document.getElementById('modalAmountPaid').max);

        var isValid = amount > 0 && amount <= maxAmount && reference.length > 0 && file;
        document.getElementById('modalSubmitBtn').disabled = !isValid;
    }

    // Add validation listeners
    document.getElementById('modalAmountPaid').addEventListener('input', validateModalForm);
    document.getElementById('modalPaymentReference').addEventListener('input', validateModalForm);

    // Form submission
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        var amount = parseFloat(document.getElementById('modalAmountPaid').value);
        var maxAmount = parseFloat(document.getElementById('modalAmountPaid').max);

        if (amount <= 0 || amount > maxAmount) {
            e.preventDefault();
            showModalAlert('Please enter a valid payment amount between ₱1.00 and ₱' + maxAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}), 'danger');
            return false;
        }

        // Show loading state
        document.getElementById('modalSubmitText').style.display = 'none';
        document.getElementById('modalLoadingText').style.display = 'inline';

        // Confirm submission
        if (!confirm('Are you sure you want to submit this payment?\n\n' +
                    'Amount: ₱' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '\n' +
                    'Reference: ' + document.getElementById('modalPaymentReference').value + '\n\n' +
                    'Please ensure all information is correct.')) {
            e.preventDefault();
            document.getElementById('modalSubmitText').style.display = 'inline';
            document.getElementById('modalLoadingText').style.display = 'none';
            return false;
        }

        showModalAlert('Submitting payment proof...', 'info');
    });

    // Modal alert function
    function showModalAlert(message, type) {
        // Remove existing alerts
        var existingAlerts = paymentModal.querySelectorAll('.alert');
        existingAlerts.forEach(function(alert) {
            alert.remove();
        });

        // Create new alert
        var alert = document.createElement('div');
        alert.className = 'alert alert-' + type + ' alert-dismissible fade show';
        alert.style.cssText = 'position: absolute; top: 10px; left: 10px; right: 10px; z-index: 9999;';
        alert.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';

        paymentModal.querySelector('.modal-body').prepend(alert);

        // Auto remove after 3 seconds
        if (type !== 'danger') {
            setTimeout(function() {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 3000);
        }
    }
});

// Add some CSS styles for the modal
document.head.insertAdjacentHTML('beforeend', `
<style>
.upload-area {
    transition: all 0.3s ease;
    background-color: #f8f9fa;
    border-color: #dee2e6 !important;
    cursor: pointer;
}

.upload-area:hover, .upload-area.dragover {
    background-color: #e3f2fd;
    border-color: #0d6efd !important;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.pulse {
    animation: pulse 2s infinite;
}

.form-control:focus, .form-select:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.25);
}
</style>
`);
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
