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
        <h5 class="fw-extrabold mb-0">Kelola Unit Usaha Perkebunan Polinela</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#assignAdminModal">
                <i class="bi bi-person-check me-1"></i> Tugaskan Admin Unit
            </button>
            <button class="btn btn-agro btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Unit Usaha
            </button>
        </div>
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
                <h6 class="fw-bold mb-0">Daftar Teaching Factory & Unit Produksi Kampus</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Unit Usaha</th>
                            <th>Penanggung Jawab</th>
                            <th>Kontak WhatsApp</th>
                            <th class="text-center">Total Produk</th>
                            <th class="text-center">Total Terjual</th>
                            <th class="text-end">Omset</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($units as $u): ?>
                        <tr>
                            <td>
                                <strong class="text-dark small d-block"><?= esc($u['nama_unit']) ?></strong>
                                <span class="text-muted small" style="font-size: 0.72rem;"><?= esc($u['lokasi'] ?: 'Kebun Polinela') ?></span>
                            </td>
                            <td class="small"><?= esc($u['pj_nama'] ?: '-') ?></td>
                            <td class="small font-monospace"><?= esc($u['kontak'] ?: '-') ?></td>
                            <td class="text-center small fw-bold"><?= $u['total_produk'] ?></td>
                            <td class="text-center small fw-bold"><?= $u['total_terjual'] ?></td>
                            <td class="text-end small fw-bold text-success"><?= format_rupiah($u['total_omset']) ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUnitModal<?= $u['id'] ?>"><i class="bi bi-pencil-square"></i></button>
                                    <a href="<?= base_url('admin/unit/delete/' . $u['id']) ?>" class="btn btn-outline-danger btn-delete-confirm"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Unit -->
                        <div class="modal fade" id="editUnitModal<?= $u['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="<?= base_url('admin/unit/update/' . $u['id']) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <div class="modal-header border-0">
                                            <h6 class="modal-title fw-bold">Edit Data Unit Usaha</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Nama Unit</label>
                                                <input type="text" name="nama_unit" class="form-control" value="<?= esc($u['nama_unit']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Penanggung Jawab (PJ)</label>
                                                <input type="text" name="pj_nama" class="form-control" value="<?= esc($u['pj_nama']) ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Nomor Kontak WhatsApp</label>
                                                <input type="text" name="kontak" class="form-control" value="<?= esc($u['kontak']) ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Lokasi Laboratorium / Kebun</label>
                                                <input type="text" name="lokasi" class="form-control" value="<?= esc($u['lokasi']) ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Deskripsi</label>
                                                <textarea name="deskripsi" class="form-control" rows="2"><?= esc($u['deskripsi']) ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-agro btn-sm">Simpan</button>
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

<!-- Modal Tambah Unit -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= base_url('admin/unit/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-bold">Tambah Unit Usaha Perkebunan Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Unit Usaha</label>
                        <input type="text" name="nama_unit" class="form-control" placeholder="Contoh: Unit Kopi Polinela" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Penanggung Jawab (PJ)</label>
                        <input type="text" name="pj_nama" class="form-control" placeholder="Nama Dosen / Teknisi">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kontak WhatsApp</label>
                        <input type="text" name="kontak" class="form-control" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Lokasi Kebun / Tefa</label>
                        <input type="text" name="lokasi" class="form-control" placeholder="Gedung Tefa / Kebun Percobaan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Komoditas fokus yang diolah..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-agro btn-sm">Tambah Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Assign Admin Unit -->
<div class="modal fade" id="assignAdminModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= base_url('admin/unit/assign') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header border-0">
                    <h6 class="modal-title fw-bold">Tugaskan Admin ke Unit Usaha</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Pengguna Admin</label>
                        <select name="user_id" class="form-select" required>
                            <?php foreach ($adminUsers as $usr): ?>
                            <option value="<?= $usr['id'] ?>"><?= esc($usr['nama']) ?> (<?= esc($usr['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Unit Usaha Tujuan</label>
                        <select name="unit_id" class="form-select" required>
                            <?php foreach ($units as $un): ?>
                            <option value="<?= $un['id'] ?>"><?= esc($un['nama_unit']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-agro btn-sm">Tugaskan Sekarang</button>
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
