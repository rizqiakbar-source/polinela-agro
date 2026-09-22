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
            <h5 class="fw-extrabold mb-0">Laporan & Rekapitulasi Data</h5>
            <small class="text-muted">Analisis penjualan, stok inventori komoditas, dan kinerja unit usaha Polinela</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url("admin/laporan/pdf?type={$type}&start_date={$start_date}&end_date={$end_date}&unit_id={$unit_id}") ?>" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak PDF
            </a>
            <a href="<?= base_url("admin/laporan/excel?type={$type}&start_date={$start_date}&end_date={$end_date}&unit_id={$unit_id}") ?>" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Ekspor CSV / Excel
            </a>
        </div>
    </header>

    <div class="p-4 p-lg-5">
        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <form action="<?= base_url('admin/laporan') ?>" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Jenis Laporan</label>
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="penjualan" <?= $type === 'penjualan' ? 'selected' : '' ?>>Laporan Penjualan Transaksi</option>
                        <option value="produk_terlaris" <?= $type === 'produk_terlaris' ? 'selected' : '' ?>>Produk Komoditas Terlaris</option>
                        <option value="stok" <?= $type === 'stok' ? 'selected' : '' ?>>Inventori & Mutasi Stok</option>
                        <option value="pelanggan" <?= $type === 'pelanggan' ? 'selected' : '' ?>>Data Pelanggan & Pembeli</option>
                        <option value="keuangan" <?= $type === 'keuangan' ? 'selected' : '' ?>>Arus Kas & Rekap Omset</option>
                        <option value="per_unit" <?= $type === 'per_unit' ? 'selected' : '' ?>>Performa Per Unit Usaha</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($start_date) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($end_date) ?>">
                </div>
                <?php if ($role !== 'admin_unit'): ?>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Unit Usaha Tefa</label>
                    <select name="unit_id" class="form-select form-select-sm">
                        <option value="">Semua Unit Usaha</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $unit_id == $u['id'] ? 'selected' : '' ?>><?= esc($u['nama_unit']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-sm btn-dark w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Render Table Depending on Type -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0 text-capitalize">
                    Hasil Rekap: <?= str_replace('_', ' ', $type) ?> 
                    <span class="text-muted fw-normal small">(<?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?>)</span>
                </h6>
                <span class="badge bg-light text-dark border"><?= count($report_data) ?> Baris Data</span>
            </div>

            <div class="table-responsive">
                <?php if ($type === 'penjualan'): ?>
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No Pesanan</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Unit Usaha</th>
                                <th>Subtotal</th>
                                <th>Ongkir</th>
                                <th>Diskon</th>
                                <th>Grand Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($report_data)): ?>
                                <?php $totOmset = 0; foreach ($report_data as $row): $totOmset += $row['grand_total']; ?>
                                <tr>
                                    <td class="font-monospace fw-bold small">#<?= esc($row['order_number']) ?></td>
                                    <td class="small text-muted"><?= date('d/m/y H:i', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <div class="small fw-semibold"><?= esc($row['customer_nama'] ?? '-') ?></div>
                                        <div class="text-muted" style="font-size: 0.72rem;"><?= esc($row['customer_email'] ?? '') ?></div>
                                    </td>
                                    <td class="small"><?= esc($row['nama_unit'] ?? 'Polinela') ?></td>
                                    <td class="small"><?= format_rupiah($row['total_produk']) ?></td>
                                    <td class="small"><?= format_rupiah($row['ongkir']) ?></td>
                                    <td class="small text-danger">-<?= format_rupiah($row['diskon']) ?></td>
                                    <td><strong class="text-success small"><?= format_rupiah($row['grand_total']) ?></strong></td>
                                    <td><?= status_badge($row['status']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-light fw-bold">
                                    <td colspan="7" class="text-end">Total Penjualan:</td>
                                    <td class="text-success"><?= format_rupiah($totOmset) ?></td>
                                    <td></td>
                                </tr>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data transaksi pada rentang tanggal ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php elseif ($type === 'produk_terlaris'): ?>
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Peringkat</th>
                                <th>Nama Produk Komoditas</th>
                                <th>Unit Usaha</th>
                                <th>Kategori</th>
                                <th>Harga Satuan</th>
                                <th>Total Terjual</th>
                                <th>Estimasi Omset</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($report_data)): ?>
                                <?php $rank = 1; foreach ($report_data as $row): ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $rank <= 3 ? 'bg-warning text-dark' : 'bg-light text-dark border' ?> rounded-circle p-2">
                                            #<?= $rank++ ?>
                                        </span>
                                    </td>
                                    <td><strong class="text-dark small"><?= esc($row['nama_produk']) ?></strong></td>
                                    <td class="small"><?= esc($row['nama_unit']) ?></td>
                                    <td class="small"><span class="badge bg-light text-dark border"><?= esc($row['nama_kategori']) ?></span></td>
                                    <td class="small"><?= format_rupiah($row['harga']) ?> / <?= esc($row['satuan']) ?></td>
                                    <td><strong class="text-primary"><?= (int)$row['terjual'] ?> <?= esc($row['satuan']) ?></strong></td>
                                    <td><strong class="text-success"><?= format_rupiah($row['pendapatan']) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data penjualan produk.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php elseif ($type === 'stok'): ?>
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Komoditas Produk</th>
                                <th>Unit Usaha</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok Fisik</th>
                                <th>Batas Minimum</th>
                                <th>Status Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($report_data)): ?>
                                <?php foreach ($report_data as $row): ?>
                                <tr>
                                    <td><strong class="text-dark small"><?= esc($row['nama_produk']) ?></strong></td>
                                    <td class="small"><?= esc($row['nama_unit']) ?></td>
                                    <td class="small"><?= esc($row['nama_kategori']) ?></td>
                                    <td class="small"><?= format_rupiah($row['harga']) ?> / <?= esc($row['satuan']) ?></td>
                                    <td>
                                        <strong class="<?= $row['stok'] <= $row['stok_min'] ? 'text-danger' : 'text-dark' ?> fs-6">
                                            <?= $row['stok'] ?> <?= esc($row['satuan']) ?>
                                        </strong>
                                    </td>
                                    <td class="small text-muted"><?= $row['stok_min'] ?> <?= esc($row['satuan']) ?></td>
                                    <td>
                                        <?php if ($row['stok'] <= 0): ?>
                                            <span class="badge bg-danger">Habis</span>
                                        <?php elseif ($row['stok'] <= $row['stok_min']): ?>
                                            <span class="badge bg-warning text-dark">Kritis / Menipis</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Aman Tersedia</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data stok produk.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php elseif ($type === 'pelanggan'): ?>
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Pelanggan</th>
                                <th>Kontak (Email / HP)</th>
                                <th>Terdaftar Sejak</th>
                                <th>Jumlah Pesanan Selesai</th>
                                <th>Total Akumulasi Belanja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($report_data)): ?>
                                <?php foreach ($report_data as $row): ?>
                                <tr>
                                    <td><strong class="text-dark small"><?= esc($row['nama']) ?></strong></td>
                                    <td>
                                        <div class="small"><?= esc($row['email']) ?></div>
                                        <div class="text-muted" style="font-size: 0.72rem;"><?= esc($row['no_hp'] ?: '-') ?></div>
                                    </td>
                                    <td class="small text-muted"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $row['total_pesanan'] ?> Transaksi</span></td>
                                    <td><strong class="text-success"><?= format_rupiah($row['total_belanja']) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data pelanggan terdaftar.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php elseif ($type === 'keuangan'): ?>
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Total Transaksi Sukses</th>
                                <th>Total Omset Penjualan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($report_data)): ?>
                                <?php $grandTot = 0; foreach ($report_data as $row): $grandTot += $row['total_omset']; ?>
                                <tr>
                                    <td>
                                        <strong class="text-dark small">
                                            <?= date('F Y', strtotime($row['bulan'] . '-01')) ?>
                                        </strong>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= $row['total_transaksi'] ?> Pesanan</span></td>
                                    <td><strong class="text-success"><?= format_rupiah($row['total_omset']) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-light fw-bold">
                                    <td>Total Omset Tahunan:</td>
                                    <td></td>
                                    <td class="text-success fs-6"><?= format_rupiah($grandTot) ?></td>
                                </tr>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">Tidak ada data rekap keuangan tahun ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                <?php elseif ($type === 'per_unit'): ?>
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Unit Usaha Tefa Polinela</th>
                                <th>Jumlah Produk Aktif</th>
                                <th>Total Item Terjual</th>
                                <th>Total Omset Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($report_data)): ?>
                                <?php $totSemua = 0; foreach ($report_data as $row): $totSemua += $row['total_pendapatan']; ?>
                                <tr>
                                    <td><strong class="text-dark small"><?= esc($row['nama_unit']) ?></strong></td>
                                    <td><span class="badge bg-light text-dark border"><?= (int)$row['total_produk'] ?> Produk</span></td>
                                    <td><strong class="text-primary"><?= (int)$row['total_terjual'] ?> Unit</strong></td>
                                    <td><strong class="text-success"><?= format_rupiah($row['total_pendapatan']) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-light fw-bold">
                                    <td>Total Seluruh Unit:</td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-success fs-6"><?= format_rupiah($totSemua) ?></td>
                                </tr>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Tidak ada data performa unit usaha.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
