<?= view('layout/header', ['title' => 'Konfirmasi Pembayaran - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center">
                <div class="text-success display-4 mb-3">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Konfirmasi Pembayaran</h4>
                <p class="text-muted small mb-4">
                    Untuk mempercepat proses pengiriman barang hasil perkebunan, Anda dapat mengonfirmasi bukti bayar melalui halaman pesanan atau WhatsApp resmi Tefa.
                </p>

                <div class="d-grid gap-2">
                    <a href="<?= base_url('pesanan') ?>" class="btn btn-agro py-2">
                        <i class="bi bi-receipt me-1"></i> Buka Riwayat Pesanan Saya
                    </a>
                    <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Polinela%20Agro,%20saya%20ingin%20konfirmasi%20pembayaran" target="_blank" class="btn btn-outline-success py-2">
                        <i class="bi bi-whatsapp me-1"></i> Konfirmasi via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
