<?php
$user = current_user();
$cartCount = 0;
if ($user) {
    $cartModel = new \App\Models\CartModel();
    $cartCount = $cartModel->where('user_id', $user['id'])->countAllResults();
}
?>
<!-- Top Bar Kampus -->
<div class="bg-success text-white py-1 small d-none d-md-block" style="background-color: #14532d !important;">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-geo-alt me-1"></i> Politeknik Negeri Lampung - Teaching Factory & Unit Usaha Perkebunan
        </div>
        <div class="d-flex gap-3 align-items-center">
            <span><i class="bi bi-whatsapp me-1"></i> 0812-3456-7890</span>
            <span><i class="bi bi-envelope me-1"></i> agro@polinela.ac.id</span>
            <a href="<?= base_url('sus') ?>" class="badge bg-warning text-dark text-decoration-none px-2 py-1">
                <i class="bi bi-star-fill me-1"></i> Evaluasi Usability (SUS)
            </a>
        </div>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-agro sticky-top">
    <div class="container">
        <!-- Logo & Branding -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= base_url('/') ?>">
            <div class="brand-badge">
                <i class="bi bi-tree"></i>
            </div>
            <div>
                <div class="brand-title">POLINELA AGRO</div>
                <div class="brand-sub">Pasar Digital Produk Kampus</div>
            </div>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAgroContent">
            <i class="bi bi-list fs-2 text-success"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarAgroContent">
            <!-- Search Bar Tengah -->
            <div class="mx-auto my-2 my-lg-0 col-12 col-lg-5 position-relative">
                <div class="header-search-wrap">
                    <i class="bi bi-search header-search-icon"></i>
                    <input type="text" id="navbar-search-input" class="form-control header-search-input" placeholder="Cari kopi robusta, kakao, lada hitam..." autocomplete="off">
                </div>
                <!-- Live Search Dropdown -->
                <div id="navbar-search-dropdown" class="position-absolute w-100 bg-white shadow-lg rounded-3 mt-1 d-none" style="z-index: 1050; max-height: 380px; overflow-y: auto; border: 1px solid #e2e8f0;"></div>
            </div>

            <!-- Nav Links & User Action -->
            <ul class="navbar-nav ms-auto align-items-center gap-2 gap-lg-3">
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-dark" href="<?= base_url('/') ?>">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-dark" href="<?= base_url('katalog') ?>">Katalog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-dark" href="<?= base_url('lacak') ?>">Lacak Resi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold text-dark" href="<?= base_url('tentang') ?>">Tentang</a>
                </li>

                <!-- Keranjang Icon -->
                <li class="nav-item">
                    <a href="<?= base_url('keranjang') ?>" class="btn btn-light rounded-circle position-relative p-2" title="Keranjang Belanja">
                        <i class="bi bi-bag fs-5 text-success"></i>
                        <span class="badge-count cart-badge <?= ($cartCount > 0) ? '' : 'd-none' ?>"><?= $cartCount ?></span>
                    </a>
                </li>

                <!-- Wishlist Icon -->
                <?php if ($user): ?>
                <li class="nav-item d-none d-lg-block">
                    <a href="<?= base_url('wishlist') ?>" class="btn btn-light rounded-circle p-2" title="Wishlist">
                        <i class="bi bi-heart fs-5 text-danger"></i>
                    </a>
                </li>
                <?php endif; ?>

                <!-- User Dropdown / Auth Buttons -->
                <?php if ($user): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 p-1" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.9rem;">
                            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                        </div>
                        <span class="d-none d-xl-inline fw-semibold text-dark"><?= esc(explode(' ', $user['nama'])[0]) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2 p-2" style="min-width: 220px;">
                        <li class="p-2 border-bottom mb-2">
                            <div class="fw-bold text-dark"><?= esc($user['nama']) ?></div>
                            <div class="small text-muted"><?= esc($user['email']) ?></div>
                            <span class="badge bg-success-subtle text-success mt-1"><?= ucfirst(str_replace('_', ' ', $user['role'])) ?></span>
                        </li>
                        <?php if (in_array($user['role'], ['superadmin', 'admin_unit', 'pimpinan'])): ?>
                        <li>
                            <a class="dropdown-item py-2 fw-semibold text-success" href="<?= base_url('admin/dashboard') ?>">
                                <i class="bi bi-speedometer2 me-2"></i> Panel Manajemen
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('pesanan') ?>">
                                <i class="bi bi-receipt me-2 text-primary"></i> Riwayat Pesanan
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('wishlist') ?>">
                                <i class="bi bi-heart me-2 text-danger"></i> Wishlist Favorit
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('profil') ?>">
                                <i class="bi bi-person me-2 text-secondary"></i> Profil Saya
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('notifikasi') ?>">
                                <i class="bi bi-bell me-2 text-warning"></i> Notifikasi
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger fw-semibold" href="<?= base_url('logout') ?>">
                                <i class="bi bi-box-arrow-right me-2"></i> Keluar
                            </a>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item d-flex gap-2 ms-lg-2">
                    <a href="<?= base_url('login') ?>" class="btn btn-agro-outline btn-sm px-3">Masuk</a>
                    <a href="<?= base_url('register') ?>" class="btn btn-agro btn-sm px-3">Daftar</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Alert Messages Global -->
<div class="container mt-3">
    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2 fs-5 align-middle"></i>
        <?= session()->getFlashdata('warning') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
</div>
