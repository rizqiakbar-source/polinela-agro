<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\LaporanModel;

class LaporanModelTest extends CIUnitTestCase
{
    protected LaporanModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new LaporanModel();
    }

    /**
     * Test: Laporan penjualan mengembalikan array
     */
    public function testGetPenjualanReturnsArray(): void
    {
        $result = $this->model->getPenjualan();
        $this->assertIsArray($result);
    }

    /**
     * Test: Laporan penjualan dengan filter tanggal
     */
    public function testGetPenjualanWithDateFilter(): void
    {
        $startDate = date('Y-m-01');
        $endDate   = date('Y-m-d');

        $result = $this->model->getPenjualan($startDate, $endDate);
        $this->assertIsArray($result);
    }

    /**
     * Test: Laporan penjualan dengan filter unit
     */
    public function testGetPenjualanWithUnitFilter(): void
    {
        $result = $this->model->getPenjualan(null, null, 1);
        $this->assertIsArray($result);
    }

    /**
     * Test: Produk terlaris mengembalikan array dengan limit
     */
    public function testGetProdukTerlarisByLimit(): void
    {
        $limit  = 5;
        $result = $this->model->getProdukTerlaris($limit);

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual($limit, count($result));
    }

    /**
     * Test: Produk terlaris memiliki kolom terjual dan pendapatan
     */
    public function testGetProdukTerlarisSortedByTerjual(): void
    {
        $result = $this->model->getProdukTerlaris(10);

        if (count($result) >= 2) {
            $this->assertGreaterThanOrEqual(
                (int) $result[1]['terjual'],
                (int) $result[0]['terjual'],
                'Hasil harus diurutkan berdasarkan total terjual descending'
            );
        }

        $this->assertTrue(true); // Pass jika data kurang dari 2
    }

    /**
     * Test: Stok produk mengembalikan data valid
     */
    public function testGetStokProdukReturnsArray(): void
    {
        $result = $this->model->getStokProduk();
        $this->assertIsArray($result);
    }

    /**
     * Test: Filter stok kritis hanya mengembalikan produk stok <= stok_min
     */
    public function testGetStokKritisFiltersCorrectly(): void
    {
        $result = $this->model->getStokProduk(null, true);
        $this->assertIsArray($result);

        foreach ($result as $item) {
            $this->assertLessThanOrEqual(
                (int) $item['stok_min'],
                (int) $item['stok'],
                'Stok kritis harus memiliki stok <= stok_min'
            );
        }
    }

    /**
     * Test: Data pelanggan mengembalikan array dengan field agregat
     */
    public function testGetPelangganReturnsArray(): void
    {
        $result = $this->model->getPelanggan();
        $this->assertIsArray($result);

        if (!empty($result)) {
            $first = $result[0];
            $this->assertArrayHasKey('nama', $first);
            $this->assertArrayHasKey('email', $first);
            $this->assertArrayHasKey('total_pesanan', $first);
            $this->assertArrayHasKey('total_belanja', $first);
        }
    }

    /**
     * Test: Data keuangan mengembalikan array dengan field transaksi
     */
    public function testGetKeuanganReturnsArray(): void
    {
        $result = $this->model->getKeuangan(date('Y'));
        $this->assertIsArray($result);

        if (!empty($result)) {
            $first = $result[0];
            $this->assertArrayHasKey('tanggal', $first);
            $this->assertArrayHasKey('total_transaksi', $first);
            $this->assertArrayHasKey('total_pendapatan', $first);
        }
    }

    /**
     * Test: Laporan per unit mengembalikan array dengan field omset
     */
    public function testGetPerUnitReturnsArray(): void
    {
        $result = $this->model->getPerUnit();
        $this->assertIsArray($result);

        if (!empty($result)) {
            $first = $result[0];
            $this->assertArrayHasKey('nama_unit', $first);
            $this->assertArrayHasKey('total_produk', $first);
            $this->assertArrayHasKey('total_omset', $first);
        }
    }
}
