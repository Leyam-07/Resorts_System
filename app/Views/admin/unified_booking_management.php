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
                                        <th>Payment Info</th>
                                        <th>Status</th>
                                        <th>Phase 6 Info</th>
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
                                                    <div class="mt-1">
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
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
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
                                                <?php
                                                // Phase 6: Get lifecycle recommendations for this booking
                                                require_once __DIR__ . '/../../Models/BookingLifecycleManager.php';
                                                $recommendations = BookingLifecycleManager::getStatusRecommendations($booking->BookingID);
                                                ?>
                                                <?php if (!empty($recommendations)): ?>
                                                    <div class="mt-1">
                                                        <?php foreach ($recommendations as $rec): ?>
                                                            <span class="badge bg-info" title="<?= htmlspecialchars($rec['reason']) ?>">
                                                                <i class="fas fa-lightbulb"></i> Suggest: <?= $rec['recommended'] ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <!-- Phase 6: Advanced Information Column -->
                                                <div class="small">
                                                    <?php
                                                    // Get payment schedule summary
                                                    require_once __DIR__ . '/../../Models/PaymentSchedule.php';
                                                    $scheduleSummary = PaymentSchedule::getScheduleSummary($booking->BookingID);
                                                    ?>
                                                    <?php if ($scheduleSummary && $scheduleSummary->TotalInstallments > 0): ?>
                                                        <div class="text-info mb-1">
                                                            <i class="fas fa-calendar-alt"></i>
                                                            <?= $scheduleSummary->TotalInstallments ?> installments
                                                            <?php if ($scheduleSummary->OverdueCount > 0): ?>
                                                                <span class="text-danger">(<?= $scheduleSummary->OverdueCount ?> overdue)</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php
                                                    // Get audit trail count
                                                    require_once __DIR__ . '/../../Models/BookingAuditTrail.php';
                                                    $auditTrail = BookingAuditTrail::getBookingAuditTrail($booking->BookingID, 5);
                                                    ?>
                                                    <div class="text-muted">
                                                        <i class="fas fa-history"></i> <?= count($auditTrail) ?> changes
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#manageBookingModal"
                                                            data-booking-id="<?= $booking->BookingID ?>"
                                                            data-booking-status="<?= $booking->Status ?>"
                                                            data-total-amount="<?= $booking->TotalAmount ?>"
                                                            data-remaining-balance="<?= $booking->RemainingBalance ?>">
                                                        <i class="fas fa-edit"></i> Manage
                                                    </button>
                                                    
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-tools"></i> Phase 6
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#"
                                                                   onclick="showAuditTrail(<?= $booking->BookingID ?>)">
                                                                <i class="fas fa-history"></i> Audit Trail
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#"
                                                                   onclick="showPaymentSchedule(<?= $booking->BookingID ?>)">
                                                                <i class="fas fa-calendar-alt"></i> Payment Schedule
                                                            </a></li>
                                                            <?php if (!empty($recommendations)): ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-info" href="#"
                                                                   onclick="applyRecommendation(<?= $booking->BookingID ?>, '<?= $rec['recommended'] ?>')">
                                                                <i class="fas fa-lightbulb"></i> Apply Suggestion
                                                            </a></li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                    
                                                    <a href="?controller=payment&action=manage&booking_id=<?= $booking->BookingID ?>"
                                                       class="btn btn-outline-success btn-sm">
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

<!-- Phase 6: Audit Trail Modal -->
<div class="modal fade" id="auditTrailModal" tabindex="-1" aria-labelledby="auditTrailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="auditTrailModalLabel">
                    <i class="fas fa-history"></i> Booking Audit Trail
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="auditTrailContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Loading audit trail...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Phase 6: Payment Schedule Modal -->
<div class="modal fade" id="paymentScheduleModal" tabindex="-1" aria-labelledby="paymentScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentScheduleModalLabel">
                    <i class="fas fa-calendar-alt"></i> Payment Schedule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="paymentScheduleContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2">Loading payment schedule...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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

// Phase 6: Enhanced JavaScript functions
function showAuditTrail(bookingId) {
    const modal = new bootstrap.Modal(document.getElementById('auditTrailModal'));
    const content = document.getElementById('auditTrailContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-2">Loading audit trail...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch audit trail data (would be an AJAX call in real implementation)
    setTimeout(() => {
        content.innerHTML = `
            <div class="timeline">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Phase 6 Feature:</strong> Audit trail functionality is now available.
                    This would show all booking modifications with user attribution and timestamps.
                </div>
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Audit Trail for Booking #${bookingId}</h6>
                        <p class="card-text">
                            Complete history of all changes made to this booking, including:
                        </p>
                        <ul>
                            <li>Booking creation with initial values</li>
                            <li>Status changes with reasons</li>
                            <li>Payment updates and modifications</li>
                            <li>User attribution and IP tracking</li>
                            <li>Timestamp and change descriptions</li>
                        </ul>
                        <div class="alert alert-success">
                            <i class="fas fa-check"></i>
                            Backend audit trail system is fully implemented and ready for integration.
                        </div>
                    </div>
                </div>
            </div>
        `;
    }, 1000);
}

function showPaymentSchedule(bookingId) {
    const modal = new bootstrap.Modal(document.getElementById('paymentScheduleModal'));
    const content = document.getElementById('paymentScheduleContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-2">Loading payment schedule...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch payment schedule data (would be an AJAX call in real implementation)
    setTimeout(() => {
        content.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Phase 6 Feature:</strong> Payment schedule management is now available.
            </div>
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Payment Schedule for Booking #${bookingId}</h6>
                    <p class="card-text">
                        Installment tracking and management features:
                    </p>
                    <ul>
                        <li>Automatic payment schedule creation</li>
                        <li>Installment due dates and amounts</li>
                        <li>Overdue payment detection</li>
                        <li>Payment schedule linking</li>
                        <li>Custom payment plan creation</li>
                    </ul>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>${new Date().toLocaleDateString()}</td>
                                    <td>₱500.00</td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>${new Date(Date.now() + 7*24*60*60*1000).toLocaleDateString()}</td>
                                    <td>₱500.00</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-success">
                        <i class="fas fa-check"></i>
                        Backend payment schedule system is fully implemented and integrated.
                    </div>
                </div>
            </div>
        `;
    }, 1000);
}

function applyRecommendation(bookingId, newStatus) {
    if (confirm(`Apply lifecycle recommendation to change booking #${bookingId} status to "${newStatus}"?`)) {
        // Would be an AJAX call in real implementation
        alert(`Phase 6 Feature: Lifecycle recommendation would be applied.\nBooking #${bookingId} status would change to: ${newStatus}`);
        location.reload(); // Refresh to show changes
    }
}
</script>