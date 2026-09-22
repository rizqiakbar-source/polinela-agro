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
        <h5 class="fw-extrabold mb-0">Kelola Produk Perkebunan</h5>
        <a href="<?= base_url('admin/produk/create') ?>" class="btn btn-agro btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk Baru
        </a>
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
                <h6 class="fw-bold mb-0">Daftar Komoditas & Hasil Olahan Perkebunan</h6>
                <span class="text-muted small">Total: <?= count($products) ?> Produk</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama Produk</th>
                            <th>Unit Usaha</th>
                            <th>Kategori</th>
                            <th>Harga Satuan</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <img src="<?= base_url('assets/img/products/' . ($p['gambar_utama'] ?: 'default-product.png')) ?>" 
                                         class="rounded-3 border" 
                                         style="width: 48px; height: 48px; object-fit: cover;"
                                         onerror="this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=120&auto=format&fit=crop&q=80';">
                                </td>
                                <td>
                                    <strong class="text-dark small d-block"><?= esc($p['nama_produk']) ?></strong>
                                    <span class="text-muted small" style="font-size: 0.72rem;">Berat: <?= $p['berat_gram'] ?>g | Terjual: <?= $p['total_terjual'] ?></span>
                                </td>
                                <td class="small text-muted"><?= esc($p['nama_unit']) ?></td>
                                <td class="small"><span class="badge bg-light text-dark border"><?= esc($p['nama_kategori']) ?></span></td>
                                <td class="small fw-bold text-success"><?= format_rupiah($p['harga']) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= ($p['stok'] <= $p['stok_min']) ? 'bg-danger' : 'bg-success' ?> px-2 py-1">
                                        <?= $p['stok'] ?> <?= esc($p['satuan']) ?>
                                    </span>
                                    <!-- Shortcut Cepat Ubah Stok -->
                                    <button class="btn btn-sm btn-link text-muted p-0 d-block mx-auto mt-1" style="font-size: 0.7rem;" data-bs-toggle="modal" data-bs-target="#stokModal<?= $p['id'] ?>">
                                        <i class="bi bi-pencil me-1"></i> Sesuaikan
                                    </button>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= ($p['status'] === 'aktif') ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> px-2 py-1">
                                        <?= ucfirst($p['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('admin/produk/edit/' . $p['id']) ?>" class="btn btn-outline-primary" title="Edit Produk">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="<?= base_url('admin/produk/delete/' . $p['id']) ?>" class="btn btn-outline-danger btn-delete-confirm" title="Hapus Produk">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Sesuaikan Stok -->
                            <div class="modal fade" id="stokModal<?= $p['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <form action="<?= base_url('admin/produk/stock') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <div class="modal-header border-0 pb-0">
                                                <h6 class="modal-title fw-bold">Mutasi Stok: <?= esc($p['nama_produk']) ?></h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Tipe Mutasi</label>
                                                    <select name="tipe" class="form-select form-select-sm">
                                                        <option value="masuk">Stok Masuk (+)</option>
                                                        <option value="keluar">Stok Keluar (-)</option>
                                                        <option value="penyesuaian">Set Stok Fisik (Opname)</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Jumlah Kuantitas</label>
                                                    <input type="number" name="qty" class="form-control form-control-sm" min="1" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold">Catatan / Keterangan</label>
                                                    <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Hasil panen baru / opname...">
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-sm btn-agro">Simpan Stok</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center py-5 text-muted">Belum ada data produk perkebunan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
