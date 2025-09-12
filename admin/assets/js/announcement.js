//modal
document.addEventListener('DOMContentLoaded', () => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('announcementDate').value = today;
});

function populateEditModal(title, content, date, image) {
    document.getElementById('editAnnouncementTitle').value = title;
    document.getElementById('editAnnouncementContent').value = content;
    document.getElementById('editAnnouncementDate').value = date;
    document.getElementById('currentImageInfo').textContent = image ? `Current image: ${image}` : '';
}