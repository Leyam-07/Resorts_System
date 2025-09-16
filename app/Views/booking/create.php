<?php
$pageTitle = "Book a Facility";
require_once __DIR__ . '/../partials/header.php';
?>

        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <form action="?controller=booking&action=createBooking" method="POST">
            <div class="mb-3">
                <label for="facility" class="form-label">Facility</label>
                <select class="form-select" id="facility" name="facilityId" required>
                    <option value="" disabled <?= empty($oldInput['facilityId']) && empty($selectedFacilityId) ? 'selected' : '' ?>>Select a facility</option>
                    <?php if (!empty($resortsWithFacilities)): ?>
                        <?php foreach ($resortsWithFacilities as $resortData): ?>
                            <optgroup label="<?= htmlspecialchars($resortData['resort']->name) ?>">
                                <?php foreach ($resortData['facilities'] as $facility): ?>
                                    <?php
                                        // Determine if this option should be selected
                                        $isSelected = (isset($oldInput['facilityId']) && $oldInput['facilityId'] == $facility->facilityId) ||
                                                      (!isset($oldInput['facilityId']) && isset($selectedFacilityId) && $selectedFacilityId == $facility->facilityId);
                                    ?>
                                    <option value="<?= htmlspecialchars($facility->facilityId) ?>" <?= $isSelected ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($facility->name) ?> (Capacity: <?= htmlspecialchars($facility->capacity) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No facilities available</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="bookingDate" value="<?= htmlspecialchars($oldInput['bookingDate'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="timeSlotType" class="form-label">Time Slot</label>
                <select class="form-select" id="timeSlotType" name="timeSlotType" required>
                    <option value="" disabled selected>Select a time slot</option>
                    <option value="12_hours" <?= (isset($oldInput['timeSlotType']) && $oldInput['timeSlotType'] == '12_hours') ? 'selected' : '' ?>>12 Hours (7 AM to 5 PM)</option>
                    <option value="24_hours" <?= (isset($oldInput['timeSlotType']) && $oldInput['timeSlotType'] == '24_hours') ? 'selected' : '' ?>>24 Hours (7 AM to 5 AM)</option>
                    <option value="overnight" <?= (isset($oldInput['timeSlotType']) && $oldInput['timeSlotType'] == 'overnight') ? 'selected' : '' ?>>Overnight (7 PM to 5 AM)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="guests" class="form-label">Number of Guests</label>
                <input type="number" class="form-control" id="guests" name="numberOfGuests" value="<?= htmlspecialchars($oldInput['numberOfGuests'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Book Now</button>
            <a href="?" class="btn btn-secondary">Back to Dashboard</a>
        </form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>