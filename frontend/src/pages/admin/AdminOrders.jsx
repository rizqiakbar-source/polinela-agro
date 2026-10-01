import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { formatRupiah, formatDate, getOrderStatusBadge, getPaymentStatusBadge } from '../../utils/format';
import { Eye, Truck, CheckCircle2, XCircle, Search, Edit3, Printer, Package, MessageCircle, Send } from 'lucide-react';
import Swal from 'sweetalert2';
import ReceiptModal from '../../components/ReceiptModal';

const AdminOrders = () => {
    const { isSuperadmin } = useAuth();

    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [statusFilter, setStatusFilter] = useState('');
    const [search, setSearch] = useState('');
    const [receiptOrder, setReceiptOrder] = useState(null);

    // Detail / Resi modal
    const [selectedOrder, setSelectedOrder] = useState(null);
    const [statusUpdate, setStatusUpdate] = useState('');
    const [noResi, setNoResi] = useState('');
    const [catatanAdmin, setCatatanAdmin] = useState('');
    const [updating, setUpdating] = useState(false);

    const fetchOrders = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (statusFilter) params.append('status', statusFilter);
            if (search) params.append('search', search);

            const res = await client.get(`/admin/orders?${params.toString()}`);
            if (res.data.status === 'success' || res.data.success) {
                const ordersList = Array.isArray(res.data.data) 
                    ? res.data.data 
                    : (res.data.data?.data || res.data.orders || []);
                setOrders(ordersList);
            }
        } catch (err) {
            console.error('Failed to load admin orders:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchOrders();
    }, [statusFilter, search]);

    const handleOpenOrderModal = (order) => {
        setSelectedOrder(order);
        setStatusUpdate(order.status);
        setNoResi(order.shipping?.no_resi || '');
        setCatatanAdmin(order.catatan_admin || '');
    };

    const handleUpdateStatus = async (e) => {
        e.preventDefault();
        setUpdating(true);
        try {
            const res = await client.put(`/admin/orders/${selectedOrder.id}/status`, {
                status: statusUpdate,
                catatan_admin: catatanAdmin,
            });

            if (res.data.status === 'success' || res.data.success) {
                // If resi also changed
                if (noResi !== (selectedOrder.shipping?.no_resi || '')) {
                    await client.put(`/admin/orders/${selectedOrder.id}/resi`, {
                        no_resi: noResi,
                        status_pengiriman: statusUpdate === 'dikirim' ? 'dikirim' : (statusUpdate === 'selesai' ? 'diterima' : 'diproses'),
                    });
                }

                const waUrl = res.data.whatsapp_url;

                Swal.fire({
                    icon: 'success',
                    title: 'Status Diperbarui!',
                    html: `
                        <p class="mb-2">Status pesanan berhasil diubah.</p>
                        <small class="text-success d-block">Notifikasi Email & In-App otomatis terkirim ke pembeli.</small>
                    `,
                    showCancelButton: !!waUrl,
                    cancelButtonText: 'Kirim WhatsApp ke Pembeli',
                    cancelButtonColor: '#16a34a',
                    confirmButtonText: 'Selesai',
                    confirmButtonColor: '#15803d',
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel && waUrl) {
                        window.open(waUrl, '_blank');
                    }
                });

                setSelectedOrder(null);
                fetchOrders();
            }
        } catch (error) {
            const msg = error.response?.data?.message || 'Gagal mengubah status pesanan.';
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
        } finally {
            setUpdating(false);
        }
    };

    const handleQuickShip = async (order) => {
        const orderNo = order.order_number || order.no_pesanan || `#${order.id}`;
        const recipient = order.shipping?.nama_penerima || order.shipping?.penerima_nama || order.user?.nama || 'Pelanggan';
        const phone = order.shipping?.no_hp || order.shipping?.penerima_telepon || order.user?.no_hp || '-';
        const address = order.shipping?.alamat_lengkap || '-';
        const courier = (order.shipping?.ekspedisi || order.shipping?.kurir || 'Kurir Polinela').toUpperCase();

        const { value: formValues } = await Swal.fire({
            title: `Kirim Pesanan ${orderNo}`,
            html: `
                <div class="text-start small p-3 bg-light rounded-3 mb-3 border">
                    <p class="mb-1"><strong>Penerima:</strong> ${recipient} (${phone})</p>
                    <p class="mb-1"><strong>Alamat:</strong> ${address}</p>
                    <p class="mb-0"><strong>Kurir / Ekspedisi:</strong> <span class="badge bg-primary">${courier}</span></p>
                </div>
                <div class="text-start">
                    <label class="form-label fw-semibold small">Masukkan Nomor Resi Pengiriman:</label>
                    <input id="swal-input-resi" class="form-control mb-2" placeholder="Contoh: PLNL-${order.id}-8899 atau Resi Kurir" value="${order.shipping?.no_resi || ''}" autofocus />
                    <small class="text-muted" style="font-size: 11px;">Nomor resi ini akan otomatis dikirimkan ke WhatsApp dan Email pembeli.</small>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Konfirmasi Kirim Sekarang',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0284c7',
            preConfirm: () => {
                const resi = document.getElementById('swal-input-resi')?.value.trim();
                return { resi: resi || `PLNL-${order.id}-${Date.now().toString().slice(-4)}` };
            }
        });

        if (formValues) {
            Swal.fire({
                title: 'Mengirim Pesanan...',
                text: 'Sedang memproses pembaruan resi dan mengirim notifikasi...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const res = await client.put(`/admin/orders/${order.id}/status`, {
                    status: 'dikirim',
                    no_resi: formValues.resi,
                });
                if (res.data.status === 'success' || res.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesanan Berhasil Dikirim!',
                        text: `Nomor resi (${formValues.resi}) dan notifikasi telah dikirim ke pembeli.`,
                        timer: 2500,
                        showConfirmButton: false,
                    });
                    fetchOrders();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengirim Pesanan',
                        text: res.data.message || 'Terjadi kendala pada server.',
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengirim Pesanan',
                    text: err.response?.data?.message || err.message || 'Terjadi kesalahan sistem.',
                });
                fetchOrders();
            }
        }
    };

    const handleQuickComplete = async (order) => {
        const orderNo = order.order_number || order.no_pesanan || `#${order.id}`;
        const result = await Swal.fire({
            title: `Selesaikan Pesanan ${orderNo}?`,
            text: 'Pesanan akan ditandai telah selesai diterima pembeli. Notifikasi terima kasih & ulasan produk akan otomatis dikirim ke pembeli.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Selesaikan Pesanan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
        });

        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menyelesaikan Pesanan...',
                text: 'Sedang memproses verifikasi dan mengirim notifikasi...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const res = await client.put(`/admin/orders/${order.id}/status`, {
                    status: 'selesai',
                });
                if (res.data.status === 'success' || res.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesanan Selesai!',
                        text: 'Pesanan resmi selesai dan notifikasi telah dikirim ke pembeli.',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    fetchOrders();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyelesaikan Pesanan',
                        text: res.data.message || 'Terjadi kendala pada server.',
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyelesaikan Pesanan',
                    text: err.response?.data?.message || err.message || 'Terjadi kesalahan sistem.',
                });
                fetchOrders();
            }
        }
    };

    const handleChatCustomerWA = (order) => {
        const phone = order.shipping?.penerima_telepon || order.shipping?.no_hp || order.user?.no_hp || '';
        if (!phone) {
            Swal.fire({ icon: 'warning', title: 'Nomor Tidak Ditemukan', text: 'Nomor telepon/WhatsApp pembeli tidak tersedia.' });
            return;
        }
        const clean = phone.replace(/[^0-9]/g, '');
        const intl = clean.startsWith('0') ? '62' + clean.substring(1) : (clean.startsWith('8') ? '62' + clean : clean);
        const orderNum = order.order_number || order.no_pesanan || `#${order.id}`;
        const name = order.shipping?.penerima_nama || order.shipping?.nama_penerima || order.user?.nama || 'Pelanggan';
        const msg = `Halo Kak ${name},\nKami dari pengelola unit ${order.unit?.nama_unit || 'Polinela Agro Digital'}.\n\nMengenai pesanan Anda #${orderNum}:\nStatus saat ini: *${order.status.toUpperCase()}*.\n${order.shipping?.no_resi ? `Nomor Resi: *${order.shipping.no_resi}*\n` : ''}\nTerima kasih telah berbelanja di Teaching Factory Polinela Agro! 🌱`;
        window.open(`https://wa.me/${intl}?text=${encodeURIComponent(msg)}`, '_blank');
    };

    return (
        <div>
            {/* Header */}
            <div className="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 className="fw-bold text-dark mb-1">Manajemen Pesanan Unit</h3>
                    <p className="text-muted small mb-0">Kelola pemrosesan order dan pengiriman nomor resi khusus toko unit Anda.</p>
                </div>
            </div>

            {/* Filter Tabs & Search */}
            <div className="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
                <div className="row g-3 align-items-center">
                    <div className="col-md-5">
                        <div className="input-group input-group-sm">
                            <span className="input-group-text bg-light border-end-0"><Search size={16} /></span>
                            <input
                                type="text"
                                className="form-control bg-light border-start-0"
                                placeholder="Cari No Pesanan / Nama Pembeli..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                    </div>
                    <div className="col-md-7">
                        <div className="d-flex gap-1 overflow-auto pb-1">
                            {['', 'menunggu_pembayaran', 'menunggu_verifikasi', 'diproses', 'dikirim', 'selesai', 'dibatalkan'].map((st) => (
                                <button
                                    key={st}
                                    type="button"
                                    className={`btn btn-sm rounded-pill text-nowrap ${statusFilter === st ? 'btn-success text-white fw-bold' : 'btn-light text-dark'}`}
                                    onClick={() => setStatusFilter(st)}
                                >
                                    {st ? st.replace('_', ' ') : 'Semua'}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Orders Table */}
            <div className="card border-0 shadow-sm rounded-4 p-4 bg-white">
                {loading ? (
                    <div className="text-center py-5">
                        <div className="spinner-border text-success" role="status">
                            <span className="visually-hidden">Loading...</span>
                        </div>
                    </div>
                ) : orders.length === 0 ? (
                    <div className="text-center py-5 text-muted small">
                        Tidak ada data pesanan yang sesuai filter.
                    </div>
                ) : (
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0 small">
                            <thead className="table-light">
                                <tr>
                                    <th>No Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Pembeli</th>
                                    {isSuperadmin && <th>Unit Toko</th>}
                                    <th>Total Akhir</th>
                                    <th>Status Pesanan</th>
                                    <th>Status Bayar</th>
                                    <th className="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {orders.map((o) => {
                                    const badge = getOrderStatusBadge(o.status);
                                    const isPaid = o.payment?.status === 'lunas' || ['diproses', 'dikirim', 'selesai'].includes(o.status);
                                    const payBadge = isPaid 
                                        ? { bg: 'bg-success text-white', label: 'Lunas' } 
                                        : getPaymentStatusBadge(o.payment?.status);
                                    const orderNo = o.order_number || o.no_pesanan || `#${o.id}`;
                                    const customerName = o.shipping?.nama_penerima || o.shipping?.penerima_nama || o.user?.nama || o.user?.nama_lengkap || o.user?.username || 'Pelanggan';
                                    const customerPhone = o.shipping?.no_hp || o.shipping?.penerima_telepon || o.user?.no_hp || '-';
                                    const totalAmount = o.grand_total ?? o.total_akhir ?? 0;

                                    return (
                                        <tr key={o.id}>
                                            <td className="fw-bold text-primary">{orderNo}</td>
                                            <td className="text-muted">{formatDate(o.created_at)}</td>
                                            <td>
                                                <div className="fw-semibold text-dark">{customerName}</div>
                                                <small className="text-muted">{customerPhone}</small>
                                            </td>
                                            {isSuperadmin && <td><span className="badge bg-light text-dark border">{o.unit?.nama_unit}</span></td>}
                                            <td className="fw-bold text-success">{formatRupiah(totalAmount)}</td>
                                            <td><span className={`badge ${badge.bg}`}>{badge.label}</span></td>
                                            <td><span className={`badge ${payBadge.bg}`}>{payBadge.label}</span></td>
                                            <td className="text-end">
                                                <div className="d-flex justify-content-end align-items-center gap-1 flex-wrap">
                                                    {o.status === 'diproses' && (
                                                        <button
                                                            type="button"
                                                            className="btn btn-sm btn-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1 fw-semibold"
                                                            onClick={() => handleQuickShip(o)}
                                                            title="Kirim pesanan dan input nomor resi"
                                                        >
                                                            <Truck size={14} />
                                                            <span>Kirim (Resi)</span>
                                                        </button>
                                                    )}

                                                    {o.status === 'dikirim' && (
                                                        <button
                                                            type="button"
                                                            className="btn btn-sm btn-success rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1 fw-semibold"
                                                            onClick={() => handleQuickComplete(o)}
                                                            title="Konfirmasi pesanan telah selesai diterima pembeli"
                                                        >
                                                            <CheckCircle2 size={14} />
                                                            <span>Selesaikan</span>
                                                        </button>
                                                    )}

                                                    <button
                                                        type="button"
                                                        className="btn btn-sm btn-outline-secondary rounded-pill px-2 d-inline-flex align-items-center gap-1"
                                                        onClick={() => handleOpenOrderModal(o)}
                                                        title="Detail Pesanan & Edit Status"
                                                    >
                                                        <Edit3 size={13} />
                                                        <span>Detail</span>
                                                    </button>

                                                    <button
                                                        type="button"
                                                        className="btn btn-sm btn-light border rounded-pill px-2 d-inline-flex align-items-center"
                                                        onClick={() => setReceiptOrder(o)}
                                                        title="Cetak Struk / Invoice"
                                                    >
                                                        <Printer size={13} />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {/* Modal Order Processing */}
            {selectedOrder && (
                <div className="modal show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-lg modal-dialog-centered">
                        <div className="modal-content rounded-4 border-0 shadow">
                            <div className="modal-header border-bottom">
                                <h5 className="modal-title fw-bold">
                                    Proses Pesanan #{selectedOrder.order_number || selectedOrder.no_pesanan || selectedOrder.id} ({selectedOrder.unit?.nama_unit})
                                </h5>
                                <button type="button" className="btn-close" onClick={() => setSelectedOrder(null)}></button>
                            </div>
                            <form onSubmit={handleUpdateStatus}>
                                <div className="modal-body p-4">
                                    {/* Quick Status Buttons */}
                                    <div className="mb-4">
                                        <label className="form-label small fw-bold text-muted text-uppercase mb-2 d-block">Pilih Status Cepat:</label>
                                        <div className="d-flex flex-wrap gap-2">
                                            <button
                                                type="button"
                                                className={`btn btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1 ${statusUpdate === 'diproses' ? 'btn-warning text-dark fw-bold shadow-sm' : 'btn-outline-secondary'}`}
                                                onClick={() => setStatusUpdate('diproses')}
                                            >
                                                <Package size={14} />
                                                <span>Sedang Diproses (Packing)</span>
                                            </button>
                                            <button
                                                type="button"
                                                className={`btn btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1 ${statusUpdate === 'dikirim' ? 'btn-primary text-white fw-bold shadow-sm' : 'btn-outline-primary'}`}
                                                onClick={() => setStatusUpdate('dikirim')}
                                            >
                                                <Truck size={14} />
                                                <span>Sedang Dikirim</span>
                                            </button>
                                            <button
                                                type="button"
                                                className={`btn btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1 ${statusUpdate === 'selesai' ? 'btn-success text-white fw-bold shadow-sm' : 'btn-outline-success'}`}
                                                onClick={() => setStatusUpdate('selesai')}
                                            >
                                                <CheckCircle2 size={14} />
                                                <span>Selesai (Diterima)</span>
                                            </button>
                                            <button
                                                type="button"
                                                className={`btn btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1 ${statusUpdate === 'dibatalkan' ? 'btn-danger text-white fw-bold shadow-sm' : 'btn-outline-danger'}`}
                                                onClick={() => setStatusUpdate('dibatalkan')}
                                            >
                                                <XCircle size={14} />
                                                <span>Batalkan</span>
                                            </button>
                                        </div>
                                    </div>
                                    {/* Order Items */}
                                    <div className="p-3 bg-light rounded-3 mb-3">
                                        <h6 className="fw-bold small mb-2">Item Produk Pesanan:</h6>
                                        {selectedOrder.details?.map((item) => (
                                            <div key={item.id} className="d-flex justify-content-between align-items-center small py-1 border-bottom last-border-none">
                                                <span>{item.nama_produk} &times; {item.qty || item.jumlah || 1}</span>
                                                <strong className="text-dark">{formatRupiah(item.subtotal)}</strong>
                                            </div>
                                        ))}
                                    </div>

                                    {/* Shipping Address */}
                                    {selectedOrder.shipping && (
                                        <div className="p-3 border rounded-3 mb-3 small">
                                            <h6 className="fw-bold small mb-1">Tujuan Pengiriman:</h6>
                                            <div className="text-muted">
                                                <strong>{selectedOrder.shipping.nama_penerima || selectedOrder.shipping.penerima_nama}</strong> ({selectedOrder.shipping.no_hp || selectedOrder.shipping.penerima_telepon})<br />
                                                {selectedOrder.shipping.alamat_lengkap}, {selectedOrder.shipping.kota}, {selectedOrder.shipping.kode_pos}<br />
                                                Kurir: <strong className="text-uppercase">{selectedOrder.shipping.ekspedisi || selectedOrder.shipping.kurir}</strong>
                                            </div>
                                        </div>
                                    )}

                                    <div className="row g-3">
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Ubah Status Pesanan</label>
                                            <select
                                                className="form-select"
                                                value={statusUpdate}
                                                onChange={(e) => setStatusUpdate(e.target.value)}
                                            >
                                                <option value="menunggu_pembayaran">Menunggu Pembayaran</option>
                                                <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                                                <option value="diproses">Sedang Diproses (Packing)</option>
                                                <option value="dikirim">Sedang Dikirim</option>
                                                <option value="selesai">Selesai (Pesanan Diterima)</option>
                                                <option value="dibatalkan">Dibatalkan</option>
                                            </select>
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Nomor Resi Pengiriman</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                placeholder="Contoh: PLNL-889922001"
                                                value={noResi}
                                                onChange={(e) => setNoResi(e.target.value)}
                                            />
                                        </div>

                                        <div className="col-12">
                                            <label className="form-label small fw-semibold">Catatan Admin untuk Pembeli</label>
                                            <textarea
                                                className="form-control"
                                                rows="2"
                                                placeholder="Catatan packing atau instruksi tambahan..."
                                                value={catatanAdmin}
                                                onChange={(e) => setCatatanAdmin(e.target.value)}
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div className="modal-footer border-top d-flex justify-content-between flex-wrap gap-2">
                                    <div className="d-flex gap-2">
                                        <button 
                                            type="button" 
                                            className="btn btn-outline-secondary rounded-pill px-3 d-flex align-items-center gap-1"
                                            onClick={() => setReceiptOrder(selectedOrder)}
                                        >
                                            <Printer size={15} />
                                            <span>Cetak Struk</span>
                                        </button>
                                        <button 
                                            type="button" 
                                            className="btn btn-outline-success rounded-pill px-3 d-flex align-items-center gap-1"
                                            onClick={() => handleChatCustomerWA(selectedOrder)}
                                            title="Chat WhatsApp langsung ke pembeli"
                                        >
                                            <MessageCircle size={15} />
                                            <span>WhatsApp Pembeli</span>
                                        </button>
                                    </div>
                                    <div className="d-flex gap-2">
                                        <button type="button" className="btn btn-light rounded-pill px-4" onClick={() => setSelectedOrder(null)}>Tutup</button>
                                        <button type="submit" disabled={updating} className="btn btn-success rounded-pill px-4 fw-bold">
                                            {updating ? 'Menyimpan...' : 'Simpan Perubahan'}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* Receipt Modal */}
            <ReceiptModal 
                isOpen={!!receiptOrder}
                onClose={() => setReceiptOrder(null)}
                order={receiptOrder}
            />
        </div>
    );
};

export default AdminOrders;
