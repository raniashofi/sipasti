<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi perubahan status tiket — dikirim ke OPD.
 *
 * Cara pakai di controller:
 *   $opdUser->notify(new StatusTiketNotification(
 *       kodeTiket : $tiket->kode_tiket,
 *       status    : 'diverifikasi',   // atau: 'ditolak', 'selesai', 'panduan_remote'
 *       keterangan: 'Tiket kamu sedang diproses.',
 *       url       : route('opd.tiket.index'),
 *   ));
 */
class StatusTiketNotification extends Notification
{

    private const LABEL = [
        'diverifikasi'     => 'Tiket Diverifikasi',
        'ditolak'          => 'Tiket Ditolak',
        'selesai'          => 'Tiket Selesai',
        'panduan_remote'   => 'Panduan Remote Dimulai',
        'sedang_ditangani' => 'Tiket Sedang Ditangani',
        'perlu_revisi'     => 'Tiket Perlu Revisi',
        'perbaikan_teknis' => 'Perbaikan Teknis Sedang Berlangsung',
        'rusak_berat'      => 'Perangkat Rusak Berat',
        'tiket_ditutup'    => 'Tiket Ditutup',
        'dibuka_kembali'   => 'Tiket Dibuka Kembali',
    ];

    private const WARNA = [
        'diverifikasi'     => 'primary',
        'ditolak'          => 'danger',
        'selesai'          => 'success',
        'panduan_remote'   => 'info',
        'sedang_ditangani' => 'info',
        'perlu_revisi'     => 'warning',
        'perbaikan_teknis' => 'info',
        'rusak_berat'      => 'danger',
        'tiket_ditutup'    => 'secondary',
        'dibuka_kembali'   => 'warning',
    ];

    public function __construct(
        public readonly string  $kodeTiket,
        public readonly string  $status,
        public readonly string  $keterangan,
        public readonly string  $url,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = self::LABEL[$this->status] ?? 'Update Tiket';

        return (new MailMessage)
            ->subject("[SIPASTI] {$label} — #{$this->kodeTiket}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Terdapat pembaruan untuk tiket Anda dengan kode **#{$this->kodeTiket}**.")
            ->line("**Status:** {$label}")
            ->line("**Keterangan:** {$this->keterangan}")
            ->action('Lihat Detail Tiket', $this->url)
            ->line('Jika Anda memiliki pertanyaan, silakan hubungi tim helpdesk kami.')
            ->salutation('Salam, Tim SIPASTI');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon'  => $this->status,
            'title' => self::LABEL[$this->status] ?? 'Update Tiket',
            'body'  => "Tiket #{$this->kodeTiket}: {$this->keterangan}",
            'url'   => $this->url,
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function broadcastType(): string
    {
        return 'StatusTiket';
    }
}
