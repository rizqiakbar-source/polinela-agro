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
        <h5 class="fw-extrabold mb-0">Kelola Kategori Produk</h5>
        <button class="btn btn-agro btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
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
                <h6 class="fw-bold mb-0">Daftar Kategori Komoditas</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Icon</th>
                            <th>Nama Kategori</th>
                            <th>Slug URL</th>
                            <th>Deskripsi Singkat</th>
                            <th class="text-center">Jumlah Produk</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>
                                <div class="rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.25rem;">
                                    <i class="bi <?= esc($cat['icon'] ?: 'bi-tag') ?>"></i>
                                </div>
                            </td>
                            <td><strong class="text-dark small"><?= esc($cat['nama_kategori']) ?></strong></td>
                            <td class="small font-monospace text-muted"><?= esc($cat['slug']) ?></td>
                            <td class="small text-muted"><?= esc($cat['deskripsi'] ?: '-') ?></td>
                            <td class="text-center"><span class="badge bg-light text-dark border"><?= $cat['product_count'] ?? 0 ?> Produk</span></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?= $cat['id'] ?>"><i class="bi bi-pencil-square"></i></button>
                                    <a href="<?= base_url('admin/kategori/delete/' . $cat['id']) ?>" class="btn btn-outline-danger btn-delete-confirm"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Kategori -->
                        <div class="modal fade" id="editCategoryModal<?= $cat['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="<?= base_url('admin/kategori/update/' . $cat['id']) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <div class="modal-header border-0">
                                            <h6 class="modal-title fw-bold">Edit Kategori</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Nama Kategori</label>
                                                <input type="text" name="nama_kategori" class="form-control" value="<?= esc($cat['nama_kategori']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Bootstrap Icon Class</label>
                                                <input type="text" name="icon" class="form-control" value="<?= esc($cat['icon']) ?>" placeholder="bi-cup-hot, bi-tree, dll">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Deskripsi</label>
                                                <textarea name="deskripsi" class="form-control" rows="3"><?= esc($cat['deskripsi']) ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-agro btn-sm">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= base_url('admin/kategori/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-bold">Tambah Kategori Komoditas Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kategori</label>
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Kopi Nusantara" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Bootstrap Icon Class</label>
                        <input type="text" name="icon" class="form-control" placeholder="bi-cup-hot">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi Singkat</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Penjelasan tentang kategori komoditas ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-agro btn-sm">Tambah Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
