<?php
// Enforce staff-only access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Staff', 'Admin'])) {
    // Redirect to a 403 Forbidden page or the login page
    header('HTTP/1.0 403 Forbidden');
    include __DIR__ . '/../errors/403.php';
    exit();
}

$pageTitle = "Staff Dashboard";
require_once __DIR__ . '/../partials/header.php';
?>

<div class="row mb-3">
    <div class="col-md-4">
        <form action="" method="GET" id="resortFilterForm">
            <input type="hidden" name="controller" value="admin">
            <input type="hidden" name="action" value="dashboard">
            <select name="resort_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Resorts</option>
                <?php foreach ($resorts as $resort): ?>
                    <option value="<?= $resort->resortId ?>" <?= (isset($_GET['resort_id']) && $_GET['resort_id'] == $resort->resortId) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($resort->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Staff Dashboard</h3>
        </div>
        <div class="card-body">
            <h4 class="mb-4">Today's Bookings (<?= date('F j, Y') ?>)</h4>
            
            <?php if (empty($todaysBookings)): ?>
                <div class="alert alert-info">No bookings scheduled for today.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Customer</th>
                                <th>Facilities</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todaysBookings as $booking): ?>
                                <?php
                                $statusColors = [
                                    'Pending' => 'bg-warning text-dark',
                                    'Confirmed' => 'bg-success',
                                    'Cancelled' => 'bg-danger',
                                    'Completed' => 'bg-primary'
                                ];
                                $statusClass = $statusColors[$booking->Status] ?? 'bg-secondary';

                                $timeSlotDisplay = [
                                    '12_hours' => '12 Hours (7:00 AM - 5:00 PM)',
                                    'overnight' => 'Overnight (7:00 PM - 5:00 AM)',
                                    '24_hours' => '24 Hours (7:00 AM - 5:00 AM)'
                                ];
                                $timeDisplay = $timeSlotDisplay[$booking->TimeSlotType] ?? 'N/A';
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($timeDisplay) ?></td>
                                    <td><?= htmlspecialchars($booking->CustomerName) ?></td>
                                    <td>
                                        <?php if (!empty($booking->FacilityNames)): ?>
                                            <span class="badge bg-info text-dark"><?= htmlspecialchars($booking->FacilityNames) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Resort access only</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $statusClass ?>">
                                            <?= htmlspecialchars($booking->Status) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <h4 class="mb-4 mt-5">Upcoming Bookings</h4>
            
            <?php if (empty($upcomingBookings)): ?>
                <div class="alert alert-info">No upcoming bookings found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Customer</th>
                                <th>Facilities</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="upcoming-bookings-tbody">
                            <?php foreach ($upcomingBookings as $index => $booking): ?>
                                <?php
                                $statusColors = [
                                    'Pending' => 'bg-warning text-dark',
                                    'Confirmed' => 'bg-success',
                                    'Cancelled' => 'bg-danger',
                                    'Completed' => 'bg-primary'
                                ];
                                $statusClass = $statusColors[$booking->Status] ?? 'bg-secondary';

                                $timeSlotDisplay = [
                                    '12_hours' => '12 Hours (7:00 AM - 5:00 PM)',
                                    'overnight' => 'Overnight (7:00 PM - 5:00 AM)',
                                    '24_hours' => '24 Hours (7:00 AM - 5:00 AM)'
                                ];
                                $timeDisplay = $timeSlotDisplay[$booking->TimeSlotType] ?? 'N/A';
                                ?>
                                <tr class="booking-row" style="<?= $index >= 10 ? 'display: none;' : '' ?>">
                                    <td><?= htmlspecialchars(date('M j, Y', strtotime($booking->BookingDate))) ?></td>
                                    <td><?= htmlspecialchars($timeDisplay) ?></td>
                                    <td><?= htmlspecialchars($booking->CustomerName) ?></td>
                                    <td>
                                        <?php if (!empty($booking->FacilityNames)): ?>
                                            <span class="badge bg-info text-dark"><?= htmlspecialchars($booking->FacilityNames) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Resort access only</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $statusClass ?>">
                                            <?= htmlspecialchars($booking->Status) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (count($upcomingBookings) > 10): ?>
                <div class="text-center mt-3">
                    <button id="view-more-btn" class="btn btn-primary">View More</button>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewMoreBtn = document.getElementById('view-more-btn');
    if (viewMoreBtn) {
        viewMoreBtn.addEventListener('click', function() {
            const hiddenRows = document.querySelectorAll('#upcoming-bookings-tbody .booking-row[style*="display: none;"]');
            hiddenRows.forEach(row => {
                row.style.display = '';
            });
            viewMoreBtn.style.display = 'none'; // Hide the button after showing all rows
        });
    }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
