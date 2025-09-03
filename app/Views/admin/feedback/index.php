<?php
$pageTitle = "View All Feedback";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <?php if (empty($feedbacks)): ?>
                <div class="alert alert-info">No feedback has been submitted yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Booking Date</th>
                                <th>Customer</th>
                                <th>Facility</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Submitted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedbacks as $feedback): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('F j, Y', strtotime($feedback->BookingDate))) ?></td>
                                    <td><?= htmlspecialchars($feedback->CustomerName) ?></td>
                                    <td><?= htmlspecialchars($feedback->FacilityName) ?></td>
                                    <td><?= htmlspecialchars($feedback->Rating) ?> &starf;</td>
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

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>