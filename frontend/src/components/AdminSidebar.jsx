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
    User
} from 'lucide-react';

const AdminSidebar = () => {
    const { user, isSuperadmin, isAdminUnit, isPimpinan, logout } = useAuth();
    const { isDark, toggleTheme } = useTheme();

    return (
        <div className="d-flex flex-column flex-shrink-0 p-3 border-end" style={{ width: '270px', minHeight: '100vh', background: 'var(--bg-sidebar)', borderColor: 'var(--border-color)' }}>
            {/* Brand Header */}
            <div className="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                <img 
                    src="/logo-polinela.png" 
                    alt="Polinela" 
                    style={{ width: '36px', height: '36px', objectFit: 'contain' }} 
                />
                <div>
                    <h6 className="fw-bold mb-0 text-agro">POLINELA AGRO</h6>
                    <small style={{ fontSize: '10.5px', color: 'var(--text-muted)', letterSpacing: '0.5px' }}>
                        {isSuperadmin ? '👑 SUPERADMIN PANEL' : (isAdminUnit ? '🏪 PANEL ADMIN UNIT' : '📊 PORTAL PIMPINAN')}
                    </small>
                </div>
            </div>

            {/* User Unit Info Badge */}
            <div className="p-3 mb-3 rounded-3" style={{ background: 'var(--bg-card-hover)', border: '1px solid var(--border-color)' }}>
                <div className="d-flex align-items-center gap-2 mb-1">
                    {user?.foto_url ? (
                        <img src={user.foto_url} alt="" style={{ width: '32px', height: '32px', borderRadius: '50%', objectFit: 'cover', border: '2px solid var(--color-primary)' }} />
                    ) : (
                        <div className="rounded-circle d-flex align-items-center justify-content-center text-white" style={{ width: '32px', height: '32px', fontSize: '13px', background: 'var(--gradient-primary)' }}>
                            {(user?.nama_lengkap || user?.nama || 'U')[0].toUpperCase()}
                        </div>
                    )}
                    <div className="fw-bold text-truncate small" style={{ color: 'var(--text-heading)' }}>
                        {user?.nama_lengkap || user?.nama}
                    </div>
                </div>
                <div className="d-flex align-items-center gap-1 mt-1">
                    <span className="badge bg-agro text-capitalize" style={{ fontSize: '10px' }}>{user?.role}</span>
                    {user?.unit && (
                        <span className="badge text-truncate bg-secondary" style={{ fontSize: '10px', maxWidth: '120px' }}>
                            {user?.unit.nama_unit}
                        </span>
                    )}
                </div>
            </div>

            {/* Scrollable Navigation Menus */}
            <div className="overflow-y-auto flex-grow-1 pe-1" style={{ maxHeight: 'calc(100vh - 280px)' }}>
                {/* Section: MENU UTAMA */}
                <div className="small fw-bold text-muted px-3 mb-1" style={{ fontSize: '11px', letterSpacing: '0.6px' }}>
                    MENU UTAMA
                </div>
                <ul className="nav nav-pills flex-column gap-1 mb-3">
                    <li className="nav-item">
                        <NavLink 
                            to="/admin" 
                            end
                            className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                            style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                        >
                            <LayoutDashboard size={17} />
                            <span>Dashboard</span>
                        </NavLink>
                    </li>
                    <li className="nav-item">
                        <NavLink 
                            to="/admin/reports" 
                            className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                            style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                        >
                            <BarChart3 size={17} />
                            <span>Laporan Penjualan</span>
                        </NavLink>
                    </li>
                </ul>

                {/* Section: TRANSAKSI & TOKO (Admin Unit & Superadmin) */}
                {(isSuperadmin || isAdminUnit) && (
                    <>
                        <div className="small fw-bold text-muted px-3 mb-1" style={{ fontSize: '11px', letterSpacing: '0.6px' }}>
                            TRANSAKSI & TOKO
                        </div>
                        <ul className="nav nav-pills flex-column gap-1 mb-3">
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/products" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <Package size={17} />
                                    <span>Katalog & Stok</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/orders" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <ShoppingBag size={17} />
                                    <span>Pesanan Unit</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/payments" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <CreditCard size={17} />
                                    <span>Verifikasi Pembayaran</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/reviews" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <MessageSquare size={17} />
                                    <span>Ulasan Produk</span>
                                </NavLink>
                            </li>
                        </ul>
                    </>
                )}

                {/* Section: PENGAWASAN EKSEKUTIF (Khusus Pimpinan) */}
                {isPimpinan && (
                    <>
                        <div className="small fw-bold text-muted px-3 mb-1" style={{ fontSize: '11px', letterSpacing: '0.6px' }}>
                            PENGAWASAN EKSEKUTIF
                        </div>
                        <ul className="nav nav-pills flex-column gap-1 mb-3">
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/units" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <Store size={17} />
                                    <span>Unit Usaha TEFA</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/reviews" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <MessageSquare size={17} />
                                    <span>Ulasan & Kepuasan</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/logs" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <History size={17} />
                                    <span>Audit Log Aktivitas</span>
                                </NavLink>
                            </li>
                        </ul>
                    </>
                )}

                {/* Section: SUPERADMIN PANEL */}
                {isSuperadmin && (

                    <>
                        <div className="small fw-bold text-muted px-3 mb-1" style={{ fontSize: '11px', letterSpacing: '0.6px' }}>
                            SUPERADMIN MASTER
                        </div>
                        <ul className="nav nav-pills flex-column gap-1 mb-3">
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/users" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <Users size={17} />
                                    <span>Kelola Pengguna</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/units" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <Store size={17} />
                                    <span>Unit Usaha TEFA</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/categories" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <Tag size={17} />
                                    <span>Kategori Produk</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/vouchers" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <Ticket size={17} />
                                    <span>Voucher & Promo</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/banners" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <Image size={17} />
                                    <span>Banner Promosi</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/ongkir" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <Truck size={17} />
                                    <span>Tarif Pengiriman</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/backup" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <Database size={17} />
                                    <span>Backup Database</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/logs" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <History size={17} />
                                    <span>Log Aktivitas</span>
                                </NavLink>
                            </li>
                            <li className="nav-item">
                                <NavLink 
                                    to="/admin/settings" 
                                    className={({ isActive }) => `nav-link d-flex align-items-center gap-2 py-2 px-3 rounded-3 ${isActive ? 'text-white fw-bold' : ''}`}
                                    style={({ isActive }) => isActive ? { background: 'var(--gradient-primary)' } : { color: 'var(--text-body)' }}
                                >
                                    <Sliders size={17} />
                                    <span>Pengaturan Toko</span>
                                </NavLink>
                            </li>
                        </ul>
                    </>
                )}
            </div>

            {/* Bottom Actions */}
            <div className="pt-2 border-top d-flex flex-column gap-2 mt-auto">
                <button 
                    onClick={toggleTheme} 
                    className="btn btn-sm d-flex align-items-center justify-content-center gap-2 rounded-3"
                    style={{ background: 'var(--bg-card-hover)', border: '1px solid var(--border-color)', color: 'var(--text-body)' }}
                >
                    {isDark ? <Sun size={15} /> : <Moon size={15} />}
                    <span style={{ fontSize: '12px' }}>{isDark ? 'Mode Terang' : 'Mode Gelap'}</span>
                </button>
                
                <div className="d-flex gap-2">
                    <Link to="/profil" className="btn btn-sm flex-fill d-flex align-items-center justify-content-center gap-1 rounded-3" style={{ border: '1px solid var(--border-color)', color: 'var(--text-body)', fontSize: '12px' }}>
                        <User size={14} />
                        <span>Profil</span>
                    </Link>
                    <Link to="/" className="btn btn-sm flex-fill btn-outline-secondary d-flex align-items-center justify-content-center gap-1 rounded-3" style={{ fontSize: '12px' }}>
                        <ArrowLeft size={14} />
                        <span>Toko</span>
                    </Link>
                </div>
                
                <button 
                    onClick={() => logout()} 
                    className="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center gap-2 rounded-3"
                >
                    <LogOut size={15} />
                    <span style={{ fontSize: '12px' }}>Keluar</span>
                </button>
            </div>
        </div>
    );
};

export default AdminSidebar;
