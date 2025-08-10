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
                    <option value="" disabled <?= empty($oldInput['facilityId']) ? 'selected' : '' ?>>Select a facility</option>
                    <?php if (!empty($facilities)): ?>
                        <?php foreach ($facilities as $facility): ?>
                            <option value="<?= htmlspecialchars($facility->facilityId) ?>" <?= (isset($oldInput['facilityId']) && $oldInput['facilityId'] == $facility->facilityId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($facility->name) ?> (Capacity: <?= htmlspecialchars($facility->capacity) ?>)
                            </option>
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
                <label for="startTime" class="form-label">Start Time</label>
                <input type="time" class="form-control" id="startTime" name="startTime" value="<?= htmlspecialchars($oldInput['startTime'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="endTime" class="form-label">End Time</label>
                <input type="time" class="form-control" id="endTime" name="endTime" value="<?= htmlspecialchars($oldInput['endTime'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label for="guests" class="form-label">Number of Guests</label>
                <input type="number" class="form-control" id="guests" name="numberOfGuests" value="<?= htmlspecialchars($oldInput['numberOfGuests'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Book Now</button>
            <a href="?" class="btn btn-secondary">Back to Dashboard</a>
        </form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>