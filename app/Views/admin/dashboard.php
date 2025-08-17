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

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
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
                                        <th>Facility</th>
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
                                            <td><?= htmlspecialchars(date('g:i A', strtotime($booking->StartTime))) . ' - ' . htmlspecialchars(date('g:i A', strtotime($booking->EndTime))) ?></td>
                                            <td><?= htmlspecialchars($booking->CustomerName) ?></td>
                                            <td><?= htmlspecialchars($booking->FacilityName) ?></td>
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
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>