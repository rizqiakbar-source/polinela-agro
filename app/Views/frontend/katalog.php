<?= view('layout/header', ['title' => 'Katalog Produk Perkebunan - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-4">
    <!-- Breadcrumb & Title -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= base_url('/') ?>" class="text-success">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Katalog Produk Perkebunan</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Filter (Desktop) -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-funnel text-success me-2"></i> Filter Produk</h5>
                    <a href="<?= base_url('katalog') ?>" class="small text-danger text-decoration-none">Reset</a>
                </div>

                <form action="<?= base_url('katalog') ?>" method="get">
                    <!-- Search Input -->
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Kata Kunci</label>
                        <div class="input-group">
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Nama produk..." value="<?= esc($filter['search'] ?? '') ?>">
                            <button class="btn btn-outline-success btn-sm" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </div>

                    <!-- Kategori -->
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Kategori Komoditas</label>
                        <div class="d-flex flex-column gap-1">
                            <a href="<?= base_url('katalog' . (!empty($filter['search']) ? '?q=' . $filter['search'] : '')) ?>" 
                               class="text-decoration-none small py-1 px-2 rounded-2 <?= empty($filter['category']) ? 'bg-success text-white fw-bold' : 'text-dark' ?>">
                                Semua Kategori
                            </a>
                            <?php foreach ($categories as $c): ?>
                            <a href="<?= base_url('katalog?kategori=' . $c['slug'] . (!empty($filter['unit_id']) ? '&unit=' . $filter['unit_id'] : '')) ?>" 
                               class="text-decoration-none small py-1 px-2 rounded-2 <?= (($filter['category'] ?? '') === $c['slug']) ? 'bg-success text-white fw-bold' : 'text-dark' ?>">
                                <?= esc($c['nama_kategori']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Unit Usaha -->
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Unit Usaha Kampus</label>
                        <select name="unit" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Unit Usaha</option>
                            <?php foreach ($units as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (($filter['unit_id'] ?? '') == $u['id']) ? 'selected' : '' ?>>
                                <?= esc($u['nama_unit']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Rentang Harga -->
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Rentang Harga (Rp)</label>
                        <div class="d-flex gap-2">
                            <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" value="<?= esc($filter['min_price'] ?? '') ?>">
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Maks" value="<?= esc($filter['max_price'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-agro btn-sm w-100 mt-2">Terapkan Harga</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Product Grid -->
        <div class="col-lg-9">
            <!-- Top Controls (Sorting & Counter) -->
            <div class="bg-white p-3 rounded-4 border shadow-sm d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <span class="text-muted small">Menampilkan <strong><?= count($products) ?></strong> produk perkebunan</span>
                    <?php if (!empty($filter['search'])): ?>
                    <span class="badge bg-light text-dark ms-2">Kata kunci: "<?= esc($filter['search']) ?>"</span>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted text-nowrap fw-semibold">Urutkan:</label>
                    <select class="form-select form-select-sm" style="width: 170px;" onchange="location = this.value;">
                        <option value="<?= base_url('katalog?' . http_build_query(array_merge($filter, ['sort' => 'latest']))) ?>" <?= ($filter['sort'] === 'latest') ? 'selected' : '' ?>>Terbaru</option>
                        <option value="<?= base_url('katalog?' . http_build_query(array_merge($filter, ['sort' => 'popular']))) ?>" <?= ($filter['sort'] === 'popular') ? 'selected' : '' ?>>Paling Laris</option>
                        <option value="<?= base_url('katalog?' . http_build_query(array_merge($filter, ['sort' => 'price_low']))) ?>" <?= ($filter['sort'] === 'price_low') ? 'selected' : '' ?>>Harga: Rendah ke Tinggi</option>
                        <option value="<?= base_url('katalog?' . http_build_query(array_merge($filter, ['sort' => 'price_high']))) ?>" <?= ($filter['sort'] === 'price_high') ? 'selected' : '' ?>>Harga: Tinggi ke Rendah</option>
                        <option value="<?= base_url('katalog?' . http_build_query(array_merge($filter, ['sort' => 'rating']))) ?>" <?= ($filter['sort'] === 'rating') ? 'selected' : '' ?>>Rating Tertinggi</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="row g-3">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <?= view('components/card_produk', ['product' => $product]) ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 py-5 text-center bg-white rounded-4 border shadow-sm">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                        <h5 class="fw-bold">Tidak ada produk ditemukan</h5>
                        <p class="text-muted small mb-3">Coba ubah kata kunci atau bersihkan filter yang aktif.</p>
                        <a href="<?= base_url('katalog') ?>" class="btn btn-agro btn-sm px-4">Lihat Semua Produk</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
