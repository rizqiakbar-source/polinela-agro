document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi Tooltip Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 2. Tombol Konfirmasi Hapus Data Universal
    document.querySelectorAll('.btn-delete-confirm').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href') || this.dataset.url;
            const item = this.dataset.item || 'data ini';

            Swal.fire({
                title: 'Konfirmasi Penghapusan',
                text: `Apakah Anda yakin ingin menghapus ${item}? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });

    // 3. Auto preview gambar form upload
    document.querySelectorAll('.image-preview-input').forEach(input => {
        input.addEventListener('change', function () {
            const previewId = this.dataset.previewTarget;
            const previewImg = document.getElementById(previewId);
            if (previewImg && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('d-none');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
});
