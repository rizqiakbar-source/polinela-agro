<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\OtpVerification;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama'         => 'nullable|string|max:150',
            'nama_lengkap' => 'nullable|string|max:150',
            'email'        => 'required|email|max:150',
            'password'     => 'required|string|min:6' . ($request->filled('password_confirmation') ? '|confirmed' : ''),
            'no_hp'        => 'nullable|string|max:20',
            'alamat'       => 'nullable|string',
        ]);

        $nama = $validated['nama'] ?? $validated['nama_lengkap'] ?? 'User';

        // Check if user already exists
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser) {
            if ($existingUser->email_verified_at) {
                throw ValidationException::withMessages([
                    'email' => ['Email ini sudah terdaftar dan terverifikasi. Silakan masuk (login).'],
                ]);
            }
            // Update unverified user record with new details
            $existingUser->update([
                'nama'      => $nama,
                'password'  => Hash::make($validated['password']),
                'no_hp'     => $validated['no_hp'] ?? $existingUser->no_hp,
                'alamat'    => $validated['alamat'] ?? $existingUser->alamat,
                'is_active' => false,
            ]);
            $user = $existingUser;
        } else {
            $user = User::create([
                'nama'              => $nama,
                'email'             => $validated['email'],
                'password'          => Hash::make($validated['password']),
                'no_hp'             => $validated['no_hp'] ?? null,
                'alamat'            => $validated['alamat'] ?? null,
                'role'              => 'konsumen',
                'is_active'         => false,
                'email_verified_at' => null,
            ]);
        }

        // Invalidate prior registration OTPs for this email
        OtpVerification::where('email', $user->email)
            ->where('action', 'registration')
            ->update(['is_used' => true]);

        // Generate 6-digit numeric OTP
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        OtpVerification::create([
            'email'      => $user->email,
            'otp_code'   => $otp,
            'action'     => 'registration',
            'expires_at' => now()->addMinutes(5),
            'is_used'    => false,
        ]);

        // Send OTP via Email
        try {
            Mail::to($user->email)->send(new SendOtpMail($otp, $user->nama, 5));
            Log::info("OTP Email successfully sent to {$user->email}");
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim email OTP ke {$user->email}: " . $e->getMessage());
        }

        ActivityLog::create([
            'user_id'   => $user->id,
            'aksi'      => 'Register (Pending OTP)',
            'modul'     => 'Autentikasi',
            'deskripsi' => "User {$user->email} ({$user->nama}) mendaftar, menunggu verifikasi OTP.",
        ]);

        return response()->json([
            'status'       => 'success',
            'success'      => true,
            'requires_otp' => true,
            'email'        => $user->email,
            'message'      => 'Kode verifikasi OTP telah dikirimkan ke email Anda (' . $user->email . '). Silakan periksa kotak masuk atau spam.',
            'debug_otp'    => app()->environment('testing') ? $otp : null,
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $otpRecord = OtpVerification::valid($request->email, $request->otp, 'registration')
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Kode OTP salah atau telah kedaluwarsa. Silakan periksa kembali atau minta kode baru.',
            ], 422);
        }

        // Mark OTP as used
        $otpRecord->update(['is_used' => true]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        // Activate user and mark email verified
        $user->update([
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        // Issue Sanctum Token
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::create([
            'user_id'   => $user->id,
            'aksi'      => 'Verify OTP',
            'modul'     => 'Autentikasi',
            'deskripsi' => "User {$user->email} ({$user->nama}) berhasil memverifikasi email dengan OTP.",
        ]);

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Verifikasi email berhasil! Selamat datang di Polinela Agro.',
            'token'   => $token,
            'user'    => $user->load('unit'),
            'data'    => [
                'user'  => $user->load('unit'),
                'token' => $token,
            ],
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Akun dengan email ini tidak ditemukan.',
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'Akun Anda sudah terverifikasi sebelumnya. Silakan langsung login.',
            ], 400);
        }

        // Rate limit cooldown (60 seconds)
        $recentOtp = OtpVerification::where('email', $request->email)
            ->where('action', 'registration')
            ->where('created_at', '>=', now()->subSeconds(60))
            ->latest()
            ->first();

        if ($recentOtp) {
            $waitSeconds = 60 - now()->diffInSeconds($recentOtp->created_at);
            return response()->json([
                'status'      => 'error',
                'success'     => false,
                'message'     => "Mohon tunggu {$waitSeconds} detik sebelum meminta kode baru.",
                'retry_after' => $waitSeconds,
            ], 429);
        }

        // Invalidate old OTPs
        OtpVerification::where('email', $user->email)
            ->where('action', 'registration')
            ->update(['is_used' => true]);

        // Generate fresh OTP
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        OtpVerification::create([
            'email'      => $user->email,
            'otp_code'   => $otp,
            'action'     => 'registration',
            'expires_at' => now()->addMinutes(5),
            'is_used'    => false,
        ]);

        try {
            Mail::to($user->email)->send(new SendOtpMail($otp, $user->nama, 5));
            Log::info("Resend OTP Email successfully sent to {$user->email}");
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim ulang email OTP ke {$user->email}: " . $e->getMessage());
        }

        return response()->json([
            'status'    => 'success',
            'success'   => true,
            'message'   => 'Kode OTP baru telah berhasil dikirimkan ke email Anda.',
            'debug_otp' => app()->environment('testing') ? $otp : null,
        ]);
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
            // Jika akun belum aktif karena belum verifikasi email OTP
            if (!$user->email_verified_at && $user->role === 'konsumen') {
                return response()->json([
                    'status'       => 'error',
                    'success'      => false,
                    'requires_otp' => true,
                    'email'        => $user->email,
                    'message'      => 'Akun Anda belum diverifikasi. Silakan masukkan kode OTP yang telah dikirimkan ke email Anda.',
                ], 403);
            }

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

        $emailChanged = false;
        $otpSent = null;
        if ($request->filled('email')) {
            $newEmail = strtolower(trim($request->input('email')));
            if ($newEmail !== strtolower($user->email)) {
                $emailChanged = true;
                $user->email = $newEmail;
                $user->email_verified_at = null; // Reset status verifikasi email

                // Invalidate OTP lama untuk email ini & generate OTP baru
                OtpVerification::where('email', $newEmail)
                    ->where('action', 'registration')
                    ->update(['is_used' => true]);

                $otpSent = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

                OtpVerification::create([
                    'email'      => $newEmail,
                    'otp_code'   => $otpSent,
                    'action'     => 'registration',
                    'expires_at' => now()->addMinutes(5),
                    'is_used'    => false,
                ]);

                // Kirim email OTP
                try {
                    Mail::to($newEmail)->send(new SendOtpMail($otpSent, $user->nama, 5));
                    Log::info("OTP Email verification sent for updated email to {$newEmail}");
                } catch (\Throwable $e) {
                    Log::error("Gagal mengirim email OTP ke {$newEmail}: " . $e->getMessage());
                }
            }
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

        // Handle foto upload (gunakan guessExtension untuk proteksi MIME spoofing)
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $ext = $file->guessExtension() ?: $file->extension() ?: 'jpg';
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $ext;

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

        ActivityLog::log('Update Profil', 'User', "User {$user->email} memperbarui profil." . ($emailChanged ? " (Email diubah, verifikasi OTP di-reset)" : ""));

        if ($emailChanged) {
            return response()->json([
                'status'        => 'success',
                'success'       => true,
                'email_changed' => true,
                'requires_otp'  => true,
                'email'         => $user->email,
                'message'       => 'Profil berhasil diperbarui. Karena Anda mengubah alamat email, silakan masukkan kode OTP yang telah dikirimkan ke email baru Anda (' . $user->email . ').',
                'debug_otp'     => app()->environment('testing') ? $otpSent : null,
                'user'          => $user->fresh()->load('unit'),
                'data'          => $user->fresh()->load('unit'),
            ]);
        }

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
