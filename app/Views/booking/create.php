<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Book a Facility</h1>
        <form action="?action=createBooking" method="POST">
            <div class="mb-3">
                <label for="facility" class="form-label">Facility</label>
                <select class="form-select" id="facility" name="facilityId" required>
                    <option value="" disabled selected>Select a facility</option>
                    <?php if (!empty($facilities)): ?>
                        <?php foreach ($facilities as $facility): ?>
                            <option value="<?= htmlspecialchars($facility->facilityId) ?>">
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
                <input type="date" class="form-control" id="date" name="bookingDate" required>
            </div>
            <div class="mb-3">
                <label for="startTime" class="form-label">Start Time</label>
                <input type="time" class="form-control" id="startTime" name="startTime" required>
            </div>
            <div class="mb-3">
                <label for="endTime" class="form-label">End Time</label>
                <input type="time" class="form-control" id="endTime" name="endTime" required>
            </div>
            <div class="mb-3">
                <label for="guests" class="form-label">Number of Guests</label>
                <input type="number" class="form-control" id="guests" name="numberOfGuests" required>
            </div>
            <button type="submit" class="btn btn-primary">Book Now</button>
        </form>
    </div>
</body>
</html>