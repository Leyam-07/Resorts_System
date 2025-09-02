</div> <!--- Close container -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
   var facilityModal = document.getElementById('facilityModal');
   if (facilityModal) {
       facilityModal.addEventListener('show.bs.modal', function (event) {
           var button = event.relatedTarget;
           var facilityId = button.getAttribute('data-facility-id');
           var modalBody = facilityModal.querySelector('.modal-body');
           var modalTitle = facilityModal.querySelector('.modal-title');

           // Clear previous content
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

                   let photosHTML = '';
                   if (data.photos && data.photos.length > 0) {
                       data.photos.forEach((photo, index) => {
                           photosHTML += `
                               <div class="carousel-item ${index === 0 ? 'active' : ''}">
                                   <img src="${photo.PhotoURL}" class="d-block w-100" alt="Facility Photo">
                               </div>`;
                       });
                   } else if (data.mainPhotoURL) {
                       photosHTML = `
                           <div class="carousel-item active">
                               <img src="${data.mainPhotoURL}" class="d-block w-100" alt="Main Photo">
                           </div>`;
                   }

                   modalBody.innerHTML = `
                       <div id="facilityCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                           <div class="carousel-inner">
                               ${photosHTML}
                           </div>
                           <button class="carousel-control-prev" type="button" data-bs-target="#facilityCarousel" data-bs-slide="prev">
                               <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                               <span class="visually-hidden">Previous</span>
                           </button>
                           <button class="carousel-control-next" type="button" data-bs-target="#facilityCarousel" data-bs-slide="next">
                               <span class="carousel-control-next-icon" aria-hidden="true"></span>
                               <span class="visually-hidden">Next</span>
                           </button>
                       </div>
                       <h5>Description</h5>
                       <p>${data.fullDescription || 'No description available.'}</p>
                       <hr>
                       <p><strong>Capacity:</strong> ${data.capacity} people</p>
                       <p><strong>Rate:</strong> ₱${parseFloat(data.rate).toFixed(2)} per hour</p>
                   `;
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