<?php
$pageTitle = "New Reservation";
require_once __DIR__ . '/../partials/guest_header.php';

// Get pre-selected resort ID and facility ID from URL if available
$selectedResortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
$selectedFacilityId = filter_input(INPUT_GET, 'facility_id', FILTER_VALIDATE_INT);
?>

<style>
    .disabled-overlay {
        position: relative;
    }
    .disabled-overlay::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.5);
        z-index: 10;
    }
    .login-prompt {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 11;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }
</style>

<div class="disabled-overlay">
    <div class="login-prompt">
        <h3>Login to Book</h3>
        <p>You must be logged in to make a reservation.</p>
        <a href="?action=login" class="btn btn-primary">Login</a>
        <a href="?action=showRegisterForm" class="btn btn-secondary">Register</a>
    </div>

    <fieldset disabled>
        <!-- Paste the content of create.php here, from the "Enhanced Page Header" comment down to the </form> tag -->
        <!-- Enhanced Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="mb-1"><i class="fas fa-calendar-plus text-primary"></i> <?= htmlspecialchars($pageTitle) ?></h1>
                        <p class="text-muted mb-0">Follow the steps below to create your resort reservation</p>
                    </div>
                    <div class="d-none d-md-block">
                        <i class="fas fa-swimming-pool fa-3x text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progressive Step Indicators -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body py-3">
                        <div class="row text-center">
                            <div class="col step-indicator active" id="step1">
                                <div class="step-circle">1</div>
                                <small>Resort</small>
                            </div>
                            <div class="col step-indicator" id="step2">
                                <div class="step-circle">2</div>
                                <small>Timeframe</small>
                            </div>
                            <div class="col step-indicator" id="step3">
                                <div class="step-circle">3</div>
                                <small>Date</small>
                            </div>
                            <div class="col step-indicator" id="step4">
                                <div class="step-circle">4</div>
                                <small>Facilities</small>
                            </div>
                            <div class="col step-indicator" id="step5">
                                <div class="step-circle">5</div>
                                <small>Summary</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <form action="?controller=booking&action=createBooking" method="POST" id="bookingForm">
            <!-- Step 1: Enhanced Resort Selection (Required) -->
            <div class="mb-4">
                <label for="resort" class="form-label fw-bold">
                    <i class="fas fa-map-marker-alt text-primary"></i> Select Resort <span class="text-danger">*</span>
                </label>
                <div class="row g-3" id="resort-selection-container">
                    <?php if (empty($resorts)): ?>
                        <div class="col-12">
                            <div class="alert alert-warning text-center">No resorts are currently available.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($resorts as $resort): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card resort-card h-100">
                                    <label for="resort_<?= $resort->resortId ?>" class="form-check-label w-100 mb-0">
                                        <input type="radio" class="form-check-input resort-radio" name="resort_id"
                                                id="resort_<?= $resort->resortId ?>" value="<?= $resort->resortId ?>"
                                                <?= ($selectedResortId == $resort->resortId) ? 'checked' : '' ?>>
                                        <img src="<?= htmlspecialchars($resort->mainPhotoURL) ?>" class="card-img-top" alt="<?= htmlspecialchars($resort->name) ?>">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="<?= htmlspecialchars($resort->icon) ?> text-primary me-2"></i>
                                                <?= htmlspecialchars($resort->name) ?>
                                            </h5>
                                            <p class="card-text small text-muted"><?= htmlspecialchars($resort->shortDescription) ?></p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- All other steps from create.php would go here, but are omitted for brevity in this example -->
            <!-- Step 6: Enhanced Booking Summary -->
            <div class="mb-4">
                <div class="card border-primary shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt"></i> Booking Summary</h5>
                    </div>
                    <div class="card-body">
                        <div id="noPricingMessage" class="text-muted text-center py-4">
                            <i class="fas fa-calculator fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0">Complete your selections to see your booking summary</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Submit Section -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg shadow" id="submitBtn" disabled>
                    <i class="fas fa-calendar-check"></i> Complete Booking
                </button>
            </div>
        </form>
    </fieldset>
</div>

<?php 
require_once __DIR__ . '/../partials/footer.php'; 
?>