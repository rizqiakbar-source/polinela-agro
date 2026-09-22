<?= view('layout/header', ['title' => 'Ulasan & Rating Produk - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item active">Ulasan Produk</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold mb-1">Ulasan Pembeli</h4>
                    <p class="text-muted small mb-0">Ulasan dan rating kepuasan konsumen terhadap produk perkebunan Polinela.</p>
                </div>
                <a href="<?= base_url('pesanan') ?>" class="btn btn-agro btn-sm px-3 rounded-pill">
                    <i class="bi bi-pencil-square me-1"></i> Beri Ulasan Pesanan
                </a>
            </div>
        </div>

        <div class="col-12">
            <?= view('components/alert') ?>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-star-half display-4 text-warning mb-2 d-block"></i>
                    <h5 class="fw-bold text-dark">Bagikan Pengalaman Belanja Anda</h5>
                    <p class="small text-muted mb-3">Ulasan dapat Anda kirimkan langsung melalui halaman <strong>Detail Pesanan</strong> setelah pesanan Anda selesai.</p>
                    <a href="<?= base_url('pesanan?status=selesai') ?>" class="btn btn-outline-success btn-sm rounded-pill px-4">
                        Lihat Pesanan Selesai
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
