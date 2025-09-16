<!-- Add Facility Modal -->
<div class="modal fade" id="addFacilityModal" tabindex="-1" aria-labelledby="addFacilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFacilityModalLabel">Add New Facility</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="?controller=admin&action=addFacility" method="POST" id="addFacilityForm" enctype="multipart/form-data">
                    <input type="hidden" name="resortId" id="addFacilityResortId">
                    <div class="mb-3">
                        <label for="name" class="form-label">Facility Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="capacity" class="form-label">Capacity</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" required>
                    </div>
                    <div class="mb-3">
                        <label for="rate" class="form-label">Rate per Slot ($)</label>
                        <input type="number" step="0.01" class="form-control" id="rate" name="rate" required>
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
                        <label for="facilityPhotos" class="form-label">Upload Photos</label>
                        <input type="file" class="form-control" id="facilityPhotos" name="photos[]" multiple>
                        <small class="form-text text-muted">You can select multiple files. The first photo uploaded will be set as the main photo.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="addFacilityForm">Save Facility</button>
            </div>
        </div>
    </div>
</div>