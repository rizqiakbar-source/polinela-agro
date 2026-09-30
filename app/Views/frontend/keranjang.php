<?= view('layout/header', ['title' => 'Keranjang Belanja - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item active">Keranjang Belanja</li>
        </ol>
    </nav>

    <h3 class="fw-extrabold mb-4"><i class="bi bi-cart3 text-success me-2"></i> Keranjang Belanja Anda</h3>

    <?php if (!empty($items)): ?>
    <?php
    $groupedItems = [];
    foreach ($items as $item) {
        $unitName = $item['nama_unit'] ?? 'Unit Usaha Polinela';
        $groupedItems[$unitName][] = $item;
    }
    ?>
    <div class="row g-4">
        <!-- List Produk di Keranjang Terkelompok per Unit Toko -->
        <div class="col-lg-8">
            <?php foreach ($groupedItems as $unitName => $unitProducts): ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-success bg-opacity-10 border-0 py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shop text-success fs-5"></i>
                        <strong class="text-dark"><?= esc($unitName) ?></strong>
                    </div>
                    <span class="badge bg-white text-success border border-success-subtle rounded-pill px-3 py-1 small">
                        <?= count($unitProducts) ?> Produk
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="small text-muted text-uppercase" style="font-size: 0.72rem;">
                                <th class="ps-4">Produk</th>
                                <th>Harga Satuan</th>
                                <th class="text-center">Kuantitas</th>
                                <th>Subtotal</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unitProducts as $item): ?>
                            <tr id="cart-row-<?= $item['id'] ?>">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= product_image_url($item['gambar_utama'] ?? '') ?>" 
                                             alt="<?= esc($item['nama_produk']) ?>" 
                                             class="rounded-3 border" 
                                             style="width: 60px; height: 60px; object-fit: cover;"
                                             onerror="this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=200&auto=format&fit=crop&q=80';">
                                        <div>
                                            <a href="<?= base_url('produk/' . $item['slug']) ?>" class="fw-bold text-dark text-decoration-none small d-block mb-1">
                                                <?= esc($item['nama_produk']) ?>
                                            </a>
                                            <span class="text-muted small" style="font-size: 0.75rem;"><i class="bi bi-box me-1"></i>Berat: <?= $item['berat_gram'] ?>g</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark"><?= format_rupiah($item['harga']) ?></div>
                                    <small class="text-muted" style="font-size: 0.72rem;">/ <?= esc($item['satuan']) ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center border rounded-3 bg-light p-1 mx-auto" style="width: 110px;">
                                        <button type="button" class="btn btn-sm btn-light border-0 fw-bold px-2 py-0" onclick="updateQty(<?= $item['id'] ?>, -1)">-</button>
                                        <input type="text" id="qty-input-<?= $item['id'] ?>" class="form-control form-control-sm text-center border-0 bg-transparent fw-bold p-0" value="<?= $item['qty'] ?>" readonly style="width: 40px;">
                                        <button type="button" class="btn btn-sm btn-light border-0 fw-bold px-2 py-0" onclick="updateQty(<?= $item['id'] ?>, 1)">+</button>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-success small" id="subtotal-<?= $item['id'] ?>">
                                        <?= format_rupiah($item['harga'] * $item['qty']) ?>
                                    </strong>
                                </td>
                                <td class="text-center pe-4">
                                    <a href="<?= base_url('keranjang/delete/' . $item['id']) ?>" class="btn btn-sm btn-light text-danger rounded-circle p-2" title="Hapus dari keranjang">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
            
            <a href="<?= base_url('katalog') ?>" class="text-success fw-bold text-decoration-none small">
                <i class="bi bi-arrow-left me-1"></i> Lanjut Belanja Produk Lainnya
            </a>
        </div>

        <!-- Ringkasan Belanja Kanan -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 100px;">
                <h5 class="fw-bold mb-3">Ringkasan Pesanan</h5>
                
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>Total Estimasi Berat</span>
                    <strong class="text-dark"><?= number_format($total_berat / 1000, 2) ?> kg</strong>
                </div>

                <div class="d-flex justify-content-between mb-3 small text-muted">
                    <span>Subtotal Produk</span>
                    <strong class="text-dark fs-6" id="cart-total-subtotal"><?= format_rupiah($subtotal) ?></strong>
                </div>

                <div class="p-3 bg-light rounded-3 mb-4 small text-muted">
                    <i class="bi bi-info-circle text-primary me-1"></i> Ongkos kirim dan kupon diskon kampus akan dihitung secara otomatis pada halaman Checkout.
                </div>

                <div class="d-grid">
                    <a href="<?= base_url('checkout') ?>" class="btn btn-agro py-2 fs-6">
                        <i class="bi bi-credit-card me-2"></i> Lanjut ke Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Empty State -->
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
        <div class="mb-3 text-muted">
            <i class="bi bi-cart-x display-1"></i>
        </div>
        <h4 class="fw-bold">Keranjang Belanja Anda Kosong</h4>
        <p class="text-muted small mb-4">Anda belum memasukkan produk perkebunan ke dalam keranjang.</p>
        <div>
            <a href="<?= base_url('katalog') ?>" class="btn btn-agro px-4 py-2">
                <i class="bi bi-bag-plus me-1"></i> Jelajahi Katalog Produk
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function updateQty(cartId, change) {
    const input = document.getElementById('qty-input-' + cartId);
    let cur = parseInt(input.value) || 1;
    let next = cur + change;
    if (next < 1) return;

    const formData = new FormData();
    formData.append('cart_id', cartId);
    formData.append('qty', next);

    fetch(window.BASE_URL + 'keranjang/update', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = next;
            document.getElementById('subtotal-' + cartId).innerText = data.item_subtotal;
            document.getElementById('cart-total-subtotal').innerText = data.total_subtotal;
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: data.message,
                confirmButtonColor: '#15803d'
            });
        }
    })
    .catch(err => console.error(err));
}
</script>

<?= view('layout/footer') ?>
