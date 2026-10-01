import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { formatRupiah, getImageUrl } from '../../utils/format';
import { Plus, Edit2, Trash2, Search, Package, Image as ImageIcon, Store, Layers, RefreshCw, CheckCircle2, XCircle, AlertTriangle } from 'lucide-react';
import Swal from 'sweetalert2';

const AdminProducts = () => {
    const { user, isSuperadmin, isAdminUnit } = useAuth();

    const [products, setProducts] = useState([]);
    const [units, setUnits] = useState([]);
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [selectedCategory, setSelectedCategory] = useState('');

    // Modal states
    const [showModal, setShowModal] = useState(false);
    const [editingProduct, setEditingProduct] = useState(null);
    const [formData, setFormData] = useState({
        unit_id: '',
        category_id: '',
        nama_produk: '',
        deskripsi: '',
        harga: '',
        berat_gram: '500',
        satuan: 'Pcs',
        is_active: true,
        stok: '10',
    });
    const [imageFile, setImageFile] = useState(null);
    const [imagePreview, setImagePreview] = useState(null);
    const [saving, setSaving] = useState(false);

    const fetchProducts = async () => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (selectedCategory) params.append('category_id', selectedCategory);

            const [prodRes, unitsRes, catRes] = await Promise.all([
                client.get(`/admin/products?${params.toString()}`),
                client.get('/units'),
                client.get('/categories'),
            ]);

            if (prodRes.data.status === 'success' || prodRes.data.success) {
                const prodData = prodRes.data.data;
                setProducts(Array.isArray(prodData) ? prodData : (prodData?.data || []));
            }
            if (unitsRes.data.status === 'success' || unitsRes.data.success) {
                setUnits(unitsRes.data.data || []);
            }
            if (catRes.data.status === 'success' || catRes.data.success) {
                setCategories(catRes.data.data || []);
            }
        } catch (err) {
            console.error('Failed to load products:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchProducts();
    }, [search, selectedCategory]);

    const handleOpenModal = (product = null) => {
        if (product) {
            setEditingProduct(product);
            setFormData({
                unit_id: product.unit_id || '',
                category_id: product.category_id || (categories[0]?.id || ''),
                nama_produk: product.nama_produk || '',
                deskripsi: product.deskripsi || '',
                harga: product.harga || '',
                berat_gram: product.berat_gram || '500',
                satuan: product.satuan || 'Pcs',
                is_active: product.status === 'aktif' || product.is_active,
                stok: product.stok ?? product.total_stok ?? 10,
            });
            setImagePreview(getImageUrl(product.gambar_utama || product.images?.[0]?.gambar));
        } else {
            setEditingProduct(null);
            setFormData({
                unit_id: user?.unit_id || units[0]?.id || '',
                category_id: categories[0]?.id || '',
                nama_produk: '',
                deskripsi: '',
                harga: '',
                berat_gram: '500',
                satuan: 'Pcs',
                is_active: true,
                stok: '10',
            });
            setImagePreview(null);
        }
        setImageFile(null);
        setShowModal(true);
    };

    const handleImageChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setImageFile(file);
            setImagePreview(URL.createObjectURL(file));
        }
    };

    const handleQuickStock = async (product) => {
        const { value: newStock } = await Swal.fire({
            title: `Sesuaikan Stok Produk`,
            html: `
                <div class="text-start mb-2">
                    <strong>${product.nama_produk}</strong><br/>
                    <small class="text-muted">Stok saat ini: ${product.stok || product.total_stok || 0} ${product.satuan || 'Pcs'}</small>
                </div>
            `,
            input: 'number',
            inputLabel: 'Jumlah Stok Baru',
            inputValue: product.stok || product.total_stok || 0,
            showCancelButton: true,
            confirmButtonText: 'Simpan Stok',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10b981',
            inputValidator: (val) => {
                if (val === '' || isNaN(val) || parseInt(val) < 0) {
                    return 'Masukkan jumlah stok yang valid (minimal 0)!';
                }
            }
        });

        if (newStock !== undefined) {
            try {
                const res = await client.put(`/admin/products/${product.id}/stock`, {
                    stok: parseInt(newStock)
                });
                if (res.data.status === 'success' || res.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Stok Diperbarui!',
                        text: `Stok ${product.nama_produk} kini ${newStock} ${product.satuan || 'Pcs'}.`,
                        timer: 1500,
                        showConfirmButton: false,
                    });
                    fetchProducts();
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Update Stok',
                    text: err.response?.data?.message || 'Terjadi kesalahan sistem.',
                });
            }
        }
    };

    const handleSave = async (e) => {
        e.preventDefault();
        setSaving(true);

        const data = new FormData();
        data.append('category_id', formData.category_id);
        data.append('nama_produk', formData.nama_produk);
        data.append('deskripsi', formData.deskripsi || '');
        data.append('harga', formData.harga);
        data.append('berat_gram', formData.berat_gram);
        data.append('satuan', formData.satuan);
        data.append('stok', formData.stok);
        data.append('is_active', formData.is_active ? '1' : '0');
        
        if (isSuperadmin && formData.unit_id) {
            data.append('unit_id', formData.unit_id);
        } else if (user?.unit_id) {
            data.append('unit_id', user.unit_id);
        }

        if (imageFile) {
            data.append('gambar', imageFile);
        }

        try {
            if (editingProduct) {
                data.append('_method', 'PUT');
                const res = await client.post(`/admin/products/${editingProduct.id}`, data, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                if (res.data.status === 'success' || res.data.success) {
                    Swal.fire({ icon: 'success', title: 'Produk Diperbarui!', timer: 1500, showConfirmButton: false });
                }
            } else {
                const res = await client.post('/admin/products', data, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                if (res.data.status === 'success' || res.data.success) {
                    Swal.fire({ icon: 'success', title: 'Produk Berhasil Ditambahkan!', timer: 1500, showConfirmButton: false });
                }
            }

            setShowModal(false);
            fetchProducts();
        } catch (error) {
            const msg = error.response?.data?.message || 'Gagal menyimpan produk.';
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (productId, name) => {
        const result = await Swal.fire({
            title: 'Hapus Produk?',
            text: `Apakah Anda yakin ingin menghapus produk "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        });

        if (result.isConfirmed) {
            try {
                const res = await client.delete(`/admin/products/${productId}`);
                if (res.data.status === 'success' || res.data.success) {
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: 'Produk berhasil dihapus.' });
                    fetchProducts();
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.response?.data?.message || 'Gagal menghapus produk.' });
            }
        }
    };

    return (
        <div className="container-fluid p-0">
            {/* Header */}
            <div className="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 className="fw-bold mb-1 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                        <Package size={26} className="text-agro" />
                        Katalog & Stok Produk {user?.unit?.nama_unit ? `(${user.unit.nama_unit})` : ''}
                    </h3>
                    <p className="text-muted small mb-0">
                        Kelola katalog olahan perkebunan, penyesuaian kuota stok, harga, dan ketersediaan etalase.
                    </p>
                </div>
                <div className="d-flex gap-2">
                    <button onClick={fetchProducts} className="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 rounded-3">
                        <RefreshCw size={14} /> Refresh
                    </button>
                    <button className="btn btn-sm btn-agro rounded-3 px-3 d-flex align-items-center gap-1 fw-bold shadow-sm" onClick={() => handleOpenModal()}>
                        <Plus size={16} /> Tambah Produk Baru
                    </button>
                </div>
            </div>

            {/* Filter bar */}
            <div className="card border-0 shadow-sm rounded-4 p-3 mb-4" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                <div className="row g-2 align-items-center">
                    <div className="col-md-5">
                        <div className="input-group input-group-sm">
                            <span className="input-group-text bg-light border-end-0"><Search size={15} /></span>
                            <input
                                type="text"
                                className="form-control bg-light border-start-0"
                                placeholder="Cari nama produk perkebunan..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                    </div>
                    <div className="col-md-4">
                        <select
                            className="form-select form-select-sm"
                            value={selectedCategory}
                            onChange={(e) => setSelectedCategory(e.target.value)}
                        >
                            <option value="">-- Semua Kategori --</option>
                            {categories.map((c) => (
                                <option key={c.id} value={c.id}>{c.nama_kategori}</option>
                            ))}
                        </select>
                    </div>
                    <div className="col-md-3 text-md-end">
                        <span className="badge bg-agro bg-opacity-10 text-agro px-3 py-2 rounded-pill small">
                            Total: {products.length} Produk
                        </span>
                    </div>
                </div>
            </div>

            {/* Products Table */}
            <div className="card border-0 shadow-sm rounded-4 overflow-hidden" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                {loading ? (
                    <div className="text-center py-5">
                        <div className="spinner-border text-agro" role="status">
                            <span className="visually-hidden">Loading...</span>
                        </div>
                    </div>
                ) : products.length === 0 ? (
                    <div className="text-center py-5">
                        <Package size={42} className="text-muted opacity-50 mb-2" />
                        <h6 className="fw-bold text-muted">Belum ada produk yang terdaftar di unit ini.</h6>
                        <p className="text-muted small mb-3">Klik tombol "Tambah Produk Baru" untuk memasukkan produk olahan perkebunan pertama Anda.</p>
                        <button className="btn btn-agro btn-sm rounded-pill px-4" onClick={() => handleOpenModal()}>
                            + Tambah Produk Sekarang
                        </button>
                    </div>
                ) : (
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0 small">
                            <thead>
                                <tr style={{ background: 'var(--bg-card-hover)', color: 'var(--text-heading)', borderBottom: '2px solid var(--border-color)' }}>
                                    <th>Produk & Satuan</th>
                                    {isSuperadmin && <th>Unit Toko TEFA</th>}
                                    <th>Kategori</th>
                                    <th>Harga Jual</th>
                                    <th style={{ width: '180px' }}>Stok Tersedia</th>
                                    <th>Status</th>
                                    <th className="text-end" style={{ width: '120px' }}>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {products.map((prod) => {
                                    const currentStock = prod.stok ?? prod.total_stok ?? 0;
                                    const isLow = currentStock <= 5;
                                    const isActive = prod.status === 'aktif' || prod.is_active;

                                    return (
                                        <tr key={prod.id} style={{ borderColor: 'var(--border-color)' }}>
                                            <td>
                                                <div className="d-flex align-items-center gap-3">
                                                    <img
                                                        src={getImageUrl(prod.gambar_utama || prod.images?.[0]?.gambar)}
                                                        alt={prod.nama_produk}
                                                        className="rounded-3 object-fit-cover border"
                                                        style={{ width: '46px', height: '46px' }}
                                                        onError={(e) => {
                                                            e.target.onerror = null;
                                                            e.target.src = 'https://placehold.co/100x100?text=Produk';
                                                        }}
                                                    />
                                                    <div>
                                                        <div className="fw-bold" style={{ color: 'var(--text-heading)' }}>{prod.nama_produk}</div>
                                                        <small className="text-muted">{prod.berat_gram || 500}g / {prod.satuan || 'Pcs'}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            {isSuperadmin && (
                                                <td>
                                                    <span className="badge bg-light text-dark border">
                                                        {prod.unit?.nama_unit || `Unit #${prod.unit_id}`}
                                                    </span>
                                                </td>
                                            )}
                                            <td>
                                                <span className="badge bg-secondary bg-opacity-10 text-secondary">
                                                    {prod.category?.nama_kategori || 'Perkebunan'}
                                                </span>
                                            </td>
                                            <td className="fw-bold text-success fs-6">{formatRupiah(prod.harga)}</td>
                                            <td>
                                                <div className="d-flex align-items-center gap-2">
                                                    <span className={`badge ${isLow ? 'bg-danger' : 'bg-success'} px-2 py-1`}>
                                                        {currentStock} {prod.satuan || 'Pcs'}
                                                    </span>
                                                    <button
                                                        onClick={() => handleQuickStock(prod)}
                                                        className="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0"
                                                        style={{ fontSize: '11px' }}
                                                        title="Ubah stok cepat"
                                                    >
                                                        ± Stok
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <span className={`badge ${isActive ? 'bg-success' : 'bg-secondary'}`}>
                                                    {isActive ? 'Aktif di Etalase' : 'Nonaktif'}
                                                </span>
                                            </td>
                                            <td className="text-end">
                                                <div className="d-flex justify-content-end gap-1">
                                                    <button
                                                        className="btn btn-sm btn-outline-primary rounded-circle p-2"
                                                        title="Edit Produk Lengkap"
                                                        onClick={() => handleOpenModal(prod)}
                                                    >
                                                        <Edit2 size={14} />
                                                    </button>
                                                    <button
                                                        className="btn btn-sm btn-outline-danger rounded-circle p-2"
                                                        title="Hapus Produk"
                                                        onClick={() => handleDelete(prod.id, prod.nama_produk)}
                                                    >
                                                        <Trash2 size={14} />
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

            {/* Modal Create/Edit */}
            {showModal && (
                <div className="modal show d-block" tabIndex="-1" style={{ backgroundColor: 'rgba(0,0,0,0.5)' }}>
                    <div className="modal-dialog modal-lg modal-dialog-centered">
                        <div className="modal-content rounded-4 border-0 shadow">
                            <div className="modal-header border-bottom">
                                <h5 className="modal-title fw-bold">
                                    {editingProduct ? `Edit Produk: ${editingProduct.nama_produk}` : 'Tambah Produk Baru'}
                                </h5>
                                <button type="button" className="btn-close" onClick={() => setShowModal(false)}></button>
                            </div>
                            <form onSubmit={handleSave}>
                                <div className="modal-body p-4">
                                    <div className="row g-3">
                                        {isSuperadmin && (
                                            <div className="col-md-6">
                                                <label className="form-label small fw-semibold">Unit Usaha TEFA</label>
                                                <select
                                                    className="form-select"
                                                    required
                                                    value={formData.unit_id}
                                                    onChange={(e) => setFormData({ ...formData, unit_id: e.target.value })}
                                                >
                                                    <option value="">-- Pilih Unit TEFA --</option>
                                                    {units.map((u) => (
                                                        <option key={u.id} value={u.id}>{u.nama_unit}</option>
                                                    ))}
                                                </select>
                                            </div>
                                        )}

                                        <div className={isSuperadmin ? 'col-md-6' : 'col-12'}>
                                            <label className="form-label small fw-semibold">Nama Produk</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                required
                                                placeholder="Contoh: Kopi Robusta Premium 250gr..."
                                                value={formData.nama_produk}
                                                onChange={(e) => setFormData({ ...formData, nama_produk: e.target.value })}
                                            />
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Kategori Produk</label>
                                            <select
                                                className="form-select"
                                                required
                                                value={formData.category_id}
                                                onChange={(e) => setFormData({ ...formData, category_id: e.target.value })}
                                            >
                                                <option value="">-- Pilih Kategori --</option>
                                                {categories.map((c) => (
                                                    <option key={c.id} value={c.id}>{c.nama_kategori}</option>
                                                ))}
                                            </select>
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Harga Jual Satuan (Rp)</label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                required
                                                min="0"
                                                step="500"
                                                placeholder="Contoh: 35000"
                                                value={formData.harga}
                                                onChange={(e) => setFormData({ ...formData, harga: e.target.value })}
                                            />
                                        </div>

                                        <div className="col-md-4">
                                            <label className="form-label small fw-semibold">Stok Saat Ini</label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                required
                                                min="0"
                                                value={formData.stok}
                                                onChange={(e) => setFormData({ ...formData, stok: e.target.value })}
                                            />
                                        </div>

                                        <div className="col-md-4">
                                            <label className="form-label small fw-semibold">Satuan Kemasan</label>
                                            <input
                                                type="text"
                                                className="form-control"
                                                required
                                                placeholder="Pcs, Botol, Pouch, Pack"
                                                value={formData.satuan}
                                                onChange={(e) => setFormData({ ...formData, satuan: e.target.value })}
                                            />
                                        </div>

                                        <div className="col-md-4">
                                            <label className="form-label small fw-semibold">Berat Bersih (Gram)</label>
                                            <input
                                                type="number"
                                                className="form-control"
                                                required
                                                min="1"
                                                placeholder="500"
                                                value={formData.berat_gram}
                                                onChange={(e) => setFormData({ ...formData, berat_gram: e.target.value })}
                                            />
                                        </div>

                                        <div className="col-12">
                                            <label className="form-label small fw-semibold">Deskripsi & Keunggulan Produk</label>
                                            <textarea
                                                className="form-control"
                                                rows="3"
                                                placeholder="Tuliskan spesifikasi, proses pengolahan, rasa, dan sertifikasi produk..."
                                                value={formData.deskripsi}
                                                onChange={(e) => setFormData({ ...formData, deskripsi: e.target.value })}
                                            ></textarea>
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold">Foto Produk</label>
                                            <input
                                                type="file"
                                                className="form-control form-control-sm"
                                                accept="image/*"
                                                onChange={handleImageChange}
                                            />
                                            <small className="text-muted" style={{ fontSize: '11px' }}>
                                                Format: JPG, PNG, WEBP. Maks 5MB.
                                            </small>
                                        </div>

                                        <div className="col-md-6">
                                            <label className="form-label small fw-semibold d-block">Status Produk di Etalase</label>
                                            <div className="form-check form-switch mt-2">
                                                <input
                                                    className="form-check-input"
                                                    type="checkbox"
                                                    id="statusSwitch"
                                                    checked={formData.is_active}
                                                    onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                                                />
                                                <label className="form-check-label small fw-bold d-inline-flex align-items-center gap-1" htmlFor="statusSwitch">
                                                    {formData.is_active ? (
                                                        <><CheckCircle2 size={14} className="text-success" /> <span>Aktif (Ditampilkan ke Pembeli)</span></>
                                                    ) : (
                                                        <><XCircle size={14} className="text-danger" /> <span>Nonaktif (Disembunyikan)</span></>
                                                    )}
                                                </label>
                                            </div>
                                        </div>

                                        {imagePreview && (
                                            <div className="col-12">
                                                <label className="form-label small text-muted d-block">Preview Foto:</label>
                                                <img
                                                    src={imagePreview}
                                                    alt="Preview"
                                                    className="rounded-3 border object-fit-cover"
                                                    style={{ width: '90px', height: '90px' }}
                                                />
                                            </div>
                                        )}
                                    </div>
                                </div>
                                <div className="modal-footer border-top">
                                    <button type="button" className="btn btn-light rounded-pill px-4" onClick={() => setShowModal(false)}>Batal</button>
                                    <button type="submit" disabled={saving} className="btn btn-agro rounded-pill px-4 fw-bold">
                                        {saving ? 'Menyimpan...' : 'Simpan Produk'}
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

export default AdminProducts;
