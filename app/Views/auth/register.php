<?= view('layout/header', ['title' => 'Daftar Akun Baru - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-success text-white p-4 text-center position-relative" style="background: linear-gradient(135deg, #15803d 0%, #166534 100%) !important;">
                    <h4 class="fw-bold mb-1">Pendaftaran Konsumen & Civitas</h4>
                    <p class="small text-white-50 mb-0">Daftarkan akun untuk berbelanja produk perkebunan resmi Polinela</p>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="<?= base_url('register') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama lengkap Anda / Civitas" value="<?= old('nama') ?>" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Alamat Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= old('email') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Nomor WhatsApp / HP</label>
                                <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" value="<?= old('no_hp') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Kata Sandi (Minimal 6 Karakter)</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small text-muted">Alamat Pengiriman Default (Opsional)</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, gedung/asrama, no rumah..."><?= old('alamat') ?></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-agro py-2 fs-6">
                                <i class="bi bi-person-plus-fill me-2"></i> Buat Akun Baru
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <span class="text-muted small">Sudah memiliki akun?</span>
                        <a href="<?= base_url('login') ?>" class="small text-success fw-bold text-decoration-none ms-1">Masuk Sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
