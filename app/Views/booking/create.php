<?php
$pageTitle = "New Booking";
require_once __DIR__ . '/../partials/header.php';

// Get pre-selected resort ID from URL if available
$selectedResortId = filter_input(INPUT_GET, 'resort_id', FILTER_VALIDATE_INT);
$selectedFacilityId = filter_input(INPUT_GET, 'facility_id', FILTER_VALIDATE_INT);
?>

        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="?controller=booking&action=createBooking" method="POST" id="bookingForm">
            <!-- Step 1: Resort Selection (Required) -->
            <div class="mb-4">
                <label for="resort" class="form-label fw-bold">Select Resort <span class="text-danger">*</span></label>
                <select class="form-select" id="resort" name="resortId" required>
                    <option value="" disabled <?= !$selectedResortId ? 'selected' : '' ?>>Choose your resort...</option>
                    <?php foreach ($resorts as $resort): ?>
                        <option value="<?= $resort->resortId ?>" <?= ($selectedResortId == $resort->resortId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($resort->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Step 2: Date Selection (Required) -->
            <div class="mb-4">
                <label for="date" class="form-label fw-bold">Select Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="date" name="bookingDate"
                       value="<?= htmlspecialchars($_SESSION['old_input']['bookingDate'] ?? '') ?>"
                       min="<?= date('Y-m-d') ?>" required>
                <div class="form-text">Availability and pricing may vary by date</div>
            </div>

            <!-- Step 3: Timeframe Selection (Required) -->
            <div class="mb-4">
                <label for="timeSlotType" class="form-label fw-bold">Select Timeframe <span class="text-danger">*</span></label>
                <select class="form-select" id="timeSlotType" name="timeSlotType" required>
                    <option value="" disabled selected>Choose your timeframe...</option>
                    <option value="12_hours" <?= (isset($_SESSION['old_input']['timeSlotType']) && $_SESSION['old_input']['timeSlotType'] == '12_hours') ? 'selected' : '' ?>>
                        12 Hours (7:00 AM - 5:00 PM)
                    </option>
                    <option value="24_hours" <?= (isset($_SESSION['old_input']['timeSlotType']) && $_SESSION['old_input']['timeSlotType'] == '24_hours') ? 'selected' : '' ?>>
                        24 Hours (7:00 AM - 5:00 AM next day)
                    </option>
                    <option value="overnight" <?= (isset($_SESSION['old_input']['timeSlotType']) && $_SESSION['old_input']['timeSlotType'] == 'overnight') ? 'selected' : '' ?>>
                        Overnight (7:00 PM - 5:00 AM)
                    </option>
                </select>
                <div id="timeframePricing" class="mt-2" style="display: none;">
                    <div class="alert alert-info mb-0">
                        <strong>Base Price:</strong> <span id="basePriceDisplay">₱0.00</span>
                        <div id="weekendNotice" style="display: none;" class="text-warning mt-1">
                            <small><i class="fas fa-info-circle"></i> Weekend pricing applies</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Number of Guests (Required) -->
            <div class="mb-4">
                <label for="guests" class="form-label fw-bold">Number of Guests <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="guests" name="numberOfGuests"
                       value="<?= htmlspecialchars($_SESSION['old_input']['numberOfGuests'] ?? '1') ?>"
                       min="1" required>
            </div>

            <!-- Step 5: Optional Facility Selection -->
            <div class="mb-4">
                <label class="form-label fw-bold">Additional Facilities <span class="text-muted">(Optional)</span></label>
                <div class="form-text mb-3">Select any additional facilities you'd like to include with your booking</div>
                <div id="facilitiesContainer" class="row">
                    <div class="col-12">
                        <div class="alert alert-secondary text-center" id="noFacilitiesMessage">
                            <i class="fas fa-info-circle"></i> Please select a resort first to view available facilities
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Total Price Display -->
            <div class="mb-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Booking Summary</h5>
                        <div id="pricingBreakdown" style="display: none;">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Base Price (<span id="summaryTimeframe"></span>):</span>
                                <span id="summaryBasePrice">₱0.00</span>
                            </div>
                            <div id="facilityPricing">
                                <!-- Facility prices will be populated here -->
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total Price:</span>
                                <span id="totalPriceDisplay" class="text-primary">₱0.00</span>
                            </div>
                        </div>
                        <div id="noPricingMessage" class="text-muted text-center">
                            Complete your selections to see pricing
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                    <i class="fas fa-calendar-check"></i> Complete Booking
                </button>
                <a href="?" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </form>

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

    // State tracking
    let currentBasePrice = 0;
    let availableFacilities = [];
    let selectedFacilities = [];

    // Event listeners
    resortSelect.addEventListener('change', handleResortChange);
    dateInput.addEventListener('change', handleDateOrTimeframeChange);
    timeSlotSelect.addEventListener('change', handleDateOrTimeframeChange);
    guestsInput.addEventListener('input', validateForm);

    // Initialize form if there are pre-selected values
    if (resortSelect.value) {
        handleResortChange();
    }

    function handleResortChange() {
        const resortId = resortSelect.value;
        if (!resortId) {
            resetFacilities();
            resetPricing();
            return;
        }

        loadFacilities(resortId);
        handleDateOrTimeframeChange();
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
                                       data-price="${facility.rate}" data-capacity="${facility.capacity}">
                                <label class="form-check-label" for="facility_${facility.facilityId}">
                                    <strong>${facility.name}</strong>
                                </label>
                            </div>
                            <div class="mt-2 small text-muted">
                                <div>Capacity: ${facility.capacity} guests</div>
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
    }

    function handleFacilitySelection() {
        selectedFacilities = [];
        document.querySelectorAll('.facility-checkbox:checked').forEach(checkbox => {
            selectedFacilities.push({
                id: checkbox.value,
                price: parseFloat(checkbox.dataset.price),
                name: checkbox.nextElementSibling.textContent.trim(),
                capacity: parseInt(checkbox.dataset.capacity)
            });
        });

        validateGuestCapacity();
        updatePricingDisplay();
        validateForm();
    }

    function validateGuestCapacity() {
        const numberOfGuests = parseInt(guestsInput.value) || 0;
        let hasCapacityIssue = false;

        document.querySelectorAll('.facility-checkbox:checked').forEach(checkbox => {
            const capacity = parseInt(checkbox.dataset.capacity);
            const cardBody = checkbox.closest('.card-body');
            
            if (numberOfGuests > capacity) {
                cardBody.classList.add('bg-warning-subtle');
                hasCapacityIssue = true;
            } else {
                cardBody.classList.remove('bg-warning-subtle');
            }
        });

        return !hasCapacityIssue;
    }

    function handleDateOrTimeframeChange() {
        const resortId = resortSelect.value;
        const date = dateInput.value;
        const timeframe = timeSlotSelect.value;

        if (resortId && date && timeframe) {
            loadTimeframePricing(resortId, timeframe, date);
            updateTotalPrice();
        } else {
            resetPricing();
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
        const isValid = resortSelect.value &&
                       dateInput.value &&
                       timeSlotSelect.value &&
                       guestsInput.value &&
                       parseInt(guestsInput.value) > 0 &&
                       validateGuestCapacity();

        submitBtn.disabled = !isValid;
        
        if (isValid) {
            submitBtn.textContent = '✓ Complete Booking';
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-primary');
        } else {
            submitBtn.innerHTML = '<i class="fas fa-calendar-check"></i> Complete Booking';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-secondary');
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
            input.name = 'facilityIds[]';
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