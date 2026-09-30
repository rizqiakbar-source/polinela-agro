import React from 'react';
import { Link } from 'react-router-dom';

const Footer = () => {
    return (
        <footer className="bg-dark text-white pt-5 pb-3 mt-auto">
            <div className="container">
                <div className="row g-4 mb-4">
                    {/* Column 1: Info */}
                    <div className="col-lg-4 col-md-6">
                        <div className="d-flex align-items-center gap-2 mb-3">
                            <span className="fs-3">🌿</span>
                            <h5 className="fw-bold mb-0 text-success">POLINELA AGRO</h5>
                        </div>
                        <p className="text-secondary small">
                            Platform Marketplace & E-Commerce Multi-Unit Perkebunan Politeknik Negeri Lampung. 
                            Menyediakan berbagai produk perkebunan berkualitas tinggi seperti Kopi, Kakao, Bibit Tanaman, dan Pupuk Organik langsung dari kebun praktikum & riset.
                        </p>
                        <div className="d-flex gap-2">
                            <span className="badge bg-secondary">Multi-Store</span>
                            <span className="badge bg-success">1x Unified Payment</span>
                            <span className="badge bg-info text-dark">Hasil Riset Polinela</span>
                        </div>
                    </div>

                    {/* Column 2: Units */}
                    <div className="col-lg-3 col-md-6">
                        <h6 className="fw-bold text-white mb-3">Unit Toko Perkebunan</h6>
                        <ul className="list-unstyled text-secondary small mb-0">
                            <li className="mb-2"><Link to="/katalog?unit=1" className="text-secondary text-decoration-none hover-white">☕ Unit Kopi Polinela</Link></li>
                            <li className="mb-2"><Link to="/katalog?unit=2" className="text-secondary text-decoration-none hover-white">🍫 Unit Kakao & Cokelat</Link></li>
                            <li className="mb-2"><Link to="/katalog?unit=3" className="text-secondary text-decoration-none hover-white">🌴 Unit Kelapa Sawit</Link></li>
                            <li className="mb-2"><Link to="/katalog?unit=4" className="text-secondary text-decoration-none hover-white">🌱 Unit Bibit & Hortikultura</Link></li>
                        </ul>
                    </div>

                    {/* Column 3: Quick Links */}
                    <div className="col-lg-2 col-md-6">
                        <h6 className="fw-bold text-white mb-3">Navigasi</h6>
                        <ul className="list-unstyled text-secondary small mb-0">
                            <li className="mb-2"><Link to="/" className="text-secondary text-decoration-none hover-white">Beranda</Link></li>
                            <li className="mb-2"><Link to="/katalog" className="text-secondary text-decoration-none hover-white">Katalog Produk</Link></li>
                            <li className="mb-2"><Link to="/keranjang" className="text-secondary text-decoration-none hover-white">Keranjang Belanja</Link></li>
                            <li className="mb-2"><Link to="/pesanan" className="text-secondary text-decoration-none hover-white">Cek Pesanan</Link></li>
                        </ul>
                    </div>

                    {/* Column 4: Contact & Location */}
                    <div className="col-lg-3 col-md-6">
                        <h6 className="fw-bold text-white mb-3">Kontak & Lokasi</h6>
                        <p className="text-secondary small mb-2">
                            <i className="bi bi-geo-alt-fill text-success me-2"></i>
                            Jl. Soekarno Hatta No. 10, Rajabasa, Kota Bandar Lampung, Lampung 35144
                        </p>
                        <p className="text-secondary small mb-2">
                            <i className="bi bi-envelope-fill text-success me-2"></i>
                            agro@polinela.ac.id
                        </p>
                        <p className="text-secondary small mb-0">
                            <i className="bi bi-telephone-fill text-success me-2"></i>
                            (0721) 703995
                        </p>
                    </div>
                </div>

                <hr className="border-secondary opacity-25" />
                <div className="d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary">
                    <p className="mb-2 mb-md-0">&copy; {new Date().getFullYear()} Polinela Agro. Politeknik Negeri Lampung. All rights reserved.</p>
                    <p className="mb-0">Powered by React Vite & Laravel REST API</p>
                </div>
            </div>
        </footer>
    );
};

export default Footer;
