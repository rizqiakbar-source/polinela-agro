<?= view('layout/header', ['title' => 'Tentang Kami - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-5">
    <div class="text-center max-w-750 mx-auto mb-5" style="max-width: 750px;">
        <span class="badge bg-success-subtle text-success fw-bold px-3 py-1 rounded-pill mb-2">Transformasi Digital Kampus Vokasi</span>
        <h2 class="display-6 fw-extrabold text-dark mb-3">Tentang Polinela Agro Digital</h2>
        <p class="text-muted lead fs-6">
            Platform pasar digital inovatif yang mengintegrasikan hasil praktikum, riset terapan, dan unit Teaching Factory (Tefa) perkebunan Politeknik Negeri Lampung.
        </p>
    </div>

    <!-- Visi & Misi Card -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white h-100 border-top border-4 border-success">
                <div class="rounded-circle bg-success-subtle text-success p-3 mb-3 d-inline-flex fs-4">
                    <i class="bi bi-eye-fill"></i>
                </div>
                <h4 class="fw-bold mb-3">Visi Kami</h4>
                <p class="text-muted mb-0 leading-relaxed">
                    Menjadi platform e-commerce terdepan untuk komoditas dan produk olahan perkebunan kampus vokasi di Indonesia, yang berdaya saing tinggi, berkelanjutan, dan mendorong hilirisasi hasil riset terapan.
                </p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white h-100 border-top border-4 border-primary">
                <div class="rounded-circle bg-primary-subtle text-primary p-3 mb-3 d-inline-flex fs-4">
                    <i class="bi bi-bullseye"></i>
                </div>
                <h4 class="fw-bold mb-3">Misi Kami</h4>
                <ul class="text-muted ps-3 mb-0 d-flex flex-column gap-2">
                    <li>Mempermudah akses pemasaran produk perkebunan Polinela ke pasar nasional.</li>
                    <li>Menyediakan sistem transaksi digital yang aman, cepat, transparan, dan real-time.</li>
                    <li>Mendukung transformasi digital tata kelola unit usaha perguruan tinggi vokasi.</li>
                    <li>Menjadi sarana media pembelajaran komprehensif bagi mahasiswa Tefa Manajemen Informatika.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Daftar Unit Usaha Perkebunan -->
    <h4 class="fw-extrabold mb-4 text-center">Unit Usaha & Laboratorium Perkebunan Terpadu</h4>
    <div class="row g-4 mb-5">
        <?php foreach ($units as $unit): ?>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-center">
                <div class="rounded-circle bg-success-subtle text-success mx-auto mb-3 d-flex align-items-center justify-content-center fs-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-building-check"></i>
                </div>
                <h6 class="fw-bold mb-1 text-dark"><?= esc($unit['nama_unit']) ?></h6>
                <p class="small text-muted mb-3"><?= esc($unit['deskripsi']) ?></p>
                <div class="mt-auto pt-3 border-top small text-muted text-start">
                    <div><strong>Penanggung Jawab:</strong> <?= esc($unit['pj_nama'] ?: 'Dosen Ahli Tefa') ?></div>
                    <div class="mt-1"><strong>Lokasi:</strong> <?= esc($unit['lokasi'] ?: 'Kebun Kampus Polinela') ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?= view('layout/footer') ?>
