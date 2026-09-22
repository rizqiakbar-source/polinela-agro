<?= view('layout/header', ['title' => 'Hubungi Kami - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-5">
    <div class="row g-4 align-items-center mb-5">
        <div class="col-lg-6">
            <span class="badge bg-success-subtle text-success fw-bold px-3 py-1 rounded-pill mb-2">Pusat Bantuan & Layanan</span>
            <h2 class="display-6 fw-extrabold text-dark mb-3">Hubungi Tim Pengelola Polinela Agro Digital</h2>
            <p class="text-muted lead fs-6 mb-4">
                Punya pertanyaan seputar produk perkebunan, pemesanan jumlah besar (bulk order untuk instansi), kerjasama riset, atau kendala transaksi? Kami siap membantu Anda.
            </p>

            <div class="d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 border shadow-sm">
                    <div class="rounded-3 bg-success-subtle text-success p-3 fs-4">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <strong class="text-dark small d-block">Lokasi Kampus & Tefa:</strong>
                        <span class="text-muted small"><?= esc($settings['campus_address'] ?? 'Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung 35141') ?></span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 border shadow-sm">
                    <div class="rounded-3 bg-success-subtle text-success p-3 fs-4">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div>
                        <strong class="text-dark small d-block">WhatsApp Resmi Tefa:</strong>
                        <span class="text-muted small"><?= esc($settings['contact_wa'] ?? '0812-3456-7890') ?></span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 border shadow-sm">
                    <div class="rounded-3 bg-success-subtle text-success p-3 fs-4">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div>
                        <strong class="text-dark small d-block">Email Resmi:</strong>
                        <span class="text-muted small"><?= esc($settings['contact_email'] ?? 'agro@polinela.ac.id') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white">
                <h5 class="fw-bold mb-3">Kirimkan Pesan Anda</h5>
                <form action="<?= base_url('kontak/kirim') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama Anda" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Alamat Email</label>
                        <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Subjek / Topik</label>
                        <input type="text" name="subjek" class="form-control" placeholder="Tanya produk kopi / pesanan..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Isi Pesan</label>
                        <textarea name="pesan" class="form-control" rows="4" placeholder="Tuliskan pertanyaan atau kebutuhan Anda..." required></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-agro py-2">Kirim Pesan Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
