<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e4d2b;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            color: #1e4d2b;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h3 {
            margin: 4px 0;
            font-size: 13px;
            color: #222;
        }
        .header p {
            margin: 0;
            font-size: 9px;
            color: #666;
        }
        .meta-info {
            margin-bottom: 15px;
            font-size: 10px;
        }
        .meta-info table {
            width: 100%;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f4f8f4;
            color: #1e4d2b;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        table.data-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer-sign {
            margin-top: 30px;
            float: right;
            width: 200px;
            text-align: center;
        }
        .footer-sign .line {
            margin-top: 50px;
            border-bottom: 1px solid #333;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>POLITEKNIK NEGERI LAMPUNG</h2>
    <h3>UNIT USAHA & TEACHING FACTORY (TEFA) PERKEBUNAN</h3>
    <p>Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung, Lampung 35144 | Sistem Polinela Agro Digital</p>
</div>

<div class="meta-info">
    <table>
        <tr>
            <td style="width: 60%;"><strong>Laporan:</strong> <?= esc($title) ?></td>
            <td style="width: 40%; text-align: right;"><strong>Tanggal Cetak:</strong> <?= date('d F Y H:i') ?></td>
        </tr>
        <tr>
            <td><strong>Periode Data:</strong> <?= date('d M Y', strtotime($start_date)) ?> s/d <?= date('d M Y', strtotime($end_date)) ?></td>
            <td style="text-align: right;"><strong>Dicetak Oleh:</strong> <?= esc(session()->get('user_nama') ?? 'Administrator') ?></td>
        </tr>
    </table>
</div>

<table class="data-table">
    <?php if ($type === 'penjualan'): ?>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th>No Pesanan</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Unit Usaha</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Ongkir</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Grand Total</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($report_data)): ?>
                <?php $no = 1; $grand = 0; foreach ($report_data as $r): $grand += $r['grand_total']; ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td>#<?= esc($r['order_number']) ?></td>
                    <td><?= date('d/m/y H:i', strtotime($r['created_at'])) ?></td>
                    <td><?= esc($r['customer_nama']) ?></td>
                    <td><?= esc($r['nama_unit'] ?? 'Polinela') ?></td>
                    <td class="text-right">Rp <?= number_format($r['total_produk'], 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($r['ongkir'], 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($r['diskon'], 0, ',', '.') ?></td>
                    <td class="text-right"><strong>Rp <?= number_format($r['grand_total'], 0, ',', '.') ?></strong></td>
                    <td class="text-center"><?= strtoupper($r['status']) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="8" class="text-right">Total Keseluruhan Omset:</td>
                    <td class="text-right">Rp <?= number_format($grand, 0, ',', '.') ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="10" class="text-center">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>

    <?php elseif ($type === 'produk_terlaris'): ?>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">Rank</th>
                <th>Nama Produk Perkebunan</th>
                <th>Unit Usaha</th>
                <th>Kategori</th>
                <th class="text-right">Harga</th>
                <th class="text-center">Total Terjual</th>
                <th class="text-right">Akumulasi Omset</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($report_data)): ?>
                <?php $rank = 1; foreach ($report_data as $r): ?>
                <tr>
                    <td class="text-center">#<?= $rank++ ?></td>
                    <td><strong><?= esc($r['nama_produk']) ?></strong></td>
                    <td><?= esc($r['nama_unit']) ?></td>
                    <td><?= esc($r['nama_kategori']) ?></td>
                    <td class="text-right">Rp <?= number_format($r['harga'], 0, ',', '.') ?></td>
                    <td class="text-center"><strong><?= (int)$r['terjual'] ?> <?= esc($r['satuan']) ?></strong></td>
                    <td class="text-right"><strong>Rp <?= number_format($r['pendapatan'], 0, ',', '.') ?></strong></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">Tidak ada data produk.</td></tr>
            <?php endif; ?>
        </tbody>

    <?php elseif ($type === 'stok'): ?>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th>Nama Produk</th>
                <th>Unit Usaha</th>
                <th>Kategori</th>
                <th class="text-right">Harga</th>
                <th class="text-center">Sisa Stok</th>
                <th class="text-center">Batas Minimum</th>
                <th class="text-center">Kondisi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($report_data)): ?>
                <?php $no = 1; foreach ($report_data as $r): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><strong><?= esc($r['nama_produk']) ?></strong></td>
                    <td><?= esc($r['nama_unit']) ?></td>
                    <td><?= esc($r['nama_kategori']) ?></td>
                    <td class="text-right">Rp <?= number_format($r['harga'], 0, ',', '.') ?></td>
                    <td class="text-center"><strong><?= $r['stok'] ?> <?= esc($r['satuan']) ?></strong></td>
                    <td class="text-center"><?= $r['stok_min'] ?> <?= esc($r['satuan']) ?></td>
                    <td class="text-center"><?= $r['stok'] <= $r['stok_min'] ? 'PERLU RESTOK' : 'AMAN' ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center">Tidak ada data stok.</td></tr>
            <?php endif; ?>
        </tbody>

    <?php else: ?>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th>Informasi</th>
                <th class="text-right">Nilai / Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="3" class="text-center">Data laporan tersedia.</td></tr>
        </tbody>
    <?php endif; ?>
</table>

<div class="footer-sign">
    <p>Bandar Lampung, <?= date('d F Y') ?><br>Penanggung Jawab Polinela Agro,</p>
    <div class="line"></div>
    <p><strong><?= esc(session()->get('user_nama') ?? 'Direktur / Pimpinan') ?></strong></p>
</div>

</body>
</html>
