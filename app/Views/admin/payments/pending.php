<?php
$pageTitle = "Pending Payments";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-clock"></i> <?= htmlspecialchars($pageTitle) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?controller=admin&action=dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle) ?></li>
            </ol>
        </nav>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($pendingPayments)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-3"></i>
            <h4>No Pending Payments</h4>
            <p class="mb-0">All payment submissions have been processed. Great job!</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($pendingPayments as $payment): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-receipt"></i> Booking #<?= $payment->BookingID ?></h6>
                                <span class="badge bg-dark">Pending Review</span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <!-- Customer Information -->
                            <h6 class="text-primary mb-2"><i class="fas fa-user"></i> Customer</h6>
                            <div class="mb-3">
                                <div><strong><?= htmlspecialchars($payment->CustomerName) ?></strong></div>
                                <div class="small text-muted"><?= htmlspecialchars($payment->CustomerEmail) ?></div>
                            </div>

                            <!-- Booking Details -->
                            <h6 class="text-primary mb-2"><i class="fas fa-calendar"></i> Booking Details</h6>
                            <div class="mb-3">
                                <div><strong>Resort:</strong> <?= htmlspecialchars($payment->ResortName) ?></div>
                                <div><strong>Date:</strong> <?= date('F j, Y', strtotime($payment->BookingDate)) ?></div>
                                <div><strong>Total:</strong> ₱<?= number_format($payment->TotalAmount, 2) ?></div>
                                <div><strong>Remaining:</strong> ₱<?= number_format($payment->RemainingBalance, 2) ?></div>
                            </div>

                            <!-- Payment Information -->
                            <h6 class="text-primary mb-2"><i class="fas fa-money-bill"></i> Payment Details</h6>
                            <div class="mb-3">
                                <div><strong>Amount Paid:</strong> ₱<?= number_format($payment->Amount, 2) ?></div>
                                <div><strong>Method:</strong> <?= htmlspecialchars($payment->PaymentMethod) ?></div>
                                <div><strong>Submitted:</strong> <?= date('M j, Y g:i A', strtotime($payment->PaymentDate)) ?></div>
                            </div>

                            <!-- Payment Proof -->
                            <?php if ($payment->ProofOfPaymentURL): ?>
                                <h6 class="text-primary mb-2"><i class="fas fa-image"></i> Payment Proof</h6>
                                <div class="mb-3">
                                    <img src="<?= htmlspecialchars($payment->ProofOfPaymentURL) ?>" 
                                         alt="Payment Proof" 
                                         class="img-fluid rounded border cursor-pointer"
                                         style="max-height: 150px; width: 100%; object-fit: cover;"
                                         onclick="showImageModal(this.src)">
                                    <div class="small text-muted mt-1">Click to view full size</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="d-grid gap-2">
                                <!-- Verify Button -->
                                <button type="button" class="btn btn-success" 
                                        onclick="verifyPayment(<?= $payment->PaymentID ?>, <?= $payment->BookingID ?>)">
                                    <i class="fas fa-check"></i> Verify & Approve Payment
                                </button>
                                
                                <!-- Reject Button -->
                                <button type="button" class="btn btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectModal"
                                        onclick="setRejectPaymentId(<?= $payment->PaymentID ?>, <?= $payment->BookingID ?>)">
                                    <i class="fas fa-times"></i> Reject Payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Payment Proof</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Payment Proof" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Reject Payment Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?controller=payment&action=rejectPayment" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="rejectPaymentId" name="payment_id" value="">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action will reject the payment submission and notify the customer to resubmit.
                    </div>
                    
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">Reason for Rejection (Optional)</label>
                        <textarea class="form-control" id="rejectReason" name="reason" rows="3" 
                                  placeholder="e.g., Image unclear, wrong amount, invalid reference number..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Show image in modal
function showImageModal(src) {
    document.getElementById('modalImage').src = src;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

// Verify payment
function verifyPayment(paymentId, bookingId) {
    if (confirm('Are you sure you want to verify and approve this payment? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?controller=payment&action=verifyPayment';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'payment_id';
        input.value = paymentId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

// Set reject payment ID in modal
function setRejectPaymentId(paymentId, bookingId) {
    document.getElementById('rejectPaymentId').value = paymentId;
}

// Auto-refresh every 30 seconds if there are pending payments
<?php if (!empty($pendingPayments)): ?>
setTimeout(function() {
    // Only refresh if user hasn't interacted with modals
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 30000);
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>