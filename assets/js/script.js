//announcemets in index
function showImageModal(imageSrc) {
  document.getElementById('modalImage').src = imageSrc;
  new bootstrap.Modal(document.getElementById('imageModal')).show();
}

function showAllImages(announcementId) {
  // Fetch all images for this announcement
  fetch(`get_announcement_images.php?id=${announcementId}`)
    .then(response => response.json())
    .then(data => {
      const gallery = document.getElementById('imageGallery');
      gallery.innerHTML = '';
      
      if (data.images && data.images.length > 0) {
        data.images.forEach(image => {
          const colDiv = document.createElement('div');
          colDiv.className = 'col-md-6 col-lg-4';
          colDiv.innerHTML = `
            <div class="card">
              <img src="${image}" class="card-img-top" style="height: 200px; object-fit: cover; cursor: pointer;" 
                   onclick="showImageModal('${image}')" alt="Announcement Image">
            </div>
          `;
          gallery.appendChild(colDiv);
        });
      } else {
        gallery.innerHTML = '<div class="col-12"><p class="text-center text-muted">No images available</p></div>';
      }
      
      new bootstrap.Modal(document.getElementById('multiImageModal')).show();
    })
    .catch(error => {
      console.error('Error fetching images:', error);
    });
}
