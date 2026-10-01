import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { formatRupiah, formatDate, getOrderStatusBadge } from '../../utils/format';
import { 
    BarChart3, 
    Download, 
    Calendar, 
    DollarSign, 
    Package, 
    Printer, 
    Store, 
    TrendingUp, 
    Filter, 
    RefreshCw, 
    ShoppingCart,
    CreditCard,
    FileSpreadsheet,
    Award
} from 'lucide-react';

const AdminReports = () => {
    const { isSuperadmin, isPimpinan, user } = useAuth();

    const [summary, setSummary] = useState(null);
    const [orders, setOrders] = useState([]);
    const [unitBreakdown, setUnitBreakdown] = useState([]);
    const [topProducts, setTopProducts] = useState([]);
    const [dailyAnalytics, setDailyAnalytics] = useState([]);
    const [loading, setLoading] = useState(true);

    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const [statusFilter, setStatusFilter] = useState('paid');

    const fetchReports = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);
            if (statusFilter) params.append('status', statusFilter);

            const res = await client.get(`/admin/reports/sales?${params.toString()}`);
            if (res.data.status === 'success' || res.data.success) {
                const data = res.data.data || res.data;
                setSummary(data.summary || data.stats || null);
                setOrders(data.orders || []);
                setUnitBreakdown(data.unit_breakdown || data.unit_summary || data.unit_stats || []);
                setTopProducts(data.top_products || []);
                setDailyAnalytics(data.daily_analytics || []);
            }
        } catch (err) {
            console.error('Failed to load reports:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchReports();
    }, [startDate, endDate, statusFilter]);

    const handleExportCSV = () => {
        if (!orders || orders.length === 0) {
            alert('Tidak ada data pesanan untuk diekspor.');
            return;
        }

        let csvContent = "\uFEFF"; // UTF-8 BOM for Excel
        csvContent += "No,No Pesanan,Tanggal,Unit Toko,Pembeli,No HP,Alamat,Total Produk,Ongkir,Diskon,Grand Total,Status Pesanan,Metode Pembayaran\n";

        orders.forEach((o, idx) => {
            const orderNo = `"${o.order_number || o.no_pesanan || o.id}"`;
            const date = `"${formatDate(o.created_at)}"`;
            const unit = `"${o.unit?.nama_unit || '-'}"`;
            const customer = `"${o.shipping?.nama_penerima || o.user?.nama || 'Pelanggan'}"`;
            const hp = `"${o.shipping?.no_hp || o.user?.no_hp || '-'}"`;
            const address = `"${(o.shipping?.alamat_lengkap || '-').replace(/"/g, '""')}"`;
            const prodTotal = o.total_produk || 0;
            const ongkir = o.ongkir || 0;
            const diskon = o.diskon || 0;
            const grandTotal = o.grand_total || 0;
            const status = `"${o.status}"`;
            const paymentMethod = `"${o.payment?.metode || 'midtrans'}"`;

            csvContent += `${idx + 1},${orderNo},${date},${unit},${customer},${hp},${address},${prodTotal},${ongkir},${diskon},${grandTotal},${status},${paymentMethod}\n`;
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `Laporan-Penjualan-Polinela-Agro-${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    const handlePrint = () => {
        window.print();
    };

    const totalPendapatan = Number(summary?.total_pendapatan || 0);
    const totalTransaksi = Number(summary?.total_transaksi || 0);
    const totalProdukTerjual = Number(summary?.total_produk_terjual || 0);
    const avgOrderValue = totalTransaksi > 0 ? Math.round(totalPendapatan / totalTransaksi) : 0;

    return (
        <div>
            {/* Header Laporan Rekapitulasi */}
            <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 className="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                        <FileSpreadsheet className="text-success" size={28} />
                        <span>Laporan Rekapitulasi & Audit Penjualan</span>
                    </h3>
                    <p className="text-muted small mb-0">
                        {user?.role === 'admin_unit' 
                            ? `Laporan pembukuan keuangan dan rekonsiliasi transaksi unit ${user.unit?.nama_unit || 'Toko Anda'}.`
                            : 'Laporan pembukuan keuangan, performa omset, dan audit multi-store perkebunan Politeknik Negeri Lampung.'}
                    </p>
                </div>
                <div className="d-flex gap-2">
                    <button
                        type="button"
                        className="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-2 shadow-sm bg-white"
                        onClick={handlePrint}
                    >
                        <Printer size={15} />
                        <span>Cetak Laporan</span>
                    </button>
                    <button
                        type="button"
                        className="btn btn-success btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-2 shadow-sm fw-semibold"
                        onClick={handleExportCSV}
                    >
                        <Download size={15} />
                        <span>Unduh Excel / CSV</span>
                    </button>
                </div>
            </div>

            {/* Filter Rentang Tanggal & Status */}
            <div className="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
                <div className="row g-3 align-items-center">
                    <div className="col-md-3">
                        <label className="form-label small fw-semibold text-muted">Dari Tanggal</label>
                        <input
                            type="date"
                            className="form-control form-control-sm"
                            value={startDate}
                            onChange={(e) => setStartDate(e.target.value)}
                        />
                    </div>
                    <div className="col-md-3">
                        <label className="form-label small fw-semibold text-muted">Sampai Tanggal</label>
                        <input
                            type="date"
                            className="form-control form-control-sm"
                            value={endDate}
                            onChange={(e) => setEndDate(e.target.value)}
                        />
                    </div>
                    <div className="col-md-4">
                        <label className="form-label small fw-semibold text-muted">Status Transaksi</label>
                        <select
                            className="form-select form-select-sm"
                            value={statusFilter}
                            onChange={(e) => setStatusFilter(e.target.value)}
                        >
                            <option value="paid">Transaksi Lunas (Diproses, Dikirim, Selesai)</option>
                            <option value="selesai">Hanya Pesanan Selesai</option>
                            <option value="diproses">Hanya Sedang Diproses</option>
                            <option value="dikirim">Hanya Sedang Dikirim</option>
                            <option value="all">Semua Status (Termasuk Menunggu Bayar & Batal)</option>
                        </select>
                    </div>
                    <div className="col-md-2 d-flex align-items-end">
                        <button
                            type="button"
                            className="btn btn-outline-secondary btn-sm rounded-pill w-100 d-inline-flex align-items-center justify-content-center gap-1"
                            onClick={() => { setStartDate(''); setEndDate(''); setStatusFilter('paid'); }}
                            title="Reset Filter"
                        >
                            <RefreshCw size={13} />
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </div>
            </div>

            {/* 4 Financial Audit Metric Cards */}
            <div className="row g-3 mb-4">
                <div className="col-lg-3 col-sm-6">
                    <div className="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div className="d-flex align-items-center justify-content-between">
                            <div>
                                <span className="text-muted small fw-semibold">Total Omset Terfilter</span>
                                <h4 className="fw-bold text-success mb-0 mt-1">{formatRupiah(totalPendapatan)}</h4>
                                <small className="text-muted" style={{ fontSize: '11px' }}>Akumulasi nilai pesanan sah</small>
                            </div>
                            <div className="p-3 bg-success bg-opacity-10 text-success rounded-4">
                                <DollarSign size={22} />
                            </div>
                        </div>
                    </div>
                </div>

                <div className="col-lg-3 col-sm-6">
                    <div className="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div className="d-flex align-items-center justify-content-between">
                            <div>
                                <span className="text-muted small fw-semibold">Jumlah Transaksi</span>
                                <h4 className="fw-bold text-primary mb-0 mt-1">{totalTransaksi} Pesanan</h4>
                                <small className="text-muted" style={{ fontSize: '11px' }}>Total order terverifikasi</small>
                            </div>
                            <div className="p-3 bg-primary bg-opacity-10 text-primary rounded-4">
                                <Package size={22} />
                            </div>
                        </div>
                    </div>
                </div>

                <div className="col-lg-3 col-sm-6">
                    <div className="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div className="d-flex align-items-center justify-content-between">
                            <div>
                                <span className="text-muted small fw-semibold">Kuantitas Produk Terjual</span>
                                <h4 className="fw-bold text-warning text-dark mb-0 mt-1">{totalProdukTerjual} Item</h4>
                                <small className="text-muted" style={{ fontSize: '11px' }}>Total unit item barang</small>
                            </div>
                            <div className="p-3 bg-warning bg-opacity-10 text-warning rounded-4">
                                <TrendingUp size={22} />
                            </div>
                        </div>
                    </div>
                </div>

                <div className="col-lg-3 col-sm-6">
                    <div className="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div className="d-flex align-items-center justify-content-between">
                            <div>
                                <span className="text-muted small fw-semibold">Rata-rata Order (AOV)</span>
                                <h4 className="fw-bold text-info mb-0 mt-1">{formatRupiah(avgOrderValue)}</h4>
                                <small className="text-muted" style={{ fontSize: '11px' }}>Nilai transaksi rata-rata</small>
                            </div>
                            <div className="p-3 bg-info bg-opacity-10 text-info rounded-4">
                                <Award size={22} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Split Section: Top Selling Products & Unit Contribution */}
            <div className="row g-4 mb-4">
                {/* Top Products Ranking */}
                <div className={`col-lg-${(isSuperadmin || isPimpinan) && unitBreakdown.length > 0 ? '6' : '12'}`}>
                    <div className="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h5 className="fw-bold mb-3 d-flex align-items-center gap-2">
                            <BarChart3 size={18} className="text-primary" /> Peringkat Produk Terlaris
                        </h5>
                        {topProducts.length === 0 ? (
                            <div className="text-center py-4 text-muted small">Belum ada data penjualan produk pada periode ini.</div>
                        ) : (
                            <div className="table-responsive">
                                <table className="table table-hover align-middle mb-0 small">
                                    <thead className="table-light">
                                        <tr>
                                            <th>Nama Produk</th>
                                            <th className="text-center">Kuantitas Terjual</th>
                                            <th className="text-end">Total Nilai Penjualan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {topProducts.map((tp, idx) => (
                                            <tr key={idx}>
                                                <td className="fw-semibold">{tp.nama_produk}</td>
                                                <td className="text-center">
                                                    <span className="badge bg-success bg-opacity-25 text-success fw-bold">
                                                        {tp.order_details_sum_jumlah || 0} {tp.satuan || 'Pcs'}
                                                    </span>
                                                </td>
                                                <td className="text-end fw-bold text-success">
                                                    {formatRupiah(tp.order_details_sum_subtotal || 0)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </div>

                {/* Multi-Store Unit Contribution (Superadmin / Pimpinan) */}
                {(isSuperadmin || isPimpinan) && unitBreakdown.length > 0 && (
                    <div className="col-lg-6">
                        <div className="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                            <h5 className="fw-bold mb-3 d-flex align-items-center gap-2">
                                <Store size={18} className="text-success" /> Kontribusi Finansial per Unit Usaha
                            </h5>
                            <div className="table-responsive">
                                <table className="table table-hover align-middle mb-0 small">
                                    <thead className="table-light">
                                        <tr>
                                            <th>Unit Usaha TEFA</th>
                                            <th className="text-center">Pesanan</th>
                                            <th className="text-end">Total Omset</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {unitBreakdown.map((u) => (
                                            <tr key={u.id || u.unit_id}>
                                                <td className="fw-semibold">
                                                    <span className="d-inline-flex align-items-center gap-1">
                                                        <Store size={14} className="text-primary" />
                                                        <span>{u.nama_unit}</span>
                                                    </span>
                                                </td>
                                                <td className="text-center">{u.orders_count || 0}</td>
                                                <td className="text-end fw-bold text-success">
                                                    {formatRupiah(u.orders_sum_total_akhir || u.total_pendapatan || 0)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* Detailed Transactions Table */}
            <div className="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div className="d-flex justify-content-between align-items-center mb-3">
                    <h5 className="fw-bold mb-0 d-flex align-items-center gap-2">
                        <ShoppingCart size={18} className="text-success" /> Rincian Log Transaksi Penjualan
                    </h5>
                    <span className="badge bg-light text-dark border">
                        {orders.length} Transaksi Ditemukan
                    </span>
                </div>

                {loading ? (
                    <div className="text-center py-5">
                        <div className="spinner-border text-success" role="status">
                            <span className="visually-hidden">Memuat laporan...</span>
                        </div>
                    </div>
                ) : orders.length === 0 ? (
                    <div className="text-center py-5 text-muted small">
                        Tidak ada transaksi penjualan yang sesuai dengan filter yang dipilih.
                    </div>
                ) : (
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0 small">
                            <thead className="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>No Pesanan</th>
                                    <th>Tanggal</th>
                                    {(isSuperadmin || isPimpinan) && <th>Unit Toko</th>}
                                    <th>Pembeli</th>
                                    <th>Item Produk</th>
                                    <th>Total Tagihan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {orders.map((o, idx) => {
                                    const badge = getOrderStatusBadge(o.status);
                                    const orderNo = o.order_number || o.no_pesanan || `#${o.id}`;
                                    const customerName = o.shipping?.nama_penerima || o.user?.nama || 'Pelanggan';
                                    const customerPhone = o.shipping?.no_hp || o.user?.no_hp || '-';
                                    const total = o.grand_total ?? o.total_akhir ?? 0;

                                    return (
                                        <tr key={o.id}>
                                            <td className="text-muted">{idx + 1}</td>
                                            <td className="fw-bold text-primary">{orderNo}</td>
                                            <td className="text-muted">{formatDate(o.created_at)}</td>
                                            {(isSuperadmin || isPimpinan) && (
                                                <td><span className="badge bg-light text-dark border">{o.unit?.nama_unit}</span></td>
                                            )}
                                            <td>
                                                <div className="fw-semibold text-dark">{customerName}</div>
                                                <small className="text-muted">{customerPhone}</small>
                                            </td>
                                            <td>
                                                <div style={{ maxWidth: '240px' }}>
                                                    {o.details && o.details.length > 0 ? (
                                                        o.details.map((d, dIdx) => (
                                                            <div key={dIdx} className="text-truncate" title={d.nama_produk}>
                                                                • {d.nama_produk} &times; {d.qty || d.jumlah || 1}
                                                            </div>
                                                        ))
                                                    ) : (
                                                        <span className="text-muted">-</span>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="fw-bold text-success">{formatRupiah(total)}</td>
                                            <td><span className={`badge ${badge.bg}`}>{badge.label}</span></td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
    );
};

export default AdminReports;
