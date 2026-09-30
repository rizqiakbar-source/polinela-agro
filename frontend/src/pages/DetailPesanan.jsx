import React, { useState, useEffect, useRef } from 'react';
import { useParams, Link, useNavigate, useSearchParams } from 'react-router-dom';
import client from '../api/client';
import { formatRupiah, formatDate, getOrderStatusBadge, getPaymentStatusBadge, getPaymentProofUrl } from '../utils/format';
import { ArrowLeft, Store, CreditCard, Truck, Upload, CheckCircle2, XCircle, AlertCircle, Info, Printer, Zap } from 'lucide-react';
import Swal from 'sweetalert2';
import ReceiptModal from '../components/ReceiptModal';
import { triggerMidtransPayment } from '../utils/midtrans';

const DetailPesanan = () => {
    const { id } = useParams();
    const [searchParams, setSearchParams] = useSearchParams();
    const navigate = useNavigate();

    const [order, setOrder] = useState(null);
    const [siblingOrders, setSiblingOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showReceiptModal, setShowReceiptModal] = useState(false);
    const [loadingSnap, setLoadingSnap] = useState(false);
    const autoPayTriggeredRef = useRef(false);

    // Payment upload states
    const [buktiFile, setBuktiFile] = useState(null);
    const [uploading, setUploading] = useState(false);

    const fetchOrderDetail = async () => {
        setLoading(true);
        try {
            const res = await client.get(`/orders/${id}`);
            if (res.data.status === 'success') {
                let orderData = res.data.data.order;

                // Jika status masih menunggu pembayaran, cek sinkronisasi status ke Midtrans secara otomatis
                if (orderData.status === 'menunggu_pembayaran') {
                    try {
                        const syncRes = await client.get(`/payment/midtrans/status/${id}`);
                        if (syncRes.data.order && syncRes.data.order.status !== 'menunggu_pembayaran') {
                            orderData = syncRes.data.order;
                        }
                    } catch (syncErr) {
                        // ignore sync error
                    }
                }

                setOrder(orderData);
                setSiblingOrders(res.data.data.sibling_orders || []);

                // Jika diarahkan langsung dari checkout (autoPay=1), buka otomatis Snap
                if (
                    searchParams.get('autoPay') === '1' &&
                    !autoPayTriggeredRef.current &&
                    orderData.status === 'menunggu_pembayaran'
                ) {
                    autoPayTriggeredRef.current = true;
                    // Bersihkan param autoPay dari URL
                    searchParams.delete('autoPay');
                    setSearchParams(searchParams, { replace: true });
                    setTimeout(() => {
                        handlePayMidtrans();
                    }, 400);
                }
            }
        } catch (err) {
            console.error('Failed to load order detail:', err);
            Swal.fire({
                icon: 'error',
                title: 'Pesanan Tidak Ditemukan',
                text: 'Detail pesanan tidak dapat dimuat.',
            });
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchOrderDetail();
    }, [id]);

    // Real-Time Background Auto-Sync & Tab-Focus Detection
    useEffect(() => {
        if (!order || order.status !== 'menunggu_pembayaran') return;

        const checkStatusSilently = async () => {
            try {
                const syncRes = await client.get(`/payment/midtrans/status/${id}`);
                if (syncRes.data.order && syncRes.data.order.status !== 'menunggu_pembayaran') {
                    setOrder(syncRes.data.order);
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil Diverifikasi! 🎉',
                        text: 'Pesanan Anda kini resmi LUNAS & sedang disiapkan.',
                        timer: 2500,
                        showConfirmButton: false,
                    });
                }
            } catch (err) {
                // quiet
            }
        };

        // Polling setiap 1.5 detik (Real-Time Ultra Responsif)
        const intervalId = setInterval(checkStatusSilently, 1500);

        // Langsung cek seketika saat user kembali ke tab web dari simulator / mobile banking
        const handleFocus = () => {
            checkStatusSilently();
        };

        window.addEventListener('focus', handleFocus);
        document.addEventListener('visibilitychange', handleFocus);

        return () => {
            clearInterval(intervalId);
            window.removeEventListener('focus', handleFocus);
            document.removeEventListener('visibilitychange', handleFocus);
        };
    }, [id, order?.status]);

    const handleUploadBukti = async (e) => {
        e.preventDefault();
        if (!buktiFile) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih File Bukti',
                text: 'Silakan pilih gambar/foto bukti transfer terlebih dahulu.',
            });
            return;
        }

        const formData = new FormData();
        formData.append('bukti_bayar', buktiFile);

        setUploading(true);
        try {
            const res = await client.post(`/orders/${id}/upload-bukti`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            if (res.data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Bukti Pembayaran Terkirim!',
                    text: 'Bukti telah disimpan dan akan segera diverifikasi oleh masing-masing admin unit toko.',
                });
                setBuktiFile(null);
                await fetchOrderDetail();
            }
        } catch (error) {
            const msg = error.response?.data?.message || 'Gagal mengunggah bukti pembayaran.';
            Swal.fire({
                icon: 'error',
                title: 'Upload Gagal',
                text: msg,
            });
        } finally {
            setUploading(false);
        }
    };

    const handlePayMidtrans = async () => {
        setLoadingSnap(true);
        try {
            const snapRes = await client.get(`/payment/midtrans/token/${id}`);
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
                    await client.post(`/payment/finish/${id}`, { payment_type: result.payment_type || 'midtrans' });
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Berhasil! 🎉',
                        text: 'Pesanan Anda telah otomatis diverifikasi LUNAS.',
                    });
                    await fetchOrderDetail();
                },
                onPending: async () => {
                    Swal.fire({
                        icon: 'info',
                        title: 'Menunggu Pembayaran',
                        text: 'Silakan selesaikan pembayaran sesuai instruksi Virtual Account / QRIS yang tertera.',
                    });
                    await fetchOrderDetail();
                },
                onError: () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Pembayaran Gagal',
                        text: 'Transaksi tidak berhasil. Silakan coba kembali.',
                    });
                },
                onClose: async () => {
                    try {
                        const syncRes = await client.get(`/payment/midtrans/status/${id}`);
                        if (syncRes.data.order) {
                            setOrder(syncRes.data.order);
                        }
                    } catch (e) {}
                    fetchOrderDetail();
                }
            });
        } catch (err) {
            console.error('Midtrans error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Membuka Midtrans',
                text: err.response?.data?.message || 'Tidak dapat memuat popup pembayaran.',
            });
        } finally {
            setLoadingSnap(false);
        }
    };

    const handleKonfirmasiTerima = async () => {
        const result = await Swal.fire({
            title: 'Konfirmasi Penerimaan Barang?',
            text: 'Pastikan paket produk telah Anda terima dengan baik dan sesuai.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Paket Diterima',
            cancelButtonText: 'Batal',
        });

        if (result.isConfirmed) {
            try {
                const res = await client.put(`/orders/${id}/konfirmasi-terima`);
                if (res.data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesanan Selesai',
                        text: 'Terima kasih telah berbelanja produk perkebunan di Polinela Agro!',
                    });
                    await fetchOrderDetail();
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Konfirmasi',
                    text: err.response?.data?.message || 'Terjadi kesalahan sistem.',
                });
            }
        }
    };

    const handleBatalkanPesanan = async () => {
        const { value: alasan } = await Swal.fire({
            title: 'Batalkan Pesanan?',
            input: 'textarea',
            inputPlaceholder: 'Tuliskan alasan pembatalan...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Batalkan Pesanan',
            cancelButtonText: 'Kembali',
            inputValidator: (val) => {
                if (!val) return 'Alasan pembatalan harus diisi!';
            },
        });

        if (alasan) {
            try {
                const res = await client.put(`/orders/${id}/batal`, { alasan });
                if (res.data.status === 'success') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Pesanan Dibatalkan',
                        text: 'Pesanan Anda telah resmi dibatalkan.',
                    });
                    await fetchOrderDetail();
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Membatalkan',
                    text: err.response?.data?.message || 'Pesanan tidak dapat dibatalkan.',
                });
            }
        }
    };

    const handleBeriUlasan = async (productId, namaProduk) => {
        const { value: formValues } = await Swal.fire({
            title: `Beri Penilaian Produk`,
            html: `
                <div class="text-start mb-2">
                    <strong class="text-success">${namaProduk}</strong>
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold">Rating Bintang (1 - 5):</label>
                    <select id="swal-rating" class="form-select">
                        <option value="5">⭐⭐⭐⭐⭐ 5 Bintang (Sangat Puas)</option>
                        <option value="4">⭐⭐⭐⭐ 4 Bintang (Puas)</option>
                        <option value="3">⭐⭐⭐ 3 Bintang (Cukup)</option>
                        <option value="2">⭐⭐ 2 Bintang (Kurang)</option>
                        <option value="1">⭐ 1 Bintang (Kecewa)</option>
                    </select>
                </div>
                <div class="text-start">
                    <label class="form-label small fw-bold">Ulasan Anda:</label>
                    <textarea id="swal-ulasan" class="form-control" rows="3" placeholder="Tuliskan pengalaman Anda tentang kualitas dan rasa produk ini..."></textarea>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Kirim Ulasan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10b981',
            preConfirm: () => {
                const rating = document.getElementById('swal-rating').value;
                const ulasan = document.getElementById('swal-ulasan').value;
                if (!ulasan.trim()) {
                    Swal.showValidationMessage('Silakan tuliskan ulasan Anda!');
                    return false;
                }
                return { rating: parseInt(rating), ulasan: ulasan.trim() };
            }
        });

        if (formValues) {
            try {
                const res = await client.post(`/products/${productId}/reviews`, formValues);
                if (res.data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terima Kasih! ⭐',
                        text: 'Ulasan dan penilaian Anda berhasil disimpan.',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengirim Ulasan',
                    text: err.response?.data?.message || 'Terjadi kesalahan sistem.',
                });
            }
        }
    };


    if (loading) {
        return (
            <div className="d-flex justify-content-center align-items-center py-5 min-vh-50">
                <div className="spinner-border text-success" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    if (!order) {
        return (
            <div className="container py-5 text-center">
                <h4>Pesanan tidak ditemukan</h4>
                <Link to="/pesanan" className="btn btn-success mt-3">Kembali ke Riwayat</Link>
            </div>
        );
    }

    const isPaid = order.payment?.status === 'lunas' || ['diproses', 'dikirim', 'selesai'].includes(order.status);
    const orderBadge = getOrderStatusBadge(order.status);
    const payBadge = isPaid 
        ? { label: 'Lunas', bg: 'bg-success text-white' } 
        : getPaymentStatusBadge(order.payment?.status);

    // Calculate unified grand total for all sibling stores in this transaction
    const combinedTotal = (siblingOrders.length > 0)
        ? siblingOrders.reduce((acc, o) => acc + Number(o.grand_total ?? o.total_akhir ?? 0), Number(order.grand_total ?? order.total_akhir ?? 0))
        : Number(order.grand_total ?? order.total_akhir ?? 0);

    const isAwaitingPayment = order.status === 'menunggu_pembayaran';
    const isUnderVerification = order.status === 'menunggu_verifikasi';
    const orderNumberDisplay = order.order_number || order.no_pesanan || `#${order.id}`;

    const handleOpenWhatsApp = async () => {
        try {
            const res = await client.get(`/orders/${id}/whatsapp-link?target=admin`);
            if (res.data.whatsapp_url) {
                window.open(res.data.whatsapp_url, '_blank');
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Tidak dapat membuka tautan WhatsApp saat ini.',
            });
        }
    };

    return (
        <div className="py-4 bg-light min-vh-100">
            <div className="container">
                {/* Header */}
                <div className="mb-4">
                    <Link to="/pesanan" className="btn btn-link text-muted text-decoration-none p-0 d-inline-flex align-items-center gap-1 small mb-2">
                        <ArrowLeft size={16} /> Kembali ke Riwayat Pesanan
                    </Link>
                    <div className="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h2 className="fw-bold text-dark mb-1">Detail Pesanan #{orderNumberDisplay}</h2>
                            <div className="d-flex align-items-center gap-2 flex-wrap">
                                <p className="text-muted small mb-0">Dibuat pada {formatDate(order.created_at)}</p>
                                <span className="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill" style={{ fontSize: '11px' }}>
                                    📧 Notifikasi Email Aktif
                                </span>
                            </div>
                        </div>
                        <div className="d-flex align-items-center gap-2 flex-wrap">
                            <button
                                type="button"
                                onClick={handleOpenWhatsApp}
                                className="btn btn-outline-success btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1 shadow-sm bg-white"
                                title="Kirim konfirmasi / chat ke Admin Unit Toko"
                            >
                                <span style={{ fontSize: '15px' }}>📲</span>
                                <span>WhatsApp Unit</span>
                            </button>
                            <button
                                type="button"
                                onClick={() => setShowReceiptModal(true)}
                                className="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm bg-white"
                            >
                                <Printer size={16} />
                                <span>Cetak Struk</span>
                            </button>
                            <span className={`badge ${orderBadge.bg} rounded-pill px-3 py-2 fs-6`}>
                                {orderBadge.label}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Sibling Stores Multi-Order Info Banner */}
                {siblingOrders.length > 0 && (
                    <div className="alert alert-info border-0 rounded-4 shadow-sm mb-4 d-flex align-items-start gap-3">
                        <Info size={22} className="text-primary flex-shrink-0 mt-1" />
                        <div>
                            <h6 className="fw-bold mb-1">Transaksi Multi-Store Bersama ({order.payment?.no_transaksi})</h6>
                            <p className="small mb-2">
                                Pesanan ini merupakan bagian dari belanja multi-toko Anda. Pembayaran untuk semua unit di bawah ini <strong>cukup 1 kali transfer</strong>:
                            </p>
                            <div className="d-flex flex-wrap gap-2">
                                <span className="badge bg-success">🏪 {order.unit?.nama_unit} (#{orderNumberDisplay} - {formatRupiah(order.grand_total ?? order.total_akhir)})</span>
                                {siblingOrders.map((sib) => (
                                    <Link key={sib.id} to={`/pesanan/${sib.id}`} className="badge bg-light text-dark text-decoration-none border">
                                        🏪 {sib.unit?.nama_unit} (#{sib.order_number || sib.no_pesanan} - {formatRupiah(sib.grand_total ?? sib.total_akhir)}) ↗
                                    </Link>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                <div className="row g-4">
                    {/* Left Column: Items & Shipping */}
                    <div className="col-lg-7">
                        {/* Items in this unit order */}
                        <div className="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                            <div className="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                                <h5 className="fw-bold mb-0 d-flex align-items-center gap-2 text-success">
                                    <Store size={20} /> Unit: {order.unit?.nama_unit}
                                </h5>
                                <span className="badge bg-secondary bg-opacity-25 text-dark">
                                    {order.details?.length || 0} Macam Produk
                                </span>
                            </div>

                            <div className="d-flex flex-column gap-3">
                                {order.details?.map((item) => {
                                    const itemPrice = Number(item.harga ?? item.harga_satuan ?? 0);
                                    const itemQty = Number(item.qty ?? item.jumlah ?? 1);
                                    const itemSubtotal = Number(item.subtotal ?? (itemPrice * itemQty));

                                    return (
                                        <div key={item.id} className="d-flex flex-wrap justify-content-between align-items-center pb-2 border-bottom last-border-none gap-2">
                                            <div>
                                                <h6 className="fw-semibold text-dark mb-1">{item.nama_produk}</h6>
                                                <div className="text-muted small">
                                                    {itemQty} item &times; {formatRupiah(itemPrice)}
                                                </div>
                                            </div>
                                            <div className="d-flex align-items-center gap-3">
                                                <div className="fw-bold text-dark">{formatRupiah(itemSubtotal)}</div>
                                                {order.status === 'selesai' && (
                                                    <button
                                                        onClick={() => handleBeriUlasan(item.product_id, item.nama_produk)}
                                                        className="btn btn-sm btn-outline-warning text-dark rounded-pill px-2 py-1 small fw-semibold"
                                                        title="Beri rating dan ulasan"
                                                    >
                                                        ⭐ Beri Ulasan
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                            <div className="pt-3 mt-2 border-top">
                                <div className="d-flex justify-content-between text-muted small mb-1">
                                    <span>Subtotal Produk Unit:</span>
                                    <span>{formatRupiah(order.total_produk ?? order.total_harga ?? 0)}</span>
                                </div>
                                <div className="d-flex justify-content-between text-muted small mb-1">
                                    <span>Ongkos Kirim Unit:</span>
                                    <span>{formatRupiah(order.ongkir ?? order.ongkos_kirim ?? 0)}</span>
                                </div>
                                {Number(order.diskon) > 0 && (
                                    <div className="d-flex justify-content-between text-success small mb-1">
                                        <span>Diskon Unit:</span>
                                        <span>-{formatRupiah(order.diskon)}</span>
                                    </div>
                                )}
                                <div className="d-flex justify-content-between fw-bold text-dark fs-6 pt-2 border-top">
                                    <span>Total Akhir Unit Ini:</span>
                                    <span className="text-success">{formatRupiah(order.grand_total ?? order.total_akhir ?? 0)}</span>
                                </div>
                            </div>
                        </div>

                        {/* Shipping & Delivery Address */}
                        <div className="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                            <h5 className="fw-bold mb-3 d-flex align-items-center gap-2">
                                <Truck size={20} className="text-success" /> Info Pengiriman & Resi
                            </h5>

                            {order.shipping ? (
                                <div className="row g-3 small">
                                    <div className="col-md-6">
                                        <span className="text-muted d-block">Penerima:</span>
                                        <strong className="text-dark">{order.shipping.nama_penerima || order.shipping.penerima_nama || order.user?.nama || 'Pelanggan'}</strong> ({order.shipping.no_hp || order.shipping.penerima_telepon || order.user?.no_hp || '-'})
                                    </div>
                                    <div className="col-md-6">
                                        <span className="text-muted d-block">Ekspedisi / Kurir:</span>
                                        <strong className="text-dark text-uppercase">{order.shipping.ekspedisi || order.shipping.kurir || 'Kurir Kampus Polinela'}</strong>
                                    </div>
                                    <div className="col-12">
                                        <span className="text-muted d-block">Alamat Pengiriman:</span>
                                        <div className="text-dark fw-medium">{order.shipping.alamat_lengkap || order.user?.alamat || '-'}, {order.shipping.kota || ''} {order.shipping.kode_pos || ''}</div>
                                    </div>
                                    <div className="col-md-6">
                                        <span className="text-muted d-block">Nomor Resi:</span>
                                        {order.shipping.no_resi ? (
                                            <span className="badge bg-primary fs-6">{order.shipping.no_resi}</span>
                                        ) : (
                                            <span className="text-muted fst-italic">Sedang disiapkan / Dalam antrian packing</span>
                                        )}
                                    </div>
                                    <div className="col-md-6">
                                        <span className="text-muted d-block">Status Pengiriman:</span>
                                        <span className="badge bg-info text-dark text-capitalize">{order.shipping.status_pengiriman || order.shipping.status || 'Diproses'}</span>
                                    </div>
                                </div>
                            ) : (
                                <div className="small">
                                    <span className="text-muted d-block">Penerima:</span>
                                    <strong className="text-dark">{order.user?.nama || 'Pelanggan'}</strong> ({order.user?.no_hp || '-'})
                                    <div className="text-muted mt-1">{order.user?.alamat || 'Alamat belum diatur'}</div>
                                </div>
                            )}
                        </div>

                        {/* Consumer Actions (Terima / Batal) */}
                        {order.status === 'dikirim' && (
                            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-success bg-opacity-10 border-success">
                                <h6 className="fw-bold text-success mb-2">Konfirmasi Barang Sampai</h6>
                                <p className="small text-muted mb-3">Jika Anda telah menerima pesanan ini dengan kondisi baik, silakan konfirmasi penerimaan.</p>
                                <button className="btn btn-success rounded-pill px-4" onClick={handleKonfirmasiTerima}>
                                    <CheckCircle2 size={16} className="me-1" /> Konfirmasi Paket Diterima
                                </button>
                            </div>
                        )}

                        {order.status === 'menunggu_pembayaran' && (
                            <div className="d-flex justify-content-end mb-4">
                                <button className="btn btn-outline-danger btn-sm rounded-pill px-3" onClick={handleBatalkanPesanan}>
                                    <XCircle size={14} className="me-1" /> Batalkan Pesanan Ini
                                </button>
                            </div>
                        )}
                    </div>

                    {/* Right Column: Unified 1x Payment Card */}
                    <div className="col-lg-5">
                        <div className="card border-0 shadow-sm rounded-4 p-4 sticky-top bg-white" style={{ top: '110px', zIndex: 10 }}>
                            <div className="d-flex align-items-center justify-content-between mb-3">
                                <h5 className="fw-bold mb-0 d-flex align-items-center gap-2">
                                    <CreditCard size={20} className="text-success" /> Pembayaran Terpadu
                                </h5>
                                <span className={`badge ${payBadge.bg}`}>{payBadge.label}</span>
                            </div>

                            {/* Total Payable Amount */}
                            <div className="p-3 bg-light rounded-4 mb-3">
                                <div className="text-muted small">Total Tagihan Gabungan (1x Bayar):</div>
                                <h3 className="fw-bold text-success mb-1">{formatRupiah(combinedTotal)}</h3>
                                <small className="text-muted">Kode Transaksi: <strong>{order.payment?.no_transaksi || '-'}</strong></small>
                            </div>

                            {/* Instant Automated Midtrans Payment Card */}
                            {isAwaitingPayment ? (
                                <div className="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-4 text-center mb-3">
                                    <div className="d-flex align-items-center justify-content-center gap-2 mb-2">
                                        <Zap size={20} className="text-warning fill-warning" />
                                        <h6 className="fw-bold text-success mb-0">Pembayaran Otomatis (Instant)</h6>
                                    </div>
                                    <p className="text-muted small mb-3" style={{ fontSize: '12px' }}>
                                        Pembayaran diverifikasi otomatis secara *real-time* oleh sistem tanpa perlu konfirmasi atau unggah struk manual.
                                    </p>
                                    
                                    <div className="d-flex justify-content-center flex-wrap gap-2 mb-3">
                                        <span className="badge bg-white text-dark border px-2 py-1 small">🏦 BCA / Mandiri / BRI / BNI VA</span>
                                        <span className="badge bg-white text-dark border px-2 py-1 small">📱 QRIS (Semua E-Wallet)</span>
                                        <span className="badge bg-white text-dark border px-2 py-1 small">🟢 GoPay & ShopeePay</span>
                                    </div>

                                    <button
                                        type="button"
                                        disabled={loadingSnap}
                                        onClick={handlePayMidtrans}
                                        className="btn btn-success w-100 rounded-pill fw-bold btn-lg shadow-sm py-2.5 d-flex align-items-center justify-content-center gap-2"
                                    >
                                        {loadingSnap ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm" role="status"></span>
                                                <span>Membuka Midtrans...</span>
                                            </>
                                        ) : (
                                            <>
                                                <Zap size={18} className="text-warning fill-warning" />
                                                <span>⚡ Bayar Sekarang via Midtrans</span>
                                            </>
                                        )}
                                    </button>
                                </div>
                            ) : (
                                <div className="p-3 bg-light rounded-4 text-center border">
                                    <CheckCircle2 size={32} className="text-success mb-2" />
                                    <h6 className="fw-bold text-success mb-1">Pembayaran Terverifikasi (Lunas)</h6>
                                    <p className="text-muted small mb-0" style={{ fontSize: '12px' }}>
                                        Pesanan ini telah lunas dan sedang dipersiapkan oleh unit pengelola toko.
                                    </p>
                                </div>
                            )}

                            {/* Display Existing Uploaded Proof */}
                            {order.payment?.bukti_bayar && (
                                <div className="mt-3 pt-3 border-top">
                                    <span className="text-muted small d-block mb-2">Bukti Pembayaran Terkirim:</span>
                                    <a
                                        href={getPaymentProofUrl(order.payment.bukti_bayar)}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="d-block rounded-3 overflow-hidden border p-1 bg-light text-center"
                                    >
                                        <img
                                            src={getPaymentProofUrl(order.payment.bukti_bayar)}
                                            alt="Bukti Transfer"
                                            className="w-100 rounded-2 object-fit-contain"
                                            style={{ maxHeight: '180px' }}
                                        />
                                        <span className="small text-primary d-block mt-1">Klik untuk memperbesar ↗</span>
                                    </a>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Receipt & Invoice Print Modal */}
            <ReceiptModal 
                isOpen={showReceiptModal} 
                onClose={() => setShowReceiptModal(false)} 
                order={order} 
            />
        </div>
    );
};

export default DetailPesanan;
