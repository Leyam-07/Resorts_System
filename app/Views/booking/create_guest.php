<?php
$pageTitle = "New Reservation";
require_once __DIR__ . '/../partials/guest_header.php';

// Get pre-selected resort ID and facility ID from URL if available
$selectedResortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
$selectedFacilityId = filter_input(INPUT_GET, 'facility_id', FILTER_VALIDATE_INT);
?>

<style>
    /* Enhanced Resort Cards */
    .resort-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        cursor: pointer;
        position: relative;
    }

    .resort-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #0d6efd;
    }

    .resort-card .card-img-top {
        height: 150px;
        object-fit: cover;
    }

    .resort-card .form-check-input {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 1.5em;
        height: 1.5em;
        z-index: 10;
    }

    .resort-card.selected {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
    }
    /* Step Indicators */
    .step-indicator {
        transition: all 0.3s ease;
    }

    .step-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin: 0 auto 5px;
        transition: all 0.3s ease;
    }

    .step-indicator.active .step-circle {
        background-color: #0d6efd;
        color: white;
        transform: scale(1.1);
    }
</style>

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
    
    <div class="alert alert-info" role="alert">
        Please <a href="?action=login">login</a> or <a href="?action=showRegisterForm">register</a> to create a new reservation.
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

            <!-- Step 2: Enhanced Timeframe Selection (Required) -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-clock text-primary"></i> Select Timeframe <span class="text-danger">*</span>
                </label>
                <div class="row g-3 opacity-50 pe-none" id="timeframe-selection-container">
                    <div class="col-md-4 col-sm-6">
                        <div class="card timeframe-card h-100 bg-warning-subtle">
                            <label for="timeframe_12_hours" class="form-check-label w-100 mb-0">
                                <input type="radio" class="form-check-input timeframe-radio" name="timeframe"
                                       id="timeframe_12_hours" value="12_hours" required>
                                <div class="card-body text-center">
                                    <h5 class="card-title">
                                        <i class="fas fa-sun text-warning me-2"></i>
                                        12 Hours
                                    </h5>
                                    <div class="small text-muted">
                                        <div class="mb-1"><strong>Check In:</strong> 7:00 AM</div>
                                        <div><strong>Check Out:</strong> 5:00 PM</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="card timeframe-card h-100 bg-info-subtle">
                            <label for="timeframe_24_hours" class="form-check-label w-100 mb-0">
                                <input type="radio" class="form-check-input timeframe-radio" name="timeframe"
                                       id="timeframe_24_hours" value="24_hours">
                                <div class="card-body text-center">
                                    <h5 class="card-title">
                                        <i class="fas fa-clock text-info me-2"></i>
                                        24 Hours
                                    </h5>
                                    <div class="small text-muted">
                                        <div class="mb-1"><strong>Check In:</strong> 7:00 AM</div>
                                        <div><strong>Check Out:</strong> 5:00 AM (next day)</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="card timeframe-card h-100 bg-dark-subtle">
                            <label for="timeframe_overnight" class="form-check-label w-100 mb-0">
                                <input type="radio" class="form-check-input timeframe-radio" name="timeframe"
                                       id="timeframe_overnight" value="overnight">
                                <div class="card-body text-center">
                                    <h5 class="card-title">
                                        <i class="fas fa-moon text-secondary me-2"></i>
                                        Overnight
                                    </h5>
                                    <div class="small text-muted">
                                        <div class="mb-1"><strong>Check In:</strong> 7:00 PM</div>
                                        <div><strong>Check Out:</strong> 5:00 AM (next day)</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Enhanced Date Selection (Required) -->
            <div class="mb-4">
                <label for="date" class="form-label fw-bold">
                    <i class="fas fa-calendar-alt text-primary"></i> Select Date <span class="text-danger">*</span>
                </label>
                <div class="row g-3 opacity-50 pe-none">
                    <div class="col-md-6 col-sm-12">
                        <div class="card date-card h-100 bg-primary-subtle">
                            <div class="card-body text-center clickable-card">
                                <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                <h6 class="card-title text-primary fw-bold mb-1">Navigate Calendar</h6>
                                <p class="card-text small text-muted mb-0">Click to browse and select your date</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Enhanced Facility Selection -->
            <div class="mb-4">
                 <label class="form-label fw-bold">
                     <i class="fas fa-swimming-pool text-primary"></i> Additional Facilities
                     <span class="text-muted">(Optional)</span>
                 </label>
                 <div id="facilitiesContainer" class="row">
                     <div class="col-12">
                         <div class="alert alert-secondary text-center" id="noFacilitiesMessage">
                             <i class="fas fa-info-circle"></i> Please select a resort first to view available facilities
                         </div>
                     </div>
                 </div>
            </div>

            <!-- Step 5: Enhanced Booking Summary -->
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

<?php 
require_once __DIR__ . '/../partials/footer.php'; 
?>