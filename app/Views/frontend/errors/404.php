<?= view('layout/header', ['title' => 'Halaman Tidak Ditemukan (404) - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-5 text-center my-5">
    <div class="py-4">
        <div class="display-1 fw-extrabold text-success mb-3">404</div>
        <div class="fs-1 mb-2">🌿</div>
        <h3 class="fw-bold text-dark mb-2">Oops! Halaman Tidak Ditemukan</h3>
        <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
            Halaman komoditas atau layanan perkebunan yang Anda cari mungkin telah dipindahkan, berganti nama, atau belum tersedia di kampus Politeknik Negeri Lampung.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="<?= base_url('/') ?>" class="btn btn-agro px-4 py-2 rounded-pill">
                <i class="bi bi-house-door-fill me-1"></i> Kembali ke Beranda
            </a>
            <a href="<?= base_url('katalog') ?>" class="btn btn-outline-success px-4 py-2 rounded-pill">
                <i class="bi bi-grid-fill me-1"></i> Jelajahi Produk
            </a>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
