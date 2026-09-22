<?= view('layout/header', ['title' => 'Masuk Akun - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-success text-white p-4 text-center position-relative" style="background: linear-gradient(135deg, #15803d 0%, #166534 100%) !important;">
                    <div class="brand-badge mx-auto mb-2" style="width: 50px; height: 50px; font-size: 1.6rem; background: rgba(255,255,255,0.2);">
                        <i class="bi bi-tree"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Masuk ke Akun Anda</h4>
                    <p class="small text-white-50 mb-0">Pasar Digital Produk Perkebunan Politeknik Negeri Lampung</p>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <form action="<?= base_url('login') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" id="loginEmail" class="form-control bg-light border-start-0" placeholder="nama@email.com" value="<?= old('email') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label class="form-label fw-semibold small text-muted">Kata Sandi</label>
                                <a href="<?= base_url('lupa-password') ?>" class="small text-success text-decoration-none">Lupa sandi?</a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" id="loginPassword" class="form-control bg-light border-start-0" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-agro py-2 fs-6">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <span class="text-muted small">Belum memiliki akun?</span>
                        <a href="<?= base_url('register') ?>" class="small text-success fw-bold text-decoration-none ms-1">Daftar Akun Baru</a>
                    </div>

                    <!-- Quick Demo Credentials Box -->
                    <div class="mt-4 p-3 bg-light rounded-3 border">
                        <div class="fw-bold small text-muted mb-2"><i class="bi bi-key-fill text-warning me-1"></i> Akun Pengujian Cepat (Klik untuk Isi):</div>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="fillLogin('admin@polinela.ac.id', 'admin123')">Super Admin</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillLogin('adminkopi@polinela.ac.id', 'admin123')">Admin Unit Kopi</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillLogin('pimpinan@polinela.ac.id', 'pimpinan123')">Pimpinan</button>
                            <button type="button" class="btn btn-sm btn-outline-warning text-dark" onclick="fillLogin('budi@gmail.com', 'konsumen123')">Konsumen (Civitas)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillLogin(email, pass) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPassword').value = pass;
}
</script>

<?= view('layout/footer') ?>
