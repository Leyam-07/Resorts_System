<?php
$pageTitle = "Preview Customer View";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="container mt-5">
    <div class="alert alert-info">
        <strong>Admin Preview Mode:</strong> This is how the customer dashboard currently looks.
    </div>

    <h2 class="text-center mb-4">Our Resorts</h2>

    <?php if (empty($resorts)): ?>
        <p class="text-center">No resorts available at the moment.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($resorts as $resort): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?= $resort->mainPhotoURL ? BASE_URL . '/' . htmlspecialchars($resort->mainPhotoURL) : 'https://via.placeholder.com/300x200' ?>" class="card-img-top" alt="<?= htmlspecialchars($resort->name) ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($resort->name) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($resort->shortDescription) ?></p>
                        </div>
                        <div class="card-footer">
                            <a href="?controller=booking&action=showBookingForm&resort_id=<?= $resort->resortId ?>" class="btn btn-primary w-100 disabled" aria-disabled="true">
                                View Resort & Book (Disabled in Preview)
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>