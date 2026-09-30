import React, { createContext, useContext, useState, useEffect } from 'react';
import client from '../api/client';
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
            console.error('Failed to fetch user:', err);
            logout(false);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchUser();
    }, [token]);

    const login = async (email, password) => {
        try {
            const res = await client.post('/auth/login', { email, password });
            if (res.data.status === 'success') {
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

    const logout = async (showAlert = true) => {
        if (token) {
            try {
                await client.post('/auth/logout');
            } catch (err) {
                // Ignore logout network error
            }
        }
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
