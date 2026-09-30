import React from 'react';
import { Outlet } from 'react-router-dom';
import AdminSidebar from './AdminSidebar';

const AdminLayout = () => {
    return (
        <div className="d-flex min-vh-100 bg-light">
            <AdminSidebar />
            <div className="flex-grow-1 p-4 overflow-auto" style={{ maxHeight: '100vh' }}>
                <Outlet />
            </div>
        </div>
    );
};

export default AdminLayout;
