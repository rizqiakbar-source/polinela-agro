<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        if (!in_array($request->user()?->role, ['superadmin', 'pimpinan'])) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Akses ditolak: Hanya Superadmin dan Pimpinan yang dapat melihat log aktivitas.',
            ], 403);
        }

        $logs = ActivityLog::with('user')
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 20))->withQueryString();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $logs,
        ]);
    }
}
