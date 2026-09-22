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
            <h5 class="fw-extrabold mb-0">Kelola Banner Promo Beranda</h5>
            <small class="text-muted">Kelola slide promosi dan pengumuman visual di halaman utama Polinela Agro Digital</small>
        </div>
        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddBanner">
            <i class="bi bi-plus-lg me-1"></i> Tambah Banner
        </button>
    </header>

    <div class="p-4 p-lg-5">
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">Daftar Banner Aktif</h6>
                <span class="text-muted small">Total: <?= count($banners) ?> Slide</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Urutan</th>
                            <th style="width: 160px;">Pratinjau Gambar</th>
                            <th>Judul Banner</th>
                            <th>Subjudul / Deskripsi</th>
                            <th>Tautan (Link)</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($banners)): ?>
                            <?php foreach ($banners as $b): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 fs-6"><?= $b['urutan'] ?></span>
                                </td>
                                <td>
                                    <div class="rounded overflow-hidden border shadow-sm" style="width: 150px; height: 75px; background: #eee;">
                                        <img src="<?= base_url('uploads/banner/' . $b['gambar']) ?>" 
                                             alt="<?= esc($b['judul']) ?>" 
                                             class="w-100 h-100 object-fit-cover"
                                             onerror="this.src='https://placehold.co/600x300/1e4d2b/ffffff?text=Banner+Polinela';">
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-dark small d-block"><?= esc($b['judul']) ?></strong>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= esc($b['subjudul'] ?: '-') ?></span>
                                </td>
                                <td>
                                    <code class="text-success small"><?= esc($b['link'] ?: 'katalog') ?></code>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('admin/banner/delete/' . $b['id']) ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Hapus Banner"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus banner promosi ini?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-images fs-1 d-block text-secondary mb-2"></i>
                                    Belum ada banner promosi yang diunggah.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal Tambah Banner -->
<div class="modal fade" id="modalAddBanner" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('admin/banner/create') ?>" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            <?= csrf_field() ?>
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold"><i class="bi bi-image me-1 text-success"></i> Tambah Banner Promo</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Judul Banner <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control form-control-sm" required placeholder="Contoh: Panen Raya Kopi Robusta & Arabika">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Subjudul / Deskripsi Singkat</label>
                    <input type="text" name="subjudul" class="form-control form-control-sm" placeholder="Contoh: Dapatkan diskon 15% untuk pembelian kemasan 500gr">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tautan Tujuan (Link)</label>
                    <input type="text" name="link" class="form-control form-control-sm" placeholder="Contoh: katalog?kategori=kopi atau produk/detail/kopi-robusta">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nomor Urutan Slide</label>
                        <input type="number" name="urutan" class="form-control form-control-sm" value="1" min="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">File Gambar Banner <span class="text-danger">*</span></label>
                        <input type="file" name="gambar" class="form-control form-control-sm" required accept="image/*">
                    </div>
                </div>
                <div class="text-muted" style="font-size: 0.72rem;">
                    <i class="bi bi-info-circle me-1"></i> Rekomendasi dimensi gambar: 1920x600 px (Rasio 16:5), format JPG/PNG/WEBP maksimal 2MB.
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-sm btn-success px-3">
                    <i class="bi bi-upload me-1"></i> Unggah Banner
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
