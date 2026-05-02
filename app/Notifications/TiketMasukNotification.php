<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk Admin Helpdesk saat OPD mengajukan tiket baru.
 *
 * Cara pakai di controller:
 *   $adminUser->notify(new TiketMasukNotification($tiket->kode_tiket, $namaOpd, $urlTiket));
 */
class TiketMasukNotification extends Notification
{
    public function __construct(
        public readonly string $kodeTiket,
        public readonly string $namaOpd,
        public readonly string $url,
    ) {}

    /**
     * Kirim melalui: database (tersimpan) + broadcast (Reverb real-time).
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Data yang disimpan ke tabel notifications (kolom "data" JSON).
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'  => 'tiket_masuk',
            'title' => 'Tiket Baru Masuk',
            'body'  => "Tiket #{$this->kodeTiket} dari {$this->namaOpd} menunggu verifikasi.",
            'url'   => $this->url,
        ];
    }

    /**
     * Payload yang di-broadcast ke Reverb.
     * Frontend mendengarkan event ini via .notification() atau .listen('.TiketMasuk').
     */
    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Nama event broadcast — digunakan di frontend: .listen('.TiketMasuk', ...)
     */
    public function broadcastType(): string
    {
        return 'TiketMasuk';
    }
}
