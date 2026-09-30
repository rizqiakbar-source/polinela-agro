import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import client from '../api/client';
import { formatRupiah, getImageUrl } from '../utils/format';
import { useCart } from '../context/CartContext';
import ProductCard from '../components/ProductCard';
import { ShoppingCart, Star, Store, ShieldCheck, Truck, Plus, Minus, ArrowLeft } from 'lucide-react';
import Swal from 'sweetalert2';

const DetailProduk = () => {
    const { id } = useParams();
    const { addToCart } = useCart();

    const [product, setProduct] = useState(null);
    const [related, setRelated] = useState([]);
    const [selectedImage, setSelectedImage] = useState(null);
    const [quantity, setQuantity] = useState(1);
    const [notes, setNotes] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchProduct = async () => {
            setLoading(true);
            try {
                const res = await client.get(`/products/${id}`);
                if (res.data.status === 'success' || res.data.success) {
                    const prod = res.data.data?.product || res.data.data;
                    setProduct(prod);
                    setRelated(res.data.data?.related || []);
                    setSelectedImage(prod?.gambar_utama || (prod?.images?.[0]?.gambar) || null);
                    setQuantity(1);
                }
            } catch (err) {
                console.error('Failed to load product detail:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Produk Tidak Ditemukan',
                    text: 'Produk yang Anda cari tidak tersedia atau telah dihapus.',
                });
            } finally {
                setLoading(false);
            }
        };

        fetchProduct();
        window.scrollTo(0, 0);
    }, [id]);

    if (loading) {
        return (
            <div className="d-flex justify-content-center align-items-center py-5 min-vh-50">
                <div className="spinner-border text-success" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    if (!product) {
        return (
            <div className="container py-5 text-center">
                <h3>Produk tidak ditemukan</h3>
                <Link to="/katalog" className="btn btn-success mt-3">Kembali ke Katalog</Link>
            </div>
        );
    }

    const totalStock = product.total_stok ?? 10;
    const isOutOfStock = totalStock <= 0;

    const handleAddToCart = async () => {
        if (quantity > totalStock) {
            Swal.fire({
                icon: 'warning',
                title: 'Stok Terbatas',
                text: `Stok hanya tersedia ${totalStock} unit.`,
            });
            return;
        }
        await addToCart(product.id, quantity, notes);
    };

    return (
        <div className="py-4 bg-light min-vh-100">
            <div className="container">
                {/* Breadcrumbs */}
                <div className="mb-3">
                    <Link to="/katalog" className="btn btn-link text-muted text-decoration-none p-0 d-inline-flex align-items-center gap-1 small">
                        <ArrowLeft size={16} /> Kembali ke Katalog
                    </Link>
                </div>

                {/* Main Product Card */}
                <div className="card border-0 shadow-sm rounded-4 overflow-hidden p-4 mb-4 bg-white">
                    <div className="row g-4">
                        {/* Gallery Column */}
                        <div className="col-lg-5">
                            <div className="rounded-4 overflow-hidden mb-3 bg-light border" style={{ height: '380px' }}>
                                <img
                                    src={getImageUrl(selectedImage)}
                                    alt={product.nama_produk}
                                    className="w-100 h-100 object-fit-cover"
                                    onError={(e) => {
                                        e.target.onerror = null;
                                        e.target.src = 'https://placehold.co/500x400?text=Polinela+Agro';
                                    }}
                                />
                            </div>

                            {/* Thumbnail list */}
                            {product.images && product.images.length > 1 && (
                                <div className="d-flex gap-2 overflow-auto pb-2">
                                    {product.images.map((img) => (
                                        <button
                                            key={img.id}
                                            type="button"
                                            className={`btn p-0 border rounded-3 overflow-hidden ${selectedImage === img.gambar ? 'border-success border-2' : ''}`}
                                            style={{ width: '65px', height: '65px', flexShrink: 0 }}
                                            onClick={() => setSelectedImage(img.gambar)}
                                        >
                                            <img src={getImageUrl(img.gambar)} alt="thumb" className="w-100 h-100 object-fit-cover" />
                                        </button>
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Details Column */}
                        <div className="col-lg-7 d-flex flex-column">
                            {/* Unit Toko Tag */}
                            {product.unit && (
                                <div className="d-flex align-items-center gap-2 mb-2">
                                    <span className="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                                        <Store size={14} /> Unit Toko: {product.unit.nama_unit}
                                    </span>
                                    {product.category && (
                                        <span className="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-1">
                                            {product.category.nama_kategori}
                                        </span>
                                    )}
                                </div>
                            )}

                            <h3 className="fw-bold text-dark mb-2">{product.nama_produk}</h3>

                            {/* Rating and Reviews */}
                            <div className="d-flex align-items-center gap-2 mb-3">
                                <div className="d-flex align-items-center text-warning">
                                    <Star size={16} fill="#ffc107" />
                                    <span className="ms-1 fw-bold text-dark small">{product.rating_avg ? Number(product.rating_avg).toFixed(1) : '5.0'}</span>
                                </div>
                                <span className="text-muted small">•</span>
                                <span className="text-muted small">{product.reviews?.length || 0} Penilaian</span>
                                <span className="text-muted small">•</span>
                                <span className={`badge ${totalStock > 0 ? 'bg-success' : 'bg-danger'}`}>
                                    {totalStock > 0 ? `Tersedia: ${totalStock} ${product.satuan || 'Pcs'}` : 'Stok Habis'}
                                </span>
                            </div>

                            {/* Price */}
                            <div className="p-3 rounded-4 bg-light mb-4">
                                <span className="text-muted small d-block">Harga Produk</span>
                                <h2 className="fw-bold text-success mb-0">{formatRupiah(product.harga)}</h2>
                            </div>

                            {/* Quantity Selector & Notes */}
                            <div className="mb-4">
                                <label className="form-label small fw-semibold">Jumlah Pembelian</label>
                                <div className="d-flex align-items-center gap-3">
                                    <div className="input-group" style={{ width: '130px' }}>
                                        <button
                                            className="btn btn-outline-secondary"
                                            type="button"
                                            disabled={quantity <= 1}
                                            onClick={() => setQuantity((q) => Math.max(1, q - 1))}
                                        >
                                            <Minus size={14} />
                                        </button>
                                        <input
                                            type="number"
                                            className="form-control text-center fw-bold"
                                            value={quantity}
                                            min="1"
                                            max={totalStock}
                                            onChange={(e) => setQuantity(Math.max(1, Math.min(totalStock, parseInt(e.target.value) || 1)))}
                                        />
                                        <button
                                            className="btn btn-outline-secondary"
                                            type="button"
                                            disabled={quantity >= totalStock}
                                            onClick={() => setQuantity((q) => Math.min(totalStock, q + 1))}
                                        >
                                            <Plus size={14} />
                                        </button>
                                    </div>
                                    <span className="small text-muted">
                                        Subtotal: <strong className="text-success">{formatRupiah(product.harga * quantity)}</strong>
                                    </span>
                                </div>
                            </div>

                            {/* Cart CTA Button */}
                            <div className="d-flex gap-3 mb-4">
                                <button
                                    className="btn btn-success btn-lg rounded-pill px-4 flex-grow-1 d-flex align-items-center justify-content-center gap-2 shadow-sm"
                                    disabled={isOutOfStock}
                                    onClick={handleAddToCart}
                                >
                                    <ShoppingCart size={20} />
                                    <span>{isOutOfStock ? 'Stok Habis' : 'Tambah ke Keranjang'}</span>
                                </button>
                            </div>

                            {/* Guaranteed Info */}
                            <div className="mt-auto pt-3 border-top d-flex gap-4 small text-muted">
                                <div className="d-flex align-items-center gap-1">
                                    <ShieldCheck size={16} className="text-success" />
                                    <span>100% Produk Original Polinela</span>
                                </div>
                                <div className="d-flex align-items-center gap-1">
                                    <Truck size={16} className="text-primary" />
                                    <span>Pengiriman Langsung dari Toko</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Product Description & Specifications */}
                <div className="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                    <h5 className="fw-bold mb-3">Deskripsi & Spesifikasi Produk</h5>
                    <div className="row g-3 mb-3">
                        <div className="col-md-3 col-6">
                            <span className="text-muted small d-block">Berat Satuan:</span>
                            <span className="fw-semibold">{product.berat_gram || 500} gram</span>
                        </div>
                        <div className="col-md-3 col-6">
                            <span className="text-muted small d-block">Satuan Kemasan:</span>
                            <span className="fw-semibold">{product.satuan || 'Pcs'}</span>
                        </div>
                        <div className="col-md-3 col-6">
                            <span className="text-muted small d-block">Unit Toko:</span>
                            <span className="fw-semibold text-success">{product.unit?.nama_unit || '-'}</span>
                        </div>
                        <div className="col-md-3 col-6">
                            <span className="text-muted small d-block">Kategori:</span>
                            <span className="fw-semibold">{product.category?.nama_kategori || '-'}</span>
                        </div>
                    </div>
                    <hr />
                    <div className="text-secondary" style={{ whiteSpace: 'pre-line', lineHeight: '1.7' }}>
                        {product.deskripsi || 'Tidak ada deskripsi rinci untuk produk ini.'}
                    </div>
                </div>

                {/* Customer Reviews & Rating Section */}
                <div className="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                    <div className="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <h5 className="fw-bold mb-1 d-flex align-items-center gap-2">
                                <Star size={20} className="text-warning" fill="#ffc107" />
                                Ulasan & Penilaian Pelanggan ({product.reviews?.length || 0})
                            </h5>
                            <small className="text-muted">Testimoni dan ulasan otentik pembeli produk perkebunan Polinela</small>
                        </div>
                        <div className="d-flex align-items-center gap-2 p-2 px-3 rounded-pill bg-light border">
                            <div className="display-6 fw-extrabold text-success mb-0 lh-1">
                                {product.rating_avg ? Number(product.rating_avg).toFixed(1) : '5.0'}
                            </div>
                            <div className="small">
                                <div className="d-flex text-warning">
                                    {[1, 2, 3, 4, 5].map((s) => (
                                        <Star key={s} size={14} fill="#ffc107" />
                                    ))}
                                </div>
                                <span className="text-muted" style={{ fontSize: '11px' }}>dari 5 Bintang</span>
                            </div>
                        </div>
                    </div>

                    {/* Reviews List */}
                    {!product.reviews || product.reviews.length === 0 ? (
                        <div className="text-center py-4 text-muted bg-light rounded-4">
                            <Star size={32} className="text-warning opacity-50 mb-2" />
                            <p className="small mb-0">Belum ada ulasan untuk produk ini. Jadilah pembeli pertama yang memberikan penilaian!</p>
                        </div>
                    ) : (
                        <div className="d-flex flex-column gap-3">
                            {product.reviews.map((rev) => (
                                <div key={rev.id} className="p-3 border rounded-3 bg-light">
                                    <div className="d-flex justify-content-between align-items-center mb-2">
                                        <div className="d-flex align-items-center gap-2">
                                            <div className="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style={{ width: '32px', height: '32px', fontSize: '13px' }}>
                                                {(rev.user?.nama_lengkap || rev.user?.nama || 'U')[0].toUpperCase()}
                                            </div>
                                            <div>
                                                <div className="fw-semibold small text-dark">{rev.user?.nama_lengkap || rev.user?.nama || 'Konsumen'}</div>
                                                <small className="text-muted" style={{ fontSize: '11px' }}>{new Date(rev.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })}</small>
                                            </div>
                                        </div>
                                        <div className="d-flex text-warning">
                                            {[1, 2, 3, 4, 5].map((s) => (
                                                <Star key={s} size={14} fill={s <= rev.rating ? '#ffc107' : 'none'} color="#ffc107" />
                                            ))}
                                        </div>
                                    </div>
                                    <p className="small text-secondary mb-0">{rev.ulasan}</p>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Related Products */}
                {related.length > 0 && (
                    <div className="mt-5">
                        <h5 className="fw-bold mb-3">Produk Terkait dari Unit Toko yang Sama</h5>
                        <div className="row g-4">
                            {related.map((rel) => (
                                <div key={rel.id} className="col-lg-3 col-md-6 col-sm-6">
                                    <ProductCard product={rel} />
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default DetailProduk;

