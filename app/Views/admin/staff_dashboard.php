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
                        <thead class="table-info">
                            <tr>
                                <th>Time</th>
                                <th>Customer</th>
                                <th>Facility</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todaysBookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('g:i A', strtotime($booking->StartTime))) . ' - ' . htmlspecialchars(date('g:i A', strtotime($booking->EndTime))) ?></td>
                                    <td><?= htmlspecialchars($booking->CustomerName) ?></td>
                                    <td><?= htmlspecialchars($booking->FacilityName) ?></td>
                                    <td>
                                        <span class="badge bg-success">
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
                        <thead class="table-info">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Customer</th>
                                <th>Facility</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingBookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('M j, Y', strtotime($booking->BookingDate))) ?></td>
                                    <td><?= htmlspecialchars(date('g:i A', strtotime($booking->StartTime))) . ' - ' . htmlspecialchars(date('g:i A', strtotime($booking->EndTime))) ?></td>
                                    <td><?= htmlspecialchars($booking->CustomerName) ?></td>
                                    <td><?= htmlspecialchars($booking->FacilityName) ?></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?= htmlspecialchars($booking->Status) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
