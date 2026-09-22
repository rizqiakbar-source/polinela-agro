<?= view('layout/header', ['title' => 'Profil Saya - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item active">Profil Akun</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Kolom Kiri: Ringkasan Pengguna -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white mb-4">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-extrabold mx-auto mb-3 shadow" style="width: 80px; height: 80px; font-size: 2rem;">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <h5 class="fw-bold mb-1"><?= esc($user['nama']) ?></h5>
                <p class="small text-muted mb-2"><?= esc($user['email']) ?></p>
                <div>
                    <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill">
                        Peran: <?= strtoupper(str_replace('_', ' ', $user['role'])) ?>
                    </span>
                </div>
                <hr class="my-3">
                <div class="small text-muted text-start">
                    <div><i class="bi bi-calendar3 me-2 text-success"></i> Terdaftar sejak: <?= date('d M Y', strtotime($user['created_at'])) ?></div>
                    <div class="mt-1"><i class="bi bi-shield-check me-2 text-success"></i> Status Akun: Aktif</div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Edit Profil & Ganti Password -->
        <div class="col-lg-8">
            <!-- Edit Data Diri -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-gear text-success me-2"></i> Perbarui Data Profil</h5>
                <form action="<?= base_url('profil/update') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="<?= esc($user['nama']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Nomor WhatsApp / HP</label>
                            <input type="text" name="no_hp" class="form-control" value="<?= esc($user['no_hp']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Alamat Pengiriman Default</label>
                            <textarea name="alamat" class="form-control" rows="3"><?= esc($user['alamat'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-agro px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>

            <!-- Ubah Password -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold mb-3"><i class="bi bi-key text-warning me-2"></i> Ganti Kata Sandi</h5>
                <form action="<?= base_url('profil/password') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold text-muted">Kata Sandi Saat Ini</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Kata Sandi Baru</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Konfirmasi Sandi Baru</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-outline-warning text-dark px-4">Update Kata Sandi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
