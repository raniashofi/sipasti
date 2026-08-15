<?php

namespace App\Notifications;

use App\Support\IdGenerator;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi email peringatan tiket yang telah melewati batas SLA.
 *
 * Dikirim oleh scheduler harian ke Admin Helpdesk dan Tim Teknis
 * yang bertanggung jawab atas tiket tersebut.
 *
 * Cara pakai:
 *   $user->notify(new SlaBreachMailNotification(
 *       kodeTiket      : $tiket->id,
 *       subjekMasalah  : $tiket->subjek_masalah,
 *       batasHari      : $bidang->batas_hari_pengerjaan,
 *       hariTerlambat  : $selisihHari,
 *   ));
 */
class SlaBreachMailNotification extends Notification
{
    public function __construct(
        public readonly string $kodeTiket,
        public readonly string $subjekMasalah,
        public readonly int    $batasHari,
        public readonly int    $hariTerlambat,
    ) {
        $this->id = IdGenerator::make('NTF');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $role = $notifiable->role ?? 'admin_helpdesk';

        $url = match ($role) {
            'tim_teknis'     => route('tim_teknis.antrean'),
            'admin_helpdesk' => route('admin_helpdesk.tiket.distribusi'),
            default          => url('/'),
        };

        return (new MailMessage)
            ->subject("[PENTING] Peringatan SLA — Tiket #{$this->kodeTiket}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tiket **#{$this->kodeTiket}** dengan subjek **\"{$this->subjekMasalah}\"** telah melewati batas waktu penanganan SLA.")
            ->line("**Batas SLA:** {$this->batasHari} hari")
            ->line("**Keterlambatan:** {$this->hariTerlambat} hari melebihi batas")
            ->line('Segera tindak lanjuti tiket ini agar layanan dapat diselesaikan secepatnya.')
            ->action('Lihat Tiket', $url)
            ->salutation('Salam, Sistem SIPASTI');
    }
}
