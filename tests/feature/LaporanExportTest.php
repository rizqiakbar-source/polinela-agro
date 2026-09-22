<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class LaporanExportTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * Test: Akses halaman laporan tanpa login admin diarahkan
     */
    public function testLaporanRequiresAdmin(): void
    {
        $result = $this->call('get', 'admin/laporan');
        // Harus redirect karena tidak terautentikasi sebagai admin
        $this->assertTrue(
            $result->isRedirect() || $result->response()->getStatusCode() === 403,
            'Laporan harus memerlukan autentikasi admin'
        );
    }

    /**
     * Test: Akses export PDF tanpa login admin diarahkan
     */
    public function testExportPdfRequiresAdmin(): void
    {
        $result = $this->call('get', 'admin/laporan/pdf?type=penjualan');
        $this->assertTrue(
            $result->isRedirect() || $result->response()->getStatusCode() === 403,
            'Export PDF harus memerlukan autentikasi admin'
        );
    }

    /**
     * Test: Akses export Excel/CSV tanpa login admin diarahkan
     */
    public function testExportExcelRequiresAdmin(): void
    {
        $result = $this->call('get', 'admin/laporan/excel?type=penjualan');
        $this->assertTrue(
            $result->isRedirect() || $result->response()->getStatusCode() === 403,
            'Export Excel harus memerlukan autentikasi admin'
        );
    }

    /**
     * Test: Akses admin dashboard tanpa login diarahkan
     */
    public function testAdminDashboardRequiresAuth(): void
    {
        $result = $this->call('get', 'admin/dashboard');
        $this->assertTrue(
            $result->isRedirect() || $result->response()->getStatusCode() === 403,
            'Dashboard admin harus memerlukan autentikasi'
        );
    }

    /**
     * Test: Halaman admin produk memerlukan autentikasi
     */
    public function testAdminProdukRequiresAuth(): void
    {
        $result = $this->call('get', 'admin/produk');
        $this->assertTrue(
            $result->isRedirect() || $result->response()->getStatusCode() === 403,
            'Admin produk harus memerlukan autentikasi admin'
        );
    }

    /**
     * Test: Halaman admin pesanan memerlukan autentikasi
     */
    public function testAdminPesananRequiresAuth(): void
    {
        $result = $this->call('get', 'admin/pesanan');
        $this->assertTrue(
            $result->isRedirect() || $result->response()->getStatusCode() === 403,
            'Admin pesanan harus memerlukan autentikasi admin'
        );
    }
}
