import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { Ticket, Plus, Edit2, Trash2, Calendar } from 'lucide-react';
import { formatRupiah } from '../../utils/format';

const AdminVouchers = () => {
    const [vouchers, setVouchers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modalOpen, setModalOpen] = useState(false);
    const [editingVoucher, setEditingVoucher] = useState(null);
    const [formData, setFormData] = useState({
        kode: '',
        nama: '',
        tipe: 'fixed',
        diskon: 0,
        min_belanja: 0,
        max_diskon: 0,
        kuota: 100,
        tgl_mulai: '',
        tgl_berakhir: '',
        is_active: true,
    });
    const [saving, setSaving] = useState(false);

    const fetchVouchers = async () => {
        setLoading(true);
        try {
            const res = await client.get('/admin/vouchers');
            if (res.data.status === 'success') {
                setVouchers(res.data.data);
            }
        } catch (err) {
            console.error('Failed to load vouchers:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchVouchers();
    }, []);

    const openCreateModal = () => {
        setEditingVoucher(null);
        setFormData({
            kode: '',
            nama: '',
            tipe: 'fixed',
            diskon: 10000,
            min_belanja: 50000,
            max_diskon: 10000,
            kuota: 100,
            tgl_mulai: new Date().toISOString().split('T')[0],
            tgl_berakhir: '2026-12-31',
            is_active: true,
        });
        setModalOpen(true);
    };

    const openEditModal = (v) => {
        setEditingVoucher(v);
        setFormData({
            kode: v.kode || '',
            nama: v.nama || '',
            tipe: v.tipe || 'fixed',
            diskon: Number(v.diskon) || 0,
            min_belanja: Number(v.min_belanja) || 0,
            max_diskon: Number(v.max_diskon) || 0,
            kuota: Number(v.kuota) || 100,
            tgl_mulai: v.tgl_mulai ? v.tgl_mulai.split('T')[0] : '',
            tgl_berakhir: v.tgl_berakhir ? v.tgl_berakhir.split('T')[0] : '',
            is_active: Boolean(v.is_active),
        });
        setModalOpen(true);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editingVoucher) {
                await client.put(`/admin/vouchers/${editingVoucher.id}`, formData);
            } else {
                await client.post('/admin/vouchers', formData);
            }
            setModalOpen(false);
            fetchVouchers();
        } catch (err) {
            alert('Gagal menyimpan voucher: ' + (err.response?.data?.message || err.message));
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id, kode) => {
        if (!window.confirm(`Yakin ingin menghapus voucher "${kode}"?`)) return;
        try {
            await client.delete(`/admin/vouchers/${id}`);
            fetchVouchers();
        } catch (err) {
            alert('Gagal menghapus voucher: ' + (err.response?.data?.message || err.message));
        }
    };

    return (
        <div className="animate-fade-in">
            {/* Header */}
            <div className="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                <div>
                    <h3 className="fw-bold text-heading mb-1 d-flex align-items-center gap-2">
                        <Ticket className="text-agro" /> Kelola Voucher & Promo
                    </h3>
                    <p className="text-muted small mb-0">Manajemen kode kupon diskon untuk belanja di Polinela Agro</p>
                </div>
                <button onClick={openCreateModal} className="btn btn-agro d-flex align-items-center gap-2">
                    <Plus size={18} /> Tambah Voucher
                </button>
            </div>

            {/* Table */}
            <div className="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div className="table-responsive">
                    <table className="table table-hover align-middle mb-0">
                        <thead className="table-light">
                            <tr>
                                <th>Kode Voucher</th>
                                <th>Nama Promo</th>
                                <th>Diskon</th>
                                <th>Min. Belanja</th>
                                <th>Pemakaian</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th className="text-end" style={{ width: '120px' }}>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr>
                                    <td colSpan="8" className="text-center py-4">
                                        <div className="spinner-border text-success" role="status"></div>
                                    </td>
                                </tr>
                            ) : vouchers.length === 0 ? (
                                <tr>
                                    <td colSpan="8" className="text-center py-4 text-muted">
                                        Belum ada voucher promo yang dibuat.
                                    </td>
                                </tr>
                            ) : (
                                vouchers.map((v) => (
                                    <tr key={v.id}>
                                        <td>
                                            <span className="badge bg-agro px-3 py-2 fw-bold font-monospace" style={{ letterSpacing: '1px' }}>
                                                {v.kode}
                                            </span>
                                        </td>
                                        <td className="fw-semibold text-heading">{v.nama}</td>
                                        <td className="fw-bold text-success">
                                            {v.tipe === 'persen' ? `${Number(v.diskon)}%` : formatRupiah(v.diskon)}
                                        </td>
                                        <td>{formatRupiah(v.min_belanja)}</td>
                                        <td>
                                            <span className="small">
                                                <strong>{v.terpakai || 0}</strong> / {v.kuota || '∞'}
                                            </span>
                                        </td>
                                        <td className="small text-muted">
                                            {v.tgl_mulai || '-'} s/d {v.tgl_berakhir || '-'}
                                        </td>
                                        <td>
                                            <span className={`badge rounded-pill ${v.is_active ? 'bg-success' : 'bg-secondary'}`}>
                                                {v.is_active ? 'Aktif' : 'Nonaktif'}
                                            </span>
                                        </td>
                                        <td className="text-end">
                                            <button onClick={() => openEditModal(v)} className="btn btn-sm btn-outline-primary rounded-circle p-1 me-1" title="Edit">
                                                <Edit2 size={14} />
                                            </button>
                                            <button onClick={() => handleDelete(v.id, v.kode)} className="btn btn-sm btn-outline-danger rounded-circle p-1" title="Hapus">
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
                                    {editingVoucher ? 'Edit Voucher' : 'Buat Voucher Baru'}
                                </h5>
                                <button type="button" className="btn-close" onClick={() => setModalOpen(false)}></button>
                            </div>
                            <form onSubmit={handleSubmit}>
                                <div className="modal-body p-4">
                                    <div className="row g-2 mb-3">
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Kode Voucher (Kapital)</label>
                                            <input
                                                type="text"
                                                className="form-control text-uppercase fw-bold font-monospace"
                                                required
                                                placeholder="POLINELAJUARA"
                                                value={formData.kode}
                                                onChange={(e) => setFormData({ ...formData, kode: e.target.value.toUpperCase() })}
                                            />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Tipe Diskon</label>
                                            <select
                                                className="form-select"
                                                value={formData.tipe}
                                                onChange={(e) => setFormData({ ...formData, tipe: e.target.value })}
                                            >
                                                <option value="fixed">Nominal Tetap (Rp)</option>
                                                <option value="persen">Persentase (%)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div className="mb-3">
                                        <label className="form-label small fw-semibold">Nama Promo</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            required
                                            placeholder="Contoh: Promo Panen Raya 10%"
                                            value={formData.nama}
                                            onChange={(e) => setFormData({ ...formData, nama: e.target.value })}
                                        />
                                    </div>

                                    <div className="row g-2 mb-3">
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">
                                                Nilai Diskon {formData.tipe === 'persen' ? '(%)' : '(Rp)'}
                                            </label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                required
                                                min="0"
                                                value={formData.diskon}
                                                onChange={(e) => setFormData({ ...formData, diskon: Number(e.target.value) })}
                                            />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Min. Belanja (Rp)</label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                min="0"
                                                value={formData.min_belanja}
                                                onChange={(e) => setFormData({ ...formData, min_belanja: Number(e.target.value) })}
                                            />
                                        </div>
                                    </div>

                                    <div className="row g-2 mb-3">
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Kuota Pemakaian</label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                min="1"
                                                value={formData.kuota}
                                                onChange={(e) => setFormData({ ...formData, kuota: Number(e.target.value) })}
                                            />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Maks. Diskon (Rp, Opsional)</label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                min="0"
                                                value={formData.max_diskon}
                                                onChange={(e) => setFormData({ ...formData, max_diskon: Number(e.target.value) })}
                                            />
                                        </div>
                                    </div>

                                    <div className="row g-2 mb-3">
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Tanggal Mulai</label>
                                            <input
                                                type="date"
                                                className="form-control"
                                                value={formData.tgl_mulai}
                                                onChange={(e) => setFormData({ ...formData, tgl_mulai: e.target.value })}
                                            />
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Tanggal Berakhir</label>
                                            <input
                                                type="date"
                                                className="form-control"
                                                value={formData.tgl_berakhir}
                                                onChange={(e) => setFormData({ ...formData, tgl_berakhir: e.target.value })}
                                            />
                                        </div>
                                    </div>

                                    <div className="form-check form-switch">
                                        <input
                                            className="form-check-input"
                                            type="checkbox"
                                            id="voucherActiveSwitch"
                                            checked={formData.is_active}
                                            onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                                        />
                                        <label className="form-check-label small" htmlFor="voucherActiveSwitch">
                                            Status Voucher Aktif
                                        </label>
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-secondary rounded-pill px-3" onClick={() => setModalOpen(false)}>
                                        Batal
                                    </button>
                                    <button type="submit" disabled={saving} className="btn btn-agro rounded-pill px-4">
                                        {saving ? 'Menyimpan...' : 'Simpan Voucher'}
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

export default AdminVouchers;
