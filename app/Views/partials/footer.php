</div> <!--- Close container -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<style>
    #main-photo-container {
        height: 400px;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: .25rem;
        overflow: hidden;
    }
    #main-photo-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .thumbnail-gallery {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 10px 0;
    }
    .thumbnail-gallery img {
        width: 100px;
        height: 75px;
        object-fit: cover;
        cursor: pointer;
        border-radius: .25rem;
        border: 2px solid transparent;
        transition: border-color 0.2s ease-in-out;
    }
    .thumbnail-gallery img:hover, .thumbnail-gallery img.active {
        border-color: #0d6efd;
    }
</style>
<script>
    const BASE_URL = '<?= BASE_URL ?>';
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var facilityModal = document.getElementById('facilityModal');
    if (facilityModal) {
        facilityModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var facilityId = button.getAttribute('data-facility-id');

            // Reset to the details tab every time the modal is opened
            var detailsTabTrigger = facilityModal.querySelector('#details-tab');
            if (detailsTabTrigger) {
                var tab = new bootstrap.Tab(detailsTabTrigger);
                tab.show();
            }

            var modalTitle = facilityModal.querySelector('.modal-title');
            var detailsTab = facilityModal.querySelector('#details-content');
            var feedbackTab = facilityModal.querySelector('#feedback-content');
            var modalFooter = facilityModal.querySelector('.modal-footer');

            // Clear previous content
            modalTitle.textContent = 'Loading...';
            detailsTab.innerHTML = '<p class="text-center">Loading facility details...</p>';
            feedbackTab.innerHTML = '<p class="text-center">Loading feedback...</p>';
            modalFooter.innerHTML = '';

            // Fetch Facility Details
            fetch('?controller=user&action=getFacilityDetails&id=' + facilityId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        modalTitle.textContent = 'Error';
                        detailsTab.innerHTML = `<p class="text-danger">${data.error}</p>`;
                        return;
                    }
                    modalTitle.textContent = data.name;

                    let allPhotos = [];
                    if (data.mainPhotoURL) {
                        allPhotos.push(data.mainPhotoURL);
                    }
                    if (data.photos) {
                        data.photos.forEach(p => {
                            // Add photo only if it's not the main photo, to avoid duplicates
                            if (p.PhotoURL !== data.mainPhotoURL) {
                                allPhotos.push(p.PhotoURL);
                            }
                        });
                    }

                    let mainPhotoHTML = '<p>No photo available.</p>';
                    if (allPhotos.length > 0) {
                        mainPhotoHTML = `<img src="${BASE_URL}/${allPhotos[0]}" id="main-facility-photo" alt="Main Facility Photo">`;
                    }
                    
                    let thumbnailsHTML = '';
                    allPhotos.forEach((photoURL, index) => {
                        thumbnailsHTML += `<img src="${BASE_URL}/${photoURL}" class="thumbnail-item ${index === 0 ? 'active' : ''}" alt="Thumbnail ${index + 1}">`;
                    });

                    detailsTab.innerHTML = `
                        <div id="main-photo-container" class="mb-3">
                            ${mainPhotoHTML}
                        </div>
                        <div class="thumbnail-gallery">
                            ${thumbnailsHTML}
                        </div>
                        <hr>
                        <h5>Description</h5>
                        <p>${data.fullDescription || 'No description available.'}</p>
                        <hr>
                        <p><strong>Capacity:</strong> ${data.capacity} people</p>
                        <p><strong>Rate:</strong> ₱${parseFloat(data.rate).toFixed(2)}</p>
                    `;

                    // Display "Book Now" for customers, "Close" for admin/staff
                    const userRole = '<?= $_SESSION['role'] ?? 'Guest' ?>';
                    if (userRole === 'Customer') {
                        modalFooter.innerHTML = `<a href="?controller=booking&action=create&facility_id=${data.facilityId}" class="btn btn-success">Book Now</a>`;
                    } else {
                        modalFooter.innerHTML = `<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`;
                    }

                    const mainPhoto = detailsTab.querySelector('#main-facility-photo');
                    const thumbnails = detailsTab.querySelectorAll('.thumbnail-item');
                    thumbnails.forEach(thumb => {
                        thumb.addEventListener('click', function() {
                            if (mainPhoto) mainPhoto.src = this.src;
                            thumbnails.forEach(t => t.classList.remove('active'));
                            this.classList.add('active');
                        });
                    });
                })
                .catch(error => {
                    modalTitle.textContent = 'Error';
                    detailsTab.innerHTML = '<p class="text-danger">Failed to load facility details.</p>';
                    console.error('Error:', error);
                });

            // Fetch Facility Feedback
            fetch('?controller=user&action=getFacilityFeedback&id=' + facilityId)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        feedbackTab.innerHTML = '<p class="text-center">No feedback available for this facility yet.</p>';
                        return;
                    }
                    let feedbackHtml = '<h5>Customer Reviews</h5>';
                    data.forEach(review => {
                        feedbackHtml += `
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h6 class="card-title">${review.CustomerName} <span class="text-warning float-end">${'⭐'.repeat(review.Rating)}</span></h6>
                                    <p class="card-text">${review.Comment}</p>
                                    <small class="text-muted">Posted on ${new Date(review.CreatedAt).toLocaleDateString()}</small>
                                </div>
                            </div>
                        `;
                    });
                    feedbackTab.innerHTML = feedbackHtml;
                })
                .catch(error => {
                    feedbackTab.innerHTML = '<p class="text-danger">Could not load feedback.</p>';
                    console.error('Error fetching feedback:', error);
                });
        });
    }
});
</script>
</body>
</html>