<?= view('layout/header', ['title' => 'Lupa Kata Sandi - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="p-4 bg-success text-white text-center" style="background: linear-gradient(135deg, #15803d 0%, #166534 100%) !important;">
                    <h5 class="fw-bold mb-1">Pemulihan Kata Sandi</h5>
                    <p class="small text-white-50 mb-0">Masukkan email Anda untuk menerima instruksi reset</p>
                </div>
                <div class="card-body p-4 bg-white">
                    <form action="<?= base_url('lupa-password') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-semibold">Email Terdaftar</label>
                            <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-agro">Kirim Tautan Reset</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <a href="<?= base_url('login') ?>" class="small text-success fw-bold text-decoration-none">← Kembali ke Halaman Masuk</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
