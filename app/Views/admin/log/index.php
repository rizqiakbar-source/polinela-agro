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
            <h5 class="fw-extrabold mb-0">Log Aktivitas & Audit Trail</h5>
            <small class="text-muted">Jejak audit dan rekaman aktivitas pengguna di seluruh sistem Polinela Agro Digital</small>
        </div>
    </header>

    <div class="p-4 p-lg-5">
        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">100 Catatan Aktivitas Terakhir</h6>
                <span class="text-muted small">Total: <?= count($logs) ?> Entri</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Waktu Kejadian</th>
                            <th style="width: 180px;">Pengguna (User)</th>
                            <th style="width: 140px;">Modul Sistem</th>
                            <th style="width: 160px;">Tindakan (Action)</th>
                            <th>Deskripsi / Detail Rincian</th>
                            <th style="width: 120px;">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $l): ?>
                            <tr>
                                <td class="small text-muted text-nowrap">
                                    <i class="bi bi-clock me-1"></i><?= date('d M Y H:i:s', strtotime($l['created_at'])) ?>
                                </td>
                                <td>
                                    <strong class="text-dark small d-block"><?= esc($l['user_nama'] ?? 'Sistem') ?></strong>
                                    <span class="text-muted" style="font-size: 0.72rem;"><?= esc($l['user_email'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= esc($l['module']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <?= esc($l['action']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-secondary"><?= esc($l['description']) ?></span>
                                </td>
                                <td class="small font-monospace text-muted">
                                    <?= esc($l['ip_address'] ?? '127.0.0.1') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block text-secondary mb-2"></i>
                                    Belum ada log aktivitas yang tercatat di sistem.
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
