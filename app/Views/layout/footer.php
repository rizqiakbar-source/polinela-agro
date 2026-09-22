<footer class="footer-agro">
    <div class="container">
        <div class="row g-4">
            <!-- Kolom 1: Profil Polinela Agro Digital -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-badge" style="width: 38px; height: 38px; font-size: 1.2rem;">
                        <i class="bi bi-tree"></i>
                    </div>
                    <div>
                        <div class="text-white fw-bold fs-5">POLINELA AGRO DIGITAL</div>
                        <div class="small text-success">Teaching Factory & Unit Usaha</div>
                    </div>
                </div>
                <p class="text-light-50 small pe-lg-3 mb-3" style="color: #94a3b8; line-height: 1.6;">
                    Platform pasar digital terpadu produk perkebunan Politeknik Negeri Lampung. Menghubungkan hasil panen kebun riset kampus vokasi dengan civitas academica dan masyarakat luas.
                </p>
                <div class="d-flex gap-2">
                    <a href="https://polinela.ac.id" target="_blank" class="btn btn-outline-light btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Website Polinela">
                        <i class="bi bi-globe"></i>
                    </a>
                    <a href="https://instagram.com/politeknik_negeri_lampung" target="_blank" class="btn btn-outline-light btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="https://youtube.com/@politekniknegerilampung" target="_blank" class="btn btn-outline-light btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="YouTube">
                        <i class="bi bi-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Kolom 2: Unit Usaha Perkebunan -->
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white fw-bold mb-3">Unit Usaha & Tefa</h5>
                <ul class="list-unstyled d-flex flex-column gap-2 small mb-0">
                    <li><a href="<?= base_url('katalog?unit=1') ?>" class="text-decoration-none"><i class="bi bi-chevron-right me-1 text-success"></i> Unit Kopi Polinela (Robusta Tefa)</a></li>
                    <li><a href="<?= base_url('katalog?unit=2') ?>" class="text-decoration-none"><i class="bi bi-chevron-right me-1 text-success"></i> Unit Kakao & Cokelat Artisan</a></li>
                    <li><a href="<?= base_url('katalog?unit=3') ?>" class="text-decoration-none"><i class="bi bi-chevron-right me-1 text-success"></i> Unit Lada Hitam Asli Lampung</a></li>
                    <li><a href="<?= base_url('katalog?unit=4') ?>" class="text-decoration-none"><i class="bi bi-chevron-right me-1 text-success"></i> Unit Olahan Atsiri & Pupuk Organik</a></li>
                    <li><a href="<?= base_url('tentang') ?>" class="text-decoration-none"><i class="bi bi-chevron-right me-1 text-success"></i> Profil Kebun Percobaan Kampus</a></li>
                </ul>
            </div>

            <!-- Kolom 3: Layanan Pelanggan -->
            <div class="col-lg-2 col-md-6">
                <h5 class="text-white fw-bold mb-3">Layanan & Bantuan</h5>
                <ul class="list-unstyled d-flex flex-column gap-2 small mb-0">
                    <li><a href="<?= base_url('katalog') ?>" class="text-decoration-none">Semua Produk</a></li>
                    <li><a href="<?= base_url('lacak') ?>" class="text-decoration-none">Lacak Pengiriman</a></li>
                    <li><a href="<?= base_url('keranjang') ?>" class="text-decoration-none">Keranjang Belanja</a></li>
                    <li><a href="<?= base_url('sus') ?>" class="text-warning fw-semibold text-decoration-none"><i class="bi bi-star-fill me-1"></i> Evaluasi Usability (SUS)</a></li>
                    <li><a href="<?= base_url('kontak') ?>" class="text-decoration-none">Hubungi Kami</a></li>
                </ul>
            </div>

            <!-- Kolom 4: Alamat Kampus -->
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white fw-bold mb-3">Kampus Polinela</h5>
                <div class="small d-flex flex-column gap-2" style="color: #94a3b8;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt-fill text-success mt-1"></i>
                        <span>Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung 35141</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-whatsapp text-success"></i>
                        <span>0812-3456-7890 (Tefa WhatsApp)</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-fill text-success"></i>
                        <span>agro@polinela.ac.id</span>
                    </div>
                    <div class="mt-2 p-2 rounded-2 bg-dark bg-opacity-50 border border-secondary border-opacity-25 small">
                        <i class="bi bi-shield-check text-success me-1"></i> Transaksi Aman, Bergaransi Mutu Kampus Vokasi
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div>
                &copy; <?= date('Y') ?> <strong>Polinela Agro Digital</strong>. Hak Cipta Dilindungi Politeknik Negeri Lampung.
            </div>
            <div class="small text-muted">
                Dikembangkan oleh <span class="text-success fw-semibold">Teaching Factory Manajemen Informatika Polinela</span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>
<!-- Custom App JS -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
