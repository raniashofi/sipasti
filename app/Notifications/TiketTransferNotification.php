<?php

namespace App\Notifications;

use App\Support\IdGenerator;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk Admin Helpdesk saat tiket ditransfer ke bidang mereka.
 *
 * Cara pakai di controller:
 *   $adminUser->notify(new TiketTransferNotification($tiket->id, $namaOpd, $instruksi, $urlTiket));
 */
class TiketTransferNotification extends Notification
{
    public function __construct(
        public readonly string $kodeTiket,
        public readonly string $namaOpd,
        public readonly string $instruksi,
        public readonly string $url,
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
            ->subject("[SIPASTI] Tiket Ditransfer ke Bidang Anda — #{$this->kodeTiket}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tiket **#{$this->kodeTiket}** dari **{$this->namaOpd}** telah ditransfer ke bidang Anda.")
            ->line("**Pesan:** {$this->instruksi}")
            ->action('Lihat Tiket', $this->url)
            ->salutation('Salam, Sistem SIPASTI');
    }

    /**
     * Data yang disimpan ke tabel notifications (kolom "data" JSON).
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'  => 'tiket_transfer',
            'title' => 'Tiket Ditransfer ke Bidang Anda',
            'body'  => "Tiket #{$this->kodeTiket} dari {$this->namaOpd} ditransfer ke bidang Anda.\nPesan: {$this->instruksi}",
            'url'   => $this->url,
        ];
    }

    /**
     * Payload yang di-broadcast ke Reverb.
     * Frontend mendengarkan event ini via .notification() atau .listen('.TiketTransfer').
     */
    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Nama event broadcast — digunakan di frontend: .listen('.TiketTransfer', ...)
     */
    public function broadcastType(): string
    {
        return 'TiketTransfer';
    }
}
