import React, { useEffect } from 'react';
import { createPortal } from 'react-dom';
import { Printer, X, ShieldCheck, MapPin, Phone, Mail, CheckCircle2, AlertCircle, Info } from 'lucide-react';
import { formatRupiah, formatDate } from '../utils/format';

const ReceiptModal = ({ isOpen, onClose, order }) => {
    useEffect(() => {
        const handleKeyDown = (e) => {
            if (e.key === 'Escape') onClose();
        };
        if (isOpen) {
            document.body.style.overflow = 'hidden';
            window.addEventListener('keydown', handleKeyDown);
        }
        return () => {
            document.body.style.overflow = 'unset';
            window.removeEventListener('keydown', handleKeyDown);
        };
    }, [isOpen, onClose]);

    if (!isOpen || !order) return null;

    const handlePrint = () => {
        window.print();
    };

    const isPaid = order.payment?.status === 'lunas' || ['diproses', 'dikirim', 'selesai'].includes(order.status);
    const paymentMethodName = order.payment?.metode 
        ? (order.payment.metode === 'transfer' ? `Transfer Bank (${order.payment.bank || 'Mandiri'})` : order.payment.metode.toUpperCase())
        : (order.payment?.bank ? `Transfer Bank (${order.payment.bank})` : 'Transfer Bank');
    
    const orderDate = formatDate(order.created_at || new Date().toISOString());
    const orderNumber = order.order_number || order.no_pesanan || `#${order.id}`;
    const trxNumber = order.payment?.no_transaksi || '-';
    const totalProduk = Number(order.total_produk ?? order.total_harga ?? 0);
    const ongkir = Number(order.ongkir ?? order.ongkos_kirim ?? 0);
    const diskon = Number(order.diskon ?? 0);
    const grandTotal = Number(order.grand_total ?? order.total_akhir ?? (totalProduk + ongkir - diskon));

    return createPortal(
        <div 
            className="receipt-modal-backdrop position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center p-2 p-md-3"
            style={{ 
                backgroundColor: 'rgba(15, 23, 42, 0.82)', 
                backdropFilter: 'blur(8px)', 
                zIndex: 999999,
                overflowY: 'auto'
            }}
            onClick={(e) => {
                if (e.target === e.currentTarget) onClose();
            }}
        >
            {/* Modal Dialog Card */}
            <div 
                className="receipt-modal-dialog bg-white text-dark rounded-4 shadow-2xl overflow-hidden d-flex flex-column my-auto"
                style={{ 
                    width: '100%', 
                    maxWidth: '820px', 
                    maxHeight: '94vh',
                    position: 'relative',
                    zIndex: 1000000
                }}
            >
                {/* Modal Action Top Bar (Hidden on print) */}
                <div className="no-print d-flex align-items-center justify-content-between px-4 py-3 border-bottom bg-light">
                    <div className="d-flex align-items-center gap-2">
                        <div className="bg-success text-white rounded-circle p-1 d-flex align-items-center justify-content-center" style={{ width: '28px', height: '28px' }}>
                            <Printer size={16} />
                        </div>
                        <span className="fw-bold text-dark fs-6">Struk & Invoice Resmi Pesanan</span>
                    </div>
                    <div className="d-flex align-items-center gap-2">
                        <button 
                            type="button" 
                            onClick={handlePrint}
                            className="btn btn-success btn-sm rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1 shadow-sm"
                        >
                            <Printer size={15} />
                            <span>Cetak Sekarang (Print / PDF)</span>
                        </button>
                        <button 
                            type="button" 
                            onClick={onClose}
                            className="btn btn-outline-secondary btn-sm rounded-circle p-1 d-flex align-items-center justify-content-center"
                            style={{ width: '32px', height: '32px' }}
                            title="Tutup (Esc)"
                        >
                            <X size={18} />
                        </button>
                    </div>
                </div>

                {/* Printable Struk & Invoice Body */}
                <div className="receipt-printable-area p-4 p-md-5 overflow-y-auto bg-white position-relative" style={{ color: '#1e293b' }}>
                    
                    {/* Official Kop Header */}
                    <div className="d-flex flex-wrap align-items-center justify-content-between border-bottom pb-4 mb-4 gap-3">
                        <div className="d-flex align-items-center gap-3">
                            <img 
                                src="/logo-polinela.png" 
                                alt="Polinela Agro" 
                                style={{ width: '56px', height: '56px', objectFit: 'contain' }}
                                onError={(e) => { e.target.style.display = 'none'; }}
                            />
                            <div>
                                <h4 className="fw-bold mb-0 text-success" style={{ letterSpacing: '-0.5px' }}>POLINELA AGRO DIGITAL</h4>
                                <div className="fw-semibold text-dark small">{order.unit?.nama_unit || 'Teaching Factory Politeknik Negeri Lampung'}</div>
                                <div className="text-muted" style={{ fontSize: '0.78rem' }}>
                                    Politeknik Negeri Lampung • Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung
                                </div>
                            </div>
                        </div>

                        <div className="text-sm-end">
                            <div className="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill small fw-bold font-monospace border mb-1"
                                 style={{
                                     backgroundColor: isPaid ? '#dcfce7' : '#fef9c3',
                                     color: isPaid ? '#15803d' : '#854d0e',
                                     borderColor: isPaid ? '#86efac' : '#fde047'
                                 }}>
                                {isPaid ? <CheckCircle2 size={14} /> : <AlertCircle size={14} />}
                                <span>{isPaid ? 'LUNAS / TERVERIFIKASI' : 'MENUNGGU PEMBAYARAN'}</span>
                            </div>
                            <div className="fw-bold fs-6 text-dark">NO: {orderNumber}</div>
                            <div className="text-muted small">TRX: {trxNumber}</div>
                        </div>
                    </div>

                    {/* Meta Transaction Information */}
                    <div className="row g-3 mb-4 pb-3 border-bottom small">
                        <div className="col-sm-6">
                            <span className="text-muted d-block mb-1 fw-medium">Tujuan Penagihan & Pengiriman:</span>
                            <strong className="text-dark fs-6 d-block">{order.shipping?.nama_penerima || order.user?.nama_lengkap || order.user?.nama || 'Pelanggan'}</strong>
                            <div className="text-secondary mt-1">
                                <div><Phone size={12} className="me-1 text-success d-inline" /> {order.shipping?.no_hp || order.user?.no_hp || '-'}</div>
                                <div><Mail size={12} className="me-1 text-success d-inline" /> {order.user?.email || '-'}</div>
                                <div className="mt-1">
                                    <MapPin size={12} className="me-1 text-success d-inline" /> 
                                    {order.shipping?.alamat_lengkap ? `${order.shipping.alamat_lengkap}, ${order.shipping.kota || ''} ${order.shipping.kode_pos || ''}` : (order.user?.alamat || '-')}
                                </div>
                            </div>
                        </div>

                        <div className="col-sm-6 text-sm-end">
                            <span className="text-muted d-block mb-1 fw-medium">Rincian Pengiriman & Pembayaran:</span>
                            <div className="text-dark"><strong>Kurir / Ekspedisi:</strong> <span className="text-uppercase">{order.shipping?.ekspedisi || 'Kurir Kampus / Standar'}</span></div>
                            <div className="text-dark"><strong>Nomor Resi:</strong> {order.shipping?.no_resi ? <span className="badge bg-primary font-monospace ms-1">{order.shipping.no_resi}</span> : <span className="text-muted fst-italic">Sedang Diproses</span>}</div>
                            <div className="text-dark mt-2"><strong>Waktu Transaksi:</strong> {orderDate}</div>
                            <div className="text-dark mt-1"><strong>Metode Bayar:</strong> {paymentMethodName}</div>
                        </div>
                    </div>

                    {/* Order Items Table */}
                    <div className="table-responsive mb-4">
                        <table className="table table-bordered align-middle mb-0" style={{ borderColor: '#cbd5e1' }}>
                            <thead style={{ backgroundColor: '#f8fafc' }}>
                                <tr className="text-uppercase small fw-bold text-secondary">
                                    <th style={{ width: '5%', textAlign: 'center' }}>No</th>
                                    <th>Komoditas Produk Perkebunan</th>
                                    <th style={{ width: '22%', textAlign: 'right' }}>Harga Satuan</th>
                                    <th style={{ width: '12%', textAlign: 'center' }}>Qty</th>
                                    <th style={{ width: '24%', textAlign: 'right' }}>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody className="small">
                                {order.details?.map((item, idx) => {
                                    const itemPrice = Number(item.harga ?? item.harga_satuan ?? 0);
                                    const itemQty = Number(item.qty ?? item.jumlah ?? 1);
                                    const itemSubtotal = Number(item.subtotal ?? (itemPrice * itemQty));

                                    return (
                                        <tr key={item.id || idx}>
                                            <td className="text-center text-muted">{idx + 1}</td>
                                            <td>
                                                <div className="fw-bold text-dark">{item.nama_produk}</div>
                                                <div className="text-muted" style={{ fontSize: '0.75rem' }}>Unit: {order.unit?.nama_unit || 'Tefa Polinela'}</div>
                                            </td>
                                            <td className="text-end font-monospace">{formatRupiah(itemPrice)}</td>
                                            <td className="text-center fw-semibold">{itemQty}</td>
                                            <td className="text-end fw-bold text-dark font-monospace">{formatRupiah(itemSubtotal)}</td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>

                    {/* Calculation Totals & Notes */}
                    <div className="row g-4 mb-4">
                        <div className="col-sm-6">
                            <div className="p-3 bg-light rounded-3 border" style={{ fontSize: '0.82rem' }}>
                                <div className="fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                                    <ShieldCheck size={14} className="text-success" /> Validasi Transaksi BLU Polinela:
                                </div>
                                <p className="text-muted mb-0">
                                    Struk ini diterbitkan secara otomatis oleh sistem e-commerce Polinela Agro Digital dan sah sebagai bukti pembayaran resmi atas produk riset Teaching Factory.
                                </p>
                            </div>

                            {/* Barcode simulation */}
                            <div className="mt-3 text-center text-sm-start">
                                <div className="font-monospace fw-bold text-muted" style={{ letterSpacing: '4px', fontSize: '0.9rem' }}>
                                    *||| | |||| | ||||| | |||*
                                </div>
                                <small className="text-muted font-monospace">{trxNumber !== '-' ? trxNumber : orderNumber}</small>
                            </div>
                        </div>

                        <div className="col-sm-6">
                            <div className="d-flex flex-column gap-1 small">
                                <div className="d-flex justify-content-between text-muted py-1 border-bottom">
                                    <span>Subtotal Produk Unit:</span>
                                    <span className="font-monospace">{formatRupiah(totalProduk)}</span>
                                </div>
                                <div className="d-flex justify-content-between text-muted py-1 border-bottom">
                                    <span>Ongkos Kirim ({order.shipping?.ekspedisi || 'Kurir'}):</span>
                                    <span className="font-monospace">{formatRupiah(ongkir)}</span>
                                </div>
                                {diskon > 0 && (
                                    <div className="d-flex justify-content-between text-success py-1 border-bottom">
                                        <span>Diskon Produk / Voucher:</span>
                                        <span className="font-monospace">-{formatRupiah(diskon)}</span>
                                    </div>
                                )}
                                <div className="d-flex justify-content-between align-items-center fw-bold text-dark fs-6 pt-2 mt-1 border-top border-2 border-dark">
                                    <span>TOTAL PEMBAYARAN:</span>
                                    <span className="text-success fs-5 font-monospace">{formatRupiah(grandTotal)}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Struk Footer */}
                    <div className="pt-3 border-top text-center text-muted small" style={{ fontSize: '0.78rem' }}>
                        <div>Terima kasih telah berbelanja di Teaching Factory & Unit Usaha Perkebunan Polinela!</div>
                        <div className="mt-1">Dicetak pada: {new Date().toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' })} WIB</div>
                    </div>

                </div>

                {/* Modal Footer Controls (Hidden on print) */}
                <div className="no-print d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light">
                    <small className="text-muted d-flex align-items-center gap-1">
                        <Info size={14} className="text-primary flex-shrink-0" /> Tips: Pilih <em>"Save as PDF"</em> pada jendela cetak browser untuk mengunduh berkas PDF.
                    </small>
                    <div className="d-flex gap-2">
                        <button type="button" onClick={onClose} className="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            Tutup
                        </button>
                        <button type="button" onClick={handlePrint} className="btn btn-success btn-sm rounded-pill px-4 fw-bold d-flex align-items-center gap-1 shadow-sm">
                            <Printer size={15} /> Cetak Struk
                        </button>
                    </div>
                </div>

            </div>
        </div>,
        document.body
    );
};

export default ReceiptModal;
