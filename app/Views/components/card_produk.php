<div class="col-6 col-md-4 col-lg-3 mb-4">
    <div class="product-card">
        <div class="product-img-wrap">
            <span class="product-badge-unit">
                <i class="bi bi-geo-alt-fill me-1"></i><?= esc($product['nama_unit'] ?? 'Polinela') ?>
            </span>
            <button type="button" class="product-wishlist-btn" data-product-id="<?= $product['id'] ?>" title="Simpan ke Wishlist">
                <i class="bi bi-heart"></i>
            </button>
            <a href="<?= base_url('produk/' . $product['slug']) ?>">
                <img src="<?= product_image_url($product['gambar_utama'] ?? '') ?>" 
                     alt="<?= esc($product['nama_produk']) ?>" 
                     class="product-img"
                     loading="lazy"
                     onerror="this.onerror=null; this.src='https://placehold.co/600x600/1b5e20/ffffff?text=Polinela+Agro';">
            </a>
        </div>
        <div class="product-body">
            <div class="product-category"><?= esc($product['nama_kategori'] ?? 'Perkebunan') ?></div>
            <a href="<?= base_url('produk/' . $product['slug']) ?>" class="text-decoration-none">
                <h6 class="product-title" title="<?= esc($product['nama_produk']) ?>"><?= esc($product['nama_produk']) ?></h6>
            </a>
            
            <div class="d-flex align-items-center gap-1 mb-2">
                <div class="text-warning small">
                    <i class="bi bi-star-fill"></i>
                </div>
                <span class="small fw-bold text-dark"><?= number_format($product['rating_avg'] ?? 5.0, 1) ?></span>
                <span class="text-muted small ms-1">(<?= $product['total_terjual'] ?? 0 ?> terjual)</span>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
                <div>
                    <div class="product-price"><?= format_rupiah($product['harga']) ?></div>
                    <div class="text-muted small" style="font-size: 0.75rem;">per <?= esc($product['satuan']) ?></div>
                </div>
                <button type="button" class="btn btn-agro btn-sm px-3 rounded-pill btn-add-cart" data-product-id="<?= $product['id'] ?>" title="Tambah ke Keranjang">
                    <i class="bi bi-plus-lg me-1"></i> Beli
                </button>
            </div>
        </div>
    </div>
</div>
