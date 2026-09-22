<?= view('layout/header', ['title' => 'Evaluasi System Usability Scale (SUS) - Polinela Agro Digital']) ?>
<?= view('layout/navbar_frontend') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Header Kartu -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fs-3" style="width: 55px; height: 55px;">
                        <i class="bi bi-patch-question-fill"></i>
                    </div>
                    <div>
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-1">Metode Penelitian Vokasi</span>
                        <h3 class="fw-extrabold mb-0 text-dark">Kuesioner Evaluasi System Usability Scale (SUS)</h3>
                    </div>
                </div>
                <p class="text-muted small leading-relaxed mb-0">
                    Kuesioner ini dirancang untuk mengukur tingkat kepuasan, efektivitas, dan kemudahan antarmuka aplikasi <strong>Polinela Agro Digital</strong>. Berikan penilaian Anda secara objektif berdasarkan pengalaman Anda menggunakan aplikasi ini (Skala 1 = Sangat Tidak Setuju s/d 5 = Sangat Setuju).
                </p>
            </div>

            <?php if (!empty($has_surveyed) && !empty($my_score)): ?>
            <!-- Jika Sudah Mengisi -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center mb-4 border-top border-4 border-success">
                <div class="rounded-circle bg-success-subtle text-success p-3 mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; font-size: 2.2rem;">
                    <i class="bi bi-award-fill"></i>
                </div>
                <h4 class="fw-bold mb-1">Terima Kasih Atas Penilaian Anda!</h4>
                <p class="text-muted small mb-4">Anda telah mengisi instrumen evaluasi kepuasan sistem ini.</p>

                <div class="p-4 bg-light rounded-4 max-w-500 mx-auto d-inline-block border">
                    <div class="small text-muted mb-1">Skor SUS Anda:</div>
                    <div class="display-4 fw-extrabold text-success mb-2"><?= $my_score['total_score'] ?> / 100</div>
                    <div>
                        <span class="badge bg-success fs-6 px-3 py-2 rounded-pill">
                            Predikat: <?= esc($my_score['kategori']) ?>
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="<?= base_url('katalog') ?>" class="btn btn-agro px-4 py-2">Kembali ke Katalog Belanja</a>
                </div>
            </div>
            <?php else: ?>

            <!-- Formulir Kuesioner SUS 10 Pertanyaan -->
            <form action="<?= base_url('sus/submit') ?>" method="post" id="susForm">
                <?= csrf_field() ?>

                <!-- Data Responden -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-3 small text-muted text-uppercase">Identitas Responden</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Responden:</label>
                            <input type="text" name="nama_responden" class="form-control" value="<?= esc($user['nama'] ?? '') ?>" placeholder="Nama lengkap Anda..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kategori Pengguna / Civitas:</label>
                            <select name="role_responden" class="form-select">
                                <option value="Mahasiswa Polinela">Mahasiswa Polinela</option>
                                <option value="Dosen / Tenaga Kependidikan">Dosen / Tenaga Kependidikan</option>
                                <option value="Pengelola Unit Usaha Tefa">Pengelola Unit Usaha Tefa</option>
                                <option value="Masyarakat Umum">Masyarakat Umum</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 10 Butir Instrumen SUS -->
                <?php
                $susQuestions = [
                    1 => ['Saya ingin sering menggunakan sistem Polinela Agro Digital ini.', 'ganjil'],
                    2 => ['Saya merasa sistem e-commerce ini rumit untuk digunakan.', 'genap'],
                    3 => ['Saya merasa sistem ini mudah untuk digunakan saat mencari dan membeli produk.', 'ganjil'],
                    4 => ['Saya membutuhkan bantuan dari orang teknis untuk dapat menggunakan sistem ini.', 'genap'],
                    5 => ['Saya merasa fitur-fitur (katalog, keranjang, checkout, verifikasi) berfungsi dengan sangat baik.', 'ganjil'],
                    6 => ['Saya merasa ada terlalu banyak ketidakkonsistenan pada sistem ini.', 'genap'],
                    7 => ['Saya rasa kebanyakan orang akan cepat belajar menggunakan sistem ini.', 'ganjil'],
                    8 => ['Saya merasa sistem ini sangat membingungkan saat digunakan.', 'genap'],
                    9 => ['Saya merasa percaya diri dan nyaman saat bertransaksi di sistem ini.', 'ganjil'],
                    10 => ['Saya perlu belajar banyak hal terlebih dahulu sebelum dapat menggunakan sistem ini.', 'genap'],
                ];
                ?>

                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <h5 class="fw-bold mb-0">Instrumen Penilaian Usability</h5>
                        <small class="text-muted">Skala 1 (Sangat Tidak Setuju) s/d 5 (Sangat Setuju)</small>
                    </div>

                    <div class="d-flex flex-column gap-4">
                        <?php foreach ($susQuestions as $num => $q): ?>
                        <div class="p-3 rounded-3 border bg-light">
                            <div class="fw-bold text-dark mb-3">
                                <span class="badge bg-success rounded-circle me-2"><?= $num ?></span> <?= esc($q[0]) ?>
                            </div>

                            <div class="row g-2 text-center">
                                <?php
                                $labels = [
                                    1 => '1 - Sangat Tidak Setuju',
                                    2 => '2 - Tidak Setuju',
                                    3 => '3 - Netral',
                                    4 => '4 - Setuju',
                                    5 => '5 - Sangat Setuju'
                                ];
                                foreach ($labels as $val => $lbl):
                                ?>
                                <div class="col">
                                    <label class="p-2 border rounded-3 bg-white w-100 d-block cursor-pointer sus-radio-card">
                                        <input type="radio" name="q<?= $num ?>" value="<?= $val ?>" class="form-check-input mb-1 sus-q" data-q="<?= $num ?>" data-type="<?= $q[1] ?>" required>
                                        <div class="small fw-semibold text-dark" style="font-size: 0.72rem;"><?= $lbl ?></div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Feedback Kualitatif -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-2">Kritik & Saran Tambahan (Opsional)</h5>
                    <p class="small text-muted mb-3">Berikan masukan kualitatif untuk peningkatan antarmuka dan layanan platform ke depan.</p>
                    <textarea name="feedback" class="form-control" rows="3" placeholder="Saran untuk tampilan katalog, alur pembayaran, atau fitur baru..."></textarea>
                </div>

                <!-- Tombol Submit & Live Preview -->
                <div class="p-4 bg-white rounded-4 border shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div>
                        <div class="small text-muted">Pastikan seluruh 10 pertanyaan telah dijawab dengan teliti.</div>
                    </div>
                    <button type="submit" class="btn btn-agro btn-lg px-5 fs-6">
                        <i class="bi bi-send-fill me-2"></i> Kirim Evaluasi SUS
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
