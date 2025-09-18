document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const addDateField = document.getElementById('announcementDate');
    if (addDateField) {
        addDateField.value = today;
    }

    const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipElements.forEach(element => {
        new bootstrap.Tooltip(element);
    });

    setupFormValidation();
    
    setupCharacterCounter('announcementContent', 1000);
    setupCharacterCounter('editAnnouncementContent', 1000);
    
    setupFormSubmissions();

    setupImagePreview('announcementImages', 'addImagePreview');
    setupImagePreview('editannouncementImages', 'editImagePreview');
});

function setupFormValidation() {
    const forms = ['addAnnouncementForm', 'editAnnouncementForm'];
    
    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('Please fill in all required fields.', 'danger');
            }
        });
    });
}

function setupCharacterCounter(textareaId, maxLength) {
    const textarea = document.getElementById(textareaId);
    if (!textarea) return;
    
    const counter = document.createElement('div');
    counter.className = 'form-text text-end';
    textarea.parentNode.insertBefore(counter, textarea.nextSibling);
    
    function updateCounter() {
        const remaining = maxLength - textarea.value.length;
        counter.textContent = `${remaining} characters remaining`;
        
        if (remaining < 50) {
            counter.className = 'form-text text-end text-warning';
        } else if (remaining < 0) {
            counter.className = 'form-text text-end text-danger';
        } else {
            counter.className = 'form-text text-end text-muted';
        }
    }
    
    textarea.addEventListener('input', updateCounter);
    updateCounter();
}

function setupImagePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    let previewContainer = document.getElementById(previewId);
    if (!previewContainer) {
        previewContainer = document.createElement('div');
        previewContainer.id = previewId;
        previewContainer.className = 'mt-2 d-flex flex-wrap gap-2';
        input.parentNode.insertBefore(previewContainer, input.nextSibling);
    }

    input.addEventListener('change', function(e) {
        previewContainer.innerHTML = '';
        const files = e.target.files;
        
        if (files.length > 5) {
            showAlert('You can only upload a maximum of 5 images.', 'warning');
            input.value = '';
            return;
        }

        Array.from(files).forEach((file, index) => {
            if (!file.type.startsWith('image/')) {
                showAlert('Please select only image files.', 'warning');
                return;
            }

            if (file.size > 5 * 1024 * 1024) { // 5MB limit
                showAlert('Each image must be less than 5MB.', 'warning');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'position-relative';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle" 
                            style="width: 20px; height: 20px; font-size: 10px; padding: 0;"
                            onclick="removeImagePreview(this, ${index})">&times;</button>
                `;
                previewContainer.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });
    });
}

function removeImagePreview(button, index) {
    const previewItem = button.parentElement;
    const input = previewItem.closest('.mb-3').querySelector('input[type="file"]');
    
    previewItem.remove();
    
    const dt = new DataTransfer();
    const files = Array.from(input.files);
    
    files.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
}

function setupFormSubmissions() {
    const saveBtn = document.getElementById('saveAnnouncementBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            submitAddForm();
        });
    }
    
    const updateBtn = document.getElementById('updateAnnouncementBtn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function() {
            submitEditForm();
        });
    }
    
    const deleteBtn = document.getElementById('deleteAnnouncementBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            submitDeleteForm();
        });
    }
}

function submitAddForm() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'announcements-backend.php';
    form.enctype = 'multipart/form-data';
    
    const title = document.getElementById('announcementTitle').value;
    const content = document.getElementById('announcementContent').value;
    const date = document.getElementById('announcementDate').value;
    const imagesInput = document.getElementById('announcementImages');
    
    if (!title.trim() || !content.trim() || !date) {
        showAlert('Please fill in all required fields.', 'danger');
        return;
    }
    
    addHiddenField(form, 'addAnnouncement', '1');
    addHiddenField(form, 'title', title);
    addHiddenField(form, 'content', content);
    addHiddenField(form, 'date', date);
    
    if (imagesInput && imagesInput.files.length > 0) {
        const clonedInput = imagesInput.cloneNode(true);
        clonedInput.style.display = 'none';
        form.appendChild(clonedInput);
    }
    
    document.body.appendChild(form);
    form.submit();
}

function submitEditForm() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'announcements-backend.php';
    form.enctype = 'multipart/form-data';
    
    const id = window.currentEditId;
    const title = document.getElementById('editAnnouncementTitle').value;
    const content = document.getElementById('editAnnouncementContent').value;
    const date = document.getElementById('editAnnouncementDate').value;
    const imagesInput = document.getElementById('editannouncementImages');
    const currentImage = window.currentImagePath || '';
    
    if (!title.trim() || !content.trim() || !date) {
        showAlert('Please fill in all required fields.', 'danger');
        return;
    }
    
    addHiddenField(form, 'editAnnouncement', '1');
    addHiddenField(form, 'id', id);
    addHiddenField(form, 'title', title);
    addHiddenField(form, 'content', content);
    addHiddenField(form, 'date', date);
    addHiddenField(form, 'current_image', currentImage);
    
    if (imagesInput && imagesInput.files.length > 0) {
        const clonedInput = imagesInput.cloneNode(true);
        clonedInput.style.display = 'none';
        form.appendChild(clonedInput);
    }
    
    document.body.appendChild(form);
    form.submit();
}

function submitDeleteForm() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'announcements-backend.php';
    
    const id = window.currentDeleteId;
    
    addHiddenField(form, 'deleteAnnouncement', '1');
    addHiddenField(form, 'id', id);
    
    document.body.appendChild(form);
    form.submit();
}

function addHiddenField(form, name, value) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    form.appendChild(input);
}

function editAnnouncement(announcementData) {
    window.currentEditId = announcementData.id;
    window.currentImagePath = announcementData.image_paths;
    
    document.getElementById('editAnnouncementTitle').value = announcementData.title;
    document.getElementById('editAnnouncementContent').value = announcementData.content;
    document.getElementById('editAnnouncementDate').value = announcementData.date_posted;
    
    const editImagesInput = document.getElementById('editannouncementImages');
    if (editImagesInput) {
        editImagesInput.value = '';
        const previewContainer = document.getElementById('editImagePreview');
        if (previewContainer) {
            previewContainer.innerHTML = '';
        }
    }
    
    const currentImageInfo = document.getElementById('currentImageInfo');
    if (announcementData.image_paths) {
        const images = announcementData.image_paths.split(',');
        const imageCount = images.length;
        const filenames = images.map(path => path.trim().split('/').pop()).join(', ');
        currentImageInfo.innerHTML = `Current images (${imageCount}): <strong>${filenames}</strong><br><small class="text-muted">New images will be added to existing ones</small>`;
    } else {
        currentImageInfo.innerHTML = '<em>No current images</em>';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('editAnnouncementModal'));
    modal.show();
}

function deleteAnnouncement(id, title, date) {
    window.currentDeleteId = id;
    
    document.getElementById('deleteAnnouncementTitle').textContent = title;
    document.getElementById('deleteAnnouncementDate').textContent = new Date(date).toLocaleDateString();
    
    const modal = new bootstrap.Modal(document.getElementById('deleteAnnouncementModal'));
    modal.show();
}

function showImageModal(imagePath) {
    let imageModal = document.getElementById('imageViewModal');
    if (!imageModal) {
        imageModal = createImageModal();
        document.body.appendChild(imageModal);
    }
    
    const modalImage = document.getElementById('modalImage');
    modalImage.src = imagePath;
    
    const modal = new bootstrap.Modal(imageModal);
    modal.show();
}

function createImageModal() {
    const modalHtml = `
        <div class="modal fade" id="imageViewModal" tabindex="-1" aria-labelledby="imageViewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imageViewModalLabel">View Image</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="modalImage" src="" class="img-fluid" alt="Announcement Image">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const div = document.createElement('div');
    div.innerHTML = modalHtml;
    return div.firstElementChild;
}

function showAlert(message, type = 'info') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const container = document.querySelector('.container-fluid') || document.querySelector('main');
    if (container) {
        const alertDiv = document.createElement('div');
        alertDiv.innerHTML = alertHtml;
        container.insertBefore(alertDiv.firstElementChild, container.firstElementChild);
        
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
}