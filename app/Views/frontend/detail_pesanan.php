<?= view('layout/header', ['title' => "Pesanan #{$order['order_number']} - Polinela Agro Digital"]) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('pesanan') ?>" class="text-success">Riwayat Pesanan</a></li>
            <li class="breadcrumb-item active">#<?= esc($order['order_number']) ?></li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="fw-extrabold mb-0">Pesanan #<?= esc($order['order_number']) ?></h3>
                <?= status_badge($order['status']) ?>
            </div>
            <small class="text-muted"><i class="bi bi-clock me-1"></i> Waktu Transaksi: <?= format_tanggal($order['created_at']) ?></small>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= base_url('invoice/' . $order['order_number']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm px-3">
                <i class="bi bi-printer me-1"></i> Lihat Invoice
            </a>
            <a href="<?= base_url('invoice/pdf/' . $order['order_number']) ?>" class="btn btn-agro btn-sm px-3">
                <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
            </a>
        </div>
    </div>

    <!-- Timeline Status Pengiriman & Transaksi -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-diagram-3 text-success me-2"></i> Status Alur Pesanan</h6>
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 text-center position-relative">
            <div class="flex-fill p-2 rounded-3 <?= in_array($order['status'], ['pending', 'menunggu_verifikasi', 'diproses', 'dikirim', 'selesai']) ? 'bg-success-subtle text-success fw-bold' : 'bg-light text-muted' ?>">
                <i class="bi bi-receipt fs-4 d-block mb-1"></i>
                <span class="small">1. Order Dibuat</span>
            </div>
            <div class="flex-fill p-2 rounded-3 <?= in_array($order['status'], ['menunggu_verifikasi', 'diproses', 'dikirim', 'selesai']) ? 'bg-success-subtle text-success fw-bold' : 'bg-light text-muted' ?>">
                <i class="bi bi-credit-card fs-4 d-block mb-1"></i>
                <span class="small">2. Verifikasi Bayar</span>
            </div>
            <div class="flex-fill p-2 rounded-3 <?= in_array($order['status'], ['diproses', 'dikirim', 'selesai']) ? 'bg-success-subtle text-success fw-bold' : 'bg-light text-muted' ?>">
                <i class="bi bi-box-seam fs-4 d-block mb-1"></i>
                <span class="small">3. Diproses Unit</span>
            </div>
            <div class="flex-fill p-2 rounded-3 <?= in_array($order['status'], ['dikirim', 'selesai']) ? 'bg-success-subtle text-success fw-bold' : 'bg-light text-muted' ?>">
                <i class="bi bi-truck fs-4 d-block mb-1"></i>
                <span class="small">4. Dikirim / Siap Ambil</span>
            </div>
            <div class="flex-fill p-2 rounded-3 <?= ($order['status'] === 'selesai') ? 'bg-success text-white fw-bold' : 'bg-light text-muted' ?>">
                <i class="bi bi-check-circle fs-4 d-block mb-1"></i>
                <span class="small">5. Selesai</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri: Rincian Produk & Pengiriman -->
        <div class="col-lg-8">
            <!-- Tabel Item Produk -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-white p-3 border-bottom">
                    <h6 class="fw-bold mb-0">Rincian Komoditas Dipesan</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light small text-muted">
                            <tr>
                                <th class="ps-4">Produk</th>
                                <th>Harga Satuan</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-end pe-4">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $d): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= base_url('assets/img/products/' . ($d['gambar_utama'] ?: 'default-product.png')) ?>" 
                                             class="rounded-3 border" 
                                             style="width: 50px; height: 50px; object-fit: cover;"
                                             onerror="this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=150&auto=format&fit=crop&q=80';">
                                        <div>
                                            <a href="<?= base_url('produk/' . $d['slug']) ?>" class="fw-bold text-dark text-decoration-none small d-block">
                                                <?= esc($d['nama_produk']) ?>
                                            </a>
                                            <small class="text-muted"><?= esc($d['nama_unit'] ?? 'Polinela') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="small"><?= format_rupiah($d['harga']) ?></td>
                                <td class="text-center small fw-bold"><?= $d['qty'] ?></td>
                                <td class="text-end pe-4 small fw-bold text-success"><?= format_rupiah($d['subtotal']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Informasi Pengiriman -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt text-success me-2"></i> Informasi Pengiriman & Penerima</h6>
                <div class="row g-3 small">
                    <div class="col-md-6">
                        <span class="text-muted d-block">Nama Penerima:</span>
                        <strong class="text-dark"><?= esc($shipping['penerima_nama'] ?? '-') ?></strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block">Nomor Telepon / WhatsApp:</span>
                        <strong class="text-dark"><?= esc($shipping['penerima_telepon'] ?? '-') ?></strong>
                    </div>
                    <div class="col-md-12">
                        <span class="text-muted d-block">Alamat Lengkap:</span>
                        <strong class="text-dark"><?= esc($shipping['alamat_lengkap'] ?? '-') ?>, <?= esc($shipping['kota'] ?? '') ?> (<?= esc($shipping['kode_pos'] ?? '') ?>)</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block">Kurir / Jasa Pengiriman:</span>
                        <span class="badge bg-light text-dark border"><?= esc($shipping['kurir'] ?? 'Kurir Kampus') ?></span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted d-block">Nomor Resi:</span>
                        <strong class="text-primary font-monospace"><?= esc($shipping['no_resi'] ?? 'Belum ada nomor resi') ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Pembayaran & Aksi -->
        <div class="col-lg-4">
            <!-- Rincian Biaya -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3">Ringkasan Biaya</h6>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>Total Produk</span>
                    <strong class="text-dark"><?= format_rupiah($order['total_produk']) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>Ongkos Kirim</span>
                    <strong class="text-dark"><?= ($order['ongkir'] == 0) ? 'GRATIS' : format_rupiah($order['ongkir']) ?></strong>
                </div>
                <?php if ($order['diskon'] > 0): ?>
                <div class="d-flex justify-content-between mb-2 small text-success">
                    <span>Diskon Voucher (<?= esc($order['kode_voucher']) ?>)</span>
                    <strong>- <?= format_rupiah($order['diskon']) ?></strong>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between pt-3 border-top mb-3">
                    <span class="fw-bold">Total Pembayaran</span>
                    <strong class="fw-extrabold text-success fs-5"><?= format_rupiah($order['grand_total']) ?></strong>
                </div>

                <div class="small">
                    <span class="text-muted d-block">Metode Bayar:</span>
                    <strong class="text-uppercase"><?= esc($payment['metode'] ?? 'Transfer Manual') ?></strong>
                    <?= payment_badge($payment['status'] ?? 'pending') ?>
                </div>
            </div>

            <!-- Upload Bukti Bayar (Jika Transfer Manual) -->
            <?php if ($order['status'] === 'pending' && ($payment['metode'] ?? '') === 'transfer'): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 border-start border-4 border-warning">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-upload text-warning me-1"></i> Unggah Bukti Transfer</h6>
                <p class="small text-muted mb-3">
                    Silakan transfer tepat sebesar <strong><?= format_rupiah($order['grand_total']) ?></strong> ke rekening resmi Polinela:
                </p>
                <div class="p-2 bg-light rounded-3 small mb-3 border font-monospace">
                    <strong>Mandiri:</strong> 114-00-8899123-4<br>
                    <strong>BRI:</strong> 0098-01-000456-30-2<br>
                    <span class="text-muted">a.n POLINELA TEFA</span>
                </div>

                <form action="<?= base_url('pesanan/upload-bukti') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Foto Struk / Bukti Transfer:</label>
                        <input type="file" name="bukti_bayar" class="form-control form-control-sm" accept="image/*" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Bank Pengirim & Rekening Asal:</label>
                        <input type="text" name="atas_nama_pengirim" class="form-control form-control-sm" placeholder="Contoh: Budi Pratama - BCA" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-agro btn-sm py-2">
                            <i class="bi bi-cloud-upload me-1"></i> Kirim Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Bukti Bayar Terunggah Preview -->
            <?php if (!empty($payment['bukti_bayar'])): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-2">Bukti Bayar Terunggah</h6>
                <a href="<?= base_url('uploads/bukti_bayar/' . $payment['bukti_bayar']) ?>" target="_blank">
                    <img src="<?= base_url('uploads/bukti_bayar/' . $payment['bukti_bayar']) ?>" class="img-fluid rounded-3 border" style="max-height: 200px; width: 100%; object-fit: cover;">
                </a>
                <small class="text-muted d-block mt-2">Status: <strong><?= payment_badge($payment['status']) ?></strong></small>
            </div>
            <?php endif; ?>

            <!-- Aksi Konfirmasi Terima / Pembatalan / Beri Ulasan -->
            <div class="d-flex flex-column gap-2">
                <?php if ($order['status'] === 'selesai'): ?>
                <button type="button" class="btn btn-warning text-dark w-100 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalReview">
                    <i class="bi bi-star-fill text-dark me-1"></i> Beri Ulasan & Rating Produk
                </button>
                <?php endif; ?>

                <?php if ($order['status'] === 'dikirim'): ?>
                <form action="<?= base_url('pesanan/terima/' . $order['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold" onclick="return confirm('Apakah pesanan sudah Anda terima dengan baik?')">
                        <i class="bi bi-check2-circle me-1"></i> Konfirmasi Pesanan Diterima
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($order['status'] === 'pending'): ?>
                <form action="<?= base_url('pesanan/batal/' . $order['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 py-2" onclick="return confirm('Yakin ingin membatalkan pesanan ini?')">
                        <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan Ini
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Beri Ulasan Produk -->
<?php if ($order['status'] === 'selesai'): ?>
<div class="modal fade" id="modalReview" tabindex="-1" aria-labelledby="modalReviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalReviewLabel"><i class="bi bi-star-fill text-warning me-2"></i>Beri Ulasan Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('ulasan/store') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                <div class="modal-body py-0">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Komoditas yang Diulas:</label>
                        <select name="product_id" class="form-select form-select-sm" required>
                            <?php foreach ($details as $item): ?>
                                <option value="<?= $item['product_id'] ?>"><?= esc($item['nama_produk']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3 text-center">
                        <label class="form-label small fw-semibold d-block">Rating Kepuasan Anda:</label>
                        <div class="btn-group" role="group" aria-label="Rating Stars">
                            <?php for ($r = 1; $r <= 5; $r++): ?>
                                <input type="radio" class="btn-check" name="rating" id="star-<?= $r ?>" value="<?= $r ?>" <?= $r === 5 ? 'checked' : '' ?> required>
                                <label class="btn btn-outline-warning text-dark px-3 py-2" for="star-<?= $r ?>">
                                    ★ <?= $r ?>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pengalaman & Komentar Ulasan:</label>
                        <textarea name="komentar" rows="4" class="form-control" placeholder="Ceritakan kesegaran, rasa, kualitas kemasan, atau pelayanan pengiriman..." required></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-agro rounded-pill px-4 fw-bold">
                        <i class="bi bi-send me-1"></i> Kirim Ulasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= view('layout/footer') ?>
