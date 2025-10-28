<?php
$pageTitle = "Manage Payments";
require_once __DIR__ . '/../../partials/header.php';
require_once __DIR__ . '/../../../Models/ResortPaymentMethods.php';
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h3>Manage Payments for Booking #<?= htmlspecialchars($booking->bookingId) ?></h3>
        </div>
        <div class="card-body">
            <p><strong>Customer:</strong> <?= htmlspecialchars($customer['Username']) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($booking->bookingDate) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($booking->status) ?></p>
            
            <hr>
            
            <h4>Update Booking Status</h4>
            <form action="index.php?controller=payment&action=updateBookingStatus" method="POST" class="mb-4">
                <input type="hidden" name="booking_id" value="<?= $booking->bookingId ?>">
                <div class="row">
                    <div class="col-md-4">
                        <label for="booking_status" class="form-label">Booking Status</label>
                        <select name="status" id="booking_status" class="form-select">
                            <option value="Pending" <?= $booking->status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Confirmed" <?= $booking->status == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="Cancelled" <?= $booking->status == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            <option value="Completed" <?= $booking->status == 'Completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </div>
            </form>

            <hr>

            <h4>Existing Payments</h4>
            <?php if (empty($payments)): ?>
                <div class="alert alert-info">No payments recorded for this booking yet.</div>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= htmlspecialchars($payment->PaymentID) ?></td>
                                <td>₱<?= htmlspecialchars(number_format($payment->Amount, 2)) ?></td>
                                <td><?= htmlspecialchars($payment->PaymentMethod) ?></td>
                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($payment->PaymentDate))) ?></td>
                                <td><?= htmlspecialchars($payment->Status) ?></td>
                                <td>
                                    <form action="index.php?controller=payment&action=updateStatus" method="POST" class="d-inline">
                                        <input type="hidden" name="payment_id" value="<?= $payment->PaymentID ?>">
                                        <input type="hidden" name="booking_id" value="<?= $booking->bookingId ?>">
                                        <select name="status" class="form-select form-select-sm d-inline w-auto">
                                            <option value="Paid" <?= $payment->Status == 'Paid' ? 'selected' : '' ?>>Paid</option>
                                            <option value="Unpaid" <?= $payment->Status == 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                            <option value="Partial" <?= $payment->Status == 'Partial' ? 'selected' : '' ?>>Partial</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <hr>

            <h4>Add New Payment</h4>
            <form action="index.php?controller=payment&action=add" method="POST">
                <input type="hidden" name="booking_id" value="<?= $booking->bookingId ?>">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select payment method...</option>
                                <?php
                                // Get payment methods for this resort
                                $resortPaymentMethods = ResortPaymentMethods::findByResortId($booking->resortId, true);
                                foreach ($resortPaymentMethods as $method): ?>
                                    <option value="<?= htmlspecialchars($method->MethodType) ?>">
                                       <?= htmlspecialchars($method->MethodType) ?> (<?= htmlspecialchars($method->AccountName) ?> - <?= htmlspecialchars($method->AccountNumber) ?>)
                                    </option>
                                <?php endforeach; ?>
                                <option value="On-Site Payment">On-Site Payment</option>
                            </select>
                            <div class="form-text small">Choose from configured payment methods for this resort</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Paid">Paid</option>
                                <option value="Unpaid" selected>Unpaid</option>
                                <option value="Partial">Partial</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 align-self-end">
                        <div class="mb-3">
                            <button type="submit" class="btn btn-success">Add Payment</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer">
             <a href="index.php?controller=admin&action=dashboard" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
