<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $logs,
        ]);
    }
}
