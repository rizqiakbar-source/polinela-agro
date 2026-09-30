<?= view('layout/header', ['title' => esc($product['nama_produk']) . ' - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('katalog') ?>" class="text-success">Katalog</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('katalog?kategori=' . ($product['kategori_slug'] ?? '')) ?>" class="text-success"><?= esc($product['nama_kategori']) ?></a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 300px;"><?= esc($product['nama_produk']) ?></li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 mb-5 bg-white">
        <div class="row g-4 g-lg-5">
            <!-- Kolom Kiri: Galeri Foto Produk -->
            <div class="col-lg-5">
                <div class="position-relative rounded-4 overflow-hidden border mb-3" style="padding-top: 85%; background: #f8fafc;">
                    <img id="mainProductImg" 
                         src="<?= product_image_url($product['gambar_utama'] ?? '') ?>" 
                         alt="<?= esc($product['nama_produk']) ?>" 
                         class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover"
                         onerror="this.onerror=null; this.src='https://placehold.co/800x800/1b5e20/ffffff?text=Polinela+Agro';">
                </div>

                <!-- Thumbnail Switcher -->
                <div class="d-flex gap-2 overflow-auto pb-2">
                    <img src="<?= product_image_url($product['gambar_utama'] ?? '') ?>" 
                         class="rounded-3 border border-2 border-success p-1 thumbnail-selector cursor-pointer" 
                         style="width: 65px; height: 65px; object-fit: cover;" 
                         onclick="document.getElementById('mainProductImg').src=this.src;"
                         onerror="this.onerror=null; this.src='https://placehold.co/200x200/1b5e20/ffffff?text=Polinela';">
                    <?php if (!empty($images)): ?>
                        <?php foreach ($images as $img): ?>
                        <img src="<?= product_image_url($img['image_url']) ?>" 
                             class="rounded-3 border p-1 thumbnail-selector cursor-pointer" 
                             style="width: 65px; height: 65px; object-fit: cover;" 
                             onclick="document.getElementById('mainProductImg').src=this.src;">
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kolom Kanan: Info & Aksi Beli -->
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill fw-bold">
                        <?= esc($product['nama_kategori']) ?>
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill">
                        <i class="bi bi-geo-alt-fill me-1"></i><?= esc($product['nama_unit']) ?>
                    </span>
                </div>

                <h2 class="fw-extrabold text-dark mb-2" style="letter-spacing: -0.5px;"><?= esc($product['nama_produk']) ?></h2>

                <!-- Rating & Terjual -->
                <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                    <div class="d-flex align-items-center text-warning fs-6">
                        <i class="bi bi-star-fill me-1"></i>
                        <strong class="text-dark me-1"><?= number_format($product['rating_avg'], 1) ?></strong>
                        <span class="text-muted small">(<?= count($reviews) ?> ulasan)</span>
                    </div>
                    <span class="text-muted">•</span>
                    <span class="text-muted small">Terjual <strong><?= $product['total_terjual'] ?></strong> kali</span>
                </div>

                <!-- Harga -->
                <div class="mb-4">
                    <div class="display-6 fw-extrabold text-success mb-1">
                        <?= format_rupiah($product['harga']) ?>
                    </div>
                    <div class="text-muted small">
                        Harga satuan per <strong><?= esc($product['satuan']) ?></strong> (Estimasi berat: <?= $product['berat_gram'] ?> gram)
                    </div>
                </div>

                <!-- Stok Status -->
                <div class="mb-4">
                    <div class="small fw-semibold text-muted mb-1">Ketersediaan Stok:</div>
                    <?php if ($product['stok'] > 10): ?>
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fs-7">
                            <i class="bi bi-check2-circle me-1"></i> Stok Melimpah (<?= $product['stok'] ?> <?= esc($product['satuan']) ?> tersedia)
                        </span>
                    <?php elseif ($product['stok'] > 0): ?>
                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fs-7">
                            <i class="bi bi-exclamation-triangle me-1"></i> Stok Menipis! Tersisa <?= $product['stok'] ?> <?= esc($product['satuan']) ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fs-7">
                            <i class="bi bi-x-circle me-1"></i> Stok Habis
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Form Kuantitas & Tombol Beli -->
                <?php if ($product['stok'] > 0): ?>
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    <div class="d-flex align-items-center border rounded-3 bg-light p-1" style="width: 140px;">
                        <button type="button" class="btn btn-sm btn-light border-0 fw-bold px-3" onclick="decQty()">-</button>
                        <input type="number" id="product-qty" class="form-control form-control-sm text-center border-0 bg-transparent fw-bold" value="1" min="1" max="<?= $product['stok'] ?>">
                        <button type="button" class="btn btn-sm btn-light border-0 fw-bold px-3" onclick="incQty(<?= $product['stok'] ?>)">+</button>
                    </div>

                    <button type="button" class="btn btn-agro btn-lg px-4 fs-6 btn-add-cart" data-product-id="<?= $product['id'] ?>">
                        <i class="bi bi-bag-plus-fill me-2"></i> Tambah ke Keranjang
                    </button>

                    <button type="button" class="btn btn-outline-danger btn-lg p-3 rounded-circle product-wishlist-btn position-static" data-product-id="<?= $product['id'] ?>" title="Wishlist">
                        <i class="bi bi-heart fs-5"></i>
                    </button>
                </div>
                <?php endif; ?>

                <!-- Profil Unit Usaha Pengelola -->
                <div class="p-3 bg-light rounded-3 border d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fs-4" style="width: 50px; height: 50px;">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0"><?= esc($product['nama_unit']) ?></h6>
                        <small class="text-muted d-block"><?= esc($product['unit_lokasi'] ?? 'Politeknik Negeri Lampung') ?></small>
                        <small class="text-success"><i class="bi bi-whatsapp me-1"></i> Kontak Unit: <?= esc($product['unit_kontak'] ?? '0812-3456-7890') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Deskripsi & Ulasan -->
        <div class="mt-5 pt-4 border-top">
            <ul class="nav nav-tabs border-bottom-0 gap-2" id="productTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold px-4 py-2 border rounded-top-3" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-pane">Deskripsi Produk</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold px-4 py-2 border rounded-top-3" id="review-tab" data-bs-toggle="tab" data-bs-target="#review-pane">Ulasan Pembeli (<?= count($reviews) ?>)</button>
                </li>
            </ul>

            <div class="tab-content p-4 border rounded-bottom-4 bg-light" id="productTabContent">
                <!-- Deskripsi -->
                <div class="tab-pane fade show active" id="desc-pane">
                    <h5 class="fw-bold mb-3">Tentang Komoditas Ini</h5>
                    <div class="text-muted leading-relaxed" style="white-space: pre-line;">
                        <?= esc($product['deskripsi']) ?>
                    </div>
                </div>

                <!-- Ulasan -->
                <div class="tab-pane fade" id="review-pane">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Ulasan & Penilaian Pembeli</h5>
                        <?php if (session()->get('logged_in')): ?>
                        <button class="btn btn-agro btn-sm" data-bs-toggle="collapse" data-bs-target="#formUlasanCollapse">
                            <i class="bi bi-pencil-fill me-1"></i> Tulis Ulasan
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Form Ulasan Collapse -->
                    <?php if (session()->get('logged_in')): ?>
                    <div class="collapse mb-4" id="formUlasanCollapse">
                        <div class="card card-body border p-4 bg-white rounded-3">
                            <h6 class="fw-bold mb-3">Tulis Ulasan Anda</h6>
                            <form action="<?= base_url('ulasan/store') ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Pilih Rating Bintang:</label>
                                    <select name="rating" class="form-select form-select-sm" style="width: 150px;">
                                        <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Puas)</option>
                                        <option value="4">⭐⭐⭐⭐ (4 - Puas)</option>
                                        <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                                        <option value="2">⭐⭐ (2 - Kurang)</option>
                                        <option value="1">⭐ (1 - Buruk)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Ulasan / Komentar Anda:</label>
                                    <textarea name="komentar" class="form-control form-control-sm" rows="3" placeholder="Ceritakan rasa, aroma, atau kualitas produk..." required></textarea>
                                </div>

                                <button type="submit" class="btn btn-agro btn-sm px-4">Kirim Ulasan</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Daftar Ulasan -->
                    <?php if (!empty($reviews)): ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($reviews as $rev): ?>
                            <div class="p-3 bg-white rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            <?= strtoupper(substr($rev['user_nama'] ?? 'K', 0, 1)) ?>
                                        </div>
                                        <strong class="small"><?= esc($rev['user_nama'] ?? 'Civitas Polinela') ?></strong>
                                    </div>
                                    <div class="text-warning small">
                                        <?php for ($i=1; $i<=5; $i++): ?>
                                            <i class="bi bi-star<?= ($i <= $rev['rating']) ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="small text-muted mb-1"><?= esc($rev['komentar']) ?></p>
                                <small class="text-secondary" style="font-size: 0.72rem;"><?= format_tanggal($rev['created_at']) ?></small>

                                <?php if (!empty($rev['reply'])): ?>
                                <div class="mt-2 p-2 bg-light rounded border-start border-3 border-success small">
                                    <strong class="text-success"><i class="bi bi-reply-fill me-1"></i> Respon Admin Tefa:</strong>
                                    <p class="mb-0 text-muted"><?= esc($rev['reply']) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mb-0">Belum ada ulasan untuk produk ini. Jadilah pembeli pertama yang memberikan ulasan!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Rekomendasi Produk Sejenis -->
    <?php if (!empty($related)): ?>
    <div class="my-5">
        <h4 class="fw-extrabold mb-4">Produk Perkebunan Terkait</h4>
        <div class="row g-3">
            <?php foreach ($related as $rel): ?>
                <?= view('components/card_produk', ['product' => $rel]) ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function incQty(max) {
    const el = document.getElementById('product-qty');
    let val = parseInt(el.value) || 1;
    if (val < max) el.value = val + 1;
}
function decQty() {
    const el = document.getElementById('product-qty');
    let val = parseInt(el.value) || 1;
    if (val > 1) el.value = val - 1;
}
</script>

<?= view('layout/footer') ?>
