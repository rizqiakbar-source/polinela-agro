<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama'         => 'nullable|string|max:150',
            'nama_lengkap' => 'nullable|string|max:150',
            'email'        => 'required|email|max:150|unique:users,email',
            'password'     => 'required|string|min:6',
            'no_hp'        => 'nullable|string|max:20',
            'alamat'       => 'nullable|string',
        ]);

        $nama = $validated['nama'] ?? $validated['nama_lengkap'] ?? 'User';

        $user = User::create([
            'nama'      => $nama,
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'no_hp'     => $validated['no_hp'] ?? null,
            'alamat'    => $validated['alamat'] ?? null,
            'role'      => 'konsumen',
            'is_active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::create([
            'user_id'   => $user->id,
            'aksi'      => 'Register',
            'modul'     => 'Autentikasi',
            'deskripsi' => "User {$user->email} ({$user->nama}) berhasil mendaftar.",
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Registrasi berhasil!',
            'token'   => $token,
            'user'    => $user->load('unit'),
            'data'    => [
                'user'  => $user->load('unit'),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required',
            'password' => 'required',
        ]);

        $loginInput = $request->email;
        $user = User::where('email', $loginInput)
            ->orWhere('nama', $loginInput)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Email atau kata sandi tidak valid.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi admin kampus.',
            ], 403);
        }

        // Revoke previous tokens
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::create([
            'user_id'   => $user->id,
            'aksi'      => 'Login',
            'modul'     => 'Autentikasi',
            'deskripsi' => "User {$user->email} ({$user->nama}) berhasil login.",
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Login berhasil! Selamat datang kembali.',
            'token'   => $token,
            'user'    => $user->load('unit'),
            'data'    => [
                'user'  => $user->load('unit'),
                'token' => $token,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('unit');

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'user'    => $user,
            'data'    => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nama'         => 'nullable|string|max:150',
            'nama_lengkap' => 'nullable|string|max:150',
            'email'        => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'no_hp'        => 'nullable|string|max:20',
            'alamat'       => 'nullable|string',
            'password'     => 'nullable|string|min:6',
            'foto'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ], [
            'email.unique' => 'Alamat email ini sudah terdaftar pada akun lain. Silakan gunakan email lain.',
            'email.email'  => 'Format email tidak valid.',
        ]);

        $nama = $request->input('nama') ?? $request->input('nama_lengkap');
        if ($nama) {
            $user->nama = $nama;
        }
        if ($request->filled('email')) {
            $user->email = trim($request->input('email'));
        }
        if ($request->has('no_hp')) {
            $user->no_hp = $request->input('no_hp');
        }
        if ($request->has('alamat')) {
            $user->alamat = $request->input('alamat');
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        // Handle foto upload
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            $uploadDir = public_path('uploads/avatars');
            if (!file_exists($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            // Delete old foto if exists
            if ($user->foto && file_exists(public_path('uploads/avatars/' . $user->foto))) {
                @unlink(public_path('uploads/avatars/' . $user->foto));
            }

            $file->move($uploadDir, $filename);
            $user->foto = $filename;
        }

        $user->save();

        ActivityLog::log('Update Profil', 'User', "User {$user->email} memperbarui profil.");

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'user'    => $user->fresh()->load('unit'),
            'data'    => $user->fresh()->load('unit'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }
}
