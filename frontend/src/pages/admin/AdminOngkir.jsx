import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import Swal from 'sweetalert2';
import { Truck, Plus, Trash2, Save, RefreshCw, MapPin, Clock, DollarSign, Info } from 'lucide-react';
import { formatRupiah } from '../../utils/format';

const AdminOngkir = () => {
    const [rates, setRates] = useState([]);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    const fetchRates = async () => {
        setLoading(true);
        try {
            const res = await client.get('/admin/ongkir');
            if (res.data.status === 'success') {
                setRates(res.data.data || []);
            }
        } catch (err) {
            console.error('Failed to fetch shipping rates:', err);
            // Default fallback
            setRates([
                { id: '1', wilayah: 'Dalam Kampus Polinela (Ambil di TEFA / Free Delivery Gedung)', tarif: 0, estimasi: '1 Jam / Langsung' },
                { id: '2', wilayah: 'Kecamatan Rajabasa & Sekitar Kampus', tarif: 5000, estimasi: '1 - 2 Jam' },
                { id: '3', wilayah: 'Kota Bandar Lampung (Kurir Kampus / Ojol)', tarif: 12000, estimasi: 'Hari yang sama (Same Day)' },
                { id: '4', wilayah: 'Luar Kota Lampung (JNE / J&T / Pos Express)', tarif: 25000, estimasi: '2 - 3 Hari Kerja' },
            ]);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchRates();
    }, []);

    const handleRateChange = (index, field, value) => {
        const updated = [...rates];
        updated[index][field] = field === 'tarif' ? parseFloat(value) || 0 : value;
        setRates(updated);
    };

    const handleAddRow = () => {
        setRates([
            ...rates,
            {
                id: Date.now().toString(),
                wilayah: '',
                tarif: 10000,
                estimasi: '1 - 2 Hari'
            }
        ]);
    };

    const handleRemoveRow = (index) => {
        if (rates.length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Minimal harus ada 1 opsi tarif pengiriman.',
            });
            return;
        }
        const updated = rates.filter((_, i) => i !== index);
        setRates(updated);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            const res = await client.post('/admin/ongkir', { rates });
            if (res.data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Tarif ongkos kirim berhasil disimpan dan diterapkan.',
                    timer: 1500,
                    showConfirmButton: false,
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.response?.data?.message || 'Gagal menyimpan tarif ongkir.',
            });
        } finally {
            setSaving(false);
        }
    };

    return (
        <div className="container-fluid p-0">
            {/* Header */}
            <div className="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h3 className="fw-bold mb-1 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                        <Truck size={26} className="text-agro" />
                        Tarif Pengiriman & Ongkir
                    </h3>
                    <p className="text-muted small mb-0">
                        Atur zona wilayah pengiriman, kurir internal kampus Polinela, dan estimasi waktu sampai.
                    </p>
                </div>
                <div className="d-flex gap-2">
                    <button
                        onClick={fetchRates}
                        className="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 rounded-3"
                    >
                        <RefreshCw size={14} /> Refresh
                    </button>
                    <button
                        onClick={handleAddRow}
                        className="btn btn-sm btn-outline-success d-flex align-items-center gap-1 rounded-3"
                    >
                        <Plus size={15} /> Tambah Wilayah
                    </button>
                    <button
                        onClick={handleSubmit}
                        disabled={saving}
                        className="btn btn-sm btn-agro d-flex align-items-center gap-1 rounded-3 px-3"
                    >
                        <Save size={15} /> {saving ? 'Menyimpan...' : 'Simpan Perubahan'}
                    </button>
                </div>
            </div>

            {loading ? (
                <div className="text-center py-5">
                    <div className="spinner-border text-agro" role="status">
                        <span className="visually-hidden">Loading...</span>
                    </div>
                </div>
            ) : (
                <form onSubmit={handleSubmit}>
                    <div className="card border-0 shadow-sm rounded-4 overflow-hidden" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                        <div className="table-responsive">
                            <table className="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style={{ background: 'var(--bg-card-hover)', color: 'var(--text-heading)', borderBottom: '2px solid var(--border-color)' }}>
                                        <th style={{ width: '60px' }} className="text-center">No</th>
                                        <th>Nama Zona / Wilayah Pengiriman</th>
                                        <th style={{ width: '220px' }}>Biaya / Tarif Ongkir</th>
                                        <th style={{ width: '240px' }}>Estimasi Pengiriman</th>
                                        <th style={{ width: '80px' }} className="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rates.map((rate, index) => (
                                        <tr key={index} style={{ borderColor: 'var(--border-color)' }}>
                                            <td className="text-center fw-bold text-muted small">{index + 1}</td>
                                            <td>
                                                <div className="input-group">
                                                    <span className="input-group-text bg-light border-0">
                                                        <MapPin size={15} className="text-muted" />
                                                    </span>
                                                    <input
                                                        type="text"
                                                        className="form-control rounded-end-3"
                                                        placeholder="Contoh: Dalam Kampus Polinela..."
                                                        value={rate.wilayah}
                                                        onChange={(e) => handleRateChange(index, 'wilayah', e.target.value)}
                                                        required
                                                    />
                                                </div>
                                            </td>
                                            <td>
                                                <div className="input-group">
                                                    <span className="input-group-text bg-light border-0 fw-bold small text-agro">Rp</span>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        step="1000"
                                                        className="form-control rounded-end-3 fw-bold text-success"
                                                        value={rate.tarif}
                                                        onChange={(e) => handleRateChange(index, 'tarif', e.target.value)}
                                                        required
                                                    />
                                                </div>
                                            </td>
                                            <td>
                                                <div className="input-group">
                                                    <span className="input-group-text bg-light border-0">
                                                        <Clock size={15} className="text-muted" />
                                                    </span>
                                                    <input
                                                        type="text"
                                                        className="form-control rounded-end-3"
                                                        placeholder="Contoh: 1 - 2 Jam"
                                                        value={rate.estimasi}
                                                        onChange={(e) => handleRateChange(index, 'estimasi', e.target.value)}
                                                        required
                                                    />
                                                </div>
                                            </td>
                                            <td className="text-center">
                                                <button
                                                    type="button"
                                                    className="btn btn-sm btn-outline-danger rounded-circle p-2"
                                                    onClick={() => handleRemoveRow(index)}
                                                    title="Hapus baris"
                                                >
                                                    <Trash2 size={15} />
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        
                        <div className="p-3 border-top d-flex justify-content-between align-items-center" style={{ background: 'var(--bg-card-hover)', borderColor: 'var(--border-color)' }}>
                            <small className="text-muted d-flex align-items-center gap-1.5">
                                <Info size={14} className="text-primary flex-shrink-0" /> <em>Tarif Rp 0 otomatis diberi label "Gratis Ongkir" di halaman keranjang & checkout pelanggan.</em>
                            </small>
                            <button
                                type="submit"
                                disabled={saving}
                                className="btn btn-agro btn-sm px-4 rounded-3 d-flex align-items-center gap-1 shadow-sm"
                            >
                                <Save size={15} /> {saving ? 'Menyimpan...' : 'Simpan Perubahan'}
                            </button>
                        </div>
                    </div>
                </form>
            )}
        </div>
    );
};

export default AdminOngkir;
