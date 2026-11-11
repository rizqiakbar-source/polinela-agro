<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'aksi',
        'modul',
        'deskripsi',
        'ip_address',
        'user_agent',
        'activity',
        'module',
        'details',
    ];

    public function setActivityAttribute(?string $value): void
    {
        $this->attributes['aksi'] = $value;
    }

    public function setModuleAttribute(?string $value): void
    {
        $this->attributes['modul'] = $value;
    }

    public function setDetailsAttribute(?string $value): void
    {
        $this->attributes['deskripsi'] = $value;
    }

    protected static function booted()
    {
        static::creating(function ($log) {
            if (empty($log->ip_address) && request()) {
                $log->ip_address = self::getClientIp();
            }
            if (empty($log->user_agent) && request()) {
                $log->user_agent = request()->userAgent();
            }
            if (empty($log->user_id) && request()) {
                $log->user_id = Auth::guard('sanctum')->id() ?? Auth::id();
            }
        });
    }

    public static function getClientIp()
    {
        $request = request();
        if (!$request) {
            return null;
        }

        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Load balancer / proxy
            'HTTP_X_REAL_IP',            // Nginx / Ingress
            'HTTP_CLIENT_IP',            // Proxy client
            'HTTP_X_CLUSTER_CLIENT_IP',
        ];

        foreach ($headers as $header) {
            $ipList = $request->server($header);
            if (!empty($ipList)) {
                $ips = explode(',', $ipList);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }

        return $request->ip() ?: '127.0.0.1';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function log(?string $aksi, ?string $modul, ?string $deskripsi): void
    {
        $userId = Auth::guard('sanctum')->id() ?? Auth::id();
        self::create([
            'user_id'    => $userId,
            'aksi'       => $aksi,
            'modul'      => $modul,
            'deskripsi'  => $deskripsi,
            'ip_address' => self::getClientIp(),
            'user_agent' => request() ? request()->userAgent() : null,
        ]);
    }
}
