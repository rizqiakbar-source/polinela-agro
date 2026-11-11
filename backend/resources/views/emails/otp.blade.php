<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Pendaftaran</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 24px;
            color: #1f2937;
        }
        .container {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }
        .header {
            background: linear-gradient(135deg, #15803d 0%, #166534 100%);
            padding: 32px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .content {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 16px;
            color: #374151;
        }
        .otp-box {
            background: #f0fdf4;
            border: 2px dashed #86efac;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 24px 0;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 38px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #15803d;
            margin: 8px 0;
        }
        .expiry-note {
            font-size: 13px;
            color: #6b7280;
            margin-top: 8px;
        }
        .warning-box {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 12px 16px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
            color: #991b1b;
            line-height: 1.5;
        }
        .footer {
            padding: 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>POLINELA AGRO DIGITAL</h1>
            <p>Teaching Factory & Kebun Riset Politeknik Negeri Lampung</p>
        </div>

        <div class="content">
            <p class="greeting">Halo <strong>{{ $nama }}</strong>,</p>
            <p style="font-size: 14px; line-height: 1.6; color: #4b5563;">
                Terima kasih telah mendaftar di <strong>Polinela Agro Digital</strong>. Untuk menyelesaikan pendaftaran dan mengaktifkan akun Anda, silakan gunakan kode verifikasi satu kali (OTP) berikut:
            </p>

            <div class="otp-box">
                <div style="font-size: 12px; text-transform: uppercase; color: #166534; font-weight: 600; letter-spacing: 1px;">KODE VERIFIKASI OTP</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="expiry-note">Kode ini hanya berlaku selama <strong>{{ $expiryMinutes }} menit</strong>.</div>
            </div>

            <p style="font-size: 14px; line-height: 1.6; color: #4b5563;">
                Masukkan kode ini pada layar verifikasi di aplikasi Polinela Agro.
            </p>

            <div class="warning-box">
                <strong>PENTING:</strong> Jangan berikan kode OTP ini kepada siapa pun, termasuk staf atau pihak yang mengaku dari Politeknik Negeri Lampung. Tim kami tidak akan pernah meminta kode OTP Anda.
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Politeknik Negeri Lampung. Seluruh hak cipta dilindungi undang-undang.<br>
            Jl. Soekarno Hatta No. 10, Rajabasa, Bandar Lampung, Lampung 35144
        </div>
    </div>
</body>
</html>

