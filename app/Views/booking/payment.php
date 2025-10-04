<?php
$pageTitle = "Booking Confirmation";
require_once __DIR__ . '/../partials/header.php';
?>

        <!-- Enhanced Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="mb-1"><i class="fas fa-check-circle text-primary"></i> <?= htmlspecialchars($pageTitle) ?></h1>
                        <p class="text-muted mb-0">Your booking details and payment information</p>
                    </div>
                    <div class="d-none d-md-block">
                        <i class="fas fa-check-circle fa-3x text-primary opacity-25"></i>
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
                                    <?= htmlspecialchars($facility->FacilityName) ?><?= $index < count($facilities) - 1 ? ', ' : '' ?>
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
                                <div class="mt-2">
                                    <a href="?controller=booking&action=showMyBookings" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-arrow-right"></i> Proceed to Submit Payment
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
