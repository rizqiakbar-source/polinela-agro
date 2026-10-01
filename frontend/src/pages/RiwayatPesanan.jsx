import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import client from '../api/client';
import { formatRupiah, formatDate, getOrderStatusBadge, getPaymentStatusBadge } from '../utils/format';
import { ShoppingBag, Eye, Store, ArrowRight, Printer, Zap, Package } from 'lucide-react';
import ReceiptModal from '../components/ReceiptModal';
import { triggerMidtransPayment } from '../utils/midtrans';
import Swal from 'sweetalert2';

const RiwayatPesanan = () => {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [statusFilter, setStatusFilter] = useState('');
    const [selectedOrderForReceipt, setSelectedOrderForReceipt] = useState(null);
    const [payingOrderId, setPayingOrderId] = useState(null);

    const fetchOrders = async () => {
        setLoading(true);
        try {
            const url = statusFilter ? `/orders?status=${statusFilter}` : '/orders';
            const res = await client.get(url);
            if (res.data.status === 'success') {
                const fetchedOrders = res.data.data.data || [];
                setOrders(fetchedOrders);

                // Auto-sync any pending orders in the background
                const pendingOrders = fetchedOrders.filter(o => o.status === 'menunggu_pembayaran');
                if (pendingOrders.length > 0) {
                    Promise.all(
                        pendingOrders.map(po => client.get(`/payment/midtrans/status/${po.id}`).catch(() => null))
                    ).then(results => {
                        const anyChanged = results.some(r => r?.data?.order && r.data.order.status !== 'menunggu_pembayaran');
                        if (anyChanged) {
                            client.get(url).then(refreshed => {
                                if (refreshed.data.status === 'success') {
                                    setOrders(refreshed.data.data.data || []);
                                }
                            });
                        }
                    });
                }
            }
        } catch (err) {
            console.error('Failed to load orders:', err);
        } finally {
            setLoading(false);
        }
    };

    const handleQuickPayMidtrans = async (orderId) => {
        setPayingOrderId(orderId);
        try {
            const snapRes = await client.get(`/payment/midtrans/token/${orderId}`);
            const snapToken = snapRes.data.snap_token || snapRes.data.data?.snap_token;
            const snapUrl = snapRes.data.snap_url;
            const clientKey = snapRes.data.client_key;

            if (!snapToken) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Midtrans',
                    text: 'Token pembayaran Midtrans tidak dapat dibuat. Coba beberapa saat lagi.',
                });
                return;
            }

            await triggerMidtransPayment(snapToken, {
                snapUrl,
                clientKey,
                onSuccess: async (result) => {
                    await client.post(`/payment/finish/${orderId}`, { payment_type: result.payment_type || 'midtrans' });
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil Diverifikasi!',
                        text: 'Status pesanan Anda kini LUNAS.',
                    });
                    await fetchOrders();
                },
                onPending: () => {
                    Swal.fire({
                        icon: 'info',
                        title: 'Menunggu Pembayaran',
                        text: 'Selesaikan pembayaran sesuai nomor VA / QRIS yang tertera.',
                    });
                    fetchOrders();
                },
                onError: () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Pembayaran Gagal / Dibatalkan',
                        text: 'Transaksi belum selesai.',
                    });
                },
            });
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Membuka Pembayaran',
                text: err.response?.data?.message || 'Tidak dapat memuat popup Midtrans.',
            });
        } finally {
            setPayingOrderId(null);
        }
    };

    useEffect(() => {
        fetchOrders();
    }, [statusFilter]);

    const statusTabs = [
        { key: '', label: 'Semua Pesanan' },
        { key: 'menunggu_pembayaran', label: 'Belum Bayar' },
        { key: 'menunggu_verifikasi', label: 'Menunggu Verifikasi' },
        { key: 'diproses', label: 'Diproses' },
        { key: 'dikirim', label: 'Dikirim' },
        { key: 'selesai', label: 'Selesai' },
        { key: 'dibatalkan', label: 'Dibatalkan' },
    ];

    return (
        <div className="py-4 bg-light min-vh-100">
            <div className="container">
                {/* Header */}
                <div className="mb-4">
                    <h2 className="fw-bold text-dark mb-1">Riwayat Pesanan Saya</h2>
                    <p className="text-muted small mb-0">Pantau status pengiriman dan verifikasi pesanan perkebunan Anda.</p>
                </div>

                {/* Filter Tabs */}
                <div className="card border-0 shadow-sm rounded-4 p-2 mb-4 bg-white">
                    <div className="d-flex gap-2 overflow-auto pb-1">
                        {statusTabs.map((tab) => (
                            <button
                                key={tab.key}
                                type="button"
                                className={`btn btn-sm rounded-pill px-3 text-nowrap ${statusFilter === tab.key ? 'btn-success text-white fw-bold' : 'btn-light text-dark'}`}
                                onClick={() => setStatusFilter(tab.key)}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Orders List */}
                {loading ? (
                    <div className="text-center py-5">
                        <div className="spinner-border text-success" role="status">
                            <span className="visually-hidden">Loading...</span>
                        </div>
                    </div>
                ) : orders.length === 0 ? (
                    <div className="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                        <div className="mb-3 d-flex justify-content-center">
                            <Package size={56} className="text-success opacity-75" />
                        </div>
                        <h5 className="fw-bold mb-2">Tidak Ada Pesanan</h5>
                        <p className="text-muted small mb-4">Anda belum memiliki pesanan dengan status ini.</p>
                        <Link to="/katalog" className="btn btn-success rounded-pill px-4 mx-auto">
                            Mulai Belanja
                        </Link>
                    </div>
                ) : (
                    <div className="d-flex flex-column gap-3">
                        {orders.map((order) => {
                            const isPaid = order.payment?.status === 'lunas' || ['diproses', 'dikirim', 'selesai'].includes(order.status);
                            const badge = getOrderStatusBadge(order.status);
                            const payBadge = isPaid 
                                ? { label: 'Lunas', bg: 'bg-success text-white' }
                                : getPaymentStatusBadge(order.payment?.status);

                            return (
                                <div key={order.id} className="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                                    {/* Header: Store Name + Order Date + Status */}
                                    <div className="p-3 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div className="d-flex align-items-center gap-2">
                                            <div className="bg-success text-white rounded-circle p-1 d-flex align-items-center justify-content-center" style={{ width: '24px', height: '24px' }}>
                                                <Store size={14} />
                                            </div>
                                            <span className="fw-bold text-success">{order.unit?.nama_unit || 'Unit Polinela'}</span>
                                            <span className="text-muted small">• No: #{order.order_number || order.no_pesanan}</span>
                                        </div>
                                        <div className="d-flex align-items-center gap-2">
                                            <span className={`badge ${badge.bg} rounded-pill px-3 py-1 small`}>
                                                {badge.label}
                                            </span>
                                            <span className="text-muted small">{formatDate(order.created_at)}</span>
                                        </div>
                                    </div>

                                    {/* Body: Items Preview */}
                                    <div className="p-3">
                                        {order.details?.map((detail) => {
                                            const itemPrice = Number(detail.harga ?? detail.harga_satuan ?? 0);
                                            const itemQty = Number(detail.qty ?? detail.jumlah ?? 1);
                                            const itemSubtotal = Number(detail.subtotal ?? (itemPrice * itemQty));

                                            return (
                                                <div key={detail.id} className="d-flex justify-content-between align-items-center py-2 border-bottom last-border-none">
                                                    <div>
                                                        <h6 className="mb-0 fw-semibold text-dark small">{detail.nama_produk}</h6>
                                                        <small className="text-muted">{itemQty} barang x {formatRupiah(itemPrice)}</small>
                                                    </div>
                                                    <span className="fw-bold text-dark small">{formatRupiah(itemSubtotal)}</span>
                                                </div>
                                            );
                                        })}

                                        {/* Footer: Shared Trx Code + Grand Total + View Action */}
                                        <div className="d-flex flex-wrap justify-content-between align-items-center pt-3 mt-2">
                                            <div>
                                                <div className="text-muted small">
                                                    Kode Transaksi Bersama: <strong className="text-primary">{order.payment?.no_transaksi || '-'}</strong>
                                                </div>
                                                <span className={`badge ${payBadge.bg} mt-1`} style={{ fontSize: '11px' }}>
                                                    {payBadge.label}
                                                </span>
                                            </div>

                                            <div className="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                                                <div className="text-end me-2">
                                                    <div className="text-muted small" style={{ fontSize: '11px' }}>Total Tagihan Unit</div>
                                                    <span className="fw-bold text-success fs-6">{formatRupiah(order.grand_total ?? order.total_akhir ?? 0)}</span>
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={() => setSelectedOrderForReceipt(order)}
                                                    className="btn btn-outline-secondary btn-sm rounded-pill px-3 d-flex align-items-center gap-1"
                                                    title="Cetak Struk / Invoice"
                                                >
                                                    <Printer size={14} />
                                                    <span>Struk</span>
                                                </button>
                                                {order.status === 'menunggu_pembayaran' && (
                                                    <button
                                                        type="button"
                                                        disabled={payingOrderId === order.id}
                                                        onClick={() => handleQuickPayMidtrans(order.id)}
                                                        className="btn btn-success btn-sm rounded-pill px-3 fw-bold d-flex align-items-center gap-1 shadow-sm"
                                                        title="Bayar langsung via Midtrans (VA / QRIS / E-Wallet)"
                                                    >
                                                        {payingOrderId === order.id ? (
                                                            <>
                                                                <span className="spinner-border spinner-border-sm" role="status"></span>
                                                                <span>Memuat...</span>
                                                            </>
                                                        ) : (
                                                            <>
                                                                <Zap size={14} className="text-warning fill-warning" />
                                                                <span>Bayar Midtrans</span>
                                                            </>
                                                        )}
                                                    </button>
                                                )}
                                                <Link to={`/pesanan/${order.id}`} className="btn btn-outline-success btn-sm rounded-pill px-3 d-flex align-items-center gap-1">
                                                    <Eye size={14} />
                                                    <span>Detail</span>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>

            {/* Receipt Modal */}
            <ReceiptModal 
                isOpen={!!selectedOrderForReceipt}
                onClose={() => setSelectedOrderForReceipt(null)}
                order={selectedOrderForReceipt}
            />
        </div>
    );
};

export default RiwayatPesanan;
