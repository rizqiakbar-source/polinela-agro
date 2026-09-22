<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class AuthTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * Test: Halaman login dapat diakses (HTTP 200)
     */
    public function testLoginPageReturns200(): void
    {
        $result = $this->call('get', 'login');
        $result->assertOK();
        $result->assertSee('Login');
    }

    /**
     * Test: Halaman register dapat diakses (HTTP 200)
     */
    public function testRegisterPageReturns200(): void
    {
        $result = $this->call('get', 'register');
        $result->assertOK();
        $result->assertSee('Daftar');
    }

    /**
     * Test: Login POST tanpa data mengembalikan redirect (validasi gagal)
     */
    public function testLoginPostWithoutDataRedirects(): void
    {
        $result = $this->call('post', 'login', []);
        $result->assertRedirect();
    }

    /**
     * Test: Login POST dengan email invalid redirect kembali
     */
    public function testLoginWithInvalidEmailRedirects(): void
    {
        $result = $this->call('post', 'login', [
            'email'    => 'invalid-email',
            'password' => '123456',
        ]);
        $result->assertRedirect();
    }

    /**
     * Test: Login POST dengan email tidak terdaftar
     */
    public function testLoginWithUnregisteredEmail(): void
    {
        $result = $this->call('post', 'login', [
            'email'    => 'tidakada@example.com',
            'password' => 'wrongpassword',
        ]);
        $result->assertRedirect();
    }

    /**
     * Test: Register POST tanpa data mengembalikan redirect
     */
    public function testRegisterPostWithoutDataRedirects(): void
    {
        $result = $this->call('post', 'register', []);
        $result->assertRedirect();
    }

    /**
     * Test: Register POST dengan nama terlalu pendek
     */
    public function testRegisterWithShortNameRedirects(): void
    {
        $result = $this->call('post', 'register', [
            'nama'     => 'AB',
            'email'    => 'test@example.com',
            'password' => '123456',
            'no_hp'    => '081234567890',
        ]);
        $result->assertRedirect();
    }

    /**
     * Test: Halaman lupa password dapat diakses
     */
    public function testForgotPasswordPageReturns200(): void
    {
        $result = $this->call('get', 'lupa-password');
        $result->assertOK();
    }

    /**
     * Test: Halaman reset password dapat diakses
     */
    public function testResetPasswordPageReturns200(): void
    {
        $result = $this->call('get', 'reset-password/test-token');
        $result->assertOK();
    }

    /**
     * Test: Akses keranjang tanpa login diarahkan ke login
     */
    public function testProtectedRouteRedirectsToLogin(): void
    {
        $result = $this->call('get', 'keranjang');
        $result->assertRedirectTo(base_url('login'));
    }

    /**
     * Test: Akses checkout tanpa login diarahkan ke login
     */
    public function testCheckoutProtectedRoute(): void
    {
        $result = $this->call('get', 'checkout');
        $result->assertRedirectTo(base_url('login'));
    }

    /**
     * Test: Akses profil tanpa login diarahkan ke login
     */
    public function testProfilProtectedRoute(): void
    {
        $result = $this->call('get', 'profil');
        $result->assertRedirectTo(base_url('login'));
    }
}
