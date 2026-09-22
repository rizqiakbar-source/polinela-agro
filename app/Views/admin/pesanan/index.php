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
        <h5 class="fw-extrabold mb-0">Kelola Transaksi Pesanan</h5>
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
            <a href="<?= base_url('admin/pesanan') ?>" class="btn btn-sm <?= empty($current_status) ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Semua</a>
            <a href="<?= base_url('admin/pesanan?status=pending') ?>" class="btn btn-sm <?= ($current_status === 'pending') ? 'btn-warning' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Menunggu Pembayaran</a>
            <a href="<?= base_url('admin/pesanan?status=menunggu_verifikasi') ?>" class="btn btn-sm <?= ($current_status === 'menunggu_verifikasi') ? 'btn-info' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Menunggu Verifikasi</a>
            <a href="<?= base_url('admin/pesanan?status=diproses') ?>" class="btn btn-sm <?= ($current_status === 'diproses') ? 'btn-primary' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Diproses Unit</a>
            <a href="<?= base_url('admin/pesanan?status=dikirim') ?>" class="btn btn-sm <?= ($current_status === 'dikirim') ? 'btn-info' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Dikirim</a>
            <a href="<?= base_url('admin/pesanan?status=selesai') ?>" class="btn btn-sm <?= ($current_status === 'selesai') ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Selesai</a>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">Daftar Semua Pesanan Pelanggan</h6>
                <span class="text-muted small">Total: <?= count($orders) ?> Pesanan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No Pesanan</th>
                            <th>Tanggal</th>
                            <th>Nama Pembeli</th>
                            <th>Pengiriman</th>
                            <th>Grand Total</th>
                            <th>Status Bayar</th>
                            <th>Status Order</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <strong class="font-monospace text-dark small">#<?= esc($o['order_number']) ?></strong>
                                </td>
                                <td class="small text-muted"><?= date('d M Y H:i', strtotime($o['created_at'])) ?></td>
                                <td>
                                    <strong class="text-dark small d-block"><?= esc($o['customer_nama'] ?? 'Pembeli') ?></strong>
                                    <span class="text-muted" style="font-size: 0.72rem;"><?= esc($o['customer_phone'] ?? '-') ?></span>
                                </td>
                                <td class="small">
                                    <span class="badge bg-light text-dark border"><?= esc($o['kurir'] ?? 'Kurir Kampus') ?></span>
                                    <?php if (!empty($o['no_resi'])): ?>
                                    <div class="font-monospace text-primary small" style="font-size: 0.7rem;">Resi: <?= esc($o['no_resi']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="small fw-extrabold text-success"><?= format_rupiah($o['grand_total']) ?></td>
                                <td><?= payment_badge($o['payment_status'] ?? 'pending') ?></td>
                                <td><?= status_badge($o['status']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('admin/pesanan/detail/' . $o['id']) ?>" class="btn btn-outline-primary" title="Buka Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= base_url('invoice/pdf/' . $o['order_number']) ?>" target="_blank" class="btn btn-outline-secondary" title="Invoice PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                        <a href="<?= base_url('admin/pesanan/surat-jalan/' . $o['id']) ?>" target="_blank" class="btn btn-outline-success" title="Cetak Surat Jalan">
                                            <i class="bi bi-truck"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center py-5 text-muted">Belum ada pesanan pada filter ini.</td></tr>
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
