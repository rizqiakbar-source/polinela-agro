<?= view('layout/header', ['title' => $title ?? 'Panduan Pembayaran - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('pesanan') ?>" class="text-success">Pesanan</a></li>
            <li class="breadcrumb-item active">Panduan Pembayaran</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                <div class="text-center mb-4">
                    <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill mb-2">
                        <i class="bi bi-wallet2 me-1"></i> Rekening Resmi Kampus
                    </span>
                    <h4 class="fw-bold text-dark">Instruksi Pembayaran Pesanan</h4>
                    <?php if (isset($order)): ?>
                        <p class="text-muted small">Nomor Pesanan: <strong class="text-dark">#<?= esc($order['order_number']) ?></strong> | Total: <strong class="text-success"><?= format_rupiah($order['grand_total']) ?></strong></p>
                    <?php endif; ?>
                </div>

                <div class="list-group list-group-flush mb-4">
                    <?php if (!empty($bank_accounts)): ?>
                        <?php foreach ($bank_accounts as $acc): ?>
                        <div class="list-group-item d-flex align-items-center justify-content-between p-3 border rounded-3 mb-2">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark"><?= esc($acc['bank']) ?></h6>
                                <div class="small text-muted">Atas Nama: <strong><?= esc($acc['atas_nama']) ?></strong></div>
                                <div class="font-monospace fs-5 fw-bold text-success mt-1"><?= esc($acc['no_rekening']) ?></div>
                            </div>
                            <button class="btn btn-outline-secondary btn-sm rounded-pill" onclick="navigator.clipboard.writeText('<?= esc($acc['no_rekening']) ?>'); Swal.fire({toast:true, position:'top-end', icon:'success', title:'Nomor rekening disalin!', showConfirmButton:false, timer:2000});">
                                <i class="bi bi-clipboard me-1"></i> Salin
                            </button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 border rounded-3 text-center text-muted">
                            Rekening Bank Mandiri: <strong>114-00-8899123-4</strong> (a.n POLINELA TEFA)
                        </div>
                    <?php endif; ?>
                </div>

                <div class="alert alert-info border-0 rounded-3 small">
                    <h6 class="fw-bold mb-1"><i class="bi bi-info-circle-fill me-1"></i> Langkah Konfirmasi:</h6>
                    <ol class="mb-0 ps-3">
                        <li>Lakukan transfer sesuai nominal total pesanan.</li>
                        <li>Simpan bukti transfer (struk ATM / screenshot m-banking).</li>
                        <li>Buka menu <strong>Riwayat Pesanan</strong> lalu klik <strong>Kirim Bukti Pembayaran</strong>.</li>
                        <li>Admin unit perkebunan akan memverifikasi pesanan Anda dalam waktu maksimal 1x24 jam.</li>
                    </ol>
                </div>

                <?php if (isset($order)): ?>
                <div class="text-center mt-3">
                    <a href="<?= base_url('pesanan/detail/' . $order['order_number']) ?>" class="btn btn-agro px-4 py-2">
                        <i class="bi bi-upload me-1"></i> Upload Bukti Pembayaran Sekarang
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
