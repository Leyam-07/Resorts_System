<?php
$pageTitle = "Submit Payment";
require_once __DIR__ . '/../partials/header.php';
?>

        <!-- Enhanced Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="mb-1"><i class="fas fa-credit-card text-primary"></i> <?= htmlspecialchars($pageTitle) ?></h1>
                        <p class="text-muted mb-0">Complete your booking by submitting payment proof</p>
                    </div>
                    <div class="d-none d-md-block">
                        <i class="fas fa-money-check-alt fa-3x text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        
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
        
        <!-- Phase 6: Payment Schedule Section -->
        <?php
        // Get payment schedule for this booking
        require_once __DIR__ . '/../Models/PaymentSchedule.php';
        $paymentSchedule = PaymentSchedule::findByBookingId($booking->bookingId);
        $scheduleSummary = PaymentSchedule::getScheduleSummary($booking->bookingId);
        $nextPayment = PaymentSchedule::getNextPaymentDue($booking->bookingId);
        ?>
        
        <?php if (!empty($paymentSchedule)): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Payment Schedule</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Schedule Summary</h6>
                        <div class="d-flex justify-content-between">
                            <span>Total Installments:</span>
                            <span class="fw-bold"><?= $scheduleSummary->TotalInstallments ?? 0 ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Paid Amount:</span>
                            <span class="text-success fw-bold">₱<?= number_format($scheduleSummary->PaidAmount ?? 0, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Remaining Amount:</span>
                            <span class="text-danger fw-bold">₱<?= number_format($scheduleSummary->RemainingAmount ?? 0, 2) ?></span>
                        </div>
                        <?php if (isset($scheduleSummary->OverdueCount) && $scheduleSummary->OverdueCount > 0): ?>
                        <div class="d-flex justify-content-between">
                            <span>Overdue Payments:</span>
                            <span class="text-warning fw-bold"><?= $scheduleSummary->OverdueCount ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($nextPayment): ?>
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary-subtle text-primary">
                                <h6 class="mb-0"><i class="fas fa-arrow-right"></i> Next Payment Due</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Installment #:</span>
                                    <span class="fw-bold"><?= $nextPayment->InstallmentNumber ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Amount Due:</span>
                                    <span class="fw-bold text-primary">₱<?= number_format($nextPayment->Amount, 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Due Date:</span>
                                    <span class="fw-bold <?= strtotime($nextPayment->DueDate) < time() ? 'text-danger' : 'text-success' ?>">
                                        <?= date('M j, Y', strtotime($nextPayment->DueDate)) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Status:</span>
                                    <span class="badge <?= $nextPayment->Status === 'Overdue' ? 'bg-danger' : 'bg-warning' ?>">
                                        <?= $nextPayment->Status ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Payment Schedule Table -->
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead class="table-info">
                            <tr>
                                <th><i class="fas fa-hashtag"></i> #</th>
                                <th><i class="fas fa-calendar"></i> Due Date</th>
                                <th><i class="fas fa-peso-sign"></i> Amount</th>
                                <th><i class="fas fa-info-circle"></i> Status</th>
                                <th><i class="fas fa-receipt"></i> Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paymentSchedule as $schedule): ?>
                            <tr class="<?= $schedule->Status === 'Paid' ? 'table-success' : ($schedule->Status === 'Overdue' ? 'table-danger' : '') ?>">
                                <td class="fw-bold"><?= $schedule->InstallmentNumber ?></td>
                                <td>
                                    <?= date('M j, Y', strtotime($schedule->DueDate)) ?>
                                    <?php if (strtotime($schedule->DueDate) < time() && $schedule->Status !== 'Paid'): ?>
                                        <i class="fas fa-exclamation-triangle text-warning ms-1" title="Overdue"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold">₱<?= number_format($schedule->Amount, 2) ?></td>
                                <td>
                                    <span class="badge <?=
                                        $schedule->Status === 'Paid' ? 'bg-success' :
                                        ($schedule->Status === 'Overdue' ? 'bg-danger' :
                                        ($schedule->Status === 'Pending' ? 'bg-warning' : 'bg-secondary'))
                                    ?>">
                                        <?= $schedule->Status ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($schedule->PaymentID): ?>
                                        <small class="text-success">
                                            <i class="fas fa-check-circle"></i> Paid (ID: <?= $schedule->PaymentID ?>)
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> Awaiting payment
                                        </small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($nextPayment): ?>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-lightbulb"></i> <strong>Payment Tip:</strong>
                    You can pay the exact installment amount (₱<?= number_format($nextPayment->Amount, 2) ?>)
                    or pay more to advance your payment schedule.
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Enhanced Payment Methods Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-credit-card"></i> Available Payment Methods</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($paymentMethods)): ?>
                    <div class="row">
                        <?php foreach ($paymentMethods as $method): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-success shadow-sm payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="mb-2">
                                            <i class="fas fa-mobile-alt fa-2x text-success"></i>
                                        </div>
                                        <h6 class="card-title text-success fw-bold"><?= htmlspecialchars($method->MethodName) ?></h6>
                                        <p class="card-text small text-muted"><?= htmlspecialchars($method->MethodDetails) ?></p>
                                        <div class="badge bg-success-subtle text-success">
                                            <i class="fas fa-check"></i> Available
                                        </div>
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

        <!-- Enhanced Payment Submission Form -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-upload"></i> Submit Payment Proof</h5>
            </div>
            <div class="card-body">
                <form action="?controller=booking&action=submitPayment" method="POST" enctype="multipart/form-data" id="paymentForm">
                    <input type="hidden" name="booking_id" value="<?= $booking->bookingId ?>">
                    
                    <!-- Enhanced Amount Being Paid -->
                    <div class="mb-4">
                        <label for="amount_paid" class="form-label fw-bold">
                            <i class="fas fa-peso-sign text-primary"></i> Amount Being Paid <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white"><i class="fas fa-peso-sign"></i></span>
                            <input type="number" class="form-control" id="amount_paid" name="amount_paid"
                                   min="1" max="<?= $booking->remainingBalance ?>" step="0.01" required
                                   placeholder="Enter amount">
                            <span class="input-group-text">.00</span>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> You can pay between ₱1.00 and ₱<?= number_format($booking->remainingBalance, 2) ?>
                        </div>
                        <div class="mt-2 d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-success btn-sm" onclick="setFullAmount()">
                                <i class="fas fa-money-bill"></i> Pay Full Amount (₱<?= number_format($booking->remainingBalance, 2) ?>)
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="setHalfAmount()">
                                <i class="fas fa-percentage"></i> Pay 50% (₱<?= number_format($booking->remainingBalance / 2, 2) ?>)
                            </button>
                            <?php if ($nextPayment && $nextPayment->Amount <= $booking->remainingBalance): ?>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="setInstallmentAmount()">
                                <i class="fas fa-calendar-check"></i> Next Installment (₱<?= number_format($nextPayment->Amount, 2) ?>)
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Enhanced Payment Reference -->
                    <div class="mb-4">
                        <label for="payment_reference" class="form-label fw-bold">
                            <i class="fas fa-hashtag text-primary"></i> Payment Reference Number <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                            <input type="text" class="form-control" id="payment_reference" name="payment_reference" required
                                   placeholder="Enter transaction/reference number">
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> Enter the transaction ID, reference number, or confirmation code from your payment.
                        </div>
                    </div>

                    <!-- Enhanced Payment Proof Upload -->
                    <div class="mb-4">
                        <label for="payment_proof" class="form-label fw-bold">
                            <i class="fas fa-camera text-primary"></i> Payment Proof <span class="text-danger">*</span>
                        </label>
                        
                        <!-- Drag and Drop Upload Area -->
                        <div class="upload-area border-2 border-dashed rounded-3 p-4 text-center" id="uploadArea">
                            <div class="upload-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h5 class="text-primary">Drop your payment proof here</h5>
                                <p class="text-muted mb-3">or click to browse files</p>
                                <input type="file" class="form-control d-none" id="payment_proof" name="payment_proof"
                                       accept="image/*" required>
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('payment_proof').click()">
                                    <i class="fas fa-folder-open"></i> Browse Files
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle"></i> Upload a screenshot or photo of your payment confirmation.
                            <strong>Accepted:</strong> JPG, PNG, GIF | <strong>Max size:</strong> 5MB
                        </div>
                        
                        <!-- Enhanced Preview area -->
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <div class="card border-success">
                                <div class="card-header bg-success-subtle">
                                    <h6 class="mb-0 text-success">
                                        <i class="fas fa-check-circle"></i> Payment Proof Preview
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <img id="previewImg" src="" alt="Payment proof preview" class="img-fluid rounded" style="max-height: 300px;">
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeImage()">
                                            <i class="fas fa-trash"></i> Remove Image
                                        </button>
                                    </div>
                                </div>
                            </div>
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

                    <!-- Enhanced Submit Section -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg shadow" id="submitBtn" disabled>
                            <span class="submit-text">
                                <i class="fas fa-paper-plane"></i> Submit Payment Proof
                            </span>
                            <span class="loading-text d-none">
                                <i class="fas fa-spinner fa-spin"></i> Submitting...
                            </span>
                        </button>
                        <a href="?controller=booking&action=showMyBookings" class="btn btn-outline-secondary">
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

<!-- Enhanced CSS for Payment Form -->
<style>
.payment-method-card {
    transition: all 0.3s ease;
    cursor: default;
}

.payment-method-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.upload-area {
    transition: all 0.3s ease;
    background-color: #f8f9fa;
    border-color: #dee2e6 !important;
    cursor: pointer;
}

.upload-area:hover {
    background-color: #e3f2fd;
    border-color: #0d6efd !important;
}

.upload-area.dragover {
    background-color: #e3f2fd;
    border-color: #0d6efd !important;
    transform: scale(1.02);
}

.form-control:focus, .form-select:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(13, 110, 253, 0.25);
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.pulse {
    animation: pulse 2s infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount_paid');
    const fileInput = document.getElementById('payment_proof');
    const previewDiv = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const form = document.getElementById('paymentForm');
    const submitBtn = document.getElementById('submitBtn');
    const uploadArea = document.getElementById('uploadArea');
    
    // Enhanced amount functions
    window.setFullAmount = function() {
        amountInput.value = <?= $booking->remainingBalance ?>;
        amountInput.classList.add('pulse');
        setTimeout(() => amountInput.classList.remove('pulse'), 1000);
        validateForm();
    };
    
    window.setHalfAmount = function() {
        amountInput.value = (<?= $booking->remainingBalance ?> / 2).toFixed(2);
        amountInput.classList.add('pulse');
        setTimeout(() => amountInput.classList.remove('pulse'), 1000);
        validateForm();
    };
    
    <?php if ($nextPayment): ?>
    window.setInstallmentAmount = function() {
        amountInput.value = <?= $nextPayment->Amount ?>;
        amountInput.classList.add('pulse');
        setTimeout(() => amountInput.classList.remove('pulse'), 1000);
        validateForm();
    };
    <?php endif; ?>

    // Enhanced drag and drop functionality
    uploadArea.addEventListener('click', function(e) {
        if (e.target !== fileInput) {
            fileInput.click();
        }
    });

    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelection(files[0]);
        }
    });

    // Remove image function
    window.removeImage = function() {
        fileInput.value = '';
        previewDiv.style.display = 'none';
        uploadArea.style.display = 'block';
        validateForm();
    };
    
    // Enhanced file preview functionality
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleFileSelection(file);
        } else {
            previewDiv.style.display = 'none';
            uploadArea.style.display = 'block';
        }
    });

    function handleFileSelection(file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            showAlert('Please select an image file (JPG, PNG, GIF)', 'danger');
            fileInput.value = '';
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            showAlert('File size must be less than 5MB', 'danger');
            fileInput.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewDiv.style.display = 'block';
            uploadArea.style.display = 'none';
            validateForm();
            showAlert('Image uploaded successfully!', 'success');
        };
        reader.readAsDataURL(file);
    }

    function showAlert(message, type) {
        // Create temporary alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alert);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 3000);
    }

    // Form validation
    function validateForm() {
        const amountValid = amountInput.value && parseFloat(amountInput.value) > 0;
        const referenceValid = document.getElementById('payment_reference').value.trim();
        const fileValid = fileInput.files.length > 0;
        
        const isValid = amountValid && referenceValid && fileValid;
        submitBtn.disabled = !isValid;
        
        if (isValid) {
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-success');
        } else {
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-secondary');
        }
    }

    // Add validation listeners
    amountInput.addEventListener('input', validateForm);
    document.getElementById('payment_reference').addEventListener('input', validateForm);
    
    // Initial validation
    validateForm();
    
    // Enhanced form submission
    form.addEventListener('submit', function(e) {
        const amount = parseFloat(amountInput.value);
        const maxAmount = <?= $booking->remainingBalance ?>;
        
        if (amount <= 0 || amount > maxAmount) {
            e.preventDefault();
            showAlert(`Please enter a valid payment amount between ₱1.00 and ₱${maxAmount.toLocaleString()}`, 'danger');
            return false;
        }
        
        // Show loading state
        const submitText = submitBtn.querySelector('.submit-text');
        const loadingText = submitBtn.querySelector('.loading-text');
        
        submitText.classList.add('d-none');
        loadingText.classList.remove('d-none');
        submitBtn.disabled = true;
        
        // Confirm submission with enhanced modal-like confirm
        if (!confirm('Are you sure you want to submit this payment?\n\n' +
                    `Amount: ₱${amount.toLocaleString()}\n` +
                    `Reference: ${document.getElementById('payment_reference').value}\n\n` +
                    'Please ensure all information is correct.')) {
            e.preventDefault();
            
            // Restore button state
            submitText.classList.remove('d-none');
            loadingText.classList.add('d-none');
            submitBtn.disabled = false;
            
            return false;
        }
        
        showAlert('Submitting payment proof...', 'info');
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>