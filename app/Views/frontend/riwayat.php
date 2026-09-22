<?= view('layout/header', ['title' => 'Riwayat Pesanan - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item active">Riwayat Pesanan</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-extrabold mb-0"><i class="bi bi-clock-history text-success me-2"></i> Riwayat Pesanan Saya</h3>
        <a href="<?= base_url('katalog') ?>" class="btn btn-agro btn-sm px-3">
            <i class="bi bi-bag-plus me-1"></i> Belanja Lagi
        </a>
    </div>

    <!-- Filter Status Tabs -->
    <div class="d-flex gap-2 overflow-auto pb-2 mb-4">
        <a href="<?= base_url('pesanan') ?>" class="btn btn-sm <?= empty($current_status) ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Semua Pesanan</a>
        <a href="<?= base_url('pesanan?status=pending') ?>" class="btn btn-sm <?= ($current_status === 'pending') ? 'btn-warning' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Menunggu Pembayaran</a>
        <a href="<?= base_url('pesanan?status=menunggu_verifikasi') ?>" class="btn btn-sm <?= ($current_status === 'menunggu_verifikasi') ? 'btn-info' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Menunggu Verifikasi</a>
        <a href="<?= base_url('pesanan?status=diproses') ?>" class="btn btn-sm <?= ($current_status === 'diproses') ? 'btn-primary' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Diproses Unit</a>
        <a href="<?= base_url('pesanan?status=dikirim') ?>" class="btn btn-sm <?= ($current_status === 'dikirim') ? 'btn-info' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Sedang Dikirim</a>
        <a href="<?= base_url('pesanan?status=selesai') ?>" class="btn btn-sm <?= ($current_status === 'selesai') ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Selesai</a>
    </div>

    <?php if (!empty($orders)): ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($orders as $o): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-bottom pb-3 mb-3">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="fw-extrabold text-dark mb-0">#<?= esc($o['order_number']) ?></h6>
                            <?= status_badge($o['status']) ?>
                        </div>
                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> <?= format_tanggal($o['created_at']) ?></small>
                    </div>
                    <div class="text-md-end">
                        <div class="small text-muted">Grand Total:</div>
                        <h5 class="fw-extrabold text-success mb-0"><?= format_rupiah($o['grand_total']) ?></h5>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="small text-muted">
                        <div><i class="bi bi-truck me-1"></i> Kurir: <strong><?= esc($o['kurir'] ?? 'Kurir Kampus') ?></strong></div>
                        <div><i class="bi bi-credit-card me-1"></i> Metode: <strong><?= strtoupper($o['payment_metode'] ?? 'Transfer') ?></strong></div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('pesanan/detail/' . $o['order_number']) ?>" class="btn btn-agro btn-sm px-3">
                            <i class="bi bi-eye me-1"></i> Detail Pesanan
                        </a>
                        <a href="<?= base_url('invoice/' . $o['order_number']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm px-3">
                            <i class="bi bi-receipt me-1"></i> Invoice
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <i class="bi bi-bag-x display-3 text-muted mb-3"></i>
            <h5 class="fw-bold">Belum Ada Riwayat Pesanan</h5>
            <p class="text-muted small mb-4">Anda belum memiliki transaksi pesanan pada kategori status ini.</p>
            <div>
                <a href="<?= base_url('katalog') ?>" class="btn btn-agro px-4 py-2">Mulai Belanja Sekarang</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= view('layout/footer') ?>
