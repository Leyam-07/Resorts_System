<?php
$pageTitle = "Dashboard";
require_once __DIR__ . '/partials/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Customer Dashboard</h5>
                    <p class="card-text">Welcome to your personal dashboard.</p>
                </div>
            </div>
        </div>
    </div>

    <h1 class="text-center mb-4">Our Facilities</h1>
    <div class="row">
        <?php if (empty($facilities)): ?>
            <p class="text-center">No facilities available at the moment.</p>
        <?php else: ?>
            <?php foreach ($facilities as $facility): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?= $facility->mainPhotoURL ? BASE_URL . '/' . htmlspecialchars($facility->mainPhotoURL) : 'https://via.placeholder.com/300x200' ?>" class="card-img-top" alt="<?= htmlspecialchars($facility->name) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($facility->name) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($facility->shortDescription) ?></p>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#facilityModal" data-facility-id="<?= $facility->facilityId ?>">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Facility Modal -->
<div class="modal fade" id="facilityModal" tabindex="-1" aria-labelledby="facilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="facilityModalLabel">Facility Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded here via JavaScript -->
            </div>
            <div class="modal-footer">
                <!-- Button will be loaded here via JavaScript -->
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>