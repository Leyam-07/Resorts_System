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
            var modalBody = facilityModal.querySelector('.modal-body');
            var modalTitle = facilityModal.querySelector('.modal-title');

            modalBody.innerHTML = '<p class="text-center">Loading...</p>';
            modalTitle.textContent = 'Facility Details';

            fetch('?controller=user&action=getFacilityDetails&id=' + facilityId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        modalBody.innerHTML = `<p class="text-danger">${data.error}</p>`;
                        return;
                    }

                    modalTitle.textContent = data.name;

                    let allPhotos = [];
                    if (data.mainPhotoURL) {
                        allPhotos.push(data.mainPhotoURL);
                    }
                    if (data.photos) {
                        data.photos.forEach(p => {
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

                    modalBody.innerHTML = `
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
                        <p><strong>Rate:</strong> ₱${parseFloat(data.rate).toFixed(2)} per hour</p>
                    `;

                    // Add Book Now button
                    const modalFooter = facilityModal.querySelector('.modal-footer');
                    if(modalFooter) {
                        modalFooter.innerHTML = `<a href="?controller=booking&action=showBookingForm&facility_id=${data.facilityId}" class="btn btn-success">Book Now</a>`;
                    }

                    // Add event listeners to thumbnails
                    const mainPhoto = modalBody.querySelector('#main-facility-photo');
                    const thumbnails = modalBody.querySelectorAll('.thumbnail-item');
                    thumbnails.forEach(thumb => {
                        thumb.addEventListener('click', function() {
                            if (mainPhoto) {
                                mainPhoto.src = this.src;
                            }
                            thumbnails.forEach(t => t.classList.remove('active'));
                            this.classList.add('active');
                        });
                    });
                })
                .catch(error => {
                    modalBody.innerHTML = '<p class="text-danger">Failed to load facility details.</p>';
                    console.error('Error:', error);
                });
        });
    }
});
</script>
</body>
</html>