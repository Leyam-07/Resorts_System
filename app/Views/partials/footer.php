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
            const mapTab = resortModalEl.querySelector('#resort-map-content');
            const feedbackTab = resortModalEl.querySelector('#resort-feedback-content');
            const modalFooter = resortModalEl.querySelector('.modal-footer');

            // Clear previous content
            modalTitle.textContent = 'Loading...';
            detailsTab.innerHTML = '<p class="text-center">Loading resort details...</p>';
            facilitiesTab.innerHTML = '<p class="text-center">Loading facilities...</p>';
            if (mapTab) mapTab.innerHTML = '<p class="text-center">Loading map...</p>';
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
                        <h5>About ${data.name}</h5><p>${data.fullDescription || 'No description available.'}</p>
                        <hr>
                        <p><strong>Address:</strong> ${data.address}</p>
                        <p><strong>Contact:</strong> ${data.contactPerson}</p>
                        ${data.startingPrice ? `<hr><h5 class="text-success fw-bold">Starting at ₱${Number(data.startingPrice).toLocaleString('en-US', { maximumFractionDigits: 0 })}</h5>` : ''}
                        `;
                    
                    const mainPhoto = detailsTab.querySelector('#main-resort-photo');
                    const thumbnails = detailsTab.querySelectorAll('.thumbnail-item-resort');
                    thumbnails.forEach(thumb => {
                        thumb.addEventListener('click', function() {
                            if(mainPhoto) mainPhoto.src = this.src;
                            thumbnails.forEach(t => t.classList.remove('active'));
                            this.classList.add('active');
                        });
                    });

                    // --- Populate Map Tab ---
                    const mapTab = resortModalEl.querySelector('#resort-map-content');
                    const mapLink = data.googleMapsLink;
                    if (mapTab) {
                        if (mapLink) {
                            // A more robust regex to find the correct part of the URL to embed
                            const embedPattern = /@([-0-9.]+),([-0-9.]+),([0-9.]+)z/;
                            const match = mapLink.match(embedPattern);
                            
                            let embedLink = '';
                            if (mapLink.includes('/maps/embed')) {
                                embedLink = mapLink;
                            } else if (match) {
                                const lat = match[1];
                                const long = match[2];
                                embedLink = `https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=${lat},${long}`;
                                // Note: This requires a Google Maps Embed API key. A simpler iframe approach might be better without an API key.
                                // Let's use a simpler approach that works for most share links.
                                const url = new URL(mapLink);
                                const params = new URLSearchParams(url.search);
                                const place = url.pathname.split('/place/')[1];
                                if (place) {
                                    embedLink = `https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=${decodeURIComponent(place)}`;
                                }
                            }
                            
                            // Simplest approach: assume the link is a standard share link and try to embed it.
                            // This is less reliable but doesn't require an API key.
                            // Let's assume the admin provides a direct embed link or we construct it.
                            // A common share link looks like https://maps.app.goo.gl/someId
                            // An embed link looks like https://www.google.com/maps/embed?pb=...
                            // We will assume the admin provides a usable link.
                            
                            // Let's create a simplified embeddable link if it's a standard google maps URL
                            let finalEmbedLink = mapLink;
                            if (!mapLink.includes('embed')) {
                                // This is a fallback and may not always work perfectly
                                finalEmbedLink = `https://maps.google.com/maps?q=${encodeURIComponent(data.address)}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
                            }

                            mapTab.innerHTML = `
                                <div class="ratio ratio-16x9">
                                    <iframe src="${finalEmbedLink}" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </div>
                            `;
                        } else {
                            mapTab.innerHTML = '<div class="alert alert-warning text-center">Map link is not available for this resort.</div>';
                        }
                    }

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
                            const feedbackCount = parseInt(facility.FeedbackCount) || 0;
                            const completedBookings = parseInt(facility.CompletedBookingsCount) || 0;

                            let badgesHtml = '<div class="mt-auto">';
                            if (feedbackCount > 0 && !isNaN(avgRating)) {
                                badgesHtml += `
                                    <span class="badge bg-light text-dark">
                                        ⭐ ${avgRating}
                                        <span class="text-muted">(${feedbackCount} reviews)</span>
                                    </span>`;
                            }
                            if (completedBookings > 0) {
                                badgesHtml += `
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-check-circle"></i> ${completedBookings} Completed Bookings
                                    </span>`;
                            }
                            badgesHtml += '</div>';

                            facilitiesHtml += `
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 d-flex flex-column">
                                        <img src="${facility.MainPhotoURL ? BASE_URL + '/' + facility.MainPhotoURL : 'https://via.placeholder.com/300x200'}" class="card-img-top" alt="${facility.Name}" style="height: 150px; object-fit: cover;">
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title">${facility.Name}</h6>
                                            <p class="card-text small">${facility.shortDescription}</p>
                                            ${badgesHtml}
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-secondary btn-sm w-100 view-facility-details" data-facility-id="${facility.FacilityID}">View Details</button>
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
                            const completedBookingsBadge = review.completedBookings > 0
                                ? `<span class="badge bg-light text-dark ms-2"><i class="fas fa-check-circle"></i> ${review.completedBookings} Completed Bookings</span>`
                                : '';
                            let mediaHtml = '';
                            if (review.Media && review.Media.length > 0) {
                                mediaHtml += '<div class="feedback-media mt-2">';
                                review.Media.forEach(media => {
                                    if (media.MediaType === 'Image') {
                                        mediaHtml += `<img src="${BASE_URL}/${media.MediaURL}" alt="Feedback Image" class="img-thumbnail" style="max-width: 100px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#mediaModal" data-media-url="${BASE_URL}/${media.MediaURL}" data-media-type="Image">`;
                                    } else if (media.MediaType === 'Video') {
                                        mediaHtml += `<video controls class="img-thumbnail" style="max-width: 100px;"><source src="${BASE_URL}/${media.MediaURL}" type="video/mp4"></video>`;
                                    }
                                });
                                mediaHtml += '</div>';
                            }
                            feedbackHtml += `
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <h6 class="card-title">${review.CustomerName} ${completedBookingsBadge} <span class="text-warning float-end">${'⭐'.repeat(review.Rating)}</span></h6>
                                        <p class="card-text">${review.Comment || '<em>No comment provided.</em>'}</p>
                                        ${mediaHtml}
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
                const totalBookings = parseInt(data.feedback.totalCompletedBookings) || 0;

                if (reviews.length === 0) {
                    let emptyHtml = '<p class="text-center">No reviews available for this facility yet.</p>';
                    if (totalBookings > 0) {
                        emptyHtml += `<p class="text-center text-muted mt-2">Used in ${totalBookings} Completed Bookings.</p>`;
                    }
                    feedbackTab.innerHTML = emptyHtml;
                    if (feedbackTabTrigger) feedbackTabTrigger.textContent = 'Feedback (0)';
                } else {
                    let feedbackHtml = `<h5>Customer Reviews (${reviews.length})</h5>`;
                    if (totalBookings > 0) {
                        feedbackHtml += `<p class="text-muted">This facility has been part of ${totalBookings} completed bookings.</p>`;
                    }
                    feedbackHtml += '<hr>';
                    reviews.forEach(review => {
                        const completedBookingsBadge = review.completedBookings > 0
                            ? `<span class="badge bg-light text-dark ms-2"><i class="fas fa-check-circle"></i> ${review.completedBookings} Completed Bookings</span>`
                            : '';
                        let mediaHtml = '';
                        if (review.Media && review.Media.length > 0) {
                            mediaHtml += '<div class="feedback-media mt-2">';
                            review.Media.forEach(media => {
                                if (media.MediaType === 'Image') {
                                    mediaHtml += `<img src="${BASE_URL}/${media.MediaURL}" alt="Feedback Image" class="img-thumbnail" style="max-width: 100px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#mediaModal" data-media-url="${BASE_URL}/${media.MediaURL}" data-media-type="Image">`;
                                } else if (media.MediaType === 'Video') {
                                    mediaHtml += `<video controls class="img-thumbnail" style="max-width: 100px;"><source src="${BASE_URL}/${media.MediaURL}" type="video/mp4"></video>`;
                                }
                            });
                            mediaHtml += '</div>';
                        }
                        feedbackHtml += `
                            <div class="card mb-2">
                                <div class="card-body">
                                    <h6 class="card-title">${review.CustomerName} ${completedBookingsBadge}<span class="text-warning float-end">${'⭐'.repeat(review.Rating)}</span></h6>
                                    <p class="card-text">${review.Comment || '<em>No comment provided.</em>'}</p>
                                    ${mediaHtml}
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
