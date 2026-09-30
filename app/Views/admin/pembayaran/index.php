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
            <h5 class="fw-extrabold mb-0">Verifikasi Pembayaran</h5>
            <small class="text-muted">Kelola dan periksa bukti transaksi pembayaran manual dari konsumen</small>
        </div>
    </header>

    <div class="p-4 p-lg-5">
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('warning') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-x-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filter Status Tabs -->
        <div class="d-flex gap-2 overflow-auto pb-2 mb-4">
            <a href="<?= base_url('admin/pembayaran') ?>" class="btn btn-sm <?= empty($current_status) ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Semua</a>
            <a href="<?= base_url('admin/pembayaran?status=menunggu_verifikasi') ?>" class="btn btn-sm <?= ($current_status === 'menunggu_verifikasi') ? 'btn-warning' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">
                <i class="bi bi-clock-history me-1"></i> Menunggu Verifikasi
            </a>
            <a href="<?= base_url('admin/pembayaran?status=lunas') ?>" class="btn btn-sm <?= ($current_status === 'lunas') ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">
                <i class="bi bi-check2-circle me-1"></i> Lunas
            </a>
            <a href="<?= base_url('admin/pembayaran?status=ditolak') ?>" class="btn btn-sm <?= ($current_status === 'ditolak') ? 'btn-danger' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">
                <i class="bi bi-x-circle me-1"></i> Ditolak
            </a>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">Daftar Transaksi Pembayaran</h6>
                <span class="text-muted small">Total: <?= count($payments) ?> Data</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Metode & Akun</th>
                            <th>Nominal</th>
                            <th>Bukti Bayar</th>
                            <th>Status</th>
                            <th>Waktu & Catatan</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments)): ?>
                            <?php foreach ($payments as $p): ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url('admin/pesanan/detail/' . $p['order_id']) ?>" class="fw-bold font-monospace text-decoration-none text-success">
                                        #<?= esc($p['order_number']) ?>
                                    </a>
                                    <span class="d-block text-muted small"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></span>
                                </td>
                                <td>
                                    <strong class="text-dark d-block small"><?= esc($p['customer_nama'] ?? 'Pembeli') ?></strong>
                                    <span class="text-muted" style="font-size: 0.75rem;"><?= esc($p['customer_email'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= strtoupper(esc($p['metode'])) ?></span>
                                    <?php if (!empty($p['bank'])): ?>
                                        <div class="small fw-semibold text-secondary mt-1"><?= esc($p['bank']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($p['atas_nama'])): ?>
                                        <div class="text-muted" style="font-size: 0.72rem;">a.n <?= esc($p['atas_nama']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-dark small"><?= format_rupiah($p['grand_total'] ?? $p['jumlah'] ?? 0) ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($p['bukti_bayar'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalBukti<?= $p['id'] ?>">
                                            <i class="bi bi-image me-1"></i> Lihat Bukti
                                        </button>

                                        <!-- Modal Bukti Bayar -->
                                        <div class="modal fade" id="modalBukti<?= $p['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header border-bottom">
                                                        <h6 class="modal-title fw-bold">Bukti Pembayaran #<?= esc($p['order_number']) ?></h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center p-4">
                                                        <img src="<?= base_url('uploads/bukti_bayar/' . $p['bukti_bayar']) ?>" 
                                                             alt="Bukti Bayar" 
                                                             class="img-fluid rounded shadow-sm mb-3"
                                                             style="max-height: 450px;"
                                                             onerror="this.src='https://placehold.co/500x700?text=Bukti+Bayar+Tidak+Ditemukan';">
                                                        <div class="small text-muted">
                                                            Nominal: <strong class="text-dark"><?= format_rupiah($p['grand_total'] ?? $p['jumlah'] ?? 0) ?></strong> | 
                                                            Bank: <strong class="text-dark"><?= esc($p['bank'] ?? '-') ?></strong> (a.n <?= esc($p['atas_nama'] ?? '-') ?>)
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <a href="<?= base_url('uploads/bukti_bayar/' . $p['bukti_bayar']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Gambar Penuh
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Belum diunggah</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= payment_badge($p['status']) ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['verified_at'])): ?>
                                        <div class="small text-muted"><i class="bi bi-clock me-1"></i><?= date('d/m/y H:i', strtotime($p['verified_at'])) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($p['catatan'])): ?>
                                        <div class="small text-secondary fst-italic" style="max-width: 180px; font-size: 0.75rem;"><?= esc($p['catatan']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($p['status'] === 'menunggu_verifikasi' || $p['status'] === 'pending'): ?>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalApprove<?= $p['id'] ?>" title="Verifikasi Lunas">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalReject<?= $p['id'] ?>" title="Tolak Bukti">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>

                                        <!-- Modal Approve -->
                                        <div class="modal fade" id="modalApprove<?= $p['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form action="<?= base_url('admin/pembayaran/verifikasi') ?>" method="POST" class="modal-content text-start border-0 shadow">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                                    <div class="modal-header border-bottom">
                                                        <h6 class="modal-title fw-bold text-success"><i class="bi bi-check-circle me-1"></i> Konfirmasi Terima Pembayaran</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted mb-3">
                                                            Pastikan dana sebesar <strong><?= format_rupiah($p['grand_total'] ?? $p['jumlah'] ?? 0) ?></strong> untuk pesanan <strong>#<?= esc($p['order_number']) ?></strong> telah benar-benar masuk ke rekening kampus Polinela.
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Catatan Verifikasi (Opsional)</label>
                                                            <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Contoh: Dana telah dicek via m-Banking mutasi masuk">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-sm btn-success px-3">
                                                            <i class="bi bi-check2-circle me-1"></i> Setujui (LUNAS)
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Modal Reject -->
                                        <div class="modal fade" id="modalReject<?= $p['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form action="<?= base_url('admin/pembayaran/tolak') ?>" method="POST" class="modal-content text-start border-0 shadow">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                                    <div class="modal-header border-bottom">
                                                        <h6 class="modal-title fw-bold text-danger"><i class="bi bi-x-circle me-1"></i> Tolak Bukti Pembayaran</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted mb-3">
                                                            Pesanan <strong>#<?= esc($p['order_number']) ?></strong> akan dikembalikan ke status belum bayar dan konsumen akan menerima notifikasi penolakan.
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                                                            <textarea name="catatan" class="form-control form-control-sm" rows="3" required placeholder="Contoh: Bukti buram, nominal transfer kurang, atau dana tidak ditemukan di rekening tujuan."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-sm btn-danger px-3">
                                                            <i class="bi bi-x-lg me-1"></i> Tolak Pembayaran
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                    <?php else: ?>
                                        <a href="<?= base_url('admin/pesanan/detail/' . $p['order_id']) ?>" class="btn btn-sm btn-light border text-muted px-2 py-1" title="Lihat Pesanan">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-cash-stack fs-1 d-block text-secondary mb-2"></i>
                                    Tidak ada data pembayaran yang ditemukan.
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
