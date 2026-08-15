<?php

namespace App\Notifications;

use App\Support\IdGenerator;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk Tim Teknis saat tiket diassign ke mereka.
 *
 * Cara pakai di controller:
 *   $teknisUser->notify(new TugasBaruNotification(
 *       kodeTiket: $tiket->kode_tiket,
 *       judulMasalah: $tiket->judul ?? $tiket->kategori->nama_kategori,
 *       url: route('tim_teknis.antrean'),
 *   ));
 */
class TugasBaruNotification extends Notification
{
    public function __construct(
        public readonly string $kodeTiket,
        public readonly string $judulMasalah,
        public readonly string $url,
    ) {
        $this->id = IdGenerator::make('NTF');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[SIPASTI] Tugas Baru — Tiket #{$this->kodeTiket}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Anda telah ditugaskan untuk menangani tiket **#{$this->kodeTiket}**.")
            ->line("**Masalah:** {$this->judulMasalah}")
            ->line('Segera tindak lanjuti tiket ini melalui aplikasi SIPASTI.')
            ->action('Lihat Antrean', $this->url)
            ->salutation('Salam, Sistem SIPASTI');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'  => 'tugas_baru',
            'title' => 'Tugas Baru Diterima',
            'body'  => "Tiket #{$this->kodeTiket} — {$this->judulMasalah} perlu ditangani.",
            'url'   => $this->url,
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function broadcastType(): string
    {
        return 'TugasBaru';
    }
}
