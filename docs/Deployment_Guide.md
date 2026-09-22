# 🚀 Panduan Deployment — Polinela Agro Digital

Dokumen ini menjelaskan langkah-langkah deployment aplikasi Polinela Agro Digital ke berbagai lingkungan produksi.

---

## 📋 Prasyarat Umum

| Komponen       | Minimum               | Direkomendasikan       |
|----------------|------------------------|------------------------|
| PHP            | 8.2                    | 8.2+                   |
| MySQL/MariaDB  | 5.7 / 10.5            | 8.0 / 10.11           |
| Apache         | 2.4                    | 2.4 + mod_rewrite      |
| RAM Server     | 512 MB                 | 1 GB+                  |
| Disk Space     | 200 MB                 | 500 MB+                |
| Composer       | 2.x                    | 2.x                    |

### Ekstensi PHP Wajib
```
curl, intl, mbstring, mysqli, pdo_mysql, json, xml, zip, fileinfo, gd
```

---

## 1️⃣ Deployment via XAMPP (Windows)

### Langkah Instalasi

```bash
# 1. Salin folder proyek ke htdocs
xcopy /E /I polinela-agro C:\xampp\htdocs\polinela-agro

# 2. Konfigurasi .env
copy .env.example .env
# Edit database credentials sesuai konfigurasi XAMPP

# 3. Install dependensi
cd C:\xampp\htdocs\polinela-agro
composer install --no-dev --optimize-autoloader

# 4. Buat database
# Akses phpMyAdmin di http://localhost/phpmyadmin
# Buat database: polinela_agro (utf8mb4_unicode_ci)

# 5. Jalankan migrasi & seeder
php spark migrate
php spark db:seed DatabaseSeeder

# 6. Set permissions
# Pastikan folder writable/ dapat ditulis oleh Apache
```

### Konfigurasi Apache (httpd.conf)
Pastikan `mod_rewrite` aktif:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

### Akses Aplikasi
```
http://localhost/polinela-agro/public/
```

---

## 2️⃣ Deployment via cPanel (Shared Hosting)

### Langkah Instalasi

1. **Upload File**
   - Compress folder proyek menjadi `.zip`
   - Upload ke `public_html/polinela-agro/` melalui File Manager
   - Extract file

2. **Konfigurasi Database**
   - Buat database MySQL via cPanel → MySQL Databases
   - Buat user dan assign ke database
   - Import SQL dump atau jalankan migrasi via SSH

3. **Konfigurasi .env**
   ```ini
   CI_ENVIRONMENT = production
   app.baseURL = 'https://yourdomain.com/'
   database.default.hostname = localhost
   database.default.database = cpanel_polinela_agro
   database.default.username = cpanel_user
   database.default.password = your_password
   ```

4. **Point Domain ke /public**
   - Jika domain utama: pindahkan isi `public/` ke `public_html/`
   - Atau gunakan subdomain/addon domain yang mengarah ke folder `public/`

5. **Set Permissions**
   ```bash
   chmod -R 755 writable/
   chmod -R 644 .env
   ```

6. **Jalankan Migrasi** (via SSH atau Terminal cPanel)
   ```bash
   php spark migrate
   php spark db:seed DatabaseSeeder
   ```

---

## 3️⃣ Deployment via VPS (Ubuntu + Nginx)

### Instalasi Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install php8.2 php8.2-fpm php8.2-cli php8.2-curl php8.2-intl \
    php8.2-mbstring php8.2-mysql php8.2-xml php8.2-zip php8.2-gd -y

# Install MariaDB
sudo apt install mariadb-server -y
sudo mysql_secure_installation

# Install Nginx
sudo apt install nginx -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Konfigurasi Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/polinela-agro/public;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/polinela-agro.access.log;
    error_log  /var/log/nginx/polinela-agro.error.log;

    # Rewrite rules (CodeIgniter 4)
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny hidden files
    location ~ /\. {
        deny all;
    }

    # Cache static assets
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff2|ttf)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### Deploy Aplikasi

```bash
# Clone repository
cd /var/www/
git clone https://github.com/polinela/agro-digital.git polinela-agro
cd polinela-agro

# Install dependensi
composer install --no-dev --optimize-autoloader

# Konfigurasi
cp .env.example .env
# Edit .env dengan credentials production

# Setup database
mysql -u root -p -e "CREATE DATABASE polinela_agro CHARACTER SET utf8mb4;"
php spark migrate
php spark db:seed DatabaseSeeder

# Set permissions
sudo chown -R www-data:www-data /var/www/polinela-agro/writable
sudo chmod -R 775 /var/www/polinela-agro/writable

# Restart Nginx
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

---

## 4️⃣ Deployment via Docker

```bash
# Build & jalankan
docker-compose up -d --build

# Jalankan migrasi di dalam container
docker exec -it polinela-agro-app php spark migrate
docker exec -it polinela-agro-app php spark db:seed DatabaseSeeder
```

### Port Default
| Service     | Port  | URL                        |
|-------------|-------|----------------------------|
| Aplikasi    | 8080  | http://localhost:8080/      |
| MariaDB     | 3307  | localhost:3307              |
| phpMyAdmin  | 8081  | http://localhost:8081/      |

---

## 🔒 Checklist Keamanan Produksi

- [ ] Set `CI_ENVIRONMENT = production` di `.env`
- [ ] Nonaktifkan debug toolbar (`app.forceGlobalSecureRequests = true`)
- [ ] Pastikan `.env` tidak dapat diakses publik
- [ ] Aktifkan HTTPS (Let's Encrypt / Cloudflare)
- [ ] Set `logger.threshold = 1` (hanya error critical)
- [ ] Gunakan password database yang kuat
- [ ] Aktifkan CSRF protection (sudah default di CI4)
- [ ] Backup database secara berkala
- [ ] Monitor log error di `writable/logs/`

---

## 🔄 Update & Maintenance

```bash
# Pull perubahan terbaru
git pull origin main

# Update dependensi
composer install --no-dev --optimize-autoloader

# Jalankan migrasi baru
php spark migrate

# Clear cache
php spark cache:clear
```

---

> 📌 **Catatan**: Untuk bantuan deployment, hubungi tim teknis TEFA Polinela melalui email atau WhatsApp yang tercantum di halaman Kontak aplikasi.
