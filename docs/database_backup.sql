-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: polinela_agro
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `aksi` varchar(100) NOT NULL,
  `modul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,6,'Logout','Auth','User Budi Pratama (Civitas Polinela) logout dari sistem.','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0','2026-09-22 23:12:16');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banners` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(200) NOT NULL,
  `subjudul` varchar(255) DEFAULT NULL,
  `gambar` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `urutan` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
INSERT INTO `banners` VALUES (1,'Panen Raya Kopi Robusta & Kakao Polinela','Nikmati cita rasa kopi petik merah dan produk olahan kakao asli kebun riset kampus vokasi terbaik Lampung.','banner-kopi.jpg','katalog',1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(2,'Lada Hitam Lampung & Minyak Atsiri Alami','Rempah aromatik berkualitas ekspor dan ekstrak serai wangi hasil produksi Teaching Factory Polinela.','banner-rempah.jpg','katalog?kategori=rempah-atsiri',2,1,'2026-09-22 23:10:48','2026-09-22 23:10:48');
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_product_id` (`user_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Kopi Nusantara Polinela','kopi-nusantara','bi-cup-hot','Biji kopi sangrai, bubuk robusta, dan produk olahan kopi spesial dari kebun Politeknik Negeri Lampung.',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(2,'Biji & Olahan Kakao','biji-olahan-kakao','bi-box-seam','Kakao nibs organik, bubuk cokelat murni, serta cokelat artisan berkualitas hasil riset Tefa.',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(3,'Rempah & Minyak Atsiri','rempah-minyak-atsiri','bi-flower1','Lada hitam asli Lampung, minyak serai wangi murni, dan simplisia rempah perkebunan.',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(4,'Bibit & Pupuk Perkebunan','bibit-pupuk-perkebunan','bi-tree','Bibit tanaman perkebunan unggul bersertifikat serta pupuk organik kompos Tefa Polinela.',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(5,'Produk Pangan Olahan Tefa','produk-pangan-olahan','bi-basket','Produk makanan dan minuman sehat berbahan baku komoditas hasil perkebunan kampus.',1,'2026-09-22 23:10:48','2026-09-22 23:10:48');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (41,'2026-01-01-000001','App\\Database\\Migrations\\CreateUsersTable','default','App',1790093441,1),(42,'2026-01-01-000002','App\\Database\\Migrations\\CreateUnitsTable','default','App',1790093441,1),(43,'2026-01-01-000003','App\\Database\\Migrations\\CreatePermissionsTable','default','App',1790093442,1),(44,'2026-01-01-000004','App\\Database\\Migrations\\CreateCategoriesTable','default','App',1790093442,1),(45,'2026-01-01-000005','App\\Database\\Migrations\\CreateProductsTable','default','App',1790093442,1),(46,'2026-01-01-000006','App\\Database\\Migrations\\CreateProductImagesTable','default','App',1790093442,1),(47,'2026-01-01-000007','App\\Database\\Migrations\\CreateStocksTable','default','App',1790093442,1),(48,'2026-01-01-000008','App\\Database\\Migrations\\CreateCartsTable','default','App',1790093442,1),(49,'2026-01-01-000009','App\\Database\\Migrations\\CreateOrdersTable','default','App',1790093442,1),(50,'2026-01-01-000010','App\\Database\\Migrations\\CreateOrderDetailsTable','default','App',1790093442,1),(51,'2026-01-01-000011','App\\Database\\Migrations\\CreatePaymentsTable','default','App',1790093442,1),(52,'2026-01-01-000012','App\\Database\\Migrations\\CreateShippingsTable','default','App',1790093442,1),(53,'2026-01-01-000013','App\\Database\\Migrations\\CreateReviewsTable','default','App',1790093442,1),(54,'2026-01-01-000014','App\\Database\\Migrations\\CreateNotificationsTable','default','App',1790093442,1),(55,'2026-01-01-000015','App\\Database\\Migrations\\CreateActivityLogsTable','default','App',1790093442,1),(56,'2026-01-01-000016','App\\Database\\Migrations\\CreateWishlistsTable','default','App',1790093442,1),(57,'2026-01-01-000017','App\\Database\\Migrations\\CreateBannersTable','default','App',1790093442,1),(58,'2026-01-01-000018','App\\Database\\Migrations\\CreateVouchersTable','default','App',1790093442,1),(59,'2026-01-01-000019','App\\Database\\Migrations\\CreateSettingsTable','default','App',1790093442,1),(60,'2026-01-01-000020','App\\Database\\Migrations\\CreateSusResponsesTable','default','App',1790093442,1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `judul` varchar(200) NOT NULL,
  `pesan` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_details`
--

DROP TABLE IF EXISTS `order_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `nama_produk` varchar(200) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `berat` int(11) NOT NULL DEFAULT 500,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_details`
--

LOCK TABLES `order_details` WRITE;
/*!40000 ALTER TABLE `order_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `unit_id` int(11) unsigned DEFAULT NULL,
  `total_produk` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ongkir` decimal(12,2) NOT NULL DEFAULT 0.00,
  `diskon` decimal(12,2) NOT NULL DEFAULT 0.00,
  `kode_voucher` varchar(50) DEFAULT NULL,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','menunggu_pembayaran','menunggu_verifikasi','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_pembayaran',
  `catatan` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL,
  `metode` enum('transfer','cod','qris','va','ewallet','cc') NOT NULL DEFAULT 'transfer',
  `bank` varchar(50) DEFAULT NULL,
  `no_rekening_tujuan` varchar(100) DEFAULT NULL,
  `atas_nama` varchar(100) DEFAULT NULL,
  `no_transaksi` varchar(100) DEFAULT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `status` enum('pending','menunggu_konfirmasi','lunas','ditolak') NOT NULL DEFAULT 'pending',
  `verified_by` int(11) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  `module` varchar(100) NOT NULL,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_read` tinyint(1) NOT NULL DEFAULT 1,
  `can_update` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'superadmin','dashboard',1,1,1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(2,'superadmin','produk',1,1,1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(3,'superadmin','kategori',1,1,1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(4,'superadmin','unit',1,1,1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(5,'superadmin','pesanan',1,1,1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(6,'superadmin','pembayaran',1,1,1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(7,'superadmin','user',1,1,1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(8,'superadmin','laporan',1,1,1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(9,'superadmin','backup',1,1,1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(10,'superadmin','pengaturan',1,1,1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(11,'admin_unit','dashboard',0,1,0,0,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(12,'admin_unit','produk',1,1,1,1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(13,'admin_unit','stok',1,1,1,0,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(14,'admin_unit','pesanan',0,1,1,0,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(15,'admin_unit','pembayaran',0,1,1,0,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(16,'admin_unit','laporan',0,1,0,0,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(17,'pimpinan','dashboard',0,1,0,0,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(18,'pimpinan','laporan',0,1,0,0,'2026-09-22 23:10:48','2026-09-22 23:10:48');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (1,1,'robusta-polinela.jpg',1,'2026-09-22 23:10:48'),(2,2,'kopi-bubuk.jpg',1,'2026-09-22 23:10:48'),(3,3,'kakao-nibs.jpg',1,'2026-09-22 23:10:48'),(4,4,'dark-chocolate.jpg',1,'2026-09-22 23:10:48'),(5,5,'lada-hitam.jpg',1,'2026-09-22 23:10:48'),(6,6,'minyak-atsiri.jpg',1,'2026-09-22 23:10:48'),(7,7,'pupuk-organik.jpg',1,'2026-09-22 23:10:48'),(8,8,'bibit-kopi.jpg',1,'2026-09-22 23:10:48');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL,
  `nama_produk` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `deskripsi` longtext DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `berat_gram` int(11) NOT NULL DEFAULT 500,
  `satuan` varchar(50) NOT NULL DEFAULT 'pack',
  `stok` int(11) NOT NULL DEFAULT 0,
  `stok_min` int(11) NOT NULL DEFAULT 5,
  `gambar_utama` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `rating_avg` decimal(3,2) NOT NULL DEFAULT 0.00,
  `total_terjual` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `unit_id` (`unit_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,1,'Kopi Robusta Polinela Roasted Beans 250g','kopi-robusta-polinela-roasted-beans-250g','Kopi Robusta murni petik merah dari kebun percobaan Politeknik Negeri Lampung. Disangrai dengan profil medium-dark roast, menghasilkan aroma cokelat karamel yang pekat dengan crema tebal dan aftertaste manis alami.',45000.00,250,'pouch',45,10,'robusta-polinela.jpg','aktif',1,4.90,86,'2026-09-22 23:10:48','2026-09-22 23:10:48',NULL),(2,1,1,'Kopi Bubuk Robusta Organik Tefa 200g','kopi-bubuk-robusta-organik-tefa-200g','Kopi bubuk halus siap seduh untuk seduhan tubruk tradisional maupun espresso. Diproses secara higienis di Tefa Pengolahan Kopi Polinela tanpa bahan pengawet atau campuran jagung.',35000.00,200,'kemasan',60,10,'kopi-bubuk.jpg','aktif',1,4.80,120,'2026-09-22 23:10:48','2026-09-22 23:10:48',NULL),(3,2,2,'Kakao Nibs Premium Fermentasi Polinela 250g','kakao-nibs-premium-fermentasi-250g','Cacahan biji kakao fermentasi murni bermutu tinggi. Kaya antioksidan flavonoid, crunchy, tanpa pemanis buatan, cocok untuk cemilan sehat, topping smoothie bowl, oatmeal, dan campuran kue.',42000.00,250,'jar',35,5,'kakao-nibs.jpg','aktif',1,4.85,54,'2026-09-22 23:10:48','2026-09-22 23:10:48',NULL),(4,2,5,'Dark Chocolate Artisan Tefa 70% Single Origin 80g','dark-chocolate-artisan-tefa-70-persen','Cokelat batang hitam artisan asli produksi Teaching Factory Polinela. Mengandung 70% padatan kakao lokal dengan rasa fruity nutty yang elegan, meleleh sempurna di lidah.',28000.00,80,'batang',50,8,'dark-chocolate.jpg','aktif',1,4.95,92,'2026-09-22 23:10:48','2026-09-22 23:10:48',NULL),(5,3,3,'Lada Hitam Lampung Asli (Black Pepper) Butir 200g','lada-hitam-lampung-asli-butir-200g','Lada hitam asli Lampung yang terkenal di dunia internasional dengan aroma pedas menyengat dan minyak atsiri piperin tinggi. Dikeringkan alami dengan kadar air terkontrol.',38000.00,200,'botol bumbu',40,10,'lada-hitam.jpg','aktif',1,4.75,67,'2026-09-22 23:10:48','2026-09-22 23:10:48',NULL),(6,4,3,'Minyak Serai Wangi Murni (Citronella Oil) 60ml','minyak-serai-wangi-murni-60ml','Minyak atsiri 100% murni hasil destilasi uap daun serai wangi kebun Polinela. Efektif sebagai aromaterapi relaksasi, pengusir nyamuk alami, dan penghangat tubuh.',55000.00,120,'botol pipet',25,5,'minyak-atsiri.jpg','aktif',0,4.88,38,'2026-09-22 23:10:48','2026-09-22 23:10:48',NULL),(7,4,4,'Pupuk Organik Cair Hayati Tefa Polinela 1 Liter','pupuk-organik-cair-hayati-tefa-1l','Pupuk cair mikrobia penyubur tanaman hasil fermentasi limbah perkebunan kampus. Mengandung unsur hara makro & mikro lengkap, merangsang pertumbuhan tunas dan bunga.',30000.00,1100,'jerigen 1L',8,10,'pupuk-organik.jpg','aktif',0,4.70,45,'2026-09-22 23:10:48','2026-09-22 23:10:48',NULL),(8,1,4,'Bibit Kopi Robusta Unggul Klon BP 42 Siap Tanam','bibit-kopi-robusta-unggul-bp42','Bibit stek sambung kopi robusta klon unggulan Polinela umur 6 bulan. Siap tanam di polibag, adaptif terhadap dataran rendah-menengah dan tahan penyakit karat daun.',18000.00,1500,'polibag',75,15,'bibit-kopi.jpg','aktif',0,4.65,110,'2026-09-22 23:10:48','2026-09-22 23:10:48',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5,
  `komentar` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `reply` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','Polinela Agro Digital','general','2026-09-22 23:10:48','2026-09-22 23:10:48'),(2,'site_tagline','Pasar Digital Produk Perkebunan Kampus','general','2026-09-22 23:10:48','2026-09-22 23:10:48'),(3,'campus_address','Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung 35141, Lampung - Indonesia','general','2026-09-22 23:10:48','2026-09-22 23:10:48'),(4,'contact_phone','0721-703995','general','2026-09-22 23:10:48','2026-09-22 23:10:48'),(5,'contact_wa','081234567890','general','2026-09-22 23:10:48','2026-09-22 23:10:48'),(6,'contact_email','agro@polinela.ac.id','general','2026-09-22 23:10:48','2026-09-22 23:10:48'),(7,'bank_accounts','[{\"bank\":\"Bank Mandiri\",\"no_rekening\":\"114-00-8899123-4\",\"atas_nama\":\"POLITEKNIK NEGERI LAMPUNG TEFA\"},{\"bank\":\"Bank BRI\",\"no_rekening\":\"0098-01-000456-30-2\",\"atas_nama\":\"POLINELA AGRO DIGITAL\"},{\"bank\":\"Bank Syariah Indonesia (BSI)\",\"no_rekening\":\"711-234-5678\",\"atas_nama\":\"BLU POLITEKNIK NEGERI LAMPUNG\"}]','payment','2026-09-22 23:10:48','2026-09-22 23:10:48'),(8,'midtrans_client_key','SB-Mid-client-polinela-demo','payment','2026-09-22 23:10:48','2026-09-22 23:10:48'),(9,'shipping_rates','[{\"wilayah\":\"Ambil di Tefa Polinela (Gratis)\",\"tarif\":0,\"estimasi\":\"Langsung ambil di kampus\"},{\"wilayah\":\"Kurir Kampus (Lingkungan Polinela \\/ Rajabasa)\",\"tarif\":5000,\"estimasi\":\"Hari yang sama\"},{\"wilayah\":\"Bandar Lampung (Kota)\",\"tarif\":12000,\"estimasi\":\"1 hari\"},{\"wilayah\":\"Luar Kota \\/ Luar Provinsi (JNE\\/SiCepat)\",\"tarif\":25000,\"estimasi\":\"2-3 hari\"}]','shipping','2026-09-22 23:10:48','2026-09-22 23:10:48');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shippings`
--

DROP TABLE IF EXISTS `shippings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shippings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL,
  `kurir` varchar(100) NOT NULL,
  `layanan` varchar(100) DEFAULT NULL,
  `no_resi` varchar(100) DEFAULT NULL,
  `ongkir` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estimasi` varchar(50) DEFAULT NULL,
  `penerima_nama` varchar(150) NOT NULL,
  `penerima_telepon` varchar(25) NOT NULL,
  `alamat_lengkap` text NOT NULL,
  `kota` varchar(100) NOT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `status` enum('pending','dikemas','dikirim','sampai') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shippings`
--

LOCK TABLES `shippings` WRITE;
/*!40000 ALTER TABLE `shippings` DISABLE KEYS */;
/*!40000 ALTER TABLE `shippings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stocks`
--

DROP TABLE IF EXISTS `stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stocks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `tipe` enum('masuk','keluar','penyesuaian') NOT NULL DEFAULT 'masuk',
  `qty` int(11) NOT NULL,
  `sisa_stok` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stocks`
--

LOCK TABLES `stocks` WRITE;
/*!40000 ALTER TABLE `stocks` DISABLE KEYS */;
INSERT INTO `stocks` VALUES (1,1,'masuk',131,45,'Stok awal produksi panen kampus Polinela',1,'2026-09-22 23:10:48'),(2,2,'masuk',180,60,'Stok awal produksi panen kampus Polinela',1,'2026-09-22 23:10:48'),(3,3,'masuk',89,35,'Stok awal produksi panen kampus Polinela',1,'2026-09-22 23:10:48'),(4,4,'masuk',142,50,'Stok awal produksi panen kampus Polinela',1,'2026-09-22 23:10:48'),(5,5,'masuk',107,40,'Stok awal produksi panen kampus Polinela',1,'2026-09-22 23:10:48'),(6,6,'masuk',63,25,'Stok awal produksi panen kampus Polinela',1,'2026-09-22 23:10:48'),(7,7,'masuk',53,8,'Stok awal produksi panen kampus Polinela',1,'2026-09-22 23:10:48'),(8,8,'masuk',185,75,'Stok awal produksi panen kampus Polinela',1,'2026-09-22 23:10:48');
/*!40000 ALTER TABLE `stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sus_responses`
--

DROP TABLE IF EXISTS `sus_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sus_responses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `nama_responden` varchar(150) DEFAULT NULL,
  `role_responden` varchar(50) DEFAULT NULL,
  `q1` tinyint(1) NOT NULL,
  `q2` tinyint(1) NOT NULL,
  `q3` tinyint(1) NOT NULL,
  `q4` tinyint(1) NOT NULL,
  `q5` tinyint(1) NOT NULL,
  `q6` tinyint(1) NOT NULL,
  `q7` tinyint(1) NOT NULL,
  `q8` tinyint(1) NOT NULL,
  `q9` tinyint(1) NOT NULL,
  `q10` tinyint(1) NOT NULL,
  `total_score` decimal(5,2) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sus_responses`
--

LOCK TABLES `sus_responses` WRITE;
/*!40000 ALTER TABLE `sus_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `sus_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nama_unit` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `pj_nama` varchar(150) DEFAULT NULL,
  `kontak` varchar(50) DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,'Unit Kopi Polinela','unit-kopi-polinela','Unit usaha dan Teaching Factory pengolahan biji kopi robusta dan arabika unggulan dari kebun riset Polinela.','unit-kopi.png','Ir. Hendra Saputra, M.T.A.','0812-3456-7801','Gedung Tefa Pengolahan Hasil Kopi Polinela',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(2,'Unit Kakao & Cokelat Polinela','unit-kakao-polinela','Unit pengolahan fermentasi biji kakao premium, cokelat batang artesanal, dan kakao nibs bermutu tinggi.','unit-kakao.png','Dr. Maya Kartika, S.P., M.Si.','0812-3456-7802','Laboratorium Terpadu Kakao Polinela',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(3,'Unit Lada & Rempah Lampung','unit-lada-rempah','Unit budidaya dan pemrosesan lada hitam (Lampung black pepper) dan rempah khas Lampung berstandar ekspor.','unit-lada.png','Bambang Kusumo, S.St., M.Tr.P.','0812-3456-7803','Kebun Percobaan Rempah Polinela',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(4,'Unit Olahan & Atsiri Tefa','unit-olahan-atsiri','Unit penyulingan minyak atsiri serai wangi, pupuk organik hayati, dan produk olahan hortikultura kampus.','unit-atsiri.png','Nurul Hidayati, S.P., M.Sc.','0812-3456-7804','Pusat Bio-Industri & Tefa Hortikultura Polinela',1,'2026-09-22 23:10:48','2026-09-22 23:10:48');
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(25) DEFAULT NULL,
  `role` enum('superadmin','admin_unit','konsumen','pimpinan') NOT NULL DEFAULT 'konsumen',
  `foto` varchar(255) DEFAULT NULL,
  `unit_id` int(11) unsigned DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Administrator','admin@polinela.ac.id','$2y$10$HuKq6wuvoxRjaY1Di8fGU.zFBsDWOf9.ZgCyDvisDn.ruaLSu.72.','081234567890','superadmin','default-avatar.png',NULL,'Gedung Rektorat Polinela, Bandar Lampung',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(2,'Admin Unit Kopi Polinela','adminkopi@polinela.ac.id','$2y$10$Cyu1LA3wZrv0LR11pP6Jj./Ml/SsMT/WOin9JIL0m6ORnfSvg4BC6','081234567891','admin_unit','default-avatar.png',1,'Kebun Percobaan & Tefa Pengolahan Kopi Polinela',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(3,'Admin Unit Kakao Polinela','adminkakao@polinela.ac.id','$2y$10$S5WeNRNKPkeQVL1y94K6d.9sRHtRgbOcUyj71UX7MXMp7qynp5nqm','081234567892','admin_unit','default-avatar.png',2,'Laboratorium Pengolahan Hasil Kakao Polinela',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(4,'Admin Unit Lada & Rempah','adminlada@polinela.ac.id','$2y$10$DlaheH7fBkf3dmCIqNayT.4.fF98uDhdWEIslsYJG9pp/O4J83AvG','081234567893','admin_unit','default-avatar.png',3,'Unit Produksi Rempah Unggulan Lampung Polinela',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(5,'Pimpinan Polinela','pimpinan@polinela.ac.id','$2y$10$2xmEeeCAOi2mCSEp/ptXBOmsedGr97iACWfFBQbY1G0dgGAwRPJs6','081234567894','pimpinan','default-avatar.png',NULL,'Ruang Manajemen & Direktur Polinela',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(6,'Budi Pratama (Civitas Polinela)','budi@gmail.com','$2y$10$e7AHDjg/4t/knqoVKCwE/uXZPtRlWLwgQA1aEBtbhtWcRB/qXojT6','085278901234','konsumen','default-avatar.png',NULL,'Jl. Flamboyan Blok B No. 4, Kemiling, Bandar Lampung',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(7,'Siti Rahmawati','siti@gmail.com','$2y$10$nP6mA2LPJYOWAr6lD0V/r.47giQltp4c4E2OyXUfsdDNNBSLHgbC.','085712345678','konsumen','default-avatar.png',NULL,'Jl. ZA Pagar Alam No. 45, Rajabasa, Bandar Lampung',1,'2026-09-22 23:10:48','2026-09-22 23:10:48');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vouchers`
--

DROP TABLE IF EXISTS `vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vouchers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `tipe` enum('fixed','persen') NOT NULL DEFAULT 'fixed',
  `diskon` decimal(12,2) NOT NULL,
  `min_belanja` decimal(12,2) NOT NULL DEFAULT 0.00,
  `max_diskon` decimal(12,2) DEFAULT NULL,
  `kuota` int(11) NOT NULL DEFAULT 100,
  `terpakai` int(11) NOT NULL DEFAULT 0,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_berakhir` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vouchers`
--

LOCK TABLES `vouchers` WRITE;
/*!40000 ALTER TABLE `vouchers` DISABLE KEYS */;
INSERT INTO `vouchers` VALUES (1,'POLINELAJUARA','Diskon Civitas Polinela Juara','fixed',20000.00,50000.00,20000.00,200,12,'2026-01-01','2026-12-31',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(2,'PANENRAYA10','Promo Panen Raya 10%','persen',10.00,30000.00,25000.00,150,28,'2026-01-01','2026-12-31',1,'2026-09-22 23:10:48','2026-09-22 23:10:48'),(3,'MAHASISWAAGRO','Subsidi Belanja Praktikum Mahasiswa','fixed',15000.00,40000.00,15000.00,100,5,'2026-01-01','2026-12-31',1,'2026-09-22 23:10:48','2026-09-22 23:10:48');
/*!40000 ALTER TABLE `vouchers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlists` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_product_id` (`user_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlists`
--

LOCK TABLES `wishlists` WRITE;
/*!40000 ALTER TABLE `wishlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlists` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-22 23:16:59
