<?php
// Prevent direct access to this file
if (!defined('APP_LOADED')) {
    http_response_code(403);
    require_once __DIR__ . '/../../errors/403.php';
    exit;
}

$pageTitle = "Manage Resorts";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage Resorts</h3>
                    <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addResortModal">
                        Add New Resort
                    </button>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'resort_added'): ?>
                        <div class="alert alert-success">Resort added successfully!</div>
                    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'resort_updated'): ?>
                        <div class="alert alert-success">Resort updated successfully!</div>
                    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'resort_deleted'): ?>
                        <div class="alert alert-success">Resort deleted successfully!</div>
                    <?php elseif (isset($_GET['error']) && $_GET['error'] == 'delete_has_facilities'): ?>
                        <div class="alert alert-danger">Cannot delete resort. It has associated facilities.</div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Contact Person</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($resorts)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No resorts found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($resorts as $resort): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($resort->resortId) ?></td>
                                            <td><?= htmlspecialchars($resort->name) ?></td>
                                            <td><?= htmlspecialchars($resort->address) ?></td>
                                            <td><?= htmlspecialchars($resort->contactPerson) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-warning edit-resort-btn" data-bs-toggle="modal" data-bs-target="#editResortModal" data-resort-id="<?= $resort->resortId ?>">
                                                    Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger delete-resort-btn" data-bs-toggle="modal" data-bs-target="#deleteResortModal" data-delete-url="?controller=resort&action=destroy&id=<?= $resort->resortId ?>">
                                                    Delete
                                                </button>
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

<?php require_once __DIR__ . '/resort_modals.php'; ?>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
