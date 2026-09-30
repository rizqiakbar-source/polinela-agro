<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Unit;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'superadmin') {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Hanya superadmin yang dapat mengelola pengguna.'], 403);
        }

        $query = User::with('unit');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $search = $request->get('search') ?? $request->get('q');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate($request->get('per_page', 15));

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'data'    => $users,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'superadmin') {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $nama = $request->input('nama') ?? $request->input('nama_lengkap') ?? $request->input('username');

        $validated = $request->validate([
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'role'      => 'required|in:superadmin,admin_unit,pimpinan,konsumen',
            'unit_id'   => 'nullable',
            'no_hp'     => 'nullable|string',
            'alamat'    => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $user = User::create([
            'nama'      => $nama ?: 'User Baru',
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => $validated['role'],
            'unit_id'   => ($validated['role'] === 'admin_unit' && !empty($validated['unit_id'])) ? $validated['unit_id'] : null,
            'no_hp'     => $validated['no_hp'] ?? null,
            'alamat'    => $validated['alamat'] ?? null,
            'avatar'    => 'default-avatar.png',
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        ActivityLog::create([
            'user_id'   => $request->user()->id,
            'aksi'      => 'Tambah User',
            'modul'     => 'Pengguna',
            'ip_address'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
            'deskripsi' => "Membuat akun {$user->email} dengan role {$user->role}",
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Pengguna berhasil dibuat!',
            'data'    => $user->load('unit'),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($request->user()->role !== 'superadmin') {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Pengguna tidak ditemukan.'], 404);
        }

        $nama = $request->input('nama') ?? $request->input('nama_lengkap') ?? $request->input('username');

        $validated = $request->validate([
            'email'     => "required|email|unique:users,email,{$id}",
            'role'      => 'required|in:superadmin,admin_unit,pimpinan,konsumen',
            'unit_id'   => 'nullable',
            'no_hp'     => 'nullable|string',
            'alamat'    => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($nama) {
            $user->nama = $nama;
        }
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->unit_id = ($validated['role'] === 'admin_unit' && !empty($validated['unit_id'])) ? $validated['unit_id'] : null;
        
        if ($request->has('no_hp')) {
            $user->no_hp = $request->input('no_hp');
        }
        if ($request->has('alamat')) {
            $user->alamat = $request->input('alamat');
        }
        if ($request->has('is_active')) {
            $user->is_active = $request->boolean('is_active');
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        ActivityLog::create([
            'user_id'   => $request->user()->id,
            'aksi'      => 'Update User',
            'modul'     => 'Pengguna',
            'ip_address'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
            'deskripsi' => "Memperbarui akun {$user->email} ({$user->role})",
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Data pengguna berhasil diperbarui!',
            'data'    => $user->load('unit'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'superadmin') {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if ($request->user()->id == $id) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'], 400);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => 'Pengguna tidak ditemukan.'], 404);
        }

        $email = $user->email;
        $user->delete();

        ActivityLog::create([
            'user_id'   => $request->user()->id,
            'aksi'      => 'Hapus User',
            'modul'     => 'Pengguna',
            'ip_address'=> $request->ip(),
            'user_agent'=> $request->userAgent(),
            'deskripsi' => "Menghapus akun pengguna {$email}",
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Pengguna berhasil dihapus.',
        ]);
    }
}
