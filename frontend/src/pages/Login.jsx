import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import { Lock, Mail, Eye, EyeOff, Sparkles, Sun, Moon, ShieldCheck, BarChart3, Coffee, Package, Leaf, FlaskConical, ShoppingCart } from 'lucide-react';

const Login = () => {
    const { login } = useAuth();
    const { isDark, toggleTheme } = useTheme();
    const navigate = useNavigate();
    const location = useLocation();

    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [submitting, setSubmitting] = useState(false);

    const from = location.state?.from?.pathname || '/';

    const demoAccounts = [
        { role: 'Superadmin', icon: ShieldCheck, email: 'admin@polinela.ac.id', pass: 'admin123', color: 'btn-outline-danger' },
        { role: 'Pimpinan', icon: BarChart3, email: 'pimpinan@polinela.ac.id', pass: 'pimpinan123', color: 'btn-outline-primary' },
        { role: 'Admin Kopi', icon: Coffee, email: 'adminkopi@polinela.ac.id', pass: 'admin123', color: 'btn-outline-success' },
        { role: 'Admin Kakao', icon: Package, email: 'adminkakao@polinela.ac.id', pass: 'admin123', color: 'btn-outline-warning text-dark' },
        { role: 'Admin Lada', icon: Leaf, email: 'adminlada@polinela.ac.id', pass: 'admin123', color: 'btn-outline-info' },
        { role: 'Admin Atsiri & Olahan', icon: FlaskConical, email: 'adminatsiri@polinela.ac.id', pass: 'admin123', color: 'btn-outline-dark' },
        { role: 'Konsumen', icon: ShoppingCart, email: 'budi@gmail.com', pass: 'konsumen123', color: 'btn-outline-secondary' },
    ];

    const fillDemo = (accEmail, accPass) => {
        setEmail(accEmail);
        setPassword(accPass);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        const result = await login(email, password);
        setSubmitting(false);

        if (result.success) {
            if (result.user.role === 'admin_unit' || result.user.role === 'superadmin' || result.user.role === 'pimpinan') {
                navigate('/admin');
            } else {
                navigate(from);
            }
        }
    };

    return (
        <div className="min-vh-100 d-flex flex-column justify-content-between position-relative login-page-bg py-3 px-3">
            {/* Top Navigation Bar */}
            <header className="container d-flex justify-content-between align-items-center py-2">
                <div className="d-flex align-items-center gap-4">
                    <Link to="/" className="d-flex align-items-center gap-2 text-decoration-none">
                        <img 
                            src="/logo-polinela.png" 
                            alt="Logo Polinela" 
                            style={{ width: '38px', height: '38px', objectFit: 'contain' }}
                            className="drop-shadow-logo"
                        />
                        <span className="fw-bold fs-6 text-heading" style={{ letterSpacing: '0.3px' }}>
                            Polinela Agro
                        </span>
                    </Link>

                    <Link to="/katalog" className="text-decoration-none small text-muted fw-semibold d-none d-sm-inline hover-primary">
                        Katalog Produk Perkebunan
                    </Link>
                </div>

                <div className="d-flex align-items-center gap-2">
                    <button
                        className="theme-toggle"
                        onClick={toggleTheme}
                        title={isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'}
                    >
                        {isDark ? <Sun size={18} /> : <Moon size={18} />}
                    </button>
                </div>
            </header>

            {/* Centered Login Card */}
            <main className="container my-auto py-3 d-flex justify-content-center">
                <div className="card polinela-auth-card p-4 p-sm-5 animate-fade-in w-100" style={{ maxWidth: '440px' }}>
                    {/* Header inside Card: Logo + APLIKASI E-COMMERCE AGRO + POLINELA (Hijau Perkebunan) */}
                    <div className="d-flex align-items-center gap-3 mb-2">
                        <img 
                            src="/logo-polinela.png" 
                            alt="Polinela" 
                            style={{ width: '48px', height: '48px', objectFit: 'contain' }}
                            className="drop-shadow-logo" 
                        />
                        <div>
                            <h6 className="fw-bold mb-0 text-heading" style={{ fontSize: '13.5px', letterSpacing: '0.5px' }}>
                                APLIKASI E-COMMERCE AGRO
                            </h6>
                            <div className="fw-bold text-agro" style={{ fontSize: '13px', letterSpacing: '0.5px' }}>
                                POLITEKNIK NEGERI LAMPUNG
                            </div>
                        </div>
                    </div>

                    <p className="text-muted small mb-4 mt-1" style={{ fontSize: '13px' }}>
                        Masuk menggunakan akun yang terdaftar
                    </p>

                    {/* Form */}
                    <form onSubmit={handleSubmit}>
                        {/* Email or Username */}
                        <div className="mb-3">
                            <div className="input-group polinela-input-group">
                                <span className="input-group-text border-end-0">
                                    <Mail size={16} className="text-muted" />
                                </span>
                                <input
                                    type="text"
                                    className="form-control border-start-0 ps-0"
                                    placeholder="Masukan email atau username"
                                    required
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                />
                            </div>
                        </div>

                        {/* Password */}
                        <div className="mb-4">
                            <div className="d-flex justify-content-between align-items-center mb-1">
                                <label className="small text-muted fw-bold" style={{ fontSize: '11px', letterSpacing: '0.5px' }}>
                                    PASSWORD
                                </label>
                                <a 
                                    href="#lupa" 
                                    onClick={(e) => { e.preventDefault(); alert('Silakan hubungi Administrator Unit/Superadmin Polinela untuk bantuan reset password.'); }}
                                    className="small fw-bold text-decoration-none text-agro" 
                                    style={{ fontSize: '12px' }}
                                >
                                    Lupa Password?
                                </a>
                            </div>
                            <div className="input-group polinela-input-group">
                                <span className="input-group-text border-end-0">
                                    <Lock size={16} className="text-muted" />
                                </span>
                                <input
                                    type={showPassword ? 'text' : 'password'}
                                    className="form-control border-start-0 border-end-0 ps-0"
                                    placeholder="Masukan password"
                                    required
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                />
                                <button 
                                    type="button"
                                    className="input-group-text border-start-0 text-muted"
                                    onClick={() => setShowPassword(!showPassword)}
                                    title={showPassword ? 'Sembunyikan password' : 'Lihat password'}
                                >
                                    {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                                </button>
                            </div>
                        </div>

                        {/* Submit Button (Agro Emerald Green) */}
                        <button
                            type="submit"
                            disabled={submitting}
                            className="btn btn-polinela-primary w-100 fw-semibold mb-3 shadow-sm"
                        >
                            {submitting ? (
                                <span className="spinner-border spinner-border-sm" role="status"></span>
                            ) : (
                                <span>Masuk</span>
                            )}
                        </button>
                    </form>

                    {/* Help text & Register */}
                    <div className="text-center small text-muted mb-2" style={{ fontSize: '12.5px' }}>
                        Kendala login?{' '}
                        <a 
                            href="https://wa.me/62881080592737?text=Halo%20Admin%20Polinela%20Agro,%20saya%20mengalami%20kendala%20saat%20login%20ke%20akun%20saya.%20Mohon%20bantuannya." 
                            target="_blank"
                            rel="noopener noreferrer"
                            className="fw-bold text-decoration-none text-agro"
                            title="Hubungi Admin CS via WhatsApp"
                        >
                            Hubungi admin (WhatsApp)
                        </a>
                    </div>

                    <div className="text-center small text-muted pb-3 mb-3 border-bottom" style={{ fontSize: '12.5px' }}>
                        Belum memiliki akun?{' '}
                        <Link to="/register" className="fw-bold text-decoration-none text-agro">
                            Daftar di sini
                        </Link>
                    </div>

                    {/* ONE-CLICK DEMO ACCOUNTS (Di bagian bawah) */}
                    <div className="p-3 rounded-3 bg-elevated-subtle border">
                        <div className="d-flex align-items-center justify-content-between mb-2">
                            <span className="small fw-bold text-heading d-flex align-items-center gap-1" style={{ fontSize: '11px' }}>
                                <Sparkles size={13} className="text-warning" /> Klik Cepat Akun Demo:
                            </span>
                        </div>
                        <div className="d-flex flex-wrap gap-1">
                            {demoAccounts.map((acc, idx) => {
                                const IconComp = acc.icon;
                                return (
                                    <button
                                        key={idx}
                                        type="button"
                                        onClick={() => fillDemo(acc.email, acc.pass)}
                                        className={`btn btn-sm ${acc.color} rounded-pill py-1 px-2 d-inline-flex align-items-center`}
                                        style={{ fontSize: '11px', fontWeight: 600 }}
                                    >
                                        <IconComp size={12} className="me-1" />
                                        {acc.role}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </main>

            {/* Bottom Footer Credits */}
            <footer className="container text-center text-muted small py-2">
                &copy; {new Date().getFullYear()} <strong>Politeknik Negeri Lampung</strong> • Polinela Agro Digital
            </footer>
        </div>
    );
};

export default Login;
