import React, { useState, useRef, useEffect } from 'react';
import { Link } from 'react-router-dom';
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
    Layers, Compass, Zap,
    BarChart3, ArrowRight, ShieldCheck, ShoppingBag, Store, Package, Sprout
} from 'lucide-react';
import { BANNER_CATEGORIES, SOLID_SWATCHES } from '../data/profileBannerThemes';

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
                if (res.data.requires_otp) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Verifikasi Email Baru Diperlukan ✉️',
                        text: res.data.message || 'Silakan masukkan 6 digit kode OTP yang telah dikirimkan ke alamat email baru Anda.',
                        input: 'text',
                        inputPlaceholder: 'Masukkan 6 Digit OTP',
                        confirmButtonText: 'Verifikasi Sekarang',
                        confirmButtonColor: '#16a34a',
                        showCancelButton: true,
                        cancelButtonText: 'Nanti Saja',
                        showLoaderOnConfirm: true,
                        preConfirm: async (otpVal) => {
                            const trimmed = (otpVal || '').trim();
                            if (!trimmed || trimmed.length !== 6) {
                                Swal.showValidationMessage('Kode OTP harus terdiri dari 6 angka.');
                                return false;
                            }
                            try {
                                const vRes = await client.post('/auth/verify-otp', {
                                    email: res.data.email,
                                    otp: trimmed,
                                });
                                return vRes.data;
                            } catch (err) {
                                Swal.showValidationMessage(err.response?.data?.message || 'Kode OTP salah atau telah kedaluwarsa.');
                                return false;
                            }
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Email Terverifikasi!',
                                text: 'Alamat email baru Anda telah berhasil diverifikasi.',
                                timer: 2000,
                                showConfirmButton: false,
                            });
                        }
                    });
                } else {
                    const titleText = passwordBaru ? 'Kata Sandi & Profil Berhasil Diperbarui!' : 'Profil Berhasil Diperbarui!';
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
                }

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

    const getRoleProfileConfig = (currUser) => {
        const role = currUser?.role || 'konsumen';
        const unitName = currUser?.unit?.nama_unit;

        switch(role) {
            case 'pimpinan':
                return {
                    roleKey: 'pimpinan',
                    badgeText: 'Pimpinan / Eksekutif',
                    badgeBg: 'rgba(139,92,246,0.12)',
                    badgeColor: '#8b5cf6',
                    heroBadgeIcon: Layers,
                    heroBadgeText: 'Akun Resmi Pimpinan • Polinela Agro Digital',
                    headerSubtitle: 'Kelola identitas resmi pimpinan, foto profil eksekutif, dan keamanan akun institusi',
                    banner: {
                        gradient: 'linear-gradient(135deg, #064e3b 0%, #047857 50%, #0d9488 100%)',
                        badgeTag: 'Hak Akses Eksekutif',
                        badgeTagClass: 'bg-warning text-dark',
                        title: 'Pusat Akun & Profil Pimpinan Polinela',
                        desc: 'Halaman ini khusus untuk mengatur foto profil, identitas resmi, dan kata sandi akun Pimpinan Anda. Untuk memantau omset, performa unit Teaching Factory (TEFA), dan laporan eksekutif institusi, silakan akses Dashboard Pimpinan.',
                        buttonText: 'Buka Dashboard Pimpinan',
                        buttonTo: '/admin',
                        buttonIcon: BarChart3
                    },
                    biodata: {
                        title: 'Informasi Identitas & Kontak Resmi Pimpinan',
                        subtitle: 'Data profil resmi pimpinan eksekutif untuk keperluan autentikasi dan komunikasi institusi Polinela Agro Digital.',
                        namaLabel: 'Nama Lengkap Beserta Gelar',
                        namaPlaceholder: 'Contoh: Dr. Ir. H. Nama Pimpinan, M.P.',
                        emailHelp: 'Digunakan untuk login pimpinan dan menerima ringkasan laporan eksekutif berkala.',
                        phoneLabel: 'Nomor Kontak Dinas / WhatsApp',
                        phoneHelp: 'Untuk koordinasi dinas darurat dan autentikasi akun pimpinan.',
                        alamatLabel: 'Alamat Kantor / Domisili Pimpinan',
                        alamatPlaceholder: 'Contoh: Gedung Direktorat / Rektorat Polinela, Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung'
                    },
                    keamanan: {
                        subtitle: 'Pastikan kata sandi akun pimpinan kuat untuk melindungi integritas dan kerahasiaan data eksekutif kampus.'
                    },
                    akun: {
                        unitLabel: 'Seluruh Unit Teaching Factory (Pengawasan Eksekutif)',
                        actionCard: {
                            bgClass: 'bg-primary bg-opacity-10 border-primary border-opacity-25',
                            textClass: 'text-primary',
                            icon: BarChart3,
                            title: 'Akses Dashboard Eksekutif Pimpinan',
                            desc: 'Tinjau statistik omset, perbandingan penjualan per-unit TEFA, dan unduh laporan resmi institusi.',
                            btnText: 'Buka Dashboard Pimpinan',
                            btnClass: 'btn-primary',
                            btnTo: '/admin'
                        }
                    }
                };

            case 'superadmin':
                return {
                    roleKey: 'superadmin',
                    badgeText: 'Super Administrator',
                    badgeBg: 'rgba(239,68,68,0.12)',
                    badgeColor: '#ef4444',
                    heroBadgeIcon: ShieldCheck,
                    heroBadgeText: 'Akun Master Super Administrator • Polinela Agro Digital',
                    headerSubtitle: 'Kelola kredensial administrator utama, foto profil, dan preferensi keamanan master sistem',
                    banner: {
                        gradient: 'linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4c0519 100%)',
                        badgeTag: 'Akses Penuh Master Admin',
                        badgeTagClass: 'bg-danger text-white',
                        title: 'Pusat Akun & Kontrol Super Administrator',
                        desc: 'Anda memiliki kendali master atas platform Polinela Agro. Untuk manajemen seluruh unit usaha perkebunan, kelola akun pengguna, katalog semua komoditas, dan audit transaksi pembayaran, silakan buka Panel Super Admin.',
                        buttonText: 'Buka Panel Super Admin',
                        buttonTo: '/admin',
                        buttonIcon: ShieldCheck
                    },
                    biodata: {
                        title: 'Informasi Identitas & Kontak Super Administrator',
                        subtitle: 'Data master administrator untuk audit keamanan, log operasional, dan pemulihan darurat sistem.',
                        namaLabel: 'Nama Lengkap Administrator',
                        namaPlaceholder: 'Nama lengkap pengelola utama sistem',
                        emailHelp: 'Email master untuk login superadmin, log audit transaksi, dan notifikasi server.',
                        phoneLabel: 'Nomor WhatsApp Admin Utama',
                        phoneHelp: 'Untuk verifikasi 2FA darurat dan pemulihan akun sistem.',
                        alamatLabel: 'Alamat Kantor Pusat Pengelola Sistem',
                        alamatPlaceholder: 'Contoh: Gedung UPT Komputer / TIK Polinela, Jl. Soekarno Hatta No. 10, Bandar Lampung'
                    },
                    keamanan: {
                        subtitle: 'Gunakan kombinasi sandi master yang sangat kuat dan unik untuk menjaga keamanan seluruh infrastruktur e-commerce Polinela Agro.'
                    },
                    akun: {
                        unitLabel: 'Seluruh Unit Usaha (Akses Penuh Master)',
                        actionCard: {
                            bgClass: 'bg-danger bg-opacity-10 border-danger border-opacity-25',
                            textClass: 'text-danger',
                            icon: ShieldCheck,
                            title: 'Akses Panel Master Administrator',
                            desc: 'Kelola pengguna, otorisasi staf unit, audit transaksi pembayaran Midtrans, dan pengaturan global.',
                            btnText: 'Buka Panel Admin',
                            btnClass: 'btn-danger',
                            btnTo: '/admin'
                        }
                    }
                };

            case 'admin_unit':
                return {
                    roleKey: 'admin_unit',
                    badgeText: 'Admin Unit Usaha',
                    badgeBg: 'rgba(59,130,246,0.12)',
                    badgeColor: '#3b82f6',
                    heroBadgeIcon: Building2,
                    heroBadgeText: `Pengelola Unit ${unitName || 'Usaha TEFA'} • Polinela Agro Digital`,
                    headerSubtitle: 'Kelola identitas penanggung jawab unit, foto profil, dan nomor kontak resmi toko perkebunan',
                    banner: {
                        gradient: 'linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #1d4ed8 100%)',
                        badgeTag: `Unit TEFA: ${unitName || 'Usaha Perkebunan'}`,
                        badgeTagClass: 'bg-info text-white',
                        title: `Pusat Akun Pengelola Unit ${unitName || 'Usaha'}`,
                        desc: 'Anda bertugas mengelola unit toko perkebunan ini. Untuk memproses pesanan komoditas yang masuk, memperbarui ketersediaan stok panen, dan mengecek omset toko unit Anda, silakan akses Panel Pengelola Unit.',
                        buttonText: 'Buka Panel Unit Toko',
                        buttonTo: '/admin',
                        buttonIcon: Store
                    },
                    biodata: {
                        title: `Informasi Kontak Pengelola Unit ${unitName || 'Usaha'}`,
                        subtitle: 'Data kontak penanggung jawab unit toko perkebunan untuk koordinasi stok panen dan pemrosesan pesanan.',
                        namaLabel: 'Nama Lengkap Penanggung Jawab Unit',
                        namaPlaceholder: 'Nama penanggung jawab operasional unit',
                        emailHelp: 'Email operasional unit untuk menerima notifikasi pesanan masuk dan konfirmasi pembayaran pembeli.',
                        phoneLabel: 'Nomor WhatsApp Layanan Unit / CS Toko',
                        phoneHelp: 'Digunakan pembeli dan kurir untuk koordinasi ketersediaan produk dan penjemputan paket.',
                        alamatLabel: 'Alamat Lokasi Fisik Unit / Toko Perkebunan',
                        alamatPlaceholder: 'Contoh: Laboratorium / Kebun Percobaan TEFA Polinela, Gedung Produksi Unit, Bandar Lampung'
                    },
                    keamanan: {
                        subtitle: 'Amankan akun pengelola unit Anda agar pengelolaan katalog barang dan pesanan pembeli selalu terlindungi.'
                    },
                    akun: {
                        unitLabel: `${unitName || 'Unit Usaha'} (Unit Yang Dikelola)`,
                        actionCard: {
                            bgClass: 'bg-primary bg-opacity-10 border-primary border-opacity-25',
                            textClass: 'text-primary',
                            icon: Store,
                            title: 'Akses Panel Pengelola Unit Toko',
                            desc: 'Kelola katalog produk komoditas panen, perbarui ketersediaan stok, dan proses pengiriman pesanan.',
                            btnText: 'Buka Panel Toko',
                            btnClass: 'btn-primary',
                            btnTo: '/admin'
                        }
                    }
                };

            default: // 'konsumen' or others
                return {
                    roleKey: 'konsumen',
                    badgeText: 'Pelanggan Terverifikasi',
                    badgeBg: 'rgba(34,197,94,0.12)',
                    badgeColor: '#22c55e',
                    heroBadgeIcon: Sprout,
                    heroBadgeText: 'Profil Pelanggan • Polinela Agro Digital',
                    headerSubtitle: 'Kelola identitas pribadi, foto profil, preferensi keamanan, dan alamat pengiriman pesanan Anda',
                    banner: {
                        gradient: 'linear-gradient(135deg, #14532d 0%, #15803d 50%, #047857 100%)',
                        badgeTag: 'Pelanggan Polinela Agro',
                        badgeTagClass: 'bg-light text-success',
                        title: 'Pusat Pengaturan Akun & Alamat Belanja',
                        desc: 'Pastikan nama penerima, nomor WhatsApp aktif, dan alamat pengiriman Anda terisi lengkap untuk mempercepat pengiriman komoditas segar langsung dari kebun Polinela ke rumah Anda.',
                        buttonText: 'Lihat Riwayat Pesanan',
                        buttonTo: '/pesanan',
                        buttonIcon: Package
                    },
                    biodata: {
                        title: 'Informasi Pribadi & Kontak Pengiriman',
                        subtitle: 'Data ini digunakan untuk pencetakan label resi pengiriman, invoice pesanan, dan konfirmasi WhatsApp dari kurir.',
                        namaLabel: 'Nama Lengkap Penerima',
                        namaPlaceholder: 'Nama lengkap sesuai KTP penerima',
                        emailHelp: 'Digunakan untuk login akun dan menerima notifikasi tagihan/resi belanja Anda.',
                        phoneLabel: 'Nomor WhatsApp Aktif Penerima',
                        phoneHelp: 'Kurir pengiriman akan menghubungi nomor ini saat mengantar produk ke alamat Anda.',
                        alamatLabel: 'Alamat Pengiriman Default (Rumah / Kantor)',
                        alamatPlaceholder: 'Nama Jalan, Nomor Rumah/Gedung, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten, Kode Pos'
                    },
                    keamanan: {
                        subtitle: 'Biarkan kolom kata sandi kosong jika Anda tidak berencana mengganti kata sandi saat ini.'
                    },
                    akun: {
                        unitLabel: 'Semua Unit Toko (Konsumen Pembeli)',
                        actionCard: {
                            bgClass: 'bg-success bg-opacity-10 border-success border-opacity-25',
                            textClass: 'text-success',
                            icon: ShoppingBag,
                            title: 'Aktivitas Belanja & Pesanan Anda',
                            desc: 'Lacak status paket komoditas yang sedang dikirim atau jelajahi produk hasil perkebunan terbaru.',
                            btnText: 'Lihat Riwayat Pesanan',
                            btnClass: 'btn-success',
                            btnTo: '/pesanan'
                        }
                    }
                };
        }
    };

    const roleConfig = getRoleProfileConfig(user);
    const roleBadge = {
        label: roleConfig.badgeText,
        color: roleConfig.badgeColor,
        bg: roleConfig.badgeBg
    };

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
                            {roleConfig.headerSubtitle}
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
                            <span className="small fw-semibold text-white d-flex align-items-center gap-1.5" style={{ textShadow: '0 1px 3px rgba(0,0,0,0.5)' }}>
                                <roleConfig.heroBadgeIcon size={15} /> {roleConfig.heroBadgeText}
                            </span>

                            <span className="small text-white-50 d-none d-sm-inline-flex align-items-center gap-1" style={{ textShadow: '0 1px 2px rgba(0,0,0,0.5)' }}>
                                <Calendar size={13} /> Terdaftar: {user?.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }) : '2026'}
                            </span>
                        </div>

                        {/* Liquid Glass Logo Button in bottom-right corner */}
                        <button
                            type="button"
                            onClick={() => setShowBannerPicker(true)}
                            className="banner-customize-btn position-absolute bottom-0 end-0 mb-2 me-3 rounded-circle shadow-sm"
                            style={{ 
                                width: '38px',
                                height: '38px',
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
                                <h5 className="fw-bold mb-2 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                                    <User size={18} className="text-success" />
                                    {roleConfig.biodata.title}
                                </h5>
                                <p className="text-muted small mb-4">
                                    {roleConfig.biodata.subtitle}
                                </p>

                                <div className="mb-3">
                                    <label className="form-label small fw-semibold" style={{ color: 'var(--text-body)' }}>
                                        {roleConfig.biodata.namaLabel} <span className="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        className="form-control rounded-3 py-2 px-3"
                                        required
                                        placeholder={roleConfig.biodata.namaPlaceholder}
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
                                        <small className="text-muted" style={{ fontSize: '11px' }}>
                                            {roleConfig.biodata.emailHelp}
                                        </small>
                                    </div>

                                    <div className="col-md-6">
                                        <label className="form-label small fw-semibold" style={{ color: 'var(--text-body)' }}>
                                            <Phone size={14} className="me-1 text-success" />
                                            {roleConfig.biodata.phoneLabel}
                                        </label>
                                        <input
                                            type="text"
                                            className="form-control rounded-3 py-2 px-3"
                                            value={noHp}
                                            onChange={(e) => setNoHp(e.target.value)}
                                            placeholder="Contoh: 081234567890"
                                        />
                                        <small className="text-muted" style={{ fontSize: '11px' }}>
                                            {roleConfig.biodata.phoneHelp}
                                        </small>
                                    </div>
                                </div>

                                <div className="mb-4">
                                    <div className="d-flex justify-content-between align-items-center mb-1">
                                        <label className="form-label small fw-semibold mb-0" style={{ color: 'var(--text-body)' }}>
                                            <MapPin size={14} className="me-1 text-danger" />
                                            {roleConfig.biodata.alamatLabel}
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
                                        placeholder={roleConfig.biodata.alamatPlaceholder}
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
                                    {roleConfig.keamanan.subtitle}
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
                                                {roleConfig.akun.unitLabel}
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

                                {/* Role Specific Action Card */}
                                <div className={`p-3 rounded-3 border d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 ${roleConfig.akun.actionCard.bgClass}`}>
                                    <div>
                                        <div className={`fw-bold d-flex align-items-center gap-2 ${roleConfig.akun.actionCard.textClass}`}>
                                            <roleConfig.akun.actionCard.icon size={18} />
                                            <span>{roleConfig.akun.actionCard.title}</span>
                                        </div>
                                        <div className="small text-muted mt-1">
                                            {roleConfig.akun.actionCard.desc}
                                        </div>
                                    </div>
                                    <Link 
                                        to={roleConfig.akun.actionCard.btnTo} 
                                        className={`btn btn-sm rounded-pill px-3 py-2 fw-semibold text-nowrap d-inline-flex align-items-center gap-1 shadow-sm ${roleConfig.akun.actionCard.btnClass}`}
                                    >
                                        <span>{roleConfig.akun.actionCard.btnText}</span>
                                        <ArrowRight size={14} />
                                    </Link>
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
                                            <span className="small fw-bold text-white d-flex align-items-center gap-1.5" style={{ textShadow: '0 1px 3px rgba(0,0,0,0.6)' }}>
                                                <Sprout size={14} /> {namaLengkap || 'Pengguna'} • Polinela Agro Digital
                                            </span>
                                            <span className="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1 small">
                                                Tema Aktif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {/* Category Switcher Tabs */}
                                <div className="d-flex gap-2 mb-3 overflow-x-auto pb-1" style={{ scrollbarWidth: 'none' }}>
                                    {BANNER_CATEGORIES.map((cat) => {
                                        const CatIcon = cat.icon;
                                        return (
                                            <button
                                                key={cat.id}
                                                type="button"
                                                onClick={() => setActiveCategory(cat.id)}
                                                className={`btn btn-sm rounded-pill px-3 py-2 text-nowrap d-flex align-items-center gap-1.5 ${
                                                    activeCategory === cat.id 
                                                        ? 'btn-success fw-bold shadow-sm' 
                                                        : 'btn-outline-secondary'
                                                }`}
                                                style={{ fontSize: '12px' }}
                                            >
                                                <CatIcon size={14} />
                                                <span>{cat.name}</span>
                                            </button>
                                        );
                                    })}
                                    <button
                                        type="button"
                                        onClick={() => setActiveCategory('solid')}
                                        className={`btn btn-sm rounded-pill px-3 py-2 text-nowrap d-flex align-items-center gap-1.5 ${
                                            activeCategory === 'solid' 
                                                ? 'btn-success fw-bold shadow-sm' 
                                                : 'btn-outline-secondary'
                                        }`}
                                        style={{ fontSize: '12px' }}
                                    >
                                        <Palette size={14} />
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
                                            title: 'Tema Banner Diterapkan!',
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
