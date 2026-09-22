<?= view('layout/header', ['title' => 'Polinela Agro Digital - Pasar Digital Produk Perkebunan Kampus']) ?>
<?= view('layout/navbar_frontend') ?>

<!-- Hero Carousel Slider -->
<section class="container py-4">
    <div id="heroCarousel" class="carousel slide hero-slider-wrap" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($banners as $idx => $b): ?>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $idx ?>" class="<?= $idx === 0 ? 'active' : '' ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($banners as $idx => $b): ?>
            <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                <div class="hero-slide-item" style="background-image: url('<?= $idx === 0 ? 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=1600&auto=format&fit=crop&q=80' : 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=1600&auto=format&fit=crop&q=80' ?>');">
                    <div class="hero-overlay"></div>
                    <div class="hero-content">
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">
                            <i class="bi bi-award-fill me-1"></i> Panen Unggulan Kampus Polinela
                        </span>
                        <h1 class="display-5 fw-extrabold mb-3 text-white" style="letter-spacing: -1px;"><?= esc($b['judul']) ?></h1>
                        <p class="lead mb-4 text-white-50" style="font-size: 1.05rem;"><?= esc($b['subjudul']) ?></p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="<?= base_url('katalog') ?>" class="btn btn-agro btn-lg px-4 fs-6">
                                <i class="bi bi-bag-check-fill me-2"></i> Belanja Produk Sekarang
                            </a>
                            <a href="<?= base_url('tentang') ?>" class="btn btn-outline-light btn-lg px-4 fs-6">
                                <i class="bi bi-info-circle me-2"></i> Tentang Kebun Riset
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</section>

<!-- Keunggulan Kampus (Trust Pillars) -->
<section class="container py-3">
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center gap-3 h-100">
                <div class="rounded-3 bg-success-subtle text-success p-3 fs-3">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">100% Produk Kampus</h6>
                    <small class="text-muted">Hasil panen & riset murni Tefa Polinela</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center gap-3 h-100">
                <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-3">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Transaksi Transparan</h6>
                    <small class="text-muted">Transfer bank resmi & payment gateway</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center gap-3 h-100">
                <div class="rounded-3 bg-warning-subtle text-warning p-3 fs-3">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Kurir & Ambil di Tefa</h6>
                    <small class="text-muted">Bisa ambil langsung di kampus / dikirim</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="p-3 bg-white rounded-4 border shadow-sm d-flex align-items-center gap-3 h-100">
                <div class="rounded-3 bg-danger-subtle text-danger p-3 fs-3">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Standar Vokasi Mutu</h6>
                    <small class="text-muted">Bebas bahan kimia pengawet sintetis</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Kategori Komoditas Perkebunan -->
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="badge bg-success-subtle text-success fw-bold px-3 py-1 rounded-pill mb-2">Komoditas Kampus</span>
            <h3 class="fw-extrabold mb-0" style="letter-spacing: -0.5px;">Kategori Produk Pilihan</h3>
        </div>
        <a href="<?= base_url('katalog') ?>" class="text-success fw-bold text-decoration-none">
            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-3">
        <?php foreach ($categories as $cat): ?>
        <div class="col-6 col-md-4 col-lg">
            <a href="<?= base_url('katalog?kategori=' . $cat['slug']) ?>" class="category-card text-decoration-none">
                <div class="category-icon-box">
                    <i class="bi <?= esc($cat['icon'] ?: 'bi-tag') ?>"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1 small"><?= esc($cat['nama_kategori']) ?></h6>
                <span class="badge bg-light text-muted fw-normal" style="font-size: 0.72rem;"><?= $cat['product_count'] ?? 0 ?> Produk</span>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Produk Unggulan (Featured Products) -->
<section class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="badge bg-warning-subtle text-warning fw-bold px-3 py-1 rounded-pill mb-2">Rekomendasi Utama</span>
            <h3 class="fw-extrabold mb-0" style="letter-spacing: -0.5px;">Produk Unggulan Kebun Polinela</h3>
        </div>
        <a href="<?= base_url('katalog?sort=popular') ?>" class="text-success fw-bold text-decoration-none">
            Lihat Terlaris <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-3">
        <?php if (!empty($featured_products)): ?>
            <?php foreach ($featured_products as $product): ?>
                <?= view('components/card_produk', ['product' => $product]) ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">Belum ada produk unggulan yang ditampilkan.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Unit Usaha Showcase -->
<section class="container py-5">
    <div class="campus-trust-banner rounded-4 p-4 p-lg-5 position-relative overflow-hidden">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill mb-3">Teaching Factory (Tefa)</span>
                <h2 class="display-6 fw-extrabold text-white mb-3">Unit Usaha & Laboratorium Perkebunan Polinela</h2>
                <p class="text-white-50 lead fs-6 mb-4">
                    Setiap produk yang Anda beli diproduksi dan diolah langsung oleh mahasiswa praktikum bersama dosen ahli perkebunan Politeknik Negeri Lampung. Mendukung kemandirian pangan dan transformasi digital vokasi.
                </p>
                <div class="d-flex gap-3">
                    <a href="<?= base_url('tentang') ?>" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-pill">
                        Pelajari Unit Usaha <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <?php foreach ($units as $unit): ?>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-20 text-white backdrop-blur">
                            <h6 class="fw-bold mb-1 text-warning"><i class="bi bi-building me-1"></i> <?= esc($unit['nama_unit']) ?></h6>
                            <p class="small text-white-50 mb-2 line-clamp-2"><?= esc($unit['lokasi']) ?></p>
                            <a href="<?= base_url('katalog?unit=' . $unit['id']) ?>" class="small text-white text-decoration-underline">Lihat Produk Unit →</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Produk Terbaru -->
<section class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill mb-2">Baru Panen</span>
            <h3 class="fw-extrabold mb-0" style="letter-spacing: -0.5px;">Koleksi Produk Terbaru</h3>
        </div>
        <a href="<?= base_url('katalog?sort=latest') ?>" class="text-success fw-bold text-decoration-none">
            Jelajahi Semua <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-3">
        <?php foreach ($latest_products as $product): ?>
            <?= view('components/card_produk', ['product' => $product]) ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- Call to Action Kuesioner SUS -->
<section class="container py-4 mb-4">
    <div class="p-4 p-md-5 rounded-4 bg-white border shadow-sm d-flex flex-column flex-md-row align-items-center justify-content-between gap-4">
        <div>
            <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-2">
                <i class="bi bi-patch-question-fill me-1"></i> Evaluasi Pengalaman Pengguna
            </span>
            <h4 class="fw-extrabold mb-1 text-dark">Bantu Kami Mengukur Kemudahan Sistem Ini (SUS)</h4>
            <p class="text-muted mb-0 small">
                Penelitian ini menguji kepuasan pengguna dengan metode ilmiah <strong>System Usability Scale (SUS)</strong>. Isi 10 pertanyaan singkat dan lihat skor kepuasan secara langsung!
            </p>
        </div>
        <a href="<?= base_url('sus') ?>" class="btn btn-agro text-nowrap px-4 py-2 fs-6">
            <i class="bi bi-pencil-square me-2"></i> Isi Kuesioner SUS
        </a>
    </div>
</section>

<?= view('layout/footer') ?>
