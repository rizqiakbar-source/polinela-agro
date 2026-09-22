<?= view('layout/header', ['title' => 'Terjadi Kendala Sistem (500) - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-5 text-center my-5">
    <div class="py-4">
        <div class="display-1 fw-extrabold text-danger mb-3">500</div>
        <div class="fs-1 mb-2">⚙️</div>
        <h3 class="fw-bold text-dark mb-2">Terjadi Gangguan Pada Server</h3>
        <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
            Sistem kami sedang mengalami kendala teknis internal. Tim Teaching Factory Manajemen Informatika Polinela sedang berupaya menanganinya.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="<?= base_url('/') ?>" class="btn btn-agro px-4 py-2 rounded-pill">
                <i class="bi bi-house-door-fill me-1"></i> Ke Beranda
            </a>
            <a href="javascript:location.reload()" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang Halaman
            </a>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
