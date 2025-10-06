<?php
// Enforce admin-only access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    // Redirect to a 403 Forbidden page or the login page
    header('HTTP/1.0 403 Forbidden');
    include __DIR__ . '/../errors/403.php';
    exit();
}

$pageTitle = "Admin Dashboard";
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
    <!-- Phase 5: Quick Admin Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tools"></i> Quick Management Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="?controller=payment&action=showPendingPayments" class="btn btn-outline-info w-100 mb-2">
                                <i class="fas fa-credit-card"></i><br>
                                <small>Payment Verification</small>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="?controller=admin&action=unifiedBookingManagement" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-calendar-check"></i><br>
                                <small>Unified Booking & Payment</small>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="?controller=admin&action=pricingManagement" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-tags"></i><br>
                                <small>Pricing Management</small>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="?controller=admin&action=advancedBlocking" class="btn btn-outline-warning w-100 mb-2">
                                <i class="fas fa-ban"></i><br>
                                <small>Advanced Blocking</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Monthly Income</div>
                <div class="card-body">
                    <h4 class="card-title">₱<?= number_format($monthlyIncome, 2) ?></h4>
                    <p class="card-text">Income for <?= date('F Y') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Admin Dashboard</h3>
                </div>
                <div class="card-body">
                    <h4 class="mb-4">Today's Bookings (<?= date('F j, Y') ?>)</h4>
                    
                    <?php if (empty($todaysBookings)): ?>
                        <div class="alert alert-info">No bookings scheduled for today.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Time</th>
                                        <th>Customer</th>
                                        <th>Resort</th>
                                        <th>Facilities</th>
                                        <th>Total</th>
                                        <th>Guests</th>
                                        <th>Booking Status</th>
                                        <th>Payment Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todaysBookings as $booking): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($booking->BookingID) ?></td>
                                            <td><?= htmlspecialchars(Booking::getTimeSlotDisplay($booking->TimeSlotType)) ?></td>
                                            <td><?= htmlspecialchars($booking->CustomerName) ?></td>
                                            <td><strong><?= htmlspecialchars($booking->ResortName ?? 'Unknown') ?></strong></td>
                                            <td>
                                                <?php if (!empty($booking->FacilityNames)): ?>
                                                    <span class="badge bg-info text-dark"><?= htmlspecialchars($booking->FacilityNames) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted small">Resort only</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($booking->TotalAmount)): ?>
                                                    <strong>₱<?= number_format($booking->TotalAmount, 2) ?></strong>
                                                    <?php if (!empty($booking->RemainingBalance) && $booking->RemainingBalance > 0): ?>
                                                        <br><small class="text-warning">Bal: ₱<?= number_format($booking->RemainingBalance, 2) ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($booking->NumberOfGuests) ?></td>
                                            <td>
                                                <span class="badge
                                                    <?php
                                                        switch ($booking->Status) {
                                                            case 'Confirmed': echo 'bg-success'; break;
                                                            case 'Pending': echo 'bg-warning text-dark'; break;
                                                            case 'Cancelled': echo 'bg-danger'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
                                                    <?= htmlspecialchars($booking->Status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge
                                                    <?php
                                                        switch ($booking->PaymentStatus) {
                                                            case 'Paid': echo 'bg-success'; break;
                                                            case 'Partial': echo 'bg-warning text-dark'; break;
                                                            case 'Unpaid': echo 'bg-danger'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
                                                    <?= htmlspecialchars($booking->PaymentStatus) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="index.php?controller=payment&action=manage&booking_id=<?= $booking->BookingID ?>" class="btn btn-primary btn-sm">Manage Payments</a>
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
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Upcoming Bookings</h4>
                </div>
                <div class="card-body">
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
                                        <th>Resort</th>
                                        <th>Facilities</th>
                                        <th>Total</th>
                                        <th>Guests</th>
                                        <th>Status</th>
                                        <th>Payment Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingBookings as $booking): ?>
                                        <tr>
                                            <td><?= htmlspecialchars(date('M j, Y', strtotime($booking->BookingDate))) ?></td>
                                            <td><?= htmlspecialchars(Booking::getTimeSlotDisplay($booking->TimeSlotType)) ?></td>
                                            <td><?= htmlspecialchars($booking->CustomerName) ?></td>
                                            <td><strong><?= htmlspecialchars($booking->ResortName ?? 'Unknown') ?></strong></td>
                                            <td>
                                                <?php if (!empty($booking->FacilityNames)): ?>
                                                    <span class="badge bg-info text-dark"><?= htmlspecialchars($booking->FacilityNames) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted small">Resort only</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($booking->TotalAmount)): ?>
                                                    <strong>₱<?= number_format($booking->TotalAmount, 2) ?></strong>
                                                    <?php if (!empty($booking->RemainingBalance) && $booking->RemainingBalance > 0): ?>
                                                        <br><small class="text-warning">Bal: ₱<?= number_format($booking->RemainingBalance, 2) ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($booking->NumberOfGuests) ?></td>
                                            <td>
                                                <span class="badge
                                                    <?php
                                                        switch ($booking->Status) {
                                                            case 'Confirmed': echo 'bg-success'; break;
                                                            case 'Pending': echo 'bg-warning text-dark'; break;
                                                            case 'Cancelled': echo 'bg-danger'; break; // This should not happen for upcoming
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
                                                    <?= htmlspecialchars($booking->Status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge
                                                    <?php
                                                        switch ($booking->PaymentStatus) {
                                                            case 'Paid': echo 'bg-success'; break;
                                                            case 'Partial': echo 'bg-warning text-dark'; break;
                                                            case 'Unpaid': echo 'bg-danger'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
                                                    <?= htmlspecialchars($booking->PaymentStatus) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="index.php?controller=payment&action=manage&booking_id=<?= $booking->BookingID ?>" class="btn btn-primary btn-sm">Manage Payments</a>
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
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Recent Booking History</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($bookingHistory)): ?>
                        <div class="alert alert-info">No past bookings found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Resort</th>
                                        <th>Facilities</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookingHistory as $booking): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($booking->BookingID) ?></td>
                                            <td><?= htmlspecialchars(date('M j, Y', strtotime($booking->BookingDate))) ?></td>
                                            <td><?= htmlspecialchars($booking->CustomerName) ?></td>
                                            <td><strong><?= htmlspecialchars($booking->ResortName ?? 'Unknown') ?></strong></td>
                                            <td>
                                                <?php if (!empty($booking->FacilityNames)): ?>
                                                    <span class="badge bg-info text-dark"><?= htmlspecialchars($booking->FacilityNames) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted small">Resort only</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                 <span class="badge
                                                    <?php
                                                        switch ($booking->Status) {
                                                            case 'Completed': echo 'bg-info'; break;
                                                            case 'Cancelled': echo 'bg-danger'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
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
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
