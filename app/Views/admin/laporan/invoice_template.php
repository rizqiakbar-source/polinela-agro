<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>INVOICE #<?= esc($order['order_number']) ?></title>
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
            margin-bottom: 25px;
            border-bottom: 2px solid #1e4d2b;
            padding-bottom: 15px;
        }
        .header table {
            width: 100%;
        }
        .logo-text {
            color: #1e4d2b;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .logo-sub {
            color: #666;
            font-size: 10px;
        }
        .invoice-title {
            text-align: right;
            font-size: 20px;
            font-weight: 800;
            color: #222;
        }
        .meta-boxes {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-boxes td {
            vertical-align: top;
            width: 50%;
        }
        .box {
            padding: 10px;
            background: #fdfdfd;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        .box-title {
            font-weight: bold;
            font-size: 11px;
            color: #1e4d2b;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.items th, table.items td {
            border: 1px solid #ddd;
            padding: 7px 10px;
            text-align: left;
        }
        table.items th {
            background-color: #1e4d2b;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-table {
            width: 45%;
            float: right;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .totals-table td {
            padding: 5px 8px;
        }
        .totals-table tr.grand td {
            border-top: 2px solid #1e4d2b;
            font-weight: bold;
            font-size: 13px;
            color: #1e4d2b;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background: #d1e7dd; color: #0f5132; }
        .badge-warning { background: #fff3cd; color: #664d03; }
        .footer {
            margin-top: 150px;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #777;
        }
    </style>
</head>
<body>

<div class="header">
    <table>
        <tr>
            <td>
                <div class="logo-text">POLINELA AGRO DIGITAL</div>
                <div class="logo-sub" style="font-weight: bold; color: #1e4d2b;"><?= esc($unit['nama_unit'] ?? 'Teaching Factory Perkebunan') ?></div>
                <div class="logo-sub">Politeknik Negeri Lampung • Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung</div>
            </td>
            <td class="invoice-title">
                INVOICE
                <div style="font-size: 12px; font-weight: normal; color: #555;">#<?= esc($order['order_number']) ?></div>
                <div style="font-size: 10px; font-weight: normal; color: #777;"><?= date('d F Y H:i', strtotime($order['created_at'])) ?></div>
                <div style="margin-top: 5px;">
                    <span class="badge <?= ($order['status'] === 'selesai' || ($payment['status'] ?? '') === 'lunas') ? 'badge-success' : 'badge-warning' ?>">
                        <?= strtoupper(esc($order['status'])) ?>
                    </span>
                </div>
            </td>
        </tr>
    </table>
</div>

<table class="meta-boxes">
    <tr>
        <td style="padding-right: 10px;">
            <div class="box">
                <div class="box-title">Diterbitkan Kepada:</div>
                <strong><?= esc($shipping['penerima_nama'] ?? $shipping['nama_penerima'] ?? $customer['nama'] ?? 'Pelanggan') ?></strong><br>
                Telp: <?= esc($shipping['penerima_telepon'] ?? $shipping['no_hp'] ?? $customer['no_hp'] ?? '-') ?><br>
                Email: <?= esc($customer['email'] ?? '-') ?><br>
                Alamat Kirim: <?= esc($shipping['alamat_lengkap'] ?? $shipping['alamat'] ?? '-') ?>, <?= esc($shipping['kota'] ?? 'Bandar Lampung') ?>
            </div>
        </td>
        <td style="padding-left: 10px;">
            <div class="box">
                <div class="box-title">Rincian Pengiriman & Bayar:</div>
                Kurir: <strong><?= strtoupper(esc($order['kurir'] ?? 'Kurir Kampus')) ?></strong><br>
                Resi: <?= !empty($shipping['no_resi']) ? esc($shipping['no_resi']) : 'Belum Ada (Menunggu Pickup)' ?><br>
                Metode Bayar: <strong><?= strtoupper(esc($payment['metode'] ?? 'Transfer Bank')) ?></strong><br>
                Status Bayar: <strong><?= strtoupper(esc($payment['status'] ?? 'pending')) ?></strong>
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th class="text-center" style="width: 30px;">No</th>
            <th>Nama Produk Komoditas</th>
            <th>Unit Usaha</th>
            <th class="text-right">Harga Satuan</th>
            <th class="text-center" style="width: 60px;">Jumlah</th>
            <th class="text-right" style="width: 100px;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($details as $d): ?>
        <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><strong><?= esc($d['nama_produk']) ?></strong></td>
            <td><?= esc($d['nama_unit'] ?? 'Unit Tefa') ?></td>
            <td class="text-right">Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
            <td class="text-center"><?= $d['qty'] ?></td>
            <td class="text-right">Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table class="totals-table">
    <tr>
        <td>Subtotal Belanja:</td>
        <td class="text-right">Rp <?= number_format($order['total_produk'], 0, ',', '.') ?></td>
    </tr>
    <tr>
        <td>Biaya Pengiriman:</td>
        <td class="text-right">Rp <?= number_format($order['ongkir'], 0, ',', '.') ?></td>
    </tr>
    <?php if ($order['diskon'] > 0): ?>
    <tr>
        <td style="color: #b02a37;">Voucher Diskon:</td>
        <td class="text-right" style="color: #b02a37;">-Rp <?= number_format($order['diskon'], 0, ',', '.') ?></td>
    </tr>
    <?php endif; ?>
    <tr class="grand">
        <td>Total Tagihan:</td>
        <td class="text-right">Rp <?= number_format($order['grand_total'], 0, ',', '.') ?></td>
    </tr>
</table>

<div style="clear: both;"></div>

<div class="footer">
    Terima kasih telah berbelanja produk Teaching Factory Politeknik Negeri Lampung!<br>
    Invoice ini sah dan diproses secara otomatis oleh sistem Polinela Agro Digital.
</div>

</body>
</html>
