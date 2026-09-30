import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { formatRupiah, formatDate, getPaymentStatusBadge, getPaymentProofUrl } from '../../utils/format';
import { CheckCircle2, XCircle, Search, Eye, AlertCircle, ShieldCheck } from 'lucide-react';
import Swal from 'sweetalert2';

const AdminPayments = () => {
    const { user, isSuperadmin } = useAuth();

    const [payments, setPayments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [statusFilter, setStatusFilter] = useState('');
    const [search, setSearch] = useState('');

    // Preview proof modal
    const [previewProof, setPreviewProof] = useState(null);

    const fetchPayments = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (statusFilter) params.append('status', statusFilter);
            if (search) params.append('search', search);

            const res = await client.get(`/admin/payments?${params.toString()}`);
            if (res.data.status === 'success') {
                setPayments(res.data.data.data || []);
            }
        } catch (err) {
            console.error('Failed to load payments:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchPayments();
    }, [statusFilter, search]);

    const handleVerifikasi = async (payment) => {
        const result = await Swal.fire({
            title: 'Verifikasi Pembayaran?',
            html: `Apakah Anda yakin ingin memverifikasi pembayaran untuk pesanan <strong>#${payment.order?.no_pesanan}</strong> sejumlah <strong>${formatRupiah(payment.jumlah)}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2d6a4f',
            confirmButtonText: 'Ya, Verifikasi Lunas',
            cancelButtonText: 'Batal',
        });

        if (result.isConfirmed) {
            try {
                const res = await client.put(`/admin/payments/${payment.id}/verifikasi`);
                if (res.data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Diverifikasi!',
                        text: 'Status pesanan otomatis beralih menjadi Diproses.',
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    fetchPayments();
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Verifikasi',
                    text: err.response?.data?.message || 'Anda tidak memiliki hak akses untuk memverifikasi pembayaran unit ini.',
                });
            }
        }
    };

    const handleTolak = async (payment) => {
        const { value: alasan } = await Swal.fire({
            title: 'Tolak Pembayaran?',
            input: 'textarea',
            inputPlaceholder: 'Tuliskan alasan penolakan (misal: Bukti buram, nominal tidak cocok)...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Tolak Pembayaran',
            cancelButtonText: 'Batal',
            inputValidator: (val) => {
                if (!val) return 'Alasan penolakan wajib diisi!';
            },
        });

        if (alasan) {
            try {
                const res = await client.put(`/admin/payments/${payment.id}/tolak`, { alasan });
                if (res.data.status === 'success') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Pembayaran Ditolak',
                        text: 'Status pembayaran berhasil ditolak.',
                    });
                    fetchPayments();
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menolak',
                    text: err.response?.data?.message || 'Terjadi kesalahan sistem.',
                });
            }
        }
    };

    return (
        <div>
            {/* Header */}
            <div className="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 className="fw-bold text-dark mb-1">Verifikasi Pembayaran Unit</h3>
                    <p className="text-muted small mb-0">
                        {isSuperadmin
                            ? 'Verifikasi seluruh pembayaran masuk dari semua unit perkebunan.'
                            : `Privasi Terisolasi: Khusus memverifikasi transaksi produk untuk unit toko ${user?.unit?.nama_unit}.`}
                    </p>
                </div>
            </div>

            {/* Privacy notice banner */}
            {!isSuperadmin && (
                <div className="alert alert-success border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center gap-2 small">
                    <ShieldCheck size={18} className="text-success flex-shrink-0" />
                    <span>
                        <strong>Keamanan Hak Akses Aktif:</strong> Anda hanya dapat melihat dan memverifikasi bukti bayar yang berhubungan dengan pesanan <strong>{user?.unit?.nama_unit}</strong>.
                    </span>
                </div>
            )}

            {/* Filter Bar */}
            <div className="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
                <div className="row g-3 align-items-center">
                    <div className="col-md-5">
                        <div className="input-group input-group-sm">
                            <span className="input-group-text bg-light border-end-0"><Search size={16} /></span>
                            <input
                                type="text"
                                className="form-control bg-light border-start-0"
                                placeholder="Cari Kode Transaksi / No Pesanan..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                    </div>
                    <div className="col-md-7">
                        <div className="d-flex gap-1 overflow-auto pb-1">
                            {['', 'pending', 'menunggu_konfirmasi', 'lunas', 'ditolak'].map((st) => (
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

            {/* Payments Table */}
            <div className="card border-0 shadow-sm rounded-4 p-4 bg-white">
                {loading ? (
                    <div className="text-center py-5">
                        <div className="spinner-border text-success" role="status">
                            <span className="visually-hidden">Loading...</span>
                        </div>
                    </div>
                ) : payments.length === 0 ? (
                    <div className="text-center py-5 text-muted small">
                        Tidak ada data pembayaran yang ditemukan.
                    </div>
                ) : (
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0 small">
                            <thead className="table-light">
                                <tr>
                                    <th>Kode Transaksi</th>
                                    <th>No Pesanan</th>
                                    {isSuperadmin && <th>Unit Toko</th>}
                                    <th>Pembeli</th>
                                    <th>Nominal</th>
                                    <th>Bukti Bayar</th>
                                    <th>Status</th>
                                    <th className="text-end">Aksi Verifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {payments.map((p) => {
                                    const badge = getPaymentStatusBadge(p.status);
                                    const proofUrl = getPaymentProofUrl(p.bukti_bayar);

                                    return (
                                        <tr key={p.id}>
                                            <td className="fw-bold text-primary">{p.no_transaksi}</td>
                                            <td className="fw-semibold">#{p.order?.no_pesanan}</td>
                                            {isSuperadmin && <td><span className="badge bg-light text-dark border">{p.order?.unit?.nama_unit}</span></td>}
                                            <td>
                                                <div className="fw-semibold">{p.order?.user?.nama_lengkap || p.order?.user?.username}</div>
                                                <small className="text-muted">{p.order?.user?.no_hp}</small>
                                            </td>
                                            <td className="fw-bold text-success">{formatRupiah(p.jumlah)}</td>
                                            <td>
                                                {proofUrl ? (
                                                    <button
                                                        type="button"
                                                        className="btn btn-sm btn-outline-info rounded-pill px-2 d-inline-flex align-items-center gap-1"
                                                        onClick={() => setPreviewProof({ url: proofUrl, payment: p })}
                                                    >
                                                        <Eye size={13} />
                                                        <span>Lihat Bukti</span>
                                                    </button>
                                                ) : (
                                                    <span className="text-muted fst-italic">Belum upload</span>
                                                )}
                                            </td>
                                            <td><span className={`badge ${badge.bg}`}>{badge.label}</span></td>
                                            <td className="text-end">
                                                {p.status === 'menunggu_konfirmasi' ? (
                                                    <div className="d-flex justify-content-end gap-2">
                                                        <button
                                                            className="btn btn-sm btn-success rounded-pill px-3 d-inline-flex align-items-center gap-1"
                                                            onClick={() => handleVerifikasi(p)}
                                                        >
                                                            <CheckCircle2 size={14} />
                                                            <span>Verifikasi</span>
                                                        </button>
                                                        <button
                                                            className="btn btn-sm btn-outline-danger rounded-pill px-2"
                                                            onClick={() => handleTolak(p)}
                                                            title="Tolak"
                                                        >
                                                            <XCircle size={14} />
                                                        </button>
                                                    </div>
                                                ) : (
                                                    <span className="text-muted small">
                                                        {p.status === 'lunas' ? '✅ Terverifikasi' : (p.status === 'ditolak' ? '❌ Ditolak' : 'Menunggu')}
                                                    </span>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {/* Preview Proof Modal */}
            {previewProof && (
                <div className="modal show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.6)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content rounded-4 border-0 shadow">
                            <div className="modal-header border-bottom">
                                <h5 className="modal-title fw-bold">Bukti Pembayaran #{previewProof.payment.order?.no_pesanan}</h5>
                                <button type="button" className="btn-close" onClick={() => setPreviewProof(null)}></button>
                            </div>
                            <div className="modal-body text-center p-3">
                                <img
                                    src={previewProof.url}
                                    alt="Bukti Bayar"
                                    className="img-fluid rounded-3 border"
                                    style={{ maxHeight: '420px' }}
                                />
                                <div className="mt-3 small text-muted">
                                    Nominal Tagihan: <strong className="text-success">{formatRupiah(previewProof.payment.jumlah)}</strong> ({previewProof.payment.bank || 'Transfer Bank'})
                                </div>
                            </div>
                            <div className="modal-footer border-top d-flex justify-content-between">
                                <button type="button" className="btn btn-light rounded-pill px-3" onClick={() => setPreviewProof(null)}>Tutup</button>
                                {previewProof.payment.status === 'menunggu_konfirmasi' && (
                                    <div className="d-flex gap-2">
                                        <button
                                            type="button"
                                            className="btn btn-danger rounded-pill px-3"
                                            onClick={() => {
                                                const p = previewProof.payment;
                                                setPreviewProof(null);
                                                handleTolak(p);
                                            }}
                                        >
                                            Tolak
                                        </button>
                                        <button
                                            type="button"
                                            className="btn btn-success rounded-pill px-3 fw-bold"
                                            onClick={() => {
                                                const p = previewProof.payment;
                                                setPreviewProof(null);
                                                handleVerifikasi(p);
                                            }}
                                        >
                                            Verifikasi Lunas
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default AdminPayments;
