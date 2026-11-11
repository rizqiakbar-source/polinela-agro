import React, { Suspense, lazy } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { CartProvider } from './context/CartContext';
import { ThemeProvider } from './context/ThemeContext';

// Components (eager loaded for immediate navigation structure)
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import ProtectedRoute from './components/ProtectedRoute';
import AdminLayout from './components/AdminLayout';

// Consumer Pages (Lazy Loaded for minimal initial bundle)
const Home = lazy(() => import('./pages/Home'));
const Katalog = lazy(() => import('./pages/Katalog'));
const DetailProduk = lazy(() => import('./pages/DetailProduk'));
const Keranjang = lazy(() => import('./pages/Keranjang'));
const Checkout = lazy(() => import('./pages/Checkout'));
const RiwayatPesanan = lazy(() => import('./pages/RiwayatPesanan'));
const DetailPesanan = lazy(() => import('./pages/DetailPesanan'));
const Profil = lazy(() => import('./pages/Profil'));
const Login = lazy(() => import('./pages/Login'));
const Register = lazy(() => import('./pages/Register'));
const VerifyOtp = lazy(() => import('./pages/VerifyOtp'));

// Admin Pages (Lazy Loaded on-demand only when admin visits /admin)
const AdminDashboard = lazy(() => import('./pages/admin/AdminDashboard'));
const AdminProducts = lazy(() => import('./pages/admin/AdminProducts'));
const AdminOrders = lazy(() => import('./pages/admin/AdminOrders'));
const AdminPayments = lazy(() => import('./pages/admin/AdminPayments'));
const AdminReports = lazy(() => import('./pages/admin/AdminReports'));
const AdminUsers = lazy(() => import('./pages/admin/AdminUsers'));
const AdminUnits = lazy(() => import('./pages/admin/AdminUnits'));
const AdminCategories = lazy(() => import('./pages/admin/AdminCategories'));
const AdminVouchers = lazy(() => import('./pages/admin/AdminVouchers'));
const AdminBanners = lazy(() => import('./pages/admin/AdminBanners'));
const AdminReviews = lazy(() => import('./pages/admin/AdminReviews'));
const AdminSettings = lazy(() => import('./pages/admin/AdminSettings'));
const AdminLogs = lazy(() => import('./pages/admin/AdminLogs'));
const AdminOngkir = lazy(() => import('./pages/admin/AdminOngkir'));
const AdminBackup = lazy(() => import('./pages/admin/AdminBackup'));

// Lightweight Loading Spinner during route transitions
const PageLoader = () => (
    <div className="d-flex flex-column align-items-center justify-content-center min-vh-50 py-5">
        <div className="spinner-border text-success" role="status" style={{ width: '2.5rem', height: '2.5rem' }}>
            <span className="visually-hidden">Memuat halaman...</span>
        </div>
    </div>
);

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
                    <Suspense fallback={<PageLoader />}>
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
                        <Route path="/verify-otp" element={<VerifyOtp />} />

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
                    </Suspense>
                </BrowserRouter>
            </CartProvider>
        </AuthProvider>
        </ThemeProvider>
    );
}

export default App;
