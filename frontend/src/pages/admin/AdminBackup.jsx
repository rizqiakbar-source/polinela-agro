import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import Swal from 'sweetalert2';
import { Database, Download, RefreshCw, HardDrive, ShieldCheck, FileText, CheckCircle2 } from 'lucide-react';

const AdminBackup = () => {
    const [backups, setBackups] = useState([]);
    const [loading, setLoading] = useState(true);
    const [creating, setCreating] = useState(false);

    const fetchBackups = async () => {
        setLoading(true);
        try {
            const res = await client.get('/admin/backup');
            if (res.data.status === 'success') {
                setBackups(res.data.data || []);
            }
        } catch (err) {
            console.error('Failed to fetch backups:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchBackups();
    }, []);

    const handleCreateBackup = async () => {
        setCreating(true);
        try {
            const res = await client.post('/admin/backup/create');
            if (res.data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Backup Berhasil!',
                    text: `File cadangan basis data (${res.data.data.filename}) berhasil dibuat.`,
                });
                
                // If SQL string is returned, trigger auto download
                if (res.data.data.sql) {
                    const blob = new Blob([res.data.data.sql], { type: 'text/sql;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', res.data.data.filename);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }

                fetchBackups();
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Backup',
                text: err.response?.data?.message || 'Terjadi kesalahan saat membackup basis data.',
            });
        } finally {
            setCreating(false);
        }
    };

    return (
        <div className="container-fluid p-0">
            {/* Header */}
            <div className="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h3 className="fw-bold mb-1 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                        <Database size={26} className="text-agro" />
                        Backup & Cadangan Basis Data
                    </h3>
                    <p className="text-muted small mb-0">
                        Unduh dan kelola salinan cadangan SQL seluruh tabel transaksi, pengguna, produk, dan pengaturan sistem.
                    </p>
                </div>
                <div className="d-flex gap-2">
                    <button
                        onClick={fetchBackups}
                        className="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 rounded-3"
                    >
                        <RefreshCw size={14} /> Refresh
                    </button>
                    <button
                        onClick={handleCreateBackup}
                        disabled={creating}
                        className="btn btn-sm btn-agro d-flex align-items-center gap-1 rounded-3 px-3 shadow-sm"
                    >
                        <Download size={15} /> {creating ? 'Memproses Dump SQL...' : 'Buat Backup Baru (.SQL)'}
                    </button>
                </div>
            </div>

            {/* Info Cards */}
            <div className="row g-3 mb-4">
                <div className="col-md-4">
                    <div className="card border-0 shadow-sm rounded-4 p-3" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                        <div className="d-flex align-items-center gap-3">
                            <div className="p-3 rounded-4 bg-success bg-opacity-10 text-success">
                                <ShieldCheck size={26} />
                            </div>
                            <div>
                                <small className="text-muted d-block">Status Sistem</small>
                                <span className="fw-bold text-success">Aman & Terenkripsi</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="card border-0 shadow-sm rounded-4 p-3" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                        <div className="d-flex align-items-center gap-3">
                            <div className="p-3 rounded-4 bg-primary bg-opacity-10 text-primary">
                                <HardDrive size={26} />
                            </div>
                            <div>
                                <small className="text-muted d-block">Total File Backup</small>
                                <span className="fw-bold" style={{ color: 'var(--text-heading)' }}>{backups.length} File Tersedia</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="card border-0 shadow-sm rounded-4 p-3" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                        <div className="d-flex align-items-center gap-3">
                            <div className="p-3 rounded-4 bg-warning bg-opacity-10 text-warning">
                                <CheckCircle2 size={26} />
                            </div>
                            <div>
                                <small className="text-muted d-block">Format Cadangan</small>
                                <span className="fw-bold" style={{ color: 'var(--text-heading)' }}>MySQL Dump (.SQL)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Backups List Table */}
            <div className="card border-0 shadow-sm rounded-4 overflow-hidden" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-color)' }}>
                <div className="p-3 border-bottom d-flex justify-content-between align-items-center" style={{ background: 'var(--bg-card-hover)', borderColor: 'var(--border-color)' }}>
                    <h6 className="fw-bold mb-0 d-flex align-items-center gap-2" style={{ color: 'var(--text-heading)' }}>
                        <FileText size={17} className="text-agro" />
                        Riwayat Berkas Cadangan SQL
                    </h6>
                    <span className="badge bg-agro bg-opacity-10 text-agro">{backups.length} Berkas</span>
                </div>

                {loading ? (
                    <div className="text-center py-5">
                        <div className="spinner-border text-agro" role="status">
                            <span className="visually-hidden">Loading...</span>
                        </div>
                    </div>
                ) : backups.length === 0 ? (
                    <div className="text-center py-5">
                        <Database size={40} className="text-muted mb-2 opacity-50" />
                        <h6 className="fw-bold text-muted">Belum ada berkas backup yang tersimpan di server.</h6>
                        <p className="text-muted small mb-3">Klik tombol "Buat Backup Baru (.SQL)" untuk membuat cadangan sekarang.</p>
                        <button
                            onClick={handleCreateBackup}
                            disabled={creating}
                            className="btn btn-agro btn-sm rounded-pill px-4"
                        >
                            {creating ? 'Memproses...' : 'Buat Cadangan Sekarang'}
                        </button>
                    </div>
                ) : (
                    <div className="table-responsive">
                        <table className="table table-hover align-middle mb-0">
                            <thead>
                                <tr style={{ background: 'var(--bg-card-hover)', color: 'var(--text-heading)', borderBottom: '2px solid var(--border-color)' }}>
                                    <th style={{ width: '60px' }} className="text-center">No</th>
                                    <th>Nama Berkas (.SQL)</th>
                                    <th>Ukuran File</th>
                                    <th>Waktu Pembuatan</th>
                                    <th style={{ width: '140px' }} className="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {backups.map((item, idx) => (
                                    <tr key={idx} style={{ borderColor: 'var(--border-color)' }}>
                                        <td className="text-center fw-bold text-muted small">{idx + 1}</td>
                                        <td>
                                            <div className="d-flex align-items-center gap-2">
                                                <Database size={16} className="text-agro" />
                                                <span className="font-monospace fw-semibold" style={{ color: 'var(--text-heading)' }}>
                                                    {item.filename}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span className="badge bg-light text-dark border font-monospace">
                                                {item.size}
                                            </span>
                                        </td>
                                        <td>
                                            <small className="text-muted">
                                                {item.date}
                                            </small>
                                        </td>
                                        <td className="text-center">
                                            <button
                                                onClick={handleCreateBackup}
                                                className="btn btn-sm btn-outline-success d-flex align-items-center gap-1 mx-auto rounded-3 px-3"
                                                title="Unduh file"
                                            >
                                                <Download size={14} /> Unduh
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
    );
};

export default AdminBackup;
