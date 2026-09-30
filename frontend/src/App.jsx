import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { CartProvider } from './context/CartContext';
import { ThemeProvider } from './context/ThemeContext';

// Components
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import ProtectedRoute from './components/ProtectedRoute';
import AdminLayout from './components/AdminLayout';

// Consumer Pages
import Home from './pages/Home';
import Katalog from './pages/Katalog';
import DetailProduk from './pages/DetailProduk';
import Keranjang from './pages/Keranjang';
import Checkout from './pages/Checkout';
import RiwayatPesanan from './pages/RiwayatPesanan';
import DetailPesanan from './pages/DetailPesanan';
import Profil from './pages/Profil';
import Login from './pages/Login';
import Register from './pages/Register';

// Admin Pages
import AdminDashboard from './pages/admin/AdminDashboard';
import AdminProducts from './pages/admin/AdminProducts';
import AdminOrders from './pages/admin/AdminOrders';
import AdminPayments from './pages/admin/AdminPayments';
import AdminReports from './pages/admin/AdminReports';
import AdminUsers from './pages/admin/AdminUsers';
import AdminUnits from './pages/admin/AdminUnits';
import AdminCategories from './pages/admin/AdminCategories';
import AdminVouchers from './pages/admin/AdminVouchers';
import AdminBanners from './pages/admin/AdminBanners';
import AdminReviews from './pages/admin/AdminReviews';
import AdminSettings from './pages/admin/AdminSettings';
import AdminLogs from './pages/admin/AdminLogs';
import AdminOngkir from './pages/admin/AdminOngkir';
import AdminBackup from './pages/admin/AdminBackup';

// Layout wrapper for Public / Consumer pages
const PublicLayout = ({ children }) => (
    <div className="d-flex flex-column min-vh-100">
        <Navbar />
        <main className="flex-grow-1">
            {children}
        </main>
        <Footer />
    </div>
);

function App() {
    return (
        <ThemeProvider>
        <AuthProvider>
            <CartProvider>
                <BrowserRouter>
                    <Routes>
                        {/* Public & Consumer Routes */}
                        <Route path="/" element={<PublicLayout><Home /></PublicLayout>} />
                        <Route path="/katalog" element={<PublicLayout><Katalog /></PublicLayout>} />
                        <Route path="/produk/:id" element={<PublicLayout><DetailProduk /></PublicLayout>} />
                        <Route path="/keranjang" element={<PublicLayout><Keranjang /></PublicLayout>} />
                        
                        <Route 
                            path="/checkout" 
                            element={
                                <ProtectedRoute allowedRoles={['konsumen', 'superadmin']}>
                                    <PublicLayout><Checkout /></PublicLayout>
                                </ProtectedRoute>
                            } 
                        />
                        <Route 
                            path="/pesanan" 
                            element={
                                <ProtectedRoute allowedRoles={['konsumen', 'superadmin']}>
                                    <PublicLayout><RiwayatPesanan /></PublicLayout>
                                </ProtectedRoute>
                            } 
                        />
                        <Route 
                            path="/pesanan/:id" 
                            element={
                                <ProtectedRoute allowedRoles={['konsumen', 'superadmin']}>
                                    <PublicLayout><DetailPesanan /></PublicLayout>
                                </ProtectedRoute>
                            } 
                        />
                        <Route 
                            path="/profil" 
                            element={
                                <ProtectedRoute>
                                    <PublicLayout><Profil /></PublicLayout>
                                </ProtectedRoute>
                            } 
                        />

                        {/* Auth Routes */}
                        <Route path="/login" element={<Login />} />
                        <Route path="/register" element={<Register />} />

                        {/* Admin Routes */}
                        <Route 
                            path="/admin" 
                            element={
                                <ProtectedRoute allowedRoles={['superadmin', 'admin_unit', 'pimpinan']}>
                                    <AdminLayout />
                                </ProtectedRoute>
                            }
                        >
                            <Route index element={<AdminDashboard />} />
                            <Route path="products" element={<AdminProducts />} />
                            <Route path="orders" element={<AdminOrders />} />
                            <Route path="payments" element={<AdminPayments />} />
                            <Route path="reports" element={<AdminReports />} />
                            <Route path="reviews" element={<AdminReviews />} />
                            
                            {/* Superadmin Exclusive Routes */}
                            <Route 
                                path="users" 
                                element={
                                    <ProtectedRoute allowedRoles={['superadmin']}>
                                        <AdminUsers />
                                    </ProtectedRoute>
                                } 
                            />
                            <Route 
                                path="units" 
                                element={
                                    <ProtectedRoute allowedRoles={['superadmin', 'pimpinan']}>
                                        <AdminUnits />
                                    </ProtectedRoute>
                                } 
                            />
                            <Route 
                                path="categories" 
                                element={
                                    <ProtectedRoute allowedRoles={['superadmin']}>
                                        <AdminCategories />
                                    </ProtectedRoute>
                                } 
                            />
                            <Route 
                                path="vouchers" 
                                element={
                                    <ProtectedRoute allowedRoles={['superadmin']}>
                                        <AdminVouchers />
                                    </ProtectedRoute>
                                } 
                            />
                            <Route 
                                path="banners" 
                                element={
                                    <ProtectedRoute allowedRoles={['superadmin']}>
                                        <AdminBanners />
                                    </ProtectedRoute>
                                } 
                            />
                            <Route 
                                path="ongkir" 
                                element={
                                    <ProtectedRoute allowedRoles={['superadmin']}>
                                        <AdminOngkir />
                                    </ProtectedRoute>
                                } 
                            />
                            <Route 
                                path="backup" 
                                element={
                                    <ProtectedRoute allowedRoles={['superadmin']}>
                                        <AdminBackup />
                                    </ProtectedRoute>
                                } 
                            />
                            <Route 
                                path="logs" 
                                element={
                                    <ProtectedRoute allowedRoles={['superadmin', 'pimpinan']}>
                                        <AdminLogs />
                                    </ProtectedRoute>
                                } 
                            />
                            <Route 
                                path="settings" 
                                element={
                                    <ProtectedRoute allowedRoles={['superadmin']}>
                                        <AdminSettings />
                                    </ProtectedRoute>
                                } 
                            />
                        </Route>

                        {/* Fallback 404 */}
                        <Route path="*" element={<Navigate to="/" replace />} />
                    </Routes>
                </BrowserRouter>
            </CartProvider>
        </AuthProvider>
        </ThemeProvider>
    );
}

export default App;
