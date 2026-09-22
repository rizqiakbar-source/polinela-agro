<div class="table-responsive">
    <table class="table table-custom align-middle mb-0">
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Total Transaksi Selesai</th>
                <th>Total Omset Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($report_data)): ?>
                <?php foreach ($report_data as $row): ?>
                <tr>
                    <td class="fw-semibold text-dark"><?= esc($row['bulan']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $row['total_transaksi'] ?> Transaksi</span></td>
                    <td><strong class="text-success"><?= format_rupiah($row['total_omset']) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada data keuangan untuk tahun ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
