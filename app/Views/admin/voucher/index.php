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
            <h5 class="fw-extrabold mb-0">Kelola Voucher Diskon Promo</h5>
            <small class="text-muted">Buat kode kupon diskon untuk civitas akademika dan pembeli umum</small>
        </div>
        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddVoucher">
            <i class="bi bi-tag-fill me-1"></i> Buat Voucher Baru
        </button>
    </header>

    <div class="p-4 p-lg-5">
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <ul class="mb-0 ps-3">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">Daftar Kode Promo & Voucher</h6>
                <span class="text-muted small">Total: <?= count($vouchers) ?> Kupon</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode Kupon</th>
                            <th>Nama Promo</th>
                            <th>Besaran Diskon</th>
                            <th>Min. Belanja</th>
                            <th>Maks. Potongan</th>
                            <th>Penggunaan / Kuota</th>
                            <th>Periode Berlaku</th>
                            <th class="text-center" style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($vouchers)): ?>
                            <?php foreach ($vouchers as $v): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace fs-6 px-2 py-1">
                                        <?= esc($v['kode']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-dark small d-block"><?= esc($v['nama']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($v['tipe'] === 'percent'): ?>
                                        <span class="badge bg-primary rounded-pill"><?= (int)$v['diskon'] ?>%</span>
                                    <?php else: ?>
                                        <strong class="text-success small"><?= format_rupiah($v['diskon']) ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?= format_rupiah($v['min_belanja']) ?>
                                </td>
                                <td class="small text-muted">
                                    <?= $v['max_diskon'] ? format_rupiah($v['max_diskon']) : '-' ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small fw-semibold text-dark"><?= $v['terpakai'] ?> / <?= $v['kuota'] ?></span>
                                        <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                            <?php $pct = $v['kuota'] > 0 ? min(100, round(($v['terpakai'] / $v['kuota']) * 100)) : 0; ?>
                                            <div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="small text-muted">
                                    <?php if (!empty($v['tgl_mulai']) && !empty($v['tgl_berakhir'])): ?>
                                        <?= date('d/m/y', strtotime($v['tgl_mulai'])) ?> - <?= date('d/m/y', strtotime($v['tgl_berakhir'])) ?>
                                    <?php else: ?>
                                        <span class="text-success">Berlaku Selamanya</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('admin/voucher/delete/' . $v['id']) ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Hapus Voucher"
                                       onclick="return confirm('Hapus voucher promosi ini?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-ticket-perforated fs-1 d-block text-secondary mb-2"></i>
                                    Belum ada voucher promo yang dibuat.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal Tambah Voucher -->
<div class="modal fade" id="modalAddVoucher" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('admin/voucher/create') ?>" method="POST" class="modal-content border-0 shadow">
            <?= csrf_field() ?>
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold"><i class="bi bi-tag-fill me-1 text-success"></i> Buat Kode Voucher Promo</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kode Voucher <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control form-control-sm text-uppercase font-monospace fw-bold" required placeholder="Contoh: PANENRAYA10">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tipe Diskon <span class="text-danger">*</span></label>
                        <select name="tipe" class="form-select form-select-sm" required>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                            <option value="percent">Persentase (%)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Promo / Keterangan <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control form-control-sm" required placeholder="Contoh: Diskon Panen Raya Civitas Polinela">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nilai Diskon <span class="text-danger">*</span></label>
                        <input type="number" name="diskon" class="form-control form-control-sm" required min="1" placeholder="Misal: 10000 atau 15">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Maksimal Diskon (Rp)</label>
                        <input type="number" name="max_diskon" class="form-control form-control-sm" placeholder="Khusus tipe persentase">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Minimal Belanja (Rp)</label>
                        <input type="number" name="min_belanja" class="form-control form-control-sm" value="0" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kuota Pemakaian</label>
                        <input type="number" name="kuota" class="form-control form-control-sm" value="100" min="1">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tanggal Mulai</label>
                        <input type="date" name="tgl_mulai" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tanggal Berakhir</label>
                        <input type="date" name="tgl_berakhir" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-sm btn-success px-3">
                    <i class="bi bi-save me-1"></i> Simpan Voucher
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
