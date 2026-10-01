import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import { formatRupiah, getImageUrl } from '../utils/format';
import { Trash2, Plus, Minus, Store, ShoppingBag, ArrowRight, ShieldCheck, ShoppingCart } from 'lucide-react';
import Swal from 'sweetalert2';

const Keranjang = () => {
    const { cartGroups, cartSummary, loading, updateQty, removeFromCart } = useCart();
    const navigate = useNavigate();

    const handleProceedToCheckout = () => {
        if (!cartGroups || cartGroups.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Silakan pilih produk terlebih dahulu sebelum melakukan checkout.',
            });
            return;
        }
        navigate('/checkout');
    };

    if (loading) {
        return (
            <div className="d-flex justify-content-center align-items-center py-5 min-vh-50">
                <div className="spinner-border text-success" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    const isEmpty = !cartGroups || cartGroups.length === 0;

    return (
        <div className="py-4 bg-light min-vh-100">
            <div className="container">
                {/* Title */}
                <div className="mb-4">
                    <h2 className="fw-bold text-dark mb-1">Keranjang Belanja</h2>
                    <p className="text-muted small mb-0">
                        Produk dari berbagai unit perkebunan akan diproses terpisah oleh masing-masing unit, namun <strong>cukup 1 kali pembayaran terpadu</strong>.
                    </p>
                </div>

                {isEmpty ? (
                    <div className="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                        <div className="mb-3 d-flex justify-content-center">
                            <ShoppingCart size={56} className="text-success opacity-75" />
                        </div>
                        <h4 className="fw-bold mb-2">Keranjang Belanja Masih Kosong</h4>
                        <p className="text-muted small mb-4">Ayo jelajahi produk kopi, kakao, bibit, dan hasil perkebunan Polinela lainnya!</p>
                        <Link to="/katalog" className="btn btn-success rounded-pill px-4 mx-auto d-inline-flex align-items-center gap-2">
                            <ShoppingBag size={18} /> Belanja Sekarang
                        </Link>
                    </div>
                ) : (
                    <div className="row g-4">
                        {/* Cart Groups by Unit Store */}
                        <div className="col-lg-8">
                            <div className="d-flex flex-column gap-3">
                                {cartGroups.map((group) => (
                                    <div key={group.unit_id} className="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                                        {/* Store Header */}
                                        <div className="bg-light p-3 border-bottom d-flex align-items-center justify-content-between">
                                            <div className="d-flex align-items-center gap-2">
                                                <div className="bg-success text-white rounded-circle p-1 d-flex align-items-center justify-content-center" style={{ width: '26px', height: '26px' }}>
                                                    <Store size={14} />
                                                </div>
                                                <h6 className="fw-bold mb-0 text-success">{group.unit_name}</h6>
                                            </div>
                                            <span className="badge bg-secondary bg-opacity-25 text-dark small">
                                                {group.items.length} Produk
                                            </span>
                                        </div>

                                        {/* Item Rows */}
                                        <div className="p-3">
                                            {group.items.map((item, index) => {
                                                const maxStock = item.product?.stocks?.reduce((acc, s) => acc + (s.jumlah_stok - s.stok_tersedia), 0) || 50;
                                                const itemImage = item.product?.gambar_utama 
                                                    ? getImageUrl(item.product.gambar_utama) 
                                                    : (item.product?.images?.[0]?.gambar ? getImageUrl(item.product.images[0].gambar) : 'https://placehold.co/100x100?text=Agro');

                                                return (
                                                    <div key={item.id} className={`row align-items-center py-3 ${index < group.items.length - 1 ? 'border-bottom' : ''}`}>
                                                        {/* Product Image & Title */}
                                                        <div className="col-md-5 col-12 mb-2 mb-md-0 d-flex align-items-center gap-3">
                                                            <img
                                                                src={itemImage}
                                                                alt={item.product?.nama_produk}
                                                                className="rounded-3 object-fit-cover border"
                                                                style={{ width: '65px', height: '65px', flexShrink: 0 }}
                                                            />
                                                            <div>
                                                                <Link to={`/produk/${item.product?.id}`} className="text-decoration-none text-dark fw-semibold small d-block mb-1 text-truncate-2">
                                                                    {item.product?.nama_produk}
                                                                </Link>
                                                                <span className="text-muted small">
                                                                    {formatRupiah(item.product?.harga)} / {item.product?.satuan || 'Pcs'}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        {/* Quantity Changer */}
                                                        <div className="col-md-3 col-6 d-flex align-items-center">
                                                            <div className="input-group input-group-sm" style={{ width: '100px' }}>
                                                                <button
                                                                    className="btn btn-outline-secondary"
                                                                    type="button"
                                                                    disabled={item.jumlah <= 1}
                                                                    onClick={() => updateQty(item.id, item.jumlah - 1)}
                                                                >
                                                                    <Minus size={12} />
                                                                </button>
                                                                <span className="form-control text-center fw-bold p-1" style={{ fontSize: '13px' }}>
                                                                    {item.jumlah}
                                                                </span>
                                                                <button
                                                                    className="btn btn-outline-secondary"
                                                                    type="button"
                                                                    disabled={item.jumlah >= maxStock}
                                                                    onClick={() => updateQty(item.id, item.jumlah + 1)}
                                                                >
                                                                    <Plus size={12} />
                                                                </button>
                                                            </div>
                                                        </div>

                                                        {/* Subtotal & Delete */}
                                                        <div className="col-md-4 col-6 d-flex align-items-center justify-content-between justify-content-md-end gap-3">
                                                            <div className="text-end">
                                                                <span className="fw-bold text-success" style={{ fontSize: '14px' }}>
                                                                    {formatRupiah(item.subtotal)}
                                                                </span>
                                                            </div>
                                                            <button
                                                                className="btn btn-sm btn-outline-danger border-0 p-1"
                                                                title="Hapus Produk"
                                                                onClick={() => {
                                                                    Swal.fire({
                                                                        title: 'Hapus Produk?',
                                                                        text: `Apakah Anda yakin ingin menghapus "${item.product?.nama_produk}" dari keranjang?`,
                                                                        icon: 'question',
                                                                        showCancelButton: true,
                                                                        confirmButtonColor: '#d33',
                                                                        confirmButtonText: 'Ya, Hapus',
                                                                        cancelButtonText: 'Batal',
                                                                    }).then((result) => {
                                                                        if (result.isConfirmed) {
                                                                            removeFromCart(item.id);
                                                                        }
                                                                    });
                                                                }}
                                                            >
                                                                <Trash2 size={16} />
                                                            </button>
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>

                                        {/* Subtotal per Store */}
                                        <div className="bg-light bg-opacity-50 p-2 px-3 border-top d-flex justify-content-between align-items-center text-muted small">
                                            <span>Subtotal {group.unit_name}:</span>
                                            <strong className="text-dark">{formatRupiah(group.unit_subtotal)}</strong>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Order Summary Checkout Card */}
                        <div className="col-lg-4">
                            <div className="card border-0 shadow-sm rounded-4 p-4 sticky-top bg-white" style={{ top: '110px', zIndex: 10 }}>
                                <h5 className="fw-bold mb-3">Ringkasan Belanja</h5>

                                <div className="d-flex justify-content-between mb-2 small text-muted">
                                    <span>Total Item ({cartSummary.total_items} produk):</span>
                                    <span className="fw-semibold text-dark">{formatRupiah(cartSummary.subtotal)}</span>
                                </div>

                                <div className="d-flex justify-content-between mb-2 small text-muted">
                                    <span>Total Toko/Unit:</span>
                                    <span className="badge bg-success">{cartSummary.total_units} Unit Toko</span>
                                </div>

                                <div className="d-flex justify-content-between mb-3 small text-muted">
                                    <span>Total Berat Estimasi:</span>
                                    <span>{(cartSummary.total_berat_gram / 1000).toFixed(2)} Kg</span>
                                </div>

                                <hr />

                                <div className="d-flex justify-content-between mb-4">
                                    <div>
                                        <div className="text-muted small">Total Pembayaran</div>
                                        <small className="text-success" style={{ fontSize: '11px' }}>1x Transfer Saja</small>
                                    </div>
                                    <h4 className="fw-bold text-success mb-0">{formatRupiah(cartSummary.subtotal)}</h4>
                                </div>

                                <button
                                    onClick={handleProceedToCheckout}
                                    className="btn btn-success btn-lg rounded-pill w-100 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm mb-3"
                                >
                                    <span>Lanjut ke Checkout</span>
                                    <ArrowRight size={18} />
                                </button>

                                <div className="p-3 bg-light rounded-3 small text-muted border">
                                    <div className="d-flex align-items-start gap-2">
                                        <ShieldCheck size={18} className="text-success flex-shrink-0 mt-1" />
                                        <p className="mb-0" style={{ fontSize: '11px' }}>
                                            <strong>Sistem Multi-Toko Polinela:</strong> Pesanan Anda akan otomatis dibagi ke masing-masing unit toko, tetapi Anda hanya perlu melakukan <strong>1 kali transfer pembayaran</strong>.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default Keranjang;
