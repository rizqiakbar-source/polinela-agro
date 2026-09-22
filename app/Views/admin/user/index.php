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
            <h5 class="fw-extrabold mb-0">Kelola Pengguna & Hak Akses</h5>
            <small class="text-muted">Manajemen multi-role: Super Admin, Admin Unit Perkebunan, Pimpinan, dan Konsumen</small>
        </div>
        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddUser">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna
        </button>
    </header>

    <div class="p-4 p-lg-5">
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-x-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <ul class="mb-0 ps-3">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filter Role Tabs -->
        <div class="d-flex gap-2 overflow-auto pb-2 mb-4">
            <a href="<?= base_url('admin/user') ?>" class="btn btn-sm <?= empty($current_role) ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Semua Role</a>
            <a href="<?= base_url('admin/user?role=superadmin') ?>" class="btn btn-sm <?= ($current_role === 'superadmin') ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Super Admin</a>
            <a href="<?= base_url('admin/user?role=admin_unit') ?>" class="btn btn-sm <?= ($current_role === 'admin_unit') ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Admin Unit Usaha</a>
            <a href="<?= base_url('admin/user?role=pimpinan') ?>" class="btn btn-sm <?= ($current_role === 'pimpinan') ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Pimpinan / Direktur</a>
            <a href="<?= base_url('admin/user?role=konsumen') ?>" class="btn btn-sm <?= ($current_role === 'konsumen') ? 'btn-success' : 'btn-outline-secondary' ?> text-nowrap rounded-pill px-3">Konsumen / Civitas</a>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h6 class="fw-bold mb-0">Daftar Akun Pengguna Terdaftar</h6>
                <span class="text-muted small">Total: <?= count($users) ?> Pengguna</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Lengkap</th>
                            <th>Email & Kontak</th>
                            <th>Role / Hak Akses</th>
                            <th>Unit Usaha Tefa</th>
                            <th>Status Akun</th>
                            <th>Terdaftar</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php $no = 1; foreach ($users as $u): ?>
                            <tr>
                                <td class="text-muted small"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center fw-bold text-success border" style="width: 38px; height: 38px; flex-shrink: 0;">
                                            <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong class="text-dark small d-block"><?= esc($u['nama']) ?></strong>
                                            <?php if ($u['id'] == session()->get('user_id')): ?>
                                                <span class="badge bg-success-subtle text-success" style="font-size: 0.68rem;">Akun Anda</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-dark font-monospace"><?= esc($u['email']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= esc($u['no_hp'] ?: '-') ?></div>
                                </td>
                                <td>
                                    <?php 
                                    $roleBadge = match($u['role']) {
                                        'superadmin' => 'bg-danger text-white',
                                        'admin_unit' => 'bg-primary text-white',
                                        'pimpinan'   => 'bg-purple text-white',
                                        default      => 'bg-secondary text-white'
                                    };
                                    ?>
                                    <span class="badge <?= $roleBadge ?> rounded-pill text-uppercase px-2" style="font-size: 0.7rem;">
                                        <?= str_replace('_', ' ', esc($u['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($u['role'] === 'admin_unit'): ?>
                                        <span class="badge bg-light text-success border border-success-subtle">
                                            <i class="bi bi-building me-1"></i><?= esc($u['nama_unit'] ?? 'Belum Ditugaskan') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['is_active']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-x-circle-fill me-1"></i> Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?= date('d M Y', strtotime($u['created_at'])) ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Toggle Status -->
                                        <?php if ($u['id'] != session()->get('user_id')): ?>
                                            <a href="<?= base_url('admin/user/toggle/' . $u['id']) ?>" 
                                               class="btn btn-outline-<?= $u['is_active'] ? 'warning' : 'success' ?>"
                                               title="<?= $u['is_active'] ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>"
                                               onclick="return confirm('Apakah Anda yakin ingin mengubah status akun ini?');">
                                                <i class="bi bi-power"></i>
                                            </a>
                                            <!-- Reset Password -->
                                            <a href="<?= base_url('admin/user/reset/' . $u['id']) ?>" 
                                               class="btn btn-outline-secondary"
                                               title="Reset Password default polinela123"
                                               onclick="return confirm('Reset password akun ini menjadi polinela123?');">
                                                <i class="bi bi-key"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block text-secondary mb-2"></i>
                                    Tidak ada data pengguna ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalAddUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('admin/user/create') ?>" method="POST" class="modal-content border-0 shadow">
            <?= csrf_field() ?>
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-1 text-success"></i> Tambah Pengguna Baru</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control form-control-sm" required placeholder="Nama lengkap staf / pengguna">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control form-control-sm" required placeholder="email@polinela.ac.id">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Password Sementara <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control form-control-sm" required minlength="6" placeholder="Minimal 6 karakter">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Role Hak Akses <span class="text-danger">*</span></label>
                        <select name="role" id="roleSelect" class="form-select form-select-sm" required onchange="toggleUnitSelect()">
                            <option value="">Pilih Role...</option>
                            <option value="superadmin">Super Admin</option>
                            <option value="admin_unit">Admin Unit Perkebunan</option>
                            <option value="pimpinan">Pimpinan / Direktur</option>
                            <option value="konsumen">Konsumen</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="unitSelectWrapper" style="display: none;">
                        <label class="form-label small fw-semibold">Unit Usaha Perkebunan <span class="text-danger">*</span></label>
                        <select name="unit_id" id="unitIdInput" class="form-select form-select-sm">
                            <option value="">Pilih Unit Tefa...</option>
                            <?php foreach ($units as $un): ?>
                                <option value="<?= $un['id'] ?>"><?= esc($un['nama_unit']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">No Handphone / WhatsApp</label>
                    <input type="text" name="no_hp" class="form-control form-control-sm" placeholder="08xxxxxxxxxx">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Alamat Domisili</label>
                    <textarea name="alamat" class="form-control form-control-sm" rows="2" placeholder="Alamat tinggal / gedung kampus"></textarea>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-sm btn-success px-3">
                    <i class="bi bi-save me-1"></i> Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleUnitSelect() {
    const role = document.getElementById('roleSelect').value;
    const unitWrapper = document.getElementById('unitSelectWrapper');
    const unitInput = document.getElementById('unitIdInput');
    if (role === 'admin_unit') {
        unitWrapper.style.display = 'block';
        unitInput.required = true;
    } else {
        unitWrapper.style.display = 'none';
        unitInput.required = false;
        unitInput.value = '';
    }
}
</script>
</body>
</html>
