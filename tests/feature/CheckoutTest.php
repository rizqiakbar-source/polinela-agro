<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class CheckoutTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * Test: Akses checkout tanpa login diarahkan ke halaman login
     */
    public function testCheckoutRequiresAuthentication(): void
    {
        $result = $this->call('get', 'checkout');
        $result->assertRedirectTo(base_url('login'));
    }

    /**
     * Test: POST checkout/process tanpa login diarahkan ke login
     */
    public function testCheckoutProcessRequiresAuth(): void
    {
        $result = $this->call('post', 'checkout/process', []);
        $result->assertRedirectTo(base_url('login'));
    }

    /**
     * Test: POST apply voucher tanpa login diarahkan ke login
     */
    public function testApplyVoucherRequiresAuth(): void
    {
        $result = $this->call('post', 'checkout/voucher', [
            'kode_voucher' => 'TESTVOUCHER',
        ]);
        $result->assertRedirectTo(base_url('login'));
    }

    /**
     * Test: Halaman keranjang membutuhkan login
     */
    public function testKeranjangRequiresAuth(): void
    {
        $result = $this->call('get', 'keranjang');
        $result->assertRedirectTo(base_url('login'));
    }

    /**
     * Test: Tambah ke keranjang tanpa login redirect
     */
    public function testAddToCartRequiresAuth(): void
    {
        $result = $this->call('post', 'keranjang/add', [
            'product_id' => 1,
            'qty'        => 1,
        ]);
        $result->assertRedirectTo(base_url('login'));
    }

    /**
     * Test: Halaman katalog publik dapat diakses (HTTP 200)
     */
    public function testKatalogPublicAccessible(): void
    {
        $result = $this->call('get', 'katalog');
        $result->assertOK();
    }

    /**
     * Test: Halaman beranda publik dapat diakses (HTTP 200)
     */
    public function testBerandaAccessible(): void
    {
        $result = $this->call('get', '/');
        $result->assertOK();
    }

    /**
     * Test: Halaman lacak pesanan publik dapat diakses
     */
    public function testLacakPageAccessible(): void
    {
        $result = $this->call('get', 'lacak');
        $result->assertOK();
    }

    /**
     * Test: Halaman SUS survei publik dapat diakses
     */
    public function testSusPageAccessible(): void
    {
        $result = $this->call('get', 'sus');
        $result->assertOK();
    }
}
