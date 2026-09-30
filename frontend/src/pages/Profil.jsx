import React, { useState, useRef, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import client, { STORAGE_BASE_URL } from '../api/client';
import Swal from 'sweetalert2';
import ImageCropperModal from '../components/ImageCropperModal';
import LocationPickerModal from '../components/LocationPickerModal';
import { 
    Camera, User, Phone, MapPin, Lock, Save, Mail, Shield, 
    Building2, Sun, Moon, CheckCircle2, AlertCircle, Copy, 
    Eye, EyeOff, Crop, RefreshCw, Calendar, Sparkles, KeyRound,
    HelpCircle, ExternalLink, Activity, Info, Palette, Check, X,
    Layers, Compass, Flame, Droplets, MoonStar, Zap
} from 'lucide-react';

const BANNER_CATEGORIES = [
    {
        id: 'agro',
        name: 'Perkebunan & Alam',
        icon: '🌿',
        presets: [
            { id: 'emerald', name: 'Hijau Polinela Agro', value: 'linear-gradient(135deg, #166534 0%, #15803d 50%, #047857 100%)' },
            { id: 'forest', name: 'Hutan Pinus Riset', value: 'linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%)' },
            { id: 'lime', name: 'Citrus & Daun Segar', value: 'linear-gradient(135deg, #3f6212 0%, #65a30d 50%, #84cc16 100%)' },
            { id: 'tea', name: 'Kebun Teh Puncak', value: 'linear-gradient(135deg, #14532d 0%, #16a34a 50%, #4ade80 100%)' },
            { id: 'coffee', name: 'Kopi Robusta Lampung', value: 'linear-gradient(135deg, #451a03 0%, #78350f 50%, #9a3412 100%)' },
            { id: 'cacao', name: 'Kakao & Cokelat Emas', value: 'linear-gradient(135deg, #3b1d11 0%, #713f12 50%, #ca8a04 100%)' },
        ]
    },
    {
        id: 'ocean',
        name: 'Samudra & Langit',
        icon: '🌊',
        presets: [
            { id: 'sapphire', name: 'Deep Ocean Sapphire', value: 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #06b6d4 100%)' },
            { id: 'pacific', name: 'Pacific Cyan Wave', value: 'linear-gradient(135deg, #0e7490 0%, #06b6d4 50%, #38bdf8 100%)' },
            { id: 'navy', name: 'Midnight Navy Blue', value: 'linear-gradient(135deg, #020617 0%, #0f172a 50%, #1e3a8a 100%)' },
            { id: 'arctic', name: 'Arctic Ice Glaze', value: 'linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #93c5fd 100%)' },
            { id: 'teal', name: 'Pantai Tropis Teal', value: 'linear-gradient(135deg, #064e3b 0%, #0d9488 50%, #2dd4bf 100%)' },
        ]
    },
    {
        id: 'sunset',
        name: 'Senja & Kehangatan',
        icon: '🌅',
        presets: [
            { id: 'sunset', name: 'Senja Tropis Lampung', value: 'linear-gradient(135deg, #9a3412 0%, #ea580c 50%, #f59e0b 100%)' },
            { id: 'flame', name: 'Crimson Ember Flame', value: 'linear-gradient(135deg, #881337 0%, #e11d48 50%, #f43f5e 100%)' },
            { id: 'gold', name: 'Panen Raya Golden', value: 'linear-gradient(135deg, #78350f 0%, #d97706 50%, #fbbf24 100%)' },
            { id: 'burgundy', name: 'Ruby Velvet Wine', value: 'linear-gradient(135deg, #4c0519 0%, #9f1239 50%, #e11d48 100%)' },
            { id: 'peach', name: 'Apricot Sunset Glow', value: 'linear-gradient(135deg, #c2410c 0%, #f97316 50%, #fdba74 100%)' },
        ]
    },
    {
        id: 'cyber',
        name: 'Cyber & Aurora',
        icon: '🔮',
        presets: [
            { id: 'aurora', name: 'Aurora Borealis Glow', value: 'linear-gradient(135deg, #064e3b 0%, #0284c7 50%, #9333ea 100%)' },
            { id: 'royal', name: 'Royal Amethyst Ungu', value: 'linear-gradient(135deg, #4c1d95 0%, #7c3aed 50%, #c084fc 100%)' },
            { id: 'cyber', name: 'Cyberpunk Neon Wave', value: 'linear-gradient(135deg, #831843 0%, #c026d3 50%, #3b82f6 100%)' },
            { id: 'rose', name: 'Rose Sakura Blossom', value: 'linear-gradient(135deg, #831843 0%, #db2777 50%, #f472b6 100%)' },
            { id: 'mystic', name: 'Mystic Indigo Nebula', value: 'linear-gradient(135deg, #312e81 0%, #4338ca 50%, #818cf8 100%)' },
        ]
    },
    {
        id: 'stealth',
        name: 'Monokrom & Elegan',
        icon: '🌑',
        presets: [
            { id: 'midnight', name: 'Midnight Carbon Slate', value: 'linear-gradient(135deg, #090d16 0%, #1e293b 50%, #334155 100%)' },
            { id: 'titanium', name: 'Titanium Metallic Gray', value: 'linear-gradient(135deg, #18181b 0%, #3f3f46 50%, #71717a 100%)' },
            { id: 'pureblack', name: 'Deep Black Onyx', value: 'linear-gradient(135deg, #000000 0%, #111827 50%, #1f2937 100%)' },
            { id: 'silver', name: 'Silver Platinum Steel', value: 'linear-gradient(135deg, #334155 0%, #64748b 50%, #94a3b8 100%)' },
        ]
    }
];

const SOLID_SWATCHES = [
    '#166534', '#15803d', '#14532d', '#047857', '#0e7490', '#1d4ed8', 
    '#1e3a8a', '#4338ca', '#6b21a8', '#831843', '#9f1239', '#9a3412', 
    '#c2410c', '#78350f', '#0f172a', '#18181b'
];

const Profil = () => {
    const { user, fetchUser } = useAuth();
    const { isDark, toggleTheme } = useTheme();
    const fileInputRef = useRef(null);

    // Profile Form States
    const [namaLengkap, setNamaLengkap] = useState(user?.nama_lengkap || user?.nama || '');
    const [email, setEmail] = useState(user?.email || '');
    const [noHp, setNoHp] = useState(user?.no_hp || '');
    const [alamat, setAlamat] = useState(user?.alamat || '');
    const [passwordBaru, setPasswordBaru] = useState('');
    const [passwordKonfirmasi, setPasswordKonfirmasi] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [showKonfirmasi, setShowKonfirmasi] = useState(false);
    const [activeTab, setActiveTab] = useState('biodata'); // 'biodata' | 'keamanan' | 'akun'
    const [submitting, setSubmitting] = useState(false);

    // Sync form state when user changes
    useEffect(() => {
        if (user) {
            setNamaLengkap(user.nama_lengkap || user.nama || '');
            setEmail(user.email || '');
            setNoHp(user.no_hp || '');
            setAlamat(user.alamat || '');
        }
    }, [user]);

    // Banner Customizer States
    const [bannerTheme, setBannerTheme] = useState(() => {
        return localStorage.getItem(`profile_banner_${user?.id || 'guest'}`) || BANNER_CATEGORIES[0].presets[0].value;
    });
    const [showBannerPicker, setShowBannerPicker] = useState(false);
    const [activeCategory, setActiveCategory] = useState('agro');
    const [customSolidColor, setCustomSolidColor] = useState('#166534');

    // Cropper States
    const [cropperOpen, setCropperOpen] = useState(false);
    const [rawImageSrc, setRawImageSrc] = useState(null);
    const [fotoPreview, setFotoPreview] = useState(null);
    const [fotoFile, setFotoFile] = useState(null);

    // Map Location Picker State
    const [showLocationModal, setShowLocationModal] = useState(false);

    const handleSelectLocation = (locData) => {
        setAlamat(locData.alamat);
        Swal.fire({
            icon: 'success',
            title: 'Lokasi Berhasil Dipilih!',
            text: `Alamat terisi: ${locData.alamat.substring(0, 50)}... (± ${locData.distanceKm} km dari Kampus Polinela)`,
            timer: 2000,
            showConfirmButton: false,
        });
    };

    // Handle Image Selection
    const handleFotoSelect = (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({ 
                icon: 'warning', 
                title: 'Ukuran Foto Terlalu Besar', 
                text: 'Maksimal ukuran foto adalah 5MB. Silakan pilih foto lain.' 
            });
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            setRawImageSrc(reader.result);
            setCropperOpen(true);
        };
        reader.readAsDataURL(file);

        // Reset input value so same file can be chosen again if needed
        e.target.value = '';
    };

    // Callback when crop completes in modal
    const handleCropComplete = (croppedBlob, croppedDataUrl, croppedFile) => {
        setFotoPreview(croppedDataUrl);
        setFotoFile(croppedFile);
        Swal.fire({
            icon: 'success',
            title: 'Foto Berhasil Di-crop!',
            text: 'Klik tombol "Simpan Perubahan" di bawah untuk menerapkan foto baru ke akun Anda.',
            timer: 2500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
        });
    };

    // Password Strength Evaluator
    const getPasswordStrength = (pass) => {
        if (!pass) return { score: 0, label: 'Kosong', color: 'secondary' };
        let score = 0;
        if (pass.length >= 6) score += 1;
        if (pass.length >= 8) score += 1;
        if (/[A-Z]/.test(pass)) score += 1;
        if (/[0-9]/.test(pass)) score += 1;
        if (/[^A-Za-z0-9]/.test(pass)) score += 1;

        if (score <= 2) return { score, label: 'Lemah', color: 'danger', width: '33%' };
        if (score <= 4) return { score, label: 'Sedang', color: 'warning', width: '66%' };
        return { score, label: 'Sangat Kuat', color: 'success', width: '100%' };
    };

    const passwordStrength = getPasswordStrength(passwordBaru);

    // Save Profile Information
    const handleUpdateProfile = async (e) => {
        e.preventDefault();
        
        if (passwordBaru && passwordBaru.length < 6) {
            Swal.fire({ 
                icon: 'warning', 
                title: 'Password Terlalu Pendek', 
                text: 'Password baru minimal harus 6 karakter.' 
            });
            return;
        }

        if (passwordBaru && passwordBaru !== passwordKonfirmasi) {
            Swal.fire({ 
                icon: 'warning', 
                title: 'Password Tidak Cocok', 
                text: 'Password baru dan konfirmasi password harus sama persis.' 
            });
            return;
        }

        setSubmitting(true);
        try {
            const formData = new FormData();
            formData.append('nama', namaLengkap);
            formData.append('nama_lengkap', namaLengkap);
            formData.append('email', email);
            formData.append('no_hp', noHp);
            formData.append('alamat', alamat);

            if (passwordBaru) {
                formData.append('password', passwordBaru);
            }

            if (fotoFile) {
                formData.append('foto', fotoFile);
            }

            const res = await client.post('/auth/profile', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            if (res.data.status === 'success' || res.data.success) {
                const titleText = passwordBaru ? 'Kata Sandi & Profil Berhasil Diperbarui! 🎉' : 'Profil Berhasil Diperbarui! 🎉';
                const descText = passwordBaru 
                    ? 'Kata sandi baru telah aktif di database. Gunakan kata sandi baru ini saat login ke sistem.' 
                    : 'Seluruh data profil dan preferensi akun Anda telah disimpan.';

                Swal.fire({
                    icon: 'success',
                    title: titleText,
                    text: descText,
                    timer: 3000,
                    showConfirmButton: false,
                });
                await fetchUser();
                setPasswordBaru('');
                setPasswordKonfirmasi('');
                setFotoFile(null);
            }
        } catch (error) {
            const msg = error.response?.data?.message || 'Gagal memperbarui profil. Periksa koneksi internet Anda.';
            Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: msg });
        } finally {
            setSubmitting(false);
        }
    };

    // Copy Helper
    const handleCopyText = (text, label) => {
        navigator.clipboard.writeText(text);
        Swal.fire({
            icon: 'info',
            title: `${label} Disalin!`,
            text: text,
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
        });
    };

    // Avatar source resolution
    let avatarUrl = fotoPreview;
    if (!avatarUrl && user?.foto) {
        avatarUrl = user.foto.startsWith('http') 
            ? user.foto 
            : `${STORAGE_BASE_URL}/uploads/avatars/${user.foto}`;
    }
    if (!avatarUrl && (user?.foto_url || user?.avatar_url)) {
        avatarUrl = user.foto_url || user.avatar_url;
    }

    const getRoleBadge = (role) => {
        switch(role) {
            case 'superadmin': return { label: 'Super Administrator', color: '#ef4444', bg: 'rgba(239,68,68,0.12)', icon: '👑' };
            case 'admin_unit': return { label: 'Admin Unit Usaha', color: '#3b82f6', bg: 'rgba(59,130,246,0.12)', icon: '🏢' };
            case 'pimpinan': return { label: 'Pimpinan / Eksekutif', color: '#8b5cf6', bg: 'rgba(139,92,246,0.12)', icon: '🏛️' };
            default: return { label: 'Pelanggan Terverifikasi', color: '#22c55e', bg: 'rgba(34,197,94,0.12)', icon: '🌱' };
        }
    };

    const roleBadge = getRoleBadge(user?.role);

    return (
        <div className="py-4 min-vh-100" style={{ background: 'var(--bg-body)' }}>
            <div className="container" style={{ maxWidth: '840px' }}>
                
                {/* Header Title */}
                <div className="d-flex justify-content-between align-items-center mb-4 animate-fade-in">
                    <div>
                        <h3 className="fw-bold mb-1 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                            <User size={26} style={{ color: 'var(--color-primary)' }} />
                            Pusat Pengaturan Akun & Profil
                        </h3>
                        <p className="mb-0 small text-muted">
                            Kelola identitas, foto profil, preferensi keamanan, dan kontak resmi Polinela Agro Digital
                        </p>
                    </div>
                    <button
                        className="theme-toggle shadow-sm"
                        onClick={toggleTheme}
                        title={isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'}
                    >
                        {isDark ? <Sun size={18} /> : <Moon size={18} />}
                    </button>
                </div>

                {/* Profile Hero Card */}
                <div className="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 animate-fade-in" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                    {/* Top Decorative Banner with Theme & Color Picker */}
                    <div 
                        className="px-4 py-3 text-white position-relative"
                        style={{
                            background: bannerTheme,
                            minHeight: '110px',
                            transition: 'background 0.4s ease'
                        }}
                    >
                        <div className="d-flex justify-content-between align-items-start">
                            <span className="small fw-semibold text-white d-flex align-items-center gap-1" style={{ textShadow: '0 1px 3px rgba(0,0,0,0.5)' }}>
                                🌿 Profil Resmi • Polinela Agro Digital
                            </span>

                            <span className="small text-white-50 d-none d-sm-inline-flex align-items-center gap-1" style={{ textShadow: '0 1px 2px rgba(0,0,0,0.5)' }}>
                                <Calendar size={13} /> Terdaftar: {user?.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }) : '2026'}
                            </span>
                        </div>

                        {/* Liquid Glass Logo Button in bottom-right corner */}
                        <button
                            type="button"
                            onClick={() => setShowBannerPicker(true)}
                            className="liquid-glass-btn position-absolute bottom-0 end-0 mb-2 me-3 rounded-circle"
                            style={{ 
                                width: '40px',
                                height: '40px',
                                zIndex: 10
                            }}
                            title="Pilih tema & kustomisasi warna banner sampul"
                        >
                            <Palette size={20} className="text-white" />
                        </button>
                    </div>

                    {/* Avatar & Main Info Strip */}
                    <div className="px-4 pb-4">
                        <div className="d-flex flex-column flex-md-row align-items-center align-items-md-center gap-4">
                            
                            {/* Avatar with Crop & Hover Overlay - negative margin only on avatar */}
                            <div className="position-relative" style={{ marginTop: '-50px' }}>
                                <div 
                                    className="profile-avatar-wrapper shadow-lg position-relative rounded-circle overflow-hidden" 
                                    style={{ 
                                        width: '105px', 
                                        height: '105px', 
                                        border: '4px solid var(--bg-card)',
                                        background: 'var(--bg-elevated)',
                                        cursor: 'pointer' 
                                    }}
                                    onClick={() => fileInputRef.current?.click()}
                                    title="Klik untuk memilih & crop foto baru"
                                >
                                    {avatarUrl ? (
                                        <img 
                                            src={avatarUrl} 
                                            alt="Avatar Profil" 
                                            className="w-100 h-100" 
                                            style={{ objectFit: 'cover' }}
                                        />
                                    ) : (
                                        <div className="w-100 h-100 d-flex align-items-center justify-content-center fw-bold fs-1 text-white" style={{ background: 'var(--gradient-primary)' }}>
                                            {(user?.nama_lengkap || user?.nama || 'U')[0].toUpperCase()}
                                        </div>
                                    )}

                                    {/* Hover Overlay Camera */}
                                    <div 
                                        className="position-absolute inset-0 d-flex flex-column align-items-center justify-content-center text-white avatar-overlay-hover"
                                        style={{
                                            background: 'rgba(15, 23, 42, 0.65)',
                                            opacity: 0,
                                            transition: 'opacity 0.2s ease',
                                            top: 0, left: 0, right: 0, bottom: 0
                                        }}
                                        onMouseEnter={(e) => e.currentTarget.style.opacity = '1'}
                                        onMouseLeave={(e) => e.currentTarget.style.opacity = '0'}
                                    >
                                        <Camera size={20} className="mb-1" />
                                        <span style={{ fontSize: '10px', fontWeight: 'bold' }}>Ganti Foto</span>
                                    </div>
                                </div>

                                {/* Crop Tool Badge Trigger */}
                                <button
                                    type="button"
                                    onClick={() => fileInputRef.current?.click()}
                                    className="btn btn-sm btn-success rounded-circle position-absolute bottom-0 end-0 p-1 d-flex align-items-center justify-content-center shadow-sm"
                                    style={{ width: '32px', height: '32px', border: '2px solid var(--bg-card)' }}
                                    title="Pilih & Crop Foto"
                                >
                                    <Crop size={14} />
                                </button>
                            </div>

                            {/* Hidden file input */}
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="image/jpg,image/jpeg,image/png,image/webp"
                                className="d-none"
                                onChange={handleFotoSelect}
                            />

                            {/* User Bio Details - Completely in the clean white/card area */}
                            <div className="text-center text-md-start flex-grow-1 pt-2 pt-md-3">
                                <div className="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                                    <h4 className="fw-bold mb-0" style={{ color: 'var(--text-heading)' }}>
                                        {namaLengkap || 'Pengguna'}
                                    </h4>
                                    <CheckCircle2 size={18} className="text-success" title="Akun Terverifikasi" />
                                </div>

                                <div className="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-3 small text-muted mb-2">
                                    <span className="d-flex align-items-center gap-1">
                                        <Mail size={13} /> {user?.email}
                                    </span>
                                    {user?.no_hp && (
                                        <span className="d-flex align-items-center gap-1">
                                            <Phone size={13} /> {user.no_hp}
                                        </span>
                                    )}
                                </div>

                                <div className="d-flex gap-2 justify-content-center justify-content-md-start flex-wrap">
                                    <span className="badge rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" style={{ background: roleBadge.bg, color: roleBadge.color, fontSize: '12px', fontWeight: '600' }}>
                                        <Shield size={13} />
                                        {roleBadge.label}
                                    </span>
                                    {user?.unit && (
                                        <span className="badge rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" style={{ background: 'var(--bg-badge)', color: 'var(--color-primary)', fontSize: '12px', fontWeight: '600' }}>
                                            <Building2 size={13} />
                                            {user.unit.nama_unit}
                                        </span>
                                    )}
                                </div>
                            </div>

                            {/* Photo status alert if ready to save */}
                            {fotoFile && (
                                <div className="p-2 px-3 rounded-3 bg-warning bg-opacity-10 border border-warning text-warning-emphasis small text-center mt-2 mt-md-0">
                                    <Sparkles size={14} className="me-1 text-warning" />
                                    <span>Foto baru siap disimpan</span>
                                </div>
                            )}

                        </div>
                    </div>
                </div>

                {/* Tabs Header Navigation */}
                <div className="d-flex border-bottom mb-4 gap-2 overflow-x-auto" style={{ borderColor: 'var(--border-color)' }}>
                    <button
                        onClick={() => setActiveTab('biodata')}
                        className={`btn px-4 py-2 fw-semibold rounded-top-3 border-0 border-bottom border-3 d-flex align-items-center gap-2 ${
                            activeTab === 'biodata' 
                                ? 'border-success text-success bg-success bg-opacity-10' 
                                : 'border-transparent text-muted'
                        }`}
                        style={{ transition: 'all 0.2s ease' }}
                    >
                        <User size={16} /> Biodata & Kontak
                    </button>
                    <button
                        onClick={() => setActiveTab('keamanan')}
                        className={`btn px-4 py-2 fw-semibold rounded-top-3 border-0 border-bottom border-3 d-flex align-items-center gap-2 ${
                            activeTab === 'keamanan' 
                                ? 'border-success text-success bg-success bg-opacity-10' 
                                : 'border-transparent text-muted'
                        }`}
                        style={{ transition: 'all 0.2s ease' }}
                    >
                        <Lock size={16} /> Keamanan & Sandi
                    </button>
                    <button
                        onClick={() => setActiveTab('akun')}
                        className={`btn px-4 py-2 fw-semibold rounded-top-3 border-0 border-bottom border-3 d-flex align-items-center gap-2 ${
                            activeTab === 'akun' 
                                ? 'border-success text-success bg-success bg-opacity-10' 
                                : 'border-transparent text-muted'
                        }`}
                        style={{ transition: 'all 0.2s ease' }}
                    >
                        <Info size={16} /> Ringkasan Akun
                    </button>
                </div>

                {/* Tab Content Box */}
                <div className="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                    <form onSubmit={handleUpdateProfile}>

                        {/* TAB 1: BIODATA & KONTAK */}
                        {activeTab === 'biodata' && (
                            <div className="animate-fade-in">
                                <h5 className="fw-bold mb-3 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                                    <User size={18} className="text-success" />
                                    Informasi Pribadi & Kontak Pengiriman
                                </h5>
                                <p className="text-muted small mb-4">
                                    Data ini digunakan untuk pencetakan label resi pengiriman, invoice pesanan, dan konfirmasi WhatsApp.
                                </p>

                                <div className="mb-3">
                                    <label className="form-label small fw-semibold" style={{ color: 'var(--text-body)' }}>
                                        Nama Lengkap <span className="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        className="form-control rounded-3 py-2 px-3"
                                        required
                                        placeholder="Nama lengkap sesuai KTP"
                                        value={namaLengkap}
                                        onChange={(e) => setNamaLengkap(e.target.value)}
                                    />
                                </div>

                                <div className="row g-3 mb-3">
                                    <div className="col-md-6">
                                        <label className="form-label small fw-semibold" style={{ color: 'var(--text-body)' }}>
                                            Alamat Email (Login & Notifikasi) <span className="text-danger">*</span>
                                        </label>
                                        <div className="input-group">
                                            <input 
                                                type="email" 
                                                className="form-control rounded-3 py-2 px-3" 
                                                required
                                                value={email}
                                                onChange={(e) => setEmail(e.target.value)}
                                                placeholder="email@domain.com"
                                            />
                                        </div>
                                        <small className="text-muted" style={{ fontSize: '11px' }}>Digunakan untuk login akun dan menerima notifikasi email tagihan/resi</small>
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label small fw-semibold" style={{ color: 'var(--text-body)' }}>
                                            <Phone size={14} className="me-1 text-success" />
                                            Nomor WhatsApp Aktif
                                        </label>
                                        <input
                                            type="text"
                                            className="form-control rounded-3 py-2 px-3"
                                            value={noHp}
                                            onChange={(e) => setNoHp(e.target.value)}
                                            placeholder="Contoh: 081234567890"
                                        />
                                        <small className="text-muted" style={{ fontSize: '11px' }}>Untuk notifikasi resi kurir dan info panen</small>
                                    </div>
                                </div>

                                <div className="mb-4">
                                    <div className="d-flex justify-content-between align-items-center mb-1">
                                        <label className="form-label small fw-semibold mb-0" style={{ color: 'var(--text-body)' }}>
                                            <MapPin size={14} className="me-1 text-danger" />
                                            Alamat Pengiriman Default
                                        </label>
                                        <button
                                            type="button"
                                            onClick={() => setShowLocationModal(true)}
                                            className="btn btn-link text-success p-0 small text-decoration-none fw-semibold d-flex align-items-center gap-1"
                                        >
                                            <Compass size={14} />
                                            <span>Pilih di Peta (GPS)</span>
                                        </button>
                                    </div>
                                    <textarea
                                        className="form-control rounded-3 py-2 px-3"
                                        rows="3"
                                        value={alamat}
                                        onChange={(e) => setAlamat(e.target.value)}
                                        placeholder="Nama Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten, Kode Pos"
                                    ></textarea>
                                </div>
                            </div>
                        )}

                        {/* TAB 2: KEAMANAN & SANDI */}
                        {activeTab === 'keamanan' && (
                            <div className="animate-fade-in">
                                <h5 className="fw-bold mb-3 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                                    <KeyRound size={18} className="text-success" />
                                    Ubah Kata Sandi Akun
                                </h5>
                                <p className="text-muted small mb-4">
                                    Biarkan kolom kata sandi kosong jika Anda tidak berencana mengganti kata sandi saat ini.
                                </p>

                                <div className="row g-3 mb-3">
                                    <div className="col-md-6">
                                        <label className="form-label small fw-semibold" style={{ color: 'var(--text-body)' }}>Kata Sandi Baru</label>
                                        <div className="input-group">
                                            <input
                                                type={showPassword ? 'text' : 'password'}
                                                className="form-control rounded-start-3 py-2 px-3"
                                                placeholder="Minimal 6 karakter"
                                                value={passwordBaru}
                                                onChange={(e) => setPasswordBaru(e.target.value)}
                                            />
                                            <button 
                                                type="button" 
                                                className="btn btn-outline-secondary"
                                                onClick={() => setShowPassword(!showPassword)}
                                            >
                                                {showPassword ? <EyeOff size={15} /> : <Eye size={15} />}
                                            </button>
                                        </div>
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label small fw-semibold" style={{ color: 'var(--text-body)' }}>Konfirmasi Kata Sandi Baru</label>
                                        <div className="input-group">
                                            <input
                                                type={showKonfirmasi ? 'text' : 'password'}
                                                className="form-control rounded-start-3 py-2 px-3"
                                                placeholder="Ulangi kata sandi baru"
                                                value={passwordKonfirmasi}
                                                onChange={(e) => setPasswordKonfirmasi(e.target.value)}
                                            />
                                            <button 
                                                type="button" 
                                                className="btn btn-outline-secondary"
                                                onClick={() => setShowKonfirmasi(!showKonfirmasi)}
                                            >
                                                {showKonfirmasi ? <EyeOff size={15} /> : <Eye size={15} />}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {/* Password Strength Meter */}
                                {passwordBaru && (
                                    <div className="p-3 mb-4 rounded-3 bg-light border">
                                        <div className="d-flex justify-content-between align-items-center mb-1 small">
                                            <span className="fw-semibold">Kekuatan Sandi:</span>
                                            <span className={`badge bg-${passwordStrength.color}`}>{passwordStrength.label}</span>
                                        </div>
                                        <div className="progress" style={{ height: '6px' }}>
                                            <div 
                                                className={`progress-bar bg-${passwordStrength.color}`} 
                                                role="progressbar" 
                                                style={{ width: passwordStrength.width, transition: 'width 0.3s ease' }}
                                            ></div>
                                        </div>
                                        <div className="mt-2 small text-muted d-flex flex-column gap-1" style={{ fontSize: '11px' }}>
                                            <span className={passwordBaru.length >= 6 ? 'text-success' : ''}>
                                                • Minimal 6 karakter ({passwordBaru.length}/6)
                                            </span>
                                            <span className={passwordBaru === passwordKonfirmasi && passwordKonfirmasi.length > 0 ? 'text-success' : 'text-muted'}>
                                                • Konfirmasi sandi cocok {passwordBaru === passwordKonfirmasi && passwordKonfirmasi.length > 0 ? '✓' : ''}
                                            </span>
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}

                        {/* TAB 3: RINGKASAN AKUN & PERMISSION */}
                        {activeTab === 'akun' && (
                            <div className="animate-fade-in">
                                <h5 className="fw-bold mb-3 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                                    <Shield size={18} className="text-success" />
                                    Informasi Status Akun & Hak Akses
                                </h5>
                                
                                <div className="row g-3 mb-4">
                                    <div className="col-sm-6">
                                        <div className="p-3 rounded-3 bg-body border">
                                            <span className="text-muted small d-block mb-1">ID Pengguna</span>
                                            <div className="d-flex align-items-center justify-content-between">
                                                <code className="fw-bold fs-6">USER-#{user?.id || '00'}</code>
                                                <button 
                                                    type="button" 
                                                    className="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0"
                                                    onClick={() => handleCopyText(`USER-#${user?.id}`, 'ID Pengguna')}
                                                >
                                                    <Copy size={12} />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="col-sm-6">
                                        <div className="p-3 rounded-3 bg-body border">
                                            <span className="text-muted small d-block mb-1">Hak Akses Sistem</span>
                                            <span className="fw-bold fs-6 text-capitalize" style={{ color: roleBadge.color }}>
                                                {roleBadge.label}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="col-sm-6">
                                        <div className="p-3 rounded-3 bg-body border">
                                            <span className="text-muted small d-block mb-1">Unit Usaha Perkebunan</span>
                                            <span className="fw-bold fs-6">
                                                {user?.unit?.nama_unit || 'Semua Unit (Konsumen Umum)'}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="col-sm-6">
                                        <div className="p-3 rounded-3 bg-body border">
                                            <span className="text-muted small d-block mb-1">Status Keamanan Akun</span>
                                            <span className="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">
                                                ● Aktif & Terlindungi
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Save Action Footer */}
                        <div className="d-flex flex-wrap align-items-center justify-content-between pt-3 border-top gap-3" style={{ borderColor: 'var(--border-color)' }}>
                            <small className="text-muted">
                                Terakhir disinkronkan: {new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} WIB
                            </small>

                            <button
                                type="submit"
                                disabled={submitting}
                                className="btn btn-agro rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow"
                            >
                                {submitting ? (
                                    <>
                                        <div className="spinner-border spinner-border-sm" role="status"></div>
                                        <span>Menyimpan...</span>
                                    </>
                                ) : (
                                    <>
                                        <Save size={18} />
                                        <span>Simpan Perubahan</span>
                                    </>
                                )}
                            </button>
                        </div>

                    </form>
                </div>

            </div>

            {/* High-Resolution Interactive Image Cropper Modal */}
            <ImageCropperModal
                isOpen={cropperOpen}
                imageSrc={rawImageSrc}
                onClose={() => {
                    setCropperOpen(false);
                    setRawImageSrc(null);
                }}
                onCropComplete={handleCropComplete}
                cropShapeDefault="circle"
            />

            {/* Banner Theme Customizer Modal (Centered, Never Cut Off) */}
            {showBannerPicker && (
                <div 
                    className="modal show d-block animate-fade-in" 
                    style={{ backgroundColor: 'rgba(15, 23, 42, 0.75)', zIndex: 1060, backdropFilter: 'blur(6px)' }}
                    tabIndex="-1"
                >
                    <div className="modal-dialog modal-dialog-centered modal-lg">
                        <div className="modal-content rounded-4 border-0 shadow-2xl overflow-hidden" style={{ background: 'var(--bg-card)', color: 'var(--text-body)' }}>
                            
                            {/* Modal Header */}
                            <div className="modal-header border-bottom px-4 py-3" style={{ borderColor: 'var(--border-color)' }}>
                                <div className="d-flex align-items-center gap-2">
                                    <div className="p-2 rounded-3 bg-success bg-opacity-10 text-success">
                                        <Palette size={20} />
                                    </div>
                                    <div>
                                        <h5 className="modal-title fw-bold mb-0" style={{ color: 'var(--text-heading)' }}>
                                            Koleksi Tema Sampul Profil
                                        </h5>
                                        <small className="text-muted">Pilih gradien tema modern atau tentukan warna solid favorit Anda</small>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    className="btn-close" 
                                    onClick={() => setShowBannerPicker(false)}
                                    style={{ filter: 'var(--bs-btn-close-filter, none)' }}
                                ></button>
                            </div>

                            {/* Modal Body */}
                            <div className="modal-body p-4">
                                
                                {/* Live Banner Preview Box */}
                                <div className="mb-4">
                                    <label className="form-label small fw-semibold text-muted mb-2">Pratinjau Langsung Tema Banner</label>
                                    <div 
                                        className="rounded-3 p-3 text-white shadow-sm position-relative overflow-hidden"
                                        style={{ 
                                            background: bannerTheme, 
                                            minHeight: '75px',
                                            transition: 'background 0.3s ease' 
                                        }}
                                    >
                                        <div className="d-flex justify-content-between align-items-center">
                                            <span className="small fw-bold text-white d-flex align-items-center gap-1" style={{ textShadow: '0 1px 3px rgba(0,0,0,0.6)' }}>
                                                🌿 {namaLengkap || 'Pengguna'} • Polinela Agro Digital
                                            </span>
                                            <span className="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1 small">
                                                Tema Aktif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {/* Category Switcher Tabs */}
                                <div className="d-flex gap-2 mb-3 overflow-x-auto pb-1" style={{ scrollbarWidth: 'none' }}>
                                    {BANNER_CATEGORIES.map((cat) => (
                                        <button
                                            key={cat.id}
                                            type="button"
                                            onClick={() => setActiveCategory(cat.id)}
                                            className={`btn btn-sm rounded-pill px-3 py-2 text-nowrap d-flex align-items-center gap-1 ${
                                                activeCategory === cat.id 
                                                    ? 'btn-success fw-bold shadow-sm' 
                                                    : 'btn-outline-secondary'
                                            }`}
                                            style={{ fontSize: '12px' }}
                                        >
                                            <span>{cat.icon}</span>
                                            <span>{cat.name}</span>
                                        </button>
                                    ))}
                                    <button
                                        type="button"
                                        onClick={() => setActiveCategory('solid')}
                                        className={`btn btn-sm rounded-pill px-3 py-2 text-nowrap d-flex align-items-center gap-1 ${
                                            activeCategory === 'solid' 
                                                ? 'btn-success fw-bold shadow-sm' 
                                                : 'btn-outline-secondary'
                                        }`}
                                        style={{ fontSize: '12px' }}
                                    >
                                        <span>🎨</span>
                                        <span>Warna Solid</span>
                                    </button>
                                </div>

                                {/* Active Category Presets Grid */}
                                {activeCategory !== 'solid' && (
                                    <div className="row g-3 mb-4" style={{ maxHeight: '280px', overflowY: 'auto' }}>
                                        {BANNER_CATEGORIES.find((c) => c.id === activeCategory)?.presets.map((p) => {
                                            const isSelected = bannerTheme === p.value;
                                            return (
                                                <div key={p.id} className="col-md-6">
                                                    <button
                                                        type="button"
                                                        onClick={() => {
                                                            setBannerTheme(p.value);
                                                            localStorage.setItem(`profile_banner_${user?.id || 'guest'}`, p.value);
                                                        }}
                                                        className="w-100 text-start p-3 rounded-4 border-0 position-relative d-flex align-items-center justify-content-between shadow-sm transition-all"
                                                        style={{ 
                                                            background: p.value, 
                                                            color: '#ffffff',
                                                            minHeight: '56px',
                                                            fontSize: '13px',
                                                            fontWeight: '600',
                                                            textShadow: '0 1px 3px rgba(0,0,0,0.7)',
                                                            outline: isSelected ? '3px solid #22c55e' : '1px solid rgba(255,255,255,0.2)',
                                                            outlineOffset: '2px',
                                                            transform: isSelected ? 'scale(1.02)' : 'none'
                                                        }}
                                                    >
                                                        <span>{p.name}</span>
                                                        {isSelected && (
                                                            <span className="badge bg-white text-dark rounded-circle p-1 d-flex align-items-center justify-content-center shadow">
                                                                <Check size={14} className="text-success" />
                                                            </span>
                                                        )}
                                                    </button>
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}

                                {/* Solid Color Swatches Grid */}
                                {activeCategory === 'solid' && (
                                    <div className="mb-4">
                                        <label className="form-label small fw-semibold text-muted mb-2">Pilihan Warna Solid Populer:</label>
                                        <div className="d-flex flex-wrap gap-2 mb-3">
                                            {SOLID_SWATCHES.map((hex) => {
                                                const isSelected = bannerTheme === hex;
                                                return (
                                                    <button
                                                        key={hex}
                                                        type="button"
                                                        onClick={() => {
                                                            setBannerTheme(hex);
                                                            setCustomSolidColor(hex);
                                                            localStorage.setItem(`profile_banner_${user?.id || 'guest'}`, hex);
                                                        }}
                                                        className="rounded-circle border-0 position-relative d-flex align-items-center justify-content-center shadow-sm"
                                                        style={{ 
                                                            background: hex, 
                                                            width: '40px', 
                                                            height: '40px',
                                                            outline: isSelected ? '3px solid #22c55e' : '1px solid rgba(255,255,255,0.25)',
                                                            outlineOffset: '2px',
                                                            transition: 'transform 0.2s ease',
                                                            transform: isSelected ? 'scale(1.1)' : 'none'
                                                        }}
                                                        title={hex}
                                                    >
                                                        {isSelected && <Check size={18} className="text-white" />}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}

                                {/* Custom Color Picker Bar */}
                                <div className="p-3 rounded-3 bg-light border d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div className="d-flex align-items-center gap-2">
                                        <label className="form-label small fw-semibold mb-0">Pemilih Warna Kustom:</label>
                                        <input
                                            type="color"
                                            className="form-control form-control-color border-0 p-0 rounded-circle"
                                            style={{ width: '36px', height: '36px', cursor: 'pointer' }}
                                            value={customSolidColor}
                                            onChange={(e) => {
                                                setCustomSolidColor(e.target.value);
                                                setBannerTheme(e.target.value);
                                                localStorage.setItem(`profile_banner_${user?.id || 'guest'}`, e.target.value);
                                            }}
                                        />
                                        <code className="px-2 py-1 bg-white rounded border small font-monospace">{customSolidColor}</code>
                                    </div>
                                    <button
                                        type="button"
                                        className="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                        onClick={() => {
                                            const defaultVal = BANNER_CATEGORIES[0].presets[0].value;
                                            setBannerTheme(defaultVal);
                                            localStorage.setItem(`profile_banner_${user?.id || 'guest'}`, defaultVal);
                                        }}
                                    >
                                        <RefreshCw size={13} className="me-1" /> Reset ke Hijau Polinela
                                    </button>
                                </div>

                            </div>

                            {/* Modal Footer */}
                            <div className="modal-footer border-top px-4 py-3" style={{ borderColor: 'var(--border-color)' }}>
                                <button 
                                    type="button" 
                                    className="btn btn-agro rounded-pill px-4 fw-bold shadow"
                                    onClick={() => {
                                        setShowBannerPicker(false);
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Tema Banner Diterapkan! 🎨',
                                            text: 'Warna baru telah tersimpan pada profil Anda.',
                                            timer: 1800,
                                            showConfirmButton: false,
                                            toast: true,
                                            position: 'top-end',
                                        });
                                    }}
                                >
                                    <Check size={16} className="me-1" /> Selesai & Terapkan
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            )}

            {/* Interactive Map Location Picker Modal */}
            <LocationPickerModal
                isOpen={showLocationModal}
                onClose={() => setShowLocationModal(false)}
                onSelectLocation={handleSelectLocation}
                currentAddress={alamat}
            />
        </div>
    );
};

export default Profil;
