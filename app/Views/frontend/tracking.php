<?= view('layout/header', ['title' => 'Lacak Pengiriman - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center mb-4">
                <div class="rounded-circle bg-success-subtle text-success p-3 mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                    <i class="bi bi-truck"></i>
                </div>
                <h4 class="fw-extrabold mb-2">Lacak Status Pesanan & Pengiriman</h4>
                <p class="text-muted small mb-4">Masukkan nomor pesanan (contoh: PNA-2026...) atau nomor resi kurir untuk mengetahui posisi barang Anda.</p>

                <form action="<?= base_url('lacak') ?>" method="get" class="col-md-9 mx-auto">
                    <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                        <input type="text" name="resi" class="form-control border-success" placeholder="Nomor pesanan atau nomor resi..." value="<?= esc($resi ?? '') ?>" required>
                        <button class="btn btn-agro px-4" type="submit">
                            <i class="bi bi-search me-1"></i> Lacak
                        </button>
                    </div>
                </form>
            </div>

            <?php if (!empty($resi)): ?>
                <?php if (!empty($order) && !empty($shipping)): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">Hasil Pelacakan Pesanan #<?= esc($order['order_number']) ?></h5>
                            <small class="text-muted"><?= format_tanggal($order['created_at']) ?></small>
                        </div>
                        <div>
                            <?= status_badge($order['status']) ?>
                        </div>
                    </div>

                    <div class="row g-3 small mb-4">
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Penerima:</span>
                            <strong><?= esc($shipping['penerima_nama']) ?> (<?= esc($shipping['penerima_telepon']) ?>)</strong>
                            <div class="text-secondary"><?= esc($shipping['alamat_lengkap']) ?>, <?= esc($shipping['kota']) ?></div>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted d-block">Jasa Kurir:</span>
                            <span class="badge bg-light text-dark border"><?= esc($shipping['kurir']) ?></span>
                            <div class="mt-1 font-monospace text-primary fw-bold">Resi: <?= esc($shipping['no_resi'] ?: 'Belum diinput') ?></div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold mb-2 small text-dark"><i class="bi bi-geo-alt-fill text-success me-1"></i> Posisi Terakhir Pengiriman:</h6>
                        <div class="text-muted small">
                            <?php if ($order['status'] === 'selesai'): ?>
                                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Paket telah diterima oleh pembeli.</span>
                            <?php elseif ($order['status'] === 'dikirim'): ?>
                                <span class="text-primary fw-bold"><i class="bi bi-truck me-1"></i> Paket sedang dalam perjalanan bersama kurir menuju alamat Anda.</span>
                            <?php elseif ($order['status'] === 'diproses'): ?>
                                <span class="text-warning fw-bold"><i class="bi bi-box-seam me-1"></i> Paket sedang dikemas dan dipersiapkan oleh unit Teaching Factory Polinela.</span>
                            <?php else: ?>
                                <span class="text-muted"><i class="bi bi-clock me-1"></i> Menunggu konfirmasi pembayaran pesanan.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning text-center rounded-4 p-4">
                    <i class="bi bi-exclamation-circle-fill fs-2 d-block mb-2 text-warning"></i>
                    Nomor pesanan atau resi <strong>"<?= esc($resi) ?>"</strong> tidak ditemukan dalam basis data sistem.
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
