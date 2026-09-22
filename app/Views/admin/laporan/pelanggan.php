<div class="table-responsive">
    <table class="table table-custom align-middle mb-0">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pelanggan</th>
                <th>Email</th>
                <th>No WhatsApp</th>
                <th>Terdaftar Sejak</th>
                <th>Total Pesanan Selesai</th>
                <th>Total Belanja</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($report_data)): ?>
                <?php $no = 1; foreach ($report_data as $row): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td class="fw-semibold text-dark"><?= esc($row['nama']) ?></td>
                    <td><?= esc($row['email']) ?></td>
                    <td><?= esc($row['no_hp'] ?: '-') ?></td>
                    <td class="small text-muted"><?= indo_date($row['created_at']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $row['total_pesanan'] ?> Pesanan</span></td>
                    <td><strong class="text-success"><?= format_rupiah($row['total_belanja']) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada pelanggan terdaftar.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
