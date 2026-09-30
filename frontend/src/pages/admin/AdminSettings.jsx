import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { Sliders, Save } from 'lucide-react';

const AdminSettings = () => {
    const [settings, setSettings] = useState({
        nama_toko: 'Polinela Agro Digital',
        tagline: 'Marketplace Hasil Pertanian & Perkebunan Politeknik Negeri Lampung',
        alamat_kampus: 'Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung 35144',
        email_kontak: 'havertz0921@gmail.com',
        no_telepon: '(0721) 703995',
        whatsapp_cs: '0881080592737',
        tarif_ongkir_default: '10000',
        bank_nama: 'Bank Mandiri',
        bank_rekening: '114-00-1234567-8',
        bank_atas_nama: 'BPU POLITEKNIK NEGERI LAMPUNG',
    });
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [savedMsg, setSavedMsg] = useState('');

    useEffect(() => {
        const fetchSettings = async () => {
            try {
                const res = await client.get('/admin/settings');
                if (res.data.status === 'success' && res.data.data) {
                    setSettings((prev) => ({ ...prev, ...res.data.data }));
                }
            } catch (err) {
                console.error('Failed to load settings:', err);
            } finally {
                setLoading(false);
            }
        };
        fetchSettings();
    }, []);

    const handleChange = (e) => {
        setSettings({ ...settings, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setSavedMsg('');
        try {
            await client.post('/admin/settings', settings);
            setSavedMsg('Pengaturan berhasil disimpan!');
            setTimeout(() => setSavedMsg(''), 3000);
        } catch (err) {
            alert('Gagal menyimpan pengaturan: ' + (err.response?.data?.message || err.message));
        } finally {
            setSaving(false);
        }
    };

    return (
        <div className="animate-fade-in" style={{ maxWidth: '900px' }}>
            {/* Header */}
            <div className="mb-4">
                <h3 className="fw-bold text-heading mb-1 d-flex align-items-center gap-2">
                    <Sliders className="text-agro" /> Pengaturan Sistem & Toko
                </h3>
                <p className="text-muted small mb-0">Konfigurasi informasi toko, rekening pembayaran, dan kontak bantuan</p>
            </div>

            {savedMsg && (
                <div className="alert alert-success d-flex align-items-center mb-4 rounded-3" role="alert">
                    {savedMsg}
                </div>
            )}

            {loading ? (
                <div className="text-center py-5">
                    <div className="spinner-border text-success" role="status"></div>
                </div>
            ) : (
                <form onSubmit={handleSubmit}>
                    {/* General Settings */}
                    <div className="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h5 className="fw-bold text-heading mb-3 border-bottom pb-2">Informasi Umum</h5>
                        <div className="row g-3">
                            <div className="col-md-6">
                                <label className="form-label small fw-semibold">Nama Aplikasi / Toko</label>
                                <input
                                    type="text"
                                    name="nama_toko"
                                    className="form-control"
                                    value={settings.nama_toko || ''}
                                    onChange={handleChange}
                                />
                            </div>
                            <div className="col-md-6">
                                <label className="form-label small fw-semibold">Tarif Ongkir Standar (Rp)</label>
                                <input
                                    type="number"
                                    name="tarif_ongkir_default"
                                    className="form-control"
                                    value={settings.tarif_ongkir_default || '10000'}
                                    onChange={handleChange}
                                />
                            </div>
                            <div className="col-12">
                                <label className="form-label small fw-semibold">Tagline Toko</label>
                                <input
                                    type="text"
                                    name="tagline"
                                    className="form-control"
                                    value={settings.tagline || ''}
                                    onChange={handleChange}
                                />
                            </div>
                            <div className="col-12">
                                <label className="form-label small fw-semibold">Alamat Kampus / Kantor</label>
                                <textarea
                                    name="alamat_kampus"
                                    className="form-control"
                                    rows="2"
                                    value={settings.alamat_kampus || ''}
                                    onChange={handleChange}
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    {/* Payment Settings */}
                    <div className="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h5 className="fw-bold text-heading mb-3 border-bottom pb-2">Rekening Pembayaran Utama</h5>
                        <div className="row g-3">
                            <div className="col-md-4">
                                <label className="form-label small fw-semibold">Nama Bank</label>
                                <input
                                    type="text"
                                    name="bank_nama"
                                    className="form-control"
                                    placeholder="Bank Mandiri"
                                    value={settings.bank_nama || ''}
                                    onChange={handleChange}
                                />
                            </div>
                            <div className="col-md-4">
                                <label className="form-label small fw-semibold">Nomor Rekening</label>
                                <input
                                    type="text"
                                    name="bank_rekening"
                                    className="form-control"
                                    placeholder="114-00-1234567-8"
                                    value={settings.bank_rekening || ''}
                                    onChange={handleChange}
                                />
                            </div>
                            <div className="col-md-4">
                                <label className="form-label small fw-semibold">Atas Nama Rekening</label>
                                <input
                                    type="text"
                                    name="bank_atas_nama"
                                    className="form-control"
                                    placeholder="BPU POLINELA"
                                    value={settings.bank_atas_nama || ''}
                                    onChange={handleChange}
                                />
                            </div>
                        </div>
                    </div>

                    {/* Contact Settings */}
                    <div className="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h5 className="fw-bold text-heading mb-3 border-bottom pb-2">Kontak Bantuan & CS</h5>
                        <div className="row g-3">
                            <div className="col-md-4">
                                <label className="form-label small fw-semibold">Email Resmi</label>
                                <input
                                    type="email"
                                    name="email_kontak"
                                    className="form-control"
                                    value={settings.email_kontak || ''}
                                    onChange={handleChange}
                                />
                            </div>
                            <div className="col-md-4">
                                <label className="form-label small fw-semibold">No. Telepon Kantor</label>
                                <input
                                    type="text"
                                    name="no_telepon"
                                    className="form-control"
                                    value={settings.no_telepon || ''}
                                    onChange={handleChange}
                                />
                            </div>
                            <div className="col-md-4">
                                <label className="form-label small fw-semibold">WhatsApp Customer Service</label>
                                <input
                                    type="text"
                                    name="whatsapp_cs"
                                    className="form-control"
                                    placeholder="081234567890"
                                    value={settings.whatsapp_cs || ''}
                                    onChange={handleChange}
                                />
                            </div>
                        </div>
                    </div>

                    <button type="submit" disabled={saving} className="btn btn-agro btn-lg rounded-pill px-5 shadow-sm d-flex align-items-center gap-2">
                        <Save size={18} /> {saving ? 'Menyimpan Pengaturan...' : 'Simpan Semua Pengaturan'}
                    </button>
                </form>
            )}
        </div>
    );
};

export default AdminSettings;
