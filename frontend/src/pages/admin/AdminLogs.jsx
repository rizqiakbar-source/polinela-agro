import React, { useState, useEffect } from 'react';
import client from '../../api/client';
import { History, Search } from 'lucide-react';
import { formatDate } from '../../utils/format';

const AdminLogs = () => {
    const [logs, setLogs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');

    const fetchLogs = async () => {
        setLoading(true);
        try {
            const res = await client.get('/admin/logs');
            if (res.data.status === 'success') {
                const lData = res.data.data;
                setLogs(Array.isArray(lData) ? lData : (lData?.data || []));
            }
        } catch (err) {
            console.error('Failed to load logs:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchLogs();
    }, []);

    const parseUserAgent = (ua) => {
        if (!ua) return '';
        let browser = 'Browser';
        let os = 'Perangkat';

        if (ua.includes('Edg/')) browser = 'Edge';
        else if (ua.includes('Chrome/')) browser = 'Chrome';
        else if (ua.includes('Safari/') && !ua.includes('Chrome')) browser = 'Safari';
        else if (ua.includes('Firefox/')) browser = 'Firefox';
        else if (ua.includes('Opera') || ua.includes('OPR/')) browser = 'Opera';

        if (ua.includes('Windows NT 10.0')) os = 'Windows 10/11';
        else if (ua.includes('Windows')) os = 'Windows';
        else if (ua.includes('iPhone')) os = 'iPhone';
        else if (ua.includes('iPad')) os = 'iPad';
        else if (ua.includes('Android')) os = 'Android';
        else if (ua.includes('Macintosh') || ua.includes('Mac OS')) os = 'macOS';
        else if (ua.includes('Linux')) os = 'Linux';

        return `${browser} (${os})`;
    };

    const filteredLogs = logs.filter(l => 
        l.aksi?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        l.modul?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        l.deskripsi?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        l.ip_address?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        l.user?.nama?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="animate-fade-in">
            {/* Header */}
            <div className="mb-4">
                <h3 className="fw-bold text-heading mb-1 d-flex align-items-center gap-2">
                    <History className="text-agro" /> Audit Trail & Log Aktivitas
                </h3>
                <p className="text-muted small mb-0">Riwayat audit jejak aktivitas staf dan aksi penting di dalam sistem</p>
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
                        placeholder="Cari aktivitas berdasarkan aksi, modul, user, deskripsi, atau IP..."
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
                                <th style={{ width: '170px' }}>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aksi</th>
                                <th>Modul</th>
                                <th>Deskripsi</th>
                                <th>IP & Perangkat</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr>
                                    <td colSpan="6" className="text-center py-4">
                                        <div className="spinner-border text-success" role="status"></div>
                                    </td>
                                </tr>
                            ) : filteredLogs.length === 0 ? (
                                <tr>
                                    <td colSpan="6" className="text-center py-4 text-muted">
                                        Belum ada rekaman aktivitas.
                                    </td>
                                </tr>
                            ) : (
                                filteredLogs.map((l) => (
                                    <tr key={l.id}>
                                        <td className="small text-muted">{formatDate(l.created_at)}</td>
                                        <td>
                                            <div className="fw-semibold text-heading">{l.user?.nama || 'Sistem'}</div>
                                            <small className="text-muted">{l.user?.role || '-'}</small>
                                        </td>
                                        <td>
                                            <span className="badge bg-secondary rounded-pill">{l.aksi}</span>
                                        </td>
                                        <td>
                                            <span className="badge bg-agro-subtle rounded-pill">{l.modul}</span>
                                        </td>
                                        <td className="small text-body">{l.deskripsi || '-'}</td>
                                        <td>
                                            <div className="d-flex flex-column">
                                                <code className="small fw-semibold text-dark">{l.ip_address || '-'}</code>
                                                {l.user_agent && (
                                                    <span className="text-muted text-truncate" style={{ fontSize: '0.72rem', maxWidth: '160px' }} title={l.user_agent}>
                                                        {parseUserAgent(l.user_agent)}
                                                    </span>
                                                )}
                                            </div>
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

export default AdminLogs;
