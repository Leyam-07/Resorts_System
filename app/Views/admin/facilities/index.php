<?php
$pageTitle = "Manage Facilities";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage Facilities</h3>
                    <a href="?controller=admin&action=addFacility" class="btn btn-primary float-end">Add New Facility</a>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'facility_added'): ?>
                        <div class="alert alert-success">Facility added successfully!</div>
                    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'facility_updated'): ?>
                        <div class="alert alert-success">Facility updated successfully!</div>
                    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'facility_deleted'): ?>
                        <div class="alert alert-success">Facility deleted successfully!</div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Capacity</th>
                                    <th>Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($facilities)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No facilities found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($facilities as $facility): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($facility->facilityId) ?></td>
                                            <td><?= htmlspecialchars($facility->name) ?></td>
                                            <td><?= htmlspecialchars($facility->capacity) ?></td>
                                            <td><?= htmlspecialchars(number_format($facility->rate, 2)) ?></td>
                                            <td>
                                                <a href="?controller=admin&action=schedule&id=<?= $facility->facilityId ?>" class="btn btn-sm btn-info">Manage Schedule</a>
                                                <a href="?controller=admin&action=editFacility&id=<?= $facility->facilityId ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="?controller=admin&action=deleteFacility&id=<?= $facility->facilityId ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this facility?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>