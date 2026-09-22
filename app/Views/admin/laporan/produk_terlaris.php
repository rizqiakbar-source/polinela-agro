<div class="table-responsive">
    <table class="table table-custom align-middle mb-0">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Unit Usaha</th>
                <th>Kategori</th>
                <th>Harga Satuan</th>
                <th>Jumlah Terjual</th>
                <th>Total Omset</th>
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
                    <td><strong class="text-dark"><?= $row['terjual'] ?></strong></td>
                    <td><strong class="text-success"><?= format_rupiah($row['pendapatan']) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data penjualan komoditas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
