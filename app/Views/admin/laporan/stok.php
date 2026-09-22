<div class="table-responsive">
    <table class="table table-custom align-middle mb-0">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Unit Usaha</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok Saat Ini</th>
                <th>Stok Min</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($report_data)): ?>
                <?php $no = 1; foreach ($report_data as $row): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td class="fw-semibold text-dark"><?= esc($row['nama_produk']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= esc($row['nama_unit']) ?></span></td>
                    <td><?= esc($row['nama_kategori']) ?></td>
                    <td class="small"><?= format_rupiah($row['harga']) ?> / <?= esc($row['satuan']) ?></td>
                    <td>
                        <strong class="<?= $row['stok'] <= $row['stok_min'] ? 'text-danger' : 'text-success' ?>">
                            <?= $row['stok'] ?> <?= esc($row['satuan']) ?>
                        </strong>
                    </td>
                    <td class="text-muted small"><?= $row['stok_min'] ?></td>
                    <td>
                        <?php if ($row['stok'] <= 0): ?>
                            <span class="badge bg-danger rounded-pill">Habis</span>
                        <?php elseif ($row['stok'] <= $row['stok_min']): ?>
                            <span class="badge bg-warning rounded-pill text-dark">Stok Kritis</span>
                        <?php else: ?>
                            <span class="badge bg-success rounded-pill">Aman</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada inventori produk.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
