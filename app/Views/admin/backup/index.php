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
            <h5 class="fw-extrabold mb-0">Cadangan & Pemulihan Database (Backup)</h5>
            <small class="text-muted">Unduh arsip SQL basis data sistem Polinela Agro Digital secara berkala</small>
        </div>
        <a href="<?= base_url('admin/backup/download') ?>" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
            <i class="bi bi-database-down me-1"></i> Buat & Unduh Cadangan SQL
        </a>
    </header>

    <div class="p-4 p-lg-5">
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-success-subtle text-success rounded-3 fs-3">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Keamanan Data Terpusat</h6>
                            <small class="text-muted">Sistem mencadangkan struktur dan isi seluruh tabel</small>
                        </div>
                    </div>
                    <p class="small text-secondary mb-0">
                        File cadangan berekstensi <code>.sql</code> berisi data tabel produk, mutasi stok, transaksi pesanan, verifikasi pembayaran, data pengguna civitas, hingga kuesioner SUS.
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="p-3 bg-primary-subtle text-primary rounded-3 fs-3">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Rekomendasi Pemeliharaan</h6>
                            <small class="text-muted">Jadwal berkala backup sistem</small>
                        </div>
                    </div>
                    <p class="small text-secondary mb-0">
                        Disarankan untuk melakukan ekspor backup database minimal seminggu sekali atau sebelum melakukan pemeliharaan server perkebunan Polinela.
                    </p>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">Riwayat Berkas Cadangan Tersimpan di Server</h6>
                <span class="text-muted small">Total: <?= count($backups) ?> Berkas</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Berkas Cadangan</th>
                            <th>Ukuran File</th>
                            <th>Waktu Pembuatan</th>
                            <th class="text-center" style="width: 120px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($backups)): ?>
                            <?php $no = 1; foreach ($backups as $b): ?>
                            <tr>
                                <td class="text-muted small"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-filetype-sql fs-4 text-primary"></i>
                                        <strong class="font-monospace text-dark small"><?= esc($b['filename']) ?></strong>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= esc($b['size']) ?></span></td>
                                <td class="small text-muted"><?= date('d F Y H:i:s', strtotime($b['date'])) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-all me-1"></i> Tersimpan
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-database-slash fs-1 d-block text-secondary mb-2"></i>
                                    Belum ada berkas backup yang disimpan di server.<br>
                                    Klik tombol "Buat & Unduh Cadangan SQL" di atas untuk membuat salinan baru.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
