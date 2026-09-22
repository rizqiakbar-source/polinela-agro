<div class="table-responsive">
    <table class="table table-custom align-middle mb-0">
        <thead>
            <tr>
                <th>No Pesanan</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Unit Usaha</th>
                <th>Subtotal</th>
                <th>Ongkir</th>
                <th>Diskon</th>
                <th>Grand Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($report_data)): ?>
                <?php foreach ($report_data as $row): ?>
                <tr>
                    <td class="font-monospace fw-bold text-success">#<?= esc($row['order_number']) ?></td>
                    <td class="small text-muted"><?= indo_date($row['created_at'], true) ?></td>
                    <td>
                        <div class="fw-semibold text-dark"><?= esc($row['customer_nama']) ?></div>
                        <small class="text-muted"><?= esc($row['customer_email']) ?></small>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?= esc($row['nama_unit'] ?? 'Polinela') ?></span></td>
                    <td class="small"><?= format_rupiah($row['total_produk']) ?></td>
                    <td class="small"><?= format_rupiah($row['ongkir']) ?></td>
                    <td class="small text-danger">-<?= format_rupiah($row['diskon']) ?></td>
                    <td><strong class="text-success small"><?= format_rupiah($row['grand_total']) ?></strong></td>
                    <td><?= status_badge($row['status']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data transaksi pada rentang waktu ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
