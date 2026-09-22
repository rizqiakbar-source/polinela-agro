<?= view('layout/header', ['title' => 'Atur Ulang Kata Sandi - Polinela Agro Digital']) ?>

<div class="container d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white" style="max-width: 460px; width: 100%;">
        <div class="text-center mb-4">
            <a href="<?= base_url('/') ?>" class="text-decoration-none d-inline-flex align-items-center gap-2 mb-2">
                <div class="brand-badge" style="width: 44px; height: 44px; font-size: 1.3rem;">
                    <i class="bi bi-tree"></i>
                </div>
                <span class="fs-5 fw-extrabold text-dark tracking-tight">POLINELA <span class="text-success">AGRO</span></span>
            </a>
            <h4 class="fw-bold mt-2 mb-1">Setel Kata Sandi Baru</h4>
            <p class="text-muted small">Buat kata sandi baru yang kuat dan aman untuk akun Anda.</p>
        </div>

        <?= view('components/alert') ?>

        <form action="<?= base_url('login') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">Kata Sandi Baru</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="Minimal 6 karakter" required minlength="6">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-muted">Konfirmasi Kata Sandi Baru</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
                    <input type="password" name="confirm_password" class="form-control border-start-0" placeholder="Ketik ulang sandi baru" required minlength="6">
                </div>
            </div>

            <button type="submit" class="btn btn-agro w-100 py-2 fs-6 mb-3 shadow-sm">
                <i class="bi bi-check-circle me-1"></i> Simpan & Masuk
            </button>

            <div class="text-center">
                <a href="<?= base_url('login') ?>" class="small text-decoration-none text-success fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Masuk
                </a>
            </div>
        </form>
    </div>
</div>

<?= view('layout/footer') ?>
