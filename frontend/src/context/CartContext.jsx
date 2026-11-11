import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import client from '../api/client';
import { useAuth } from './AuthContext';
import Swal from 'sweetalert2';

const CartContext = createContext(null);

export const CartProvider = ({ children }) => {
    const { user, token } = useAuth();
    const [cartGroups, setCartGroups] = useState([]);
    const [cartSummary, setCartSummary] = useState({
        total_items: 0,
        subtotal: 0,
        total_berat_gram: 0,
        total_units: 0,
    });
    const [loading, setLoading] = useState(false);
    const [updatingCartId, setUpdatingCartId] = useState(null);

    // Derived cart count directly from summary to prevent duplicate state and cascading renders
    const cartCount = cartSummary?.total_items || 0;

    const fetchCart = useCallback(async (isInitial = false) => {
        if (!token || user?.role !== 'konsumen') {
            setCartGroups([]);
            setCartSummary({ total_items: 0, subtotal: 0, total_berat_gram: 0, total_units: 0 });
            return;
        }

        try {
            if (isInitial) setLoading(true);
            const res = await client.get('/cart');
            if (res.data.status === 'success') {
                setCartGroups(res.data.data.groups || []);
                setCartSummary(res.data.data.summary || { total_items: 0, subtotal: 0, total_berat_gram: 0, total_units: 0 });
            }
        } catch (error) {
            console.error('Failed to fetch cart:', error);
        } finally {
            if (isInitial) setLoading(false);
        }
    }, [token, user]);

    useEffect(() => {
        fetchCart(true);
    }, [fetchCart]);

    const addToCart = async (produk_id, jumlah = 1, catatan = '') => {
        if (!token) {
            Swal.fire({
                icon: 'warning',
                title: 'Perlu Login',
                text: 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.',
                showCancelButton: true,
                confirmButtonText: 'Login Sekarang',
                cancelButtonText: 'Nanti',
            }).then((res) => {
                if (res.isConfirmed) {
                    window.location.href = '/login';
                }
            });
            return false;
        }

        try {
            const res = await client.post('/cart', {
                produk_id,
                jumlah,
                catatan,
            });

            if (res.data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Masuk Keranjang',
                    text: 'Produk telah ditambahkan ke keranjang belanja Anda.',
                    timer: 1500,
                    showConfirmButton: false,
                });
                await fetchCart();
                return true;
            }
        } catch (error) {
            const msg = error.response?.data?.message || 'Gagal menambahkan produk ke keranjang.';
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: msg,
            });
            return false;
        }
    };

    const updateQty = async (cartId, jumlah) => {
        setUpdatingCartId(cartId);
        try {
            const res = await client.put(`/cart/${cartId}`, { jumlah });
            if (res.data.status === 'success') {
                await fetchCart(false);
                return true;
            }
        } catch (error) {
            const msg = error.response?.data?.message || 'Gagal mengubah jumlah produk.';
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: msg,
            });
            return false;
        } finally {
            setUpdatingCartId(null);
        }
    };

    const removeFromCart = async (cartId) => {
        setUpdatingCartId(cartId);
        try {
            const res = await client.delete(`/cart/${cartId}`);
            if (res.data.status === 'success') {
                await fetchCart(false);
                return true;
            }
        } catch (error) {
            const msg = error.response?.data?.message || 'Gagal menghapus produk dari keranjang.';
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: msg,
            });
            return false;
        } finally {
            setUpdatingCartId(null);
        }
    };

    return (
        <CartContext.Provider
            value={{
                cartGroups,
                cartCount,
                cartSummary,
                loading,
                updatingCartId,
                fetchCart,
                addToCart,
                updateQty,
                removeFromCart,
            }}
        >
            {children}
        </CartContext.Provider>
    );
};

export const useCart = () => useContext(CartContext);
