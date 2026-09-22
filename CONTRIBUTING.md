# Panduan Kontribusi — Polinela Agro Digital

Terima kasih atas minat Anda untuk berkontribusi pada proyek **Polinela Agro Digital**! Dokumen ini menjelaskan panduan dan standar yang berlaku bagi kontributor.

---

## 🚀 Cara Berkontribusi

### 1. Fork & Clone
```bash
git clone https://github.com/your-username/polinela-agro.git
cd polinela-agro
composer install
cp .env.example .env
```

### 2. Buat Branch Baru
```bash
git checkout -b fitur/nama-fitur
# atau
git checkout -b perbaikan/deskripsi-bug
```

### 3. Lakukan Perubahan
- Pastikan kode mengikuti standar PSR-12
- Tambahkan unit/feature test untuk fitur baru
- Pastikan semua test lulus sebelum commit

### 4. Commit & Push
```bash
git add .
git commit -m "feat: deskripsi singkat perubahan"
git push origin fitur/nama-fitur
```

### 5. Buat Pull Request
- Buka halaman repository di GitHub
- Klik **"New Pull Request"**
- Jelaskan perubahan yang dilakukan secara detail

---

## 📏 Standar Kode

### PHP
- Mengikuti standar **PSR-12** (PHP Standard Recommendations)
- Menggunakan **type hints** pada parameter dan return value
- Setiap class dan method publik wajib memiliki **PHPDoc**
- Nama variabel dan method menggunakan **camelCase**
- Nama class menggunakan **PascalCase**

### Database
- Nama tabel menggunakan **snake_case** dan bentuk jamak (contoh: `order_details`)
- Migrasi harus memiliki method `up()` dan `down()` yang lengkap
- Gunakan **foreign keys** dan indexing yang tepat

### Views
- Template menggunakan PHP native (CodeIgniter 4 standard)
- Komponen reusable ditempatkan di `app/Views/components/`
- Layout menggunakan section/yield pattern

### JavaScript
- Menggunakan ES6+ syntax
- Nama file menggunakan **kebab-case** atau **camelCase**
- Hindari inline JavaScript; gunakan event listener

---

## 🧪 Pengujian

Sebelum mengajukan Pull Request, pastikan:

```bash
# Cek syntax PHP
Get-ChildItem -Path "app" -Recurse -Filter "*.php" | ForEach-Object { php -l $_.FullName }

# Jalankan PHPUnit
php vendor/bin/phpunit

# Pastikan tidak ada error
php spark routes
```

---

## 📝 Konvensi Commit

Gunakan format **Conventional Commits**:

| Prefix     | Penggunaan                              |
|------------|------------------------------------------|
| `feat:`    | Fitur baru                               |
| `fix:`     | Perbaikan bug                            |
| `docs:`    | Perubahan dokumentasi                    |
| `style:`   | Perubahan formatting (bukan logic kode)  |
| `refactor:`| Refactoring kode tanpa perubahan fitur   |
| `test:`    | Penambahan atau perbaikan test           |
| `chore:`   | Perubahan konfigurasi, build, dll        |

---

## 🐛 Melaporkan Bug

1. Buka tab **Issues** di repository
2. Gunakan template **Bug Report**
3. Sertakan:
   - Langkah reproduksi
   - Perilaku yang diharapkan vs aktual
   - Screenshot (jika ada)
   - Versi PHP, browser, dan OS

---

## 📜 Lisensi

Dengan berkontribusi, Anda setuju bahwa kontribusi Anda akan dilisensikan di bawah [MIT License](LICENSE).
