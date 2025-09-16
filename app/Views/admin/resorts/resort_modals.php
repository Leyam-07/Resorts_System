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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addResortModalLabel">Add New Resort</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="?controller=admin&action=storeResort" method="POST" id="addResortForm" enctype="multipart/form-data">
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
                    <div class="mb-3">
                        <label for="shortDescription" class="form-label">Short Description</label>
                        <textarea class="form-control" id="shortDescription" name="shortDescription" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="fullDescription" class="form-label">Full Description</label>
                        <textarea class="form-control" id="fullDescription" name="fullDescription" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="photos" class="form-label">Upload Photos</label>
                        <input type="file" class="form-control" id="photos" name="photos[]" multiple>
                        <small class="form-text text-muted">You can select multiple files. The first photo uploaded will be set as the main photo.</small>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editResortModalLabel">Edit Resort</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="?controller=admin&action=updateResort" method="POST" id="editResortForm" enctype="multipart/form-data">
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
                    <div class="mb-3">
                        <label for="editShortDescription" class="form-label">Short Description</label>
                        <textarea class="form-control" id="editShortDescription" name="shortDescription" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editFullDescription" class="form-label">Full Description</label>
                        <textarea class="form-control" id="editFullDescription" name="fullDescription" rows="4"></textarea>
                    </div>
                    <hr>
                    <h5>Manage Photos</h5>
                    <div id="photoGallery" class="mb-3">
                        <!-- Photo gallery will be populated by JavaScript -->
                    </div>
                     <div class="mb-3">
                        <label for="newPhotos" class="form-label">Upload New Photos</label>
                        <input type="file" class="form-control" id="newPhotos" name="photos[]" multiple>
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