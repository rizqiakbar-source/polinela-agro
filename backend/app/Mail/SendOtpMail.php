<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $nama;
    public int $expiryMinutes;

    public function __construct(string $otp, string $nama = 'Konsumen', int $expiryMinutes = 5)
    {
        $this->otp = $otp;
        $this->nama = $nama;
        $this->expiryMinutes = $expiryMinutes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address', 'havertz0921@gmail.com'), 'Polinela Agro Digital'),
            subject: "{$this->otp} adalah Kode Verifikasi Akun Polinela Agro Anda",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }
}

