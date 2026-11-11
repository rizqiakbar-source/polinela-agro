import React, { useState, useEffect, useRef } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { MailCheck, RotateCw, ArrowLeft, CheckCircle2 } from 'lucide-react';
import Swal from 'sweetalert2';

const VerifyOtp = () => {
    const { verifyOtp, resendOtp } = useAuth();
    const location = useLocation();
    const navigate = useNavigate();

    const initialEmail = location.state?.email || sessionStorage.getItem('pending_otp_email') || '';
    const [email, setEmail] = useState(initialEmail);
    const [otpDigits, setOtpDigits] = useState(['', '', '', '', '', '']);
    const [submitting, setSubmitting] = useState(false);
    const [resending, setResending] = useState(false);
    const [cooldown, setCooldown] = useState(60);
    const [debugOtp, setDebugOtp] = useState(location.state?.debug_otp || null);

    const inputRefs = useRef([]);

    // Persist email in session storage so page refresh retains state
    useEffect(() => {
        if (location.state?.email) {
            sessionStorage.setItem('pending_otp_email', location.state.email);
            setEmail(location.state.email);
        }
    }, [location.state?.email]);

    // Countdown timer for resend OTP
    useEffect(() => {
        if (cooldown <= 0) return;
        const timer = setInterval(() => {
            setCooldown((prev) => prev - 1);
        }, 1000);
        return () => clearInterval(timer);
    }, [cooldown]);

    // Auto-focus the first input on mount
    useEffect(() => {
        if (inputRefs.current[0]) {
            inputRefs.current[0].focus();
        }
    }, []);

    // Handle single digit input
    const handleDigitChange = (index, value) => {
        // Only accept numbers
        const cleanVal = value.replace(/\D/g, '');
        if (!cleanVal) {
            const newDigits = [...otpDigits];
            newDigits[index] = '';
            setOtpDigits(newDigits);
            return;
        }

        // If user pastes multi-character code into an input
        if (cleanVal.length > 1) {
            const pasted = cleanVal.slice(0, 6).split('');
            const newDigits = [...otpDigits];
            pasted.forEach((char, i) => {
                if (index + i < 6) {
                    newDigits[index + i] = char;
                }
            });
            setOtpDigits(newDigits);
            const nextIdx = Math.min(index + pasted.length, 5);
            inputRefs.current[nextIdx]?.focus();
            return;
        }

        const newDigits = [...otpDigits];
        newDigits[index] = cleanVal;
        setOtpDigits(newDigits);

        // Move to next input box
        if (cleanVal && index < 5) {
            inputRefs.current[index + 1]?.focus();
        }
    };

    // Handle Backspace navigation
    const handleKeyDown = (index, e) => {
        if (e.key === 'Backspace' && !otpDigits[index] && index > 0) {
            inputRefs.current[index - 1]?.focus();
        }
    };

    // Handle full paste on any input
    const handlePaste = (e) => {
        e.preventDefault();
        const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
        if (pastedData) {
            const newDigits = pastedData.split('');
            while (newDigits.length < 6) {
                newDigits.push('');
            }
            setOtpDigits(newDigits);
            const focusIdx = Math.min(pastedData.length, 5);
            inputRefs.current[focusIdx]?.focus();
        }
    };

    const fullOtp = otpDigits.join('');

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!email) {
            Swal.fire({
                icon: 'warning',
                title: 'Email Tidak Ditemukan',
                text: 'Silakan kembali ke halaman pendaftaran dan isi data Anda.',
            });
            return;
        }

        if (fullOtp.length < 6) {
            Swal.fire({
                icon: 'warning',
                title: 'Kode Tidak Lengkap',
                text: 'Harap masukkan 6 digit kode OTP yang tertera pada email Anda.',
            });
            return;
        }

        setSubmitting(true);
        const res = await verifyOtp(email, fullOtp);
        setSubmitting(false);

        if (res?.success) {
            navigate('/');
        }
    };

    const handleResend = async () => {
        if (cooldown > 0 || resending || !email) return;

        setResending(true);
        const res = await resendOtp(email);
        setResending(false);

        if (res?.success) {
            setCooldown(60);
            if (res.debug_otp) {
                setDebugOtp(res.debug_otp);
            }
        } else if (res?.retry_after) {
            setCooldown(res.retry_after);
        }
    };

    return (
        <div className="min-vh-100 d-flex flex-column justify-content-between position-relative login-page-bg py-4 px-3">
            {/* Top Bar */}
            <div className="container d-flex justify-content-between align-items-center mb-3">
                <Link to="/" className="d-flex align-items-center gap-2 text-decoration-none">
                    <img
                        src="/logo-polinela.png"
                        alt="Logo Polinela"
                        style={{ width: '42px', height: '42px', objectFit: 'contain' }}
                    />
                    <div>
                        <span className="fw-bold fs-6 d-block lh-1 text-heading">POLITEKNIK NEGERI LAMPUNG</span>
                        <small className="text-muted" style={{ fontSize: '11px', letterSpacing: '0.8px' }}>
                            POLINELA AGRO DIGITAL
                        </small>
                    </div>
                </Link>
            </div>

            {/* Main Form Container */}
            <div className="container my-auto" style={{ maxWidth: '480px' }}>
                <div className="card border-0 shadow-lg rounded-4 p-4 p-sm-5 glass-card login-card animate-fade-in text-center">
                    {/* Header Icon */}
                    <div className="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mx-auto mb-3" style={{ width: '72px', height: '72px' }}>
                        <MailCheck size={36} />
                    </div>

                    <h4 className="fw-bold text-heading mb-2">Verifikasi Email Anda</h4>
                    <p className="text-muted small mb-3">
                        Kami telah mengirimkan 6 digit kode OTP ke alamat email:
                    </p>

                    {!email ? (
                        <div className="mb-4">
                            <input
                                type="email"
                                className="form-control form-control-sm text-center mx-auto"
                                style={{ maxWidth: '300px' }}
                                placeholder="Ketik alamat email Anda..."
                                onChange={(e) => {
                                    setEmail(e.target.value);
                                    sessionStorage.setItem('pending_otp_email', e.target.value);
                                }}
                            />
                        </div>
                    ) : (
                        <div className="p-2 px-3 rounded-pill bg-light border d-inline-block fw-semibold text-dark small mb-4 mx-auto text-truncate" style={{ maxWidth: '100%' }}>
                            {email}
                        </div>
                    )}

                    {/* Debug OTP Pill (Bila di lokal/dev) */}
                    {debugOtp && (
                        <div className="alert alert-info py-2 px-3 small rounded-3 mb-3 text-start">
                            <strong>Kode OTP Uji Coba:</strong> <span className="badge bg-primary fs-6 ms-2 font-monospace">{debugOtp}</span>
                            <div className="text-muted" style={{ fontSize: '11px' }}>
                                (Ditampilkan untuk memudahkan pengujian sistem secara langsung)
                            </div>
                        </div>
                    )}

                    {/* OTP Inputs Form */}
                    <form onSubmit={handleSubmit}>
                        <div className="d-flex justify-content-center gap-2 mb-4" onPaste={handlePaste}>
                            {otpDigits.map((digit, idx) => (
                                <input
                                    key={idx}
                                    ref={(el) => (inputRefs.current[idx] = el)}
                                    type="text"
                                    inputMode="numeric"
                                    maxLength={1}
                                    value={digit}
                                    onChange={(e) => handleDigitChange(idx, e.target.value)}
                                    onKeyDown={(e) => handleKeyDown(idx, e.target)}
                                    className="form-control text-center fw-bold fs-3 rounded-3 border-2"
                                    style={{
                                        width: '48px',
                                        height: '56px',
                                        borderColor: digit ? '#15803d' : '#dee2e6',
                                        backgroundColor: digit ? '#f0fdf4' : '#ffffff',
                                        color: '#15803d',
                                    }}
                                    required
                                />
                            ))}
                        </div>

                        <p className="text-muted small mb-4" style={{ fontSize: '12px' }}>
                            Masa berlaku kode adalah <span className="fw-bold text-dark">5 menit</span>. Periksa juga folder <em>Spam / Junk</em> jika tidak ditemukan di kotak masuk utama.
                        </p>

                        <button
                            type="submit"
                            disabled={submitting || fullOtp.length < 6}
                            className="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 mb-3"
                        >
                            {submitting ? (
                                <span className="spinner-border spinner-border-sm" role="status"></span>
                            ) : (
                                <>
                                    <CheckCircle2 size={18} />
                                    <span>Verifikasi & Lanjutkan</span>
                                </>
                            )}
                        </button>
                    </form>

                    {/* Resend OTP section */}
                    <div className="d-flex align-items-center justify-content-center gap-2 mt-2">
                        {cooldown > 0 ? (
                            <span className="text-muted small">
                                Kirim ulang kode dalam <strong className="text-dark">{cooldown} detik</strong>
                            </span>
                        ) : (
                            <button
                                type="button"
                                onClick={handleResend}
                                disabled={resending}
                                className="btn btn-link text-agro text-decoration-none fw-semibold p-0 small d-inline-flex align-items-center gap-1"
                            >
                                <RotateCw size={14} className={resending ? 'spinner-border spinner-border-sm' : ''} />
                                <span>Kirim Ulang Kode OTP</span>
                            </button>
                        )}
                    </div>

                    {/* Back link */}
                    <div className="border-top pt-3 mt-4 text-center">
                        <Link to="/register" className="text-muted small text-decoration-none d-inline-flex align-items-center gap-1">
                            <ArrowLeft size={14} />
                            <span>Salah email? Kembali ke pendaftaran</span>
                        </Link>
                    </div>
                </div>
            </div>

            {/* Bottom Credits */}
            <div className="text-center text-muted small mt-3">
                &copy; {new Date().getFullYear()} Politeknik Negeri Lampung • Teaching Factory & Kebun Riset
            </div>
        </div>
    );
};

export default VerifyOtp;

