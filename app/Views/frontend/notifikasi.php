<?= view('layout/header', ['title' => 'Notifikasi - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item active">Pemberitahuan Sistem</li>
        </ol>
    </nav>

    <h3 class="fw-extrabold mb-4"><i class="bi bi-bell text-warning me-2"></i> Kotak Notifikasi</h3>

    <?php if (!empty($notifications)): ?>
        <div class="list-group shadow-sm rounded-4 border-0 overflow-hidden bg-white">
            <?php foreach ($notifications as $n): ?>
            <div class="list-group-item list-group-item-action p-3 d-flex align-items-start gap-3 border-bottom <?= $n['is_read'] ? 'bg-white' : 'bg-light' ?>">
                <div class="rounded-circle bg-success-subtle text-success p-2 fs-5">
                    <i class="bi bi-info-circle-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="text-dark small"><?= esc($n['judul']) ?></strong>
                        <small class="text-muted" style="font-size: 0.72rem;"><?= format_tanggal($n['created_at']) ?></small>
                    </div>
                    <p class="small text-muted mb-2"><?= esc($n['pesan']) ?></p>
                    <?php if (!empty($n['link'])): ?>
                    <a href="<?= esc($n['link']) ?>" class="btn btn-agro btn-sm px-3" style="font-size: 0.75rem;">
                        Buka Tautan <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <i class="bi bi-bell-slash display-3 text-muted mb-3"></i>
            <h5 class="fw-bold">Tidak Ada Notifikasi</h5>
            <p class="text-muted small mb-0">Semua aktivitas dan info terbaru pesanan Anda akan muncul di sini.</p>
        </div>
    <?php endif; ?>
</div>

<?= view('layout/footer') ?>
