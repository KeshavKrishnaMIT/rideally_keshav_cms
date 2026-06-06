'use strict';

// Auto-dismiss alerts after 4 seconds
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 4000);
    });

    // Activate current nav link
    const current = window.location.href;
    document.querySelectorAll('#mainNav .nav-link').forEach(link => {
        if (link.href === current) link.classList.add('active');
    });

    // Confirm delete
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
        });
    });

    // Image preview on file input
    const imgInput = document.getElementById('imageInput');
    const imgPreview = document.getElementById('imagePreview');
    if (imgInput && imgPreview) {
        imgInput.addEventListener('change', () => {
            const file = imgInput.files[0];
            if (file) {
                imgPreview.src = URL.createObjectURL(file);
                imgPreview.classList.remove('d-none');
            }
        });
    }
});