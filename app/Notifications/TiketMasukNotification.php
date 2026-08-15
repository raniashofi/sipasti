<?php

namespace App\Notifications;

use App\Support\IdGenerator;

use Illuminate\Notifications\Messages\MailMessage;
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
        public readonly ?string $judul = null,
        public readonly ?string $isi = null,
        public readonly ?string $icon = null,
    ) {
        $this->id = IdGenerator::make('NTF');
    }

    /**
     * Kirim melalui: database (tersimpan) + broadcast (Reverb real-time).
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[SIPASTI] Tiket Baru Masuk — #{$this->kodeTiket}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tiket baru **#{$this->kodeTiket}** dari **{$this->namaOpd}** telah masuk dan menunggu verifikasi Anda.")
            ->action('Lihat Tiket', $this->url)
            ->salutation('Salam, Sistem SIPASTI');
    }

    /**
     * Data yang disimpan ke tabel notifications (kolom "data" JSON).
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'  => $this->icon ?? 'tiket_masuk',
            'title' => $this->judul ?? 'Tiket Baru Masuk',
            'body'  => $this->isi ?? "Tiket #{$this->kodeTiket} dari {$this->namaOpd} menunggu verifikasi.",
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
