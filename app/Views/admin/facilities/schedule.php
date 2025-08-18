<?php
$pageTitle = "Manage " . htmlspecialchars($facility->name) . " Schedule";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage Schedule for <?= htmlspecialchars($facility->name) ?></h3>
                </div>
                <div class="card-body">
                    <h4>Block a Time Slot</h4>
                    <form action="?controller=admin&action=blockTime" method="POST" class="row g-3">
                        <input type="hidden" name="facilityId" value="<?= $facility->facilityId ?>">
                        <div class="col-md-3">
                            <label for="blockDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="blockDate" name="blockDate" required>
                        </div>
                        <div class="col-md-3">
                            <label for="startTime" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="startTime" name="startTime" required>
                        </div>
                        <div class="col-md-3">
                            <label for="endTime" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="endTime" name="endTime" required>
                        </div>
                        <div class="col-md-3">
                            <label for="reason" class="form-label">Reason</label>
                            <input type="text" class="form-control" id="reason" name="reason" placeholder="e.g., Maintenance">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-danger">Block Time</button>
                        </div>
                    </form>

                    <hr>

                    <h4>Currently Blocked Time Slots</h4>
                    <?php if (empty($blockedSlots)): ?>
                        <div class="alert alert-info">No time slots are currently blocked for this facility.</div>
                    <?php else: ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Reason</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blockedSlots as $slot): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($slot->blockDate) ?></td>
                                        <td><?= htmlspecialchars(date('g:i A', strtotime($slot->startTime))) ?> - <?= htmlspecialchars(date('g:i A', strtotime($slot->endTime))) ?></td>
                                        <td><?= htmlspecialchars($slot->reason) ?></td>
                                        <td>
                                            <a href="?controller=admin&action=unblockTime&id=<?= $slot->blockedAvailabilityId ?>&facilityId=<?= $facility->facilityId ?>" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to unblock this time slot?');">Unblock</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>