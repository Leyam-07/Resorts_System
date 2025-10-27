</div> <!--- Close container -->


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
    const resortModalEl = document.getElementById('resortModal');
    const facilityModalEl = document.getElementById('facilityModal');

    if (!resortModalEl || !facilityModalEl) return;

    const resortModal = new bootstrap.Modal(resortModalEl);
    const facilityModal = new bootstrap.Modal(facilityModalEl);

    // --- Resort Modal Logic ---
    if (resortModalEl) {
        resortModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const resortId = button.getAttribute('data-resort-id');
            resortModalEl.dataset.currentResortId = resortId; // Store the current resort ID

            // Reset to the details tab every time the modal is opened
            const detailsTabTrigger = resortModalEl.querySelector('#resort-details-tab');
            if (detailsTabTrigger) {
                new bootstrap.Tab(detailsTabTrigger).show();
            }

            const modalTitle = resortModalEl.querySelector('.modal-title');
            const detailsTab = resortModalEl.querySelector('#resort-details-content');
            const facilitiesTab = resortModalEl.querySelector('#resort-facilities-content');
            const feedbackTab = resortModalEl.querySelector('#resort-feedback-content');
            const modalFooter = resortModalEl.querySelector('.modal-footer');

            // Clear previous content
            modalTitle.textContent = 'Loading...';
            detailsTab.innerHTML = '<p class="text-center">Loading resort details...</p>';
            facilitiesTab.innerHTML = '<p class="text-center">Loading facilities...</p>';
            feedbackTab.innerHTML = '<p class="text-center">Loading feedback...</p>';
            modalFooter.innerHTML = '';

            // Fetch Resort Details
            fetch('?controller=user&action=getResortDetails&id=' + resortId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        modalTitle.textContent = 'Error';
                        detailsTab.innerHTML = `<p class="text-danger">${data.error}</p>`;
                        return;
                    }
                    modalTitle.textContent = data.name;

                    let allPhotos = [];
                    if (data.mainPhotoURL) allPhotos.push(data.mainPhotoURL);
                    if (data.photos) data.photos.forEach(p => { if (p.PhotoURL !== data.mainPhotoURL) allPhotos.push(p.PhotoURL) });

                    const mainPhotoHTML = allPhotos.length > 0 ? `<img src="${BASE_URL}/${allPhotos[0]}" id="main-resort-photo" alt="Main Resort Photo">` : '<p>No photo available.</p>';
                    const thumbnailsHTML = allPhotos.map((photoURL, index) => `<img src="${BASE_URL}/${photoURL}" class="thumbnail-item-resort ${index === 0 ? 'active' : ''}" alt="Thumbnail ${index + 1}">`).join('');

                    detailsTab.innerHTML = `
                        <div id="main-photo-container" class="mb-3">${mainPhotoHTML}</div>
                        <div class="thumbnail-gallery">${thumbnailsHTML}</div><hr>
                        <h5>About ${data.name}</h5><p>${data.fullDescription || 'No description available.'}</p><hr>
                        <p><strong>Address:</strong> ${data.address}</p>
                        <p><strong>Contact:</strong> ${data.contactPerson}</p>`;
                    
                    const mainPhoto = detailsTab.querySelector('#main-resort-photo');
                    const thumbnails = detailsTab.querySelectorAll('.thumbnail-item-resort');
                    thumbnails.forEach(thumb => {
                        thumb.addEventListener('click', function() {
                            if(mainPhoto) mainPhoto.src = this.src;
                            thumbnails.forEach(t => t.classList.remove('active'));
                            this.classList.add('active');
                        });
                    });
                }).catch(error => {
                    modalTitle.textContent = 'Error';
                    detailsTab.innerHTML = '<p class="text-danger">Failed to load resort details.</p>';
                });

            // Fetch Resort Facilities
            fetch('?controller=user&action=getResortFacilities&id=' + resortId)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        facilitiesTab.innerHTML = '<p class="text-center">No facilities available for this resort.</p>';
                    } else {
                        let facilitiesHtml = '<div class="row">';
                        data.forEach(facility => {
                            const avgRating = parseFloat(facility.AverageRating).toFixed(1);
                            const feedbackCount = facility.FeedbackCount;
                            const completedBookings = facility.CompletedBookingsCount;

                            facilitiesHtml += `
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <img src="${facility.mainPhotoURL ? BASE_URL + '/' + facility.mainPhotoURL : 'https://via.placeholder.com/300x200'}" class="card-img-top" alt="${facility.name}" style="height: 150px; object-fit: cover;">
                                        <div class="card-body">
                                            <h6 class="card-title">${facility.name}</h6>
                                            <p class="card-text small">${facility.shortDescription}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-warning">${'⭐'.repeat(Math.round(avgRating))} (${avgRating})</span>
                                                <small class="text-muted">${feedbackCount} reviews</small>
                                            </div>
                                             <small class="text-muted">Used in ${completedBookings} Completed Bookings</small>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-secondary btn-sm w-100 view-facility-details" data-facility-id="${facility.facilityId}">View Details</button>
                                        </div>
                                    </div>
                                </div>`;
                        });
                        facilitiesHtml += '</div>';
                        facilitiesTab.innerHTML = facilitiesHtml;
                    }
                }).catch(error => {
                    facilitiesTab.innerHTML = '<p class="text-danger">Could not load facilities.</p>';
                });

            // Fetch Resort Feedback
            fetch('?controller=user&action=getResortFeedback&id=' + resortId)
                .then(response => {
                    if (!response.ok) {
                        // If the server returns a 404 or other error, treat it as empty feedback
                        return [];
                    }
                    return response.json();
                })
                .then(data => {
                    const feedbackTabTrigger = resortModalEl.querySelector('#resort-feedback-tab');
                    if (!data || data.length === 0) {
                        feedbackTab.innerHTML = '<p class="text-center">No feedback available for this resort yet.</p>';
                        if (feedbackTabTrigger) feedbackTabTrigger.textContent = 'Feedback (0)';
                    } else {
                        let feedbackHtml = `<h5>Customer Reviews (${data.length})</h5>`;
                        data.forEach(review => {
                            const completedBookingsText = review.completedBookings > 0 ? `<span class="badge bg-light text-dark ms-2">${review.completedBookings} Completed Bookings</span>` : '';
                            feedbackHtml += `
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <h6 class="card-title">${review.CustomerName} ${completedBookingsText} <span class="text-warning float-end">${'⭐'.repeat(review.Rating)}</span></h6>
                                        <p class="card-text">${review.Comment || '<em>No comment provided.</em>'}</p>
                                        <small class="text-muted">Posted on ${new Date(review.CreatedAt).toLocaleDateString()}</small>
                                    </div>
                                </div>`;
                        });
                        feedbackTab.innerHTML = feedbackHtml;
                        if (feedbackTabTrigger) feedbackTabTrigger.textContent = `Feedback (${data.length})`;
                    }
                }).catch(error => {
                    feedbackTab.innerHTML = '<p class="text-danger">Could not load feedback.</p>';
                    const feedbackTabTrigger = resortModalEl.querySelector('#resort-feedback-tab');
                    if (feedbackTabTrigger) feedbackTabTrigger.textContent = 'Feedback (Error)';
                });

            // Set Footer Button
            modalFooter.innerHTML = `<a href="?controller=booking&action=showBookingForm&resort_id=${resortId}" class="btn btn-success">Book Resort Experience</a>`;
        });

        // Use event delegation for facility buttons
        resortModalEl.addEventListener('click', function(event) {
            const facilityButton = event.target.closest('.view-facility-details');
            if (facilityButton) {
                const facilityId = facilityButton.dataset.facilityId;
                const resortId = resortModalEl.dataset.currentResortId;
                
                resortModal.hide();

                const handleFacilityModalClose = () => {
                    resortModal.show();
                    facilityModalEl.removeEventListener('hidden.bs.modal', handleFacilityModalClose);
                };
                facilityModalEl.addEventListener('hidden.bs.modal', handleFacilityModalClose);
                
                populateAndShowFacilityModal(facilityId, resortId);
            }
        });
    }

    // --- Facility Modal Logic ---
    function populateAndShowFacilityModal(facilityId, resortId) {
        const detailsTabTrigger = facilityModalEl.querySelector('#details-tab');
        if (detailsTabTrigger) new bootstrap.Tab(detailsTabTrigger).show();

        const modalTitle = facilityModalEl.querySelector('.modal-title');
        const detailsTab = facilityModalEl.querySelector('#details-content');
        const feedbackTab = facilityModalEl.querySelector('#feedback-content');
        const modalFooter = facilityModalEl.querySelector('.modal-footer');

        modalTitle.textContent = 'Loading...';
        detailsTab.innerHTML = '<p class="text-center">Loading facility details...</p>';
        feedbackTab.innerHTML = '<p class="text-center">Loading feedback...</p>';
        modalFooter.innerHTML = '';
        
        facilityModal.show();

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

                let allPhotos = [ ...(data.mainPhotoURL ? [data.mainPhotoURL] : []), ...(data.photos ? data.photos.map(p => p.PhotoURL) : []) ];
                allPhotos = [...new Set(allPhotos)];

                const mainPhotoHTML = allPhotos.length > 0 ? `<img src="${BASE_URL}/${allPhotos[0]}" id="main-facility-photo" alt="Main Facility Photo">` : '<p>No photo available.</p>';
                const thumbnailsHTML = allPhotos.map((photoURL, index) => `<img src="${BASE_URL}/${photoURL}" class="thumbnail-item ${index === 0 ? 'active' : ''}" alt="Thumbnail ${index + 1}">`).join('');

                detailsTab.innerHTML = `
                    <div id="main-photo-container" class="mb-3">${mainPhotoHTML}</div>
                    <div class="thumbnail-gallery">${thumbnailsHTML}</div><hr>
                    <h5>Description</h5><p>${data.fullDescription || 'No description available.'}</p><hr>
                    <p><strong>Price:</strong> ₱${parseFloat(data.rate).toFixed(2)}</p>`;

                modalFooter.innerHTML = `<a href="?controller=booking&action=showBookingForm&resort_id=${resortId}&facility_id=${data.facilityId}" class="btn btn-success">Book This Facility</a>`;

                const mainPhoto = detailsTab.querySelector('#main-facility-photo');
                const thumbnails = detailsTab.querySelectorAll('.thumbnail-item');
                thumbnails.forEach(thumb => {
                    thumb.addEventListener('click', function() {
                        if (mainPhoto) mainPhoto.src = this.src;
                        thumbnails.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
            }).catch(error => {
                modalTitle.textContent = 'Error';
                detailsTab.innerHTML = '<p class="text-danger">Failed to load facility details.</p>';
            });

        // Fetch Facility Feedback
        // Fetch Facility Feedback
        fetch('?controller=feedback&action=getFacilityFeedback&facility_id=' + facilityId)
            .then(response => response.json())
            .then(data => {
                const feedbackTabTrigger = facilityModalEl.querySelector('#feedback-tab');

                if (!data.success) {
                    feedbackTab.innerHTML = `<p class="text-danger">${data.error}</p>`;
                    if (feedbackTabTrigger) feedbackTabTrigger.textContent = 'Feedback (Error)';
                    return;
                }

                const reviews = data.feedback.reviews || [];
                const totalBookings = data.feedback.totalCompletedBookings;

                if (reviews.length === 0) {
                    feedbackTab.innerHTML = `<p class="text-center">No reviews available for this facility yet.</p>
                                             <p class="text-center text-muted">Used in ${totalBookings} Completed Bookings.</p>`;
                    if (feedbackTabTrigger) feedbackTabTrigger.textContent = 'Feedback (0)';
                } else {
                    let feedbackHtml = `<h5>Customer Reviews (${reviews.length})</h5>
                                        <p class="text-muted">This facility has been part of ${totalBookings} Completed Bookings.</p><hr>`;
                    reviews.forEach(review => {
                        feedbackHtml += `
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h6 class="card-title">${review.CustomerName} <span class="badge bg-light text-dark ms-2">${review.completedBookings} Completed Bookings</span><span class="text-warning float-end">${'⭐'.repeat(review.Rating)}</span></h6>
                                    <p class="card-text">${review.Comment || '<em>No comment provided.</em>'}</p>
                                    <small class="text-muted">Posted on ${new Date(review.CreatedAt).toLocaleDateString()}</small>
                                </div>
                            </div>`;
                    });
                    feedbackTab.innerHTML = feedbackHtml;
                    if (feedbackTabTrigger) feedbackTabTrigger.textContent = `Feedback (${reviews.length})`;
                }
            }).catch(error => {
                const feedbackTabTrigger = facilityModalEl.querySelector('#feedback-tab');
                feedbackTab.innerHTML = '<p class="text-danger">Could not load feedback.</p>';
                if (feedbackTabTrigger) feedbackTabTrigger.textContent = 'Feedback (Error)';
            });
    }
});
</script>
<!-- Bootstrap JS Bundle with Popper -->
</body>
</html>
