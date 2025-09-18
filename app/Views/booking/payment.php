<?php
$pageTitle = "Submit Payment";
require_once __DIR__ . '/../partials/header.php';
?>

        <h1><i class="fas fa-credit-card"></i> <?= htmlspecialchars($pageTitle) ?></h1>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <!-- Booking Summary Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-receipt"></i> Booking Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Booking Details</h6>
                        <p><strong>Resort:</strong> <?= htmlspecialchars($resort->name ?? 'N/A') ?></p>
                        <p><strong>Date:</strong> <?= date('F j, Y', strtotime($booking->bookingDate)) ?></p>
                        <p><strong>Timeframe:</strong> <?= htmlspecialchars(Booking::getTimeSlotDisplay($booking->timeSlotType)) ?></p>
                        <p><strong>Guests:</strong> <?= $booking->numberOfGuests ?> person<?= $booking->numberOfGuests > 1 ? 's' : '' ?></p>
                        <?php if (!empty($facilities)): ?>
                            <p><strong>Facilities:</strong>
                                <?php foreach ($facilities as $index => $facility): ?>
                                    <?= htmlspecialchars($facility->Name) ?><?= $index < count($facilities) - 1 ? ', ' : '' ?>
                                <?php endforeach; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Payment Information</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Amount:</span>
                            <span class="fw-bold">₱<?= number_format($booking->totalAmount, 2) ?></span>
                        </div>
                        <?php if ($booking->remainingBalance < $booking->totalAmount): ?>
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Amount Paid:</span>
                                <span>₱<?= number_format($booking->totalAmount - $booking->remainingBalance, 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Remaining Balance:</span>
                            <span class="fw-bold text-danger">₱<?= number_format($booking->remainingBalance, 2) ?></span>
                        </div>
                        
                        <?php if ($booking->remainingBalance <= 0): ?>
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle"></i> <strong>Fully Paid</strong><br>
                                This booking has been paid in full.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> <strong>Payment Required</strong><br>
                                You can pay the full amount or make a partial payment.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($booking->remainingBalance > 0): ?>
        <!-- Payment Methods Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-money-bill"></i> Available Payment Methods</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($paymentMethods)): ?>
                    <div class="row">
                        <?php foreach ($paymentMethods as $method): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-secondary">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-primary"><?= htmlspecialchars($method->MethodName) ?></h6>
                                        <p class="card-text small text-muted"><?= htmlspecialchars($method->MethodDetails) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No payment methods configured for this resort. Please contact the resort directly.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment Submission Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-upload"></i> Submit Payment Proof</h5>
            </div>
            <div class="card-body">
                <form action="?controller=booking&action=submitPayment" method="POST" enctype="multipart/form-data" id="paymentForm">
                    <input type="hidden" name="booking_id" value="<?= $booking->bookingId ?>">
                    
                    <!-- Amount Being Paid -->
                    <div class="mb-4">
                        <label for="amount_paid" class="form-label fw-bold">Amount Being Paid <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="amount_paid" name="amount_paid"
                                   min="1" max="<?= $booking->remainingBalance ?>" step="0.01" required
                                   placeholder="Enter amount">
                        </div>
                        <div class="form-text">
                            You can pay between ₱1.00 and ₱<?= number_format($booking->remainingBalance, 2) ?>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="setFullAmount()">
                                Pay Full Amount (₱<?= number_format($booking->remainingBalance, 2) ?>)
                            </button>
                        </div>
                    </div>

                    <!-- Payment Reference -->
                    <div class="mb-4">
                        <label for="payment_reference" class="form-label fw-bold">Payment Reference Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="payment_reference" name="payment_reference" required
                               placeholder="Enter transaction/reference number">
                        <div class="form-text">
                            Enter the transaction ID, reference number, or confirmation code from your payment.
                        </div>
                    </div>

                    <!-- Payment Proof Upload -->
                    <div class="mb-4">
                        <label for="payment_proof" class="form-label fw-bold">Payment Proof <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="payment_proof" name="payment_proof" 
                               accept="image/*" required>
                        <div class="form-text">
                            Upload a screenshot or photo of your payment confirmation. Accepted formats: JPG, PNG, GIF. Max size: 5MB.
                        </div>
                        <!-- Preview area -->
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <img id="previewImg" src="" alt="Payment proof preview" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>

                    <!-- Important Notice -->
                    <div class="alert alert-warning mb-4">
                        <h6><i class="fas fa-exclamation-triangle"></i> Important Notice</h6>
                        <ul class="mb-0">
                            <li>Your booking will be confirmed only after payment verification</li>
                            <li>Please ensure the payment proof clearly shows the transaction amount and reference number</li>
                            <li>Verification may take up to 24 hours</li>
                            <li>You will receive an email notification once your payment is verified</li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                            <i class="fas fa-paper-plane"></i> Submit Payment Proof
                        </button>
                        <a href="?controller=booking&action=showMyBookings" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to My Bookings
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Already Paid -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4>Payment Complete!</h4>
                <p class="text-muted">This booking has been fully paid. No further payment is required.</p>
                <a href="?controller=booking&action=showMyBookings" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to My Bookings
                </a>
            </div>
        </div>
        <?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount_paid');
    const fileInput = document.getElementById('payment_proof');
    const previewDiv = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const form = document.getElementById('paymentForm');
    
    // Set full amount function
    window.setFullAmount = function() {
        amountInput.value = <?= $booking->remainingBalance ?>;
    };
    
    // File preview functionality
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file');
                    e.target.value = '';
                    previewDiv.style.display = 'none';
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    e.target.value = '';
                    previewDiv.style.display = 'none';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewDiv.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewDiv.style.display = 'none';
            }
        });
    }
    
    // Form validation
    if (form) {
        form.addEventListener('submit', function(e) {
            const amount = parseFloat(amountInput.value);
            const maxAmount = <?= $booking->remainingBalance ?>;
            
            if (amount <= 0 || amount > maxAmount) {
                e.preventDefault();
                alert('Please enter a valid payment amount between ₱1.00 and ₱' + maxAmount.toLocaleString());
                return false;
            }
            
            // Confirm submission
            if (!confirm('Are you sure you want to submit this payment? Please ensure all information is correct.')) {
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>