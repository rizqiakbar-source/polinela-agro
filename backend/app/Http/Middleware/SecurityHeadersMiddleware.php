<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and attach enterprise security headers
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Mencegah Clickjacking (tidak bisa di-embed di iframe situs luar)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Mencegah MIME-Type Sniffing (browser wajib ikuti content-type yang sah)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Mengaktifkan filter Cross-Site Scripting (XSS) di browser
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Melindungi data URL & referer saat berpindah domain
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Batasi akses fitur perangkat sensitif (Izinkan geolocation untuk origin sendiri)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        // Content Security Policy Baseline (Melindungi dari embedding tidak sah & framing)
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'self';");

        // Hapus header server information disclosure jika ada
        if (!headers_sent()) {
            @header_remove('X-Powered-By');
        }
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
