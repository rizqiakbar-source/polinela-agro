<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard - Admin Polinela Agro Digital') ?></title>
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.min.css">
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">

<?= view('layout/sidebar_admin') ?>

<main class="admin-main">
    <!-- Top Navbar Admin -->
    <header class="admin-header">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light d-lg-none" type="button" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
                <i class="bi bi-list fs-4"></i>
            </button>
            <h5 class="fw-extrabold mb-0 text-dark">Dashboard Analitik</h5>
        </div>

        <div class="d-flex align-items-center gap-3">
            <a href="<?= base_url('/') ?>" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3">
                <i class="bi bi-globe me-1"></i> Beranda Toko
            </a>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                        <?= strtoupper(substr(session()->get('user_nama') ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="d-none d-md-block text-start">
                        <div class="small fw-bold text-dark leading-tight"><?= esc(session()->get('user_nama')) ?></div>
                        <div class="text-muted" style="font-size: 0.72rem;"><?= ucfirst(str_replace('_', ' ', $role)) ?></div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                    <li><a class="dropdown-item py-2 text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Body Container -->
    <div class="p-4 p-lg-5">
        <!-- Global Alert Flashdata -->
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- 4 Kartu KPI Metrik Utama -->
        <div class="row g-4 mb-4">
            <!-- Total Pendapatan -->
            <div class="col-sm-6 col-xl-3">
                <div class="kpi-card">
                    <div>
                        <div class="small fw-semibold text-muted mb-1">Total Pendapatan (Lunas)</div>
                        <h4 class="fw-extrabold text-success mb-0"><?= format_rupiah($total_pendapatan) ?></h4>
                    </div>
                    <div class="kpi-icon primary">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
            </div>

            <!-- Total Pesanan -->
            <div class="col-sm-6 col-xl-3">
                <div class="kpi-card">
                    <div>
                        <div class="small fw-semibold text-muted mb-1">Total Transaksi Pesanan</div>
                        <h4 class="fw-extrabold text-primary mb-0"><?= $total_pesanan ?></h4>
                    </div>
                    <div class="kpi-icon info">
                        <i class="bi bi-cart-check"></i>
                    </div>
                </div>
            </div>

            <!-- Total Produk Aktif -->
            <div class="col-sm-6 col-xl-3">
                <div class="kpi-card">
                    <div>
                        <div class="small fw-semibold text-muted mb-1">Produk Perkebunan Aktif</div>
                        <h4 class="fw-extrabold text-dark mb-0"><?= $total_produk ?></h4>
                    </div>
                    <div class="kpi-icon warning">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>

            <!-- Peringatan Stok Kritis -->
            <div class="col-sm-6 col-xl-3">
                <div class="kpi-card">
                    <div>
                        <div class="small fw-semibold text-muted mb-1">Peringatan Stok Kritis</div>
                        <h4 class="fw-extrabold text-danger mb-0"><?= $stok_kritis ?> Produk</h4>
                    </div>
                    <div class="kpi-icon danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Penjualan & Ringkasan SUS Score -->
        <div class="row g-4 mb-4">
            <!-- Grafik Tren Penjualan -->
            <div class="col-lg-8">
                <div class="admin-card h-100">
                    <div class="admin-card-header">
                        <h6 class="fw-bold mb-0"><i class="bi bi-graph-up text-success me-2"></i> Tren Pendapatan Penjualan Bulanan (<?= date('Y') ?>)</h6>
                        <span class="badge bg-light text-muted border">Berdasarkan Order Selesai</span>
                    </div>
                    <div class="admin-card-body">
                        <canvas id="salesChart" style="max-height: 280px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Kartu Skor Evaluasi SUS -->
            <div class="col-lg-4">
                <div class="admin-card h-100">
                    <div class="admin-card-header">
                        <h6 class="fw-bold mb-0"><i class="bi bi-award text-warning me-2"></i> Evaluasi Usability (SUS)</h6>
                        <a href="<?= base_url('sus') ?>" target="_blank" class="small text-success text-decoration-none">Lihat Form →</a>
                    </div>
                    <div class="admin-card-body text-center p-4">
                        <div class="small text-muted mb-2">Rata-Rata Skor System Usability Scale</div>
                        <div class="display-4 fw-extrabold text-success mb-2">
                            <?= $sus_summary['average_score'] ?>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-success fs-6 px-3 py-2 rounded-pill">
                                Kategori: <?= esc($sus_summary['kategori']) ?>
                            </span>
                        </div>
                        <div class="small text-muted border-top pt-3">
                            Total Responden: <strong><?= $sus_summary['total_respondents'] ?></strong> orang (Civitas & Pengguna)
                        </div>
                        <div class="mt-2" style="font-size: 0.72rem; color: #64748b;">
                            Standar acuan SUS: Skor &ge; 68 menunjukkan antarmuka di atas rata-rata industri.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Pesanan Terbaru & Top 5 Produk -->
        <div class="row g-4 mb-4">
            <!-- Pesanan Terbaru -->
            <div class="col-lg-8">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-success me-2"></i> Pesanan Masuk Terbaru</h6>
                        <?php if ($role !== 'pimpinan'): ?>
                        <a href="<?= base_url('admin/pesanan') ?>" class="small text-success text-decoration-none fw-bold">Kelola Semua Pesanan →</a>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No Pesanan</th>
                                    <th>Pembeli</th>
                                    <th>Grand Total</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <?php if ($role !== 'pimpinan'): ?>
                                    <th class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_orders)): ?>
                                    <?php foreach ($recent_orders as $ro): ?>
                                    <tr>
                                        <td>
                                            <strong class="font-monospace text-dark small">#<?= esc($ro['order_number']) ?></strong>
                                            <div class="text-muted" style="font-size: 0.72rem;"><?= date('d M H:i', strtotime($ro['created_at'])) ?></div>
                                        </td>
                                        <td class="small"><?= esc($ro['customer_nama'] ?? 'Pembeli') ?></td>
                                        <td class="small fw-bold text-success"><?= format_rupiah($ro['grand_total']) ?></td>
                                        <td class="small"><span class="badge bg-light text-dark border text-uppercase"><?= esc($ro['payment_metode'] ?? 'Transfer') ?></span></td>
                                        <td><?= status_badge($ro['status']) ?></td>
                                        <?php if ($role !== 'pimpinan'): ?>
                                        <td class="text-center">
                                            <a href="<?= base_url('admin/pesanan/detail/' . $ro['id']) ?>" class="btn btn-sm btn-light p-1 px-2 text-success" title="Buka Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted small">Belum ada pesanan masuk.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top 5 Produk Terlaris -->
            <div class="col-lg-4">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h6 class="fw-bold mb-0"><i class="bi bi-fire text-danger me-2"></i> Top 5 Produk Terlaris</h6>
                    </div>
                    <div class="admin-card-body p-3">
                        <div class="d-flex flex-column gap-3">
                            <?php if (!empty($top_products)): ?>
                                <?php foreach ($top_products as $idx => $tp): ?>
                                <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success rounded-circle" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.72rem;"><?= $idx + 1 ?></span>
                                        <div>
                                            <div class="fw-bold small text-dark text-truncate" style="max-width: 170px;" title="<?= esc($tp['nama_produk']) ?>">
                                                <?= esc($tp['nama_produk']) ?>
                                            </div>
                                            <span class="text-muted" style="font-size: 0.72rem;"><?= esc($tp['nama_unit'] ?? 'Polinela') ?></span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-success small d-block"><?= $tp['terjual'] ?> terjual</strong>
                                        <span class="text-muted" style="font-size: 0.72rem;"><?= format_rupiah($tp['harga']) ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small text-center my-3">Belum ada data penjualan.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performa Unit Usaha (Super Admin & Pimpinan) -->
        <?php if (!empty($unit_stats)): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-buildings text-success me-2"></i> Ringkasan Performa Unit Usaha Perkebunan Polinela</h6>
                <a href="<?= base_url('admin/laporan?type=per_unit') ?>" class="small text-success text-decoration-none fw-bold">Laporan Lengkap Unit →</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Unit Usaha</th>
                            <th>Penanggung Jawab</th>
                            <th class="text-center">Total Produk</th>
                            <th class="text-center">Total Item Terjual</th>
                            <th class="text-end">Total Omset Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unit_stats as $us): ?>
                        <tr>
                            <td>
                                <strong><?= esc($us['nama_unit']) ?></strong>
                                <div class="text-muted small"><?= esc($us['lokasi'] ?? '') ?></div>
                            </td>
                            <td class="small"><?= esc($us['pj_nama'] ?: '-') ?></td>
                            <td class="text-center small fw-bold"><?= $us['total_produk'] ?></td>
                            <td class="text-center small fw-bold"><?= $us['total_terjual'] ?></td>
                            <td class="text-end small fw-extrabold text-success"><?= format_rupiah($us['total_omset']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Render Chart.js Penjualan
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($monthly_chart['labels']) ?>,
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: <?= json_encode($monthly_chart['totals']) ?>,
            borderColor: '#15803d',
            backgroundColor: 'rgba(21, 128, 61, 0.12)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.3,
            pointBackgroundColor: '#15803d',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'k';
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>
