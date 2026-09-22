<?php
$role   = session()->get('user_role');
$unitId = session()->get('user_unit_id');
$currentUri = uri_string();

// Hitung order pending verifikasi
$db = \Config\Database::connect();
$pendingPaymentCount = $db->table('payments')->where('status', 'pending')->countAllResults();
$pendingOrderCount   = $db->table('orders')->whereIn('status', ['pending', 'menunggu_verifikasi'])->countAllResults();
?>
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-badge" style="width: 36px; height: 36px; font-size: 1.15rem;">
            <i class="bi bi-tree"></i>
        </div>
        <div>
            <div class="text-white fw-bold small text-uppercase tracking-wider">POLINELA AGRO</div>
            <div class="text-success small fw-semibold" style="font-size: 0.7rem;">PANEL KONTROL</div>
        </div>
    </div>

    <!-- User Profile Strip -->
    <div class="p-3 mx-3 my-2 rounded-3 bg-dark border border-secondary border-opacity-25 d-flex align-items-center gap-2">
        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
            <?= strtoupper(substr(session()->get('user_nama') ?? 'A', 0, 1)) ?>
        </div>
        <div class="overflow-hidden">
            <div class="text-white fw-bold small text-truncate"><?= esc(session()->get('user_nama')) ?></div>
            <span class="badge bg-success-subtle text-success small" style="font-size: 0.68rem;"><?= strtoupper(str_replace('_', ' ', $role)) ?></span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <ul class="sidebar-menu">
        <li class="sidebar-heading">Menu Utama</li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/dashboard') || $currentUri === 'admin' ? 'active' : '' ?>">
            <a href="<?= base_url('admin/dashboard') ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <?php if ($role !== 'pimpinan'): ?>
        <li class="sidebar-heading">Katalog & Stok</li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/produk') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/produk') ?>">
                <i class="bi bi-box-seam"></i>
                <span>Produk Perkebunan</span>
            </a>
        </li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/kategori') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/kategori') ?>">
                <i class="bi bi-tag"></i>
                <span>Kategori Produk</span>
            </a>
        </li>

        <li class="sidebar-heading">Transaksi</li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/pesanan') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/pesanan') ?>">
                <i class="bi bi-cart-check"></i>
                <span>Pesanan</span>
                <?php if ($pendingOrderCount > 0): ?>
                <span class="badge bg-warning text-dark ms-auto"><?= $pendingOrderCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/pembayaran') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/pembayaran') ?>">
                <i class="bi bi-credit-card"></i>
                <span>Verifikasi Bayar</span>
                <?php if ($pendingPaymentCount > 0): ?>
                <span class="badge bg-danger ms-auto"><?= $pendingPaymentCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/review') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/review') ?>">
                <i class="bi bi-chat-left-dots"></i>
                <span>Moderasi Ulasan</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="sidebar-heading">Laporan & Eksekutif</li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/laporan') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/laporan') ?>">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Laporan & Export</span>
            </a>
        </li>

        <?php if ($role === 'superadmin'): ?>
        <li class="sidebar-heading">Manajemen Kampus</li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/unit') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/unit') ?>">
                <i class="bi bi-buildings"></i>
                <span>Unit Usaha Polinela</span>
            </a>
        </li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/user') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/user') ?>">
                <i class="bi bi-people"></i>
                <span>Pengguna & Hak Akses</span>
            </a>
        </li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/banner') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/banner') ?>">
                <i class="bi bi-images"></i>
                <span>Banner Promosi</span>
            </a>
        </li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/voucher') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/voucher') ?>">
                <i class="bi bi-ticket-perforated"></i>
                <span>Voucher Diskon</span>
            </a>
        </li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/ongkir') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/ongkir') ?>">
                <i class="bi bi-truck"></i>
                <span>Tarif Pengiriman</span>
            </a>
        </li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/backup') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/backup') ?>">
                <i class="bi bi-database-down"></i>
                <span>Backup Basis Data</span>
            </a>
        </li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/log') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/log') ?>">
                <i class="bi bi-clock-history"></i>
                <span>Log Aktivitas</span>
            </a>
        </li>
        <li class="sidebar-item <?= str_contains($currentUri, 'admin/pengaturan') ? 'active' : '' ?>">
            <a href="<?= base_url('admin/pengaturan') ?>">
                <i class="bi bi-gear"></i>
                <span>Pengaturan Sistem</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="sidebar-heading">Akses Cepat</li>
        <li class="sidebar-item">
            <a href="<?= base_url('/') ?>" target="_blank">
                <i class="bi bi-box-arrow-up-right text-success"></i>
                <span>Buka Beranda Toko</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="<?= base_url('logout') ?>" class="text-danger">
                <i class="bi bi-box-arrow-right text-danger"></i>
                <span>Keluar Akun</span>
            </a>
        </li>
    </ul>
</aside>
