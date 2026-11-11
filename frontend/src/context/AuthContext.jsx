import React, { createContext, useContext, useState, useEffect } from 'react';
import client, { clearApiCache } from '../api/client';
import Swal from 'sweetalert2';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(() => {
        const saved = localStorage.getItem('user');
        return saved ? JSON.parse(saved) : null;
    });
    const [token, setToken] = useState(() => localStorage.getItem('token') || null);
    const [loading, setLoading] = useState(true);

    const fetchUser = async () => {
        if (!token) {
            setLoading(false);
            return;
        }
        try {
            const res = await client.get('/auth/me');
            if (res.data.status === 'success') {
                setUser(res.data.data);
                localStorage.setItem('user', JSON.stringify(res.data.data));
            }
        } catch (err) {
            if (err.response?.status === 401) {
                logout(false);
            } else {
                console.warn('Gagal sinkronisasi data user dari server:', err?.message || err);
            }
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchUser();
    }, [token]);

    const login = async (email, password) => {
        try {
            clearApiCache();
            const res = await client.post('/auth/login', { email, password });
            if (res.data.status === 'success') {
                clearApiCache();
                const { user: userData, token: tokenData } = res.data.data;
                setUser(userData);
                setToken(tokenData);
                localStorage.setItem('token', tokenData);
                localStorage.setItem('user', JSON.stringify(userData));
                
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil',
                    text: `Selamat datang kembali, ${userData.nama_lengkap || userData.username}!`,
                    timer: 1500,
                    showConfirmButton: false,
                });
                return { success: true, user: userData };
            }
        } catch (error) {
            if (error.response?.data?.requires_otp) {
                return { 
                    success: false, 
                    requires_otp: true, 
                    email: error.response.data.email, 
                    message: error.response.data.message 
                };
            }
            const message = error.response?.data?.message || 'Login gagal. Silakan periksa email dan password Anda.';
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: message,
            });
            return { success: false, message };
        }
    };

    const register = async (userData) => {
        try {
            const res = await client.post('/auth/register', userData);
            if (res.data.status === 'success') {
                if (res.data.requires_otp) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Verifikasi Email Diperlukan',
                        text: res.data.message || 'Silakan periksa kode OTP yang telah dikirim ke email Anda.',
                        confirmButtonText: 'Masukkan Kode OTP',
                        confirmButtonColor: '#15803d',
                    });
                    return { 
                        success: true, 
                        requires_otp: true, 
                        email: res.data.email, 
                        debug_otp: res.data.debug_otp 
                    };
                }

                const { user: newUser, token: newToken } = res.data.data;
                setUser(newUser);
                setToken(newToken);
                localStorage.setItem('token', newToken);
                localStorage.setItem('user', JSON.stringify(newUser));

                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil',
                    text: 'Akun Anda berhasil didaftarkan!',
                    timer: 1500,
                    showConfirmButton: false,
                });
                return { success: true, user: newUser };
            }
        } catch (error) {
            const message = error.response?.data?.message || 'Pendaftaran gagal.';
            const errors = error.response?.data?.errors;
            Swal.fire({
                icon: 'error',
                title: 'Pendaftaran Gagal',
                text: errors ? Object.values(errors).flat().join('\n') : message,
            });
            return { success: false, message, errors };
        }
    };

    const verifyOtp = async (email, otp) => {
        try {
            clearApiCache();
            const res = await client.post('/auth/verify-otp', { email, otp });
            if (res.data.status === 'success') {
                clearApiCache();
                const { user: newUser, token: newToken } = res.data.data;
                setUser(newUser);
                setToken(newToken);
                localStorage.setItem('token', newToken);
                localStorage.setItem('user', JSON.stringify(newUser));

                Swal.fire({
                    icon: 'success',
                    title: 'Verifikasi Berhasil',
                    text: res.data.message || 'Akun Anda berhasil diverifikasi!',
                    timer: 2000,
                    showConfirmButton: false,
                });
                return { success: true, user: newUser };
            }
        } catch (error) {
            const message = error.response?.data?.message || 'Verifikasi gagal. Pastikan kode OTP benar.';
            Swal.fire({
                icon: 'error',
                title: 'Verifikasi Gagal',
                text: message,
            });
            return { success: false, message };
        }
    };

    const resendOtp = async (email) => {
        try {
            const res = await client.post('/auth/resend-otp', { email });
            if (res.data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Kode Terkirim',
                    text: res.data.message || 'Kode OTP baru telah dikirimkan ke email Anda.',
                    timer: 2000,
                    showConfirmButton: false,
                });
                return { success: true, debug_otp: res.data.debug_otp };
            }
        } catch (error) {
            const message = error.response?.data?.message || 'Gagal mengirim ulang kode OTP.';
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: message,
            });
            return { success: false, message, retry_after: error.response?.data?.retry_after };
        }
    };

    const logout = async (showAlert = true) => {
        clearApiCache();
        if (token) {
            try {
                await client.post('/auth/logout');
            } catch (err) {
                // Ignore logout network error
            }
        }
        clearApiCache();
        setUser(null);
        setToken(null);
        localStorage.removeItem('token');
        localStorage.removeItem('user');

        if (showAlert) {
            Swal.fire({
                icon: 'info',
                title: 'Logout',
                text: 'Anda telah keluar dari akun.',
                timer: 1500,
                showConfirmButton: false,
            });
        }
    };

    const isSuperadmin = user?.role === 'superadmin';
    const isAdminUnit = user?.role === 'admin_unit';
    const isPimpinan = user?.role === 'pimpinan';
    const isKonsumen = user?.role === 'konsumen' || !user?.role;
    const isStaffOrAdmin = isSuperadmin || isAdminUnit || isPimpinan;

    return (
        <AuthContext.Provider
            value={{
                user,
                token,
                loading,
                login,
                register,
                verifyOtp,
                resendOtp,
                logout,
                fetchUser,
                isSuperadmin,
                isAdminUnit,
                isPimpinan,
                isKonsumen,
                isStaffOrAdmin,
            }}
        >
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);
