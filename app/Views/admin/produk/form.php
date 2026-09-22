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
        <h5 class="fw-extrabold mb-0"><?= isset($product) ? 'Edit Produk' : 'Tambah Produk Baru' ?></h5>
        <a href="<?= base_url('admin/produk') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </header>

    <div class="p-4 p-lg-5">
        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <form action="<?= isset($product) ? base_url('admin/produk/edit/' . $product['id']) : base_url('admin/produk/create') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="row g-4">
                    <!-- Kolom Kiri: Info Utama -->
                    <div class="col-lg-8">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Nama Komoditas / Produk Perkebunan</label>
                            <input type="text" name="nama_produk" class="form-control form-control-lg fs-6" value="<?= old('nama_produk', $product['nama_produk'] ?? '') ?>" placeholder="Contoh: Kopi Robusta Polinela Roasted Beans 250g" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Kategori Komoditas</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (old('category_id', $product['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                        <?= esc($cat['nama_kategori']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Unit Usaha Kampus Pengelola</label>
                                <?php if ($role === 'superadmin'): ?>
                                    <select name="unit_id" class="form-select" required>
                                        <option value="">-- Pilih Unit Usaha --</option>
                                        <?php foreach ($units as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= (old('unit_id', $product['unit_id'] ?? '') == $u['id']) ? 'selected' : '' ?>>
                                            <?= esc($u['nama_unit']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="text" class="form-control bg-light" value="<?= esc(array_column($units, 'nama_unit', 'id')[$my_unit_id] ?? 'Unit Anda') ?>" readonly>
                                    <input type="hidden" name="unit_id" value="<?= $my_unit_id ?>">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Deskripsi Lengkap Produk & Informasi Petik Panen</label>
                            <textarea name="deskripsi" class="form-control" rows="6" placeholder="Jelaskan aroma, cita rasa, proses pengolahan, dan saran penyajian..."><?= old('deskripsi', $product['deskripsi'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Harga, Stok, & Foto -->
                    <div class="col-lg-4">
                        <div class="p-3 bg-light rounded-4 border mb-4">
                            <h6 class="fw-bold mb-3 small text-muted text-uppercase">Harga & Varian Satuan</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Harga Satuan (Rp)</label>
                                <input type="number" name="harga" class="form-control" value="<?= old('harga', $product['harga'] ?? '') ?>" placeholder="45000" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Satuan Kemasan</label>
                                    <input type="text" name="satuan" class="form-control" value="<?= old('satuan', $product['satuan'] ?? 'pack') ?>" placeholder="pack / pouch / botol" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Berat (Gram)</label>
                                    <input type="number" name="berat_gram" class="form-control" value="<?= old('berat_gram', $product['berat_gram'] ?? '250') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-4 border mb-4">
                            <h6 class="fw-bold mb-3 small text-muted text-uppercase">Pengaturan Stok</h6>
                            <?php if (!isset($product)): ?>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Stok Awal</label>
                                <input type="number" name="stok" class="form-control" value="<?= old('stok', 10) ?>" min="0" required>
                            </div>
                            <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Stok Saat Ini (Hanya via Penyesuaian)</label>
                                <input type="text" class="form-control bg-white" value="<?= $product['stok'] ?> <?= esc($product['satuan']) ?>" readonly>
                            </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Batas Minimum Peringatan (Alert)</label>
                                <input type="number" name="stok_min" class="form-control" value="<?= old('stok_min', $product['stok_min'] ?? 5) ?>" min="1" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-semibold">Status Visibilitas</label>
                                <select name="status" class="form-select">
                                    <option value="aktif" <?= (old('status', $product['status'] ?? 'aktif') === 'aktif') ? 'selected' : '' ?>>Aktif (Tampil di Toko)</option>
                                    <option value="nonaktif" <?= (old('status', $product['status'] ?? '') === 'nonaktif') ? 'selected' : '' ?>>Nonaktif (Draft)</option>
                                </select>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="featured" value="1" id="featuredCheck" <?= (old('featured', $product['featured'] ?? 0) == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label small fw-semibold" for="featuredCheck">
                                    Jadikan Produk Unggulan Beranda
                                </label>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-4 border">
                            <h6 class="fw-bold mb-2 small text-muted text-uppercase">Foto Utama Produk</h6>
                            <input type="file" name="gambar_utama" class="form-control form-control-sm mb-2" accept="image/*">
                            <?php if (!empty($product['gambar_utama'])): ?>
                            <div class="small text-muted mb-2">Foto saat ini:</div>
                            <img src="<?= base_url('assets/img/products/' . $product['gambar_utama']) ?>" class="img-thumbnail" style="max-height: 120px;" onerror="this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=200&auto=format&fit=crop&q=80';">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <a href="<?= base_url('admin/produk') ?>" class="btn btn-light px-4 me-2">Batal</a>
                    <button type="submit" class="btn btn-agro px-5">Simpan Data Produk</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
