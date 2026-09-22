<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RateLimitFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Fitur rate limiting dasar untuk login & register
        $throttler = \Config\Services::throttler();
        $ip = $request->getIPAddress();
        
        // Batasi maksimal 30 request per menit untuk endpoint auth
        if ($throttler->check(md5($ip), 30, MINUTE) === false) {
            return \Config\Services::response()->setStatusCode(429, 'Terlalu banyak permintaan. Silakan tunggu 1 menit.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
