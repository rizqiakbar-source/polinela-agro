<?= view('layout/header', ['title' => 'Checkout Pesanan - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('keranjang') ?>" class="text-success">Keranjang</a></li>
            <li class="breadcrumb-item active">Checkout & Pembayaran</li>
        </ol>
    </nav>

    <h3 class="fw-extrabold mb-4"><i class="bi bi-shield-check text-success me-2"></i> Checkout & Penyelesaian Pesanan</h3>

    <form action="<?= base_url('checkout/process') ?>" method="post" id="checkoutForm">
        <?= csrf_field() ?>
        <input type="hidden" id="raw-subtotal" value="<?= $subtotal ?>">
        <input type="hidden" name="kode_voucher" id="applied-voucher-code" value="">

        <div class="row g-4">
            <!-- Kolom Kiri: Data Pengiriman & Metode Pembayaran -->
            <div class="col-lg-8">
                <!-- 1. Alamat & Kontak Penerima -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-circle px-2 py-1">1</span> Alamat & Data Penerima
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Nama Penerima</label>
                            <input type="text" name="penerima_nama" class="form-control" value="<?= esc($user['nama']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Nomor WhatsApp / HP</label>
                            <input type="text" name="penerima_telepon" class="form-control" value="<?= esc($user['no_hp']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Alamat Lengkap (Jalan, Asrama / Fakultas / Gedung)</label>
                            <textarea name="alamat_lengkap" class="form-control" rows="2" placeholder="Contoh: Gedung Sakura Asrama Polinela / Jl. Soekarno Hatta..." required><?= esc($user['alamat'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Kota / Kabupaten</label>
                            <input type="text" name="kota" class="form-control" value="Bandar Lampung" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Kode Pos</label>
                            <input type="text" name="kode_pos" class="form-control" value="35141">
                        </div>
                    </div>
                </div>

                <!-- 2. Opsi Pengiriman / Kurir -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-circle px-2 py-1">2</span> Pilihan Jasa Kirim & Pengambilan
                    </h5>

                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($shipping_rates as $idx => $sr): ?>
                        <label class="p-3 border rounded-3 d-flex justify-content-between align-items-center cursor-pointer shipping-option-card">
                            <div class="d-flex align-items-center gap-3">
                                <input type="radio" name="shipping_option" value="<?= $idx ?>" class="form-check-input mt-0 shipping-radio" data-tarif="<?= $sr['tarif'] ?>" <?= ($idx === 0) ? 'checked' : '' ?>>
                                <div>
                                    <div class="fw-bold text-dark small"><?= esc($sr['wilayah']) ?></div>
                                    <small class="text-muted"><i class="bi bi-clock me-1"></i> Estimasi: <?= esc($sr['estimasi']) ?></small>
                                </div>
                            </div>
                            <div class="fw-bold <?= ($sr['tarif'] == 0) ? 'text-success' : 'text-dark' ?>">
                                <?= ($sr['tarif'] == 0) ? 'GRATIS' : format_rupiah($sr['tarif']) ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3. Metode Pembayaran Hybrid -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-circle px-2 py-1">3</span> Metode Pembayaran
                    </h5>

                    <div class="row g-3">
                        <!-- Transfer Bank Manual -->
                        <div class="col-md-6">
                            <label class="p-3 border rounded-3 d-flex align-items-start gap-3 h-100 cursor-pointer payment-method-card active">
                                <input type="radio" name="metode_bayar" value="transfer" class="form-check-input mt-1" checked>
                                <div>
                                    <div class="fw-bold text-dark small"><i class="bi bi-bank me-1 text-primary"></i> Transfer Bank Manual</div>
                                    <small class="text-muted d-block mt-1">Transfer ke rekening resmi BLU Politeknik Negeri Lampung, lalu unggah struk.</small>
                                </div>
                            </label>
                        </div>

                        <!-- COD -->
                        <div class="col-md-6">
                            <label class="p-3 border rounded-3 d-flex align-items-start gap-3 h-100 cursor-pointer payment-method-card">
                                <input type="radio" name="metode_bayar" value="cod" class="form-check-input mt-1">
                                <div>
                                    <div class="fw-bold text-dark small"><i class="bi bi-cash-stack me-1 text-success"></i> COD / Bayar di Tempat</div>
                                    <small class="text-muted d-block mt-1">Bayar tunai saat mengambil pesanan di Teaching Factory Kampus Polinela.</small>
                                </div>
                            </label>
                        </div>

                        <!-- QRIS -->
                        <div class="col-md-6">
                            <label class="p-3 border rounded-3 d-flex align-items-start gap-3 h-100 cursor-pointer payment-method-card">
                                <input type="radio" name="metode_bayar" value="qris" class="form-check-input mt-1">
                                <div>
                                    <div class="fw-bold text-dark small"><i class="bi bi-qr-code-scan me-1 text-danger"></i> QRIS (Instan Otomatis)</div>
                                    <small class="text-muted d-block mt-1">Pindai QRIS via BCA Mobile, GoPay, OVO, Dana, ShopeePay (Simulasi Midtrans).</small>
                                </div>
                            </label>
                        </div>

                        <!-- Virtual Account -->
                        <div class="col-md-6">
                            <label class="p-3 border rounded-3 d-flex align-items-start gap-3 h-100 cursor-pointer payment-method-card">
                                <input type="radio" name="metode_bayar" value="va" class="form-check-input mt-1">
                                <div>
                                    <div class="fw-bold text-dark small"><i class="bi bi-credit-card-2-front me-1 text-info"></i> Virtual Account</div>
                                    <small class="text-muted d-block mt-1">Nomor akun virtual otomatis Bank Mandiri / BRI / BCA.</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Rekening Tujuan Info -->
                    <div class="mt-4 p-3 rounded-3 bg-light border" id="bank-info-box">
                        <div class="small fw-bold text-dark mb-2"><i class="bi bi-info-circle text-success me-1"></i> Rekening Resmi Politeknik Negeri Lampung:</div>
                        <ul class="list-unstyled mb-0 small text-muted">
                            <?php foreach ($bank_accounts as $b): ?>
                            <li class="mb-1">
                                <strong><?= esc($b['bank']) ?></strong>: <span class="badge bg-white text-dark border font-monospace"><?= esc($b['no_rekening']) ?></span> a.n <?= esc($b['atas_nama']) ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="mt-3">
                        <label class="form-label small fw-semibold text-muted">Catatan Pesanan (Opsional)</label>
                        <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Contoh: Titip di pos satpam depan, atau hubungi sebelum sampai...">
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Rincian Produk, Voucher, & Tombol Bayar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 100px;">
                    <h5 class="fw-bold mb-3">Ringkasan Pembayaran</h5>

                    <!-- Item Ringkas -->
                    <div class="mb-3 d-flex flex-column gap-2 border-bottom pb-3">
                        <?php foreach ($items as $it): ?>
                        <div class="d-flex justify-content-between align-items-center small">
                            <span class="text-truncate" style="max-width: 190px;">
                                <?= esc($it['nama_produk']) ?> <span class="text-muted">x<?= $it['qty'] ?></span>
                            </span>
                            <strong class="text-dark"><?= format_rupiah($it['harga'] * $it['qty']) ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Input Voucher Promo -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Punya Voucher Diskon?</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="voucher-input" class="form-control text-uppercase" placeholder="POLINELAJUARA">
                            <button type="button" class="btn btn-outline-success" onclick="applyVoucher()">Gunakan</button>
                        </div>
                        <div id="voucher-message" class="small mt-1"></div>
                    </div>

                    <!-- Kalkulasi Total -->
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Subtotal Produk</span>
                        <strong class="text-dark"><?= format_rupiah($subtotal) ?></strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Ongkos Kirim</span>
                        <strong class="text-dark" id="display-ongkir">Rp 0</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3 small text-success d-none" id="row-diskon">
                        <span>Potongan Voucher</span>
                        <strong id="display-diskon">- Rp 0</strong>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top mb-4">
                        <span class="fw-bold fs-6">Grand Total</span>
                        <strong class="fw-extrabold text-success fs-5" id="display-grand-total">
                            <?= format_rupiah($subtotal) ?>
                        </strong>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-agro py-2 fs-6">
                            <i class="bi bi-lock-fill me-2"></i> Buat Pesanan Sekarang
                        </button>
                    </div>

                    <div class="text-center mt-3 text-muted small" style="font-size: 0.75rem;">
                        <i class="bi bi-shield-check text-success me-1"></i> Transaksi dijamin aman oleh Politeknik Negeri Lampung
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let currentSubtotal = <?= $subtotal ?>;
let currentOngkir = 0;
let currentDiskon = 0;

function recalculateGrandTotal() {
    let grand = Math.max(0, currentSubtotal + currentOngkir - currentDiskon);
    document.getElementById('display-grand-total').innerText = 'Rp ' + grand.toLocaleString('id-ID');
}

// Event listener pilihan ongkir
const shippingRadios = document.querySelectorAll('.shipping-radio');
shippingRadios.forEach(radio => {
    radio.addEventListener('change', function () {
        currentOngkir = parseFloat(this.dataset.tarif) || 0;
        document.getElementById('display-ongkir').innerText = (currentOngkir === 0) ? 'GRATIS' : 'Rp ' + currentOngkir.toLocaleString('id-ID');
        recalculateGrandTotal();
    });
});

// Set default ongkir
const checkedRadio = document.querySelector('.shipping-radio:checked');
if (checkedRadio) {
    currentOngkir = parseFloat(checkedRadio.dataset.tarif) || 0;
    document.getElementById('display-ongkir').innerText = (currentOngkir === 0) ? 'GRATIS' : 'Rp ' + currentOngkir.toLocaleString('id-ID');
    recalculateGrandTotal();
}

function applyVoucher() {
    const code = document.getElementById('voucher-input').value.trim();
    const msgEl = document.getElementById('voucher-message');

    if (!code) {
        msgEl.innerHTML = '<span class="text-danger">Masukkan kode voucher.</span>';
        return;
    }

    const formData = new FormData();
    formData.append('code', code);
    formData.append('subtotal', currentSubtotal);

    fetch(window.BASE_URL + 'checkout/voucher', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.valid) {
            currentDiskon = parseFloat(data.nilai_diskon) || 0;
            document.getElementById('applied-voucher-code').value = code;
            document.getElementById('row-diskon').classList.remove('d-none');
            document.getElementById('display-diskon').innerText = '- Rp ' + currentDiskon.toLocaleString('id-ID');
            msgEl.innerHTML = `<span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>${data.message}</span>`;
            recalculateGrandTotal();
        } else {
            msgEl.innerHTML = `<span class="text-danger"><i class="bi bi-x-circle me-1"></i>${data.message}</span>`;
        }
    })
    .catch(err => console.error(err));
}
</script>

<?= view('layout/footer') ?>
