import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Lock, Mail, User, Phone, MapPin, ArrowRight } from 'lucide-react';

const Register = () => {
    const { register } = useAuth();
    const navigate = useNavigate();

    const [formData, setFormData] = useState({
        username: '',
        nama_lengkap: '',
        email: '',
        password: '',
        password_confirmation: '',
        no_hp: '',
        alamat: '',
    });
    const [submitting, setSubmitting] = useState(false);

    const handleChange = (e) => {
        setFormData((prev) => ({
            ...prev,
            [e.target.name]: e.target.value,
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        const res = await register(formData);
        setSubmitting(false);

        if (res.success) {
            navigate('/');
        }
    };

    return (
        <div className="min-vh-100 d-flex flex-column justify-content-between position-relative login-page-bg py-4 px-3">
            {/* Top Bar with Logo & Theme Toggle */}
            <div className="container d-flex justify-content-between align-items-center mb-3">
                <Link to="/" className="d-flex align-items-center gap-2 text-decoration-none">
                    <img src="/logo-polinela.png" alt="Logo Politeknik Negeri Lampung" style={{ width: '42px', height: '42px', objectFit: 'contain' }} />
                    <div>
                        <span className="fw-bold fs-6 d-block lh-1 text-heading">POLITEKNIK NEGERI LAMPUNG</span>
                        <small className="text-muted" style={{ fontSize: '11px', letterSpacing: '0.8px' }}>POLINELA AGRO DIGITAL</small>
                    </div>
                </Link>
            </div>

            <div className="container my-auto" style={{ maxWidth: '560px' }}>
                <div className="card border-0 shadow-lg rounded-4 p-4 p-sm-5 glass-card login-card animate-fade-in">
                    {/* Header */}
                    <div className="text-center mb-4">
                        <div className="position-relative d-inline-block mb-3">
                            <div className="p-2 rounded-4 bg-white shadow-sm d-inline-flex align-items-center justify-content-center" style={{ width: '80px', height: '80px' }}>
                                <img 
                                    src="/logo-polinela.png" 
                                    alt="Polinela Agro" 
                                    className="img-fluid" 
                                    style={{ width: '64px', height: '64px', objectFit: 'contain' }} 
                                />
                            </div>
                        </div>
                        <h4 className="fw-bold text-heading mb-1">Daftar Akun Baru</h4>
                        <p className="text-muted small mb-0">Bergabunglah dan nikmati kemudahan berbelanja hasil perkebunan Politeknik Negeri Lampung</p>
                    </div>

                    {/* Form */}
                    <form onSubmit={handleSubmit}>
                        <div className="row g-3 mb-3">
                            <div className="col-md-6">
                                <label className="form-label small fw-semibold">Nama Lengkap</label>
                                <div className="input-group">
                                    <span className="input-group-text bg-light border-end-0">
                                        <User size={16} className="text-muted" />
                                    </span>
                                    <input
                                        type="text"
                                        name="nama_lengkap"
                                        className="form-control bg-light border-start-0 ps-0"
                                        placeholder="Nama Lengkap"
                                        required
                                        value={formData.nama_lengkap}
                                        onChange={handleChange}
                                    />
                                </div>
                            </div>
                            <div className="col-md-6">
                                <label className="form-label small fw-semibold">Username</label>
                                <input
                                    type="text"
                                    name="username"
                                    className="form-control bg-light"
                                    placeholder="Username"
                                    required
                                    value={formData.username}
                                    onChange={handleChange}
                                />
                            </div>
                        </div>

                        <div className="row g-3 mb-3">
                            <div className="col-md-6">
                                <label className="form-label small fw-semibold">Email</label>
                                <div className="input-group">
                                    <span className="input-group-text bg-light border-end-0">
                                        <Mail size={16} className="text-muted" />
                                    </span>
                                    <input
                                        type="email"
                                        name="email"
                                        className="form-control bg-light border-start-0 ps-0"
                                        placeholder="email@domain.com"
                                        required
                                        value={formData.email}
                                        onChange={handleChange}
                                    />
                                </div>
                            </div>
                            <div className="col-md-6">
                                <label className="form-label small fw-semibold">No. WhatsApp / HP</label>
                                <div className="input-group">
                                    <span className="input-group-text bg-light border-end-0">
                                        <Phone size={16} className="text-muted" />
                                    </span>
                                    <input
                                        type="text"
                                        name="no_hp"
                                        className="form-control bg-light border-start-0 ps-0"
                                        placeholder="08123456789"
                                        required
                                        value={formData.no_hp}
                                        onChange={handleChange}
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="mb-3">
                            <label className="form-label small fw-semibold">Alamat Pengiriman</label>
                            <div className="input-group">
                                <span className="input-group-text bg-light border-end-0">
                                    <MapPin size={16} className="text-muted" />
                                </span>
                                <input
                                    type="text"
                                    name="alamat"
                                    className="form-control bg-light border-start-0 ps-0"
                                    placeholder="Alamat lengkap tempat tinggal"
                                    value={formData.alamat}
                                    onChange={handleChange}
                                />
                            </div>
                        </div>

                        <div className="row g-3 mb-4">
                            <div className="col-md-6">
                                <label className="form-label small fw-semibold">Password</label>
                                <div className="input-group">
                                    <span className="input-group-text bg-light border-end-0">
                                        <Lock size={16} className="text-muted" />
                                    </span>
                                    <input
                                        type="password"
                                        name="password"
                                        className="form-control bg-light border-start-0 ps-0"
                                        placeholder="••••••••"
                                        required
                                        minLength={6}
                                        value={formData.password}
                                        onChange={handleChange}
                                    />
                                </div>
                            </div>
                            <div className="col-md-6">
                                <label className="form-label small fw-semibold">Ulangi Password</label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    className="form-control bg-light"
                                    placeholder="••••••••"
                                    required
                                    value={formData.password_confirmation}
                                    onChange={handleChange}
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            disabled={submitting}
                            className="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 mb-3"
                        >
                            {submitting ? (
                                <span className="spinner-border spinner-border-sm" role="status"></span>
                            ) : (
                                <>
                                    <span>Daftar Sekarang</span>
                                    <ArrowRight size={18} />
                                </>
                            )}
                        </button>
                    </form>

                    {/* Footer note */}
                    <div className="text-center text-muted small border-top pt-3 mt-2">
                        Sudah memiliki akun?{' '}
                        <Link to="/login" className="text-agro fw-bold text-decoration-none">
                            Masuk di sini
                        </Link>
                    </div>
                </div>
            </div>

            {/* Bottom Footer Credits */}
            <div className="text-center text-muted small mt-3">
                &copy; {new Date().getFullYear()} Politeknik Negeri Lampung • Teaching Factory & Kebun Riset
            </div>
        </div>
    );
};

export default Register;
