<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SURAT JALAN #<?= esc($order['order_number']) ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .kop {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .kop h2 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
        }
        .kop h3 {
            margin: 3px 0;
            font-size: 12px;
            font-weight: normal;
        }
        .kop p {
            margin: 0;
            font-size: 9px;
            color: #555;
        }
        .doc-title {
            text-align: center;
            margin: 15px 0;
        }
        .doc-title h4 {
            margin: 0;
            text-decoration: underline;
            font-size: 14px;
            letter-spacing: 1px;
        }
        .doc-title span {
            font-size: 11px;
            color: #444;
        }
        table.meta {
            width: 100%;
            margin-bottom: 15px;
        }
        table.meta td {
            vertical-align: top;
            padding: 4px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table.items th, table.items td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
        }
        table.items th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .signs-table {
            width: 100%;
            margin-top: 30px;
            text-align: center;
        }
        .signs-table td {
            width: 33.33%;
            vertical-align: top;
        }
        .sign-space {
            height: 60px;
        }
    </style>
</head>
<body>

<div class="kop">
    <h2>POLITEKNIK NEGERI LAMPUNG</h2>
    <h3>TEACHING FACTORY (TEFA) UNIT PERKEBUNAN</h3>
    <p>Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung 35144 | www.polinela.ac.id</p>
</div>

<div class="doc-title">
    <h4>SURAT JALAN PENGIRIMAN BARANG</h4>
    <span>No. Ref: SJ/POLINELA-AGRO/<?= date('Ym', strtotime($order['created_at'])) ?>/<?= esc($order['order_number']) ?></span>
</div>

<table class="meta">
    <tr>
        <td style="width: 50%;">
            <strong>Pengirim:</strong><br>
            Polinela Agro Digital (Tefa Perkebunan)<br>
            Jl. Soekarno Hatta No. 10, Rajabasa<br>
            Bandar Lampung, Lampung 35144<br>
            Telp: (0721) 703995
        </td>
        <td style="width: 50%;">
            <strong>Tujuan Pengiriman (Penerima):</strong><br>
            <strong><?= esc($shipping['nama_penerima'] ?? $customer['nama'] ?? 'Pelanggan') ?></strong><br>
            Telp/WA: <?= esc($shipping['no_hp'] ?? $customer['no_hp'] ?? '-') ?><br>
            Alamat: <?= esc($shipping['alamat'] ?? '-') ?><br>
            Kota/Kab: <?= esc($shipping['kota'] ?? 'Bandar Lampung') ?> (Kode Pos: <?= esc($shipping['kode_pos'] ?? '-') ?>)
        </td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 10px;">
            <strong>Kurir / Ekspedisi:</strong> <?= strtoupper(esc($order['kurir'] ?? 'Kurir Internal Kampus')) ?> 
            <?php if (!empty($shipping['no_resi'])): ?>
                | <strong>No. Resi:</strong> <?= esc($shipping['no_resi']) ?>
            <?php endif; ?>
            | <strong>Tanggal:</strong> <?= date('d F Y', strtotime($order['created_at'])) ?>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th class="text-center" style="width: 35px;">No</th>
            <th>Nama Barang / Produk Perkebunan</th>
            <th>Unit Tefa Asal</th>
            <th class="text-center" style="width: 70px;">Jumlah</th>
            <th>Kondisi & Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($details as $d): ?>
        <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><strong><?= esc($d['nama_produk']) ?></strong></td>
            <td><?= esc($d['nama_unit'] ?? 'Unit Tefa') ?></td>
            <td class="text-center"><strong><?= $d['qty'] ?></strong></td>
            <td>Baik, Tersegel Standar Lab Tefa</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table class="signs-table">
    <tr>
        <td>
            Pengirim / Petugas Gudang Tefa,<br>
            <div class="sign-space"></div>
            ( ........................................ )
        </td>
        <td>
            Kurir / Ekspedisi Pengantar,<br>
            <div class="sign-space"></div>
            ( ........................................ )
        </td>
        <td>
            Penerima Barang,<br>
            <div class="sign-space"></div>
            ( <strong><?= esc($shipping['nama_penerima'] ?? $customer['nama'] ?? 'Penerima') ?></strong> )
        </td>
    </tr>
</table>

<div style="margin-top: 40px; font-size: 9px; color: #666; border-top: 1px dotted #ccc; padding-top: 6px;">
    * Surat jalan ini merupakan bukti serah terima resmi barang perkebunan. Harap diperiksa saat barang tiba.
</div>

</body>
</html>
