<?php
$pageTitle = "Unified Management";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Unified Resort & Facility Management</h3>
                    <button class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#addResortModal">
                        <i class="fas fa-plus"></i> Add New Resort
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($resortsWithFacilities)): ?>
                        <div class="text-center p-4">
                            <h4>No resorts found.</h4>
                            <p>Get started by adding your first resort.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResortModal">
                                Add Your First Resort
                            </button>
                        </div>
                    <?php else: ?>
                    <div class="accordion" id="resortsAccordion">
                        <?php foreach ($resortsWithFacilities as $index => $resortData): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?= $resortData['resort']->resortId ?>">
                                    <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $resortData['resort']->resortId ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $resortData['resort']->resortId ?>">
                                        <strong><?= htmlspecialchars($resortData['resort']->name) ?></strong>
                                    </button>
                                </h2>
                                <div id="collapse<?= $resortData['resort']->resortId ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $resortData['resort']->resortId ?>" data-bs-parent="#resortsAccordion">
                                    <div class="accordion-body">
                                        <div class="d-flex justify-content-end mb-3">
                                            <button class="btn btn-secondary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#scheduleResortModal" data-resort-id="<?= $resortData['resort']->resortId ?>">
                                                Manage Schedule
                                            </button>
                                            <button class="btn btn-warning btn-sm edit-resort-btn" data-bs-toggle="modal" data-bs-target="#editResortModal" data-resort-id="<?= $resortData['resort']->resortId ?>">Edit Resort</button>
                                            <button class="btn btn-danger btn-sm ms-2 delete-resort-btn" data-bs-toggle="modal" data-bs-target="#deleteResortModal" data-resort-id="<?= $resortData['resort']->resortId ?>">Delete Resort</button>
                                        </div>
                                        <h5>Facilities</h5>
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Capacity</th>
                                                    <th>Rate</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($resortData['facilities'])): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No facilities found for this resort.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($resortData['facilities'] as $facility): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($facility->facilityId) ?></td>
                                                            <td><?= htmlspecialchars($facility->name) ?></td>
                                                            <td><?= htmlspecialchars($facility->capacity) ?></td>
                                                            <td>$<?= htmlspecialchars(number_format($facility->rate, 2)) ?></td>
                                                            <td>
                                                                <button class="btn btn-outline-primary btn-sm">Edit</button>
                                                                <button class="btn btn-outline-danger btn-sm">Delete</button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?php
// The APP_LOADED constant is already defined in public/index.php
// Including the resort modals, which now handle add, edit, and delete
require_once __DIR__ . '/../resorts/resort_modals.php';
?>


<!-- Resort Schedule Modal -->
<div class="modal fade" id="scheduleResortModal" tabindex="-1" aria-labelledby="scheduleResortModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleResortModalLabel">Manage Resort Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Schedule Modal Handler
    var scheduleModal = document.getElementById('scheduleResortModal');
    if(scheduleModal) {
        scheduleModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var resortId = button.getAttribute('data-resort-id');
            var modalBody = scheduleModal.querySelector('.modal-body');
            modalBody.innerHTML = 'Loading...';

            // This is a simplified example. A real implementation would fetch existing blocks via AJAX.
            modalBody.innerHTML = `
                <h4>Block Availability</h4>
                <form action="?controller=admin&action=blockResortAvailability" method="POST">
                    <input type="hidden" name="resortId" value="${resortId}">
                    <div class="mb-3">
                        <label for="blockDate" class="form-label">Date</label>
                        <input type="date" class="form-control" id="blockDate" name="blockDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason (Optional)</label>
                        <input type="text" class="form-control" id="reason" name="reason">
                    </div>
                    <button type="submit" class="btn btn-danger">Block Date</button>
                </form>
                <hr>
                <h4>Existing Blocks</h4>
                <p><em>For a full implementation, this area would be populated by an AJAX call to fetch and list existing blocks for this resort, with a delete button for each.</em></p>
            `;
        });
    }

    // Edit Resort Modal Handler
    var editResortModal = document.getElementById('editResortModal');
    if(editResortModal) {
        editResortModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var resortId = button.getAttribute('data-resort-id');
            
            fetch('?controller=admin&action=getResortJson&id=' + resortId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        document.getElementById('editResortId').value = data.resortId;
                        document.getElementById('editName').value = data.name;
                        document.getElementById('editAddress').value = data.address;
                        document.getElementById('editContactPerson').value = data.contactPerson;
                        document.getElementById('editShortDescription').value = data.shortDescription || '';
                        document.getElementById('editFullDescription').value = data.fullDescription || '';
                        
                        var photoPreview = document.getElementById('currentMainPhoto');
                        if (data.mainPhotoURL) {
                            // The path from the DB is /public/uploads/..., we need uploads/... for the src attribute
                            const imagePath = data.mainPhotoURL.replace('/public/', '');
                            photoPreview.src = imagePath;
                            photoPreview.style.display = 'block';
                        } else {
                            photoPreview.style.display = 'none';
                        }
                    }
                });
        });
    }

    // Delete Resort Modal Handler
    var deleteResortModal = document.getElementById('deleteResortModal');
    if(deleteResortModal) {
        deleteResortModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var resortId = button.getAttribute('data-resort-id');
            var confirmBtn = document.getElementById('confirmDeleteResortBtn');
            confirmBtn.href = '?controller=admin&action=destroyResort&id=' + resortId;
        });
    }
});
</script>