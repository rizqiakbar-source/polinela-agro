<?= view('layout/header', ['title' => 'Alamat Pengiriman - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('profil') ?>" class="text-success">Profil Akun</a></li>
            <li class="breadcrumb-item active">Alamat Pengiriman</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="bi bi-geo-alt-fill text-success me-2"></i> Alamat Pengiriman Saya</h5>
                        <p class="text-muted small mb-0">Alamat ini digunakan otomatis saat Anda melakukan checkout produk perkebunan.</p>
                    </div>
                </div>

                <?= view('components/alert') ?>

                <form action="<?= base_url('profil/update') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="nama" value="<?= esc(session()->get('user_nama')) ?>">
                    <input type="hidden" name="no_hp" value="<?= esc(session()->get('user_no_hp') ?? '081234567890') ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Alamat Lengkap (Jalan, No Rumah, RT/RW, Kelurahan, Kecamatan)</label>
                        <textarea name="alamat" class="form-control" rows="4" placeholder="Contoh: Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung" required><?= esc(session()->get('user_alamat') ?? '') ?></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-agro px-4">
                            <i class="bi bi-save me-1"></i> Simpan Alamat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
