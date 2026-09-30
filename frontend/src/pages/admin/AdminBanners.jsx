import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { Image, Plus, Edit2, Trash2, ExternalLink } from 'lucide-react';

const AdminBanners = () => {
    const [banners, setBanners] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modalOpen, setModalOpen] = useState(false);
    const [editingBanner, setEditingBanner] = useState(null);
    const [formData, setFormData] = useState({
        judul: '',
        subjudul: '',
        gambar: '',
        link: '',
        urutan: 1,
        is_active: true,
    });
    const [saving, setSaving] = useState(false);

    const fetchBanners = async () => {
        setLoading(true);
        try {
            const res = await client.get('/admin/banners');
            if (res.data.status === 'success') {
                setBanners(res.data.data);
            }
        } catch (err) {
            console.error('Failed to load banners:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchBanners();
    }, []);

    const openCreateModal = () => {
        setEditingBanner(null);
        setFormData({
            judul: '',
            subjudul: '',
            gambar: 'banner-kopi.jpg',
            link: 'katalog',
            urutan: (banners.length + 1),
            is_active: true,
        });
        setModalOpen(true);
    };

    const openEditModal = (b) => {
        setEditingBanner(b);
        setFormData({
            judul: b.judul || '',
            subjudul: b.subjudul || '',
            gambar: b.gambar || '',
            link: b.link || '',
            urutan: Number(b.urutan) || 1,
            is_active: Boolean(b.is_active),
        });
        setModalOpen(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editingBanner) {
                await client.put(`/admin/banners/${editingBanner.id}`, formData);
            } else {
                await client.post('/admin/banners', formData);
            }
            setModalOpen(false);
            fetchBanners();
        } catch (err) {
            alert('Gagal menyimpan banner: ' + (err.response?.data?.message || err.message));
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id, judul) => {
        if (!window.confirm(`Yakin ingin menghapus banner "${judul}"?`)) return;
        try {
            await client.delete(`/admin/banners/${id}`);
            fetchBanners();
        } catch (err) {
            alert('Gagal menghapus banner: ' + (err.response?.data?.message || err.message));
        }
    };

    return (
        <div className="animate-fade-in">
            {/* Header */}
            <div className="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                <div>
                    <h3 className="fw-bold text-heading mb-1 d-flex align-items-center gap-2">
                        <Image className="text-agro" /> Kelola Banner Promosi
                    </h3>
                    <p className="text-muted small mb-0">Manajemen banner slider beranda toko Polinela Agro</p>
                </div>
                <button onClick={openCreateModal} className="btn btn-agro d-flex align-items-center gap-2">
                    <Plus size={18} /> Tambah Banner
                </button>
            </div>

            {/* Banners Grid */}
            {loading ? (
                <div className="text-center py-5">
                    <div className="spinner-border text-success" role="status"></div>
                </div>
            ) : banners.length === 0 ? (
                <div className="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                    <Image size={48} className="mx-auto mb-2 opacity-50" />
                    <p>Belum ada banner promosi yang dibuat.</p>
                </div>
            ) : (
                <div className="row g-4">
                    {banners.map((b) => {
                        const bannerImgSrc = b.gambar?.startsWith('http') 
                            ? b.gambar 
                            : 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=1200&auto=format&fit=crop&q=80';

                        return (
                            <div key={b.id} className="col-lg-6">
                                <div className="card border-0 shadow-sm rounded-4 overflow-hidden h-100 position-relative d-flex flex-column">
                                    {/* Uniform Banner Thumbnail Preview */}
                                    <div 
                                        className="position-relative w-100 bg-dark overflow-hidden" 
                                        style={{ height: '180px', minHeight: '180px', maxHeight: '180px' }}
                                    >
                                        <img 
                                            src={bannerImgSrc} 
                                            alt={b.judul}
                                            className="w-100 h-100"
                                            style={{ objectFit: 'cover', objectPosition: 'center' }}
                                            onError={(e) => {
                                                e.target.onerror = null;
                                                e.target.src = 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=1200&auto=format&fit=crop&q=80';
                                            }}
                                        />
                                        <div 
                                            className="position-absolute inset-0"
                                            style={{
                                                background: 'linear-gradient(90deg, rgba(15,23,42,0.85) 0%, rgba(15,23,42,0.4) 60%, rgba(15,23,42,0.15) 100%)',
                                                top: 0, left: 0, right: 0, bottom: 0
                                            }}
                                        ></div>
                                        
                                        <div className="position-absolute top-0 start-0 p-3 w-100 d-flex justify-content-between align-items-start z-1">
                                            <span className="badge bg-white text-dark shadow-sm rounded-pill fw-bold">
                                                Urutan #{b.urutan}
                                            </span>
                                            <span className={`badge rounded-pill shadow-sm ${b.is_active ? 'bg-success' : 'bg-secondary'}`}>
                                                {b.is_active ? '● Aktif' : 'Nonaktif'}
                                            </span>
                                        </div>

                                        <div className="position-absolute bottom-0 start-0 p-3 text-white z-1" style={{ maxWidth: '85%' }}>
                                            <h6 className="fw-bold text-white mb-1 text-truncate" title={b.judul}>
                                                {b.judul}
                                            </h6>
                                            <p className="small text-white-50 mb-0 text-truncate" title={b.subjudul}>
                                                {b.subjudul || 'Tidak ada subjudul'}
                                            </p>
                                        </div>
                                    </div>

                                    {/* Card Content Details */}
                                    <div className="p-4 d-flex flex-column flex-grow-1">
                                        <div className="small text-muted mb-3 flex-grow-1">
                                            <div className="d-flex align-items-center justify-content-between py-1 border-bottom">
                                                <span className="fw-semibold">File Gambar:</span>
                                                <code className="text-truncate" style={{ maxWidth: '220px' }}>{b.gambar}</code>
                                            </div>
                                            <div className="d-flex align-items-center justify-content-between py-1">
                                                <span className="fw-semibold">Link Tautan:</span>
                                                <span className="text-truncate text-agro" style={{ maxWidth: '220px' }}>{b.link || '-'}</span>
                                            </div>
                                        </div>

                                        <div className="d-flex justify-content-end gap-2 pt-2 border-top mt-auto">
                                            <button onClick={() => openEditModal(b)} className="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 rounded-pill px-3">
                                                <Edit2 size={14} /> Edit
                                            </button>
                                            <button onClick={() => handleDelete(b.id, b.judul)} className="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3">
                                                <Trash2 size={14} /> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            )}

            {/* Modal Form */}
            {modalOpen && (
                <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.6)' }} tabIndex="-1">
                    <div className="modal-dialog modal-dialog-centered modal-lg">
                        <div className="modal-content rounded-4 border-0 shadow-lg">
                            <div className="modal-header">
                                <h5 className="modal-title fw-bold text-heading">
                                    {editingBanner ? 'Edit Banner Promosi' : 'Tambah Banner Baru'}
                                </h5>
                                <button type="button" className="btn-close" onClick={() => setModalOpen(false)}></button>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="modal-body p-4">
                                    {/* Live Banner Preview Box */}
                                    <div className="mb-4">
                                        <label className="form-label small fw-semibold text-muted mb-2">Pratinjau Tampilan Banner (Ukuran Proporsional)</label>
                                        <div 
                                            className="hero-slider-wrap position-relative w-100 rounded-3 overflow-hidden shadow-sm"
                                            style={{ height: '180px', minHeight: '180px', maxHeight: '180px', background: '#0f172a' }}
                                        >
                                            <div 
                                                className="hero-slide-item w-100 h-100"
                                                style={{
                                                    backgroundImage: `url('${formData.gambar?.startsWith('http') ? formData.gambar : 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=1200&auto=format&fit=crop&q=80'}')`,
                                                    backgroundSize: 'cover',
                                                    backgroundPosition: 'center',
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                }}
                                            >
                                                <div className="hero-overlay"></div>
                                                <div className="hero-content p-3 text-white" style={{ maxWidth: '80%' }}>
                                                    <span className="badge bg-warning text-dark px-2 py-1 rounded-pill fw-bold small mb-2 d-inline-block">
                                                        Banner Preview
                                                    </span>
                                                    <h5 className="fw-bold text-white mb-1 text-truncate">
                                                        {formData.judul || 'Judul Banner Promosi'}
                                                    </h5>
                                                    <p className="small text-white-50 mb-0 text-truncate">
                                                        {formData.subjudul || 'Deskripsi singkat banner promosi beranda'}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label small fw-semibold">Judul Banner</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            required
                                            placeholder="Contoh: Panen Raya Kopi Robusta & Kakao"
                                            value={formData.judul}
                                            onChange={(e) => setFormData({ ...formData, judul: e.target.value })}
                                        />
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label small fw-semibold">Subjudul / Deskripsi Singkat</label>
                                        <textarea
                                            className="form-control"
                                            rows="2"
                                            placeholder="Keterangan promosi"
                                            value={formData.subjudul}
                                            onChange={(e) => setFormData({ ...formData, subjudul: e.target.value })}
                                        ></textarea>
                                    </div>

                                    <div className="row g-2 mb-3">
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">URL / Nama File Gambar</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                required
                                                placeholder="https://... atau banner-kopi.jpg"
                                                value={formData.gambar}
                                                onChange={(e) => setFormData({ ...formData, gambar: e.target.value })}
                                            />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Urutan Tampil</label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                min="1"
                                                value={formData.urutan}
                                                onChange={(e) => setFormData({ ...formData, urutan: Number(e.target.value) })}
                                            />
                                        </div>
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label small fw-semibold">Tautan / Halaman Target (Opsional)</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="Contoh: katalog?kategori=kopi"
                                            value={formData.link}
                                            onChange={(e) => setFormData({ ...formData, link: e.target.value })}
                                        />
                                    </div>

                                    <div className="form-check form-switch">
                                        <input
                                            className="form-check-input"
                                            type="checkbox"
                                            id="bannerActiveSwitch"
                                            checked={formData.is_active}
                                            onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                                        />
                                        <label className="form-check-label small" htmlFor="bannerActiveSwitch">
                                            Status Banner Aktif
                                        </label>
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-secondary rounded-pill px-3" onClick={() => setModalOpen(false)}>
                                        Batal
                                    </button>
                                    <button type="submit" disabled={saving} className="btn btn-agro rounded-pill px-4">
                                        {saving ? 'Menyimpan...' : 'Simpan Banner'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default AdminBanners;
