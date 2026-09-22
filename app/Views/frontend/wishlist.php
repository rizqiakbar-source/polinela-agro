<?= view('layout/header', ['title' => 'Wishlist Produk Favorit - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item active">Wishlist Favorit</li>
        </ol>
    </nav>

    <h3 class="fw-extrabold mb-4"><i class="bi bi-heart-fill text-danger me-2"></i> Wishlist Produk Favorit Anda</h3>

    <?php if (!empty($items)): ?>
        <div class="row g-3">
            <?php foreach ($items as $product): ?>
                <?= view('components/card_produk', ['product' => $product]) ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
            <i class="bi bi-heartbreak display-2 text-muted mb-3"></i>
            <h4 class="fw-bold">Wishlist Anda Masih Kosong</h4>
            <p class="text-muted small mb-4">Simpan produk perkebunan yang Anda sukai dengan menekan ikon hati pada katalog produk.</p>
            <div>
                <a href="<?= base_url('katalog') ?>" class="btn btn-agro px-4 py-2">Eksplorasi Katalog Produk</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= view('layout/footer') ?>
