import React from 'react';
import { NavLink, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import { 
    LayoutDashboard, 
    Package, 
    ShoppingBag, 
    CreditCard, 
    BarChart3, 
    Users, 
    Store, 
    Tag,
    Ticket,
    Image,
    MessageSquare,
    Sliders,
    History,
    Truck,
    Database,
    ArrowLeft,
    LogOut,
    Sun,
    Moon,
    User,
    ShieldCheck
} from 'lucide-react';

const AdminSidebar = () => {
    const { user, isSuperadmin, isAdminUnit, isPimpinan, logout } = useAuth();
    const { isDark, toggleTheme } = useTheme();

    return (
        <div className="admin-sidebar-forest d-flex flex-column flex-shrink-0 p-3" style={{ width: '275px', minHeight: '100vh' }}>
            {/* Brand Header */}
            <div className="admin-sidebar-brand d-flex align-items-center gap-2 mb-3 pb-3">
                <div className="admin-sidebar-logo-box">
                    <img 
                        src="/logo-polinela.png" 
                        alt="Polinela" 
                        style={{ width: '32px', height: '32px', objectFit: 'contain' }} 
                    />
                </div>
                <div>
                    <h6 className="fw-bold mb-0 text-white" style={{ letterSpacing: '0.6px' }}>POLINELA AGRO</h6>
                    <small className="d-flex align-items-center gap-1" style={{ fontSize: '10px', color: '#86efac', letterSpacing: '0.5px', fontWeight: 600 }}>
                        {isSuperadmin ? (
                            <><ShieldCheck size={11} className="text-warning" /> SUPERADMIN MASTER</>
                        ) : isAdminUnit ? (
                            <><Store size={11} className="text-light" /> PANEL ADMIN UNIT</>
                        ) : (
                            <><BarChart3 size={11} className="text-info" /> PORTAL PIMPINAN</>
                        )}
                    </small>
                </div>
            </div>

            {/* User Unit Info Badge */}
            <div className="admin-sidebar-user-card p-3 mb-3 rounded-3">
                <div className="d-flex align-items-center gap-2 mb-1">
                    {user?.foto_url ? (
                        <img src={user.foto_url} alt="" style={{ width: '34px', height: '34px', borderRadius: '50%', objectFit: 'cover', border: '2px solid #4ade80' }} />
                    ) : (
                        <div className="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style={{ width: '34px', height: '34px', fontSize: '13px', background: 'linear-gradient(135deg, #22c55e 0%, #15803d 100%)', border: '2px solid rgba(255,255,255,0.2)' }}>
                            {(user?.nama_lengkap || user?.nama || 'U')[0].toUpperCase()}
                        </div>
                    )}
                    <div className="fw-bold text-truncate small text-white">
                        {user?.nama_lengkap || user?.nama}
                    </div>
                </div>
                <div className="d-flex align-items-center flex-wrap gap-1 mt-1">
                    <span className="badge rounded-pill text-capitalize" style={{ fontSize: '10px', background: 'rgba(34, 197, 94, 0.25)', color: '#86efac', border: '1px solid rgba(34, 197, 94, 0.4)' }}>
                        {user?.role?.replace('_', ' ')}
                    </span>
                    {user?.unit && (
                        <span className="badge text-truncate rounded-pill" style={{ fontSize: '10px', maxWidth: '130px', background: 'rgba(255, 255, 255, 0.12)', color: '#e2e8f0' }}>
                            {user?.unit.nama_unit}
                        </span>
                    )}
                </div>
            </div>

            {/* Scrollable Navigation Menus */}
            <div className="admin-sidebar-scroll overflow-y-auto flex-grow-1 pe-1" style={{ maxHeight: 'calc(100vh - 280px)' }}>
                {/* Section: MENU UTAMA */}
                <div className="admin-sidebar-section-title px-3 mb-1">
                    Menu Utama
                </div>
                <ul className="nav nav-pills flex-column gap-1 mb-3">
                    <li className="nav-item">
                        <NavLink 
                            to="/admin" 
                            end
                            className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                        >
                            <LayoutDashboard size={17} className="nav-icon" />
                            <span>Dashboard</span>
                        </NavLink>
                    </li>
                    <li className="nav-item">
                        <NavLink 
                            to="/admin/reports" 
                            className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                        >
                            <BarChart3 size={17} className="nav-icon" />
                            <span>Laporan Penjualan</span>
                        </NavLink>
                    </li>
                </ul>

                {/* Section: TRANSAKSI & TOKO (Admin Unit & Superadmin) */}
                {(isSuperadmin || isAdminUnit) && (
                    <>
                        <div className="admin-sidebar-section-title px-3 mb-1">
                            Transaksi & Toko
                        </div>
                        <ul className="nav nav-pills flex-column gap-1 mb-3">
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/products" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <Package size={17} className="nav-icon" />
                                    <span>Katalog & Stok</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/orders" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <ShoppingBag size={17} className="nav-icon" />
                                    <span>Pesanan Unit</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/payments" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <CreditCard size={17} className="nav-icon" />
                                    <span>Verifikasi Pembayaran</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/reviews" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <MessageSquare size={17} className="nav-icon" />
                                    <span>Ulasan Produk</span>
                                </NavLink>
                            </li>
                        </ul>
                    </>
                )}

                {/* Section: PENGAWASAN EKSEKUTIF (Khusus Pimpinan) */}
                {isPimpinan && (
                    <>
                        <div className="admin-sidebar-section-title px-3 mb-1">
                            Pengawasan Eksekutif
                        </div>
                        <ul className="nav nav-pills flex-column gap-1 mb-3">
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/units" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <Store size={17} className="nav-icon" />
                                    <span>Unit Usaha TEFA</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/reviews" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <MessageSquare size={17} className="nav-icon" />
                                    <span>Ulasan & Kepuasan</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/logs" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <History size={17} className="nav-icon" />
                                    <span>Audit Log Aktivitas</span>
                                </NavLink>
                            </li>
                        </ul>
                    </>
                )}

                {/* Section: SUPERADMIN MASTER */}
                {isSuperadmin && (
                    <>
                        <div className="admin-sidebar-section-title px-3 mb-1">
                            Superadmin Master
                        </div>
                        <ul className="nav nav-pills flex-column gap-1 mb-3">
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/users" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <Users size={17} className="nav-icon" />
                                    <span>Kelola Pengguna</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/units" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <Store size={17} className="nav-icon" />
                                    <span>Unit Usaha TEFA</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/categories" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <Tag size={17} className="nav-icon" />
                                    <span>Kategori Produk</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/vouchers" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <Ticket size={17} className="nav-icon" />
                                    <span>Voucher & Promo</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/banners" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <Image size={17} className="nav-icon" />
                                    <span>Banner Promosi</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/ongkir" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <Truck size={17} className="nav-icon" />
                                    <span>Tarif Pengiriman</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/backup" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <Database size={17} className="nav-icon" />
                                    <span>Backup Database</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/logs" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <History size={17} className="nav-icon" />
                                    <span>Log Aktivitas</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/settings" 
                                    className={({ isActive }) => `admin-sidebar-link nav-link d-flex align-items-center gap-2 ${isActive ? 'active' : ''}`}
                                >
                                    <Sliders size={17} className="nav-icon" />
                                    <span>Pengaturan Toko</span>
                                </NavLink>
                            </li>
                        </ul>
                    </>
                )}
            </div>

            {/* Bottom Actions */}
            <div className="pt-3 d-flex flex-column gap-2 mt-auto" style={{ borderTop: '1px solid rgba(255, 255, 255, 0.1)', position: 'relative', zIndex: 1 }}>
                <button 
                    onClick={toggleTheme} 
                    className="admin-sidebar-bottom-btn btn btn-sm d-flex align-items-center justify-content-center gap-2 rounded-3 py-2"
                >
                    {isDark ? <Sun size={15} className="text-warning" /> : <Moon size={15} />}
                    <span style={{ fontSize: '12px' }}>{isDark ? 'Mode Terang' : 'Mode Gelap'}</span>
                </button>
                
                <div className="d-flex gap-2">
                    <Link to="/profil" className="admin-sidebar-bottom-btn btn btn-sm flex-fill d-flex align-items-center justify-content-center gap-1 rounded-3 py-2" style={{ fontSize: '12px' }}>
                        <User size={14} />
                        <span>Profil</span>
                    </Link>
                    <Link to="/" className="admin-sidebar-bottom-btn btn btn-sm flex-fill d-flex align-items-center justify-content-center gap-1 rounded-3 py-2" style={{ fontSize: '12px' }}>
                        <ArrowLeft size={14} />
                        <span>Toko</span>
                    </Link>
                </div>
                
                <button 
                    onClick={() => logout()} 
                    className="admin-sidebar-logout-btn btn btn-sm d-flex align-items-center justify-content-center gap-2 rounded-3 py-2"
                >
                    <LogOut size={15} />
                    <span style={{ fontSize: '12px', fontWeight: 600 }}>Keluar</span>
                </button>
            </div>
        </div>
    );
};

export default AdminSidebar;
