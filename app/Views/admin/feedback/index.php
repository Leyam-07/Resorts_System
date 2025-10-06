<?php
$pageTitle = "View All Feedback";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4"><?= htmlspecialchars($pageTitle) ?></h1>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <form action="" method="GET" id="resortFilterForm">
                <input type="hidden" name="controller" value="feedback">
                <input type="hidden" name="action" value="listAllFeedback">
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

    <div class="row">
        <div class="col-md-12">

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <!-- Resort Feedbacks Section -->
            <div class="mb-5">
                <h3 class="text-info">Resort Experience Feedback</h3>
                <?php if (empty($resortFeedbacks)): ?>
                    <div class="alert alert-info">No resort feedback has been submitted yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-info">
                                <tr>
                                    <th>Booking Date</th>
                                    <th>Customer</th>
                                    <th>Resort</th>
                                    <th>Experience</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Submitted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resortFeedbacks as $feedback): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(date('F j, Y', strtotime($feedback->BookingDate))) ?></td>
                                        <td><?= htmlspecialchars($feedback->CustomerName) ?></td>
                                        <td><strong><?= htmlspecialchars($feedback->ResortName) ?></strong></td>
                                        <td><?= htmlspecialchars($feedback->FacilityName) ?></td>
                                        <td>
                                            <div class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $feedback->Rating): ?>
                                                        &#9733;
                                                    <?php else: ?>
                                                        &#9734;
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td><?= nl2br(htmlspecialchars($feedback->Comment)) ?></td>
                                        <td><?= htmlspecialchars(date('F j, Y, g:i a', strtotime($feedback->CreatedAt))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Facility Feedbacks Section -->
            <div>
                <h3 class="text-success">Facility-Specific Feedback</h3>
                <?php if (empty($facilityFeedbacks)): ?>
                    <div class="alert alert-info">No facility feedback has been submitted yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-success">
                                <tr>
                                    <th>Booking Date</th>
                                    <th>Customer</th>
                                    <th>Resort</th>
                                    <th>Facility</th>
                                    <th>Rating</th>
                                    <th>Comment</th>
                                    <th>Submitted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($facilityFeedbacks as $feedback): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(date('F j, Y', strtotime($feedback->BookingDate))) ?></td>
                                        <td><?= htmlspecialchars($feedback->CustomerName) ?></td>
                                        <td><strong><?= htmlspecialchars($feedback->ResortName) ?></strong></td>
                                        <td><?= htmlspecialchars($feedback->FacilityName) ?></td>
                                        <td>
                                            <div class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $feedback->Rating): ?>
                                                        &#9733;
                                                    <?php else: ?>
                                                        &#9734;
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td><?= nl2br(htmlspecialchars($feedback->Comment)) ?></td>
                                        <td><?= htmlspecialchars(date('F j, Y, g:i a', strtotime($feedback->CreatedAt))) ?></td>
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

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
