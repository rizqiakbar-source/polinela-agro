import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { formatDate } from '../../utils/format';
import { Plus, Edit2, Trash2, Search, UserCheck, Shield } from 'lucide-react';
import Swal from 'sweetalert2';

const AdminUsers = () => {
    const [users, setUsers] = useState([]);
    const [units, setUnits] = useState([]);
    const [loading, setLoading] = useState(true);
    const [roleFilter, setRoleFilter] = useState('');
    const [search, setSearch] = useState('');

    // Modal state
    const [showModal, setShowModal] = useState(false);
    const [editingUser, setEditingUser] = useState(null);
    const [formData, setFormData] = useState({
        username: '',
        nama_lengkap: '',
        email: '',
        password: '',
        role: 'admin_unit',
        unit_id: '',
        no_hp: '',
        is_active: true,
    });
    const [saving, setSaving] = useState(false);

    const fetchUsers = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (roleFilter) params.append('role', roleFilter);
            if (search) params.append('search', search);

            const [usersRes, unitsRes] = await Promise.all([
                client.get(`/admin/users?${params.toString()}`),
                client.get('/units'),
            ]);

            if (usersRes.data.status === 'success') {
                setUsers(usersRes.data.data.data || []);
            }
            if (unitsRes.data.status === 'success') {
                setUnits(unitsRes.data.data || []);
            }
        } catch (err) {
            console.error('Failed to load users:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchUsers();
    }, [roleFilter, search]);

    const handleOpenModal = (user = null) => {
        if (user) {
            setEditingUser(user);
            setFormData({
                username: user.username,
                nama_lengkap: user.nama_lengkap || '',
                email: user.email,
                password: '',
                role: user.role,
                unit_id: user.unit_id || '',
                no_hp: user.no_hp || '',
                is_active: !!user.is_active,
            });
        } else {
            setEditingUser(null);
            setFormData({
                username: '',
                nama_lengkap: '',
                email: '',
                password: '',
                role: 'admin_unit',
                unit_id: units[0]?.id || '',
                no_hp: '',
                is_active: true,
            });
        }
        setShowModal(true);
    };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (editingUser) {
                const res = await client.put(`/admin/users/${editingUser.id}`, formData);
                if (res.data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'User Diperbarui', timer: 1500, showConfirmButton: false });
                }
            } else {
                const res = await client.post('/admin/users', formData);
                if (res.data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'User Dibuat', timer: 1500, showConfirmButton: false });
                }
            }
            setShowModal(false);
            fetchUsers();
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: err.response?.data?.message || 'Gagal menyimpan user.' });
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (user) => {
        const result = await Swal.fire({
            title: 'Hapus Pengguna?',
            text: `Apakah Anda yakin ingin menghapus akun "${user.username}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
        });

        if (result.isConfirmed) {
            try {
                const res = await client.delete(`/admin/users/${user.id}`);
                if (res.data.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: 'Pengguna telah dihapus.' });
                    fetchUsers();
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.response?.data?.message || 'Gagal menghapus user.' });
            }
        }
    };

    return (
        <div>
            {/* Header */}
            <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 className="fw-bold text-dark mb-1">Kelola Pengguna & Hak Akses Unit</h3>
                    <p className="text-muted small mb-0">Atur penugasan Admin Unit Toko, Superadmin, dan Pimpinan.</p>
                </div>
                <button className="btn btn-success rounded-pill px-4 fw-bold d-flex align-items-center gap-2" onClick={() => handleOpenModal()}>
                    <Plus size={18} /> Tambah Pengguna Baru
                </button>
            </div>

            {/* Filter Bar */}
            <div className="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
                <div className="row g-3 align-items-center">
                    <div className="col-md-5">
                        <div className="input-group input-group-sm">
                            <span className="input-group-text bg-light border-end-0"><Search size={16} /></span>
                            <input
                                type="text"
                                className="form-control bg-light border-start-0"
                                placeholder="Cari nama / email / username..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                    </div>
                    <div className="col-md-7">
                        <div className="d-flex gap-1 overflow-auto pb-1">
                            {['', 'superadmin', 'admin_unit', 'pimpinan', 'konsumen'].map((r) => (
                                <button
                                    key={r}
                                    type="button"
                                    className={`btn btn-sm rounded-pill text-nowrap ${roleFilter === r ? 'btn-success text-white fw-bold' : 'btn-light text-dark'}`}
                                    onClick={() => setRoleFilter(r)}
                                >
                                    {r ? r.replace('_', ' ') : 'Semua Role'}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Users Table */}
            <div className="card border-0 shadow-sm rounded-4 p-4 bg-white">
                {loading ? (
                    <div className="text-center py-5">
                        <div className="spinner-border text-success" role="status">
                            <span className="visually-hidden">Loading...</span>
                        </div>
                    </div>
                ) : users.length === 0 ? (
                    <div className="text-center py-5 text-muted small">Tidak ada data pengguna.</div>
                ) : (
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0 small">
                            <thead className="table-light">
                                <tr>
                                    <th>Pengguna</th>
                                    <th>Email / HP</th>
                                    <th>Role</th>
                                    <th>Penugasan Unit</th>
                                    <th>Status</th>
                                    <th className="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.map((u) => (
                                    <tr key={u.id}>
                                        <td>
                                            <div className="fw-bold text-dark">{u.nama_lengkap || u.username}</div>
                                            <small className="text-muted">@{u.username}</small>
                                        </td>
                                        <td>
                                            <div>{u.email}</div>
                                            <small className="text-muted">{u.no_hp || '-'}</small>
                                        </td>
                                        <td>
                                            <span className={`badge ${u.role === 'superadmin' ? 'bg-danger' : (u.role === 'admin_unit' ? 'bg-primary' : (u.role === 'pimpinan' ? 'bg-info text-dark' : 'bg-secondary'))} text-capitalize`}>
                                                {u.role.replace('_', ' ')}
                                            </span>
                                        </td>
                                        <td>
                                            {u.unit ? (
                                                <span className="badge bg-success">🏪 {u.unit.nama_unit}</span>
                                            ) : (
                                                <span className="text-muted">-</span>
                                            )}
                                        </td>
                                        <td>
                                            <span className={`badge ${u.is_active ? 'bg-success' : 'bg-danger'}`}>
                                                {u.is_active ? 'Aktif' : 'Nonaktif'}
                                            </span>
                                        </td>
                                        <td className="text-end">
                                            <div className="d-flex justify-content-end gap-2">
                                                <button
                                                    className="btn btn-sm btn-outline-primary rounded-circle p-2"
                                                    title="Edit User"
                                                    onClick={() => handleOpenModal(u)}
                                                >
                                                    <Edit2 size={14} />
                                                </button>
                                                <button
                                                    className="btn btn-sm btn-outline-danger rounded-circle p-2"
                                                    title="Hapus User"
                                                    onClick={() => handleDelete(u)}
                                                >
                                                    <Trash2 size={14} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {/* Modal User Form */}
            {showModal && (
                <div className="modal show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-dialog-centered">
                        <div className="modal-content rounded-4 border-0 shadow">
                            <div className="modal-header border-bottom">
                                <h5 className="modal-title fw-bold">
                                    {editingUser ? 'Edit Pengguna' : 'Tambah Pengguna Baru'}
                                </h5>
                                <button type="button" className="btn-close" onClick={() => setShowModal(false)}></button>
                            </div>
                            <form onSubmit={handleSave}>
                                <div className="modal-body p-4">
                                    <div className="row g-3">
                                        <div className="col-12">
                                            <label className="form-label small fw-semibold">Nama Lengkap</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                required
                                                value={formData.nama_lengkap}
                                                onChange={(e) => setFormData({ ...formData, nama_lengkap: e.target.value })}
                                            />
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Username</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                required
                                                value={formData.username}
                                                onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                                            />
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Email</label>
                                            <input
                                                type="email"
                                                className="form-control"
                                                required
                                                value={formData.email}
                                                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                            />
                                        </div>

                                        <div className="col-12">
                                            <label className="form-label small fw-semibold">Password {editingUser && '(Kosongkan jika tidak diubah)'}</label>
                                            <input
                                                type="password"
                                                className="form-control"
                                                required={!editingUser}
                                                placeholder="••••••••"
                                                value={formData.password}
                                                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                                            />
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Role Pengguna</label>
                                            <select
                                                className="form-select"
                                                value={formData.role}
                                                onChange={(e) => setFormData({ ...formData, role: e.target.value })}
                                            >
                                                <option value="admin_unit">Admin Unit Toko</option>
                                                <option value="superadmin">Superadmin</option>
                                                <option value="pimpinan">Pimpinan</option>
                                                <option value="konsumen">Konsumen</option>
                                            </select>
                                        </div>

                                        {formData.role === 'admin_unit' && (
                                            <div className="col-md-6">
                                                <label className="form-label small fw-semibold">Tugaskan ke Unit</label>
                                                <select
                                                    className="form-select"
                                                    required
                                                    value={formData.unit_id}
                                                    onChange={(e) => setFormData({ ...formData, unit_id: e.target.value })}
                                                >
                                                    <option value="">-- Pilih Unit Toko --</option>
                                                    {units.map((u) => (
                                                        <option key={u.id} value={u.id}>{u.nama_unit}</option>
                                                    ))}
                                                </select>
                                            </div>
                                        )}

                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">No. HP / WhatsApp</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                value={formData.no_hp}
                                                onChange={(e) => setFormData({ ...formData, no_hp: e.target.value })}
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div className="modal-footer border-top">
                                    <button type="button" className="btn btn-light rounded-pill px-4" onClick={() => setShowModal(false)}>Batal</button>
                                    <button type="submit" disabled={saving} className="btn btn-success rounded-pill px-4 fw-bold">
                                        {saving ? 'Menyimpan...' : 'Simpan User'}
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

export default AdminUsers;
