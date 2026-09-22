<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'id'            => 1,
                'unit_id'       => 1,
                'category_id'   => 1,
                'nama_produk'   => 'Kopi Robusta Polinela Roasted Beans 250g',
                'slug'          => 'kopi-robusta-polinela-roasted-beans-250g',
                'deskripsi'     => 'Kopi Robusta murni petik merah dari kebun percobaan Politeknik Negeri Lampung. Disangrai dengan profil medium-dark roast, menghasilkan aroma cokelat karamel yang pekat dengan crema tebal dan aftertaste manis alami.',
                'harga'         => 45000.00,
                'berat_gram'    => 250,
                'satuan'        => 'pouch',
                'stok'          => 45,
                'stok_min'      => 10,
                'gambar_utama'  => 'robusta-polinela.jpg',
                'status'        => 'aktif',
                'featured'      => 1,
                'rating_avg'    => 4.90,
                'total_terjual' => 86,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'id'            => 2,
                'unit_id'       => 1,
                'category_id'   => 1,
                'nama_produk'   => 'Kopi Bubuk Robusta Organik Tefa 200g',
                'slug'          => 'kopi-bubuk-robusta-organik-tefa-200g',
                'deskripsi'     => 'Kopi bubuk halus siap seduh untuk seduhan tubruk tradisional maupun espresso. Diproses secara higienis di Tefa Pengolahan Kopi Polinela tanpa bahan pengawet atau campuran jagung.',
                'harga'         => 35000.00,
                'berat_gram'    => 200,
                'satuan'        => 'kemasan',
                'stok'          => 60,
                'stok_min'      => 10,
                'gambar_utama'  => 'kopi-bubuk.jpg',
                'status'        => 'aktif',
                'featured'      => 1,
                'rating_avg'    => 4.80,
                'total_terjual' => 120,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'id'            => 3,
                'unit_id'       => 2,
                'category_id'   => 2,
                'nama_produk'   => 'Kakao Nibs Premium Fermentasi Polinela 250g',
                'slug'          => 'kakao-nibs-premium-fermentasi-250g',
                'deskripsi'     => 'Cacahan biji kakao fermentasi murni bermutu tinggi. Kaya antioksidan flavonoid, crunchy, tanpa pemanis buatan, cocok untuk cemilan sehat, topping smoothie bowl, oatmeal, dan campuran kue.',
                'harga'         => 42000.00,
                'berat_gram'    => 250,
                'satuan'        => 'jar',
                'stok'          => 35,
                'stok_min'      => 5,
                'gambar_utama'  => 'kakao-nibs.jpg',
                'status'        => 'aktif',
                'featured'      => 1,
                'rating_avg'    => 4.85,
                'total_terjual' => 54,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'id'            => 4,
                'unit_id'       => 2,
                'category_id'   => 5,
                'nama_produk'   => 'Dark Chocolate Artisan Tefa 70% Single Origin 80g',
                'slug'          => 'dark-chocolate-artisan-tefa-70-persen',
                'deskripsi'     => 'Cokelat batang hitam artisan asli produksi Teaching Factory Polinela. Mengandung 70% padatan kakao lokal dengan rasa fruity nutty yang elegan, meleleh sempurna di lidah.',
                'harga'         => 28000.00,
                'berat_gram'    => 80,
                'satuan'        => 'batang',
                'stok'          => 50,
                'stok_min'      => 8,
                'gambar_utama'  => 'dark-chocolate.jpg',
                'status'        => 'aktif',
                'featured'      => 1,
                'rating_avg'    => 4.95,
                'total_terjual' => 92,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'id'            => 5,
                'unit_id'       => 3,
                'category_id'   => 3,
                'nama_produk'   => 'Lada Hitam Lampung Asli (Black Pepper) Butir 200g',
                'slug'          => 'lada-hitam-lampung-asli-butir-200g',
                'deskripsi'     => 'Lada hitam asli Lampung yang terkenal di dunia internasional dengan aroma pedas menyengat dan minyak atsiri piperin tinggi. Dikeringkan alami dengan kadar air terkontrol.',
                'harga'         => 38000.00,
                'berat_gram'    => 200,
                'satuan'        => 'botol bumbu',
                'stok'          => 40,
                'stok_min'      => 10,
                'gambar_utama'  => 'lada-hitam.jpg',
                'status'        => 'aktif',
                'featured'      => 1,
                'rating_avg'    => 4.75,
                'total_terjual' => 67,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'id'            => 6,
                'unit_id'       => 4,
                'category_id'   => 3,
                'nama_produk'   => 'Minyak Serai Wangi Murni (Citronella Oil) 60ml',
                'slug'          => 'minyak-serai-wangi-murni-60ml',
                'deskripsi'     => 'Minyak atsiri 100% murni hasil destilasi uap daun serai wangi kebun Polinela. Efektif sebagai aromaterapi relaksasi, pengusir nyamuk alami, dan penghangat tubuh.',
                'harga'         => 55000.00,
                'berat_gram'    => 120,
                'satuan'        => 'botol pipet',
                'stok'          => 25,
                'stok_min'      => 5,
                'gambar_utama'  => 'minyak-atsiri.jpg',
                'status'        => 'aktif',
                'featured'      => 0,
                'rating_avg'    => 4.88,
                'total_terjual' => 38,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'id'            => 7,
                'unit_id'       => 4,
                'category_id'   => 4,
                'nama_produk'   => 'Pupuk Organik Cair Hayati Tefa Polinela 1 Liter',
                'slug'          => 'pupuk-organik-cair-hayati-tefa-1l',
                'deskripsi'     => 'Pupuk cair mikrobia penyubur tanaman hasil fermentasi limbah perkebunan kampus. Mengandung unsur hara makro & mikro lengkap, merangsang pertumbuhan tunas dan bunga.',
                'harga'         => 30000.00,
                'berat_gram'    => 1100,
                'satuan'        => 'jerigen 1L',
                'stok'          => 8,
                'stok_min'      => 10,
                'gambar_utama'  => 'pupuk-organik.jpg',
                'status'        => 'aktif',
                'featured'      => 0,
                'rating_avg'    => 4.70,
                'total_terjual' => 45,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'id'            => 8,
                'unit_id'       => 1,
                'category_id'   => 4,
                'nama_produk'   => 'Bibit Kopi Robusta Unggul Klon BP 42 Siap Tanam',
                'slug'          => 'bibit-kopi-robusta-unggul-bp42',
                'deskripsi'     => 'Bibit stek sambung kopi robusta klon unggulan Polinela umur 6 bulan. Siap tanam di polibag, adaptif terhadap dataran rendah-menengah dan tahan penyakit karat daun.',
                'harga'         => 18000.00,
                'berat_gram'    => 1500,
                'satuan'        => 'polibag',
                'stok'          => 75,
                'stok_min'      => 15,
                'gambar_utama'  => 'bibit-kopi.jpg',
                'status'        => 'aktif',
                'featured'      => 0,
                'rating_avg'    => 4.65,
                'total_terjual' => 110,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]
        ];

        $this->db->table('products')->insertBatch($products);

        // Tambahkan relasi gambar produk
        $images = [];
        foreach ($products as $p) {
            $images[] = [
                'product_id' => $p['id'],
                'image_url'  => $p['gambar_utama'],
                'is_primary' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        $this->db->table('product_images')->insertBatch($images);

        // Tambahkan stok awal
        $stocks = [];
        foreach ($products as $p) {
            $stocks[] = [
                'product_id' => $p['id'],
                'tipe'       => 'masuk',
                'qty'        => $p['stok'] + $p['total_terjual'],
                'sisa_stok'  => $p['stok'],
                'keterangan' => 'Stok awal produksi panen kampus Polinela',
                'user_id'    => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        $this->db->table('stocks')->insertBatch($stocks);
    }
}
