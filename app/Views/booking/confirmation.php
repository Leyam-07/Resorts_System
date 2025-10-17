<?php
$pageTitle = "Booking Confirmation";
require_once __DIR__ . '/../partials/header.php';
?>

        <!-- Enhanced Page Header -->
        <div class="text-center mb-4">
            <i class="fas fa-check-circle fa-5x text-primary mb-3"></i>
            <h1 class="text-primary"><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="lead text-muted">Your booking details and payment information</p>
        </div>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($successMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Booking Summary Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-receipt"></i> Booking Summary</h5>
            </div>
            <div class="card-body">
                <!-- Customer Information Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Customer Information</h6>
                        <div class="mb-2">
                            <strong>Name:</strong> <?= htmlspecialchars($customer['FirstName'] . ' ' . $customer['LastName']) ?>
                        </div>
                        <div class="mb-2">
                            <strong>Contact Number:</strong> <?= htmlspecialchars($customer['PhoneNumber']) ?>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Booking Details</h6>
                        <div class="mb-2">
                            <strong>Booking ID:</strong> #<?= $booking->bookingId ?>
                        </div>
                        <div class="mb-2">
                            <strong>Resort:</strong> <?= htmlspecialchars($resort->name ?? 'N/A') ?>
                        </div>
                        <div class="mb-2">
                            <strong>Date:</strong> <?= date('F j, Y', strtotime($booking->bookingDate)) ?>
                        </div>
                        <div class="mb-2">
                            <strong>Timeframe:</strong> <?= htmlspecialchars(Booking::getTimeSlotDisplay($booking->timeSlotType)) ?>
                        </div>

                        <?php if (!empty($facilities)): ?>
                            <div class="mb-2">
                                <strong>Facilities:</strong>
                                <ul class="mb-0">
                                    <?php foreach ($facilities as $facility): ?>
                                        <li><?= htmlspecialchars($facility->FacilityName) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Payment Information</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Booking Amount:</span>
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
                            <span class="fw-bold <?= $booking->remainingBalance > 0 ? 'text-warning' : 'text-success' ?>">
                                ₱<?= number_format($booking->remainingBalance, 2) ?>
                            </span>
                        </div>

                        <?php if (isset($latestPayment) && !empty($latestPayment->PaymentMethod)): ?>
                            <div class="mb-2">
                                <strong>Payment Method:</strong> <?= htmlspecialchars($latestPayment->PaymentMethod) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($booking->paymentReference): ?>
                            <div class="mb-2">
                                <strong>Payment Reference:</strong> <?= htmlspecialchars($booking->paymentReference) ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-2">
                            <strong>Status:</strong>
                            <span class="badge bg-<?= $booking->status == 'Confirmed' ? 'success' : 'warning' ?>">
                                <?= htmlspecialchars($booking->status) ?>
                            </span>
                        </div>

                        <?php if ($booking->remainingBalance <= 0): ?>
                            <div class="alert alert-success alert-dismissible fade show mb-0">
                                <i class="fas fa-check-circle"></i> <strong>Fully Paid</strong><br>
                                This booking has been paid in full.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info alert-dismissible fade show mb-0">
                                <i class="fas fa-info-circle"></i> <strong>Payment Required</strong><br>
                                You can pay the full amount or make a partial payment.
                                <div class="mt-2">
                                    <a href="?controller=booking&action=showMyReservations" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-arrow-right"></i> Proceed to Submit Payment
                                    </a>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
