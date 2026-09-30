import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { Tag, Plus, Edit2, Trash2, Search } from 'lucide-react';

const AdminCategories = () => {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modalOpen, setModalOpen] = useState(false);
    const [editingCategory, setEditingCategory] = useState(null);
    const [formData, setFormData] = useState({
        nama_kategori: '',
        deskripsi: '',
        icon: '',
        is_active: true,
    });
    const [saving, setSaving] = useState(false);

    const fetchCategories = async () => {
        setLoading(true);
        try {
            const res = await client.get('/admin/categories');
            if (res.data.status === 'success') {
                setCategories(res.data.data);
            }
        } catch (err) {
            console.error('Failed to load categories:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchCategories();
    }, []);

    const openCreateModal = () => {
        setEditingCategory(null);
        setFormData({
            nama_kategori: '',
            deskripsi: '',
            icon: 'bi-box',
            is_active: true,
        });
        setModalOpen(true);
    };

    const openEditModal = (cat) => {
        setEditingCategory(cat);
        setFormData({
            nama_kategori: cat.nama_kategori || '',
            deskripsi: cat.deskripsi || '',
            icon: cat.icon || 'bi-box',
            is_active: Boolean(cat.is_active),
        });
        setModalOpen(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editingCategory) {
                await client.put(`/admin/categories/${editingCategory.id}`, formData);
            } else {
                await client.post('/admin/categories', formData);
            }
            setModalOpen(false);
            fetchCategories();
        } catch (err) {
            alert('Gagal menyimpan kategori: ' + (err.response?.data?.message || err.message));
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id, nama) => {
        if (!window.confirm(`Yakin ingin menghapus kategori "${nama}"?`)) return;
        try {
            await client.delete(`/admin/categories/${id}`);
            fetchCategories();
        } catch (err) {
            alert('Gagal menghapus kategori: ' + (err.response?.data?.message || err.message));
        }
    };

    return (
        <div className="animate-fade-in">
            {/* Header */}
            <div className="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                <div>
                    <h3 className="fw-bold text-heading mb-1 d-flex align-items-center gap-2">
                        <Tag className="text-agro" /> Kelola Kategori Produk
                    </h3>
                    <p className="text-muted small mb-0">Manajemen pengelompokan komoditas hasil perkebunan</p>
                </div>
                <button onClick={openCreateModal} className="btn btn-agro d-flex align-items-center gap-2">
                    <Plus size={18} /> Tambah Kategori
                </button>
            </div>

            {/* Table */}
            <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div className="table-responsive">
                    <table className="table table-hover align-middle mb-0">
                        <thead className="table-light">
                            <tr>
                                <th style={{ width: '60px' }}>#</th>
                                <th>Nama Kategori</th>
                                <th>Slug</th>
                                <th>Deskripsi</th>
                                <th>Jumlah Produk</th>
                                <th>Status</th>
                                <th className="text-end" style={{ width: '140px' }}>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr>
                                    <td colSpan="7" className="text-center py-4">
                                        <div className="spinner-border text-success" role="status"></div>
                                    </td>
                                </tr>
                            ) : categories.length === 0 ? (
                                <tr>
                                    <td colSpan="7" className="text-center py-4 text-muted">
                                        Belum ada kategori yang dibuat.
                                    </td>
                                </tr>
                            ) : (
                                categories.map((cat, idx) => (
                                    <tr key={cat.id}>
                                        <td>{idx + 1}</td>
                                        <td className="fw-semibold text-heading">{cat.nama_kategori}</td>
                                        <td><code>{cat.slug}</code></td>
                                        <td className="small text-muted text-truncate" style={{ maxWidth: '250px' }}>
                                            {cat.deskripsi || '-'}
                                        </td>
                                        <td>
                                            <span className="badge bg-agro-subtle rounded-pill">
                                                {cat.products_count || 0} produk
                                            </span>
                                        </td>
                                        <td>
                                            <span className={`badge rounded-pill ${cat.is_active ? 'bg-success' : 'bg-secondary'}`}>
                                                {cat.is_active ? 'Aktif' : 'Nonaktif'}
                                            </span>
                                        </td>
                                        <td className="text-end">
                                            <button onClick={() => openEditModal(cat)} className="btn btn-sm btn-outline-primary rounded-circle p-1 me-1" title="Edit">
                                                <Edit2 size={14} />
                                            </button>
                                            <button onClick={() => handleDelete(cat.id, cat.nama_kategori)} className="btn btn-sm btn-outline-danger rounded-circle p-1" title="Hapus">
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

            {/* Modal Form */}
            {modalOpen && (
                <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.6)' }} tabIndex="-1">
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content rounded-4 border-0 shadow-lg">
                            <div className="modal-header">
                                <h5 className="modal-title fw-bold text-heading">
                                    {editingCategory ? 'Edit Kategori' : 'Tambah Kategori Baru'}
                                </h5>
                                <button type="button" className="btn-close" onClick={() => setModalOpen(false)}></button>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="modal-body p-4">
                                    <div className="mb-3">
                                        <label className="form-label small fw-semibold">Nama Kategori</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            required
                                            placeholder="Contoh: Kopi Nusantara Polinela"
                                            value={formData.nama_kategori}
                                            onChange={(e) => setFormData({ ...formData, nama_kategori: e.target.value })}
                                        />
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label small fw-semibold">Deskripsi</label>
                                        <textarea
                                            className="form-control"
                                            rows="3"
                                            placeholder="Deskripsi jenis produk dalam kategori ini"
                                            value={formData.deskripsi}
                                            onChange={(e) => setFormData({ ...formData, deskripsi: e.target.value })}
                                        ></textarea>
                                    </div>

                                    <div className="form-check form-switch">
                                        <input
                                            className="form-check-input"
                                            type="checkbox"
                                            id="catActiveSwitch"
                                            checked={formData.is_active}
                                            onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                                        />
                                        <label className="form-check-label small" htmlFor="catActiveSwitch">
                                            Status Kategori Aktif
                                        </label>
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-secondary rounded-pill px-3" onClick={() => setModalOpen(false)}>
                                        Batal
                                    </button>
                                    <button type="submit" disabled={saving} className="btn btn-agro rounded-pill px-4">
                                        {saving ? 'Menyimpan...' : 'Simpan'}
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

export default AdminCategories;
