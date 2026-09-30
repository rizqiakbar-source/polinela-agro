import { STORAGE_BASE_URL } from '../api/client';

export const formatRupiah = (number) => {
    if (number === null || number === undefined) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number);
};

export const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
};

export const getImageUrl = (path, defaultPlaceholder = 'https://placehold.co/400x300?text=Produk+Agro') => {
    if (!path) return defaultPlaceholder;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    
    // Remove leading slash if any
    const cleanPath = path.startsWith('/') ? path.slice(1) : path;
    
    // If path points to uploads
    if (cleanPath.startsWith('uploads/')) {
        return `${STORAGE_BASE_URL}/${cleanPath}`;
    }
    
    return `${STORAGE_BASE_URL}/uploads/produk/${cleanPath}`;
};

export const getPaymentProofUrl = (path) => {
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    const cleanPath = path.startsWith('/') ? path.slice(1) : path;
    if (cleanPath.startsWith('uploads/')) {
        return `${STORAGE_BASE_URL}/${cleanPath}`;
    }
    return `${STORAGE_BASE_URL}/uploads/bukti_bayar/${cleanPath}`;
};

export const getOrderStatusBadge = (status) => {
    switch (status) {
        case 'menunggu_pembayaran':
            return { label: 'Menunggu Pembayaran', bg: 'bg-warning text-dark', icon: 'bi-clock-history' };
        case 'menunggu_verifikasi':
            return { label: 'Menunggu Verifikasi', bg: 'bg-info text-dark', icon: 'bi-hourglass-split' };
        case 'diproses':
            return { label: 'Sedang Diproses', bg: 'bg-primary text-white', icon: 'bi-gear-wide-connected' };
        case 'dikirim':
            return { label: 'Sedang Dikirim', bg: 'bg-primary text-white', icon: 'bi-truck' };
        case 'selesai':
            return { label: 'Selesai', bg: 'bg-success text-white', icon: 'bi-check-circle-fill' };
        case 'dibatalkan':
            return { label: 'Dibatalkan', bg: 'bg-danger text-white', icon: 'bi-x-circle-fill' };
        default:
            return { label: status || 'Unknown', bg: 'bg-secondary text-white', icon: 'bi-info-circle' };
    }
};

export const getPaymentStatusBadge = (status) => {
    switch (status) {
        case 'pending':
            return { label: 'Belum Dibayar', bg: 'bg-warning text-dark' };
        case 'menunggu_konfirmasi':
            return { label: 'Perlu Verifikasi', bg: 'bg-info text-dark' };
        case 'lunas':
            return { label: 'Lunas', bg: 'bg-success text-white' };
        case 'ditolak':
            return { label: 'Ditolak', bg: 'bg-danger text-white' };
        default:
            return { label: status || 'Unknown', bg: 'bg-secondary text-white' };
    }
};
