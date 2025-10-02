<?php
$pageTitle = "New Booking";
require_once __DIR__ . '/../partials/header.php';

// Get pre-selected resort ID and facility ID from URL if available
$selectedResortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
$selectedFacilityId = filter_input(INPUT_GET, 'facility_id', FILTER_VALIDATE_INT);
?>

<!-- Enhanced Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="mb-1"><i class="fas fa-calendar-plus text-primary"></i> <?= htmlspecialchars($pageTitle) ?></h1>
                <p class="text-muted mb-0">Follow the steps below to create your resort booking</p>
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
                        <small>Guests</small>
                    </div>
                    <div class="col step-indicator" id="step5">
                        <div class="step-circle">5</div>
                        <small>Facilities</small>
                    </div>
                    <div class="col step-indicator" id="step6">
                        <div class="step-circle">6</div>
                        <small>Summary</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="?controller=booking&action=createBooking" method="POST" id="bookingForm">
            <!-- Step 1: Enhanced Resort Selection (Required) -->
            <div class="mb-4">
                <label for="resort" class="form-label fw-bold">
                    <i class="fas fa-map-marker-alt text-primary"></i> Select Resort <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                    <select class="form-select" id="resort" name="resort_id" required>
                        <option value="" disabled <?= !$selectedResortId ? 'selected' : '' ?>>Choose your resort...</option>
                        <?php foreach ($resorts as $resort): ?>
                            <option value="<?= $resort->resortId ?>" <?= ($selectedResortId == $resort->resortId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($resort->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-text">
                    <i class="fas fa-info-circle"></i> Your resort selection will determine available facilities and pricing
                </div>
            </div>

            <!-- Step 2: Enhanced Timeframe Selection (Required) -->
            <div class="mb-4">
                <label for="timeSlotType" class="form-label fw-bold">
                    <i class="fas fa-clock text-primary"></i> Select Timeframe <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-time"></i></span>
                    <select class="form-select" id="timeSlotType" name="timeframe" required>
                        <option value="" disabled selected>Choose your timeframe...</option>
                        <option value="12_hours" <?= (isset($_SESSION['old_input']['timeSlotType']) && $_SESSION['old_input']['timeSlotType'] == '12_hours') ? 'selected' : '' ?>>
                            ☀️ 12 Hours (7:00 AM - 5:00 PM)
                        </option>
                        <option value="24_hours" <?= (isset($_SESSION['old_input']['timeSlotType']) && $_SESSION['old_input']['timeSlotType'] == '24_hours') ? 'selected' : '' ?>>
                            🕐 24 Hours (7:00 AM - 5:00 AM next day)
                        </option>
                        <option value="overnight" <?= (isset($_SESSION['old_input']['timeSlotType']) && $_SESSION['old_input']['timeSlotType'] == 'overnight') ? 'selected' : '' ?>>
                            🌙 Overnight (7:00 PM - 5:00 AM)
                        </option>
                    </select>
                </div>
                <div class="form-text">
                    <i class="fas fa-info-circle"></i> Select your preferred timeframe to enable date browsing and see pricing
                </div>
                <div id="timeframePricing" class="mt-2" style="display: none;">
                    <div class="card border-primary">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-primary">Base Price:</strong>
                                    <span id="basePriceDisplay" class="fs-5 fw-bold text-success">₱0.00</span>
                                </div>
                                <div id="weekendNotice" style="display: none;" class="badge bg-warning">
                                    <i class="fas fa-calendar-week"></i> Weekend Rate
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Enhanced Date Selection (Required) -->
            <div class="mb-4">
                <label for="date" class="form-label fw-bold">
                    <i class="fas fa-calendar-alt text-primary"></i> Select Date <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="date" class="form-control" id="date" name="booking_date"
                           value="<?= htmlspecialchars($_SESSION['old_input']['bookingDate'] ?? '') ?>"
                           min="<?= date('Y-m-d') ?>" required>
                    <button type="button" class="btn btn-primary" id="calendarModalBtn" disabled>
                        <i class="fas fa-calendar-check"></i> Browse Available Dates
                    </button>
                </div>
                <div class="form-text">
                    <i class="fas fa-info-circle"></i> Use "Browse Available Dates" for real-time availability with color-coded calendar view, or select directly above.
                </div>
                <div id="dateAvailabilityInfo" class="mt-2" style="display: none;">
                    <div class="alert alert-info mb-0">
                        <small><span id="selectedDateInfo"></span></small>
                    </div>
                </div>
            </div>

            <!-- Step 4: Enhanced Number of Guests (Required) -->
            <div class="mb-4">
                <label for="guests" class="form-label fw-bold">
                    <i class="fas fa-users text-primary"></i> Number of Guests <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="number" class="form-control" id="guests" name="number_of_guests"
                           value="<?= htmlspecialchars($_SESSION['old_input']['numberOfGuests'] ?? '') ?>"
                           placeholder="Enter number of guests" min="1" required>
                    <span class="input-group-text" id="guest_capacity_display">/ 0</span>
                </div>
                <div class="form-text">
                    <i class="fas fa-info-circle"></i> <span id="guestCapacityNote">Ensure the number doesn't exceed resort capacity</span>
                </div>
                <div id="guestCapacityWarning" class="mt-2" style="display: none;">
                    <div class="alert alert-warning mb-0">
                        <small><i class="fas fa-exclamation-triangle"></i> <span id="capacityWarningText"></span></small>
                    </div>
                </div>
            </div>

            <!-- Step 5: Enhanced Facility Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold">
                    <i class="fas fa-swimming-pool text-primary"></i> Additional Facilities
                    <span class="text-muted">(Optional)</span>
                </label>
                <div class="form-text mb-3">
                    <i class="fas fa-plus-circle"></i> Select any additional facilities to enhance your booking experience
                </div>
                <div id="facilitiesContainer" class="row">
                    <div class="col-12">
                        <div class="alert alert-secondary text-center" id="noFacilitiesMessage">
                            <i class="fas fa-info-circle"></i> Please select a resort first to view available facilities
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Enhanced Booking Summary -->
            <div class="mb-4">
                <div class="card border-primary shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt"></i> Booking Summary</h5>
                    </div>
                    <div class="card-body">
                        <div id="pricingBreakdown" style="display: none;">
                            <div class="d-flex justify-content-between mb-2 py-2">
                                <span><i class="fas fa-clock text-muted"></i> Base Price (<span id="summaryTimeframe"></span>):</span>
                                <span id="summaryBasePrice" class="fw-bold text-success">₱0.00</span>
                            </div>
                            <div id="facilityPricing">
                                <!-- Facility prices will be populated here -->
                            </div>
                            <hr class="my-3">
                            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
                                <span class="fs-5 fw-bold"><i class="fas fa-tag text-primary"></i> Total Price:</span>
                                <span id="totalPriceDisplay" class="fs-4 fw-bold text-primary">₱0.00</span>
                            </div>
                        </div>
                        <div id="noPricingMessage" class="text-muted text-center py-4">
                            <i class="fas fa-calculator fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0">Complete your selections to see pricing breakdown</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Submit Section -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg shadow" id="submitBtn" disabled>
                    <i class="fas fa-calendar-check"></i> Complete Booking
                </button>
                <a href="?" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>

<!-- Enhanced Calendar Modal -->
<div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title" id="calendarModalLabel">
                    <i class="fas fa-calendar-alt"></i> Select Date - Availability Calendar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="calendarLoading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2">Loading availability...</p>
                </div>
                <div id="calendarContent" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-12">
                            <input type="month" class="form-control form-control-sm" id="calendarMonth" value="<?= date('Y-m') ?>">
                        </div>
                    </div>
                    <div class="legend text-center mb-2">
                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                            <span class="badge bg-success">Available</span>
                            <span class="badge bg-warning">Weekend</span>
                            <span class="badge bg-info text-dark">Booked</span>
                            <span class="badge bg-danger">Taken</span>
                            <span class="badge bg-secondary">Blocked</span>
                        </div>
                    </div>
                    <div id="calendarGrid" class="calendar-grid">
                        <!-- Calendar will be populated here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="selectDateBtn" disabled>
                    <i class="fas fa-check"></i> Select Date
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add CSS for enhanced UI components -->
<style>
/* Hide native date picker calendar icon */
input[type="date"]::-webkit-calendar-picker-indicator {
    display: none;
    -webkit-appearance: none;
}

input[type="date"]::-webkit-inner-spin-button,
input[type="date"]::-webkit-clear-button {
    display: none;
    -webkit-appearance: none;
}

/* Firefox date input styling */
input[type="date"] {
    -moz-appearance: textfield;
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

.step-indicator.completed .step-circle {
    background-color: #198754;
    color: white;
}

.step-indicator.completed .step-circle::before {
    content: "✓";
}

/* Calendar Grid */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    font-size: 0.7rem;
}

.calendar-day {
    aspect-ratio: 1 / 1.2;
    border: 1px solid #f0f0f0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background-color: white;
    position: relative;
    padding: 1px;
    text-align: center;
}

.calendar-day:hover:not(.disabled) {
    transform: scale(1.05);
    z-index: 1;
}

.calendar-day.available {
    background-color: #d1e7dd;
    border-color: #198754;
    color: #0f5132;
}

.calendar-day.weekend {
    background-color: #fff3cd;
    border-color: #ffc107;
    color: #664d03;
}

.calendar-day.unavailable {
    background-color: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.calendar-day.booked {
    background-color: #cff4fc;
    border-color: #0dcaf0;
    color: #087990;
}

.calendar-day.blocked {
    background-color: #e2e3e5;
    border-color: #6c757d;
    color: #495057;
}

.calendar-day.past {
    background-color: #f8f9fa;
    color: #adb5bd;
    cursor: not-allowed;
}

.calendar-day.selected {
    background-color: #0d6efd !important;
    color: white !important;
    border-color: #0d6efd !important;
    font-weight: bold;
}

.calendar-day.disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

.calendar-day-header {
    background-color: #495057;
    color: white;
    font-weight: bold;
    border: 1px solid #495057;
    justify-content: center;
    padding: 5px;
}

.calendar-day .day-number {
    font-size: 1em;
    font-weight: bold;
}

.calendar-day .day-status {
    font-size: 0.55em;
    text-transform: uppercase;
    margin-top: 1px;
    font-weight: 500;
}

/* Enhanced form animations */
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

/* Loading states */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Mobile responsiveness improvements */
@media (max-width: 768px) {
    .step-indicator {
        margin-bottom: 10px;
    }
    
    .step-circle {
        width: 25px;
        height: 25px;
        font-size: 0.8rem;
    }
    
    .calendar-grid {
        font-size: 0.8rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const resortSelect = document.getElementById('resort');
    const dateInput = document.getElementById('date');
    const timeSlotSelect = document.getElementById('timeSlotType');
    const guestsInput = document.getElementById('guests');
    const facilitiesContainer = document.getElementById('facilitiesContainer');
    const submitBtn = document.getElementById('submitBtn');

    // Display elements
    const timeframePricing = document.getElementById('timeframePricing');
    const basePriceDisplay = document.getElementById('basePriceDisplay');
    const weekendNotice = document.getElementById('weekendNotice');
    const pricingBreakdown = document.getElementById('pricingBreakdown');
    const noPricingMessage = document.getElementById('noPricingMessage');
    const totalPriceDisplay = document.getElementById('totalPriceDisplay');
    const summaryTimeframe = document.getElementById('summaryTimeframe');
    const summaryBasePrice = document.getElementById('summaryBasePrice');
    const facilityPricing = document.getElementById('facilityPricing');
    const noFacilitiesMessage = document.getElementById('noFacilitiesMessage');

    // Enhanced UI elements
    const calendarModalBtn = document.getElementById('calendarModalBtn');
    const calendarModal = new bootstrap.Modal(document.getElementById('calendarModal'));
    const calendarMonth = document.getElementById('calendarMonth');
    const calendarGrid = document.getElementById('calendarGrid');
    const calendarLoading = document.getElementById('calendarLoading');
    const calendarContent = document.getElementById('calendarContent');
    const selectDateBtn = document.getElementById('selectDateBtn');
    const dateAvailabilityInfo = document.getElementById('dateAvailabilityInfo');
    const selectedDateInfo = document.getElementById('selectedDateInfo');
    const guestCapacityWarning = document.getElementById('guestCapacityWarning');
    const capacityWarningText = document.getElementById('capacityWarningText');
    const guestCapacityDisplay = document.getElementById('guest_capacity_display');

    // Step indicators
    const stepIndicators = document.querySelectorAll('.step-indicator');

    // State tracking
    let currentBasePrice = 0;
    let availableFacilities = [];
    let selectedFacilities = [];
    let calendarData = {};
    let selectedCalendarDate = null;
    let currentStep = 1;
    let resortCapacity = 0;

    // Event listeners
    resortSelect.addEventListener('change', handleResortChange);
    dateInput.addEventListener('change', handleDateOrTimeframeChange);
    timeSlotSelect.addEventListener('change', handleDateOrTimeframeChange);
    guestsInput.addEventListener('input', handleGuestsChange);
    
    // Enhanced calendar modal events
    calendarModalBtn.addEventListener('click', openCalendarModal);
    calendarMonth.addEventListener('change', loadCalendarData);
    selectDateBtn.addEventListener('click', selectCalendarDate);

    // Initialize form if there are pre-selected values
    updateStepIndicators();
    if (resortSelect.value) {
        handleResortChange();
    }

    // Enhanced step management
    function updateStepIndicators() {
        stepIndicators.forEach((indicator, index) => {
            const stepNum = index + 1;
            indicator.classList.remove('active', 'completed');
            
            if (stepNum < currentStep) {
                indicator.classList.add('completed');
            } else if (stepNum === currentStep) {
                indicator.classList.add('active');
            }
        });
    }

    function advanceToStep(step) {
        if (step > currentStep) {
            currentStep = step;
            updateStepIndicators();
        }
    }

    function handleResortChange() {
        const resortId = resortSelect.value;
        if (!resortId) {
            resetFacilities();
            resetPricing();
            calendarModalBtn.disabled = true;
            return;
        }

        advanceToStep(2);
        calendarModalBtn.disabled = false;
        loadFacilities(resortId);
        loadResortDetails(resortId);
        handleDateOrTimeframeChange();
    }

    // Enhanced calendar functionality
    function openCalendarModal() {
        if (!resortSelect.value || !timeSlotSelect.value) {
            alert('Please select a resort and timeframe first');
            return;
        }
        
        calendarModal.show();
        loadCalendarData();
    }

    function loadCalendarData() {
        const resortId = resortSelect.value;
        const timeframe = timeSlotSelect.value;
        const month = calendarMonth.value;

        if (!resortId || !timeframe) return;

        calendarLoading.style.display = 'block';
        calendarContent.style.display = 'none';

        fetch(`?controller=booking&action=getCalendarAvailability&resort_id=${resortId}&timeframe=${timeframe}&month=${month}`)
            .then(response => response.json())
            .then(data => {
                calendarData = data.availability;
                renderCalendar(data.month);
                calendarLoading.style.display = 'none';
                calendarContent.style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading calendar:', error);
                calendarLoading.innerHTML = '<div class="alert alert-danger">Error loading calendar data</div>';
            });
    }

    function renderCalendar(month) {
        const [year, monthNum] = month.split('-');
        const firstDay = new Date(year, monthNum - 1, 1);
        
        let html = '';
        
        // Add day headers
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayNames.forEach(day => {
            html += `<div class="calendar-day-header">${day}</div>`;
        });

        // Fix: Use UTC dates to avoid timezone issues completely
        const startOfMonth = new Date(Date.UTC(parseInt(year), parseInt(monthNum) - 1, 1));
        const startCalendarDate = new Date(startOfMonth);
        startCalendarDate.setUTCDate(startCalendarDate.getUTCDate() - startOfMonth.getUTCDay());
        
        // Generate 6 weeks (42 days) for the calendar
        for (let i = 0; i < 42; i++) {
            const currentCalendarDate = new Date(startCalendarDate);
            currentCalendarDate.setUTCDate(startCalendarDate.getUTCDate() + i);
            
            const dateStr = currentCalendarDate.toISOString().split('T')[0];
            const dayNum = currentCalendarDate.getUTCDate();
            const isCurrentMonth = currentCalendarDate.getUTCMonth() === startOfMonth.getUTCMonth();
            
            let dayClass = 'calendar-day';
            let dayData = calendarData[dateStr];
            
            if (!isCurrentMonth) {
                dayClass += ' disabled text-muted';
            } else if (dayData) {
                // Use the backend status directly - no frontend weekend calculation
                dayClass += ` ${dayData.status}`;
                if (!dayData.available) dayClass += ' disabled';
            } else {
                // No data for this date
                dayClass += ' disabled';
            }

            const dayName = currentCalendarDate.toLocaleDateString('en-US', {weekday: 'long'});
            
            html += `
                <div class="${dayClass}" data-date="${dateStr}" title="${dayName}, ${dayNum}" ${dayData && dayData.available && isCurrentMonth ? 'onclick="selectDate(\'' + dateStr + '\')"' : ''}>
                    <div class="day-number">${dayNum}</div>
                    <div class="day-status">${dayData ? dayData.statusText : ''}</div>
                </div>
            `;
        }

        calendarGrid.innerHTML = html;
    }

    window.selectDate = function(dateStr) {
        // Remove previous selection
        document.querySelectorAll('.calendar-day.selected').forEach(day => {
            day.classList.remove('selected');
        });
        
        // Select new date
        const dayElement = document.querySelector(`[data-date="${dateStr}"]`);
        if (dayElement && !dayElement.classList.contains('disabled')) {
            dayElement.classList.add('selected');
            selectedCalendarDate = dateStr;
            selectDateBtn.disabled = false;
        }
    };

    function selectCalendarDate() {
        if (selectedCalendarDate) {
            dateInput.value = selectedCalendarDate;
            calendarModal.hide();
            handleDateOrTimeframeChange();
        }
    }

    // Enhanced guest handling
    function handleGuestsChange() {
        const numberOfGuests = parseInt(guestsInput.value) || 0;
        if (numberOfGuests > resortCapacity) {
            guestsInput.value = resortCapacity;
            guestCapacityWarning.style.display = 'block';
            capacityWarningText.textContent = `Number of guests cannot exceed the resort capacity of ${resortCapacity}.`;
        } else {
            guestCapacityWarning.style.display = 'none';
        }
        validateGuestCapacity();
        validateForm();
    }

    function loadFacilities(resortId) {
        facilitiesContainer.innerHTML = '<div class="col-12"><div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading facilities...</div></div>';

        fetch(`?controller=booking&action=getFacilitiesByResort&resort_id=${resortId}`)
            .then(response => response.json())
            .then(facilities => {
                availableFacilities = facilities;
                renderFacilities(facilities);
            })
            .catch(error => {
                console.error('Error fetching facilities:', error);
                facilitiesContainer.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error loading facilities</div></div>';
            });
    }

    function renderFacilities(facilities) {
        if (facilities.length === 0) {
            facilitiesContainer.innerHTML = '<div class="col-12"><div class="alert alert-info text-center">No additional facilities available for this resort</div></div>';
            return;
        }

        let html = '';
        facilities.forEach(facility => {
            html += `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input facility-checkbox" type="checkbox"
                                       value="${facility.facilityId}" id="facility_${facility.facilityId}"
                                       data-price="${facility.rate}">
                                <label class="form-check-label" for="facility_${facility.facilityId}">
                                    <strong>${facility.name}</strong>
                                </label>
                            </div>
                            <div class="mt-2 small text-muted">
                                <div class="text-primary fw-bold">${facility.priceDisplay}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        facilitiesContainer.innerHTML = html;

        // Add event listeners to checkboxes
        document.querySelectorAll('.facility-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', handleFacilitySelection);
        });

        // Pre-select facility if facility_id is provided in URL
        const urlParams = new URLSearchParams(window.location.search);
        const preSelectedFacilityId = urlParams.get('facility_id');
        if (preSelectedFacilityId) {
            const facilityCheckbox = document.querySelector(`#facility_${preSelectedFacilityId}`);
            if (facilityCheckbox) {
                facilityCheckbox.checked = true;
                facilityCheckbox.dispatchEvent(new Event('change'));
            }
        }
    }

    function handleFacilitySelection() {
        selectedFacilities = [];
        document.querySelectorAll('.facility-checkbox:checked').forEach(checkbox => {
            selectedFacilities.push({
                id: checkbox.value,
                price: parseFloat(checkbox.dataset.price),
                name: checkbox.nextElementSibling.textContent.trim()
            });
        });

        // Advance to step 6 if facilities are selected
        if (selectedFacilities.length > 0) {
            advanceToStep(6);
        }

        validateGuestCapacity();
        updatePricingDisplay();
        validateForm();
    }

    function loadResortDetails(resortId) {
        console.log('loadResortDetails called with resortId:', resortId);
        const url = `?controller=booking&action=getResortDetails&resort_id=${resortId}`;
        console.log('Fetching from URL:', url);

        fetch(url)
            .then(response => {
                console.log('Fetch response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('API response data:', data);
                if (data.capacity !== undefined) {
                    resortCapacity = data.capacity;
                    guestsInput.max = resortCapacity;
                    guestCapacityDisplay.textContent = `/ ${resortCapacity}`;
                    // Update the help text with maximum capacity
                    document.getElementById('guestCapacityNote').textContent = `Ensure the number doesn't exceed resort capacity - ${resortCapacity} maximum.`;
                    console.log('Capacity updated to:', resortCapacity);
                } else {
                    console.error('No capacity in response data');
                }
            })
            .catch(error => {
                console.error('Error fetching resort details:', error);
                // Set a fallback capacity to test
                resortCapacity = 10; // Fallback for testing
                guestCapacityDisplay.textContent = `/ ${resortCapacity}`;
                // Update the help text with fallback capacity
                document.getElementById('guestCapacityNote').textContent = `Ensure the number doesn't exceed resort capacity - ${resortCapacity} maximum.`;
                console.log('Using fallback capacity:', resortCapacity);
            });
    }

    function validateGuestCapacity() {
        // This function is now a placeholder and can be removed or repurposed.
        // For now, it will always return true as capacity is handled at the resort level.
        return true;
    }

    function handleDateOrTimeframeChange() {
        const resortId = resortSelect.value;
        const date = dateInput.value;
        const timeframe = timeSlotSelect.value;

        if (resortId && date && timeframe) {
            loadTimeframePricing(resortId, timeframe, date);
            updateTotalPrice();
            updateDateAvailabilityInfo(date);
        } else {
            resetPricing();
            if (dateAvailabilityInfo) {
                dateAvailabilityInfo.style.display = 'none';
            }
        }
        validateForm();
    }

    function loadTimeframePricing(resortId, timeframe, date) {
        fetch(`?controller=booking&action=getResortPricing&resort_id=${resortId}&timeframe=${timeframe}&date=${date}`)
            .then(response => response.json())
            .then(pricing => {
                currentBasePrice = pricing.basePrice;
                basePriceDisplay.textContent = pricing.basePriceDisplay;
                summaryTimeframe.textContent = pricing.timeframeDisplay;
                summaryBasePrice.textContent = pricing.basePriceDisplay;

                if (pricing.isWeekend) {
                    weekendNotice.style.display = 'block';
                } else {
                    weekendNotice.style.display = 'none';
                }

                timeframePricing.style.display = 'block';
                updatePricingDisplay();
            })
            .catch(error => {
                console.error('Error fetching pricing:', error);
                resetPricing();
            });
    }

    function updateTotalPrice() {
        const resortId = resortSelect.value;
        const timeframe = timeSlotSelect.value;
        const date = dateInput.value;
        const facilityIds = selectedFacilities.map(f => f.id);

        if (!resortId || !timeframe || !date) {
            return;
        }

        const formData = new FormData();
        formData.append('resort_id', resortId);
        formData.append('timeframe', timeframe);
        formData.append('date', date);
        facilityIds.forEach(id => formData.append('facility_ids[]', id));

        fetch('?controller=booking&action=calculateBookingPrice', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            totalPriceDisplay.textContent = result.totalPriceDisplay;
        })
        .catch(error => {
            console.error('Error calculating total price:', error);
        });
    }

    function updatePricingDisplay() {
        if (currentBasePrice > 0) {
            // Update facility pricing breakdown
            let facilityHtml = '';
            selectedFacilities.forEach(facility => {
                facilityHtml += `
                    <div class="d-flex justify-content-between mb-1">
                        <span class="small">${facility.name}:</span>
                        <span class="small">₱${facility.price.toLocaleString()}</span>
                    </div>
                `;
            });
            facilityPricing.innerHTML = facilityHtml;

            pricingBreakdown.style.display = 'block';
            noPricingMessage.style.display = 'none';
            
            updateTotalPrice();
        } else {
            pricingBreakdown.style.display = 'none';
            noPricingMessage.style.display = 'block';
        }
    }

    function validateForm() {
        const resortValid = resortSelect.value;
        const timeframeValid = timeSlotSelect.value;
        const dateValid = dateInput.value;
        const guestsValid = guestsInput.value && parseInt(guestsInput.value) > 0;
        const capacityValid = validateGuestCapacity();
        const facilitiesSelected = selectedFacilities.length > 0;

        const isValid = resortValid && timeframeValid && dateValid && guestsValid && capacityValid;

        submitBtn.disabled = !isValid;
        
        if (isValid) {
            submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Complete Booking';
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-success');
        } else {
            submitBtn.innerHTML = '<i class="fas fa-calendar-check"></i> Complete Booking';
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-secondary');
        }

        // Update step indicators based on sequential completion
        updateStepProgress(resortValid, timeframeValid, dateValid, guestsValid, facilitiesSelected);
    }

    function updateStepProgress(resortValid, timeframeValid, dateValid, guestsValid, facilitiesSelected) {
        // Reset all steps first
        stepIndicators.forEach((indicator, index) => {
            indicator.classList.remove('active', 'completed');
        });

        // Sequential step progression for required steps
        if (resortValid) {
            markStepCompleted(1);
            
            if (timeframeValid) {
                markStepCompleted(2);
                
                if (dateValid) {
                    markStepCompleted(3);
                    
                    // Step 6 (Summary) becomes green when summary is displayed (after date selection)
                    markStepCompleted(6);
                }
            }
        }

        // Step 4 (Guests) - independent validation
        if (guestsValid) {
            markStepCompleted(4);
        }
        
        // Step 5 (Facilities) - independent validation, turns green when facilities are selected
        if (facilitiesSelected) {
            markStepCompleted(5);
        }
    }

    function markStepCompleted(stepNumber) {
        if (stepIndicators[stepNumber - 1]) {
            stepIndicators[stepNumber - 1].classList.add('completed');
            stepIndicators[stepNumber - 1].classList.remove('active');
        }
    }


    function updateDateAvailabilityInfo(date) {
        const dayOfWeek = new Date(date).getDay();
        const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        let info = `Selected: ${dayNames[dayOfWeek]}, ${new Date(date).toLocaleDateString()}`;
        if (isWeekend) {
            info += ' (Weekend rates may apply)';
        }
        
        if (selectedDateInfo) {
            selectedDateInfo.textContent = info;
            dateAvailabilityInfo.style.display = 'block';
        }
    }

    function resetFacilities() {
        facilitiesContainer.innerHTML = '<div class="col-12">' + noFacilitiesMessage.outerHTML + '</div>';
        selectedFacilities = [];
    }

    function resetPricing() {
        timeframePricing.style.display = 'none';
        pricingBreakdown.style.display = 'none';
        noPricingMessage.style.display = 'block';
        currentBasePrice = 0;
    }

    // Form submission handler to include facility IDs
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        // Add hidden inputs for selected facility IDs
        selectedFacilities.forEach(facility => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'facility_ids[]';
            input.value = facility.id;
            this.appendChild(input);
        });
    });
});
</script>

<?php 
unset($_SESSION['old_input']);
require_once __DIR__ . '/../partials/footer.php'; 
?>
