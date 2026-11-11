import React, { useState } from 'react';
import { Outlet } from 'react-router-dom';
import AdminSidebar from './AdminSidebar';
import { Menu, X } from 'lucide-react';
import { useAuth } from '../context/AuthContext';

const AdminLayout = () => {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { isSuperadmin, isAdminUnit } = useAuth();

    return (
        <div className="d-flex flex-column flex-lg-row min-vh-100 bg-light">
            {/* Mobile Header Bar */}
            <div 
                className="d-lg-none d-flex align-items-center justify-content-between px-3 py-2 border-bottom shadow-sm text-white no-print" 
                style={{ background: 'linear-gradient(90deg, #0a331c 0%, #062313 100%)', zIndex: 1030 }}
            >
                <div className="d-flex align-items-center gap-2">
                    <button 
                        type="button"
                        onClick={() => setSidebarOpen(!sidebarOpen)} 
                        className="btn btn-sm btn-outline-light d-flex align-items-center justify-content-center p-1.5 rounded-3"
                        aria-label="Toggle Sidebar"
                    >
                        {sidebarOpen ? <X size={20} /> : <Menu size={20} />}
                    </button>
                    <div className="d-flex align-items-center gap-2">
                        <img src="/logo-polinela.png" alt="Logo" style={{ width: '24px', height: '24px', objectFit: 'contain' }} />
                        <span className="fw-bold small text-white" style={{ letterSpacing: '0.5px', fontSize: '13px' }}>POLINELA AGRO</span>
                    </div>
                </div>
                <div className="d-flex align-items-center gap-2">
                    <span className="badge rounded-pill bg-success bg-opacity-25 text-white small px-2.5 py-1" style={{ fontSize: '10px' }}>
                        {isSuperadmin ? 'SUPERADMIN' : isAdminUnit ? 'ADMIN UNIT' : 'PIMPINAN'}
                    </span>
                </div>
            </div>

            {/* Backdrop overlay for mobile drawer */}
            {sidebarOpen && (
                <div 
                    className="modal-backdrop fade show d-lg-none" 
                    onClick={() => setSidebarOpen(false)}
                    style={{ zIndex: 1040, background: 'rgba(0, 0, 0, 0.6)' }}
                />
            )}

            {/* Sidebar with responsive mobile drawer support */}
            <AdminSidebar 
                isOpen={sidebarOpen} 
                onClose={() => setSidebarOpen(false)} 
            />

            <div className="flex-grow-1 p-3 p-md-4 overflow-auto" style={{ maxHeight: '100vh', minWidth: 0 }}>
                <Outlet />
            </div>
        </div>
    );
};

export default AdminLayout;
