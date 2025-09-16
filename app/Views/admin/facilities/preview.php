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
                            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#resortModal" data-resort-id="<?= $resort->resortId ?>">
                                View Details (Preview)
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Resort Modal -->
<div class="modal fade" id="resortModal" tabindex="-1" aria-labelledby="resortModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resortModalLabel">Resort Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <ul class="nav nav-tabs" id="resortTab" role="tablist">
                   <li class="nav-item" role="presentation">
                       <button class="nav-link active" id="resort-details-tab" data-bs-toggle="tab" data-bs-target="#resort-details-content" type="button" role="tab" aria-controls="resort-details-content" aria-selected="true">Resort</button>
                   </li>
                   <li class="nav-item" role="presentation">
                       <button class="nav-link" id="resort-facilities-tab" data-bs-toggle="tab" data-bs-target="#resort-facilities-content" type="button" role="tab" aria-controls="resort-facilities-content" aria-selected="false">Facilities</button>
                   </li>
                   <li class="nav-item" role="presentation">
                       <button class="nav-link" id="resort-feedback-tab" data-bs-toggle="tab" data-bs-target="#resort-feedback-content" type="button" role="tab" aria-controls="resort-feedback-content" aria-selected="false">Feedback</button>
                   </li>
               </ul>
               <div class="tab-content pt-3" id="resortTabContent">
                   <div class="tab-pane fade show active" id="resort-details-content" role="tabpanel" aria-labelledby="resort-details-tab">
                       <!-- Resort Details content will be loaded here by JavaScript -->
                   </div>
                   <div class="tab-pane fade" id="resort-facilities-content" role="tabpanel" aria-labelledby="resort-facilities-tab">
                       <!-- Resort Facilities content will be loaded here by JavaScript -->
                   </div>
                   <div class="tab-pane fade" id="resort-feedback-content" role="tabpanel" aria-labelledby="resort-feedback-tab">
                       <!-- Resort Feedback content will be loaded here by JavaScript -->
                   </div>
               </div>
            </div>
            <div class="modal-footer">
                <!-- Buttons will be loaded here by JavaScript -->
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>

<!-- Facility Modal -->
<div class="modal fade" id="facilityModal" tabindex="-1" aria-labelledby="facilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="facilityModalLabel">Facility Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="facilityTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-content" type="button" role="tab" aria-controls="details-content" aria-selected="true">Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="feedback-tab" data-bs-toggle="tab" data-bs-target="#feedback-content" type="button" role="tab" aria-controls="feedback-content" aria-selected="false">Feedback</button>
                    </li>
                </ul>
                <div class="tab-content pt-3" id="facilityTabContent">
                    <div class="tab-pane fade show active" id="details-content" role="tabpanel" aria-labelledby="details-tab">
                        <!-- Details content will be loaded here by JavaScript -->
                    </div>
                    <div class="tab-pane fade" id="feedback-content" role="tabpanel" aria-labelledby="feedback-tab">
                        <!-- Feedback content will be loaded here by JavaScript -->
                    </div>
                </div>
             </div>
            <div class="modal-footer">
                <!-- Buttons will be loaded here via JavaScript -->
            </div>
        </div>
    </div>
</div>