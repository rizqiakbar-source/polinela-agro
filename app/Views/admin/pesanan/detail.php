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
        <div class="d-flex align-items-center gap-2">
            <h5 class="fw-extrabold mb-0">Pesanan #<?= esc($order['order_number']) ?></h5>
            <?= status_badge($order['status']) ?>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('admin/pesanan') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <a href="<?= base_url('invoice/pdf/' . $order['order_number']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i> Invoice PDF
            </a>
            <a href="<?= base_url('admin/pesanan/surat-jalan/' . $order['id']) ?>" target="_blank" class="btn btn-agro btn-sm">
                <i class="bi bi-printer me-1"></i> Cetak Surat Jalan
            </a>
        </div>
    </header>

    <div class="p-4 p-lg-5">
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Kolom Kiri: Tabel Barang & Detail Pengiriman -->
            <div class="col-lg-8">
                <!-- Rincian Produk -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h6 class="fw-bold mb-0">Rincian Komoditas Dipesan</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga Satuan</th>
                                    <th class="text-center">Kuantitas</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($details as $d): ?>
                                <tr>
                                    <td>
                                        <strong class="text-dark small d-block"><?= esc($d['nama_produk']) ?></strong>
                                        <span class="text-muted small" style="font-size: 0.72rem;"><?= esc($d['nama_unit'] ?? 'Polinela') ?></span>
                                    </td>
                                    <td class="small"><?= format_rupiah($d['harga']) ?></td>
                                    <td class="text-center small fw-bold"><?= $d['qty'] ?></td>
                                    <td class="text-end small fw-bold text-success"><?= format_rupiah($d['subtotal']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Info Pembeli & Alamat -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt text-success me-2"></i> Data Penerima & Pengiriman</h6>
                    </div>
                    <div class="admin-card-body">
                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nama Pembeli:</span>
                                <strong><?= esc($customer['nama'] ?? 'Pembeli') ?></strong> (<?= esc($customer['email'] ?? '-') ?>)
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nomor Telepon / WhatsApp:</span>
                                <strong><?= esc($shipping['penerima_telepon'] ?? '-') ?></strong>
                            </div>
                            <div class="col-12">
                                <span class="text-muted d-block">Alamat Tujuan:</span>
                                <strong class="text-dark"><?= esc($shipping['alamat_lengkap'] ?? '-') ?>, <?= esc($shipping['kota'] ?? '') ?> (Kode Pos: <?= esc($shipping['kode_pos'] ?? '-') ?>)</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Jasa Kurir:</span>
                                <span class="badge bg-light text-dark border"><?= esc($shipping['kurir'] ?? 'Kurir Kampus') ?></span>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted d-block">Nomor Resi:</span>
                                <strong class="text-primary font-monospace"><?= esc($shipping['no_resi'] ?: 'Belum diisi') ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Status & Biaya -->
            <div class="col-lg-4">
                <!-- Form Update Status Pesanan -->
                <div class="admin-card">
                    <div class="admin-card-header bg-light">
                        <h6 class="fw-bold mb-0"><i class="bi bi-gear-wide-connected text-primary me-2"></i> Perbarui Status Pesanan</h6>
                    </div>
                    <div class="admin-card-body">
                        <form action="<?= base_url('admin/pesanan/update-status') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Pilih Status Baru:</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="pending" <?= ($order['status'] === 'pending') ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                                    <option value="menunggu_verifikasi" <?= ($order['status'] === 'menunggu_verifikasi') ? 'selected' : '' ?>>Menunggu Verifikasi Pembayaran</option>
                                    <option value="diproses" <?= ($order['status'] === 'diproses') ? 'selected' : '' ?>>Diproses Unit Tefa</option>
                                    <option value="dikirim" <?= ($order['status'] === 'dikirim') ? 'selected' : '' ?>>Sedang Dikirim</option>
                                    <option value="selesai" <?= ($order['status'] === 'selesai') ? 'selected' : '' ?>>Selesai</option>
                                    <option value="dibatalkan" <?= ($order['status'] === 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nomor Resi / Bukti Kirim (Jika Dikirim):</label>
                                <input type="text" name="no_resi" class="form-control form-control-sm font-monospace" value="<?= esc($shipping['no_resi'] ?? '') ?>" placeholder="Nomor resi kurir...">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-agro btn-sm py-2">Update Status & Notifikasi</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Rincian Total Biaya -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h6 class="fw-bold mb-0">Rincian Pembayaran</h6>
                    </div>
                    <div class="admin-card-body">
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>Total Produk:</span>
                            <strong class="text-dark"><?= format_rupiah($order['total_produk']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>Biaya Ongkir:</span>
                            <strong class="text-dark"><?= format_rupiah($order['ongkir']) ?></strong>
                        </div>
                        <?php if ($order['diskon'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Diskon Voucher:</span>
                            <strong>- <?= format_rupiah($order['diskon']) ?></strong>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between pt-3 border-top mb-3">
                            <strong class="fs-6">Grand Total:</strong>
                            <strong class="fs-5 text-success"><?= format_rupiah($order['grand_total']) ?></strong>
                        </div>
                        <div class="p-2 rounded bg-light border small text-muted">
                            <div>Metode: <strong><?= strtoupper($payment['metode'] ?? 'Transfer') ?></strong></div>
                            <div>Status Bayar: <?= payment_badge($payment['status'] ?? 'pending') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
