<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Invoice - Polinela Agro Digital') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; color: #1e293b; }
        .invoice-card { max-width: 850px; margin: 30px auto; background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); padding: 40px; border: 1px solid #e2e8f0; }
        .campus-header { border-bottom: 2.5px solid #15803d; padding-bottom: 20px; margin-bottom: 25px; }
        @media print {
            body { background: white; }
            .invoice-card { box-shadow: none; border: none; padding: 0; margin: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Tombol Aksi Cetak -->
    <div class="d-flex justify-content-between align-items-center max-w-850 mx-auto mt-4 mb-2 no-print" style="max-width: 850px;">
        <a href="<?= base_url('pesanan/detail/' . $order['order_number']) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pesanan
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-success btn-sm">
                <i class="bi bi-printer me-1"></i> Cetak Dokumen
            </button>
            <a href="<?= base_url('invoice/pdf/' . $order['order_number']) ?>" class="btn btn-success btn-sm" style="background-color: #15803d;">
                <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF Resmi
            </a>
        </div>
    </div>

    <!-- Faktur Dokumen -->
    <div class="invoice-card">
        <!-- Header Kop Kampus -->
        <div class="campus-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 55px; height: 55px; background: #15803d; color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
                    <i class="bi bi-tree"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-success" style="letter-spacing: -0.5px;">POLINELA AGRO DIGITAL</h5>
                    <div class="small fw-semibold text-muted">Teaching Factory & Unit Usaha Perkebunan</div>
                    <div class="small text-secondary" style="font-size: 0.78rem;">Politeknik Negeri Lampung • Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung</div>
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 rounded-pill font-monospace">
                    INVOICE RESMI
                </span>
                <div class="small text-muted mt-1">#<?= esc($order['order_number']) ?></div>
            </div>
        </div>

        <!-- Info Pemesan & Status -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6">
                <span class="text-muted small d-block">Tujuan Penagihan & Pengiriman:</span>
                <strong class="text-dark fs-6"><?= esc($shipping['penerima_nama'] ?? $customer['nama']) ?></strong>
                <div class="small text-secondary mt-1">
                    <i class="bi bi-telephone me-1"></i> <?= esc($shipping['penerima_telepon'] ?? $customer['no_hp']) ?><br>
                    <i class="bi bi-envelope me-1"></i> <?= esc($customer['email']) ?><br>
                    <i class="bi bi-geo-alt me-1"></i> <?= esc($shipping['alamat_lengkap'] ?? '-') ?>, <?= esc($shipping['kota'] ?? '') ?>
                </div>
            </div>
            <div class="col-sm-6 text-sm-end">
                <div class="small text-muted">Tanggal Transaksi:</div>
                <strong class="text-dark"><?= format_tanggal($order['created_at']) ?></strong>

                <div class="small text-muted mt-2">Metode Pembayaran:</div>
                <span class="badge bg-light text-dark border text-uppercase font-monospace"><?= esc($payment['metode'] ?? 'Transfer Manual') ?></span>

                <div class="small text-muted mt-2">Status Pembayaran:</div>
                <strong class="<?= ($payment['status'] === 'lunas') ? 'text-success' : 'text-warning' ?> text-uppercase">
                    <?= ($payment['status'] === 'lunas') ? 'LUNAS / TERVERIFIKASI' : 'MENUNGGU VERIFIKASI' ?>
                </strong>
            </div>
        </div>

        <!-- Tabel Rincian Pembelian -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light small text-muted text-uppercase">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Komoditas Produk Perkebunan</th>
                        <th class="text-end" style="width: 20%;">Harga Satuan</th>
                        <th class="text-center" style="width: 12%;">Jumlah</th>
                        <th class="text-end" style="width: 22%;">Total</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php $no = 1; foreach ($details as $d): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <strong><?= esc($d['nama_produk']) ?></strong>
                            <small class="text-muted d-block"><?= esc($d['nama_unit'] ?? 'Polinela') ?></small>
                        </td>
                        <td class="text-end"><?= format_rupiah($d['harga']) ?></td>
                        <td class="text-center"><?= $d['qty'] ?></td>
                        <td class="text-end fw-bold"><?= format_rupiah($d['subtotal']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="small">
                    <tr>
                        <td colspan="4" class="text-end fw-semibold">Subtotal Produk:</td>
                        <td class="text-end fw-bold"><?= format_rupiah($order['total_produk']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end fw-semibold">Biaya Pengiriman (<?= esc($shipping['kurir'] ?? 'Kurir Kampus') ?>):</td>
                        <td class="text-end fw-bold"><?= ($order['ongkir'] == 0) ? 'GRATIS' : format_rupiah($order['ongkir']) ?></td>
                    </tr>
                    <?php if ($order['diskon'] > 0): ?>
                    <tr class="text-success">
                        <td colspan="4" class="text-end fw-semibold">Diskon Voucher (<?= esc($order['kode_voucher']) ?>):</td>
                        <td class="text-end fw-bold">- <?= format_rupiah($order['diskon']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="table-light fs-6">
                        <td colspan="4" class="text-end fw-bold text-dark">Total Pembayaran Akhir:</td>
                        <td class="text-end fw-extrabold text-success"><?= format_rupiah($order['grand_total']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tanda Tangan & Stempel Resmi Kampus -->
        <div class="row pt-4 mt-4 border-top">
            <div class="col-6">
                <div class="small text-muted">
                    <strong>Catatan Penting:</strong>
                    <ul class="ps-3 mb-0" style="font-size: 0.76rem;">
                        <li>Dokumen ini merupakan bukti transaksi sah Polinela Agro Digital.</li>
                        <li>Produk perkebunan disimpan sesuai petunjuk penyimpanan kemasan.</li>
                        <li>Layanan bantuan: agro@polinela.ac.id / WhatsApp 0812-3456-7890.</li>
                    </ul>
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="small text-muted">Bandar Lampung, <?= date('d F Y', strtotime($order['created_at'])) ?></div>
                <div class="small text-muted mb-4">Pengelola Teaching Factory Perkebunan Polinela</div>
                <div class="fw-bold text-dark text-decoration-underline mt-4">Unit Usaha & Tefa Polinela</div>
                <div class="small text-secondary" style="font-size: 0.72rem;">BLU Politeknik Negeri Lampung</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
