import React from 'react';
import { Link } from 'react-router-dom';
import { ShoppingCart, Star } from 'lucide-react';
import { formatRupiah, getImageUrl } from '../utils/format';
import { useCart } from '../context/CartContext';

const ProductCard = ({ product }) => {
    const { addToCart } = useCart();

    const imageSrc = product.gambar_utama 
        ? getImageUrl(product.gambar_utama) 
        : (product.images && product.images.length > 0 ? getImageUrl(product.images[0].gambar) : 'https://placehold.co/400x300?text=Polinela+Agro');

    const totalStock = product.total_stok ?? (product.stocks?.reduce((acc, s) => acc + (s.jumlah_stok - s.stok_tersedia), 0) || 10);
    const isOutOfStock = totalStock <= 0;

    return (
        <div className="card h-100 border-0 shadow-sm rounded-4 overflow-hidden product-card transition-all" style={{ transition: 'transform 0.2s, box-shadow 0.2s' }}>
            {/* Image Header with Store Badge */}
            <div className="position-relative overflow-hidden product-card-img-wrapper" style={{ height: '200px' }}>
                <Link to={`/produk/${product.id || product.slug}`}>
                    <img
                        src={imageSrc}
                        alt={product.nama_produk}
                        className="w-100 h-100 object-fit-cover product-img"
                        onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = 'https://placehold.co/400x300?text=Polinela+Agro';
                        }}
                    />
                </Link>

                {/* Unit Toko Badge */}
                {product.unit && (
                    <span 
                        className="position-absolute top-0 start-0 m-2 badge bg-success shadow-sm rounded-pill px-2 py-1 small"
                        style={{ fontSize: '11px', backdropFilter: 'blur(6px)' }}
                    >
                        🏪 {product.unit.nama_unit}
                    </span>
                )}

                {/* Category Badge */}
                {product.category && (
                    <span className="position-absolute top-0 end-0 m-2 badge badge-category shadow-sm rounded-pill px-2 py-1 small" style={{ fontSize: '10px' }}>
                        {product.category.nama_kategori}
                    </span>
                )}
            </div>

            {/* Card Body */}
            <div className="card-body p-3 d-flex flex-column">
                <Link to={`/produk/${product.id || product.slug}`} className="text-decoration-none product-title-link mb-1">
                    <h6 className="card-title fw-semibold text-truncate-2 mb-1" style={{ fontSize: '14px', minHeight: '40px', lineHeight: '1.4' }}>
                        {product.nama_produk}
                    </h6>
                </Link>

                <div className="d-flex align-items-center gap-1 mb-2">
                    <Star size={14} className="text-warning fill-warning" style={{ fill: '#ffc107' }} />
                    <span className="small fw-semibold text-muted">
                        {product.rating_avg ? Number(product.rating_avg).toFixed(1) : '5.0'}
                    </span>
                    <span className="small text-muted">({product.reviews_count || 0} ulasan)</span>
                </div>

                <div className="mt-auto pt-2 border-top d-flex align-items-center justify-content-between">
                    <div>
                        <div className="text-muted small" style={{ fontSize: '11px' }}>Harga</div>
                        <div className="fw-bold text-success fs-6">
                            {formatRupiah(product.harga)}
                        </div>
                    </div>

                    <button
                        className="btn btn-sm btn-outline-success rounded-circle p-2 d-flex align-items-center justify-content-center"
                        style={{ width: '36px', height: '36px' }}
                        title="Tambah ke Keranjang"
                        disabled={isOutOfStock}
                        onClick={() => addToCart(product.id, 1)}
                    >
                        <ShoppingCart size={16} />
                    </button>
                </div>
            </div>
        </div>
    );
};

export default ProductCard;
