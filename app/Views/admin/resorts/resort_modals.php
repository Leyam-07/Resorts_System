<?php
// Prevent direct access to this file
if (!defined('APP_LOADED')) {
    http_response_code(403);
    require_once __DIR__ . '/../../errors/403.php';
    exit;
}
?>

<!-- Add Resort Modal -->
<div class="modal fade" id="addResortModal" tabindex="-1" aria-labelledby="addResortModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addResortModalLabel">Add New Resort</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="?controller=resort&action=store" method="POST" id="addResortForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Resort Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label for="contactPerson" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contactPerson" name="contactPerson" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" form="addResortForm">Add Resort</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Resort Modal -->
<div class="modal fade" id="editResortModal" tabindex="-1" aria-labelledby="editResortModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editResortModalLabel">Edit Resort</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="?controller=resort&action=update" method="POST" id="editResortForm">
                    <input type="hidden" id="editResortId" name="resortId">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Resort Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Address</label>
                        <input type="text" class="form-control" id="editAddress" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label for="editContactPerson" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="editContactPerson" name="contactPerson" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" form="editResortForm">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Resort Modal -->
<div class="modal fade" id="deleteResortModal" tabindex="-1" aria-labelledby="deleteResortModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteResortModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this resort? This action cannot be undone and may fail if facilities are associated with it.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="confirmDeleteResortBtn" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>