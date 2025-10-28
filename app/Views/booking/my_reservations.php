<?php
$pageTitle = "My Reservations";
require_once __DIR__ . '/../partials/header.php';
?>

<h1><?= htmlspecialchars($pageTitle) ?></h1>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <?php unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <?php unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<?php if (empty($activeBookings)): ?>
    <div class="alert alert-info" role="alert">
        You have no pending reservations. <a href="?controller=booking&action=showBookingForm">Make a booking now!</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Creation Date</th>
                    <th>Resort</th>
                    <th>Date & Time</th>
                    <th>Facilities</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Time Left</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activeBookings as $booking): ?>
                    <tr>
                        <td>
                            <div><strong><?= htmlspecialchars(date('F j, Y', strtotime($booking->CreatedAt))) ?></strong></div>
                            <small class="text-muted"><?= htmlspecialchars(date('g:i A', strtotime($booking->CreatedAt))) ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($booking->ResortName ?? 'Unknown Resort') ?></strong>
                        </td>
                        <td>
                            <div><strong><?= htmlspecialchars(date('F j, Y', strtotime($booking->BookingDate))) ?></strong></div>
                            <small class="text-muted">
                                <?php
                                    $timeSlotDisplay = [
                                        '12_hours' => '12 Hours - Check In: 7:00 AM Check Out: 5:00 PM',
                                        '24_hours' => '24 Hours - Check In: 7:00 AM Check Out: 5:00 AM Next Day',
                                        'overnight' => 'Overnight - Check In: 7:00 PM Check Out: 5:00 AM'
                                    ];
                                    echo htmlspecialchars($timeSlotDisplay[$booking->TimeSlotType] ?? 'N/A');
                                ?>
                            </small>
                        </td>
                        <td>
                            <?php if (!empty($booking->FacilityNames)): ?>
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($booking->FacilityNames) ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Resort access only</span>
                            <?php endif; ?>
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
                                    'Cancelled' => 'bg-danger',
                                ];
                                $statusClass = $statusColors[$booking->Status] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($booking->Status) ?></span>
                        </td>
                        <td>
                            <?php
                                // Case 1: Timer should be running.
                                if ($booking->Status === 'Pending' && !empty($booking->ExpiresAt) && new DateTime($booking->ExpiresAt) > new DateTime()) {
                                    echo '<div class="countdown-timer" data-expires-at="' . htmlspecialchars($booking->ExpiresAt) . '"></div>';
                                }
                                // Case 2: Payment submitted, reservation is secured from expiring.
                                elseif ($booking->Status === 'Pending' && empty($booking->ExpiresAt)) {
                                    echo '<span class="badge bg-success">Secured</span>';
                                }
                                // Case 3: Timer has expired (JS will also handle this, but good for server-side).
                                elseif ($booking->Status === 'Pending' && !empty($booking->ExpiresAt) && new DateTime($booking->ExpiresAt) <= new DateTime()) {
                                    echo '<span class="badge bg-danger">Expired</span>';
                                }
                                // Case 4: Booking is cancelled.
                                elseif ($booking->Status === 'Cancelled') {
                                    echo '<span class="badge bg-secondary">N/A</span>';
                                }
                                // Fallback
                                else {
                                    echo '-';
                                }
                            ?>
                        </td>
                        <td>
                            <div class="btn-group-vertical btn-group-sm" role="group">
                                    <!-- Payment Actions -->
                                    <?php if (!empty($booking->RemainingBalance) && $booking->RemainingBalance > 0 && $booking->Status !== 'Cancelled'): ?>
                                        <button type="button" class="btn btn-primary btn-sm mb-1 payment-modal-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#paymentModal"
                                                data-booking-id="<?= htmlspecialchars($booking->BookingID) ?>"
                                                data-booking-date="<?= htmlspecialchars(date('F j, Y', strtotime($booking->BookingDate))) ?>"
                                                data-total-amount="<?= htmlspecialchars($booking->TotalAmount) ?>"
                                                data-remaining-balance="<?= htmlspecialchars($booking->RemainingBalance) ?>"
                                                data-resort-name="<?= htmlspecialchars($booking->ResortName ?? 'Unknown Resort') ?>"
                                                data-facility-names="<?= htmlspecialchars($booking->FacilityNames ?? 'Resort access only') ?>">
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

                                    <?php if ($booking->Status === 'Cancelled'): ?>
                                    <span class="badge bg-secondary">Reservation Cancelled</span>
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
                                <p><strong>Facilities:</strong> <span id="paymentFacilityNames" class="badge bg-info text-dark"></span></p>
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
                    <input type="hidden" name="payment_method" id="selectedPaymentMethod">

                    <div id="paymentFormFields" style="display: none;">
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
                                    <i class="fas fa-money-bill"></i> Full Amount
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="payHalfBtn">
                                    <i class="fas fa-percentage"></i> Half Amount
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

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Payment Modal Handler
    var paymentModal = document.getElementById('paymentModal');
    paymentModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var bookingId = button.getAttribute('data-booking-id');
        var bookingDate = button.getAttribute('data-booking-date');
        var totalAmount = parseFloat(button.getAttribute('data-total-amount'));
        var remainingBalance = parseFloat(button.getAttribute('data-remaining-balance'));
        var resortName = button.getAttribute('data-resort-name');
        var facilityNames = button.getAttribute('data-facility-names');

        // Populate modal data
        document.getElementById('paymentBookingId').value = bookingId;
        document.getElementById('paymentBookingDate').textContent = bookingDate;
        document.getElementById('paymentTotalAmount').textContent = '₱' + totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('paymentRemainingBalance').textContent = '₱' + remainingBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('paymentResortName').textContent = resortName;

        // Handle facility names display and styling
        var facilityBadge = document.getElementById('paymentFacilityNames');
        facilityBadge.textContent = facilityNames;
        facilityBadge.classList.remove('bg-info', 'text-dark', 'bg-secondary'); // Reset classes

        if (facilityNames === 'Resort access only') {
            facilityBadge.classList.add('bg-secondary');
        } else {
            facilityBadge.classList.add('bg-info', 'text-dark');
        }

        // Set input constraints
        var amountInput = document.getElementById('modalAmountPaid');
        amountInput.max = remainingBalance;
        amountInput.value = remainingBalance; // Default to remaining balance

        // Load payment methods
        loadPaymentMethods(bookingId, resortName);
        
        // Hide the main form fields initially
        document.getElementById('paymentFormFields').style.display = 'none';

        // Reset form validation
        validateModalForm();
    });

    // Reset modal when hidden
    paymentModal.addEventListener('hidden.bs.modal', function () {
        // Reset form
        document.getElementById('paymentForm').reset();
        document.getElementById('modalImagePreview').style.display = 'none';
        document.getElementById('modalUploadArea').style.display = 'block';
        document.getElementById('paymentFormFields').style.display = 'none';

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
                </div>
            `;
            paymentForm.style.display = 'none'; // Hide the form completely
            return;
        }

        var html = '<div class="row justify-content-center">'; // Center the columns if there's only one
        methods.forEach(function(method, index) {
            const qrCodeHtml = method.qrCodeUrl ?
                `<div class="qr-code-container text-center mb-3">
                    <img src="${method.qrCodeUrl}" alt="${method.name} QR Code" class="img-fluid rounded" style="max-height: 220px; height: auto; width: 100%; object-fit: contain;">
                    <a href="${method.qrCodeUrl}" class="btn btn-sm btn-outline-secondary mt-2" download><i class="fas fa-download"></i> Download QR</a>
                </div>` :
                `<div class="qr-code-container text-center mb-3 p-5 border rounded bg-light">
                    <i class="fas fa-qrcode fa-3x text-muted"></i>
                    <p class="mt-2 text-muted small">QR Code Not Available</p>
                </div>`;

            html += `
                <div class="col-md-6 mb-3">
                    <label for="payment_method_option_${index}" class="card h-100 payment-method-card" style="cursor: pointer;">
                        <div class="card-body d-flex flex-column text-center">
                            <div class="form-check mb-2">
                                <input type="radio" name="payment_method_option" value="${method.name}" id="payment_method_option_${index}" class="form-check-input" required>
                                <label class="form-check-label" for="payment_method_option_${index}">
                                    <h5 class="card-title text-success fw-bold">${method.name}</h5>
                                </label>
                            </div>
                            
                            ${qrCodeHtml}
                            
                            <div class="payment-details mt-auto">
                                <p class="card-text mb-1"><strong>Account Name:</strong><br>${method.accountName}</p>
                                <p class="card-text"><strong>Account Number:</strong><br>${method.accountNumber}</p>
                            </div>
                        </div>
                    </label>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;

        // Add event listeners to the radio buttons
        var paymentRadios = container.querySelectorAll('input[name="payment_method_option"]');
        paymentRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    document.getElementById('selectedPaymentMethod').value = this.value;
                    document.getElementById('paymentFormFields').style.display = 'block';
                    document.querySelectorAll('.payment-method-card').forEach(card => card.classList.remove('border-primary', 'border-3'));
                    this.closest('.payment-method-card').classList.add('border-primary', 'border-3');
                    validateModalForm();
                }
            });
        });
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

    function handleModalFileSelection(file) {
        // Immediately check for a file
        if (!file) {
            removeModalImage(); // Reset if no file is selected
            return;
        }

        const validExtensions = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const maxFileSize = 5 * 1024 * 1024; // 5MB

        // --- Step 1: Basic file validation (Client-side) ---
        // Validate file type based on MIME type for better accuracy
        if (!validExtensions.includes(file.type)) {
            showModalAlert('Invalid file type. Please select a JPG, PNG, GIF, or WebP image.', 'danger');
            removeModalImage();
            return;
        }

        // Validate file size
        if (file.size > maxFileSize) {
            showModalAlert(`File is too large (${(file.size / 1024 / 1024).toFixed(2)}MB). Maximum size is 5MB.`, 'danger');
            removeModalImage();
            return;
        }

        // --- Step 2: Advanced validation using FileReader and Image object ---
        const reader = new FileReader();

        // Show a loading/processing state to the user
        showModalAlert('Validating image...', 'info');

        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                // This confirms the file is a valid, renderable image
                // All validations passed, proceed to show preview
                modalPreviewImg.src = e.target.result;
                modalPreview.style.display = 'block';
                modalUploadArea.style.display = 'none';
                validateModalForm();
                showModalAlert('Image accepted and ready for upload!', 'success');
            };

            img.onerror = function() {
                // This triggers if the file data is corrupted or not a real image
                showModalAlert('The selected file is not a valid or supported image. Please try another file.', 'danger');
                removeModalImage();
            };

            // Set the src to trigger the image load/error events
            img.src = e.target.result;
        };

        reader.onerror = function() {
            // This triggers if the browser cannot read the file at all
            showModalAlert('There was an error reading the file. Please try selecting it again.', 'danger');
            removeModalImage();
        };

        // Start reading the file
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
        var paymentMethodSelected = document.getElementById('selectedPaymentMethod').value;

        var isValid = paymentMethodSelected && amount > 0 && amount <= maxAmount && reference.length > 0 && file;
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

    // Countdown Timer Logic
    function initializeCountdown() {
        const timers = document.querySelectorAll('.countdown-timer');
        timers.forEach(timer => {
            const expiresAt = new Date(timer.dataset.expiresAt + 'Z').getTime(); // Assume UTC

            const interval = setInterval(() => {
                const now = new Date().getTime();
                const distance = expiresAt - now;

                if (distance < 0) {
                    clearInterval(interval);
                    timer.innerHTML = '<span class="badge bg-danger">Expired</span>';
                    // Optionally, find the associated row and update its status visually or reload
                    // For example, disable the payment button
                    const row = timer.closest('tr');
                    if (row) {
                        const paymentButton = row.querySelector('.payment-modal-btn');
                        if (paymentButton) {
                            paymentButton.disabled = true;
                            paymentButton.innerHTML = '<i class="fas fa-credit-card"></i> Expired';
                        }
                         const cancelButton = row.querySelector('a[href*="cancelBooking"]');
                        if (cancelButton) {
                            cancelButton.remove();
                        }
                    }
                    return;
                }

                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                timer.innerHTML = `<span class="badge bg-info text-dark">${hours}h ${minutes}m ${seconds}s</span>`;
            }, 1000);
        });
    }

    initializeCountdown();
});
</script>
