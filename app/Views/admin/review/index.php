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
            <h5 class="fw-extrabold mb-0">Moderasi Ulasan & Rating Produk</h5>
            <small class="text-muted">Tinjau penilaian pembeli, moderasi ulasan, dan berikan balasan resmi admin</small>
        </div>
    </header>

    <div class="p-4 p-lg-5">
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filter Status Tabs -->
        <div class="d-flex gap-2 overflow-auto pb-2 mb-4">
            <a href="<?= base_url('admin/review') ?>" class="btn btn-sm <?= empty($current_status) ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Semua Status</a>
            <a href="<?= base_url('admin/review?status=disetujui') ?>" class="btn btn-sm <?= ($current_status === 'disetujui') ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">
                <i class="bi bi-check2-circle me-1"></i> Disetujui (Tampil di Web)
            </a>
            <a href="<?= base_url('admin/review?status=pending') ?>" class="btn btn-sm <?= ($current_status === 'pending') ? 'btn-warning' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">
                <i class="bi bi-clock-history me-1"></i> Menunggu Moderasi
            </a>
            <a href="<?= base_url('admin/review?status=ditolak') ?>" class="btn btn-sm <?= ($current_status === 'ditolak') ? 'btn-danger' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">
                <i class="bi bi-x-circle me-1"></i> Ditolak
            </a>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">Daftar Ulasan Masuk</h6>
                <span class="text-muted small">Total: <?= count($reviews) ?> Ulasan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 220px;">Produk Perkebunan</th>
                            <th style="width: 160px;">Pelanggan</th>
                            <th style="width: 110px;">Rating</th>
                            <th>Ulasan & Komentar</th>
                            <th style="width: 120px;">Status</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $rev): ?>
                            <tr>
                                <td>
                                    <strong class="text-dark small d-block"><?= esc($rev['nama_produk'] ?? 'Produk') ?></strong>
                                    <span class="text-muted" style="font-size: 0.72rem;"><?= esc($rev['nama_unit'] ?? 'Unit Tefa') ?></span>
                                </td>
                                <td>
                                    <strong class="text-dark small d-block"><?= esc($rev['customer_nama'] ?? 'Pengguna') ?></strong>
                                    <span class="text-muted" style="font-size: 0.72rem;"><?= date('d/m/Y', strtotime($rev['created_at'])) ?></span>
                                </td>
                                <td>
                                    <div class="text-warning small text-nowrap">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi <?= $i <= $rev['rating'] ? 'bi-star-fill' : 'bi-star text-muted' ?>"></i>
                                        <?php endfor; ?>
                                        <span class="fw-bold text-dark ms-1">(<?= $rev['rating'] ?>)</span>
                                    </div>
                                </td>
                                <td>
                                    <p class="small text-secondary mb-1"><?= esc($rev['review'] ?: '-') ?></p>
                                    <?php if (!empty($rev['reply'])): ?>
                                        <div class="p-2 rounded bg-light border-start border-3 border-success small">
                                            <strong class="text-success d-block" style="font-size: 0.72rem;">Balasan Admin:</strong>
                                            <span class="text-muted"><?= esc($rev['reply']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $stBadge = match($rev['status']) {
                                        'disetujui' => 'bg-success-subtle text-success border border-success-subtle',
                                        'ditolak'   => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        default     => 'bg-warning-subtle text-warning border border-warning-subtle'
                                    };
                                    ?>
                                    <span class="badge <?= $stBadge ?> text-capitalize">
                                        <?= esc($rev['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalModerate<?= $rev['id'] ?>" title="Tanggapi / Moderasi">
                                            <i class="bi bi-chat-dots"></i> Respon
                                        </button>
                                        <a href="<?= base_url('admin/review/delete/' . $rev['id']) ?>" 
                                           class="btn btn-outline-danger" 
                                           title="Hapus Ulasan"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>

                                    <!-- Modal Moderasi -->
                                    <div class="modal fade" id="modalModerate<?= $rev['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="<?= base_url('admin/review/status/' . $rev['id']) ?>" method="POST" class="modal-content text-start border-0 shadow">
                                                <?= csrf_field() ?>
                                                <div class="modal-header border-bottom">
                                                    <h6 class="modal-title fw-bold"><i class="bi bi-shield-check me-1 text-success"></i> Moderasi Ulasan</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3 p-3 rounded bg-light">
                                                        <div class="small fw-bold text-dark"><?= esc($rev['customer_nama']) ?> untuk <?= esc($rev['nama_produk']) ?></div>
                                                        <div class="text-warning small my-1">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="bi <?= $i <= $rev['rating'] ? 'bi-star-fill' : 'bi-star text-muted' ?>"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <div class="small text-muted fst-italic">"<?= esc($rev['review']) ?>"</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Status Moderasi</label>
                                                        <select name="status" class="form-select form-select-sm" required>
                                                            <option value="disetujui" <?= $rev['status'] === 'disetujui' ? 'selected' : '' ?>>Setujui (Tampilkan ke Publik)</option>
                                                            <option value="pending" <?= $rev['status'] === 'pending' ? 'selected' : '' ?>>Pending (Tahan Sementara)</option>
                                                            <option value="ditolak" <?= $rev['status'] === 'ditolak' ? 'selected' : '' ?>>Tolak (Sembunyikan)</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Balasan Resmi Admin / Unit</label>
                                                        <textarea name="reply" class="form-control form-control-sm" rows="3" placeholder="Terima kasih atas ulasannya!"><?= esc($rev['reply'] ?? '') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top bg-light">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-sm btn-success px-3">
                                                        <i class="bi bi-save me-1"></i> Simpan Moderasi
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-chat-square-quote fs-1 d-block text-secondary mb-2"></i>
                                    Belum ada ulasan produk yang masuk.
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
