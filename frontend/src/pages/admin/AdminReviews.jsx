import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { Star, Trash2, Search, MessageSquare } from 'lucide-react';
import { formatDate } from '../../utils/format';

const AdminReviews = () => {
    const [reviews, setReviews] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');

    const fetchReviews = async () => {
        setLoading(true);
        try {
            const res = await client.get('/admin/reviews');
            if (res.data.status === 'success') {
                const rData = res.data.data;
                setReviews(Array.isArray(rData) ? rData : (rData?.data || []));
            }
        } catch (err) {
            console.error('Failed to load reviews:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchReviews();
    }, []);

    const handleDelete = async (id) => {
        if (!window.confirm('Yakin ingin menghapus ulasan ini?')) return;
        try {
            await client.delete(`/admin/reviews/${id}`);
            fetchReviews();
        } catch (err) {
            alert('Gagal menghapus ulasan: ' + (err.response?.data?.message || err.message));
        }
    };

    const filteredReviews = reviews.filter(r => 
        r.product?.nama_produk?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        r.user?.nama?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        r.komentar?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="animate-fade-in">
            {/* Header */}
            <div className="mb-4">
                <h3 className="fw-bold text-heading mb-1 d-flex align-items-center gap-2">
                    <MessageSquare className="text-agro" /> Kelola Ulasan & Rating Produk
                </h3>
                <p className="text-muted small mb-0">Moderasi feedback dan ulasan pembeli terhadap produk perkebunan</p>
            </div>

            {/* Search */}
            <div className="card border-0 shadow-sm rounded-3 p-3 mb-4">
                <div className="input-group">
                    <span className="input-group-text bg-input border-end-0">
                        <Search size={16} className="text-muted" />
                    </span>
                    <input
                        type="text"
                        className="form-control bg-input border-start-0 ps-0"
                        placeholder="Cari ulasan berdasarkan produk, pembeli, atau isi komentar..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
            </div>

            {/* Table */}
            <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div className="table-responsive">
                    <table className="table table-hover align-middle mb-0">
                        <thead className="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Pembeli</th>
                                <th>Rating</th>
                                <th>Ulasan</th>
                                <th>Tanggal</th>
                                <th className="text-end" style={{ width: '80px' }}>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr>
                                    <td colSpan="6" className="text-center py-4">
                                        <div className="spinner-border text-success" role="status"></div>
                                    </td>
                                </tr>
                            ) : filteredReviews.length === 0 ? (
                                <tr>
                                    <td colSpan="6" className="text-center py-4 text-muted">
                                        Belum ada ulasan produk yang masuk.
                                    </td>
                                </tr>
                            ) : (
                                filteredReviews.map((r) => (
                                    <tr key={r.id}>
                                        <td>
                                            <div className="fw-semibold text-heading">{r.product?.nama_produk || 'Produk Dihapus'}</div>
                                            <small className="text-muted">{r.product?.unit?.nama_unit || '-'}</small>
                                        </td>
                                        <td>
                                            <div className="fw-medium">{r.user?.nama || 'Anonim'}</div>
                                            <small className="text-muted">{r.user?.email || '-'}</small>
                                        </td>
                                        <td>
                                            <div className="d-flex align-items-center gap-1 text-warning fw-bold">
                                                <Star size={16} fill="#ffc107" />
                                                <span>{r.rating || 5}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <p className="mb-0 small text-body">{r.komentar || 'Tidak ada komentar tertulis.'}</p>
                                        </td>
                                        <td className="small text-muted">{formatDate(r.created_at)}</td>
                                        <td className="text-end">
                                            <button onClick={() => handleDelete(r.id)} className="btn btn-sm btn-outline-danger rounded-circle p-1" title="Hapus Ulasan">
                                                <Trash2 size={14} />
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default AdminReviews;
