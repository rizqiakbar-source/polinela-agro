<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrStaff
{
    /**
     * Handle an incoming request.
     * Ensure only superadmin, admin_unit, or pimpinan can access admin routes.
     * Regular customers (konsumen) are strictly forbidden (HTTP 403).
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Unauthenticated: Silakan login terlebih dahulu.',
            ], 401);
        }

        // Default allowed roles for admin area
        $roles = !empty($allowedRoles) ? $allowedRoles : ['superadmin', 'admin_unit', 'pimpinan'];

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk mengakses area administrator.',
            ], 403);
        }

        // Pimpinan is read-only (only safe HTTP methods: GET, HEAD, OPTIONS)
        if ($user->role === 'pimpinan' && !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Akses ditolak: Akun Pimpinan hanya memiliki hak akses baca (Read-Only) untuk laporan dan dashboard eksekutif.',
            ], 403);
        }

        return $next($request);
    }
}

