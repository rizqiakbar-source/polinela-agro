<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body class="admin-body">

<?= view('layout/sidebar_admin') ?>

<main class="admin-main">
    <header class="admin-header">
        <div>
            <h5 class="fw-extrabold mb-0">Pengaturan Toko & Informasi Kampus</h5>
            <small class="text-muted">Konfigurasi identitas platform, narahubung resmi Polinela, dan integrasi payment gateway</small>
        </div>
    </header>

    <div class="p-4 p-lg-5">
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/pengaturan/save') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="row g-4">
                <!-- Identitas Toko -->
                <div class="col-lg-6">
                    <div class="admin-card h-100">
                        <div class="admin-card-header">
                            <h6 class="fw-bold mb-0"><i class="bi bi-shop me-2 text-success"></i> Identitas Toko & Kampus</h6>
                        </div>
                        <div class="p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nama Platform / Toko</label>
                                <input type="text" name="site_name" class="form-control" value="<?= esc($settings['site_name'] ?? 'Polinela Agro Digital') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Slogan / Tagline</label>
                                <input type="text" name="site_tagline" class="form-control" value="<?= esc($settings['site_tagline'] ?? 'E-Commerce Resmi Produk Hasil Perkebunan Politeknik Negeri Lampung') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Alamat Lengkap Kampus / Tefa</label>
                                <textarea name="campus_address" class="form-control" rows="3"><?= esc($settings['campus_address'] ?? 'Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung, Lampung 35144') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kontak & Dukungan Pelanggan -->
                <div class="col-lg-6">
                    <div class="admin-card h-100">
                        <div class="admin-card-header">
                            <h6 class="fw-bold mb-0"><i class="bi bi-telephone me-2 text-success"></i> Kontak Resmi & Pelayanan</h6>
                        </div>
                        <div class="p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">No Telepon Kantor</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="contact_phone" class="form-control" value="<?= esc($settings['contact_phone'] ?? '(0721) 703995') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">WhatsApp Layanan Konsumen</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
                                    <input type="text" name="contact_wa" class="form-control" value="<?= esc($settings['contact_wa'] ?? '081234567890') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Email Resmi Tefa Perkebunan</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="contact_email" class="form-control" value="<?= esc($settings['contact_email'] ?? 'agro@polinela.ac.id') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Gateway Midtrans Integration -->
                <div class="col-12">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h6 class="fw-bold mb-0"><i class="bi bi-credit-card-2-front me-2 text-success"></i> Konfigurasi Payment Gateway (Midtrans Snap & QRIS)</h6>
                        </div>
                        <div class="p-4">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label small fw-semibold">Midtrans Client Key (Sandbox / Production)</label>
                                    <input type="text" name="midtrans_client_key" class="form-control font-monospace" value="<?= esc($settings['midtrans_client_key'] ?? 'SB-Mid-client-demoPolinelaAgro123') ?>">
                                    <small class="text-muted">Digunakan untuk inisialisasi Midtrans Snap JS pada saat konsumen memilih metode pembayaran QRIS / Virtual Account.</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Mode Integrasi</label>
                                    <input type="text" class="form-control bg-light" value="Sandbox (Simulasi Siap Pakai)" readonly>
                                    <small class="text-success"><i class="bi bi-check-circle me-1"></i> Mode pengujian aktif</small>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 border-top bg-light text-end">
                            <button type="submit" class="btn btn-success px-4 rounded-pill shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Semua Pengaturan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
