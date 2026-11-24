// Mentora – app.js
// Minimal JS: hanya enhancement, tidak ada fitur utama yang bergantung JS saja.

// Konfirmasi hapus
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
});

// Preview gambar sebelum upload
const imgInput = document.getElementById('imgPreviewInput');
const imgPreview = document.getElementById('imgPreview');
if (imgInput && imgPreview) {
    imgInput.addEventListener('change', () => {
        const file = imgInput.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            imgPreview.src = e.target.result;
            imgPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
}

// Auto-dismiss alert setelah 5 detik
document.querySelectorAll('.alert.alert-success').forEach(el => {
    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
        bsAlert.close();
    }, 5000);
});
