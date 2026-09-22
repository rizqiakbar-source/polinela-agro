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
            <h5 class="fw-extrabold mb-0">Kelola Tarif Ongkir Pengiriman</h5>
            <small class="text-muted">Konfigurasi zona pengiriman internal kampus Polinela dan wilayah Bandar Lampung</small>
        </div>
    </header>

    <div class="p-4 p-lg-5">
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/ongkir/save') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="admin-card">
                <div class="admin-card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0">Zona & Tarif Kurir</h6>
                        <small class="text-muted">Atur nama wilayah, biaya ongkos kirim, dan estimasi waktu sampai</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="addRow()">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Wilayah
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0" id="ratesTable">
                        <thead>
                            <tr>
                                <th>Zona / Wilayah Pengiriman</th>
                                <th style="width: 200px;">Biaya Ongkir (Rp)</th>
                                <th style="width: 220px;">Estimasi Tiba</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="ratesBody">
                            <?php if (!empty($rates) && is_array($rates)): ?>
                                <?php foreach ($rates as $r): ?>
                                <tr>
                                    <td>
                                        <input type="text" name="wilayah[]" class="form-control form-control-sm" required value="<?= esc($r['wilayah']) ?>" placeholder="Nama Zona / Area">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="tarif[]" class="form-control form-control-sm" required min="0" value="<?= (int) $r['tarif'] ?>">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="estimasi[]" class="form-control form-control-sm" value="<?= esc($r['estimasi']) ?>" placeholder="Misal: 1 - 2 Jam">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>
                                        <input type="text" name="wilayah[]" class="form-control form-control-sm" required value="Internal Kampus Polinela" placeholder="Nama Zona / Area">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="tarif[]" class="form-control form-control-sm" required min="0" value="0">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="estimasi[]" class="form-control form-control-sm" value="Langsung / COD Kampus" placeholder="Misal: 1 - 2 Jam">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-top bg-light d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        <i class="bi bi-info-circle me-1"></i> Perubahan tarif akan langsung berlaku saat pembeli memilih kurir kampus di form checkout.
                    </span>
                    <button type="submit" class="btn btn-sm btn-success px-4 rounded-pill shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Semua Tarif
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function addRow() {
    const tbody = document.getElementById('ratesBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <input type="text" name="wilayah[]" class="form-control form-control-sm" required placeholder="Nama Wilayah Baru">
        </td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text">Rp</span>
                <input type="number" name="tarif[]" class="form-control form-control-sm" required min="0" value="10000">
            </div>
        </td>
        <td>
            <input type="text" name="estimasi[]" class="form-control form-control-sm" value="1 - 2 Hari" placeholder="Estimasi Waktu">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

function removeRow(btn) {
    const tbody = document.getElementById('ratesBody');
    if (tbody.querySelectorAll('tr').length > 1) {
        btn.closest('tr').remove();
    } else {
        alert('Minimal harus ada 1 zona tarif pengiriman.');
    }
}
</script>
</body>
</html>
