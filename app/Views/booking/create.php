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

        <form action="?controller=booking&action=createBooking" method="POST">
            <div class="mb-3">
                <label for="resort" class="form-label">Resort</label>
                <select class="form-select" id="resort" name="resortId" required>
                    <option value="" disabled <?= !$selectedResortId ? 'selected' : '' ?>>Select a resort</option>
                    <?php foreach ($resorts as $resort): ?>
                        <option value="<?= $resort->resortId ?>" <?= ($selectedResortId == $resort->resortId) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($resort->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="facility" class="form-label">Facility</label>
                <select class="form-select" id="facility" name="facilityId" required disabled>
                    <option value="" disabled selected>Select a resort first</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="bookingDate" value="<?= htmlspecialchars($_SESSION['old_input']['bookingDate'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="timeSlotType" class="form-label">Time Slot</label>
                <select class="form-select" id="timeSlotType" name="timeSlotType" required>
                    <option value="" disabled selected>Select a time slot</option>
                    <option value="12_hours" <?= (isset($_SESSION['old_input']['timeSlotType']) && $_SESSION['old_input']['timeSlotType'] == '12_hours') ? 'selected' : '' ?>>12 Hours (7 AM to 5 PM)</option>
                    <option value="24_hours" <?= (isset($_SESSION['old_input']['timeSlotType']) && $_SESSION['old_input']['timeSlotType'] == '24_hours') ? 'selected' : '' ?>>24 Hours (7 AM to 5 AM)</option>
                    <option value="overnight" <?= (isset($_SESSION['old_input']['timeSlotType']) && $_SESSION['old_input']['timeSlotType'] == 'overnight') ? 'selected' : '' ?>>Overnight (7 PM to 5 AM)</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="guests" class="form-label">Number of Guests</label>
                <input type="number" class="form-control" id="guests" name="numberOfGuests" value="<?= htmlspecialchars($_SESSION['old_input']['numberOfGuests'] ?? '') ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Book Now</button>
            <a href="?" class="btn btn-secondary">Back to Dashboard</a>
        </form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resortSelect = document.getElementById('resort');
    const facilitySelect = document.getElementById('facility');
    const selectedFacilityId = <?= json_encode($selectedFacilityId ?? $_SESSION['old_input']['facilityId'] ?? null) ?>;

    function fetchFacilities(resortId) {
        if (!resortId) {
            facilitySelect.innerHTML = '<option value="" disabled selected>Select a resort first</option>';
            facilitySelect.disabled = true;
            return;
        }

        fetch(`?controller=booking&action=getFacilitiesByResort&resort_id=${resortId}`)
            .then(response => response.json())
            .then(data => {
                facilitySelect.innerHTML = '<option value="" disabled selected>Select a facility</option>';
                if (data.length > 0) {
                    data.forEach(facility => {
                        const option = new Option(`${facility.name} (Capacity: ${facility.capacity})`, facility.facilityId);
                        if (selectedFacilityId && facility.facilityId == selectedFacilityId) {
                            option.selected = true;
                        }
                        facilitySelect.appendChild(option);
                    });
                    facilitySelect.disabled = false;
                } else {
                    facilitySelect.innerHTML = '<option value="" disabled selected>No facilities available for this resort</option>';
                    facilitySelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error fetching facilities:', error);
                facilitySelect.innerHTML = '<option value="" disabled selected>Error loading facilities</option>';
                facilitySelect.disabled = true;
            });
    }

    resortSelect.addEventListener('change', function() {
        fetchFacilities(this.value);
    });

    // If a resort is already selected on page load (e.g., from URL or old input), fetch its facilities
    if (resortSelect.value) {
        fetchFacilities(resortSelect.value);
    }
});
</script>

<?php 
unset($_SESSION['old_input']);
require_once __DIR__ . '/../partials/footer.php'; 
?>