import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import client from '../api/client';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';
import { formatRupiah, getImageUrl } from '../utils/format';
import { Store, CreditCard, Truck, Tag, ShieldCheck, ArrowLeft, CheckCircle2, MapPin, Compass, Zap, Package, ShoppingBag, ShoppingCart, Sparkles } from 'lucide-react';
import Swal from 'sweetalert2';
import LocationPickerModal from '../components/LocationPickerModal';
import { triggerMidtransPayment } from '../utils/midtrans';

const Checkout = () => {
    const { cartGroups = [], cartSummary = {}, loading = false, fetchCart } = useCart();
    const { user } = useAuth();
    const navigate = useNavigate();

    // Form states - auto-populated from user profile
    const [namaPenerima, setNamaPenerima] = useState(user?.nama || user?.nama_lengkap || user?.username || '');
    const [noHp, setNoHp] = useState(user?.no_hp || '');
    const [alamat, setAlamat] = useState(user?.alamat || '');
    const [kota, setKota] = useState('Bandar Lampung');
    const [kodePos, setKodePos] = useState('35144');
    const [kurir, setKurir] = useState('kurir_polinela');
    const [metodePembayaran, setMetodePembayaran] = useState('midtrans');
    const [bankPilihan, setBankPilihan] = useState('Bank Mandiri');

    // Auto sync when user object loads or updates
    useEffect(() => {
        if (user) {
            if (!namaPenerima) setNamaPenerima(user.nama || user.nama_lengkap || user.username || '');
            if (!noHp) setNoHp(user.no_hp || '');
            if (!alamat) setAlamat(user.alamat || '');
        }
    }, [user]);
    
    // Map Location states
    const [showMapModal, setShowMapModal] = useState(false);
    const [pinnedLocation, setPinnedLocation] = useState(null);
    
    // Voucher states
    const [voucherCode, setVoucherCode] = useState('');
    const [appliedVoucher, setAppliedVoucher] = useState(null);
    const [voucherDiscount, setVoucherDiscount] = useState(0);
    const [validatingVoucher, setValidatingVoucher] = useState(false);

    const [submitting, setSubmitting] = useState(false);

    // Shipping cost calculation (e.g. standard flat per unit)
    const ongkirPerUnit = kurir === 'ambil_sendiri' ? 0 : 10000;
    const totalOngkir = (cartGroups?.length || 1) * ongkirPerUnit;
    const subtotal = cartSummary?.subtotal || 0;
    const grandTotal = Math.max(0, subtotal + totalOngkir - voucherDiscount);

    const handleApplyVoucher = async (e) => {
        e.preventDefault();
        if (!voucherCode.trim()) return;

        setValidatingVoucher(true);
        try {
            const res = await client.post('/checkout/validate-voucher', {
                kode: voucherCode.trim(),
                subtotal: subtotal,
            });

            if (res.data.status === 'success') {
                const { voucher, diskon } = res.data.data;
                setAppliedVoucher(voucher);
                setVoucherDiscount(diskon);
                Swal.fire({
                    icon: 'success',
                    title: 'Voucher Diterapkan!',
                    text: `Anda mendapatkan potongan diskon ${formatRupiah(diskon)}.`,
                    timer: 2000,
                    showConfirmButton: false,
                });
            }
        } catch (error) {
            const msg = error.response?.data?.message || 'Kode voucher tidak valid atau sudah kedaluwarsa.';
            Swal.fire({
                icon: 'error',
                title: 'Voucher Tidak Valid',
                text: msg,
            });
            setAppliedVoucher(null);
            setVoucherDiscount(0);
        } finally {
            setValidatingVoucher(false);
        }
    };

    const handleProcessOrder = async (e) => {
        e.preventDefault();

        if (!namaPenerima.trim() || !noHp.trim() || !alamat.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Lengkapi Data Pengiriman',
                text: 'Mohon lengkapi nama penerima, nomor HP/WhatsApp, dan alamat pengiriman Anda.',
            });
            return;
        }

        setSubmitting(true);
        try {
            const payload = {
                nama_penerima: namaPenerima,
                no_hp: noHp,
                alamat_lengkap: alamat,
                kota: kota,
                kode_pos: kodePos,
                ekspedisi: kurir,
                metode_pembayaran: metodePembayaran,
                bank: metodePembayaran === 'transfer_bank' ? bankPilihan : null,
                voucher_id: appliedVoucher ? appliedVoucher.id : null,
                diskon: voucherDiscount,
                catatan_pembeli: `Pesanan Multi-Store Polinela Agro (${cartGroups.length} unit)`,
            };

            const res = await client.post('/checkout', payload);

            if (res.data.status === 'success' || res.data.success) {
                fetchCart(); // refresh cart in background
                const responseData = res.data.data || res.data;
                const { sharedTrxNumber, orders, whatsapp_url } = responseData;
                const snapToken = res.data.snap_token || responseData.snap_token;
                const snapUrl = res.data.snap_url || responseData.snap_url;
                const clientKey = res.data.client_key || responseData.client_key;
                const firstOrderId = responseData.first_order_id || (orders && orders.length > 0 ? orders[0].id : '');

                if (metodePembayaran === 'midtrans' && snapToken && firstOrderId) {
                    // Buka popup Midtrans SECARA INSTAN di halaman checkout (0 detik delay)
                    await triggerMidtransPayment(snapToken, {
                        snapUrl,
                        clientKey,
                        onSuccess: async (result) => {
                            try {
                                await client.post(`/payment/finish/${firstOrderId}`, { payment_type: result.payment_type || 'midtrans' });
                            } catch (e) {}
                            Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Berhasil!',
                                text: 'Pesanan Anda otomatis diverifikasi LUNAS & notifikasi telah dikirim.',
                                timer: 2000,
                                showConfirmButton: false,
                            });
                            navigate(`/pesanan/${firstOrderId}`);
                        },
                        onPending: () => {
                            navigate(`/pesanan/${firstOrderId}`);
                        },
                        onError: () => {
                            navigate(`/pesanan/${firstOrderId}`);
                        },
                        onClose: () => {
                            navigate(`/pesanan/${firstOrderId}`);
                        }
                    });
                    return;
                }

                if (firstOrderId) {
                    navigate(`/pesanan/${firstOrderId}`);
                } else {
                    navigate('/pesanan');
                }
            }
        } catch (error) {
            const msg = error.response?.data?.message || 'Gagal memproses pesanan. Silakan coba lagi.';
            Swal.fire({
                icon: 'error',
                title: 'Checkout Gagal',
                text: msg,
            });
        } finally {
            setSubmitting(false);
        }
    };

    const handleSelectLocation = (data) => {
        setAlamat(data.alamat);
        setKota(data.kota);
        if (data.kodePos) setKodePos(data.kodePos);
        setPinnedLocation(data);
        Swal.fire({
            icon: 'success',
            title: 'Titik Lokasi Ditentukan!',
            text: `Alamat berhasil diatur (± ${data.distanceKm} km dari Kampus Polinela).`,
            timer: 2000,
            showConfirmButton: false,
        });
    };

    return (
        <div className="py-4 bg-light min-vh-100">
            <div className="container">
                {/* Header */}
                <div className="mb-4">
                    <Link to="/keranjang" className="btn btn-link text-muted text-decoration-none p-0 d-inline-flex align-items-center gap-1 small mb-2">
                        <ArrowLeft size={16} /> Kembali ke Keranjang
                    </Link>
                </div>

                {loading ? (
                    <div className="d-flex justify-content-center align-items-center py-5 min-vh-50">
                        <div className="spinner-border text-success" role="status">
                            <span className="visually-hidden">Memuat keranjang...</span>
                        </div>
                    </div>
                ) : cartGroups.length === 0 ? (
                    <div className="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
                        <div className="mb-3 d-flex justify-content-center">
                            <ShoppingBag size={56} className="text-success opacity-75" />
                        </div>
                        <h4 className="fw-bold mb-2">Keranjang Belanja Kosong</h4>
                        <p className="text-muted small mb-4">
                            Pesanan Anda sebelumnya telah berhasil dibuat, atau belum ada produk di keranjang belanja.
                        </p>
                        <div className="d-flex justify-content-center gap-3 flex-wrap">
                            <Link to="/pesanan" className="btn btn-success rounded-pill px-4 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                                <Package size={16} /> Lihat Pesanan Saya
                            </Link>
                            <Link to="/katalog" className="btn btn-outline-secondary rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2">
                                <ShoppingCart size={16} /> Belanja Produk Baru
                            </Link>
                        </div>
                    </div>
                ) : (
                    <form onSubmit={handleProcessOrder}>
                    <div className="row g-4">
                        {/* Left Column: Delivery & Store Items */}
                        <div className="col-lg-8">
                            {/* Alamat Pengiriman */}
                            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                                <div className="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                    <h5 className="fw-bold mb-0 d-flex align-items-center gap-2">
                                        <Truck size={20} className="text-success" /> Alamat Pengiriman
                                    </h5>
                                    <button
                                        type="button"
                                        onClick={() => setShowMapModal(true)}
                                        className="btn btn-outline-success btn-sm rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1 shadow-sm"
                                    >
                                        <Compass size={15} />
                                        <span>Pilih / Pin di Peta (GPS)</span>
                                    </button>
                                </div>

                                <div className="row g-3">
                                    <div className="col-md-6">
                                        <label className="form-label small fw-semibold">Nama Penerima</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            required
                                            value={namaPenerima}
                                            onChange={(e) => setNamaPenerima(e.target.value)}
                                        />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-semibold">Nomor WhatsApp / HP</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            required
                                            value={noHp}
                                            onChange={(e) => setNoHp(e.target.value)}
                                        />
                                    </div>
                                    <div className="col-12">
                                        <div className="d-flex justify-content-between align-items-center mb-1">
                                            <label className="form-label small fw-semibold mb-0">Alamat Lengkap</label>
                                            <button
                                                type="button"
                                                onClick={() => setShowMapModal(true)}
                                                className="btn btn-link text-success p-0 small text-decoration-none fw-semibold d-inline-flex align-items-center gap-1"
                                            >
                                                <MapPin size={13} /> Cari di Peta
                                            </button>
                                        </div>
                                        <textarea
                                            className="form-control"
                                            rows="2"
                                            required
                                            placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
                                            value={alamat}
                                            onChange={(e) => setAlamat(e.target.value)}
                                        ></textarea>
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-semibold">Kota / Kabupaten</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={kota}
                                            onChange={(e) => setKota(e.target.value)}
                                        />
                                    </div>
                                    <div className="col-md-6">
                                        <label className="form-label small fw-semibold">Kode Pos</label>
                                        <input
                                            type="text"
                                            className="form-control"
                                            value={kodePos}
                                            onChange={(e) => setKodePos(e.target.value)}
                                        />
                                    </div>
                                </div>

                                {/* Pinpoint GPS Verified Banner */}
                                {pinnedLocation && (
                                    <div className="alert alert-success border-success border-opacity-25 bg-success bg-opacity-10 rounded-3 d-flex align-items-center justify-content-between p-2 px-3 mt-3 mb-0 small">
                                        <div className="d-flex align-items-center gap-2">
                                            <MapPin size={16} className="text-success flex-shrink-0" />
                                            <span>
                                                <strong>Titik Presisi Terpasang:</strong> ± {pinnedLocation.distanceKm} km dari Kampus Polinela
                                            </span>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => setShowMapModal(true)}
                                            className="btn btn-link text-success p-0 fw-bold text-decoration-none small"
                                        >
                                            Ubah Titik
                                        </button>
                                    </div>
                                )}
                            </div>

                            {/* Opsi Pengiriman & Kurir */}
                            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                                <h5 className="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <Truck size={20} className="text-success" /> Metode Pengiriman
                                </h5>
                                <div className="d-flex flex-column gap-2">
                                    <div className={`p-3 border rounded-3 d-flex align-items-center justify-content-between cursor-pointer ${kurir === 'kurir_polinela' ? 'border-success bg-light' : ''}`} onClick={() => setKurir('kurir_polinela')}>
                                        <div className="d-flex align-items-center gap-3">
                                            <input type="radio" name="kurir" checked={kurir === 'kurir_polinela'} onChange={() => setKurir('kurir_polinela')} />
                                            <div>
                                                <div className="fw-bold text-dark">Kurir Polinela Express (Lokal Bandar Lampung)</div>
                                                <small className="text-muted">Estimasi tiba hari yang sama atau 1 hari kerja</small>
                                            </div>
                                        </div>
                                        <span className="fw-bold text-success">{formatRupiah(10000 * (cartGroups?.length || 1))}</span>
                                    </div>

                                    <div className={`p-3 border rounded-3 d-flex align-items-center justify-content-between cursor-pointer ${kurir === 'jne' ? 'border-success bg-light' : ''}`} onClick={() => setKurir('jne')}>
                                        <div className="d-flex align-items-center gap-3">
                                            <input type="radio" name="kurir" checked={kurir === 'jne'} onChange={() => setKurir('jne')} />
                                            <div>
                                                <div className="fw-bold text-dark">JNE Regular / J&T Express</div>
                                                <small className="text-muted">Estimasi tiba 2-3 hari kerja (Luar Kota)</small>
                                            </div>
                                        </div>
                                        <span className="fw-bold text-success">{formatRupiah(15000 * (cartGroups?.length || 1))}</span>
                                    </div>

                                    <div className={`p-3 border rounded-3 d-flex align-items-center justify-content-between cursor-pointer ${kurir === 'ambil_sendiri' ? 'border-success bg-light' : ''}`} onClick={() => setKurir('ambil_sendiri')}>
                                        <div className="d-flex align-items-center gap-3">
                                            <input type="radio" name="kurir" checked={kurir === 'ambil_sendiri'} onChange={() => setKurir('ambil_sendiri')} />
                                            <div>
                                                <div className="fw-bold text-dark">Ambil Langsung di Kebun Praktikum Polinela</div>
                                                <small className="text-muted">Gratis tanpa ongkos kirim</small>
                                            </div>
                                        </div>
                                        <span className="badge bg-success">Gratis</span>
                                    </div>
                                </div>
                            </div>

                            {/* Multi-Store Items Breakdown */}
                            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                                <h5 className="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <Store size={20} className="text-success" /> Rincian Pesanan per Unit Toko
                                </h5>

                                <div className="d-flex flex-column gap-3">
                                    {cartGroups.map((group) => (
                                        <div key={group.unit_id} className="border rounded-3 p-3 bg-light bg-opacity-50">
                                            <div className="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
                                                <div className="fw-bold text-success d-flex align-items-center gap-2">
                                                    <Store size={15} />
                                                    <span>{group.unit_name}</span>
                                                </div>
                                                <span className="badge bg-secondary bg-opacity-25 text-dark small">Sub-Order Terpisah</span>
                                            </div>

                                            {group.items.map((item) => (
                                                <div key={item.id} className="d-flex justify-content-between align-items-center py-1 small">
                                                    <span className="text-truncate" style={{ maxWidth: '350px' }}>
                                                        {item.product?.nama_produk} <span className="text-muted">x{item.jumlah}</span>
                                                    </span>
                                                    <span className="fw-semibold text-dark">{formatRupiah(item.subtotal)}</span>
                                                </div>
                                            ))}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Right Column: Voucher & Payment Selection */}
                        <div className="col-lg-4">
                            {/* Voucher Card */}
                            <div className="card border-0 shadow-sm rounded-4 p-4 mb-3 bg-white">
                                <h6 className="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <Tag size={18} className="text-success" /> Gunakan Voucher Promo
                                </h6>
                                <div className="input-group input-group-sm mb-2">
                                    <input
                                        type="text"
                                        className="form-control text-uppercase fw-bold"
                                        placeholder="KODE VOUCHER"
                                        value={voucherCode}
                                        onChange={(e) => setVoucherCode(e.target.value)}
                                    />
                                    <button
                                        type="button"
                                        className="btn btn-outline-success"
                                        disabled={validatingVoucher || !voucherCode.trim()}
                                        onClick={handleApplyVoucher}
                                    >
                                        {validatingVoucher ? 'Cek...' : 'Terapkan'}
                                    </button>
                                </div>
                                {appliedVoucher && (
                                    <div className="p-2 bg-success bg-opacity-10 text-success rounded-3 small d-flex align-items-center justify-content-between">
                                        <span className="d-inline-flex align-items-center gap-1"><Sparkles size={14} /> Voucher: <strong>{appliedVoucher.kode_voucher}</strong></span>
                                        <span className="fw-bold">-{formatRupiah(voucherDiscount)}</span>
                                    </div>
                                )}
                            </div>

                            {/* Metode Pembayaran Card */}
                            <div className="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                                <h6 className="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <CreditCard size={18} className="text-success" /> Metode Pembayaran (1x Bayar)
                                </h6>

                                <div className="p-3 border border-success bg-success bg-opacity-10 rounded-3 mb-3">
                                    <div className="d-flex align-items-center justify-content-between mb-1">
                                        <span className="fw-bold text-success small d-flex align-items-center gap-1.5">
                                            <Zap size={16} className="text-warning fill-warning" /> Pembayaran Otomatis (Midtrans)
                                        </span>
                                        <span className="badge bg-success" style={{ fontSize: '10px' }}>Verifikasi Realtime</span>
                                    </div>
                                    <p className="text-muted small mb-2" style={{ fontSize: '11.5px' }}>
                                        Diverifikasi otomatis secara instan oleh sistem. Tersedia Virtual Account (BCA, Mandiri, BRI, BNI), QRIS, GoPay & ShopeePay.
                                    </p>
                                    <div className="d-flex flex-wrap gap-1.5">
                                        <span className="badge bg-white text-dark border" style={{ fontSize: '10px' }}>BCA VA</span>
                                        <span className="badge bg-white text-dark border" style={{ fontSize: '10px' }}>Mandiri Bill</span>
                                        <span className="badge bg-white text-dark border" style={{ fontSize: '10px' }}>BRI / BNI VA</span>
                                        <span className="badge bg-white text-dark border" style={{ fontSize: '10px' }}>QRIS</span>
                                        <span className="badge bg-white text-dark border" style={{ fontSize: '10px' }}>GoPay</span>
                                    </div>
                                </div>

                                <hr />

                                {/* Price Summary */}
                                <div className="d-flex justify-content-between mb-2 small text-muted">
                                    <span>Subtotal Produk:</span>
                                    <span className="fw-semibold text-dark">{formatRupiah(subtotal)}</span>
                                </div>
                                <div className="d-flex justify-content-between mb-2 small text-muted">
                                    <span>Total Ongkir ({cartGroups.length} unit):</span>
                                    <span className="fw-semibold text-dark">{formatRupiah(totalOngkir)}</span>
                                </div>
                                {voucherDiscount > 0 && (
                                    <div className="d-flex justify-content-between mb-2 small text-success fw-semibold">
                                        <span>Diskon Voucher:</span>
                                        <span>-{formatRupiah(voucherDiscount)}</span>
                                    </div>
                                )}

                                <div className="d-flex justify-content-between my-3 pt-2 border-top">
                                    <div>
                                        <span className="fw-bold">Total Tagihan:</span>
                                        <div className="text-success small fw-semibold">1x Pembayaran</div>
                                    </div>
                                    <h4 className="fw-bold text-success mb-0">{formatRupiah(grandTotal)}</h4>
                                </div>

                                <button
                                    type="submit"
                                    disabled={submitting}
                                    className="btn btn-success btn-lg rounded-pill w-100 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
                                >
                                    {submitting ? (
                                        <>
                                            <span className="spinner-border spinner-border-sm" role="status"></span>
                                            <span>Memproses...</span>
                                        </>
                                    ) : (
                                        <>
                                            <CheckCircle2 size={20} />
                                            <span>Buat Pesanan Sekarang</span>
                                        </>
                                    )}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                )}
            </div>

            {/* Interactive Map Location Picker Modal */}
            <LocationPickerModal
                isOpen={showMapModal}
                onClose={() => setShowMapModal(false)}
                onSelectLocation={handleSelectLocation}
                initialCoords={pinnedLocation ? { lat: pinnedLocation.lat, lng: pinnedLocation.lng } : null}
                currentAddress={alamat}
            />
        </div>
    );
};

export default Checkout;
