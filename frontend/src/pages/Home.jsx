import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import client from '../api/client';
import ProductCard from '../components/ProductCard';
import { ArrowRight, ShoppingBag, ShieldCheck, Truck, Award, Sparkles, Coffee, Package, Palmtree, Sprout, TreePine } from 'lucide-react';

const Home = () => {
    const [units, setUnits] = useState([]);
    const [featuredProducts, setFeaturedProducts] = useState([]);
    const [banners, setBanners] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchHomeData = async () => {
            try {
                const [unitsRes, productsRes, bannersRes] = await Promise.all([
                    client.get('/units'),
                    client.get('/products?per_page=8&sort=terbaru'),
                    client.get('/banners'),
                ]);

                if (unitsRes.data.status === 'success' || unitsRes.data.success) {
                    setUnits(unitsRes.data.data || []);
                }
                if (productsRes.data.status === 'success' || productsRes.data.success) {
                    const pData = productsRes.data.data;
                    setFeaturedProducts(Array.isArray(pData) ? pData : (pData?.data || []));
                }
                if (bannersRes.data.status === 'success' || bannersRes.data.success) {
                    setBanners(bannersRes.data.data || []);
                }
            } catch (err) {
                console.error('Error fetching home data:', err);
            } finally {
                setLoading(false);
            }
        };

        fetchHomeData();
    }, []);

    const renderUnitIcon = (unitId, unitNama = '') => {
        const id = String(unitId);
        const name = unitNama.toLowerCase();
        if (id === '1' || name.includes('kopi')) {
            return <Coffee size={36} className="text-warning" />;
        }
        if (id === '2' || name.includes('kakao') || name.includes('cokelat')) {
            return <Package size={36} className="text-info" />;
        }
        if (id === '3' || name.includes('sawit') || name.includes('kelapa')) {
            return <Palmtree size={36} className="text-success" />;
        }
        if (id === '4' || name.includes('bibit') || name.includes('horti')) {
            return <Sprout size={36} className="text-success" />;
        }
        return <TreePine size={36} className="text-success" />;
    };

    const [currentSlide, setCurrentSlide] = useState(0);
    const [isHovered, setIsHovered] = useState(false);

    // Default slides matching original design
    const defaultSlides = [
        {
            id: 1,
            badge: 'Panen Unggulan Kampus Polinela',
            judul: 'Panen Raya Kopi Robusta & Kakao Polinela',
            subjudul: 'Nikmati cita rasa kopi petik merah dan produk olahan kakao asli kebun riset kampus vokasi terbaik Lampung.',
            gambar: 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=1600&auto=format&fit=crop&q=80',
            primaryText: 'Belanja Produk Sekarang',
            primaryLink: '/katalog',
            secondaryText: 'Tentang Kebun Riset',
            secondaryLink: '#units-section',
        },
        {
            id: 2,
            badge: 'Teaching Factory Unggulan',
            judul: 'Lada Hitam Lampung & Minyak Atsiri Alami',
            subjudul: 'Rempah aromatik berkualitas ekspor dan ekstrak serai wangi hasil produksi Teaching Factory Polinela.',
            gambar: 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=1600&auto=format&fit=crop&q=80',
            primaryText: 'Jelajahi Rempah & Atsiri',
            primaryLink: '/katalog',
            secondaryText: 'Lihat Unit Usaha',
            secondaryLink: '#units-section',
        },
        {
            id: 3,
            badge: 'Inovasi Pertanian & Perkebunan',
            judul: 'Bibit Sawit Unggul & Klon Kopi Pilihan',
            subjudul: 'Bibit tanaman berkualitas tinggi hasil sertifikasi riset dosen dan mahasiswa perkebunan Polinela.',
            gambar: 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=1600&auto=format&fit=crop&q=80',
            primaryText: 'Beli Bibit Berkualitas',
            primaryLink: '/katalog',
            secondaryText: 'Pelajari Produk',
            secondaryLink: '#units-section',
        },
    ];

    // Merge API banners with default slides if available
    const activeSlides = banners.length > 0
        ? banners.map((b, idx) => ({
            id: b.id || idx,
            badge: b.badge || (idx === 0 ? 'Panen Unggulan Kampus Polinela' : 'Teaching Factory Unggulan'),
            judul: b.judul || 'Produk Unggulan Perkebunan Polinela',
            subjudul: b.subjudul || 'Hasil riset dan inovasi pertanian Politeknik Negeri Lampung.',
            gambar: b.gambar?.startsWith('http')
                ? b.gambar
                : (defaultSlides[idx % defaultSlides.length]?.gambar || defaultSlides[0].gambar),
            primaryText: 'Belanja Produk Sekarang',
            primaryLink: b.link ? `/${b.link.replace(/^\//, '')}` : '/katalog',
            secondaryText: 'Tentang Kebun Riset',
            secondaryLink: '#units-section',
        }))
        : defaultSlides;

    // Auto-slide effect every 4.5 seconds
    useEffect(() => {
        if (isHovered || activeSlides.length <= 1) return;
        const interval = setInterval(() => {
            setCurrentSlide((prev) => (prev + 1) % activeSlides.length);
        }, 4500);
        return () => clearInterval(interval);
    }, [isHovered, activeSlides.length]);

    const prevSlide = () => {
        setCurrentSlide((prev) => (prev - 1 + activeSlides.length) % activeSlides.length);
    };

    const nextSlide = () => {
        setCurrentSlide((prev) => (prev + 1) % activeSlides.length);
    };

    return (
        <div>
            {/* Hero Carousel Slider */}
            <section className="container py-4">
                <div 
                    className="hero-slider-wrap position-relative"
                    onMouseEnter={() => setIsHovered(true)}
                    onMouseLeave={() => setIsHovered(false)}
                >
                    {/* Slides */}
                    {activeSlides.map((slide, idx) => (
                        <div
                            key={slide.id || idx}
                            className={`hero-slide-item ${idx === currentSlide ? 'd-flex' : 'd-none'}`}
                            style={{
                                backgroundImage: `url('${slide.gambar}')`,
                            }}
                        >
                            <div className="hero-overlay"></div>
                            <div className="hero-content animate__animated animate__fadeIn">
                                <span className="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3 d-inline-flex align-items-center gap-1 shadow-sm">
                                    <Award size={16} /> {slide.badge}
                                </span>
                                <h1 className="display-5 fw-bold mb-3 text-white lh-sm">
                                    {slide.judul}
                                </h1>
                                <p className="lead mb-4 text-white-50" style={{ fontSize: '1.05rem', lineHeight: '1.6' }}>
                                    {slide.subjudul}
                                </p>
                                <div className="d-flex flex-wrap gap-3">
                                    <Link to={slide.primaryLink} className="btn btn-agro btn-lg px-4 fs-6 rounded-pill d-inline-flex align-items-center gap-2 shadow">
                                        <ShoppingBag size={18} /> {slide.primaryText}
                                    </Link>
                                    {slide.secondaryLink.startsWith('#') ? (
                                        <a href={slide.secondaryLink} className="btn btn-outline-light btn-lg px-4 fs-6 rounded-pill d-inline-flex align-items-center gap-2">
                                            {slide.secondaryText}
                                        </a>
                                    ) : (
                                        <Link to={slide.secondaryLink} className="btn btn-outline-light btn-lg px-4 fs-6 rounded-pill d-inline-flex align-items-center gap-2">
                                            {slide.secondaryText}
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}

                    {/* Navigation Arrows */}
                    {activeSlides.length > 1 && (
                        <>
                            <button 
                                className="hero-nav-btn prev"
                                onClick={prevSlide}
                                aria-label="Previous Slide"
                            >
                                &#10094;
                            </button>
                            <button 
                                className="hero-nav-btn next"
                                onClick={nextSlide}
                                aria-label="Next Slide"
                            >
                                &#10095;
                            </button>
                        </>
                    )}

                    {/* Indicator Dots */}
                    {activeSlides.length > 1 && (
                        <div className="hero-indicators">
                            {activeSlides.map((_, idx) => (
                                <button
                                    key={idx}
                                    type="button"
                                    className={`hero-dot ${idx === currentSlide ? 'active' : ''}`}
                                    onClick={() => setCurrentSlide(idx)}
                                    aria-label={`Slide ${idx + 1}`}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </section>

            {/* Keunggulan Kampus (Trust Pillars) */}
            <section className="container py-2 mb-3">
                <div className="row g-3">
                    <div className="col-6 col-lg-3">
                        <div className="trust-pillar-card">
                            <div className="p-3 bg-success bg-opacity-10 text-success rounded-3 fs-4 d-flex align-items-center justify-content-center">
                                <ShieldCheck size={26} />
                            </div>
                            <div>
                                <h6 className="fw-bold mb-1 text-heading fs-6">100% Produk Kampus</h6>
                                <small className="text-muted d-block lh-sm" style={{ fontSize: '0.78rem' }}>Hasil panen & riset murni Tefa Polinela</small>
                            </div>
                        </div>
                    </div>
                    <div className="col-6 col-lg-3">
                        <div className="trust-pillar-card">
                            <div className="p-3 bg-primary bg-opacity-10 text-primary rounded-3 fs-4 d-flex align-items-center justify-content-center">
                                <Award size={26} />
                            </div>
                            <div>
                                <h6 className="fw-bold mb-1 text-heading fs-6">Transaksi Transparan</h6>
                                <small className="text-muted d-block lh-sm" style={{ fontSize: '0.78rem' }}>Transfer bank resmi & multi-store aman</small>
                            </div>
                        </div>
                    </div>
                    <div className="col-6 col-lg-3">
                        <div className="trust-pillar-card">
                            <div className="p-3 bg-warning bg-opacity-10 text-warning rounded-3 fs-4 d-flex align-items-center justify-content-center">
                                <Truck size={26} />
                            </div>
                            <div>
                                <h6 className="fw-bold mb-1 text-heading fs-6">Kurir & Ambil di Tefa</h6>
                                <small className="text-muted d-block lh-sm" style={{ fontSize: '0.78rem' }}>Bisa ambil langsung di kampus / dikirim</small>
                            </div>
                        </div>
                    </div>
                    <div className="col-6 col-lg-3">
                        <div className="trust-pillar-card">
                            <div className="p-3 bg-danger bg-opacity-10 text-danger rounded-3 fs-4 d-flex align-items-center justify-content-center">
                                <Sparkles size={26} />
                            </div>
                            <div>
                                <h6 className="fw-bold mb-1 text-heading fs-6">Standar Vokasi Mutu</h6>
                                <small className="text-muted d-block lh-sm" style={{ fontSize: '0.78rem' }}>Bebas bahan kimia pengawet sintetis</small>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Unit Toko Section */}
            <section id="units-section" className="py-5 section-units">
                <div className="container">
                    <div className="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <span className="text-success fw-bold text-uppercase small">Toko Mitra</span>
                            <h2 className="fw-bold text-heading mb-0">Unit Usaha & Perkebunan</h2>
                        </div>
                        <Link to="/katalog" className="btn btn-outline-success btn-sm rounded-pill px-3">
                            Semua Toko <ArrowRight size={14} />
                        </Link>
                    </div>

                    <div className="row g-4">
                        {units.map((unit) => (
                            <div key={unit.id} className="col-lg-3 col-md-6">
                                <Link to={`/katalog?unit=${unit.id}`} className="text-decoration-none">
                                    <div className="card h-100 border-0 shadow-sm rounded-4 p-4 unit-card text-center transition-all">
                                        <div className="mb-2 d-flex justify-content-center align-items-center" style={{ minHeight: '45px' }}>
                                            {renderUnitIcon(unit.id, unit.nama_unit)}
                                        </div>
                                        <h5 className="fw-bold text-heading mb-1">{unit.nama_unit}</h5>
                                        <p className="text-muted small mb-3 text-truncate-2" style={{ minHeight: '38px' }}>
                                            {unit.deskripsi || `Produk olahan dan hasil panen terbaik dari ${unit.nama_unit}`}
                                        </p>
                                        <div className="mt-auto">
                                            <span className="badge badge-unit-count rounded-pill px-3 py-2 fw-semibold">
                                                {unit.products_count || 0} Produk Tersedia
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Featured Products Section */}
            <section className="py-5 section-featured">
                <div className="container">
                    <div className="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <span className="text-success fw-bold text-uppercase small">Katalog Unggulan</span>
                            <h2 className="fw-bold text-heading mb-0">Produk Terbaru Polinela Agro</h2>
                        </div>
                        <Link to="/katalog" className="btn btn-success btn-sm rounded-pill px-3 d-flex align-items-center gap-1">
                            Lihat Semua <ArrowRight size={14} />
                        </Link>
                    </div>

                    {loading ? (
                        <div className="text-center py-5">
                            <div className="spinner-border text-success" role="status">
                                <span className="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    ) : (
                        <div className="row g-4">
                            {featuredProducts.map((product) => (
                                <div key={product.id} className="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                    <ProductCard product={product} />
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </section>
        </div>
    );
};

export default Home;
