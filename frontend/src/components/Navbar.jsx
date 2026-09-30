import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useCart } from '../context/CartContext';
import { useTheme } from '../context/ThemeContext';
import { ShoppingCart, User, LogOut, LayoutDashboard, Package, History, Search, Sun, Moon } from 'lucide-react';

const Navbar = () => {
    const { user, isStaffOrAdmin, logout } = useAuth();
    const { cartCount } = useCart();
    const { isDark, toggleTheme } = useTheme();
    const navigate = useNavigate();
    const location = useLocation();
    const [searchQuery, setSearchQuery] = useState('');

    const handleSearch = (e) => {
        e.preventDefault();
        if (searchQuery.trim()) {
            navigate(`/katalog?q=${encodeURIComponent(searchQuery.trim())}`);
        }
    };

    return (
        <header className="sticky-top">
            {/* Top info bar */}
            <div className="top-info-bar py-1 px-3 small d-none d-md-block">
                <div className="container d-flex justify-content-between align-items-center">
                    <div>
                        <span>🌱 Polinela Agro - Hasil Pertanian & Perkebunan Unggulan Politeknik Negeri Lampung</span>
                    </div>
                    <div className="d-flex gap-3 align-items-center">
                        {isStaffOrAdmin && (
                            <Link to="/admin" className="text-white text-decoration-none fw-semibold">
                                <i className="bi bi-shield-lock me-1"></i> Portal Admin
                            </Link>
                        )}
                        <span><i className="bi bi-geo-alt me-1"></i> Bandar Lampung</span>
                    </div>
                </div>
            </div>

            {/* Main Navbar */}
            <nav className="navbar navbar-expand-lg py-2">
                <div className="container">
                    {/* Brand Logo */}
                    <Link to="/" className="navbar-brand d-flex align-items-center gap-2 text-decoration-none">
                        <div className="rounded-3 p-2 d-flex align-items-center justify-content-center" style={{ width: '38px', height: '38px', background: 'var(--gradient-primary)' }}>
                            <span className="fw-bold fs-5">🌿</span>
                        </div>
                        <div>
                            <span className="fw-bold fs-5 d-block lh-1" style={{ color: 'var(--color-primary)' }}>POLINELA AGRO</span>
                            <small style={{ fontSize: '11px', letterSpacing: '1px', color: 'var(--text-muted)' }}>MULTI-STORE PERKEBUNAN</small>
                        </div>
                    </Link>

                    {/* Mobile menu button */}
                    <button
                        className="navbar-toggler"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#navbarMain"
                    >
                        <span className="navbar-toggler-icon"></span>
                    </button>

                    <div className="collapse navbar-collapse" id="navbarMain">
                        {/* Search Bar */}
                        <form onSubmit={handleSearch} className="d-flex mx-lg-4 my-2 my-lg-0 flex-grow-1" style={{ maxWidth: '480px' }}>
                            <div className="input-group">
                                <input
                                    type="text"
                                    className="form-control rounded-start-pill border-end-0 ps-3"
                                    placeholder="Cari kopi, kakao, bibit, sawit, pupuk..."
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                />
                                <button className="btn btn-outline-success rounded-end-pill px-3" type="submit">
                                    <Search size={18} />
                                </button>
                            </div>
                        </form>

                        {/* Navigation Links */}
                        <ul className="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                            <li className="nav-item">
                                <Link className={`nav-link nav-link-agro ${location.pathname === '/' ? 'active' : ''}`} to="/">
                                    Beranda
                                </Link>
                            </li>
                            <li className="nav-item">
                                <Link className={`nav-link nav-link-agro ${location.pathname === '/katalog' && !location.search.includes('view=units') ? 'active' : ''}`} to="/katalog">
                                    Semua Produk
                                </Link>
                            </li>
                            <li className="nav-item">
                                <Link className={`nav-link nav-link-agro ${location.search.includes('view=units') ? 'active' : ''}`} to="/katalog?view=units">
                                    Unit Toko
                                </Link>
                            </li>
                        </ul>

                        {/* Right side items */}
                        <div className="d-flex align-items-center gap-2">
                            {/* Theme Toggle */}
                            <button
                                className="theme-toggle"
                                onClick={toggleTheme}
                                title={isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'}
                            >
                                {isDark ? <Sun size={18} /> : <Moon size={18} />}
                            </button>

                            {/* Shopping Cart Button */}
                            <Link to="/keranjang" className="btn btn-light rounded-pill position-relative border px-3 d-flex align-items-center gap-2">
                                <ShoppingCart size={19} style={{ color: 'var(--color-primary)' }} />
                                <span className="d-none d-sm-inline small fw-semibold">Keranjang</span>
                                {cartCount > 0 && (
                                    <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {cartCount}
                                    </span>
                                )}
                            </Link>

                            {/* User Menu */}
                            {user ? (
                                <div className="dropdown">
                                    <button
                                        className="btn btn-outline-secondary rounded-pill px-3 py-1 d-flex align-items-center gap-2 dropdown-toggle"
                                        type="button"
                                        id="userDropdown"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                    >
                                        {user.foto_url ? (
                                            <img src={user.foto_url} alt="" style={{ width: '28px', height: '28px', borderRadius: '50%', objectFit: 'cover' }} />
                                        ) : (
                                            <div className="rounded-circle d-flex align-items-center justify-content-center text-white" style={{ width: '28px', height: '28px', fontSize: '13px', background: 'var(--gradient-primary)' }}>
                                                {(user.nama_lengkap || user.nama || 'U')[0].toUpperCase()}
                                            </div>
                                        )}
                                        <span className="fw-medium small text-truncate" style={{ maxWidth: '110px' }}>
                                            {user.nama_lengkap || user.nama}
                                        </span>
                                    </button>
                                    <ul className="dropdown-menu dropdown-menu-end shadow-sm border-0 py-2" aria-labelledby="userDropdown">
                                        <li className="px-3 py-1 small border-bottom mb-1" style={{ color: 'var(--text-muted)' }}>
                                            Masuk sebagai <strong>{user.role}</strong>
                                            {user.unit && <div style={{ color: 'var(--color-primary)' }} className="fw-bold">{user.unit.nama_unit}</div>}
                                        </li>
                                        {isStaffOrAdmin && (
                                            <li>
                                                <Link className="dropdown-item d-flex align-items-center gap-2 py-2" to="/admin">
                                                    <LayoutDashboard size={16} className="text-primary" />
                                                    <span>Dashboard Admin</span>
                                                </Link>
                                            </li>
                                        )}
                                        <li>
                                            <Link className="dropdown-item d-flex align-items-center gap-2 py-2" to="/pesanan">
                                                <History size={16} style={{ color: 'var(--color-primary)' }} />
                                                <span>Riwayat Pesanan</span>
                                            </Link>
                                        </li>

                                        <li>
                                            <Link className="dropdown-item d-flex align-items-center gap-2 py-2" to="/profil">
                                                <User size={16} style={{ color: 'var(--text-muted)' }} />
                                                <span>Profil Saya</span>
                                            </Link>
                                        </li>
                                        <li><hr className="dropdown-divider" /></li>
                                        <li>
                                            <button className="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" onClick={() => logout()}>
                                                <LogOut size={16} />
                                                <span>Keluar</span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            ) : (
                                <div className="d-flex gap-2">
                                    <Link to="/login" className="btn btn-agro-outline btn-sm rounded-pill px-3">
                                        Masuk
                                    </Link>
                                    <Link to="/register" className="btn btn-agro btn-sm rounded-pill px-3">
                                        Daftar
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </nav>
        </header>
    );
};

export default Navbar;
