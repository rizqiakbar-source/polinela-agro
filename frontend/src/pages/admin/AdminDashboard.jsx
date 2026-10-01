import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { formatRupiah, formatDate, getOrderStatusBadge } from '../../utils/format';
import { Link } from 'react-router-dom';
import SalesAnalyticsOverview from '../../components/admin/SalesAnalyticsOverview';
import Swal from 'sweetalert2';
import { 
    DollarSign, 
    ShoppingBag, 
    Clock, 
    CheckCircle, 
    Package, 
    AlertTriangle,
    Store,
    ArrowRight,
    TrendingUp,
    BarChart3,
    Award,
    RefreshCw,
    ShieldCheck,
    Printer,
    FileSpreadsheet,
    Building,
    CheckCircle2,
    Layers,
    Sparkles,
    CreditCard,
    MessageSquare,
    Plus,
    Tag,
    User,
    Building2,
    MapPin,
    Zap
} from 'lucide-react';

const AdminDashboard = () => {
    const { user, isSuperadmin, isAdminUnit, isPimpinan } = useAuth();
    const [dashboardData, setDashboardData] = useState(null);
    const [loading, setLoading] = useState(true);

    const fetchDashboard = async () => {
        setLoading(true);
        try {
            const res = await client.get('/admin/dashboard');
            if (res.data.status === 'success') {
                setDashboardData(res.data.data);
            }
        } catch (err) {
            console.error('Failed to load dashboard:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchDashboard();
    }, []);

    const handlePrintExecutiveReport = () => {
        window.print();
    };

    const handleQuickStock = async (product) => {
        if (!product) return;
        const currentStock = product.stok ?? product.total_stok ?? 0;
        const { value: newStock } = await Swal.fire({
            title: `Restok Cepat Produk`,
            html: `
                <div class="text-start p-3 bg-light rounded-3 mb-2" style="font-size: 13px;">
                    <div class="fw-bold text-dark fs-6">${product.nama_produk}</div>
                    <div class="text-muted small">Unit Usaha: ${product.unit?.nama_unit || 'Unit TEFA'}</div>
                    <div class="mt-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted">Stok Saat Ini:</span>
                        <span class="badge bg-danger rounded-pill px-2.5 py-1">${currentStock} ${product.satuan || 'Pcs'}</span>
                    </div>
                </div>
            `,
            input: 'number',
            inputLabel: 'Masukkan Jumlah Stok Baru:',
            inputValue: currentStock,
            inputAttributes: {
                min: '0',
                step: '1'
            },
            showCancelButton: true,
            confirmButtonText: 'Simpan Perubahan Stok',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
            inputValidator: (val) => {
                if (val === '' || isNaN(val) || parseInt(val) < 0) {
                    return 'Harap masukkan angka stok yang valid (minimal 0)!';
                }
            }
        });

        if (newStock !== undefined) {
            try {
                const res = await client.put(`/admin/products/${product.id}/stock`, {
                    stok: parseInt(newStock)
                });
                if (res.data.status === 'success' || res.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Stok Berhasil Diperbarui!',
                        text: `Stok produk ${product.nama_produk} telah disesuaikan menjadi ${newStock} ${product.satuan || 'Pcs'}.`,
                        timer: 1600,
                        showConfirmButton: false,
                    });
                    fetchDashboard();
                }
            } catch (err) {
                Swal.fire('Gagal!', err.response?.data?.message || 'Gagal memperbarui stok di katalog.', 'error');
            }
        }
    };

    if (loading) {
        return (
            <div className="d-flex justify-content-center align-items-center py-5 min-vh-50">
                <div className="spinner-border text-agro" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    const { 
        stats, 
        unit, 
        recent_orders, 
        low_stock_products, 
        monthly_chart, 
        unit_stats, 
        sus_score,
        sparklines,
        goals,
        daily_analytics,
        product_heatmap,
        recent_reviews
    } = dashboardData || {};

    const chartTotals = monthly_chart?.totals || [0, 0, 0, 0, 0, 0];
    const chartLabels = monthly_chart?.labels || ['Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'];
    const maxVal = Math.max(...chartTotals, 1000000);

    // Find top performing unit for Pimpinan
    const topUnit = unit_stats && unit_stats.length > 0
        ? [...unit_stats].sort((a, b) => b.total_omset - a.total_omset)[0]
        : null;

    const totalOmsetAll = stats?.total_pendapatan || 0;

    /* =========================================================================
       VIEW 1: KHUSUS PORTAL PIMPINAN (EXECUTIVE LEADERSHIP DASHBOARD)
       ========================================================================= */
    if (isPimpinan) {
        return (
            <div className="container-fluid p-0">
                {/* Executive Header Banner */}
                <div className="card border-0 shadow-sm rounded-4 p-4 mb-4" style={{ background: 'var(--gradient-primary)', color: '#ffffff' }}>
                    <div className="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div className="d-flex align-items-center gap-2 mb-1">
                                <span className="badge bg-white text-success fw-bold px-3 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                    <Building2 size={13} /> Portal Pengawasan Eksekutif
                                </span>
                                <span className="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1">
                                    Politeknik Negeri Lampung
                                </span>
                            </div>
                            <h3 className="fw-bold mb-1 text-white">
                                Dashboard Eksekutif & Hilirisasi TEFA
                            </h3>
                            <p className="mb-0 text-white-50 small">
                                Ringkasan strategis komersialisasi riset terpadu, evaluasi finansial, dan kontribusi 4 unit Teaching Factory perkebunan.
                            </p>
                        </div>

                        <div className="d-flex gap-2">
                            <button
                                onClick={handlePrintExecutiveReport}
                                className="btn btn-light btn-sm fw-bold d-flex align-items-center gap-1 rounded-pill px-3 shadow-sm text-success"
                            >
                                <Printer size={15} /> Cetak Lembar Eksekutif
                            </button>
                            <Link
                                to="/admin/reports"
                                className="btn btn-outline-light btn-sm rounded-pill px-3 d-flex align-items-center gap-1"
                            >
                                <BarChart3 size={15} /> Laporan Penjualan
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Rich Sales Analytics Overview Component */}
                <SalesAnalyticsOverview
                    stats={stats}
                    sparklines={sparklines}
                    goals={goals}
                    dailyAnalytics={daily_analytics}
                    monthlyChart={monthly_chart}
                    productHeatmap={product_heatmap}
                    recentReviews={recent_reviews}
                    lowStockProducts={low_stock_products}
                    unitStats={unit_stats}
                    roleTitle="Store Overview (Executive Leadership)"
                    roleSubtitle="Monitoring performa finansial, pencapaian target bulanan, dan matriks dinamika penjualan produk TEFA"
                    onQuickStock={handleQuickStock}
                />

                {/* Komparasi Finansial & Kontribusi 4 Unit TEFA */}
                <div className="card border-0 shadow-sm rounded-4 p-4 mb-4" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                    <div className="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 className="fw-bold mb-0 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                                <BarChart3 size={18} className="text-success" /> Matriks Kontribusi Finansial 4 Unit TEFA Perkebunan
                            </h5>
                            <small className="text-muted">Komparasi pendapatan, produktivitas, dan pangsa pasar tiap Teaching Factory</small>
                        </div>
                        <Link to="/admin/reports" className="btn btn-sm btn-outline-success rounded-pill px-3">
                            Laporan Lengkap & Unduh Excel →
                        </Link>
                    </div>

                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead>
                                <tr style={{ background: 'var(--bg-card-hover)', color: 'var(--text-heading)', borderBottom: '2px solid var(--border-color)' }}>
                                    <th>Unit Usaha Teaching Factory</th>
                                    <th className="text-center">Katalog Produk</th>
                                    <th className="text-center">Total Pesanan</th>
                                    <th className="text-end">Total Omset Finansial</th>
                                    <th style={{ width: '220px' }}>Kontribusi Omset</th>
                                </tr>
                            </thead>
                            <tbody>
                                {unit_stats && unit_stats.map((u) => {
                                    const percent = totalOmsetAll > 0 ? Math.round((u.total_omset / totalOmsetAll) * 100) : 25;
                                    return (
                                        <tr key={u.id} style={{ borderColor: 'var(--border-color)' }}>
                                            <td>
                                                <div className="d-flex align-items-center gap-2">
                                                    <div className="p-2 rounded-3 bg-success bg-opacity-10 text-success">
                                                        <Store size={18} />
                                                    </div>
                                                    <div>
                                                        <div className="fw-bold" style={{ color: 'var(--text-heading)' }}>{u.nama_unit}</div>
                                                        <small className="text-muted">Unit Bisnis Polinela #{u.id}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="text-center fw-semibold">{u.total_produk} Item</td>
                                            <td className="text-center fw-semibold">{u.total_pesanan} Order</td>
                                            <td className="text-end fw-bold text-success fs-6">{formatRupiah(u.total_omset)}</td>
                                            <td>
                                                <div className="d-flex align-items-center gap-2">
                                                    <div className="progress flex-grow-1" style={{ height: '8px' }}>
                                                        <div 
                                                            className="progress-bar bg-success" 
                                                            role="progressbar" 
                                                            style={{ width: `${percent}%` }}
                                                        ></div>
                                                    </div>
                                                    <span className="small fw-bold text-muted" style={{ minWidth: '35px' }}>{percent}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        );
    }

    /* =========================================================================
       VIEW 2: KHUSUS PANEL ADMIN UNIT TOKO (STORE OPERATIONAL DASHBOARD)
       ========================================================================= */
    if (isAdminUnit) {
        return (
            <div className="container-fluid p-0">
                {/* Store Unit Hero Banner */}
                <div className="card border-0 shadow-sm rounded-4 p-4 mb-4" style={{ background: 'var(--gradient-primary)', color: '#ffffff' }}>
                    <div className="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div className="d-flex align-items-center gap-2 mb-1">
                                <span className="badge bg-white text-success fw-bold px-3 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                    <Store size={13} /> Pusat Operasional Unit Toko
                                </span>
                                <span className="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1">
                                    TEFA #{user?.unit_id || unit?.id || 1}
                                </span>
                            </div>
                            <h3 className="fw-bold mb-1 text-white">
                                {unit?.nama_unit || user?.unit?.nama_unit || 'Unit Usaha Perkebunan Polinela'}
                            </h3>
                            <p className="mb-0 text-white-50 small">
                                {unit?.lokasi ? <span className="d-inline-flex align-items-center gap-1"><MapPin size={12} /> Lokasi: {unit.lokasi}</span> : 'Pengelolaan inventaris, pemrosesan pesanan, dan verifikasi pembayaran unit.'}
                            </p>
                        </div>

                        <div className="d-flex gap-2">
                            <Link
                                to="/admin/products"
                                className="btn btn-light btn-sm fw-bold d-flex align-items-center gap-1 rounded-pill px-3 shadow-sm text-success"
                            >
                                <Plus size={16} /> Tambah Produk
                            </Link>
                            <Link
                                to="/admin/orders"
                                className="btn btn-outline-light btn-sm rounded-pill px-3 d-flex align-items-center gap-1"
                            >
                                <ShoppingBag size={15} /> Pesanan Masuk
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Rich Sales Analytics Overview Component */}
                <SalesAnalyticsOverview
                    stats={stats}
                    sparklines={sparklines}
                    goals={goals}
                    dailyAnalytics={daily_analytics}
                    monthlyChart={monthly_chart}
                    productHeatmap={product_heatmap}
                    recentReviews={recent_reviews}
                    lowStockProducts={low_stock_products}
                    unitStats={unit_stats}
                    roleTitle={`Store Overview (${unit?.nama_unit || user?.unit?.nama_unit || 'Unit Toko Anda'})`}
                    roleSubtitle="Monitoring performa penjualan harian, order masuk, dan ketersediaan stok unit"
                    onQuickStock={handleQuickStock}
                />

                {/* 4 Unit Quick Operational Action Tiles */}
                <div className="row g-3 mb-4">
                    <div className="col-md-3 col-6">
                        <Link to="/admin/products" className="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                            <div className="d-flex align-items-center gap-3">
                                <div className="p-3 rounded-4 bg-primary bg-opacity-10 text-primary">
                                    <Package size={22} />
                                </div>
                                <div>
                                    <h6 className="fw-bold mb-0" style={{ color: 'var(--text-heading)', fontSize: '14px' }}>Katalog & Stok</h6>
                                    <small className="text-muted" style={{ fontSize: '11.5px' }}>Atur harga & kuota stok</small>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div className="col-md-3 col-6">
                        <Link to="/admin/orders" className="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                            <div className="d-flex align-items-center gap-3">
                                <div className="p-3 rounded-4 bg-success bg-opacity-10 text-success">
                                    <ShoppingBag size={22} />
                                </div>
                                <div>
                                    <h6 className="fw-bold mb-0" style={{ color: 'var(--text-heading)', fontSize: '14px' }}>Pesanan Masuk</h6>
                                    <small className="text-muted" style={{ fontSize: '11.5px' }}>Proses order & resi kurir</small>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div className="col-md-3 col-6">
                        <Link to="/admin/payments" className="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                            <div className="d-flex align-items-center gap-3">
                                <div className="p-3 rounded-4 bg-warning bg-opacity-10 text-warning">
                                    <CreditCard size={22} />
                                </div>
                                <div>
                                    <h6 className="fw-bold mb-0" style={{ color: 'var(--text-heading)', fontSize: '14px' }}>Verifikasi Bayar</h6>
                                    <small className="text-muted" style={{ fontSize: '11.5px' }}>Validasi bukti transfer</small>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div className="col-md-3 col-6">
                        <Link to="/admin/reviews" className="card border-0 shadow-sm rounded-4 p-3 text-decoration-none h-100" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                            <div className="d-flex align-items-center gap-3">
                                <div className="p-3 rounded-4 bg-info bg-opacity-10 text-info">
                                    <MessageSquare size={22} />
                                </div>
                                <div>
                                    <h6 className="fw-bold mb-0" style={{ color: 'var(--text-heading)', fontSize: '14px' }}>Ulasan Pelanggan</h6>
                                    <small className="text-muted" style={{ fontSize: '11.5px' }}>Rating & feedback rasa</small>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                {/* Split Section: Unit Recent Orders & Unit Info */}
                <div className="row g-4 mb-4">
                    {/* Unit Recent Orders Table */}
                    <div className="col-lg-8">
                        <div className="card border-0 shadow-sm rounded-4 p-4 h-100" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                            <div className="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 className="fw-bold mb-0" style={{ color: 'var(--text-heading)' }}>
                                        Pesanan Masuk Terbaru (Unit Toko Anda)
                                    </h6>
                                    <small className="text-muted">Daftar transaksi belanja produk unit yang memerlukan perhatian</small>
                                </div>
                                <Link to="/admin/orders" className="btn btn-outline-success btn-sm rounded-pill px-3">
                                    Lihat Semua <ArrowRight size={14} />
                                </Link>
                            </div>

                            {!recent_orders || recent_orders.length === 0 ? (
                                <div className="text-center py-4 text-muted small">Belum ada pesanan masuk untuk unit ini.</div>
                            ) : (
                                <div className="table-responsive">
                                    <table className="table table-hover align-middle mb-0 small">
                                        <thead>
                                            <tr style={{ background: 'var(--bg-card-hover)', color: 'var(--text-heading)' }}>
                                                <th>No Pesanan</th>
                                                <th>Pembeli</th>
                                                <th>Tagihan Unit</th>
                                                <th>Status</th>
                                                <th className="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {recent_orders.map((o) => {
                                                const badge = getOrderStatusBadge(o.status);
                                                return (
                                                    <tr key={o.id} style={{ borderColor: 'var(--border-color)' }}>
                                                        <td className="fw-bold text-primary font-monospace">#{o.no_pesanan}</td>
                                                        <td style={{ color: 'var(--text-body)' }}>{o.user?.nama_lengkap || o.user?.username}</td>
                                                        <td className="fw-bold text-success">{formatRupiah(o.total_akhir)}</td>
                                                        <td><span className={`badge ${badge.bg}`}>{badge.label}</span></td>
                                                        <td className="text-center">
                                                            <Link to={`/admin/orders`} className="btn btn-sm btn-light rounded-pill px-3">
                                                                Proses
                                                            </Link>
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Unit Info Card */}
                    <div className="col-lg-4">
                        <div className="card border-0 shadow-sm rounded-4 p-4 h-100" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                            <h6 className="fw-bold mb-3 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                                <Store size={18} className="text-agro" /> Info Pengelola Unit
                            </h6>
                            <div className="small d-flex flex-column gap-2">
                                <div className="d-flex justify-content-between py-2 border-bottom">
                                    <span className="text-muted">Kepala / PJ:</span>
                                    <strong className="text-dark">{unit?.pj_nama || 'Dosen Pengampu TEFA'}</strong>
                                </div>
                                <div className="d-flex justify-content-between py-2 border-bottom">
                                    <span className="text-muted">Kontak Unit:</span>
                                    <span>{unit?.kontak || '0812-3456-7800'}</span>
                                </div>
                                <div className="d-flex justify-content-between py-2 border-bottom">
                                    <span className="text-muted">Status TEFA:</span>
                                    <span className="badge bg-success">Operasional Aktif</span>
                                </div>
                                <div className="d-flex justify-content-between py-2">
                                    <span className="text-muted">Lokasi Usaha:</span>
                                    <span>{unit?.lokasi || 'Kampus Polinela'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    /* =========================================================================
       VIEW 3: KHUSUS SUPERADMIN MASTER (SYSTEM OPERATIONS & ALL UNITS)
       ========================================================================= */
    return (
        <div className="container-fluid p-0 animate-fade-in">
            {/* Superadmin Master Hero Banner */}
            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4" style={{ background: 'var(--gradient-primary)', color: '#ffffff' }}>
                <div className="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div className="d-flex align-items-center gap-2 mb-1">
                            <span className="badge bg-white text-success fw-bold px-3 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                <ShieldCheck size={13} /> Pusat Kendali Superadmin Master
                            </span>
                            <span className="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1">
                                Polinela Agro Digital
                            </span>
                        </div>
                        <h3 className="fw-bold mb-1 text-white">
                            Dashboard Analitik & Operasional Master
                        </h3>
                        <p className="mb-0 text-white-50 small">
                            Ringkasan performa penjualan, monitoring transaksi seluruh 4 unit TEFA, dan ketersediaan stok terpadu.
                        </p>
                    </div>

                    <div className="d-flex gap-2">
                        <button 
                            onClick={fetchDashboard} 
                            className="btn btn-light btn-sm fw-bold d-flex align-items-center gap-1 rounded-pill px-3 shadow-sm text-success"
                        >
                            <RefreshCw size={14} /> Refresh Data
                        </button>
                        <Link 
                            to="/admin/reports" 
                            className="btn btn-outline-light btn-sm rounded-pill px-3 d-flex align-items-center gap-1"
                        >
                            <BarChart3 size={15} /> Laporan Penjualan
                        </Link>
                    </div>
                </div>
            </div>

            {/* Rich Sales Analytics Overview Component */}
            <SalesAnalyticsOverview
                stats={stats}
                sparklines={sparklines}
                goals={goals}
                dailyAnalytics={daily_analytics}
                monthlyChart={monthly_chart}
                productHeatmap={product_heatmap}
                recentReviews={recent_reviews}
                lowStockProducts={low_stock_products}
                unitStats={unit_stats}
                roleTitle="Store Overview (Master Analytics)"
                roleSubtitle="Monitoring performa penjualan harian, transaksi seluruh unit TEFA, dan ketersediaan stok master"
                onQuickStock={handleQuickStock}
            />

            {/* Quick Operational Status Cards */}
            <div className="row g-3 mb-4">
                <div className="col-6 col-lg-3">
                    <Link to="/admin/payments" className="text-decoration-none">
                        <div className="card border-0 shadow-sm rounded-4 p-3 h-100 hover-lift" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)', borderLeft: '4px solid #f59e0b' }}>
                            <div className="d-flex align-items-center justify-content-between">
                                <div>
                                    <span className="text-muted small fw-semibold d-block">Perlu Verifikasi</span>
                                    <h4 className="fw-bold text-warning mb-0">{stats?.pesanan_perlu_verifikasi || 0}</h4>
                                </div>
                                <div className="p-2.5 rounded-3 bg-warning bg-opacity-10 text-warning">
                                    <Clock size={20} />
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <div className="col-6 col-lg-3">
                    <Link to="/admin/orders" className="text-decoration-none">
                        <div className="card border-0 shadow-sm rounded-4 p-3 h-100 hover-lift" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)', borderLeft: '4px solid #10b981' }}>
                            <div className="d-flex align-items-center justify-content-between">
                                <div>
                                    <span className="text-muted small fw-semibold d-block">Pesanan Selesai</span>
                                    <h4 className="fw-bold text-success mb-0">{stats?.pesanan_selesai || 0}</h4>
                                </div>
                                <div className="p-2.5 rounded-3 bg-success bg-opacity-10 text-success">
                                    <CheckCircle size={20} />
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <div className="col-6 col-lg-3">
                    <Link to="/admin/products" className="text-decoration-none">
                        <div className="card border-0 shadow-sm rounded-4 p-3 h-100 hover-lift" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)', borderLeft: '4px solid #3b82f6' }}>
                            <div className="d-flex align-items-center justify-content-between">
                                <div>
                                    <span className="text-muted small fw-semibold d-block">Total Katalog Produk</span>
                                    <h4 className="fw-bold text-primary mb-0">{stats?.total_produk || 0}</h4>
                                </div>
                                <div className="p-2.5 rounded-3 bg-primary bg-opacity-10 text-primary">
                                    <Package size={20} />
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <div className="col-6 col-lg-3">
                    <Link to="/admin/units" className="text-decoration-none">
                        <div className="card border-0 shadow-sm rounded-4 p-3 h-100 hover-lift" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)', borderLeft: '4px solid #8b5cf6' }}>
                            <div className="d-flex align-items-center justify-content-between">
                                <div>
                                    <span className="text-muted small fw-semibold d-block">Unit Usaha TEFA</span>
                                    <h4 className="fw-bold text-purple mb-0" style={{ color: '#7c3aed' }}>{unit_stats?.length || 4} Unit</h4>
                                </div>
                                <div className="p-2.5 rounded-3 bg-purple bg-opacity-10" style={{ color: '#7c3aed', backgroundColor: '#ede9fe' }}>
                                    <Store size={20} />
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            {/* Komparasi Kinerja Seluruh Unit Usaha TEFA */}
            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                <div className="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 className="fw-bold mb-0 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                            <Store size={18} className="text-success" /> Kinerja & Kontribusi 4 Unit Usaha TEFA
                        </h6>
                        <small className="text-muted">Komparasi perolehan omset dan volume pesanan antar unit bisnis perkebunan Polinela</small>
                    </div>
                    <Link to="/admin/reports" className="btn btn-outline-success btn-sm rounded-pill px-3">
                        Lihat Laporan Lengkap <ArrowRight size={14} />
                    </Link>
                </div>

                <div className="table-responsive">
                    <table className="table table-hover align-middle mb-0 small">
                        <thead className="table-light">
                            <tr>
                                <th>Unit Usaha Teaching Factory</th>
                                <th className="text-center">Katalog Produk</th>
                                <th className="text-center">Total Pesanan</th>
                                <th className="text-end">Total Omset Penjualan</th>
                                <th style={{ width: '220px' }}>Persentase Kontribusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {unit_stats && unit_stats.map((u) => {
                                const percent = totalOmsetAll > 0 ? Math.round((u.total_omset / totalOmsetAll) * 100) : 25;
                                return (
                                    <tr key={u.id}>
                                        <td>
                                            <div className="d-flex align-items-center gap-2.5">
                                                <div className="p-2 rounded-3 bg-success bg-opacity-10 text-success">
                                                    <Store size={16} />
                                                </div>
                                                <div>
                                                    <div className="fw-bold text-dark">{u.nama_unit}</div>
                                                    <small className="text-muted">Unit Bisnis Polinela #{u.id}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="text-center fw-semibold">{u.total_produk} Item</td>
                                        <td className="text-center fw-semibold">{u.total_pesanan} Order</td>
                                        <td className="text-end fw-bold text-success fs-6">{formatRupiah(u.total_omset)}</td>
                                        <td>
                                            <div className="d-flex align-items-center gap-2">
                                                <div className="progress flex-grow-1" style={{ height: '7px', backgroundColor: '#e2e8f0' }}>
                                                    <div 
                                                        className="progress-bar bg-success rounded-pill" 
                                                        role="progressbar" 
                                                        style={{ width: `${Math.max(3, percent)}%` }}
                                                    ></div>
                                                </div>
                                                <span className="small fw-bold text-muted" style={{ minWidth: '35px' }}>{percent}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Bottom Split Section: Recent Orders & SUS Usability Card */}
            <div className="row g-4 mb-3">
                <div className="col-lg-8">
                    <div className="card border-0 shadow-sm rounded-4 p-4 h-100" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 className="fw-bold mb-0" style={{ color: 'var(--text-heading)' }}>Pesanan Masuk Terbaru</h6>
                                <small className="text-muted">Transaksi belanja masuk dari seluruh 4 unit TEFA perkebunan</small>
                            </div>
                            <Link to="/admin/orders" className="btn btn-outline-success btn-sm rounded-pill px-3">
                                Kelola Semua Pesanan <ArrowRight size={14} />
                            </Link>
                        </div>

                        {!recent_orders || recent_orders.length === 0 ? (
                            <div className="text-center py-4 text-muted small">Belum ada pesanan masuk.</div>
                        ) : (
                            <div className="table-responsive">
                                <table className="table table-hover align-middle mb-0 small">
                                    <thead className="table-light">
                                        <tr>
                                            <th>No Pesanan</th>
                                            <th>Pembeli</th>
                                            <th>Unit Toko</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th className="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {recent_orders.map((o) => {
                                            const badge = getOrderStatusBadge(o.status);
                                            return (
                                                <tr key={o.id}>
                                                    <td className="fw-bold text-primary font-monospace">#{o.no_pesanan}</td>
                                                    <td className="text-dark fw-semibold">{o.user?.nama_lengkap || o.user?.username || o.user?.nama || 'Pelanggan'}</td>
                                                    <td>
                                                        <span className="badge bg-light text-dark border">{o.unit?.nama_unit}</span>
                                                    </td>
                                                    <td className="fw-bold text-success">{formatRupiah(o.total_akhir)}</td>
                                                    <td><span className={`badge ${badge.bg}`}>{badge.label}</span></td>
                                                    <td className="text-center">
                                                        <Link to={`/admin/orders`} className="btn btn-sm btn-light border rounded-pill px-2.5">
                                                            Detail
                                                        </Link>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </div>

                <div className="col-lg-4">
                    <div className="card border-0 shadow-sm rounded-4 p-4 h-100 text-center d-flex flex-column justify-content-center" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                        <div className="d-flex align-items-center justify-content-center gap-2 mb-2">
                            <Award size={22} className="text-warning" />
                            <h6 className="fw-bold mb-0" style={{ color: 'var(--text-heading)' }}>Evaluasi Usability (SUS)</h6>
                        </div>
                        <div className="display-5 fw-bold text-success mb-1">
                            {sus_score || '82.5'}
                        </div>
                        <span className="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1.5 mx-auto small fw-semibold">
                            Grade A (Excellent Usability)
                        </span>
                        <p className="text-muted mt-3 mb-0 small" style={{ fontSize: '11.5px' }}>
                            Skor kepuasan antarmuka pengguna berdasarkan 10 instrumen kuesioner System Usability Scale civitas akademika Polinela.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AdminDashboard;
