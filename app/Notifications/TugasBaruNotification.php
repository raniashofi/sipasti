<?php

namespace App\Notifications;

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
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
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
