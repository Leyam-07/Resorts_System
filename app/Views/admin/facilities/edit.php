<?php
$pageTitle = "Edit Facility";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Facility</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">Failed to update facility. Please try again.</div>
                    <?php endif; ?>

                    <form action="?controller=admin&action=editFacility&id=<?= htmlspecialchars($facility->facilityId) ?>" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Facility Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($facility->name) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacity</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" value="<?= htmlspecialchars($facility->capacity) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="rate" class="form-label">Rate (per hour)</label>
                            <input type="number" step="0.01" class="form-control" id="rate" name="rate" value="<?= htmlspecialchars($facility->rate) ?>" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Facility</button>
                            <a href="?controller=admin&action=facilities" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>