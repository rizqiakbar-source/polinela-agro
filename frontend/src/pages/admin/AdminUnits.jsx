import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { Store, Plus, Edit2, Trash2, CheckCircle2, XCircle, Search, Save, X } from 'lucide-react';

const AdminUnits = () => {
    const [units, setUnits] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [modalOpen, setModalOpen] = useState(false);
    const [editingUnit, setEditingUnit] = useState(null);
    const [formData, setFormData] = useState({
        nama_unit: '',
        deskripsi: '',
        pj_nama: '',
        kontak: '',
        lokasi: '',
        is_active: true,
    });
    const [saving, setSaving] = useState(false);

    const fetchUnits = async () => {
        setLoading(true);
        try {
            const res = await client.get('/admin/units');
            if (res.data.status === 'success') {
                setUnits(res.data.data);
            }
        } catch (err) {
            console.error('Failed to load units:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchUnits();
    }, []);

    const openCreateModal = () => {
        setEditingUnit(null);
        setFormData({
            nama_unit: '',
            deskripsi: '',
            pj_nama: '',
            kontak: '',
            lokasi: '',
            is_active: true,
        });
        setModalOpen(true);
    };

    const openEditModal = (unit) => {
        setEditingUnit(unit);
        setFormData({
            nama_unit: unit.nama_unit || '',
            deskripsi: unit.deskripsi || '',
            pj_nama: unit.pj_nama || '',
            kontak: unit.kontak || '',
            lokasi: unit.lokasi || '',
            is_active: Boolean(unit.is_active),
        });
        setModalOpen(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editingUnit) {
                await client.put(`/admin/units/${editingUnit.id}`, formData);
            } else {
                await client.post('/admin/units', formData);
            }
            setModalOpen(false);
            fetchUnits();
        } catch (err) {
            alert('Gagal menyimpan unit: ' + (err.response?.data?.message || err.message));
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id, nama) => {
        if (!window.confirm(`Yakin ingin menghapus unit "${nama}"?`)) return;
        try {
            await client.delete(`/admin/units/${id}`);
            fetchUnits();
        } catch (err) {
            alert('Gagal menghapus unit: ' + (err.response?.data?.message || err.message));
        }
    };

    const filteredUnits = units.filter(u => 
        u.nama_unit?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        u.pj_nama?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="animate-fade-in">
            {/* Header */}
            <div className="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                <div>
                    <h3 className="fw-bold text-heading mb-1 d-flex align-items-center gap-2">
                        <Store className="text-agro" /> Kelola Unit Usaha & TEFA
                    </h3>
                    <p className="text-muted small mb-0">Manajemen unit Teaching Factory perkebunan Polinela</p>
                </div>
                <button onClick={openCreateModal} className="btn btn-agro d-flex align-items-center gap-2">
                    <Plus size={18} /> Tambah Unit TEFA
                </button>
            </div>

            {/* Search Bar */}
            <div className="card border-0 shadow-sm rounded-3 p-3 mb-4">
                <div className="input-group">
                    <span className="input-group-text bg-input border-end-0">
                        <Search size={16} className="text-muted" />
                    </span>
                    <input
                        type="text"
                        className="form-control bg-input border-start-0 ps-0"
                        placeholder="Cari nama unit atau penanggung jawab..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
            </div>

            {/* Units Grid / Table */}
            {loading ? (
                <div className="text-center py-5">
                    <div className="spinner-border text-success" role="status"></div>
                </div>
            ) : filteredUnits.length === 0 ? (
                <div className="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                    <Store size={48} className="mx-auto mb-2 opacity-50" />
                    <p>Tidak ada unit usaha yang ditemukan.</p>
                </div>
            ) : (
                <div className="row g-4">
                    {filteredUnits.map((unit) => (
                        <div key={unit.id} className="col-lg-6">
                            <div className="card border-0 shadow-sm rounded-4 p-4 h-100 unit-card position-relative">
                                <div className="d-flex justify-content-between align-items-start mb-2">
                                    <h5 className="fw-bold text-heading mb-0">{unit.nama_unit}</h5>
                                    <span className={`badge rounded-pill ${unit.is_active ? 'bg-success' : 'bg-secondary'}`}>
                                        {unit.is_active ? 'Aktif' : 'Nonaktif'}
                                    </span>
                                </div>
                                <p className="text-muted small mb-3">{unit.deskripsi || 'Tidak ada deskripsi.'}</p>
                                
                                <div className="small border-top pt-3 mt-auto d-flex flex-column gap-1 text-muted">
                                    <div><strong>PJ:</strong> {unit.pj_nama || '-'}</div>
                                    <div><strong>Kontak:</strong> {unit.kontak || '-'}</div>
                                    <div><strong>Lokasi:</strong> {unit.lokasi || '-'}</div>
                                    <div><strong>Total Produk:</strong> {unit.products_count || 0} produk</div>
                                </div>

                                <div className="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                                    <button onClick={() => openEditModal(unit)} className="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 rounded-pill px-3">
                                        <Edit2 size={14} /> Edit
                                    </button>
                                    <button onClick={() => handleDelete(unit.id, unit.nama_unit)} className="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3">
                                        <Trash2 size={14} /> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Modal Form */}
            {modalOpen && (
                <div className="modal show d-block" style={{ backgroundColor: 'rgba(0,0,0,0.6)' }} tabIndex="-1">
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content rounded-4 border-0 shadow-lg">
                            <div className="modal-header">
                                <h5 className="modal-title fw-bold text-heading">
                                    {editingUnit ? 'Edit Unit TEFA' : 'Tambah Unit TEFA Baru'}
                                </h5>
                                <button type="button" className="btn-close" onClick={() => setModalOpen(false)}></button>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="modal-body p-4">
                                    <div className="mb-3">
                                        <label className="form-label small fw-semibold">Nama Unit Usaha</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            required
                                            placeholder="Contoh: Unit Kopi Polinela"
                                            value={formData.nama_unit}
                                            onChange={(e) => setFormData({ ...formData, nama_unit: e.target.value })}
                                        />
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label small fw-semibold">Deskripsi</label>
                                        <textarea
                                            className="form-control"
                                            rows="3"
                                            placeholder="Deskripsi kegiatan dan produk unit TEFA"
                                            value={formData.deskripsi}
                                            onChange={(e) => setFormData({ ...formData, deskripsi: e.target.value })}
                                        ></textarea>
                                    </div>

                                    <div className="row g-2 mb-3">
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Penanggung Jawab (PJ)</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                placeholder="Nama PJ & Gelar"
                                                value={formData.pj_nama}
                                                onChange={(e) => setFormData({ ...formData, pj_nama: e.target.value })}
                                            />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Kontak / No HP</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                placeholder="0812-xxxx-xxxx"
                                                value={formData.kontak}
                                                onChange={(e) => setFormData({ ...formData, kontak: e.target.value })}
                                            />
                                        </div>
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label small fw-semibold">Lokasi / Gedung</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            placeholder="Lokasi di kampus Polinela"
                                            value={formData.lokasi}
                                            onChange={(e) => setFormData({ ...formData, lokasi: e.target.value })}
                                        />
                                    </div>

                                    <div className="form-check form-switch">
                                        <input
                                            className="form-check-input"
                                            type="checkbox"
                                            id="isActiveSwitch"
                                            checked={formData.is_active}
                                            onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                                        />
                                        <label className="form-check-label small" htmlFor="isActiveSwitch">
                                            Status Unit Aktif
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

export default AdminUnits;
