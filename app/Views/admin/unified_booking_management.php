<?php
$pageTitle = "Unified Booking & Payment Management";
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3><i class="fas fa-calendar-check"></i> Unified Booking & Payment Management</h3>
                    <div class="d-flex gap-2">
                        <?php if ($pendingPaymentCount > 0): ?>
                            <a href="?controller=payment&action=showPendingPayments" class="btn btn-warning">
                                <i class="fas fa-exclamation-circle"></i> <?= $pendingPaymentCount ?> Pending Payments
                            </a>
                        <?php endif; ?>
                        <a href="?controller=admin&action=dashboard" class="btn btn-secondary">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body border-bottom">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="controller" value="admin">
                        <input type="hidden" name="action" value="unifiedBookingManagement">
                        
                        <div class="col-md-4">
                            <label class="form-label">Resort Filter</label>
                            <select name="resort_id" class="form-select">
                                <option value="">All Resorts</option>
                                <?php foreach ($resorts as $resort): ?>
                                    <option value="<?= $resort->resortId ?>" 
                                        <?= (isset($_GET['resort_id']) && $_GET['resort_id'] == $resort->resortId) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($resort->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Status Filter</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="Pending" <?= (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="Confirmed" <?= (isset($_GET['status']) && $_GET['status'] == 'Confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                <option value="Completed" <?= (isset($_GET['status']) && $_GET['status'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                                <option value="Cancelled" <?= (isset($_GET['status']) && $_GET['status'] == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="?controller=admin&action=unifiedBookingManagement" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Bookings Table -->
                <div class="card-body">
                    <?php if (empty($bookings)): ?>
                        <div class="text-center p-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No bookings found</h4>
                            <p class="text-muted">No bookings match your current filters.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Date & Time</th>
                                        <th>Customer</th>
                                        <th>Resort</th>
                                        <th>Facilities</th>
                                        <th>Total Amount</th>
                                        <th>Payment Status</th>
                                        <th>Booking Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($booking->BookingID) ?></strong></td>
                                            <td>
                                                <div><?= date('M j, Y', strtotime($booking->BookingDate)) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($booking->TimeSlotType ?? 'N/A') ?></small>
                                            </td>
                                            <td>
                                                <div><?= htmlspecialchars($booking->CustomerName) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($booking->CustomerEmail) ?></small>
                                            </td>
                                            <td><strong><?= htmlspecialchars($booking->ResortName) ?></strong></td>
                                            <td>
                                                <?php if (!empty($booking->FacilityNames)): ?>
                                                    <span class="badge bg-info"><?= htmlspecialchars($booking->FacilityNames) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted small">Resort only</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($booking->TotalAmount)): ?>
                                                    <div><strong>₱<?= number_format($booking->TotalAmount, 2) ?></strong></div>
                                                    <?php if (!empty($booking->RemainingBalance) && $booking->RemainingBalance > 0): ?>
                                                        <small class="text-warning">Bal: ₱<?= number_format($booking->RemainingBalance, 2) ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
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
                                                <span class="badge 
                                                    <?php
                                                        switch ($booking->Status) {
                                                            case 'Confirmed': echo 'bg-success'; break;
                                                            case 'Pending': echo 'bg-warning text-dark'; break;
                                                            case 'Completed': echo 'bg-info'; break;
                                                            case 'Cancelled': echo 'bg-danger'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
                                                    <?= htmlspecialchars($booking->Status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#manageBookingModal"
                                                            data-booking-id="<?= $booking->BookingID ?>"
                                                            data-booking-status="<?= $booking->Status ?>"
                                                            data-total-amount="<?= $booking->TotalAmount ?>"
                                                            data-remaining-balance="<?= $booking->RemainingBalance ?>">
                                                        <i class="fas fa-edit"></i> Manage
                                                    </button>
                                                    <a href="?controller=payment&action=manage&booking_id=<?= $booking->BookingID ?>" 
                                                       class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-credit-card"></i> Payments
                                                    </a>
                                                </div>
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

<!-- Manage Booking Modal -->
<div class="modal fade" id="manageBookingModal" tabindex="-1" aria-labelledby="manageBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="?controller=admin&action=updateBookingPayment">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageBookingModalLabel">Manage Booking & Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modalBookingId" name="booking_id">
                    
                    <div class="mb-3">
                        <label for="bookingStatus" class="form-label">Booking Status</label>
                        <select class="form-select" id="bookingStatus" name="booking_status">
                            <option value="">Keep current status</option>
                            <option value="Pending">Pending</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>

                    <hr>
                    <h6>Add Payment (Optional)</h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paymentAmount" class="form-label">Payment Amount</label>
                                <input type="number" step="0.01" class="form-control" id="paymentAmount" name="payment_amount">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paymentMethod" class="form-label">Payment Method</label>
                                <select class="form-select" id="paymentMethod" name="payment_method">
                                    <option value="">Select method</option>
                                    <option value="Cash">Cash</option>
                                    <option value="GCash">GCash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Credit Card">Credit Card</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentStatus" class="form-label">Payment Status</label>
                        <select class="form-select" id="paymentStatus" name="payment_status">
                            <option value="">Select status</option>
                            <option value="Verified">Verified (Confirmed)</option>
                            <option value="Pending">Pending (Needs Review)</option>
                            <option value="Partial">Partial Payment</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('manageBookingModal');
    
    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        
        // Update modal data
        document.getElementById('modalBookingId').value = button.getAttribute('data-booking-id');
        document.getElementById('bookingStatus').value = button.getAttribute('data-booking-status');
        
        // Update modal title
        document.getElementById('manageBookingModalLabel').textContent = 
            'Manage Booking #' + button.getAttribute('data-booking-id');
    });
});
</script>